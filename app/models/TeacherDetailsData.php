<?php
/**
 * টিচারের বিস্তারিত তথ্য (Tutor Details) — Db ক্লাসে trait হিসেবে যুক্ত।
 * টিচার হওয়ার আবেদনের সময় (Teacher/RequestForm) ফর্ম পূরণ করে; পরে Teacher/EditDetails থেকে বদলাতে পারে।
 * এই তথ্যগুলো Tutor List → See (/Tutors/Profile/{id}) পেজে দেখায়।
 * টেবিল: TeacherDetails (UserId, Data = JSON, PageViews, UpdatedAt)
 */
trait TeacherDetailsData
{
    private bool $tdReady = false;

    /** ফর্মের সব ঘর: key => [ধরন, সর্বোচ্চ দৈর্ঘ্য] — ধরন: text | long | list (কমা দিয়ে আলাদা / একাধিক) */
    public static function teacherDetailFields(): array
    {
        $f = [
            'preface' => ['long', 1500], 'tuitionExperience' => ['long', 1500], 'whyHire' => ['long', 1500],
            'university' => ['text', 150], 'department' => ['text', 120],
            'livingCountry' => ['text', 60], 'livingCity' => ['text', 60], 'livingArea' => ['text', 80],
            'preferredAreas' => ['list', 400], 'mediums' => ['list', 200], 'classes' => ['list', 600], 'subjects' => ['list', 600],
            'methods' => ['list', 120], 'days' => ['list', 120], 'timeFrom' => ['text', 10], 'timeTo' => ['text', 10],
            'experienceYears' => ['text', 20], 'expectedSalary' => ['text', 20],
            'bloodGroup' => ['text', 5], 'religion' => ['text', 30], 'nationality' => ['text', 40], 'dob' => ['text', 10], 'gender' => ['text', 10],
            'presentAddress' => ['text', 250], 'permanentAddress' => ['text', 250],
        ];
        // শিক্ষাগত যোগ্যতা: SSC, HSC, স্নাতক, স্নাতকোত্তর
        foreach (['ssc', 'hsc'] as $k) foreach (['Title' => 40, 'Institute' => 150, 'Board' => 40, 'Group' => 40, 'Year' => 10, 'Result' => 10] as $c => $n) $f[$k . $c] = ['text', $n];
        foreach (['grad', 'post'] as $k) foreach (['Title' => 40, 'Institute' => 150, 'StudyType' => 40, 'Department' => 120, 'Year' => 20, 'Result' => 10] as $c => $n) $f[$k . $c] = ['text', $n];
        return $f;
    }

