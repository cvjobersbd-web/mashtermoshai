<?php
/**
 * KYC — শিক্ষার্থী আর টিচারের পরিচয় যাচাই।
 *  - শিক্ষার্থী: নাম, বাবা-মা, মোবাইল, ঠিকানা, জেলা, ক্লাস, প্রতিষ্ঠান, SSC/HSC (রেজাল্ট বা Running), ID কার্ডের ছবি (সামনে + পেছনে)
 *  - টিচার: মোবাইল, WhatsApp, NID এর ছবি (সামনে + পেছনে) — "Become a Tutor" ফর্মের সাথে, বা পুরোনো টিচার আলাদা করে
 * ছবিগুলো পাবলিক uploads এ না — app/storage/kyc এ রাখা হয় (ওয়েব থেকে সরাসরি খোলা যায় না), শুধু অ্যাডমিন / নিজে দেখতে পারে।
 * প্রতিবার জমা দিলে নতুন সারি (আগেরগুলো ইতিহাস হিসেবে থাকে); সর্বশেষটাই চালু।
 */
trait KycData
{
    private bool $kycReady = false;

    public const KYC_CLASSES = [
        'play' => 'Play', 'nursery' => 'Nursery', 'kg' => 'KG',
        'c1' => 'Class 1', 'c2' => 'Class 2', 'c3' => 'Class 3', 'c4' => 'Class 4', 'c5' => 'Class 5', 'c6' => 'Class 6', 'c7' => 'Class 7', 'c8' => 'Class 8',
        'c9' => 'Class 9 (SSC)', 'c10' => 'Class 10 (SSC)', 'c11' => 'Class 11 (HSC ১ম বর্ষ)', 'c12' => 'Class 12 (HSC ২য় বর্ষ)',
        'diploma' => 'Diploma / Polytechnic', 'uni1' => 'অনার্স / ডিগ্রি ১ম বর্ষ', 'uni2' => 'অনার্স / ডিগ্রি ২য় বর্ষ', 'uni3' => 'অনার্স / ডিগ্রি ৩য় বর্ষ',
        'uni4' => 'অনার্স / ডিগ্রি ৪র্থ বর্ষ', 'masters' => 'মাস্টার্স',
    ];

    public const KYC_DISTRICTS = ['Bagerhat', 'Bandarban', 'Barguna', 'Barishal', 'Bhola', 'Bogura', 'Brahmanbaria', 'Chandpur', 'Chapainawabganj', 'Chattogram', 'Chuadanga',
        "Cox's Bazar", 'Cumilla', 'Dhaka', 'Dinajpur', 'Faridpur', 'Feni', 'Gaibandha', 'Gazipur', 'Gopalganj', 'Habiganj', 'Jamalpur', 'Jashore', 'Jhalokathi', 'Jhenaidah',
        'Joypurhat', 'Khagrachhari', 'Khulna', 'Kishoreganj', 'Kurigram', 'Kushtia', 'Lakshmipur', 'Lalmonirhat', 'Madaripur', 'Magura', 'Manikganj', 'Meherpur', 'Moulvibazar',
        'Munshiganj', 'Mymensingh', 'Naogaon', 'Narail', 'Narayanganj', 'Narsingdi', 'Natore', 'Netrokona', 'Nilphamari', 'Noakhali', 'Pabna', 'Panchagarh', 'Patuakhali',
        'Pirojpur', 'Rajbari', 'Rajshahi', 'Rangamati', 'Rangpur', 'Satkhira', 'Shariatpur', 'Sherpur', 'Sirajganj', 'Sunamganj', 'Sylhet', 'Tangail', 'Thakurgaon'];

    public const KYC_BOARDS = ['Dhaka', 'Rajshahi', 'Cumilla', 'Jashore', 'Chattogram', 'Barishal', 'Sylhet', 'Dinajpur', 'Mymensingh', 'Madrasah', 'Technical'];

