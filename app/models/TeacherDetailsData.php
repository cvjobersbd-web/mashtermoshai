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
        foreach (['ssc', 'hsc'] as $k) foreach (['Title' => 40, 'Institute' => 150, 'Board' => 40, 'Group' => 40, 'Year' => 4, 'Result' => 10] as $c => $n) $f[$k . $c] = ['text', $n];
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
            if (in_array($d['gender'] ?? '', ['Male', 'Female'], true)) $this->exec('UPDATE `Users` SET `Gender` = :g WHERE `Id` = :u', ['g' => $d['gender'], 'u' => $userId]);
        } catch (Throwable $e) { error_log('[SaveTeacherDetails sync] ' . $e->getMessage()); }
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
}
