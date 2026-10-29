<?php
/**
 * টিচার প্রোফাইলের ডাটা (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - TeacherStudents     : টিচারের স্টুডেন্ট (Accept হওয়া অ্যাপ্লিকেশন থেকে নিজে আসে + টিচার নিজে যোগ করতে পারে)
 *  - TeacherClasses      : ক্লাস রুটিন (সাপ্তাহিক দিন অথবা নির্দিষ্ট তারিখ)
 *  - TeacherPayments     : মাসিক আয় (টিচার নিজে লিখে রাখে)
 *  - TeacherReviews      : স্টুডেন্টদের রিভিউ (১–৫ স্টার)
 *  - TeacherCertificates : সার্টিফিকেট (ছবি / PDF)
 * সব টেবিল দরকার হলে নিজে থেকেই তৈরি হয় — আলাদা SQL Import লাগে না।
 */
trait TeacherProfileData
{
    private bool $teacherReady = false;

    public function EnsureTeacherTables(): void
    {
        if ($this->teacherReady) return;
        $this->EnsureLearningTables();
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        foreach (['Headline' => 'VARCHAR(80)', 'Levels' => 'VARCHAR(200)', 'LastSeenAt' => 'DATETIME'] as $col => $type) {
            if (!$this->columnExists('UserProfileExtras', $col)) {
                try { $this->pdo->exec("ALTER TABLE `UserProfileExtras` ADD COLUMN `$col` " . ($sqlite ? 'TEXT' : $type) . ' NULL'); } catch (Throwable $e) { error_log("[extras $col] " . $e->getMessage()); }
            }
        }
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherStudents" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "StudentUserId" INTEGER NULL,
                "Name" TEXT NOT NULL, "ClassLevel" TEXT NULL, "Subject" TEXT NULL, "Phone" TEXT NULL, "MonthlyFee" INTEGER NULL, "Status" TEXT NOT NULL DEFAULT \'Active\',
                "Note" TEXT NULL, "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherClasses" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "Title" TEXT NOT NULL,
                "Days" TEXT NULL, "ClassDate" TEXT NULL, "StartTime" TEXT NOT NULL, "EndTime" TEXT NOT NULL, "MeetLink" TEXT NULL, "Location" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherPayments" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "StudentId" INTEGER NULL,
                "Amount" INTEGER NOT NULL, "Month" TEXT NOT NULL, "PaidOn" TEXT NULL, "Note" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherReviews" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "StudentUserId" INTEGER NOT NULL,
                "Rating" INTEGER NOT NULL, "Comment" TEXT NULL, "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL, UNIQUE ("TeacherId", "StudentUserId"))');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TeacherCertificates" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "Title" TEXT NOT NULL,
                "Issuer" TEXT NULL, "Year" TEXT NULL, "FileUrl" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $e = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherStudents` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `StudentUserId` INT NULL,
                `Name` VARCHAR(80) NOT NULL, `ClassLevel` VARCHAR(60) NULL, `Subject` VARCHAR(80) NULL, `Phone` VARCHAR(20) NULL, `MonthlyFee` INT NULL,
                `Status` VARCHAR(10) NOT NULL DEFAULT 'Active', `Note` VARCHAR(200) NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_TS_Teacher` (`TeacherId`), KEY `IX_TS_Student` (`StudentUserId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherClasses` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `Title` VARCHAR(100) NOT NULL,
                `Days` VARCHAR(20) NULL, `ClassDate` DATE NULL, `StartTime` CHAR(5) NOT NULL, `EndTime` CHAR(5) NOT NULL, `MeetLink` VARCHAR(300) NULL, `Location` VARCHAR(100) NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), KEY `IX_TC_Teacher` (`TeacherId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherPayments` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `StudentId` INT NULL,
                `Amount` INT NOT NULL, `Month` CHAR(7) NOT NULL, `PaidOn` DATE NULL, `Note` VARCHAR(150) NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`), KEY `IX_TP_TeacherMonth` (`TeacherId`, `Month`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherReviews` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `StudentUserId` INT NOT NULL,
                `Rating` TINYINT NOT NULL, `Comment` TEXT NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_TR` (`TeacherId`, `StudentUserId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TeacherCertificates` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `Title` VARCHAR(120) NOT NULL,
                `Issuer` VARCHAR(120) NULL, `Year` VARCHAR(10) NULL, `FileUrl` VARCHAR(500) NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`), KEY `IX_TCert_Teacher` (`TeacherId`)) $e");
        }
        $this->teacherReady = true;
    }

    // ---------------- Intro (Headline / Levels) + online ----------------
    /** টিচারের হেডলাইন ("Mathematics Tutor") আর লেভেল (SSC, HSC...) */
    public function GetTeacherIntro(int $userId): Obj
    {
        $out = obj(['Headline' => '', 'Levels' => [], 'LastSeenAt' => null]);
        try {
            $this->EnsureTeacherTables();
            $r = $this->one('SELECT `Headline`, `Levels`, `LastSeenAt` FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]);
            if ($r) {
                $out->Headline = (string)($r->Headline ?? '');
                $out->Levels = array_values(array_filter(array_map('trim', explode(',', (string)($r->Levels ?? ''))), fn($x) => $x !== ''));
                $out->LastSeenAt = $r->LastSeenAt;
            }
        } catch (Throwable $e) { error_log('[GetTeacherIntro] ' . $e->getMessage()); }
        return $out;
    }

    public function SaveTeacherIntro(int $userId, string $headline, array $levels): void
    {
        $this->EnsureTeacherTables();
        $exists = (int)$this->scalar('SELECT COUNT(*) FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]) > 0;
        if (!$exists) $this->insert('INSERT INTO `UserProfileExtras` (`UserId`, `CoverPosY`, `UpdatedAt`) VALUES (:u, 50, :t)', ['u' => $userId, 't' => now()]);
        $this->exec('UPDATE `UserProfileExtras` SET `Headline` = :h, `Levels` = :l WHERE `UserId` = :u', ['h' => $headline, 'l' => implode(', ', $levels), 'u' => $userId]);
    }

    /** "অনলাইন" দেখানোর জন্য শেষ কখন সাইটে ছিল */
    public function TouchLastSeen(int $userId): void
    {
        try {
            $this->EnsureTeacherTables();
            $n = $this->exec('UPDATE `UserProfileExtras` SET `LastSeenAt` = :t WHERE `UserId` = :u', ['t' => now(), 'u' => $userId]);
            if ($n === 0) $this->insert('INSERT INTO `UserProfileExtras` (`UserId`, `CoverPosY`, `LastSeenAt`) VALUES (:u, 50, :t)', ['u' => $userId, 't' => now()]);
        } catch (Throwable $e) { /* না হলেও সমস্যা নেই */ }
    }

    // ---------------- Students ----------------
    /** Accept হওয়া অ্যাপ্লিকেশনের স্টুডেন্টদের তালিকায় যোগ করে (একবারই) */
    public function SyncTeacherStudents(int $teacherId): void
    {
        try {
            $this->EnsureTeacherTables();
            $this->EnsureCircularApplicationsTable();
            $rows = $this->all("SELECT p.`UserId` AS Sid, MAX(p.`ClassLevel`) AS Cls, MAX(p.`Subjects`) AS Subj, MAX(u.`FullName`) AS Nm, MAX(u.`UserName`) AS Un, MAX(u.`MobileNumber`) AS Ph
                FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId` LEFT JOIN `Users` u ON u.`Id` = p.`UserId`
                WHERE a.`TeacherId` = :t AND a.`Status` = 'Accepted' GROUP BY p.`UserId`", ['t' => $teacherId]);
            foreach ($rows as $r) {
                $has = (int)$this->scalar('SELECT COUNT(*) FROM `TeacherStudents` WHERE `TeacherId` = :t AND `StudentUserId` = :s', ['t' => $teacherId, 's' => (int)$r->Sid]);
                if ($has) continue;
                $this->insert('INSERT INTO `TeacherStudents` (`TeacherId`, `StudentUserId`, `Name`, `ClassLevel`, `Subject`, `Phone`, `Status`, `CreatedAt`)
                    VALUES (:t, :s, :n, :c, :sub, :p, \'Active\', :now)', ['t' => $teacherId, 's' => (int)$r->Sid,
                    'n' => mb_substr(!is_blank($r->Nm) ? $r->Nm : (string)$r->Un, 0, 80), 'c' => mb_substr((string)$r->Cls, 0, 60) ?: null,
                    'sub' => mb_substr((string)$r->Subj, 0, 80) ?: null, 'p' => mb_substr((string)$r->Ph, 0, 20) ?: null, 'now' => now()]);
            }
        } catch (Throwable $e) { error_log('[SyncTeacherStudents] ' . $e->getMessage()); }
    }

    /** @return Obj[] নতুন আগে */
    public function GetTeacherStudents(int $teacherId): array
    {
        try {
            $this->EnsureTeacherTables();
            $rows = $this->all('SELECT s.*, u.`ProfileImageLink` AS Img, u.`FullName` AS UName FROM `TeacherStudents` s LEFT JOIN `Users` u ON u.`Id` = s.`StudentUserId`
                WHERE s.`TeacherId` = :t ORDER BY s.`Id` DESC', ['t' => $teacherId]);
            return array_map(function ($r) {
                $name = !is_blank($r->UName) ? (string)$r->UName : (string)$r->Name;
                return obj(['Id' => (int)$r->Id, 'StudentUserId' => $r->StudentUserId !== null ? (int)$r->StudentUserId : null, 'Name' => $name,
                    'ClassLevel' => (string)($r->ClassLevel ?? ''), 'Subject' => (string)($r->Subject ?? ''), 'Phone' => (string)($r->Phone ?? ''),
                    'MonthlyFee' => $r->MonthlyFee !== null ? (int)$r->MonthlyFee : null, 'Status' => $r->Status === 'Inactive' ? 'Inactive' : 'Active',
                    'Note' => (string)($r->Note ?? ''), 'Image' => is_blank($r->Img) ? null : (string)$r->Img, 'Initials' => initials($name, 'S'), 'CreatedAt' => $r->CreatedAt]);
            }, $rows);
        } catch (Throwable $e) { error_log('[GetTeacherStudents] ' . $e->getMessage()); return []; }
    }

    public function SaveTeacherStudent(int $teacherId, ?int $id, array $f): int
    {
        $this->EnsureTeacherTables();
        $p = ['n' => $f['Name'], 'c' => $f['ClassLevel'] ?: null, 's' => $f['Subject'] ?: null, 'p' => $f['Phone'] ?: null,
              'fee' => $f['MonthlyFee'], 'st' => $f['Status'], 'no' => $f['Note'] ?: null, 'now' => now(), 't' => $teacherId];
        if ($id) {
            $this->exec('UPDATE `TeacherStudents` SET `Name` = :n, `ClassLevel` = :c, `Subject` = :s, `Phone` = :p, `MonthlyFee` = :fee, `Status` = :st, `Note` = :no,
                `UpdatedAt` = :now WHERE `Id` = :id AND `TeacherId` = :t', $p + ['id' => $id]);
            return $id;
        }
        return $this->insert('INSERT INTO `TeacherStudents` (`TeacherId`, `Name`, `ClassLevel`, `Subject`, `Phone`, `MonthlyFee`, `Status`, `Note`, `CreatedAt`)
            VALUES (:t, :n, :c, :s, :p, :fee, :st, :no, :now)', $p);
    }

    public function OwnsRow(string $table, int $id, int $teacherId): bool
    {
        if (!in_array($table, ['TeacherStudents', 'TeacherClasses', 'TeacherPayments', 'TeacherCertificates'], true)) return false;
        $this->EnsureTeacherTables();
        return (int)$this->scalar("SELECT COUNT(*) FROM `$table` WHERE `Id` = :id AND `TeacherId` = :t", ['id' => $id, 't' => $teacherId]) > 0;
    }

    public function DeleteTeacherRow(string $table, int $id, int $teacherId): bool
    {
        if (!$this->OwnsRow($table, $id, $teacherId)) return false;
        if ($table === 'TeacherStudents') $this->exec('UPDATE `TeacherPayments` SET `StudentId` = NULL WHERE `StudentId` = :id AND `TeacherId` = :t', ['id' => $id, 't' => $teacherId]);
        return $this->exec("DELETE FROM `$table` WHERE `Id` = :id AND `TeacherId` = :t", ['id' => $id, 't' => $teacherId]) > 0;
    }

    public function CountTeacherRows(string $table, int $teacherId): int
    {
        $this->EnsureTeacherTables();
        return (int)$this->scalar("SELECT COUNT(*) FROM `$table` WHERE `TeacherId` = :t", ['t' => $teacherId]);
    }

    // ---------------- Schedule ----------------
    /** @return Obj[] */
    public function GetTeacherClasses(int $teacherId): array
    {
        try {
            $this->EnsureTeacherTables();
            return array_map(fn($r) => obj(['Id' => (int)$r->Id, 'Title' => (string)$r->Title,
                'Days' => array_values(array_filter(array_map('intval', array_filter(explode(',', (string)$r->Days), 'strlen')), fn($d) => $d >= 0 && $d <= 6)),
                'ClassDate' => is_blank($r->ClassDate) ? null : substr((string)$r->ClassDate, 0, 10), 'StartTime' => substr((string)$r->StartTime, 0, 5),
                'EndTime' => substr((string)$r->EndTime, 0, 5), 'MeetLink' => (string)($r->MeetLink ?? ''), 'Location' => (string)($r->Location ?? '')]),
                $this->all('SELECT * FROM `TeacherClasses` WHERE `TeacherId` = :t ORDER BY `StartTime`', ['t' => $teacherId]));
        } catch (Throwable $e) { return []; }
    }

    public function SaveTeacherClass(int $teacherId, ?int $id, array $f): int
    {
        $this->EnsureTeacherTables();
        $p = ['ti' => $f['Title'], 'd' => $f['Days'] ? implode(',', $f['Days']) : null, 'cd' => $f['ClassDate'], 's' => $f['StartTime'], 'e' => $f['EndTime'],
              'm' => $f['MeetLink'] ?: null, 'l' => $f['Location'] ?: null, 't' => $teacherId];
        if ($id) {
            $this->exec('UPDATE `TeacherClasses` SET `Title` = :ti, `Days` = :d, `ClassDate` = :cd, `StartTime` = :s, `EndTime` = :e, `MeetLink` = :m, `Location` = :l
                WHERE `Id` = :id AND `TeacherId` = :t', $p + ['id' => $id]);
            return $id;
        }
        return $this->insert('INSERT INTO `TeacherClasses` (`TeacherId`, `Title`, `Days`, `ClassDate`, `StartTime`, `EndTime`, `MeetLink`, `Location`, `CreatedAt`)
            VALUES (:t, :ti, :d, :cd, :s, :e, :m, :l, :now)', $p + ['now' => now()]);
    }

    /**
     * আজ থেকে $days দিনের মধ্যে ক্লাসগুলো কবে কবে হবে (শেষ হয়ে যাওয়া বাদ)।
     * @return Obj[] Class, Date (Y-m-d), Start, End (timestamp), IsLive, IsToday
     */
    public function UpcomingClassOccurrences(array $classes, int $days = 7, int $limit = 50): array
    {
        $out = [];
        $now = time();
        for ($i = 0; $i < $days; $i++) {
            $ts = strtotime("+$i days", strtotime('today'));
            $date = date('Y-m-d', $ts); $dow = (int)date('w', $ts);
            foreach ($classes as $c) {
                $hit = $c->ClassDate ? $c->ClassDate === $date : in_array($dow, $c->Days, true);
                if (!$hit) continue;
                $start = strtotime("$date {$c->StartTime}"); $end = strtotime("$date {$c->EndTime}");
                if ($end <= $start) $end = $start + 3600;
                if ($end < $now) continue;
                $out[] = obj(['Class' => $c, 'Date' => $date, 'Start' => $start, 'End' => $end, 'IsLive' => $start - 600 <= $now && $now <= $end, 'IsToday' => $i === 0]);
            }
        }
        usort($out, fn($a, $b) => $a->Start <=> $b->Start);
        return array_slice($out, 0, $limit);
    }

    /** আজকের মোট ক্লাস (শেষ হয়ে যাওয়াগুলোসহ) */
    public function CountTodayClasses(array $classes): int
    {
        $date = date('Y-m-d'); $dow = (int)date('w');
        return count(array_filter($classes, fn($c) => $c->ClassDate ? $c->ClassDate === $date : in_array($dow, $c->Days, true)));
    }

    // ---------------- Earnings ----------------
    /** @return Obj[] */
    public function GetTeacherPayments(int $teacherId, int $limit = 200): array
    {
        try {
            $this->EnsureTeacherTables();
            return array_map(fn($r) => obj(['Id' => (int)$r->Id, 'StudentId' => $r->StudentId !== null ? (int)$r->StudentId : null, 'StudentName' => (string)($r->SName ?? ''),
                'Amount' => (int)$r->Amount, 'Month' => (string)$r->Month, 'PaidOn' => $r->PaidOn ? substr((string)$r->PaidOn, 0, 10) : null, 'Note' => (string)($r->Note ?? '')]),
                $this->all('SELECT p.*, s.`Name` AS SName FROM `TeacherPayments` p LEFT JOIN `TeacherStudents` s ON s.`Id` = p.`StudentId`
                    WHERE p.`TeacherId` = :t ORDER BY p.`Month` DESC, p.`Id` DESC LIMIT ' . (int)$limit, ['t' => $teacherId]));
        } catch (Throwable $e) { return []; }
    }

    public function SaveTeacherPayment(int $teacherId, ?int $id, ?int $studentId, int $amount, string $month, ?string $paidOn, string $note): int
    {
        $this->EnsureTeacherTables();
        $p = ['s' => $studentId, 'a' => $amount, 'm' => $month, 'd' => $paidOn, 'n' => $note ?: null, 't' => $teacherId];
        if ($id) {
            $this->exec('UPDATE `TeacherPayments` SET `StudentId` = :s, `Amount` = :a, `Month` = :m, `PaidOn` = :d, `Note` = :n WHERE `Id` = :id AND `TeacherId` = :t', $p + ['id' => $id]);
            return $id;
        }
        return $this->insert('INSERT INTO `TeacherPayments` (`TeacherId`, `StudentId`, `Amount`, `Month`, `PaidOn`, `Note`, `CreatedAt`) VALUES (:t, :s, :a, :m, :d, :n, :now)', $p + ['now' => now()]);
    }

    /** @return array{this:int, last:int, pct:?int, months: array<string,int>, total:int} */
    public function EarningsSummary(int $teacherId): array
    {
        $this->EnsureTeacherTables();
        $months = [];
        for ($i = 5; $i >= 0; $i--) $months[date('Y-m', strtotime("first day of -$i month"))] = 0;
        try {
            foreach ($this->all('SELECT `Month`, SUM(`Amount`) AS S FROM `TeacherPayments` WHERE `TeacherId` = :t GROUP BY `Month`', ['t' => $teacherId]) as $r) {
                if (array_key_exists($r->Month, $months)) $months[$r->Month] = (int)$r->S;
            }
            $total = (int)$this->scalar('SELECT COALESCE(SUM(`Amount`), 0) FROM `TeacherPayments` WHERE `TeacherId` = :t', ['t' => $teacherId]);
        } catch (Throwable $e) { $total = 0; }
        $keys = array_keys($months);
        $this1 = $months[$keys[5]]; $last = $months[$keys[4]];
        return ['this' => $this1, 'last' => $last, 'pct' => $last > 0 ? (int)round(($this1 - $last) / $last * 100) : null, 'months' => $months, 'total' => $total];
    }

    // ---------------- Reviews ----------------
    /** রিভিউ দিতে পারবে: যার পোস্টে টিচারের অ্যাপ্লিকেশন Accept হয়েছে, অথবা টিচারের স্টুডেন্ট তালিকায় লিংক করা আছে */
    public function CanReviewTeacher(int $studentId, int $teacherId): bool
    {
        if ($studentId === $teacherId) return false;
        try {
            $this->EnsureTeacherTables();
            $this->EnsureCircularApplicationsTable();
            $a = (int)$this->scalar("SELECT COUNT(*) FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId`
                WHERE a.`TeacherId` = :t AND p.`UserId` = :s AND a.`Status` = 'Accepted'", ['t' => $teacherId, 's' => $studentId]);
            if ($a > 0) return true;
            return (int)$this->scalar('SELECT COUNT(*) FROM `TeacherStudents` WHERE `TeacherId` = :t AND `StudentUserId` = :s', ['t' => $teacherId, 's' => $studentId]) > 0;
        } catch (Throwable $e) { return false; }
    }

    /** @return Obj[] */
    public function GetTeacherReviews(int $teacherId, int $limit = 100): array
    {
        try {
            $this->EnsureTeacherTables();
            return array_map(function ($r) {
                $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)($r->UserName ?? 'Student');
                return obj(['Id' => (int)$r->Id, 'StudentUserId' => (int)$r->StudentUserId, 'Name' => $name, 'Image' => is_blank($r->ProfileImageLink) ? null : (string)$r->ProfileImageLink,
                    'Initials' => initials($name, 'S'), 'Rating' => max(1, min(5, (int)$r->Rating)), 'Comment' => (string)($r->Comment ?? ''),
                    'TimeAgo' => time_ago($r->UpdatedAt ?: $r->CreatedAt), 'Edited' => !is_blank($r->UpdatedAt), 'Level' => (string)($r->EducationLevel ?? '')]);
            }, $this->all('SELECT r.*, u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`EducationLevel` FROM `TeacherReviews` r INNER JOIN `Users` u ON u.`Id` = r.`StudentUserId`
                WHERE r.`TeacherId` = :t ORDER BY COALESCE(r.`UpdatedAt`, r.`CreatedAt`) DESC LIMIT ' . (int)$limit, ['t' => $teacherId]));
        } catch (Throwable $e) { return []; }
    }

    /** @return array{avg: float, count:int, dist: array<int,int>} */
    public function TeacherReviewStats(int $teacherId): array
    {
        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        try {
            $this->EnsureTeacherTables();
            foreach ($this->all('SELECT `Rating`, COUNT(*) AS N FROM `TeacherReviews` WHERE `TeacherId` = :t GROUP BY `Rating`', ['t' => $teacherId]) as $r) {
                $dist[max(1, min(5, (int)$r->Rating))] += (int)$r->N;
            }
        } catch (Throwable $e) { }
        $count = array_sum($dist);
        $sum = 0; foreach ($dist as $k => $n) $sum += $k * $n;
        return ['avg' => $count ? round($sum / $count, 1) : 0.0, 'count' => $count, 'dist' => $dist];
    }

    public function GetMyTeacherReview(int $teacherId, int $studentId): ?Obj
    {
        $this->EnsureTeacherTables();
        return $this->one('SELECT * FROM `TeacherReviews` WHERE `TeacherId` = :t AND `StudentUserId` = :s', ['t' => $teacherId, 's' => $studentId]);
    }

    public function SaveTeacherReview(int $teacherId, int $studentId, int $rating, string $comment): bool
    {
        $this->EnsureTeacherTables();
        $ex = $this->GetMyTeacherReview($teacherId, $studentId);
        if ($ex) {
            return $this->exec('UPDATE `TeacherReviews` SET `Rating` = :r, `Comment` = :c, `UpdatedAt` = :n WHERE `Id` = :id',
                ['r' => $rating, 'c' => $comment ?: null, 'n' => now(), 'id' => (int)$ex->Id]) >= 0;
        }
        $this->insert('INSERT INTO `TeacherReviews` (`TeacherId`, `StudentUserId`, `Rating`, `Comment`, `CreatedAt`) VALUES (:t, :s, :r, :c, :n)',
            ['t' => $teacherId, 's' => $studentId, 'r' => $rating, 'c' => $comment ?: null, 'n' => now()]);
        return true;
    }

    public function DeleteTeacherReview(int $reviewId, ?int $studentId): bool
    {
        $this->EnsureTeacherTables();
        if ($studentId === null) return $this->exec('DELETE FROM `TeacherReviews` WHERE `Id` = :id', ['id' => $reviewId]) > 0; // অ্যাডমিন
        return $this->exec('DELETE FROM `TeacherReviews` WHERE `Id` = :id AND `StudentUserId` = :s', ['id' => $reviewId, 's' => $studentId]) > 0;
    }

    // ---------------- Certificates ----------------
    /** @return Obj[] */
    public function GetTeacherCertificates(int $teacherId): array
    {
        try {
            $this->EnsureTeacherTables();
            return array_map(fn($r) => obj(['Id' => (int)$r->Id, 'Title' => (string)$r->Title, 'Issuer' => (string)($r->Issuer ?? ''), 'Year' => (string)($r->Year ?? ''),
                'FileUrl' => is_blank($r->FileUrl) ? null : (string)$r->FileUrl,
                'IsPdf' => !is_blank($r->FileUrl) && str_ends_with(strtolower((string)$r->FileUrl), '.pdf')]),
                $this->all('SELECT * FROM `TeacherCertificates` WHERE `TeacherId` = :t ORDER BY `Id` DESC', ['t' => $teacherId]));
        } catch (Throwable $e) { return []; }
    }

    public function GetTeacherCertificate(int $id): ?Obj
    {
        $this->EnsureTeacherTables();
        return $this->one('SELECT * FROM `TeacherCertificates` WHERE `Id` = :id', ['id' => $id]);
    }

    public function AddTeacherCertificate(int $teacherId, string $title, string $issuer, string $year, ?string $fileUrl): int
    {
        $this->EnsureTeacherTables();
        return $this->insert('INSERT INTO `TeacherCertificates` (`TeacherId`, `Title`, `Issuer`, `Year`, `FileUrl`, `CreatedAt`) VALUES (:t, :ti, :i, :y, :f, :n)',
            ['t' => $teacherId, 'ti' => $title, 'i' => $issuer ?: null, 'y' => $year ?: null, 'f' => $fileUrl, 'n' => now()]);
    }

    /** দর্শক যতগুলো Study Material দেখতে পায় */
    public function CountTeacherMaterials(int $teacherId, ?int $viewerId, bool $isAdmin): int
    {
        try {
            $this->EnsureProfilePostTables();
            $sql = "SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :o AND `PostType` = 'material' AND `SharedPostId` IS NULL";
            if (!$isAdmin && $viewerId !== $teacherId) {
                $sql .= $viewerId !== null && $this->GetFriendRelation($viewerId, $teacherId) === 'friends' ? " AND `Privacy` IN ('Public','Friends')" : " AND `Privacy` = 'Public'";
            }
            return (int)$this->scalar($sql, ['o' => $teacherId]);
        } catch (Throwable $e) { return 0; }
    }

    // ---------------- Find Tutor posts ----------------
    /** টিচারের Find Tutor পেজের পোস্ট @return Obj[] */
    public function GetTeacherTeachPosts(int $teacherId): array
    {
        try {
            return array_map(fn($r) => obj(['Id' => (int)$r->Id, 'Content' => (string)$r->Content, 'Image' => is_blank($r->ImageUrl) ? null : (string)$r->ImageUrl,
                'Likes' => (int)$r->LikeCount, 'CreatedAt' => $r->CreatedAt, 'Comments' => (int)$r->Cm]),
                $this->all('SELECT p.*, (SELECT COUNT(*) FROM `TeachWantedComments` c WHERE c.`PostId` = p.`Id`) AS Cm FROM `TeachWantedPosts` p WHERE p.`UserId` = :u ORDER BY p.`Id` DESC', ['u' => $teacherId]));
        } catch (Throwable $e) { return []; }
    }

    /** ইউজার মুছে গেলে টিচার ডাটা মুছে ফেলা */
    public function DeleteTeacherDataOf(int $userId): void
    {
        try {
            $this->EnsureTeacherTables();
            foreach ($this->all('SELECT `FileUrl` FROM `TeacherCertificates` WHERE `TeacherId` = :u', ['u' => $userId]) as $c) delete_upload($c->FileUrl);
            foreach (['TeacherStudents', 'TeacherClasses', 'TeacherPayments', 'TeacherCertificates'] as $t) $this->exec("DELETE FROM `$t` WHERE `TeacherId` = :u", ['u' => $userId]);
            $this->exec('DELETE FROM `TeacherReviews` WHERE `TeacherId` = :a OR `StudentUserId` = :b', ['a' => $userId, 'b' => $userId]);
        } catch (Throwable $e) { error_log('[DeleteTeacherDataOf] ' . $e->getMessage()); }
    }
}