    /** কোন ক্লাসে SSC / HSC এর ঘর লাগবে আর Running চলবে কিনা: [দেখাবে, running চলবে] */
    public static function kycExamRule(string $cls, string $exam): array
    {
        $order = array_keys(self::KYC_CLASSES);
        $i = array_search($cls, $order, true);
        if ($i === false) return [false, false];
        $ge = fn($k) => $i >= array_search($k, $order, true);
        if ($exam === 'ssc') return [$ge('c9'), in_array($cls, ['c9', 'c10'], true)];
        // HSC: ১১-১২ ক্লাসে Running, অনার্স / মাস্টার্সে পাস করা লাগবে; ডিপ্লোমায় লাগে না
        if ($cls === 'diploma') return [false, false];
        return [$ge('c11'), in_array($cls, ['c11', 'c12'], true)];
    }

    public function EnsureKycTable(): void
    {
        if ($this->kycReady) return;
        $this->kycReady = true;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "KycRequests" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Kind" TEXT NOT NULL,
                "Status" TEXT NOT NULL DEFAULT \'Pending\', "Data" TEXT NULL, "FileFront" TEXT NULL, "FileBack" TEXT NULL, "TeacherRequestId" INTEGER NULL,
                "AdminNote" TEXT NULL, "ReviewedBy" INTEGER NULL, "ReviewedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Kyc_User" ON "KycRequests" ("UserId", "Kind")');
        } else {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS `KycRequests` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Kind` VARCHAR(10) NOT NULL,
                `Status` VARCHAR(12) NOT NULL DEFAULT \'Pending\', `Data` TEXT NULL, `FileFront` VARCHAR(120) NULL, `FileBack` VARCHAR(120) NULL, `TeacherRequestId` INT NULL,
                `AdminNote` VARCHAR(500) NULL, `ReviewedBy` INT NULL, `ReviewedAt` DATETIME NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), KEY `IX_Kyc_User` (`UserId`, `Kind`), KEY `IX_Kyc_Status` (`Status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
    }

    private function mapKyc(?Obj $k): ?Obj
    {
        if (!$k) return null;
        $k->Id = (int)$k->Id; $k->UserId = (int)$k->UserId;
        $d = json_decode((string)($k->Data ?? ''), true);
        $k->D = is_array($d) ? $d : [];
        return $k;
    }

    /** ইউজারের সর্বশেষ KYC (student / teacher) */
    public function GetKyc(int $userId, string $kind): ?Obj
    {
        $this->EnsureKycTable();
        return $this->mapKyc($this->one('SELECT * FROM `KycRequests` WHERE `UserId` = :u AND `Kind` = :k ORDER BY `Id` DESC LIMIT 1', ['u' => $userId, 'k' => $kind]));
    }

    public function GetKycById(int $id): ?Obj
    {
        $this->EnsureKycTable();
        return $this->mapKyc($this->one('SELECT * FROM `KycRequests` WHERE `Id` = :i', ['i' => $id]));
    }

    /** 'None' | 'Pending' | 'Approved' | 'Rejected' */
    public function KycStatus(int $userId, string $kind): string
    {
        try { $k = $this->GetKyc($userId, $kind); return $k ? (string)$k->Status : 'None'; } catch (Throwable $e) { return 'None'; }
    }

    public function SaveKyc(int $userId, string $kind, array $data, string $front, string $back, ?int $teacherRequestId = null): int
    {
        $this->EnsureKycTable();
        return (int)$this->insert('INSERT INTO `KycRequests` (`UserId`, `Kind`, `Status`, `Data`, `FileFront`, `FileBack`, `TeacherRequestId`, `CreatedAt`) VALUES (:u, :k, \'Pending\', :d, :f, :b, :t, :c)',
            ['u' => $userId, 'k' => $kind, 'd' => json_encode($data, JSON_UNESCAPED_UNICODE), 'f' => $front, 'b' => $back, 't' => $teacherRequestId, 'c' => now()]);
    }

