<?php
/**
 * কোর্স — টিচার নিজের কোর্স বানিয়ে বিক্রি করে, শিক্ষার্থী কিনে ক্লাস করে।
 *
 *  পাবলিক     : GET /Course (তালিকা), /Course/Details/{id}, /Course/Certificate/{code}
 *  শিক্ষার্থী : GET /Course/My, /Course/Learn/{id}?lesson=  · POST Done, Submit, Quiz, Enroll (ফ্রি কোর্স)
 *  টিচার/অ্যাডমিন: GET /Course/Manage, Edit/{id?}, Build/{id}, Students/{id}, Earnings
 *               POST Save, Publish, Delete, SaveLesson, DeleteLesson, MoveLesson, Rerecord, SyncRec, SaveTask, DeleteTask, Review
 *  কেনা = /Payment/Checkout?course={id} (আগের মতো bKash/Nagad পেমেন্ট → অ্যাডমিন Approve → ভর্তি)। টাকার ভাগ: course_admin_pct() (ডিফল্ট ৫০%)
 */
class CourseController extends Controller
{
    private const LEVELS = ['সব লেভেল', 'শুরু (Beginner)', 'মাঝারি (Intermediate)', 'অ্যাডভান্সড (Advanced)'];

    private function fail(string $m, array $x = []): void { json_response(['success' => false, 'message' => $m] + $x); }

    private function me(bool $json = false): int
    {
        $id = current_user_id();
        if ($id === null) { if ($json) $this->fail('আগে লগইন করুন।', ['login' => true]); redirect('/Account/Login?returnUrl=' . rawurlencode((string)($_SERVER['REQUEST_URI'] ?? '/Course'))); }
        return $id;
    }

    /** অ্যাডমিন (কোর্সের অনুমতি থাকলে) */
    private function isCourseAdmin(): bool { return is_admin() && admin_can(admin_action_rule('Courses')); }

    /** এই কোর্স এডিট / ক্লাস নেওয়ার অধিকার: কোর্সের টিচার বা অ্যাডমিন */
    private function canManage(?Obj $c): bool { $me = current_user_id(); return $c && $me !== null && ((int)$c->TeacherId === $me || $this->isCourseAdmin()); }

    private function manageable(int $id, bool $json = true): Obj
    {
        $c = $this->db->GetCourse($id);
        if (!$this->canManage($c)) { if ($json) $this->fail('কোর্সটি পাওয়া যায়নি বা আপনার অনুমতি নেই।'); not_found('কোর্সটি পাওয়া যায়নি।'); }
        return $c;
    }

    /** কোর্সের সব শিক্ষার্থীকে নোটিফিকেশন */
    private function notifyStudents(Obj $c, string $title, string $msg, string $url, string $icon = 'fa-graduation-cap'): void
    {
        foreach ($this->db->GetEnrolledUserIds($c->Id) as $uid) { try { $this->db->CreateNotification($uid, $title, $msg, 'info', $url, $icon); } catch (Throwable $e) { } }
    }

    private static function dt(string $v): ?string
    {
        $v = trim(str_replace('T', ' ', $v));
        if ($v === '') return null;
        foreach (['Y-m-d H:i', 'Y-m-d H:i:s'] as $f) { $d = DateTime::createFromFormat($f, $v); if ($d) return $d->format('Y-m-d H:i:s'); }
        return 'bad';
    }

    // ================================================================ পাবলিক
    public function Index(): void
    {
        $q = mb_substr(trim((string)input_str('q', '')), 0, 60);
        $cat = mb_substr(trim((string)input_str('cat', '')), 0, 60);
        $me = current_user_id();
        $mine = $me ? array_flip(array_map(fn($c) => $c->Id, $this->db->GetMyCourses($me))) : [];
        $kind = input_str('kind', '') === 'live' ? 'live' : (input_str('kind', '') === 'course' ? 'course' : '');
        $this->view(null, $this->db->GetPublicCourses($q, $cat, $kind), ['Q' => $q, 'Cat' => $cat, 'Cats' => $this->db->GetCourseCategories(), 'Mine' => $mine, 'Kind' => $kind]);
    }

