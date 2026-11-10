<?php
/**
 * Student Verification (পরিচয় যাচাই)
 *  GET  /Kyc                → নিজের রোল অনুযায়ী Student / Teacher ফর্মে পাঠায়
 *  GET  /Kyc/Student        → শিক্ষার্থীর ফর্ম (Play থেকে University) / অবস্থা
 *  POST /Kyc/Student        → জমা (multipart, JSON উত্তর)
 *  GET  /Kyc/Teacher        → পুরোনো টিচারদের NID + WhatsApp (নতুন আবেদনকারীরা "Become a Tutor" ফর্মেই দেয়)
 *  POST /Kyc/Teacher
 *  GET  /Kyc/File/{id}?side=front|back → ID / NID এর ছবি — শুধু অ্যাডমিন (Student Verification অনুমতি) বা নিজে
 */
class KycController extends Controller
{
    private function fail(string $m, ?string $field = null): void { json_response(['success' => false, 'message' => $m] + ($field ? ['field' => $field] : [])); }

    private function me(bool $json = false): int
    {
        $id = current_user_id();
        if ($id === null) { if ($json) $this->fail('আগে লগইন করুন।'); redirect('/Account/Login?returnUrl=' . rawurlencode((string)($_SERVER['REQUEST_URI'] ?? '/Kyc'))); }
        return $id;
    }