    /** যাচাই বাকি থাকা অবস্থায় ইউজার নিজের তথ্য / ID কার্ডের ছবি বদলালে — একই আবেদন আপডেট (নতুন সারি না) */
    public function UpdatePendingKyc(int $id, array $data, string $front, string $back): bool
    {
        $this->EnsureKycTable();
        return $this->exec('UPDATE `KycRequests` SET `Data` = :d, `FileFront` = :f, `FileBack` = :b WHERE `Id` = :i AND `Status` = \'Pending\'',
            ['d' => json_encode($data, JSON_UNESCAPED_UNICODE), 'f' => $front, 'b' => $back, 'i' => $id]) > 0;
    }

    public function LinkKycTeacherRequest(int $kycId, int $requestId): void
    {
        $this->EnsureKycTable();
        $this->exec('UPDATE `KycRequests` SET `TeacherRequestId` = :t WHERE `Id` = :i', ['t' => $requestId, 'i' => $kycId]);
    }

    public function SetKycStatus(int $id, string $status, string $note, int $adminId): bool
    {
        $this->EnsureKycTable();
        return $this->exec('UPDATE `KycRequests` SET `Status` = :s, `AdminNote` = :n, `ReviewedBy` = :a, `ReviewedAt` = :t WHERE `Id` = :i',
            ['s' => $status, 'n' => $note !== '' ? $note : null, 'a' => $adminId, 't' => now(), 'i' => $id]) > 0;
    }