    public const TD_MEDIUMS = ['Bangla Medium', 'English Medium', 'English Version', 'Madrasa'];
    public const TD_METHODS = ['Home Tutoring', 'Online Tutoring', 'Batch Tutoring'];
    public const TD_DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function EnsureTeacherDetailsTable(): void
    {
        if ($this->tdReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherDetails" ("UserId" INTEGER PRIMARY KEY, "Data" TEXT NULL, "PageViews" INTEGER NOT NULL DEFAULT 0, "UpdatedAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherDetails` (`UserId` INT NOT NULL, `Data` MEDIUMTEXT NULL, `PageViews` INT NOT NULL DEFAULT 0, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->tdReady = true;
    }

    /** ফর্ম থেকে আসা তথ্য পরিষ্কার করে (HTML বাদ, দৈর্ঘ্য সীমা, তালিকা) */
    public static function cleanTeacherDetails(array $in): array
    {
        $out = [];
        foreach (self::teacherDetailFields() as $k => [$type, $max]) {
            $v = $in[$k] ?? '';
            if ($type === 'list') {
                $items = is_array($v) ? $v : preg_split('/\s*[,،]\s*/u', (string)$v);
                $clean = [];
                foreach ($items as $it) {
                    $it = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$it))), 0, 60);
                    if ($it !== '' && !in_array(mb_strtolower($it), array_map('mb_strtolower', $clean), true)) $clean[] = $it;
                }
                $out[$k] = array_slice($clean, 0, 40);
                continue;
            }
            $v = str_replace("\r", '', strip_tags((string)$v));
            $v = $type === 'long' ? preg_replace("/\n{3,}/", "\n\n", trim($v)) : trim(preg_replace('/\s+/u', ' ', $v));
            $out[$k] = mb_substr($v, 0, $max);
        }
        if ($out['dob'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $out['dob'])) $out['dob'] = '';
        foreach (['timeFrom', 'timeTo'] as $t) if ($out[$t] !== '' && !preg_match('/^\d{2}:\d{2}$/', $out[$t])) $out[$t] = '';
        return $out;
    }

    /** সব ঘর সহ (খালি থাকলে '' / []) */
    public function GetTeacherDetails(int $userId): array
    {
        $blank = [];
        foreach (self::teacherDetailFields() as $k => [$type]) $blank[$k] = $type === 'list' ? [] : '';
        try {
            $this->EnsureTeacherDetailsTable();
            $raw = $this->scalar('SELECT `Data` FROM `TeacherDetails` WHERE `UserId` = :u', ['u' => $userId]);
            $d = json_decode((string)$raw, true);
            if (is_array($d)) foreach ($blank as $k => $def) if (isset($d[$k])) $blank[$k] = is_array($def) ? (array)$d[$k] : (string)$d[$k];
        } catch (Throwable $e) { error_log('[GetTeacherDetails] ' . $e->getMessage()); }
        return $blank;
    }

    public function HasTeacherDetails(int $userId): bool
    {
        try { $this->EnsureTeacherDetailsTable(); return (int)$this->scalar('SELECT COUNT(*) FROM `TeacherDetails` WHERE `UserId` = :u AND `Data` IS NOT NULL', ['u' => $userId]) > 0; }
        catch (Throwable $e) { return false; }
    }

    /** সেভ + Find Tutor / Tutor List এর জন্য শহর, বিষয়, ক্লাস, পদ্ধতি আর বেতন UserProfileExtras এও লেখে; লিঙ্গ Users এ */
    public function SaveTeacherDetails(int $userId, array $d): void
    {
        $this->EnsureTeacherDetailsTable();
        $json = json_encode($d, JSON_UNESCAPED_UNICODE);
        if ($this->exec('UPDATE `TeacherDetails` SET `Data` = :d, `UpdatedAt` = :t WHERE `UserId` = :u', ['d' => $json, 't' => now(), 'u' => $userId]) === 0
            && !(int)$this->scalar('SELECT COUNT(*) FROM `TeacherDetails` WHERE `UserId` = :u', ['u' => $userId])) {
            $this->exec('INSERT INTO `TeacherDetails` (`UserId`, `Data`, `PageViews`, `UpdatedAt`) VALUES (:u, :d, 0, :t)', ['u' => $userId, 'd' => $json, 't' => now()]);
        }
        try {
            $this->EnsureTutorDirectory();
            $m = $d['methods'] ?? [];
            $online = in_array('Online Tutoring', $m, true); $home = in_array('Home Tutoring', $m, true) || in_array('Batch Tutoring', $m, true);
            $mode = $online && $home ? 'Both' : ($online ? 'Online' : ($home ? 'Home' : null));
            $fee = preg_match('/(\d[\d,]*)/', (string)($d['expectedSalary'] ?? ''), $mm) ? (int)str_replace(',', '', $mm[1]) : null;
            $vals = ['c' => $d['livingCity'] !== '' ? $d['livingCity'] : null, 's' => $d['subjects'] ? implode(', ', $d['subjects']) : null,
                'l' => ($d['classes'] || $d['mediums']) ? implode(', ', array_merge($d['classes'], $d['mediums'])) : null, 'm' => $mode, 'f' => $fee, 'u' => $userId];
            if ($this->exec('UPDATE `UserProfileExtras` SET `City` = COALESCE(:c, `City`), `Subjects` = COALESCE(:s, `Subjects`), `Levels` = COALESCE(:l, `Levels`),
                    `TeachMode` = COALESCE(:m, `TeachMode`), `FeeMin` = COALESCE(:f, `FeeMin`) WHERE `UserId` = :u', $vals) === 0
                && !(int)$this->scalar('SELECT COUNT(*) FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId])) {
                $this->exec('INSERT INTO `UserProfileExtras` (`UserId`, `City`, `Subjects`, `Levels`, `TeachMode`, `FeeMin`) VALUES (:u, :c, :s, :l, :m, :f)', $vals);
            }
        } catch (Throwable $e) { error_log('[SaveTeacherDetails sync] ' . $e->getMessage()); }
        $this->SyncTeacherDetailsToProfile($userId, $d);   // লিঙ্গ, দেশ, ঠিকানা, প্রতিষ্ঠান, অভিজ্ঞতা → প্রোফাইলের About
    }

    // ============================================================ প্রোফাইল (About) ↔ Become a Tutor ফর্মের মিল থাকা তথ্য
    // প্রোফাইলের Users কলাম => Tutor Details ঘর (একই তথ্য — এক জায়গায় দিলে অন্য জায়গায় অটো বসে যায়)
    private const TD_PROFILE_MAP = ['Gender' => 'gender', 'Country' => 'livingCountry', 'StreetAddress' => 'presentAddress', 'PermanentAddress' => 'permanentAddress', 'InstitutionName' => 'university'];
    private const TD_EXP_TO_PROFILE = ['Fresher' => '0-1 year', '1 Year' => '1-2 years', '2 Years' => '1-2 years', '3 Years' => '3-5 years', '4 Years' => '3-5 years', '5 Years' => '3-5 years', '6-10 Years' => '5-10 years', '10+ Years' => '10+ years'];
    private const TD_EXP_FROM_PROFILE = ['0-1 year' => 'Fresher', '1-2 years' => '1 Year', '3-5 years' => '3 Years', '5-10 years' => '6-10 Years', '10+ years' => '10+ Years'];

    /** ফর্ম খোলার সময়: Tutor Details এর খালি ঘরগুলো প্রোফাইলের মিল থাকা তথ্য দিয়ে ভরে দেয় */
    public function FillTeacherDetailsFromProfile(int $userId, array $d): array
    {
        try {
            $u = $this->GetUserById($userId);
            if (!$u) return $d;
            foreach (self::TD_PROFILE_MAP as $col => $k) {
                $v = trim((string)($u->$col ?? ''));
                if ($v === '' || trim((string)($d[$k] ?? '')) !== '') continue;
                if ($k === 'gender' && !in_array($v, ['Male', 'Female'], true)) continue;
                $d[$k] = $v;
            }
            $ex = (string)($u->YearsOfExperience ?? '');
            if (trim((string)($d['experienceYears'] ?? '')) === '' && isset(self::TD_EXP_FROM_PROFILE[$ex])) $d['experienceYears'] = self::TD_EXP_FROM_PROFILE[$ex];
        } catch (Throwable $e) { error_log('[FillTeacherDetailsFromProfile] ' . $e->getMessage()); }
        return $d;
    }

    /** Become a Tutor / Tutor Details সেভ হলে: মিল থাকা তথ্য প্রোফাইলের About এ বসে যায় (খালি ঘর দিয়ে প্রোফাইলের তথ্য মোছে না) */
    public function SyncTeacherDetailsToProfile(int $userId, array $d): void
    {
        try {
            $set = []; $p = ['u' => $userId];
            foreach (self::TD_PROFILE_MAP as $col => $k) {
                $v = trim((string)($d[$k] ?? ''));
                if ($v === '') continue;
                $set[] = "`$col` = :$col"; $p[$col] = $v;
            }
            $ex = self::TD_EXP_TO_PROFILE[(string)($d['experienceYears'] ?? '')] ?? null;
            if ($ex !== null) { $set[] = '`YearsOfExperience` = :ex'; $p['ex'] = $ex; }
            if ($set) $this->exec('UPDATE `Users` SET ' . implode(', ', $set) . ' WHERE `Id` = :u', $p);
        } catch (Throwable $e) { error_log('[SyncTeacherDetailsToProfile] ' . $e->getMessage()); }
    }

    /** প্রোফাইলের About এডিট হলে: Tutor Details আগে থেকে থাকলে মিল থাকা ঘরগুলোও আপডেট হয় */
    public function SyncProfileToTeacherDetails(int $userId): void
    {
        try {
            if (!$this->HasTeacherDetails($userId)) return;
            $u = $this->GetUserById($userId);
            if (!$u) return;
            $d = $this->GetTeacherDetails($userId); $changed = false;
            foreach (self::TD_PROFILE_MAP as $col => $k) {
                $v = trim((string)($u->$col ?? ''));
                if ($v === '' || $v === (string)$d[$k] || ($k === 'gender' && !in_array($v, ['Male', 'Female'], true))) continue;
                $d[$k] = $v; $changed = true;
            }
            $ex = (string)($u->YearsOfExperience ?? '');
            if (isset(self::TD_EXP_FROM_PROFILE[$ex]) && (self::TD_EXP_TO_PROFILE[$d['experienceYears']] ?? '') !== $ex) { $d['experienceYears'] = self::TD_EXP_FROM_PROFILE[$ex]; $changed = true; }
            if (!$changed) return;
            $d = self::cleanTeacherDetails($d);
            $this->exec('UPDATE `TeacherDetails` SET `Data` = :d, `UpdatedAt` = :t WHERE `UserId` = :u', ['d' => json_encode($d, JSON_UNESCAPED_UNICODE), 't' => now(), 'u' => $userId]);
        } catch (Throwable $e) { error_log('[SyncProfileToTeacherDetails] ' . $e->getMessage()); }
    }

    /** ডিটেইলস পেজ কতবার দেখা হয়েছে (একই ব্রাউজার সেশনে একবারই গোনা হয়) */
    public function BumpTeacherPageViews(int $userId): int
    {
        try {
            $this->EnsureTeacherDetailsTable();
            $seen = $_SESSION['__tdv'] ?? [];
            if (empty($seen[$userId])) {
                if ($this->exec('UPDATE `TeacherDetails` SET `PageViews` = `PageViews` + 1 WHERE `UserId` = :u', ['u' => $userId]) === 0) {
                    try { $this->exec('INSERT INTO `TeacherDetails` (`UserId`, `Data`, `PageViews`) VALUES (:u, NULL, 1)', ['u' => $userId]); } catch (Throwable $e) { }
                }
                $seen[$userId] = 1; $_SESSION['__tdv'] = array_slice($seen, -200, null, true);
            }
            return (int)$this->scalar('SELECT `PageViews` FROM `TeacherDetails` WHERE `UserId` = :u', ['u' => $userId]);
        } catch (Throwable $e) { return 0; }
    }

    /** টিচারের প্ল্যাটফর্ম কার্যক্রম — আবেদন / নির্বাচিত / নির্বাচিত হয়নি / সরাসরি রিকোয়েস্ট / স্টুডেন্ট */
    public function TeacherPlatformStats(int $userId): array
    {
        $s = ['applied' => 0, 'selected' => 0, 'notSelected' => 0, 'requests' => 0, 'students' => 0];
        try {
            $this->EnsureCircularApplicationsTable();
            foreach ($this->all('SELECT `Status`, COUNT(*) AS N FROM `TuitionCircularApplications` WHERE `TeacherId` = :t GROUP BY `Status`', ['t' => $userId]) as $r) {
                $s['applied'] += (int)$r->N;
                if ($r->Status === 'Accepted') $s['selected'] += (int)$r->N;
                if ($r->Status === 'Rejected') $s['notSelected'] += (int)$r->N;
            }
        } catch (Throwable $e) { }
        try {
            $this->EnsureTutorRequestUserColumns();
            foreach ($this->all('SELECT `Status`, COUNT(*) AS N FROM `TutorRequests` WHERE `TeacherUserId` = :t GROUP BY `Status`', ['t' => $userId]) as $r) {
                $s['requests'] += (int)$r->N;
                if ($r->Status === 'Accepted') $s['selected'] += (int)$r->N;
            }
        } catch (Throwable $e) { }
        try { $s['students'] = (int)$this->scalar('SELECT COUNT(*) FROM `TeacherStudents` WHERE `TeacherId` = :t', ['t' => $userId]); } catch (Throwable $e) { }
        return $s;
    }

    // ---------- টিচার আবেদন + পেমেন্ট ----------
    // নিয়ম: ফর্ম জমা → (Status = AwaitingPayment) → টিচার প্যাকেজ কিনে পেমেন্টের প্রমাণ জমা → (Status = Pending, PaymentId) → অ্যাডমিন দেখে Approve
    private bool $trPayReady = false;
    public function EnsureTeacherRequestPaymentCol(): void
    {
        if ($this->trPayReady) return;
        $this->trPayReady = true;
        try { $this->pdo->query('SELECT `PaymentId` FROM `TeacherRequests` LIMIT 1'); }
        catch (Throwable $e) { try { $this->pdo->exec('ALTER TABLE `TeacherRequests` ADD COLUMN `PaymentId` INT NULL'); } catch (Throwable $e2) { error_log('[TR PaymentId] ' . $e2->getMessage()); } }
    }

    /** কোনো চালু টিচার প্যাকেজ থাকলে আবেদনের আগে পেমেন্ট লাগবে */
    public function TeacherApplyNeedsPayment(): bool
    {
        try { return count($this->GetPackages('Teacher', true)) > 0; } catch (Throwable $e) { return false; }
    }

    public function GetLatestTeacherRequest(int $userId): ?Obj
    {
        $this->EnsureTeacherRequestPaymentCol();
        return $this->one('SELECT * FROM `TeacherRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => $userId]);
    }

    public function SetTeacherRequestState(int $id, string $status, ?int $paymentId = null): void
    {
        $this->EnsureTeacherRequestPaymentCol();
        if ($paymentId !== null) $this->exec('UPDATE `TeacherRequests` SET `Status` = :s, `PaymentId` = :p WHERE `Id` = :i', ['s' => $status, 'p' => $paymentId, 'i' => $id]);
        else $this->exec('UPDATE `TeacherRequests` SET `Status` = :s WHERE `Id` = :i', ['s' => $status, 'i' => $id]);
    }

    // ---------- প্যাকেজের মেয়াদ: শেষ হওয়ার ৫ দিন আগে থেকে রিমাইন্ডার, শেষ হলে টিচার → সাধারণ ইউজার ----------
    private bool $subColsReady = false;
    private function EnsureSubscriptionReminderCols(): void
    {
        if ($this->subColsReady) return;
        $this->subColsReady = true;
        $this->EnsurePaymentTables();
        foreach (['RemindedAt', 'Reminded1At', 'EndNotifiedAt'] as $c) {
            try { $this->pdo->query("SELECT `$c` FROM `UserSubscriptions` LIMIT 1"); }
            catch (Throwable $e) { try { $this->pdo->exec("ALTER TABLE `UserSubscriptions` ADD COLUMN `$c` " . ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'TEXT' : 'DATETIME') . ' NULL'); } catch (Throwable $e2) { } }
        }
    }

    /** মেয়াদ শেষের কত দিন আগে থেকে সতর্ক করা হবে (Admin → Subscriptions, ডিফল্ট ৫) */
    public function SubAlertDays(): int
    {
        static $d = null;
        if ($d === null) $d = max(1, min(30, (int)($this->GetSiteContent(['sub_alert_days' => '5'])['sub_alert_days'] ?? 5) ?: 5));
        return $d;
    }

    /** এই ইউজারের যে Subscription সতর্ক করার দিনের মধ্যে শেষ হবে (আর পরে কোনো নতুন Subscription নেই) — না থাকলে null */
    public function GetExpiringSubscription(int $userId, ?int $days = null): ?Obj
    {
        $days ??= $this->SubAlertDays();
        try {
            $this->EnsurePaymentTables();
            $now = now(); $lim = date('Y-m-d H:i:s', time() + $days * 86400);
            foreach (['Teacher', 'Student'] as $aud) {
                $last = $this->one('SELECT * FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = :a AND `EndsAt` IS NOT NULL AND `EndsAt` > :n ORDER BY `EndsAt` DESC LIMIT 1',
                    ['u' => $userId, 'a' => $aud, 'n' => $now]);
                if ($last && $last->EndsAt <= $lim) return $last;
            }
        } catch (Throwable $e) { }
        return null;
    }

    /**
     * সাইটে কেউ ঢুকলেই চলে (প্রতি ৩০ মিনিটে একবার) — আলাদা Cron লাগে না:
     *  1) মেয়াদ শেষের ৫ দিন আগে আর ১ দিন আগে নোটিফিকেশন (রিনিউ করার জন্য)
     *  2) টিচারের প্যাকেজের মেয়াদ শেষ (আর কোনো চালু প্যাকেজ নেই) → টিচারই থাকে, Tutor List এ থাকে, কিন্তু ছবি / সম্পূর্ণ তথ্য / যোগাযোগ বন্ধ (একবার জানানো)
     */
    public function RunSubscriptionHousekeeping(bool $force = false): void
    {
        // নতুন নিয়ম (একবার): Verification Approve হওয়া কিন্তু পেমেন্ট বাকি থাকায় টিচার না হওয়া আবেদনকারীরা টিচার হয়
        try {
            if ($this->GetSiteContent(['mig_kyc_teacher_v1' => ''])['mig_kyc_teacher_v1'] === '') {
                $this->SaveSiteContent(['mig_kyc_teacher_v1' => (string)time()]);
                $this->FinalizeApprovedTeacherKycs();
            }
        } catch (Throwable $e) { error_log('[mig_kyc_teacher_v1] ' . $e->getMessage()); }
        try {
            $last = $this->GetSiteContent(['sub_sweep_at' => '0'])['sub_sweep_at'];
            if (!$force && (int)$last > time() - 1800) return;
            $this->SaveSiteContent(['sub_sweep_at' => (string)time()]);
            $this->EnsureSubscriptionReminderCols();
            $now = now();

            // 1) রিমাইন্ডার — প্রত্যেক ইউজার+ধরনের সবচেয়ে দেরিতে শেষ হওয়া Subscription
            $alert = $this->SubAlertDays();
            foreach ($alert > 1 ? [['RemindedAt', $alert], ['Reminded1At', 1]] : [['Reminded1At', 1]] as [$col, $days]) {
                $rows = $this->all("SELECT s.* FROM `UserSubscriptions` s WHERE s.`EndsAt` IS NOT NULL AND s.`EndsAt` > :n AND s.`EndsAt` <= :l AND s.`$col` IS NULL
                    AND NOT EXISTS (SELECT 1 FROM `UserSubscriptions` x WHERE x.`UserId` = s.`UserId` AND x.`Audience` = s.`Audience` AND x.`EndsAt` > s.`EndsAt`)",
                    ['n' => $now, 'l' => date('Y-m-d H:i:s', time() + $days * 86400)]);
                foreach ($rows as $s) {
                    $left = max(1, (int)ceil((strtotime((string)$s->EndsAt) - time()) / 86400));
                    $end = date('d M Y', strtotime((string)$s->EndsAt));
                    if ($s->Audience === 'Teacher') {
                        $msg = "আপনার টিচার প্যাকেজ “{$s->PackageName}” এর মেয়াদ আর {$left} দিন ($end) — রিনিউ না করলে মেয়াদ শেষে Tutor List এ আপনার ছবি, সম্পূর্ণ তথ্য আর শিক্ষার্থীদের যোগাযোগের সুযোগ বন্ধ হয়ে যাবে।";
                        $link = '/Package?for=teacher';
                    } else {
                        $msg = "আপনার প্যাকেজ “{$s->PackageName}” এর মেয়াদ আর {$left} দিন ($end) — চালু রাখতে রিনিউ করুন।";
                        $link = '/Package?for=student';
                    }
                    $this->CreateNotification((int)$s->UserId, '⏰ প্যাকেজের মেয়াদ শেষ হচ্ছে', $msg, 'warning', $link, 'fa-clock');
                    $this->exec("UPDATE `UserSubscriptions` SET `$col` = :t WHERE `Id` = :i", ['t' => $now, 'i' => (int)$s->Id]);
                }
            }

            // 1b) শিক্ষার্থীর প্যাকেজের মেয়াদ শেষ (পরে নতুন প্যাকেজ নেই) — একবার জানানো
            $ended = $this->all("SELECT s.* FROM `UserSubscriptions` s WHERE s.`Audience` = 'Student' AND s.`EndsAt` IS NOT NULL AND s.`EndsAt` <= :n AND s.`EndsAt` > :old AND s.`EndNotifiedAt` IS NULL
                AND NOT EXISTS (SELECT 1 FROM `UserSubscriptions` x WHERE x.`UserId` = s.`UserId` AND x.`Audience` = 'Student' AND (x.`EndsAt` IS NULL OR x.`EndsAt` > s.`EndsAt`))",
                ['n' => $now, 'old' => date('Y-m-d H:i:s', time() - 7 * 86400)]);
            foreach ($ended as $s) {
                $this->CreateNotification((int)$s->UserId, 'প্যাকেজের মেয়াদ শেষ', "আপনার প্যাকেজ “{$s->PackageName}” এর মেয়াদ শেষ হয়েছে — টিচারের ছবি / যোগাযোগের তথ্য, Direct Request, চ্যাট আর Tuition পোস্ট বন্ধ। চালু রাখতে প্যাকেজ রিনিউ করুন।", 'danger', '/Package?for=student', 'fa-hourglass-end');
                $this->exec('UPDATE `UserSubscriptions` SET `EndNotifiedAt` = :t WHERE `Id` = :i', ['t' => $now, 'i' => (int)$s->Id]);
            }

            // 2) টিচারের প্যাকেজের মেয়াদ শেষ (পরে নতুন প্যাকেজ নেই) — টিচারই থাকে, শুধু প্রোফাইল লক; একবার জানানো
            $ended = $this->all("SELECT s.* FROM `UserSubscriptions` s INNER JOIN `Users` u ON u.`Id` = s.`UserId` AND u.`Role` = 'Teacher'
                WHERE s.`Audience` = 'Teacher' AND s.`EndsAt` IS NOT NULL AND s.`EndsAt` <= :n AND s.`EndsAt` > :old AND s.`EndNotifiedAt` IS NULL
                AND NOT EXISTS (SELECT 1 FROM `UserSubscriptions` x WHERE x.`UserId` = s.`UserId` AND x.`Audience` = 'Teacher' AND (x.`EndsAt` IS NULL OR x.`EndsAt` > s.`EndsAt`))",
                ['n' => $now, 'old' => date('Y-m-d H:i:s', time() - 7 * 86400)]);
            foreach ($ended as $s) {
                $this->CreateNotification((int)$s->UserId, 'টিচার প্যাকেজের মেয়াদ শেষ', "আপনার টিচার প্যাকেজ “{$s->PackageName}” এর মেয়াদ শেষ — আপনি টিচারই আছেন, Tutor List এও আছেন, কিন্তু শিক্ষার্থীরা এখন শুধু বেসিক তথ্য দেখবে (ছবি, সম্পূর্ণ তথ্য আর যোগাযোগ বন্ধ)। চালু রাখতে প্যাকেজ রিনিউ করুন।", 'danger', '/Package?for=teacher', 'fa-user-clock');
                $this->exec('UPDATE `UserSubscriptions` SET `EndNotifiedAt` = :t WHERE `Id` = :i', ['t' => $now, 'i' => (int)$s->Id]);
            }
        } catch (Throwable $e) { error_log('[SubscriptionHousekeeping] ' . $e->getMessage()); }
    }

    /**
     * Admin → Subscriptions: প্রত্যেক ইউজার (টিচার / শিক্ষার্থী আলাদা) — শেষ প্যাকেজ, তার দাম, মোট কতবার কিনেছে, মোট কত টাকা, মেয়াদ
     * @return Obj[] (Audience, UserId, Name, UserName, Role, Photo, Package, Price, Bought, StartsAt, EndsAt, Count, Total, DaysLeft, State: active|expiring|expired|credit)
     */
    public function GetSubscriptionOverview(): array
    {
        try {
            $this->EnsurePaymentTables();
            $rows = $this->all('SELECT s.`Id`, s.`UserId`, s.`Audience`, s.`PackageName`, s.`StartsAt`, s.`EndsAt`, s.`PostsTotal`, s.`PostsUsed`, s.`CreatedAt`,
                    p.`Amount`, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`
                FROM `UserSubscriptions` s LEFT JOIN `PaymentRequests` p ON p.`Id` = s.`PaymentId` LEFT JOIN `Users` u ON u.`Id` = s.`UserId`
                ORDER BY s.`Id` ASC');
            $alert = $this->SubAlertDays(); $now = time(); $by = [];
            foreach ($rows as $r) {
                $k = $r->Audience . ':' . $r->UserId;
                $o = $by[$k] ?? obj(['Audience' => (string)$r->Audience, 'UserId' => (int)$r->UserId, 'Name' => !is_blank($r->FullName) ? (string)$r->FullName : ((string)$r->UserName ?: 'Deleted user'),
                    'UserName' => (string)$r->UserName, 'Role' => (string)$r->Role, 'Photo' => (string)($r->ProfileImageLink ?? ''), 'Count' => 0, 'Total' => 0, 'EndsAt' => null, 'Credit' => false]);
                $o->Count++; $o->Total += (int)($r->Amount ?? 0);
                // সবচেয়ে দেরিতে শেষ হওয়াটাই "বর্তমান" প্যাকেজ (মেয়াদহীন ক্রেডিট হলে সবশেষ কেনাটা)
                if ($o->EndsAt === null || ($r->EndsAt !== null && $r->EndsAt >= $o->EndsAt) || $r->EndsAt === null) {
                    $o->Package = (string)$r->PackageName; $o->Price = (int)($r->Amount ?? 0); $o->Bought = (string)$r->CreatedAt; $o->StartsAt = (string)$r->StartsAt;
                    if ($r->EndsAt !== null) $o->EndsAt = (string)$r->EndsAt; else $o->Credit = (int)$r->PostsTotal === 0 || (int)$r->PostsUsed < (int)$r->PostsTotal;
                }
                $by[$k] = $o;
            }
            foreach ($by as $o) {
                $left = $o->EndsAt ? (int)ceil((strtotime($o->EndsAt) - $now) / 86400) : null;
                $o->DaysLeft = $left;
                $o->State = $o->EndsAt === null ? ($o->Credit ? 'credit' : 'expired') : ($left <= 0 ? 'expired' : ($left <= $alert ? 'expiring' : 'active'));
            }
            $out = array_values($by);
            usort($out, fn($a, $b) => [$a->State === 'expired', $b->Bought] <=> [$b->State === 'expired', $a->Bought]);
            return $out;
        } catch (Throwable $e) { error_log('[GetSubscriptionOverview] ' . $e->getMessage()); return []; }
    }

    /**
     * Admin → Subscriptions রিপোর্ট: প্যাকেজ কেনার প্রতিটা পেমেন্ট (Approved / Pending / Rejected)
     * $aud = all|Teacher|Student, $status = all|Approved|Pending|Rejected, $from/$to = Y-m-d (পেমেন্ট জমার তারিখ, দুই দিনসহ)
     * @return Obj[] (Id, Date, UserId, Name, UserName, Role, Phone, Email, Audience, Package, Amount, Months, Days, Status, Method, Txn, StartsAt, EndsAt, DaysLeft, UserTotal)
     */
    public function GetPurchaseReport(string $aud, string $status, ?string $from, ?string $to): array
    {
        try {
            $this->EnsurePaymentTables();
            $w = []; $a = [];
            if ($aud !== 'all') { $w[] = 'p.`Audience` = :aud'; $a['aud'] = $aud; } else $w[] = "p.`Audience` IN ('Teacher', 'Student')";   // কোর্সের পেমেন্ট আলাদা (Admin → Courses)
            if ($status !== 'all') { $w[] = 'p.`Status` = :st'; $a['st'] = $status; }
            if ($from) { $w[] = 'p.`CreatedAt` >= :f'; $a['f'] = $from . ' 00:00:00'; }
            if ($to) { $w[] = 'p.`CreatedAt` < :t'; $a['t'] = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00'; }
            $rows = $this->all('SELECT p.`Id`, p.`CreatedAt`, p.`UserId`, p.`Audience`, p.`PackageName`, p.`Amount`, p.`DurationMonths`, p.`Status`, p.`MethodName`, p.`TxnId`, p.`ReviewedAt`,
                    u.`FullName`, u.`UserName`, u.`Role`, u.`MobileNumber`, u.`Email`, s.`StartsAt`, s.`EndsAt`
                FROM `PaymentRequests` p LEFT JOIN `Users` u ON u.`Id` = p.`UserId` LEFT JOIN `UserSubscriptions` s ON s.`PaymentId` = p.`Id`'
                . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY p.`CreatedAt` DESC, p.`Id` DESC LIMIT 5000', $a);
            $tot = [];
            foreach ($this->all("SELECT `UserId`, `Audience`, SUM(`Amount`) AS T FROM `PaymentRequests` WHERE `Status` = 'Approved' GROUP BY `UserId`, `Audience`") as $r) $tot[$r->Audience . ':' . $r->UserId] = (int)$r->T;
            $now = time();
            return array_map(function ($r) use ($tot, $now) {
                $days = ($r->StartsAt && $r->EndsAt) ? (int)round((strtotime((string)$r->EndsAt) - strtotime((string)$r->StartsAt)) / 86400) : ((int)$r->DurationMonths * 30);
                return obj([
                    'Id' => (int)$r->Id, 'Date' => (string)$r->CreatedAt, 'UserId' => (int)$r->UserId,
                    'Name' => !is_blank($r->FullName) ? (string)$r->FullName : ((string)$r->UserName ?: 'Deleted user'), 'UserName' => (string)$r->UserName,
                    'Role' => (string)$r->Role, 'Phone' => (string)($r->MobileNumber ?? ''), 'Email' => (string)($r->Email ?? ''),
                    'Audience' => (string)$r->Audience, 'Package' => (string)$r->PackageName, 'Amount' => (int)$r->Amount, 'Months' => (int)$r->DurationMonths, 'Days' => $days,
                    'Status' => (string)$r->Status, 'Method' => (string)$r->MethodName, 'Txn' => (string)($r->TxnId ?? ''),
                    'StartsAt' => $r->StartsAt ? (string)$r->StartsAt : null, 'EndsAt' => $r->EndsAt ? (string)$r->EndsAt : null,
                    'DaysLeft' => $r->EndsAt ? (int)ceil((strtotime((string)$r->EndsAt) - $now) / 86400) : null,
                    'UserTotal' => $tot[$r->Audience . ':' . $r->UserId] ?? 0,
                ]);
            }, $rows);
        } catch (Throwable $e) { error_log('[GetPurchaseReport] ' . $e->getMessage()); return []; }
    }

    /** যারা এখনো কোনো প্যাকেজ কেনেননি (Approve হয়নি) — টিচার = Role Teacher, শিক্ষার্থী = Role User; $from/$to = অ্যাকাউন্ট খোলার তারিখ */
    public function GetNeverBoughtUsers(string $aud, ?string $from, ?string $to): array
    {
        try {
            $this->EnsurePaymentTables();
            $out = [];
            foreach (['Teacher' => 'Teacher', 'Student' => 'User'] as $audience => $role) {
                if ($aud !== 'all' && $aud !== $audience) continue;
                $w = ['u.`Role` = :r']; $a = ['r' => $role, 'au' => $audience];
                if ($from) { $w[] = 'u.`CreatedAt` >= :f'; $a['f'] = $from . ' 00:00:00'; }
                if ($to) { $w[] = 'u.`CreatedAt` < :t'; $a['t'] = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00'; }
                $rows = $this->all('SELECT u.`Id`, u.`FullName`, u.`UserName`, u.`Email`, u.`MobileNumber`, u.`CreatedAt`, u.`LastLoginAt`,
                        (SELECT COUNT(*) FROM `PaymentRequests` x WHERE x.`UserId` = u.`Id` AND x.`Audience` = :au2 AND x.`Status` = \'Pending\') AS Pend,
                        (SELECT COUNT(*) FROM `PaymentRequests` x WHERE x.`UserId` = u.`Id` AND x.`Audience` = :au3 AND x.`Status` = \'Rejected\') AS Rej
                    FROM `Users` u WHERE ' . implode(' AND ', $w) . ' AND NOT EXISTS (SELECT 1 FROM `PaymentRequests` p WHERE p.`UserId` = u.`Id` AND p.`Audience` = :au AND p.`Status` = \'Approved\')
                    ORDER BY u.`CreatedAt` DESC LIMIT 5000', $a + ['au2' => $audience, 'au3' => $audience]);
                foreach ($rows as $r) $out[] = obj(['UserId' => (int)$r->Id, 'Name' => !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName, 'UserName' => (string)$r->UserName,
                    'Email' => (string)($r->Email ?? ''), 'Phone' => (string)($r->MobileNumber ?? ''), 'Audience' => $audience, 'Joined' => (string)$r->CreatedAt,
                    'LastLogin' => $r->LastLoginAt ? (string)$r->LastLoginAt : null, 'Pending' => (int)$r->Pend, 'Rejected' => (int)$r->Rej]);
            }
            usort($out, fn($x, $y) => strcmp($y->Joined, $x->Joined));
            return $out;
        } catch (Throwable $e) { error_log('[GetNeverBoughtUsers] ' . $e->getMessage()); return []; }
    }
}