    /** 01XXXXXXXXX — +88 / স্পেস / ড্যাশ বাদ দিয়ে; ভুল হলে null */
    public static function cleanMobile(string $v): ?string
    {
        $v = preg_replace('/[^\d]/', '', strtr(trim($v), ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']));
        if (str_starts_with($v, '880')) $v = substr($v, 2);
        return preg_match('/^01[3-9]\d{8}$/', $v) ? $v : null;
    }

    private static function txt(string $k, int $max = 120): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str($k, '')))), 0, $max);
    }

    public function Index(): void
    {
        $this->me();
        if (is_admin()) redirect('/Admin/Kyc');
        redirect(session_get('UserRole') === 'Teacher' ? '/Kyc/Teacher' : '/Kyc/Student');
    }

    // ============================================================ শিক্ষার্থী
    public function Student(): void
    {
        $me = $this->me();
        if (is_admin()) redirect('/Admin/Kyc');
        $this->view(null, $this->db->GetKyc($me, 'student'), ['Classes' => Db::KYC_CLASSES, 'Districts' => Db::KYC_DISTRICTS, 'Boards' => Db::KYC_BOARDS,
            'Edit' => input_bool('edit'), 'Next' => $this->safeNext()]);
    }

    private function safeNext(): string
    {
        $n = (string)input_str('next', '');
        return preg_match('#^/[A-Za-z0-9/_\-?=&%.]*$#', $n) && !str_starts_with($n, '//') ? $n : '';
    }

    public function Student_POST(): void
    {
        $me = $this->me(true);
        if (is_admin()) $this->fail('অ্যাডমিন অ্যাকাউন্টের Student Verification লাগে না।');
        $old = $this->db->GetKyc($me, 'student');
        if ($old && $old->Status === 'Pending') $this->fail('আপনার ফর্ম যাচাই হচ্ছে — অ্যাডমিন দেখার পর আবার জমা দিতে পারবেন।');
        if ($old && $old->Status === 'Approved') $this->fail('আপনার Student Verification আগেই Approve হয়েছে।');

        $d = [];
        $need = ['fullName' => ['আপনার পুরো নাম', 80], 'fatherName' => ['বাবার নাম', 80], 'motherName' => ['মায়ের নাম', 80],
            'permanentAddress' => ['গ্রামের বাড়ির ঠিকানা (স্থায়ী)', 200], 'presentAddress' => ['বর্তমান ঠিকানা', 200], 'institution' => ['স্কুল / কলেজ / বিশ্ববিদ্যালয়ের নাম', 120]];
        foreach ($need as $k => [$label, $max]) { $d[$k] = self::txt($k, $max); if (mb_strlen($d[$k]) < 2) $this->fail("$label দিন।", $k); }
        $d['district'] = (string)input_str('district', '');
        if (!in_array($d['district'], Db::KYC_DISTRICTS, true)) $this->fail('জেলা বাছাই করুন।', 'district');
        $d['classLevel'] = (string)input_str('classLevel', '');
        if (!isset(Db::KYC_CLASSES[$d['classLevel']])) $this->fail('কোন ক্লাসে পড়েন বাছাই করুন।', 'classLevel');
        $d['group'] = in_array($g = (string)input_str('group', ''), ['Science', 'Commerce', 'Arts', 'Other'], true) ? $g : '';

        // মোবাইল: বাবা / মায়ের অন্তত একটা বাধ্যতামূলক, নিজের ঐচ্ছিক
        foreach (['fatherMobile' => 'বাবার', 'motherMobile' => 'মায়ের', 'mobile' => 'নিজের'] as $k => $who) {
            $raw = trim((string)input_str($k, ''));
            $d[$k] = $raw === '' ? '' : (self::cleanMobile($raw) ?? $this->fail("$who মোবাইল নম্বর ঠিক নেই — 01XXXXXXXXX (১১ ডিজিট) লিখুন।", $k));
        }
        if ($d['fatherMobile'] === '' && $d['motherMobile'] === '') $this->fail('বাবা অথবা মায়ের মোবাইল নম্বর দিন।', 'fatherMobile');

        // SSC / HSC — ক্লাস অনুযায়ী; Running হলে সাল / রেজাল্ট লাগে না
        foreach (['ssc' => 'SSC', 'hsc' => 'HSC'] as $ex => $EX) {
            [$show, $runOk] = Db::kycExamRule($d['classLevel'], $ex);
            if (!$show) { $d[$ex] = null; continue; }
            $st = (string)input_str($ex . 'Status', '');
            if ($st === 'running') {
                if (!$runOk) $this->fail("আপনার ক্লাস অনুযায়ী $EX পাস করা থাকার কথা — $EX এর সাল আর রেজাল্ট দিন।", $ex);
                $d[$ex] = ['status' => 'running']; continue;
            }
            if ($st !== 'passed') $this->fail("$EX: পাস করেছেন নাকি এখন পড়ছেন (Running) বাছাই করুন।", $ex);
            $yr = (int)input_int($ex . 'Year', 0);
            if ($yr < 1980 || $yr > (int)date('Y')) $this->fail("$EX পাসের সাল ঠিক করে দিন।", $ex);
            $gpa = trim((string)input_str($ex . 'Gpa', ''));
            if (!preg_match('/^[0-5](\.\d{1,2})?$/', $gpa) || (float)$gpa > 5) $this->fail("$EX এর রেজাল্ট (GPA ০.০০–৫.০০) দিন — যেমন 4.50", $ex);
            $board = (string)input_str($ex . 'Board', '');
            $d[$ex] = ['status' => 'passed', 'year' => $yr, 'gpa' => number_format((float)$gpa, 2), 'board' => in_array($board, Db::KYC_BOARDS, true) ? $board : ''];
        }
        if (!empty($d['ssc']['year']) && !empty($d['hsc']['year']) && $d['hsc']['year'] <= $d['ssc']['year']) $this->fail('HSC এর সাল SSC এর পরে হওয়ার কথা — সাল দুটো দেখে দিন।', 'hsc');

        [$front, $back] = $this->images($me, 'stid', $old);
        $id = $this->db->SaveKyc($me, 'student', $d, $front, $back);
        if (!is_blank($d['mobile']) && is_blank($this->db->GetUserById($me)->MobileNumber ?? '')) { try { $this->db->exec('UPDATE `Users` SET `MobileNumber` = :m WHERE `Id` = :u', ['m' => $d['mobile'], 'u' => $me]); } catch (Throwable $e) { } }
        $who = (string)(session_get('UserFullName') ?: session_get('UserName'));
        foreach ($this->db->GetAdmins() as $a) { try { $this->db->CreateNotification((int)$a->Id, '🪪 নতুন Student Verification (শিক্ষার্থী)', "$who Student Verification ফর্ম জমা দিয়েছেন — যাচাই করে Approve করুন।", 'info', '/Admin/Kyc?k=student', 'fa-id-card'); } catch (Throwable $e) { } }
        json_response(['success' => true, 'id' => $id, 'message' => 'ফর্ম জমা হয়েছে! অ্যাডমিন যাচাই করে Approve করলে নোটিফিকেশন পাবেন।', 'redirect' => '/Kyc/Student' . (($n = $this->safeNext()) !== '' ? '?next=' . rawurlencode($n) : '')]);
    }

    /** সামনে + পেছনের ছবি — আবার জমা দেওয়ার সময় নতুন না দিলে আগেরটাই থাকে */
    private function images(int $me, string $tag, ?Obj $old): array
    {
        $out = [];
        foreach (['Front' => 'সামনের', 'Back' => 'পেছনের'] as $side => $bn) {
            $f = upload_file('file' . $side);
            $prop = 'File' . $side;
            if (!$f && $old && kyc_file_path($old->$prop)) { $out[] = (string)$old->$prop; continue; }
            $err = null;
            $name = kyc_store_image($f, $me, $tag . strtolower($side), $err);
            if (!$name) { foreach ($out as $n) if (!$old || ($n !== $old->FileFront && $n !== $old->FileBack)) kyc_delete_file($n); $this->fail("$bn ছবি: " . $err, 'file' . $side); }
            $out[] = $name;
        }
        return $out;
    }

    // ============================================================ টিচার (পুরোনো টিচার / আবার জমা)
    public function Teacher(): void
    {
        $me = $this->me();
        if (is_admin()) redirect('/Admin/Kyc?k=teacher');
        $this->view(null, $this->db->GetKyc($me, 'teacher'), ['User' => $this->db->GetUserById($me), 'Edit' => input_bool('edit')]);
    }

    /** NID + WhatsApp + মোবাইল যাচাই — RequestForm থেকেও ব্যবহার হয়। [data, front, back] */
    public static function readTeacherKyc(Db $db, int $me, ?Obj $old, callable $fail): array
    {
        $d = [];
        $d['mobile'] = self::cleanMobile((string)input_str('kycMobile', '')) ?? $fail('মোবাইল নম্বর ঠিক নেই — 01XXXXXXXXX (১১ ডিজিট) লিখুন।', 'kycMobile');
        $d['whatsapp'] = self::cleanMobile((string)input_str('kycWhatsapp', '')) ?? $fail('WhatsApp নম্বর ঠিক নেই — 01XXXXXXXXX (১১ ডিজিট) লিখুন।', 'kycWhatsapp');
        $nid = preg_replace('/\D/', '', (string)input_str('kycNid', ''));
        if ($nid !== '' && !in_array(strlen($nid), [10, 13, 17], true)) $fail('NID নম্বর ১০, ১৩ বা ১৭ ডিজিটের হয় — দেখে লিখুন (না দিলেও চলবে)।', 'kycNid');
        $d['nid'] = $nid;
        $out = [];
        foreach (['Front' => 'NID এর সামনের', 'Back' => 'NID এর পেছনের'] as $side => $bn) {
            $f = upload_file('kycFile' . $side);
            $prop = 'File' . $side;
            if (!$f && $old && kyc_file_path($old->$prop)) { $out[] = (string)$old->$prop; continue; }
            $err = null;
            $name = kyc_store_image($f, $me, 'nid' . strtolower($side), $err);
            if (!$name) { foreach ($out as $n) if (!$old || ($n !== $old->FileFront && $n !== $old->FileBack)) kyc_delete_file($n); $fail("$bn ছবি: " . $err, 'kycFile' . $side); }
            $out[] = $name;
        }
        return [$d, $out[0], $out[1]];
    }

    public function Teacher_POST(): void
    {
        $me = $this->me(true);
        if (is_admin()) $this->fail('অ্যাডমিন অ্যাকাউন্টের Teacher Verification লাগে না।');
        $old = $this->db->GetKyc($me, 'teacher');
        if ($old && $old->Status === 'Pending') $this->fail('আপনার তথ্য যাচাই হচ্ছে — অ্যাডমিন দেখার পর আবার জমা দিতে পারবেন।');
        if ($old && $old->Status === 'Approved') $this->fail('আপনার Teacher Verification আগেই Approve হয়েছে।');
        [$d, $front, $back] = self::readTeacherKyc($this->db, $me, $old, fn($m, $f = null) => $this->fail($m, $f));
        // চালু টিচার আবেদন থাকলে তার সাথে জুড়ে দেওয়া (প্রত্যাখ্যাত Teacher Verification আবার জমা)
        $tr = $this->db->GetLatestTeacherRequest($me);
        $trId = $tr && in_array($tr->Status, ['Pending', 'AwaitingPayment'], true) ? (int)$tr->Id : null;
        $this->db->SaveKyc($me, 'teacher', $d, $front, $back, $trId);
        $who = (string)(session_get('UserFullName') ?: session_get('UserName'));
        foreach ($this->db->GetAdmins() as $a) { try { $this->db->CreateNotification((int)$a->Id, '🪪 নতুন Teacher Verification (টিচার)', "$who টিচারের Teacher Verification (NID + WhatsApp) জমা দিয়েছেন — যাচাই করে Approve করুন।", 'info', '/Admin/Kyc?k=teacher', 'fa-id-card'); } catch (Throwable $e) { } }
        json_response(['success' => true, 'message' => 'জমা হয়েছে! অ্যাডমিন যাচাই করে Approve করলে নোটিফিকেশন পাবেন।', 'redirect' => '/Kyc/Teacher']);
    }

    // ============================================================ ছবি দেখা (ব্যক্তিগত)
    public function File(): void
    {
        $me = $this->me();
        $k = $this->db->GetKycById((int)input_int('id', 0));
        if (!$k) not_found('পাওয়া যায়নি।');
        $ok = $k->UserId === $me || (is_admin() && admin_can(admin_action_rule('Kyc')));
        if (!$ok) not_found('পাওয়া যায়নি।');
        $path = kyc_file_path(input_str('side', 'front') === 'back' ? $k->FileBack : $k->FileFront);
        if (!$path) not_found('ছবিটা পাওয়া যায়নি।');
        $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        header('X-Robots-Tag: noindex');
        readfile($path);
        exit;
    }
}
