<?php
/**
 * Find Tutor পেজের ডাটা (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - আসল তথ্য দিয়ে টিউটর তালিকা: রিভিউ রেটিং, শহর, বিষয়, ক্লাস/লেভেল, পড়ানোর ধরন, বেতনের রেঞ্জ
 *  - টিচারের "পড়াতে চাই" পোস্টে ভিডিও (VideoUrl)
 * নতুন কলাম দরকার হলে নিজে থেকেই যোগ হয়।
 */
trait FindTutorData
{
    private bool $tutorDirReady = false;

    /** পড়ানোর ধরন: Online | Home | Both */
    public const TEACH_MODES = ['Online', 'Home', 'Both'];

    public function EnsureTutorDirectory(): void
    {
        if ($this->tutorDirReady) return;
        $this->EnsureTeacherTables();
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        foreach (['TeachMode' => $sqlite ? 'TEXT NULL' : 'VARCHAR(10) NULL', 'FeeMin' => 'INT NULL', 'FeeMax' => 'INT NULL'] as $c => $def) {
            if (!$this->columnExists('UserProfileExtras', $c)) {
                try { $this->pdo->exec("ALTER TABLE `UserProfileExtras` ADD COLUMN `$c` $def"); } catch (Throwable $e) { error_log("[extras $c] " . $e->getMessage()); }
            }
        }
        if (!$this->columnExists('TeachWantedPosts', 'VideoUrl')) {
            try { $this->pdo->exec('ALTER TABLE `TeachWantedPosts` ADD COLUMN `VideoUrl` ' . ($sqlite ? 'TEXT NULL' : 'VARCHAR(500) NULL')); } catch (Throwable $e) { error_log('[teach video] ' . $e->getMessage()); }
        }
        $this->tutorDirReady = true;
    }