    /** অ্যাডমিন KYC পেজ — প্রতিটা ইউজারের সর্বশেষ জমাটাই (kind = student / teacher, status = Pending / Approved / Rejected / all) */
    public function GetKycList(string $kind, string $status = 'Pending'): array
    {
        $this->EnsureKycTable();
        $p = ['k' => $kind];
        $w = '';
        if ($status !== 'all') { $w = ' AND k.`Status` = :s'; $p['s'] = $status; }
        $rows = $this->all('SELECT k.*, u.`FullName` AS UFullName, u.`UserName` AS UUserName, u.`Email` AS UEmail, u.`Role` AS URole, u.`ProfileImageLink` AS UPhoto, u.`MobileNumber` AS UMobile
            FROM `KycRequests` k LEFT JOIN `Users` u ON u.`Id` = k.`UserId`
            WHERE k.`Kind` = :k AND k.`Id` = (SELECT MAX(x.`Id`) FROM `KycRequests` x WHERE x.`UserId` = k.`UserId` AND x.`Kind` = k.`Kind`)' . $w . ' ORDER BY k.`Id` DESC LIMIT 500', $p);
        return array_map(fn($r) => $this->mapKyc($r), $rows);
    }

    /** [kind => [Pending => n, Approved => n, Rejected => n]] */
    public function KycCounts(): array
    {
        $this->EnsureKycTable();
        $out = ['student' => ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0], 'teacher' => ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0]];
        foreach ($this->all('SELECT k.`Kind`, k.`Status`, COUNT(*) AS N FROM `KycRequests` k
            WHERE k.`Id` = (SELECT MAX(x.`Id`) FROM `KycRequests` x WHERE x.`UserId` = k.`UserId` AND x.`Kind` = k.`Kind`) GROUP BY k.`Kind`, k.`Status`') as $r)
            if (isset($out[$r->Kind][$r->Status])) $out[$r->Kind][$r->Status] = (int)$r->N;
        return $out;
    }

    public function CountPendingKyc(): int
    {
        try { $c = $this->KycCounts(); return $c['student']['Pending'] + $c['teacher']['Pending']; } catch (Throwable $e) { return 0; }
    }

    /**
     * টিচার আবেদন চূড়ান্ত — Teacher Verification Approve হলেই ইউজার Teacher (Tutor List এ আসে, Teacher Dashboard, নীল টিক)।
     * প্যাকেজ লাগে না; প্যাকেজ কিনলে (পেমেন্ট Accept) ছবি, সম্পূর্ণ তথ্য আর যোগাযোগ খোলে।
     * পুরোনো আবেদন যেখানে Verification নেই — আগের মতো পেমেন্ট Accept (বা প্যাকেজই না থাকলে) হলে। হয়ে গেলে true।
     */
    public function FinalizeTeacherIfReady(int $requestId): bool
    {
        $this->EnsureTeacherRequestPaymentCol();
        $r = $this->one('SELECT * FROM `TeacherRequests` WHERE `Id` = :i', ['i' => $requestId]);
        if (!$r || !in_array($r->Status, ['Pending', 'AwaitingPayment'], true)) return false;
        $kyc = $this->GetKyc((int)$r->UserId, 'teacher');
        if ($kyc) {
            if ($kyc->Status !== 'Approved') return false;
        } elseif (!empty($r->PaymentId)) {
            $p = $this->GetPaymentRequest((int)$r->PaymentId);
            if (!$p || $p->Status !== 'Approved') return false;
        } elseif ($this->TeacherApplyNeedsPayment()) return false;
        $this->UpdateTeacherRequest((int)$r->Id, 'Approved', null);
        $this->MakeUserTeacher((int)$r->UserId);
        return true;
    }

    /**
     * আগে Verification Approve হয়েছে কিন্তু পেমেন্ট বাকি থাকায় টিচার হয়নি — তাদের এখন টিচার বানানো (নতুন নিয়ম, একবার চলে)
     * @return int কতজন টিচার হলো
     */
    public function FinalizeApprovedTeacherKycs(): int
    {
        $n = 0;
        try {
            $this->EnsureKycTable();
            $rows = $this->all("SELECT tr.`Id`, tr.`UserId` FROM `TeacherRequests` tr
                INNER JOIN `KycRequests` k ON k.`UserId` = tr.`UserId` AND k.`Kind` = 'teacher' AND k.`Status` = 'Approved'
                WHERE tr.`Status` IN ('Pending', 'AwaitingPayment')
                  AND tr.`Id` = (SELECT MAX(t2.`Id`) FROM `TeacherRequests` t2 WHERE t2.`UserId` = tr.`UserId`)");
            foreach ($rows as $r) {
                if ($this->FinalizeTeacherIfReady((int)$r->Id)) {
                    $n++;
                    $this->CreateNotification((int)$r->UserId, "🎓 You're now a Teacher!", 'অভিনন্দন! আপনার Teacher Verification Approve হয়েছে — আপনি এখন টিচার। প্যাকেজ কিনলে Tutor List এ আপনার ছবি, সম্পূর্ণ তথ্য আর যোগাযোগের সুযোগ খুলে যাবে।', 'success', '/Teacher/TeacherDashboard', 'fa-chalkboard-teacher');
                }
            }
            // প্যাকেজের মেয়াদ শেষে আগের নিয়মে সাধারণ ইউজার হয়ে যাওয়া টিচাররা — আবার টিচার (প্রোফাইল লক থাকবে, রিনিউ করলে খুলবে)
            $exp = $this->all("SELECT tr.`Id`, tr.`UserId` FROM `TeacherRequests` tr INNER JOIN `Users` u ON u.`Id` = tr.`UserId` AND u.`Role` = 'User'
                WHERE tr.`Status` = 'Expired' AND tr.`Id` = (SELECT MAX(t2.`Id`) FROM `TeacherRequests` t2 WHERE t2.`UserId` = tr.`UserId`)");
            foreach ($exp as $r) {
                $this->UpdateTeacherRequest((int)$r->Id, 'Approved', 'নতুন নিয়ম — প্যাকেজ ছাড়াও টিচার (প্রোফাইল লক)');
                $this->MakeUserTeacher((int)$r->UserId);
                $n++;
            }
        } catch (Throwable $e) { error_log('[FinalizeApprovedTeacherKycs] ' . $e->getMessage()); }
        return $n;
    }

    /** কোনো পেমেন্টের সাথে চালু (Pending) টিচার আবেদন থাকলে সেটা */
    public function GetTeacherRequestByPayment(int $paymentId): ?Obj
    {
        $this->EnsureTeacherRequestPaymentCol();
        return $this->one('SELECT * FROM `TeacherRequests` WHERE `PaymentId` = :p ORDER BY `Id` DESC LIMIT 1', ['p' => $paymentId]);
    }
}

// ============================================================ KYC ফাইল (ব্যক্তিগত — ওয়েব থেকে সরাসরি খোলা যায় না)
function kyc_dir(): string
{
    $d = APP_ROOT . '/app/storage/kyc';
    if (!is_dir($d)) { @mkdir($d, 0750, true); @file_put_contents(APP_ROOT . '/app/storage/.htaccess', "Require all denied\n"); }
    return $d;
}

/** ছবি যাচাই করে সেভ — ফাইলের নাম ফেরত দেয়, সমস্যা হলে null আর $err এ কারণ */
function kyc_store_image(?array $f, int $userId, string $tag, ?string &$err = null): ?string
{
    $err = null;
    if (!$f) { $err = 'ছবি দিন।'; return null; }
    if ((int)$f['size'] > 6 * 1024 * 1024) { $err = 'ছবি ৬ MB এর মধ্যে হতে হবে।'; return null; }
    $info = @getimagesize($f['tmp_name']);
    $ext = $info ? ([IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2]] ?? null) : null;
    if (!$ext) { $err = 'শুধু ছবি (JPG, PNG, WEBP) দেওয়া যাবে।'; return null; }
    if ($info[0] < 200 || $info[1] < 120) { $err = 'ছবিটা অনেক ছোট — পরিষ্কার করে তোলা ছবি দিন।'; return null; }
    $name = 'u' . $userId . '_' . preg_replace('/[^a-z]/', '', $tag) . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $dest = kyc_dir() . '/' . $name;
    if (!@move_uploaded_file($f['tmp_name'], $dest) && !@rename($f['tmp_name'], $dest) && !@copy($f['tmp_name'], $dest)) { $err = 'ছবি সেভ করা যায়নি — আবার চেষ্টা করুন।'; return null; }
    @chmod($dest, 0640);
    return $name;
}

function kyc_file_path(?string $name): ?string
{
    if (!$name || !preg_match('/^u\d+_[a-z]+_[0-9a-f]{20}\.(jpg|png|webp)$/', $name)) return null;
    $p = kyc_dir() . '/' . $name;
    return is_file($p) ? $p : null;
}

function kyc_delete_file(?string $name): void
{
    if ($p = kyc_file_path($name)) @unlink($p);
}

// ============================================================ রোলের নাম (User → Student Verification Approve হলে Student)
/** Student Verification Approve হওয়া ইউজারদের Id (একবারই পড়া হয়) */
function verified_student_ids(): array
{
    static $ids = null;
    if ($ids !== null) return $ids;
    $ids = [];
    try {
        db()->EnsureKycTable();
        foreach (db()->all("SELECT k.`UserId` FROM `KycRequests` k WHERE k.`Kind` = 'student' AND k.`Status` = 'Approved'
            AND k.`Id` = (SELECT MAX(x.`Id`) FROM `KycRequests` x WHERE x.`UserId` = k.`UserId` AND x.`Kind` = 'student')") as $r) $ids[(int)$r->UserId] = true;
    } catch (Throwable $e) { }
    return $ids;
}

/**
 * Instructor — অ্যাডমিনের দেওয়া বিশেষ রোল (Users.Role = 'User' থাকে, তালিকা SiteContents এ `instructor_ids`)।
 * Edu Feed এ পোস্ট সরাসরি প্রকাশ (Approve লাগে না), Helpdesk পোস্টে "Instructor" ব্যাজ।
 */
function instructor_ids(bool $fresh = false): array
{
    static $ids = null;
    if ($ids !== null && !$fresh) return $ids;
    $ids = [];
    try {
        $raw = (string)(db()->GetSiteContent(['instructor_ids' => ''])['instructor_ids'] ?? '');
        foreach (preg_split('/[,\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $x) if (ctype_digit($x)) $ids[(int)$x] = true;
    } catch (Throwable $e) { }
    return $ids;
}
function is_instructor($userId): bool { return $userId !== null && isset(instructor_ids()[(int)$userId]); }
function set_instructor(int $userId, bool $on): void
{
    $ids = instructor_ids(true);
    if ($on) $ids[$userId] = true; else unset($ids[$userId]);
    db()->SaveSiteContent(['instructor_ids' => implode(',', array_keys($ids))]);
    instructor_ids(true);
}

/** দেখানোর নাম: Teacher / Admin / Instructor / Student (ভেরিফাইড) / User (নতুন, এখনো ভেরিফাই হয়নি) */
function role_label(?string $role, $userId): string
{
    if ($role === 'Teacher') return 'Teacher';
    if ($role === 'Admin') return 'Admin';
    if (is_instructor($userId)) return 'Instructor';
    return isset(verified_student_ids()[(int)$userId]) ? 'Student' : 'User';
}

/**
 * এক অ্যাকাউন্ট = হয় শিক্ষার্থী, নয় টিচার — দুটো একসাথে না।
 * 'teacher' = টিচার বা টিচার হওয়ার আবেদন চালু; 'student' = Student Verification জমা / Approve; null = এখনো কিছু বাছেনি
 */
function account_track(?int $userId): ?string
{
    if (!$userId) return null;
    static $c = [];
    if (array_key_exists($userId, $c)) return $c[$userId];
    try {
        $u = db()->GetUserById($userId);
        if ($u && $u->Role === 'Teacher') return $c[$userId] = 'teacher';
        if ($u && $u->Role === 'Admin') return $c[$userId] = null;
        if (in_array(db()->GetUserTeacherRequestStatus($userId), ['Pending', 'AwaitingPayment', 'Approved', 'Expired'], true)) return $c[$userId] = 'teacher';
        if (in_array(db()->KycStatus($userId, 'student'), ['Pending', 'Approved'], true)) return $c[$userId] = 'student';
    } catch (Throwable $e) { }
    return $c[$userId] = null;
}

const ONE_ROLE_MSG_TEACHER = 'এই অ্যাকাউন্টটা টিচার হিসেবে ব্যবহার হচ্ছে (বা টিচার হওয়ার আবেদন করা আছে) — একই অ্যাকাউন্টে শিক্ষার্থী হওয়া যায় না। শিক্ষার্থী হিসেবে চাইলে লগআউট করে নতুন অ্যাকাউন্ট খুলুন।';
const ONE_ROLE_MSG_STUDENT = 'এই অ্যাকাউন্টটা শিক্ষার্থী হিসেবে ব্যবহার হচ্ছে (Student Verification জমা / Approve আছে) — একই অ্যাকাউন্টে টিচার হওয়া যায় না। টিচার হতে চাইলে লগআউট করে নতুন অ্যাকাউন্ট খুলুন।';

/**
 * নীল টিক — টিচার প্যাকেজ কিনলে না, "Become a Tutor" ফর্ম অ্যাডমিন Approve করলে (Teacher Verification Approved,
 * অথবা Verification চালুর আগের Approve হওয়া টিচার আবেদন)। শুধু চালু টিচার অ্যাকাউন্ট।
 */
function verified_teacher_ids(): array
{
    static $ids = null;
    if ($ids !== null) return $ids;
    $ids = [];
    try {
        db()->EnsureKycTable();
        foreach (db()->all("SELECT u.`Id` FROM `Users` u WHERE u.`Role` = 'Teacher' AND (
                EXISTS (SELECT 1 FROM `TeacherRequests` r WHERE r.`UserId` = u.`Id` AND r.`Status` = 'Approved')
             OR EXISTS (SELECT 1 FROM `KycRequests` k WHERE k.`UserId` = u.`Id` AND k.`Kind` = 'teacher' AND k.`Status` = 'Approved'))") as $r) $ids[(int)$r->Id] = true;
        // Verification বাতিল (Approve বাতিল) করা থাকলে টিক নেই
        foreach (db()->all("SELECT k.`UserId` FROM `KycRequests` k WHERE k.`Kind` = 'teacher' AND k.`Status` = 'Rejected'
            AND k.`Id` = (SELECT MAX(x.`Id`) FROM `KycRequests` x WHERE x.`UserId` = k.`UserId` AND x.`Kind` = 'teacher')") as $r) unset($ids[(int)$r->UserId]);
    } catch (Throwable $e) { }
    return $ids;
}

function has_blue_tick($userId): bool { return $userId !== null && isset(verified_teacher_ids()[(int)$userId]); }
