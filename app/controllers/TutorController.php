<?php
/**
 * টিচার প্রোফাইলের ড্যাশবোর্ড অংশ (সব JSON, সব POST এ CSRF লাগে)।
 *
 *  POST /Tutor/SaveIntro          (headline, levels, bio, city, subjects, quote)
 *  POST /Tutor/SaveStudent        (id?, name, classLevel, subject, phone, monthlyFee, status, note)
 *  POST /Tutor/DeleteStudent      (id)
 *  POST /Tutor/SaveClass          (id?, title, days[] অথবা classDate, startTime, endTime, meetLink, location)
 *  POST /Tutor/DeleteClass        (id)
 *  POST /Tutor/SavePayment        (id?, studentId, amount, month, paidOn, note)
 *  POST /Tutor/DeletePayment      (id)
 *  POST /Tutor/SaveCertificate    (title, issuer, year, file?)
 *  POST /Tutor/DeleteCertificate  (id)
 *  POST /Tutor/SaveReview         (teacherId, rating, comment) — স্টুডেন্ট রিভিউ দেয় / বদলায়
 *  POST /Tutor/DeleteReview       (id) — নিজের রিভিউ, অথবা অ্যাডমিন
 */
class TutorController extends Controller
{
    private function fail(string $msg): void { json_response(['success' => false, 'message' => $msg]); }

    /** লগইন করা টিচার (ডাটাবেস থেকে যাচাই) */
    private function teacher(): int
    {
        $me = current_user_id();
        if ($me === null) $this->fail('আগে লগইন করুন।');
        $u = $this->db->GetUserById($me);
        if (!$u || $u->Role !== 'Teacher') $this->fail('এই অপশন শুধু টিচারদের জন্য।');
        return $me;
    }