    /** টিচারের পড়ানোর ধরন আর বেতন (প্রোফাইলের Edit intro থেকে) */
    public function GetTeachPrefs(int $userId): Obj
    {
        $o = obj(['TeachMode' => '', 'FeeMin' => null, 'FeeMax' => null]);
        try {
            $this->EnsureTutorDirectory();
            $r = $this->one('SELECT `TeachMode`, `FeeMin`, `FeeMax` FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]);
            if ($r) {
                $o->TeachMode = in_array($r->TeachMode, self::TEACH_MODES, true) ? $r->TeachMode : '';
                $o->FeeMin = $r->FeeMin !== null ? (int)$r->FeeMin : null;
                $o->FeeMax = $r->FeeMax !== null ? (int)$r->FeeMax : null;
            }
        } catch (Throwable $e) { }
        return $o;
    }

    public function SaveTeachPrefs(int $userId, string $mode, ?int $feeMin, ?int $feeMax): void
    {
        $this->EnsureTutorDirectory();
        $exists = (int)$this->scalar('SELECT COUNT(*) FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]) > 0;
        if (!$exists) $this->insert('INSERT INTO `UserProfileExtras` (`UserId`, `CoverPosY`, `UpdatedAt`) VALUES (:u, 50, :t)', ['u' => $userId, 't' => now()]);
        $this->exec('UPDATE `UserProfileExtras` SET `TeachMode` = :m, `FeeMin` = :a, `FeeMax` = :b WHERE `UserId` = :u',
            ['m' => in_array($mode, self::TEACH_MODES, true) ? $mode : null, 'a' => $feeMin, 'b' => $feeMax, 'u' => $userId]);
    }

    /**
     * Find Tutor পেজের টিউটর তালিকা — সব আসল তথ্য।
     * @return Obj[]
     */
    public function GetTutorDirectory(): array
    {
        try {
            $this->EnsureTutorDirectory();
            $rows = $this->all("SELECT tr.`Id`, tr.`UserId`, tr.`FullName`, tr.`Education`, tr.`Institution`, tr.`SubjectExpertise`, tr.`Experience`,
                    tr.`TeachingStyle`, tr.`AvailableDays`, tr.`PreferredTime`, tr.`HourlyRate`, tr.`WhyTeach`, tr.`RequestDate`,
                    u.`FullName` AS UName, u.`ProfileImageLink`, u.`Gender`, u.`Country`, u.`YearsOfExperience`, u.`IsActive`, u.`CreatedAt` AS UCreated,
                    x.`City`, x.`Subjects` AS XSubjects, x.`Levels`, x.`Headline`, x.`Bio`, x.`TeachMode`, x.`FeeMin`, x.`FeeMax`
                FROM `TeacherRequests` tr
                INNER JOIN `Users` u ON u.`Id` = tr.`UserId`
                LEFT JOIN `UserProfileExtras` x ON x.`UserId` = tr.`UserId`
                WHERE tr.`Status` = 'Approved' AND u.`Role` = 'Teacher'
                ORDER BY tr.`Id` DESC");
            $ratings = [];
            foreach ($this->all('SELECT `TeacherId`, AVG(`Rating`) AS A, COUNT(*) AS N FROM `TeacherReviews` GROUP BY `TeacherId`') as $r) {
                $ratings[(int)$r->TeacherId] = [round((float)$r->A, 1), (int)$r->N];
            }
            $students = [];
            foreach ($this->all("SELECT `TeacherId`, COUNT(*) AS N FROM `TeacherStudents` GROUP BY `TeacherId`") as $r) $students[(int)$r->TeacherId] = (int)$r->N;
        } catch (Throwable $e) { error_log('[GetTutorDirectory] ' . $e->getMessage()); return []; }

        $split = fn($s) => array_values(array_unique(array_filter(array_map(fn($x) => mb_substr(trim($x), 0, 30), preg_split('#[,/|;&]+#u', (string)$s)), fn($x) => $x !== '')));
        $seen = [];
        $out = [];
        try { $susp = $this->GetActiveSuspensionsMap(); } catch (Throwable $e) { $susp = []; }
        // পেমেন্টের নিয়ম চালু থাকলে শুধু Subscription চালু থাকা শিক্ষকরা দেখাবে
        $subOnly = null;
        try { if ($this->PaymentRules()['teacher']) $subOnly = $this->GetActiveTeacherSubscriptionMap(); } catch (Throwable $e) { }
        foreach ($rows as $r) {
            $uid = (int)$r->UserId;
            if (isset($seen[$uid]) || !(int)$r->IsActive) continue;
            $seen[$uid] = true;
            if (isset($susp[$uid])) continue;
            if ($subOnly !== null && !isset($subOnly[$uid])) continue;
            $name = !is_blank($r->UName) ? (string)$r->UName : ((string)$r->FullName ?: 'Tutor');
            $subjects = $split($r->XSubjects) ?: $split($r->SubjectExpertise);
            $levels = $split($r->Levels);
            $mediums = array_values(array_filter($levels, fn($l) => stripos($l, 'medium') !== false));
            $classes = array_values(array_filter($levels, fn($l) => stripos($l, 'medium') === false));
            $expRaw = !is_blank($r->Experience) ? (string)$r->Experience : (string)($r->YearsOfExperience ?? '');
            $exp = preg_match('/(\d+)/', $expRaw, $m) ? (int)$m[1] : 0;
            // বেতন: প্রোফাইলে দেওয়া রেঞ্জ, না থাকলে আবেদনের "Expected Salary" থেকে সংখ্যা
            $feeMin = $r->FeeMin !== null ? (int)$r->FeeMin : null;
            $feeMax = $r->FeeMax !== null ? (int)$r->FeeMax : null;
            if ($feeMin === null && preg_match_all('/\d[\d,]*/', (string)$r->HourlyRate, $mm)) {
                $nums = array_map(fn($n) => (int)str_replace(',', '', $n), $mm[0]);
                $nums = array_values(array_filter($nums, fn($n) => $n >= 100));
                if ($nums) { $feeMin = min($nums); $feeMax = count($nums) > 1 ? max($nums) : null; }
            }
            [$avg, $cnt] = $ratings[$uid] ?? [0.0, 0];
            $mode = in_array($r->TeachMode, self::TEACH_MODES, true) ? $r->TeachMode : '';
            $location = implode(', ', array_filter([trim((string)$r->City), trim((string)$r->Country)]));
            $out[] = obj([
                'Id' => (int)$r->Id, 'UserId' => $uid, 'TutorId' => 'TCH-' . (1000 + (int)$r->Id),
                'FullName' => $name, 'Initials' => initials($name, 'T'),
                'Gender' => !is_blank($r->Gender) ? (string)$r->Gender : 'Any',
                'PhotoUrl' => (string)($r->ProfileImageLink ?? ''),
                'Headline' => (string)($r->Headline ?? ''),
                'Subjects' => $subjects ?: ['General'],
                'Classes' => $classes ?: ['All Classes'], 'Levels' => $classes, 'Mediums' => $mediums,
                'Qualification' => !is_blank($r->Education) ? (string)$r->Education : 'Not specified',
                'Institution' => !is_blank($r->Institution) ? (string)$r->Institution : 'Not specified',
                'ExperienceYears' => $exp,
                'Location' => $location !== '' ? $location : 'Bangladesh', 'City' => trim((string)$r->City),
                'TeachingMode' => $mode !== '' ? $mode : 'Both',
                'MonthlyFee' => $feeMin ?? 0, 'FeeMax' => $feeMax ?? 0,
                'Rating' => $avg, 'TotalReviews' => $cnt, 'Students' => $students[$uid] ?? 0,
                'AvailableTime' => !is_blank($r->PreferredTime) ? $r->PreferredTime : ((string)$r->AvailableDays ?: 'Contact for schedule'),
                'About' => !is_blank($r->Bio) ? $r->Bio : (!is_blank($r->WhyTeach) ? $r->WhyTeach : (!is_blank($r->TeachingStyle) ? $r->TeachingStyle : 'Experienced tutor ready to help students excel.')),
                'IsVerified' => true, 'IsTopRated' => false,
                'JoinedAt' => $r->RequestDate ?? $r->UCreated,
            ]);
        }
        // TOP: অন্তত ৩টা রিভিউ আর ৪.৫+ রেটিং, সেরা ৩ জন
        $rank = array_filter($out, fn($t) => $t->TotalReviews >= 3 && $t->Rating >= 4.5);
        usort($rank, fn($a, $b) => [$b->Rating, $b->TotalReviews] <=> [$a->Rating, $a->TotalReviews]);
        foreach (array_slice($rank, 0, 3) as $t) $t->IsTopRated = true;
        return $out;
    }
}