    public function Details(): void
    {
        $c = $this->db->GetCourse((int)input_int('id', 0));
        $me = current_user_id();
        if (!$c || (!$c->IsLive && !$this->canManage($c) && !($me && $this->db->IsEnrolled($c->Id, $me)))) not_found('কোর্সটি পাওয়া যায়নি।');
        $pending = false;
        if ($me) { try { $pending = (int)$this->db->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `UserId` = :u AND `CourseId` = :c AND `Status` = 'Pending'", ['u' => $me, 'c' => $c->Id]) > 0; } catch (Throwable $e) { } }
        $this->view(null, $c, ['Lessons' => $this->db->GetLessons($c->Id), 'Tasks' => $this->db->GetTasks($c->Id), 'Enrolled' => $me ? $this->db->IsEnrolled($c->Id, $me) : false,
            'CanManage' => $this->canManage($c), 'PendingPay' => $pending, 'Students' => $this->db->CountCourseEnrollments($c->Id)]);
    }

    /** GET /Course/Certificate/{code} — যে কেউ যাচাই করতে পারে */
    public function Certificate(): void
    {
        $code = strtoupper(trim((string)input_str('id', input_str('code', ''))));
        $cert = preg_match('/^MM-[A-F0-9]{8}$/', $code) ? $this->db->GetCertificate($code) : null;
        if (!$cert) not_found('সার্টিফিকেটটি পাওয়া যায়নি।');
        $this->view(null, $cert);
    }

    // ================================================================ শিক্ষার্থী
    public function My(): void
    {
        $me = $this->me();
        $this->view(null, $this->db->GetMyCourses($me));
    }

    /** POST /Course/Enroll (id) — শুধু ফ্রি কোর্স; দামি কোর্স → Checkout */
    public function Enroll_POST(): void
    {
        $me = $this->me(true);
        $c = $this->db->GetCourse((int)input_int('id', 0));
        if (!$c || !$c->IsLive) $this->fail('কোর্সটি পাওয়া যায়নি।');
        if (current_role() === 'Admin') $this->fail('অ্যাডমিন অ্যাকাউন্ট দিয়ে কোর্সে ভর্তি হওয়া যায় না।');
        if ((int)$c->TeacherId === $me) $this->fail('এটা আপনার নিজের কোর্স।');
        if ($c->Price > 0) json_response(['success' => true, 'redirect' => '/Payment/Checkout?course=' . $c->Id]);
        $this->db->Enroll($c->Id, $me, null, 0, 'free');
        try { $this->db->CreateNotification((int)$c->TeacherId, 'নতুন শিক্ষার্থী 🎓', (session_get('UserFullName') ?: 'একজন শিক্ষার্থী') . " আপনার ফ্রি কোর্স \"{$c->Title}\" এ ভর্তি হয়েছেন।", 'success', '/Course/Students/' . $c->Id, 'fa-user-graduate'); } catch (Throwable $e) { }
        json_response(['success' => true, 'redirect' => '/Course/Learn/' . $c->Id, 'message' => 'ভর্তি হয়েছেন!']);
    }

    /** GET /Course/Learn/{id}?lesson= — ক্লাস দেখা, অ্যাসাইনমেন্ট, কুইজ, অগ্রগতি */
    public function Learn(): void
    {
        $me = $this->me();
        $c = $this->db->GetCourse((int)input_int('id', 0));
        if (!$c) not_found('কোর্সটি পাওয়া যায়নি।');
        $manager = $this->canManage($c);
        $enr = $this->db->GetEnrollment($c->Id, $me);
        if (!$enr && !$manager) redirect('/Course/Details/' . $c->Id);
        $lessons = $this->db->GetLessons($c->Id);
        $processing = $this->db->SyncLessonRecordings($lessons);
        $lid = (int)input_int('lesson', 0);
        $cur = null;
        foreach ($lessons as $l) if ($l->Id === $lid) $cur = $l;
        if (!$cur && $lessons) { $done = array_flip($this->db->GetDoneLessonIds($c->Id, $me)); foreach ($lessons as $l) if (!isset($done[$l->Id]) && !$l->Locked) { $cur = $l; break; } $cur ??= $lessons[0]; }
        $this->view(null, $c, ['Lessons' => $lessons, 'Cur' => $cur, 'Tasks' => $this->db->GetTasks($c->Id), 'Subs' => $this->db->GetUserSubmissions($c->Id, $me),
            'Done' => $this->db->GetDoneLessonIds($c->Id, $me), 'Progress' => $this->db->GetCourseProgress($c->Id, $me), 'Enrollment' => $enr, 'Manager' => $manager,
            'Processing' => $processing]);
    }

    private function learner(int $courseId): array
    {
        $me = $this->me(true);
        $c = $this->db->GetCourse($courseId);
        if (!$c || !$this->db->IsEnrolled($c->Id, $me)) $this->fail('এই কোর্সে আপনি ভর্তি নন।');
        return [$me, $c];
    }

    /** নতুন সার্টিফিকেট দেওয়া হলে নোটিফিকেশন — কোড ফেরত */
    private function afterProgress(Obj $c, int $me): ?string
    {
        $code = $this->db->CheckCertificate($c->Id, $me);
        if ($code) {
            try {
                $this->db->CreateNotification($me, 'অভিনন্দন! সার্টিফিকেট পেয়েছেন 🎉', "\"{$c->Title}\" কোর্স সম্পূর্ণ করেছেন — সার্টিফিকেট ডাউনলোড করুন।", 'success', '/Course/Certificate/' . $code, 'fa-award');
                $this->db->CreateNotification((int)$c->TeacherId, 'কোর্স সম্পূর্ণ 🎓', (session_get('UserFullName') ?: 'একজন শিক্ষার্থী') . " \"{$c->Title}\" কোর্স শেষ করে সার্টিফিকেট পেয়েছেন।", 'success', '/Course/Students/' . $c->Id, 'fa-award');
            } catch (Throwable $e) { }
        }
        return $code;
    }

    /** যে ক্লাস এখনো খোলেনি তার কাজ জমা দেওয়া যাবে না */
    private function taskOpen(Obj $t): void
    {
        if (!$t->LessonId) return;
        $l = $this->db->GetLesson($t->LessonId);
        if ($l && $l->Locked) $this->fail('এই ক্লাসটা এখনো খোলেনি — খুললে কাজটা জমা দিতে পারবেন।');
    }

    /** POST /Course/Done (lessonId) — ক্লাস শেষ হিসেবে চিহ্নিত */
    public function Done_POST(): void
    {
        $l = $this->db->GetLesson((int)input_int('lessonId', 0));
        if (!$l) $this->fail('ক্লাসটি পাওয়া যায়নি।');
        [$me, $c] = $this->learner($l->CourseId);
        if ($l->Kind === 'recorded' && $l->Locked) $this->fail('ক্লাসটি এখনো খোলেনি।');
        if ($l->Kind === 'live' && $l->LiveState === 'upcoming') $this->fail('লাইভ ক্লাসটি এখনো হয়নি।');
        $this->db->MarkLessonDone($c->Id, $l->Id, $me);
        $code = $this->afterProgress($c, $me);
        json_response(['success' => true, 'progress' => $this->db->GetCourseProgress($c->Id, $me), 'cert' => $code]);
    }

    /** POST /Course/Submit (taskId, link, note) — অ্যাসাইনমেন্ট জমা (Google Drive / GitHub / যেকোনো লিংক) */
    public function Submit_POST(): void
    {
        $t = $this->db->GetTask((int)input_int('taskId', 0));
        if (!$t || $t->Kind !== 'assignment') $this->fail('অ্যাসাইনমেন্টটি পাওয়া যায়নি।');
        [$me, $c] = $this->learner($t->CourseId);
        $this->taskOpen($t);
        $link = trim((string)input_str('link', ''));
        if (!preg_match('~^https?://[^\s<>"]{4,}$~i', $link) || filter_var($link, FILTER_VALIDATE_URL) === false || mb_strlen($link) > 500) $this->fail('সঠিক লিংক দিন (https:// দিয়ে শুরু) — যেমন Google Drive বা GitHub লিংক।', ['field' => 'link']);
        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 1000);
        $old = $this->db->GetSubmission($t->Id, $me);
        $this->db->SaveSubmission($t, $me, ['Link' => $link, 'Note' => $note !== '' ? $note : null]);
        try { $this->db->CreateNotification((int)$c->TeacherId, $old ? 'অ্যাসাইনমেন্ট আবার জমা 📎' : 'নতুন অ্যাসাইনমেন্ট জমা 📎', (session_get('UserFullName') ?: 'একজন শিক্ষার্থী') . " \"{$t->Title}\" জমা দিয়েছেন ({$c->Title})।", 'info', '/Course/Students/' . $c->Id . '#asg', 'fa-paperclip'); } catch (Throwable $e) { }
        $code = $this->afterProgress($c, $me);
        json_response(['success' => true, 'message' => 'জমা হয়েছে! টিচার দেখে নম্বর / মন্তব্য দেবেন।', 'progress' => $this->db->GetCourseProgress($c->Id, $me), 'cert' => $code]);
    }

    /** POST /Course/Quiz (taskId, answers = JSON [index...]) — নম্বর সার্ভারে হিসাব হয় */
    public function Quiz_POST(): void
    {
        $t = $this->db->GetTask((int)input_int('taskId', 0));
        if (!$t || $t->Kind !== 'quiz' || !$t->QuestionList) $this->fail('কুইজটি পাওয়া যায়নি।');
        [$me, $c] = $this->learner($t->CourseId);
        $this->taskOpen($t);
        $ans = json_decode((string)input_str('answers', '[]'), true);
        if (!is_array($ans) || count($ans) !== count($t->QuestionList)) $this->fail('সব প্রশ্নের উত্তর দিন।');
        $score = 0; $result = [];
        foreach ($t->QuestionList as $i => $q) {
            $a = $ans[$i];
            if (!is_int($a) && !ctype_digit((string)$a)) $this->fail('সব প্রশ্নের উত্তর দিন।');
            $ok = (int)$a === (int)$q['a'];
            if ($ok) $score++;
            $result[] = ['ok' => $ok, 'correct' => (int)$q['a'], 'given' => (int)$a];
        }
        $total = count($t->QuestionList);
        $need = $t->PassPercent ?? $c->PassPercent;
        $passed = $total > 0 && ($score * 100 / $total) >= $need;
        // ফেল করলে সঠিক উত্তর দেখানো হয় না (শুধু কোনগুলো ভুল) — না হলে পরের বার না পড়েই পাস করা যেত
        if (!$passed) foreach ($result as &$r) $r['correct'] = $r['ok'] ? $r['given'] : -1;
        unset($r);
        $this->db->SaveSubmission($t, $me, ['Answers' => array_map('intval', $ans), 'Score' => $score, 'Total' => $total, 'Passed' => $passed]);
        $code = $this->afterProgress($c, $me);
        json_response(['success' => true, 'score' => $score, 'total' => $total, 'passed' => $passed, 'need' => $need, 'result' => $result,
            'message' => $passed ? "পাস করেছেন! {$score}/{$total}" : "{$score}/{$total} — পাস করতে {$need}% লাগবে, আবার চেষ্টা করুন।",
            'progress' => $this->db->GetCourseProgress($c->Id, $me), 'cert' => $code]);
    }

    // ================================================================ টিচার / অ্যাডমিন
    private function requireCreator(): int
    {
        $me = $this->me();
        if (current_role() !== 'Teacher' && !$this->isCourseAdmin()) {
            tempdata_set('Error', 'কোর্স বানাতে পারেন শুধু টিচাররা — আগে টিচার হিসেবে যুক্ত হোন।');
            redirect('/Package?for=teacher');
        }
        return $me;
    }

    /** GET /Course/Manage — আমার কোর্সগুলো + আয়ের সারাংশ */
    public function Manage(): void
    {
        $me = $this->requireCreator();
        $this->view(null, $this->db->GetTeacherCourses($me), ['Earn' => $this->db->GetTeacherCourseEarnings($me)]);
    }

    /** GET /Course/Edit/{id?} */
    public function Edit(): void
    {
        $this->requireCreator();
        $id = (int)input_int('id', 0);
        $c = $id ? $this->manageable($id, false) : null;
        $this->view(null, $c, ['Levels' => self::LEVELS, 'Cats' => $this->db->GetCourseCategories()]);
    }

    /** POST /Course/Save (id, title, subtitle, description(html), includes, category, level, price, regularPrice, passPercent, thumbnail(file)) */
    public function Save_POST(): void
    {
        $me = $this->me(true);
        if (current_role() !== 'Teacher' && !$this->isCourseAdmin()) $this->fail('কোর্স বানাতে পারেন শুধু টিচাররা।');
        $id = (int)input_int('id', 0);
        $c = $id ? $this->manageable($id) : null;
        $clean = fn($k, $n) => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str($k, '')))), 0, $n);
        $title = $clean('title', 150);
        if (mb_strlen($title) < 3) $this->fail('কোর্সের নাম দিন (অন্তত ৩ অক্ষর)।', ['field' => 'title']);
        $desc = sanitize_rich_html((string)input_str('description', ''));
        if (strlen($desc) > 60000) $this->fail('বিবরণ অনেক বড় — একটু ছোট করুন।', ['field' => 'description']);
        $inc = array_slice(array_values(array_filter(array_map(fn($l) => mb_substr(trim(strip_tags($l)), 0, 140), preg_split('/\r?\n/', (string)input_str('includes', ''))))), 0, 25);
        $price = trim((string)input_str('price', '0'));
        if (!preg_match('/^\d{1,6}$/', $price)) $this->fail('সঠিক দাম দিন (শুধু সংখ্যা, ফ্রি হলে 0)।', ['field' => 'price']);
        $reg = trim((string)input_str('regularPrice', ''));
        if ($reg !== '' && (!preg_match('/^\d{1,6}$/', $reg) || (int)$reg <= (int)$price)) $this->fail('আগের দাম বর্তমান দামের চেয়ে বেশি হবে (না হলে খালি রাখুন)।', ['field' => 'regularPrice']);
        $level = (string)input_str('level', self::LEVELS[0]);
        $data = ['Title' => $title, 'Subtitle' => $clean('subtitle', 250) ?: null, 'Description' => $desc ?: null, 'Includes' => $inc ? implode("\n", $inc) : null,
            'Category' => $clean('category', 60) ?: null, 'Level' => in_array($level, self::LEVELS, true) ? $level : self::LEVELS[0],
            'Price' => (int)$price, 'RegularPrice' => $reg === '' ? null : (int)$reg, 'PassPercent' => max(0, min(100, (int)input_int('passPercent', 60))),
            // আলাদা পেইড লাইভ ক্লাস বন্ধ — নতুন সবই "কোর্স"; আগের লাইভ ক্লাস যেমন ছিল তেমন থাকে (লাইভ ক্লাস এখন Premium প্যাকেজে চ্যাট থেকে)
            'Kind' => $c && $c->Kind === 'live' ? 'live' : 'course'];
        $file = upload_file('thumbnail');
        if ($file) {
            $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === '.jpeg') $ext = '.jpg';
            if (!in_array($ext, ['.jpg', '.png', '.webp'], true) || @getimagesize($file['tmp_name']) === false) $this->fail('থাম্বনেইল হিসেবে JPG, PNG বা WEBP ছবি দিন।', ['field' => 'thumbnail']);
            if ($file['size'] > 3 * 1024 * 1024) $this->fail('থাম্বনেইল ৩ MB এর কম হতে হবে।', ['field' => 'thumbnail']);
            $data['Thumbnail'] = save_upload($file, 'courses', 'c' . ($id ?: 'n') . '_' . ticks(), $ext);
            if ($c && $c->Thumbnail) delete_upload($c->Thumbnail);
        }
        $nid = $this->db->SaveCourse($id, $c ? (int)$c->TeacherId : $me, $data);
        json_response(['success' => true, 'id' => $nid, 'message' => $id ? 'সেভ হয়েছে।' : ($data['Kind'] === 'live' ? 'লাইভ ক্লাস তৈরি হয়েছে — এখন "লাইভ ক্লাস" যোগ করে তারিখ ও সময় দিন।' : 'কোর্স তৈরি হয়েছে — এখন ক্লাস যোগ করুন।'), 'redirect' => $id ? null : '/Course/Build/' . $nid]);
    }

    /** POST /Course/Publish (id, on) */
    public function Publish_POST(): void
    {
        $this->me(true);
        $c = $this->manageable((int)input_int('id', 0));
        $on = input_bool('on');
        if ($on && !$this->db->GetLessons($c->Id)) $this->fail('প্রকাশ করার আগে অন্তত একটা ক্লাস যোগ করুন।');
        // প্রকাশ ফি — অ্যাডমিন দাম ঠিক না করা পর্যন্ত টিচার প্রকাশ করতে পারবে না; দাম দিলে আগে ফি দিতে হয় (একবারই)
        if ($on && !$c->PublishPaid && ($c->TeacherRole ?? '') !== 'Admin') {
            $kl = $c->Kind === 'live' ? 'লাইভ ক্লাস' : 'কোর্স';
            $fee = course_publish_fee($c->Kind);
            if ($fee === null) $this->fail("{$kl} প্রকাশ করার সুবিধা এখনো চালু হয়নি — অ্যাডমিন প্রকাশ ফি ঠিক করলে তখন প্রকাশ করতে পারবেন। ততক্ষণ সব তৈরি করে Draft এ রাখুন।", ['locked' => true]);
            if ($fee === 0) $this->db->SetCoursePublishPaid($c->Id);
            else {
                if ((int)$this->db->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `CourseId` = :c AND `Audience` = 'Publish' AND `Status` = 'Pending'", ['c' => $c->Id]) > 0)
                    $this->fail("এই {$kl} এর প্রকাশ ফি এর পেমেন্ট যাচাই হচ্ছে — অ্যাডমিন Accept করলেই নিজে থেকে প্রকাশ হয়ে যাবে।");
                $this->fail("{$kl} প্রকাশ করতে একবার " . taka($fee) . ' প্রকাশ ফি দিতে হবে।', ['needPay' => '/Payment/Checkout?publish=' . $c->Id]);
            }
        }
        $this->db->SetCourseStatus($c->Id, $on ? 'published' : 'draft');
        json_response(['success' => true, 'message' => $on ? ($c->AdminHidden ? 'প্রকাশ করা হয়েছে — তবে অ্যাডমিন কোর্সটা লুকিয়ে রেখেছেন।' : 'কোর্স প্রকাশিত — এখন Course পেজে দেখাবে।') : 'কোর্স Draft এ নেওয়া হয়েছে — Course পেজে দেখাবে না।']);
    }

    /** POST /Course/Delete (id) — কেউ না কিনলে */
    public function Delete_POST(): void
    {
        $this->me(true);
        $c = $this->manageable((int)input_int('id', 0));
        if ($this->db->CountCourseEnrollments($c->Id) > 0) $this->fail('এই কোর্সে শিক্ষার্থী আছে — মোছা যাবে না। চাইলে Draft করে রাখুন।');
        if ($c->Thumbnail) delete_upload($c->Thumbnail);
        $this->db->DeleteCourse($c->Id);
        json_response(['success' => true, 'message' => 'কোর্স মুছে ফেলা হয়েছে।', 'redirect' => '/Course/Manage']);
    }

    /** GET /Course/Build/{id} — ক্লাস, অ্যাসাইনমেন্ট, কুইজ */
    public function Build(): void
    {
        $this->me();
        $c = $this->manageable((int)input_int('id', 0), false);
        $lessons = $this->db->GetLessons($c->Id);
        $proc = $this->db->SyncLessonRecordings($lessons, input_bool('sync'));
        $this->view(null, $c, ['Lessons' => $lessons, 'Tasks' => $this->db->GetTasks($c->Id), 'Processing' => $proc, 'Students' => $this->db->CountCourseEnrollments($c->Id),
            'BbbReady' => bbb_ready()]);
    }

    /** POST /Course/SaveLesson (courseId, id, kind, title, description, unlockAt, startsAt, duration) */
    public function SaveLesson_POST(): void
    {
        $me = $this->me(true);
        $c = $this->manageable((int)input_int('courseId', 0));
        $id = (int)input_int('id', 0);
        $old = $id ? $this->db->GetLesson($id) : null;
        if ($id && (!$old || $old->CourseId !== $c->Id)) $this->fail('ক্লাসটি পাওয়া যায়নি।');
        $kind = $old ? $old->Kind : ((string)input_str('kind', 'recorded') === 'live' ? 'live' : 'recorded');
        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('title', '')))), 0, 200);
        if (mb_strlen($title) < 2) $this->fail('ক্লাসের নাম দিন।', ['field' => 'title']);
        $desc = mb_substr(trim(strip_tags((string)input_str('description', ''))), 0, 3000);
        $unlock = self::dt((string)input_str('unlockAt', ''));
        $starts = self::dt((string)input_str('startsAt', ''));
        if ($unlock === 'bad') $this->fail('আনলকের তারিখ / সময় ঠিক নেই।', ['field' => 'unlockAt']);
        if ($starts === 'bad') $this->fail('লাইভ ক্লাসের তারিখ / সময় ঠিক নেই।', ['field' => 'startsAt']);
        if ($kind === 'live' && !$starts) $this->fail('লাইভ ক্লাস কবে হবে — তারিখ আর সময় দিন।', ['field' => 'startsAt']);
        $dur = max(15, min(300, (int)input_int('duration', 60)));
        $nid = $this->db->SaveLesson($id, $c->Id, ['Title' => $title, 'Description' => $desc, 'Kind' => $kind, 'UnlockAt' => $kind === 'recorded' ? $unlock : null,
            'StartsAt' => $kind === 'live' ? $starts : null, 'DurationMin' => $dur], $me);
        if (!$id) {
            $url = '/Course/Learn/' . $c->Id . '?lesson=' . $nid;
            if ($kind === 'live') $this->notifyStudents($c, 'নতুন লাইভ ক্লাস 📅', "\"{$c->Title}\" — \"$title\" লাইভ ক্লাস " . date('d M Y, h:i A', strtotime($starts)) . ' এ।', $url, 'fa-video');
            else $this->notifyStudents($c, 'নতুন ক্লাস যোগ হয়েছে 🎬', "\"{$c->Title}\" — \"$title\"" . ($unlock && strtotime($unlock) > time() ? ' খুলবে ' . date('d M Y, h:i A', strtotime($unlock)) . ' এ।' : '।'), $url, 'fa-circle-play');
        }
        json_response(['success' => true, 'id' => $nid, 'message' => $id ? 'ক্লাস আপডেট হয়েছে।' : ($kind === 'live' ? 'লাইভ ক্লাস যোগ হয়েছে — সময় হলে "লাইভ শুরু করুন" চাপুন।' : 'ক্লাস যোগ হয়েছে — এখন "রেকর্ড করুন" চেপে ক্লাসটা রেকর্ড করুন।')]);
    }

    private function myLesson(): array
    {
        $this->me(true);
        $l = $this->db->GetLesson((int)input_int('id', 0));
        if (!$l) $this->fail('ক্লাসটি পাওয়া যায়নি।');
        return [$l, $this->manageable($l->CourseId)];
    }

    public function DeleteLesson_POST(): void { [$l] = $this->myLesson(); $this->db->DeleteLesson($l->Id); json_response(['success' => true, 'message' => 'ক্লাস মুছে ফেলা হয়েছে।']); }
    public function MoveLesson_POST(): void { [$l, $c] = $this->myLesson(); $this->db->MoveLesson($c->Id, $l->Id, (int)input_int('dir', 1)); json_response(['success' => true]); }
    public function Rerecord_POST(): void { [$l] = $this->myLesson(); $this->db->ResetLessonRecording($l->Id); json_response(['success' => true, 'message' => 'আগের রেকর্ডিং বাদ — এখন আবার "রেকর্ড করুন" চাপুন।']); }

    /** POST /Course/SaveTask (courseId, id, lessonId, kind, title, instructions, dueAt, maxMarks, passPercent, questions JSON) */
    public function SaveTask_POST(): void
    {
        $this->me(true);
        $c = $this->manageable((int)input_int('courseId', 0));
        $id = (int)input_int('id', 0);
        $old = $id ? $this->db->GetTask($id) : null;
        if ($id && (!$old || $old->CourseId !== $c->Id)) $this->fail('কাজটি পাওয়া যায়নি।');
        $kind = $old ? $old->Kind : ((string)input_str('kind', 'assignment') === 'quiz' ? 'quiz' : 'assignment');
        $lid = (int)input_int('lessonId', 0);
        if ($lid) { $l = $this->db->GetLesson($lid); if (!$l || $l->CourseId !== $c->Id) $this->fail('ক্লাসটি পাওয়া যায়নি।'); }
        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('title', '')))), 0, 200);
        if (mb_strlen($title) < 2) $this->fail('শিরোনাম দিন।', ['field' => 'title']);
        $due = self::dt((string)input_str('dueAt', ''));
        if ($due === 'bad') $this->fail('জমার শেষ সময় ঠিক নেই।', ['field' => 'dueAt']);
        $qs = [];
        if ($kind === 'quiz') {
            $raw = json_decode((string)input_str('questions', '[]'), true);
            if (!is_array($raw) || !$raw) $this->fail('অন্তত একটা প্রশ্ন দিন।');
            if (count($raw) > 50) $this->fail('একটা কুইজে সর্বোচ্চ ৫০টা প্রশ্ন।');
            foreach ($raw as $i => $q) {
                $qt = mb_substr(trim(strip_tags((string)($q['q'] ?? ''))), 0, 500);
                $opts = array_values(array_filter(array_map(fn($o) => mb_substr(trim(strip_tags((string)$o)), 0, 200), (array)($q['o'] ?? [])), fn($o) => $o !== ''));
                $a = (int)($q['a'] ?? -1);
                if ($qt === '' || count($opts) < 2 || count($opts) > 6) $this->fail('প্রশ্ন ' . ($i + 1) . ': প্রশ্ন আর ২–৬টা উত্তর দিন।');
                if ($a < 0 || $a >= count($opts)) $this->fail('প্রশ্ন ' . ($i + 1) . ': সঠিক উত্তর বাছাই করুন।');
                $qs[] = ['q' => $qt, 'o' => $opts, 'a' => $a];
            }
        }
        $pp = trim((string)input_str('passPercent', ''));
        $nid = $this->db->SaveTask($id, $c->Id, ['LessonId' => $lid ?: null, 'Kind' => $kind, 'Title' => $title, 'Instructions' => mb_substr(trim(strip_tags((string)input_str('instructions', ''))), 0, 3000),
            'DueAt' => $due, 'Questions' => $qs, 'PassPercent' => $pp === '' ? null : max(0, min(100, (int)$pp)), 'MaxMarks' => max(1, min(1000, (int)input_int('maxMarks', 10)))]);
        if (!$id) $this->notifyStudents($c, $kind === 'quiz' ? 'নতুন কুইজ 📝' : 'নতুন অ্যাসাইনমেন্ট 📎', "\"{$c->Title}\" — \"$title\"" . ($due ? ' · জমার শেষ সময় ' . date('d M Y, h:i A', strtotime($due)) : ''), '/Course/Learn/' . $c->Id . ($lid ? '?lesson=' . $lid : ''), $kind === 'quiz' ? 'fa-list-check' : 'fa-paperclip');
        json_response(['success' => true, 'id' => $nid, 'message' => $id ? 'আপডেট হয়েছে।' : ($kind === 'quiz' ? 'কুইজ যোগ হয়েছে।' : 'অ্যাসাইনমেন্ট যোগ হয়েছে।')]);
    }

    public function DeleteTask_POST(): void
    {
        $this->me(true);
        $t = $this->db->GetTask((int)input_int('id', 0));
        if (!$t) $this->fail('কাজটি পাওয়া যায়নি।');
        $this->manageable($t->CourseId);
        $this->db->DeleteTask($t->Id);
        json_response(['success' => true, 'message' => 'মুছে ফেলা হয়েছে।']);
    }

    /** GET /Course/Students/{id} — শিক্ষার্থীদের অগ্রগতি + জমা দেখা / নম্বর দেওয়া */
    public function Students(): void
    {
        $this->me();
        $c = $this->manageable((int)input_int('id', 0), false);
        $this->view(null, $c, ['Students' => $this->db->GetCourseStudents($c->Id), 'Subs' => $this->db->GetCourseSubmissions($c->Id), 'Tasks' => $this->db->GetTasks($c->Id)]);
    }

    /** POST /Course/Review (id, marks, feedback) */
    public function Review_POST(): void
    {
        $this->me(true);
        $s = $this->db->one('SELECT s.*, t.`Title` AS TaskTitle, t.`MaxMarks` FROM `CourseSubmissions` s JOIN `CourseTasks` t ON t.`Id` = s.`TaskId` WHERE s.`Id` = :i', ['i' => (int)input_int('id', 0)]);
        if (!$s) $this->fail('জমাটি পাওয়া যায়নি।');
        $c = $this->manageable((int)$s->CourseId);
        $mk = trim((string)input_str('marks', ''));
        if ($mk !== '' && (!preg_match('/^\d{1,4}$/', $mk) || (int)$mk > (int)$s->MaxMarks)) $this->fail('নম্বর ০ থেকে ' . (int)$s->MaxMarks . ' এর মধ্যে দিন।');
        $fb = mb_substr(trim(strip_tags((string)input_str('feedback', ''))), 0, 500);
        $this->db->ReviewSubmission((int)$s->Id, $mk === '' ? null : (int)$mk, $fb);
        try { $this->db->CreateNotification((int)$s->UserId, 'অ্যাসাইনমেন্ট দেখা হয়েছে ✅', "\"{$s->TaskTitle}\"" . ($mk !== '' ? " — নম্বর: $mk/" . (int)$s->MaxMarks : '') . ($fb !== '' ? " · মন্তব্য: " . str_preview($fb, 60) : ''), 'success', '/Course/Learn/' . $c->Id, 'fa-check-double'); } catch (Throwable $e) { }
        json_response(['success' => true, 'message' => 'সেভ হয়েছে — শিক্ষার্থী নোটিফিকেশন পাবে।']);
    }

    /** GET /Course/Earnings — কোর্স বিক্রির হিসাব (টিচারের ভাগ, পেয়েছে, বাকি) */
    public function Earnings(): void
    {
        $me = $this->requireCreator();
        $this->view(null, $this->db->GetTeacherCourseEarnings($me), ['Pct' => course_admin_pct()]);
    }
}