    private function clean(string $k, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str($k, ''))), 0, $max);
    }

    /** কমা দিয়ে আলাদা তালিকা → ইউনিক অ্যারে */
    private function list(string $k, int $maxItems, int $maxLen): array
    {
        $out = [];
        foreach (explode(',', (string)input_str($k, '')) as $x) {
            $x = mb_substr(trim(preg_replace('/\s+/u', ' ', $x)), 0, $maxLen);
            if ($x !== '' && !in_array(mb_strtolower($x), array_map('mb_strtolower', $out), true)) $out[] = $x;
        }
        if (count($out) > $maxItems) $this->fail("সর্বোচ্চ {$maxItems}টা দেওয়া যায়।");
        return $out;
    }

    private function idInput(): ?int { $id = input_int('id', 0) ?? 0; return $id > 0 ? $id : null; }

    private function ownOrFail(string $table, ?int $id, int $me): void
    {
        if ($id !== null && !$this->db->OwnsRow($table, $id, $me)) $this->fail('এটি পাওয়া যায়নি।');
    }

    // ============================================================
    /** POST /Tutor/SaveIntro */
    public function SaveIntro_POST(): void
    {
        $me = $this->teacher();
        $headline = $this->clean('headline', 60);
        $levels = $this->list('levels', 10, 30);
        $subjects = $this->list('subjects', 12, 30);
        $fee = function (string $k): ?int { $v = trim((string)input_str($k, '')); return $v === '' ? null : max(0, min(1000000, (int)$v)); };
        $min = $fee('feeMin'); $max = $fee('feeMax');
        if ($min !== null && $max !== null && $max < $min) $this->fail('সর্বোচ্চ বেতন সর্বনিম্নের চেয়ে কম হতে পারে না।');
        if ($min === null && $max !== null) { $min = $max; $max = null; }
        $this->db->SaveIntro($me, $this->clean('bio', 160), $this->clean('city', 80), $subjects, $this->clean('quote', 160));
        $this->db->SaveTeacherIntro($me, $headline, $levels);
        $this->db->SaveTeachPrefs($me, (string)input_str('teachMode', ''), $min, $max);
        json_response(['success' => true, 'message' => 'প্রোফাইল আপডেট হয়েছে।']);
    }

    // ---------------- Students ----------------
    /** POST /Tutor/SaveStudent */
    public function SaveStudent_POST(): void
    {
        $me = $this->teacher();
        $id = $this->idInput();
        $this->ownOrFail('TeacherStudents', $id, $me);
        $name = $this->clean('name', 80);
        if ($name === '') $this->fail('স্টুডেন্টের নাম লিখুন।');
        $phone = preg_replace('/[^0-9+]/', '', (string)input_str('phone', ''));
        if ($phone !== '' && !preg_match('/^\+?\d{6,15}$/', $phone)) $this->fail('মোবাইল নম্বর সঠিক নয়।');
        $feeRaw = trim((string)input_str('monthlyFee', ''));
        $fee = $feeRaw === '' ? null : (int)$feeRaw;
        if ($fee !== null && ($fee < 0 || $fee > 1000000)) $this->fail('মাসিক বেতন সঠিক নয়।');
        if ($id === null && $this->db->CountTeacherRows('TeacherStudents', $me) >= 300) $this->fail('সর্বোচ্চ ৩০০ জন স্টুডেন্ট রাখা যায়।');
        $this->db->SaveTeacherStudent($me, $id, [
            'Name' => $name, 'ClassLevel' => $this->clean('classLevel', 60), 'Subject' => $this->clean('subject', 80), 'Phone' => $phone,
            'MonthlyFee' => $fee, 'Status' => input_str('status', 'Active') === 'Inactive' ? 'Inactive' : 'Active', 'Note' => $this->clean('note', 200),
        ]);
        json_response(['success' => true, 'message' => $id ? 'স্টুডেন্টের তথ্য আপডেট হয়েছে।' : 'স্টুডেন্ট যোগ হয়েছে।']);
    }

    /** POST /Tutor/DeleteStudent */
    public function DeleteStudent_POST(): void
    {
        $me = $this->teacher();
        if (!$this->db->DeleteTeacherRow('TeacherStudents', (int)$this->idInput(), $me)) $this->fail('স্টুডেন্ট পাওয়া যায়নি।');
        json_response(['success' => true, 'message' => 'তালিকা থেকে সরানো হয়েছে।']);
    }

    // ---------------- Schedule ----------------
    /** POST /Tutor/SaveClass */
    public function SaveClass_POST(): void
    {
        $me = $this->teacher();
        $id = $this->idInput();
        $this->ownOrFail('TeacherClasses', $id, $me);
        $title = $this->clean('title', 100);
        if ($title === '') $this->fail('ক্লাসের নাম লিখুন (যেমন: Class 9 - Mathematics)।');
        $days = $_POST['days'] ?? [];
        if (!is_array($days)) $days = explode(',', (string)$days);
        $days = array_values(array_unique(array_filter(array_map('intval', array_filter($days, fn($d) => $d !== '' && is_numeric($d))), fn($d) => $d >= 0 && $d <= 6)));
        sort($days);
        $date = trim((string)input_str('classDate', ''));
        if ($date !== '') {
            $dt = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$dt || $dt->format('Y-m-d') !== $date) $this->fail('তারিখ সঠিক নয়।');
            $days = [];
        } else {
            $date = null;
            if (!$days) $this->fail('সপ্তাহের কোন কোন দিন ক্লাস, অথবা একটা নির্দিষ্ট তারিখ দিন।');
        }
        $tm = fn($k) => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v = trim((string)input_str($k, ''))) ? $v : null;
        $start = $tm('startTime'); $end = $tm('endTime');
        if (!$start || !$end) $this->fail('শুরু ও শেষের সময় দিন।');
        if ($end <= $start) $this->fail('শেষের সময় শুরুর পরে হতে হবে।');
        $link = trim((string)input_str('meetLink', ''));
        if ($link !== '' && !preg_match('#^https://[^\s<>"\']+$#i', $link)) $this->fail('ক্লাস লিংক https:// দিয়ে শুরু হতে হবে (যেমন Google Meet / Zoom লিংক)।');
        if ($id === null && $this->db->CountTeacherRows('TeacherClasses', $me) >= 100) $this->fail('সর্বোচ্চ ১০০টা ক্লাস রাখা যায়।');
        $this->db->SaveTeacherClass($me, $id, ['Title' => $title, 'Days' => $days, 'ClassDate' => $date, 'StartTime' => $start, 'EndTime' => $end,
            'MeetLink' => mb_substr($link, 0, 300), 'Location' => $this->clean('location', 100)]);
        json_response(['success' => true, 'message' => $id ? 'ক্লাস আপডেট হয়েছে।' : 'রুটিনে ক্লাস যোগ হয়েছে।']);
    }

    /** POST /Tutor/DeleteClass */
    public function DeleteClass_POST(): void
    {
        $me = $this->teacher();
        if (!$this->db->DeleteTeacherRow('TeacherClasses', (int)$this->idInput(), $me)) $this->fail('ক্লাস পাওয়া যায়নি।');
        json_response(['success' => true, 'message' => 'ক্লাস মুছে ফেলা হয়েছে।']);
    }

    // ---------------- Earnings ----------------
    /** POST /Tutor/SavePayment */
    public function SavePayment_POST(): void
    {
        $me = $this->teacher();
        $id = $this->idInput();
        $this->ownOrFail('TeacherPayments', $id, $me);
        $amount = input_int('amount', 0) ?? 0;
        if ($amount <= 0 || $amount > 10000000) $this->fail('টাকার পরিমাণ সঠিকভাবে লিখুন।');
        $month = trim((string)input_str('month', date('Y-m')));
        if (!preg_match('/^(20\d\d)-(0[1-9]|1[0-2])$/', $month)) $this->fail('মাস সঠিক নয়।');
        $sid = input_int('studentId', 0) ?: null;
        if ($sid !== null && !$this->db->OwnsRow('TeacherStudents', $sid, $me)) $this->fail('স্টুডেন্ট পাওয়া যায়নি।');
        $paid = trim((string)input_str('paidOn', ''));
        if ($paid !== '') { $d = DateTime::createFromFormat('!Y-m-d', $paid); if (!$d || $d->format('Y-m-d') !== $paid) $this->fail('তারিখ সঠিক নয়।'); } else $paid = null;
        if ($id === null && $this->db->CountTeacherRows('TeacherPayments', $me) >= 5000) $this->fail('অনেক বেশি এন্ট্রি হয়ে গেছে।');
        $this->db->SaveTeacherPayment($me, $id, $sid, $amount, $month, $paid, $this->clean('note', 150));
        json_response(['success' => true, 'message' => $id ? 'আপডেট হয়েছে।' : 'আয় যোগ হয়েছে।']);
    }

    /** POST /Tutor/DeletePayment */
    public function DeletePayment_POST(): void
    {
        $me = $this->teacher();
        if (!$this->db->DeleteTeacherRow('TeacherPayments', (int)$this->idInput(), $me)) $this->fail('এন্ট্রি পাওয়া যায়নি।');
        json_response(['success' => true, 'message' => 'মুছে ফেলা হয়েছে।']);
    }

    // ---------------- Certificates ----------------
    /** POST /Tutor/SaveCertificate */
    public function SaveCertificate_POST(): void
    {
        $me = $this->teacher();
        $title = $this->clean('title', 120);
        if ($title === '') $this->fail('সার্টিফিকেটের নাম লিখুন।');
        $year = $this->clean('year', 10);
        if ($year !== '' && !preg_match('/^(19|20)\d\d$/', $year)) $this->fail('সাল সঠিক নয় (যেমন 2023)।');
        if ($this->db->CountTeacherRows('TeacherCertificates', $me) >= 30) $this->fail('সর্বোচ্চ ৩০টা সার্টিফিকেট রাখা যায়।');
        $url = null;
        $file = upload_file('file');
        if ($file) {
            $ext = strtolower('.' . pathinfo((string)$file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['.jpg', '.jpeg', '.png', '.webp', '.pdf'], true)) $this->fail('শুধু ছবি (JPG/PNG/WEBP) বা PDF দেওয়া যাবে।');
            if ($file['size'] > 5 * 1024 * 1024) $this->fail('ফাইল ৫ MB এর কম হতে হবে।');
            if ($ext === '.pdf') { if ((string)@file_get_contents($file['tmp_name'], false, null, 0, 5) !== '%PDF-') $this->fail('ফাইলটি সঠিক PDF নয়।'); }
            elseif (@getimagesize($file['tmp_name']) === false) $this->fail('ছবিটি সঠিক নয়।');
            $url = save_upload($file, 'certificates', $me . '_' . ticks() . '_' . bin2hex(random_bytes(3)), $ext);
        }
        $this->db->AddTeacherCertificate($me, $title, $this->clean('issuer', 120), $year, $url);
        json_response(['success' => true, 'message' => 'সার্টিফিকেট যোগ হয়েছে।']);
    }

    /** POST /Tutor/DeleteCertificate */
    public function DeleteCertificate_POST(): void
    {
        $me = $this->teacher();
        $c = $this->db->GetTeacherCertificate((int)$this->idInput());
        if (!$c || (int)$c->TeacherId !== $me) $this->fail('সার্টিফিকেট পাওয়া যায়নি।');
        $this->db->DeleteTeacherRow('TeacherCertificates', (int)$c->Id, $me);
        delete_upload($c->FileUrl);
        json_response(['success' => true, 'message' => 'মুছে ফেলা হয়েছে।']);
    }

    // ---------------- Reviews ----------------
    /** POST /Tutor/SaveReview */
    public function SaveReview_POST(): void
    {
        $me = current_user_id();
        if ($me === null) $this->fail('আগে লগইন করুন।');
        if (current_role() === 'Admin') $this->fail('অ্যাডমিন রিভিউ দিতে পারেন না।');
        $tid = input_int('teacherId', 0) ?? 0;
        $t = $this->db->GetUserById($tid);
        if (!$t || $t->Role !== 'Teacher') $this->fail('টিচার পাওয়া যায়নি।');
        if (!$this->db->CanReviewTeacher($me, $tid)) $this->fail('শুধু এই টিচারের কাছে পড়েছেন (অ্যাপ্লিকেশন Accept করেছেন) এমন স্টুডেন্টরা রিভিউ দিতে পারেন।');
        $rating = input_int('rating', 0) ?? 0;
        if ($rating < 1 || $rating > 5) $this->fail('১ থেকে ৫ স্টার বাছাই করুন।');
        $comment = trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', (string)input_str('comment', ''))));
        if (mb_strlen($comment) > 1000) $this->fail('রিভিউ সর্বোচ্চ ১০০০ অক্ষর।');
        $isNew = $this->db->GetMyTeacherReview($tid, $me) === null;
        $this->db->SaveTeacherReview($tid, $me, $rating, $comment);
        if ($isNew) {
            $who = session_get('UserFullName') ?? session_get('UserName') ?? 'একজন স্টুডেন্ট';
            $this->db->CreateNotification($tid, 'New Review ' . str_repeat('⭐', $rating), "$who আপনাকে $rating স্টার রিভিউ দিয়েছেন।", 'success', '/Profile/Show/' . $tid . '?tab=reviews', 'fa-star');
        }
        json_response(['success' => true, 'message' => $isNew ? 'রিভিউ দেওয়ার জন্য ধন্যবাদ!' : 'রিভিউ আপডেট হয়েছে।']);
    }

    /** POST /Tutor/DeleteReview */
    public function DeleteReview_POST(): void
    {
        $me = current_user_id();
        if ($me === null) $this->fail('আগে লগইন করুন।');
        $ok = $this->db->DeleteTeacherReview((int)$this->idInput(), current_role() === 'Admin' ? null : $me);
        if (!$ok) $this->fail('রিভিউ পাওয়া যায়নি।');
        json_response(['success' => true, 'message' => 'রিভিউ মুছে ফেলা হয়েছে।']);
    }
}
