<?php
/**
 * কোর্স (টিচারের নিজের কোর্স বিক্রি) — Db ক্লাসে trait হিসেবে যুক্ত।
 *  Courses            : কোর্স (টিচার, দাম, বিবরণ, কী কী থাকবে, থাম্বনেইল, Draft/Published, অ্যাডমিন লুকাতে পারে)
 *  CourseLessons      : ক্লাস — recorded (BigBlueButton এ রেকর্ড, VPS এ থাকে) বা live; আনলক হওয়ার সময়
 *  CourseTasks        : প্রতিটা ক্লাসের পর অ্যাসাইনমেন্ট (লিংক জমা) বা কুইজ (MCQ)
 *  CourseSubmissions  : শিক্ষার্থীর জমা / কুইজের ফল
 *  CourseEnrollments  : কে কোন কোর্স কিনেছে — দাম, টিচারের ভাগ, অ্যাডমিনের ভাগ, সার্টিফিকেট
 *  CourseProgress     : কোন ক্লাস শেষ করেছে
 *  CoursePayouts      : অ্যাডমিন টিচারকে কত টাকা দিয়েছে
 *  PaymentRequests.CourseId : কোর্স কেনার পেমেন্ট (Audience = 'Course')
 * সব টেবিল / কলাম নিজে থেকেই তৈরি হয় — SQL ইমপোর্ট লাগবে না।
 */
trait CourseData
{
    private bool $courseReady = false;

    public function EnsureCourseTables(): void
    {
        if ($this->courseReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if ($sqlite) {
            foreach ([
                'CREATE TABLE IF NOT EXISTS "Courses" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "Title" TEXT NOT NULL, "Subtitle" TEXT NULL,
                    "Description" TEXT NULL, "Includes" TEXT NULL, "Category" TEXT NULL, "Level" TEXT NULL, "Price" INTEGER NOT NULL DEFAULT 0, "RegularPrice" INTEGER NULL,
                    "Thumbnail" TEXT NULL, "Status" TEXT NOT NULL DEFAULT \'draft\', "AdminHidden" INTEGER NOT NULL DEFAULT 0, "PassPercent" INTEGER NOT NULL DEFAULT 60,
                    "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)',
                'CREATE TABLE IF NOT EXISTS "CourseLessons" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "CourseId" INTEGER NOT NULL, "Title" TEXT NOT NULL, "Description" TEXT NULL,
                    "Kind" TEXT NOT NULL DEFAULT \'recorded\', "SortOrder" INTEGER NOT NULL DEFAULT 0, "UnlockAt" TEXT NULL, "StartsAt" TEXT NULL, "DurationMin" INTEGER NOT NULL DEFAULT 60,
                    "MeetingId" TEXT NULL, "RecordingUrl" TEXT NULL, "RecordingMin" INTEGER NULL, "RecCheckedAt" TEXT NULL, "LiveStartedAt" TEXT NULL, "CreatedBy" INTEGER NULL, "CreatedAt" TEXT NOT NULL)',
                'CREATE INDEX IF NOT EXISTS "IX_CL_Course" ON "CourseLessons" ("CourseId", "SortOrder")',
                'CREATE TABLE IF NOT EXISTS "CourseTasks" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "CourseId" INTEGER NOT NULL, "LessonId" INTEGER NULL, "Kind" TEXT NOT NULL,
                    "Title" TEXT NOT NULL, "Instructions" TEXT NULL, "DueAt" TEXT NULL, "Questions" TEXT NULL, "PassPercent" INTEGER NULL, "MaxMarks" INTEGER NOT NULL DEFAULT 10, "CreatedAt" TEXT NOT NULL)',
                'CREATE INDEX IF NOT EXISTS "IX_CT_Course" ON "CourseTasks" ("CourseId")',
                'CREATE TABLE IF NOT EXISTS "CourseSubmissions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TaskId" INTEGER NOT NULL, "CourseId" INTEGER NOT NULL, "UserId" INTEGER NOT NULL,
                    "Link" TEXT NULL, "Note" TEXT NULL, "Answers" TEXT NULL, "Score" INTEGER NULL, "Total" INTEGER NULL, "Passed" INTEGER NOT NULL DEFAULT 0,
                    "Status" TEXT NOT NULL DEFAULT \'submitted\', "Marks" INTEGER NULL, "Feedback" TEXT NULL, "Attempts" INTEGER NOT NULL DEFAULT 1, "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL,
                    UNIQUE ("TaskId", "UserId"))',
                'CREATE TABLE IF NOT EXISTS "CourseEnrollments" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "CourseId" INTEGER NOT NULL, "UserId" INTEGER NOT NULL, "PaymentId" INTEGER NULL,
                    "Price" INTEGER NOT NULL DEFAULT 0, "TeacherShare" INTEGER NOT NULL DEFAULT 0, "AdminShare" INTEGER NOT NULL DEFAULT 0, "Source" TEXT NOT NULL DEFAULT \'payment\',
                    "CertCode" TEXT NULL, "CertIssuedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("CourseId", "UserId"))',
                'CREATE TABLE IF NOT EXISTS "CourseProgress" ("UserId" INTEGER NOT NULL, "LessonId" INTEGER NOT NULL, "CourseId" INTEGER NOT NULL, "DoneAt" TEXT NOT NULL, PRIMARY KEY ("UserId", "LessonId"))',
                'CREATE TABLE IF NOT EXISTS "CoursePayouts" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "Amount" INTEGER NOT NULL, "Method" TEXT NULL, "Note" TEXT NULL,
                    "CreatedBy" INTEGER NULL, "CreatedAt" TEXT NOT NULL)',
            ] as $sql) $this->pdo->exec($sql);
        } else {
            $e = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            foreach ([
                "CREATE TABLE IF NOT EXISTS `Courses` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `Title` VARCHAR(150) NOT NULL, `Subtitle` VARCHAR(250) NULL,
                    `Description` MEDIUMTEXT NULL, `Includes` TEXT NULL, `Category` VARCHAR(60) NULL, `Level` VARCHAR(30) NULL, `Price` INT NOT NULL DEFAULT 0, `RegularPrice` INT NULL,
                    `Thumbnail` VARCHAR(500) NULL, `Status` VARCHAR(12) NOT NULL DEFAULT 'draft', `AdminHidden` TINYINT(1) NOT NULL DEFAULT 0, `PassPercent` INT NOT NULL DEFAULT 60,
                    `CreatedAt` DATETIME NOT NULL, `UpdatedAt` DATETIME NULL, PRIMARY KEY (`Id`), KEY `IX_Course_Teacher` (`TeacherId`), KEY `IX_Course_Status` (`Status`, `AdminHidden`)) $e",
                "CREATE TABLE IF NOT EXISTS `CourseLessons` (`Id` INT NOT NULL AUTO_INCREMENT, `CourseId` INT NOT NULL, `Title` VARCHAR(200) NOT NULL, `Description` TEXT NULL,
                    `Kind` VARCHAR(10) NOT NULL DEFAULT 'recorded', `SortOrder` INT NOT NULL DEFAULT 0, `UnlockAt` DATETIME NULL, `StartsAt` DATETIME NULL, `DurationMin` INT NOT NULL DEFAULT 60,
                    `MeetingId` VARCHAR(80) NULL, `RecordingUrl` VARCHAR(500) NULL, `RecordingMin` INT NULL, `RecCheckedAt` DATETIME NULL, `LiveStartedAt` DATETIME NULL, `CreatedBy` INT NULL,
                    `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_CL_Course` (`CourseId`, `SortOrder`)) $e",
                "CREATE TABLE IF NOT EXISTS `CourseTasks` (`Id` INT NOT NULL AUTO_INCREMENT, `CourseId` INT NOT NULL, `LessonId` INT NULL, `Kind` VARCHAR(12) NOT NULL, `Title` VARCHAR(200) NOT NULL,
                    `Instructions` TEXT NULL, `DueAt` DATETIME NULL, `Questions` MEDIUMTEXT NULL, `PassPercent` INT NULL, `MaxMarks` INT NOT NULL DEFAULT 10, `CreatedAt` DATETIME NOT NULL,
                    PRIMARY KEY (`Id`), KEY `IX_CT_Course` (`CourseId`)) $e",
                "CREATE TABLE IF NOT EXISTS `CourseSubmissions` (`Id` INT NOT NULL AUTO_INCREMENT, `TaskId` INT NOT NULL, `CourseId` INT NOT NULL, `UserId` INT NOT NULL, `Link` VARCHAR(500) NULL,
                    `Note` TEXT NULL, `Answers` TEXT NULL, `Score` INT NULL, `Total` INT NULL, `Passed` TINYINT(1) NOT NULL DEFAULT 0, `Status` VARCHAR(12) NOT NULL DEFAULT 'submitted',
                    `Marks` INT NULL, `Feedback` VARCHAR(500) NULL, `Attempts` INT NOT NULL DEFAULT 1, `CreatedAt` DATETIME NOT NULL, `UpdatedAt` DATETIME NULL,
                    PRIMARY KEY (`Id`), UNIQUE KEY `UX_CS` (`TaskId`, `UserId`), KEY `IX_CS_Course` (`CourseId`)) $e",
                "CREATE TABLE IF NOT EXISTS `CourseEnrollments` (`Id` INT NOT NULL AUTO_INCREMENT, `CourseId` INT NOT NULL, `UserId` INT NOT NULL, `PaymentId` INT NULL, `Price` INT NOT NULL DEFAULT 0,
                    `TeacherShare` INT NOT NULL DEFAULT 0, `AdminShare` INT NOT NULL DEFAULT 0, `Source` VARCHAR(10) NOT NULL DEFAULT 'payment', `CertCode` VARCHAR(20) NULL, `CertIssuedAt` DATETIME NULL,
                    `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), UNIQUE KEY `UX_CE` (`CourseId`, `UserId`), KEY `IX_CE_User` (`UserId`)) $e",
                "CREATE TABLE IF NOT EXISTS `CourseProgress` (`UserId` INT NOT NULL, `LessonId` INT NOT NULL, `CourseId` INT NOT NULL, `DoneAt` DATETIME NOT NULL, PRIMARY KEY (`UserId`, `LessonId`), KEY `IX_CP_Course` (`CourseId`)) $e",
                "CREATE TABLE IF NOT EXISTS `CoursePayouts` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `Amount` INT NOT NULL, `Method` VARCHAR(40) NULL, `Note` VARCHAR(300) NULL,
                    `CreatedBy` INT NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_CPay_Teacher` (`TeacherId`)) $e",
            ] as $sql) $this->pdo->exec($sql);
        }
        // ধরন: course (রেকর্ড করা কোর্স) | live (লাইভ ক্লাস / ব্যাচ) — বিক্রি, হিসাব, পেমেন্ট আলাদা দেখানোর জন্য
        try { $this->pdo->query('SELECT `PublishPaid` FROM `Courses` LIMIT 1'); }
        catch (Throwable $ex) {
            try {
                $this->pdo->exec('ALTER TABLE `Courses` ADD COLUMN `PublishPaid` INT NOT NULL DEFAULT 0');
                // আগে থেকে প্রকাশিত কোর্সগুলোর জন্য প্রকাশ ফি লাগবে না
                $this->pdo->exec("UPDATE `Courses` SET `PublishPaid` = 1 WHERE `Status` = 'published'");
            } catch (Throwable $e2) { error_log('[Courses PublishPaid] ' . $e2->getMessage()); }
        }
        foreach (['Courses', 'CoursePayouts'] as $t) {
            try { $this->pdo->query("SELECT `Kind` FROM `$t` LIMIT 1"); }
            catch (Throwable $ex) { try { $this->pdo->exec("ALTER TABLE `$t` ADD COLUMN `Kind` VARCHAR(10) NOT NULL DEFAULT 'course'"); } catch (Throwable $e2) { error_log("[$t Kind] " . $e2->getMessage()); } }
        }
        // অ্যাডমিন ব্লক — কোর্স পুরো বন্ধ (কেনা, দেখা, এডিট সব)
        try { $this->pdo->query('SELECT `AdminBlocked` FROM `Courses` LIMIT 1'); }
        catch (Throwable $ex) {
            try { $this->pdo->exec('ALTER TABLE `Courses` ADD COLUMN `AdminBlocked` INT NOT NULL DEFAULT 0'); $this->pdo->exec('ALTER TABLE `Courses` ADD COLUMN `BlockReason` VARCHAR(300) NULL'); }
            catch (Throwable $e2) { error_log('[Courses AdminBlocked] ' . $e2->getMessage()); }
        }
        $this->EnsurePaymentTables();
        try { $this->pdo->query('SELECT `CourseId` FROM `PaymentRequests` LIMIT 1'); }
        catch (Throwable $ex) { try { $this->pdo->exec('ALTER TABLE `PaymentRequests` ADD COLUMN `CourseId` INT NULL'); } catch (Throwable $e2) { error_log('[CourseId col] ' . $e2->getMessage()); } }
        $this->courseReady = true;
    }

    // ============================================================ কোর্স
    private function mapCourse(Obj $c): Obj
    {
        foreach (['Id', 'TeacherId', 'Price', 'AdminHidden', 'AdminBlocked', 'PassPercent'] as $k) $c->$k = (int)($c->$k ?? 0);
        $c->BlockReason = (string)($c->BlockReason ?? '');
        $c->RegularPrice = $c->RegularPrice !== null && $c->RegularPrice !== '' ? (int)$c->RegularPrice : null;
        $c->IncludesList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)($c->Includes ?? ''))), fn($x) => $x !== ''));
        $c->IsLive = $c->Status === 'published' && !$c->AdminHidden && !$c->AdminBlocked;
        $c->Kind = ($c->Kind ?? '') === 'live' ? 'live' : 'course';
        $c->PublishPaid = (int)($c->PublishPaid ?? 0) === 1;
        $c->KindLabel = $c->Kind === 'live' ? 'লাইভ ক্লাস' : 'কোর্স';
        return $c;
    }

    /** $d: Title, Subtitle, Description, Includes, Category, Level, Price, RegularPrice, Thumbnail, PassPercent, Status */
    public function SaveCourse(int $id, int $teacherId, array $d): int
    {
        $this->EnsureCourseTables();
        $cols = ['Title', 'Subtitle', 'Description', 'Includes', 'Category', 'Level', 'Price', 'RegularPrice', 'Thumbnail', 'PassPercent', 'Status', 'Kind'];
        $v = [];
        foreach ($cols as $c) if (array_key_exists($c, $d)) $v[$c] = $d[$c];
        if ($id > 0) {
            $set = implode(', ', array_map(fn($c) => "`$c` = :$c", array_keys($v)));
            $this->exec("UPDATE `Courses` SET $set, `UpdatedAt` = :u WHERE `Id` = :id", $v + ['u' => now(), 'id' => $id]);
            return $id;
        }
        $v += ['Status' => 'draft'];
        $keys = array_keys($v);
        return (int)$this->insert('INSERT INTO `Courses` (`TeacherId`, `' . implode('`, `', $keys) . '`, `CreatedAt`) VALUES (:tid, :' . implode(', :', $keys) . ', :ca)', $v + ['tid' => $teacherId, 'ca' => now()]);
    }

    public function GetCourse(int $id): ?Obj
    {
        $this->EnsureCourseTables();
        $c = $this->one('SELECT c.*, u.`FullName` AS TeacherName, u.`UserName` AS TeacherUser, u.`ProfileImageLink` AS TeacherPhoto, u.`Role` AS TeacherRole FROM `Courses` c
            LEFT JOIN `Users` u ON u.`Id` = c.`TeacherId` WHERE c.`Id` = :i', ['i' => $id]);
        return $c ? $this->mapCourse($c) : null;
    }

    private function courseListSql(string $where): string
    {
        return "SELECT c.*, u.`FullName` AS TeacherName, u.`UserName` AS TeacherUser, u.`ProfileImageLink` AS TeacherPhoto, u.`Role` AS TeacherRole,
            (SELECT COUNT(*) FROM `CourseLessons` l WHERE l.`CourseId` = c.`Id`) AS LessonCount,
            (SELECT COUNT(*) FROM `CourseEnrollments` e WHERE e.`CourseId` = c.`Id`) AS StudentCount,
            (SELECT COUNT(*) FROM `CourseTasks` t WHERE t.`CourseId` = c.`Id` AND t.`Kind` <> 'quiz') AS AssignmentCount,
            (SELECT COUNT(*) FROM `CourseTasks` t2 WHERE t2.`CourseId` = c.`Id` AND t2.`Kind` = 'quiz') AS QuizCount
            FROM `Courses` c LEFT JOIN `Users` u ON u.`Id` = c.`TeacherId` WHERE $where";
    }

    /** পাবলিক Course পেজ — Published আর অ্যাডমিন লুকায়নি এমন */
    public function GetPublicCourses(string $q = '', string $cat = '', string $kind = ''): array
    {
        $this->EnsureCourseTables();
        $p = []; $w = "c.`Status` = 'published' AND c.`AdminHidden` = 0 AND c.`AdminBlocked` = 0";
        if ($kind !== '') { $w .= $kind === 'live' ? " AND c.`Kind` = 'live'" : " AND c.`Kind` <> 'live'"; }
        if ($q !== '') { $w .= ' AND (LOWER(c.`Title`) LIKE :q OR LOWER(COALESCE(c.`Subtitle`, \'\')) LIKE :q2 OR LOWER(COALESCE(u.`FullName`, \'\')) LIKE :q3)'; $l = '%' . mb_strtolower($q) . '%'; $p += ['q' => $l, 'q2' => $l, 'q3' => $l]; }
        if ($cat !== '') { $w .= ' AND c.`Category` = :cat'; $p['cat'] = $cat; }
        return array_map(fn($c) => $this->mapCourse($c), $this->all($this->courseListSql($w) . ' ORDER BY c.`Id` DESC', $p));
    }

    public function GetCourseCategories(): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($r) => (string)$r->Category, $this->all("SELECT DISTINCT `Category` FROM `Courses` WHERE `Status` = 'published' AND `AdminHidden` = 0 AND `AdminBlocked` = 0 AND `Category` IS NOT NULL AND `Category` <> '' ORDER BY `Category`"));
    }

    public function GetTeacherCourses(int $teacherId): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($c) => $this->mapCourse($c), $this->all($this->courseListSql('c.`TeacherId` = :t') . ' ORDER BY c.`Id` DESC', ['t' => $teacherId]));
    }

    public function GetAllCoursesAdmin(string $kind = ''): array
    {
        $this->EnsureCourseTables();
        $w = $kind === 'live' ? "c.`Kind` = 'live'" : ($kind === 'course' ? "c.`Kind` <> 'live'" : '1 = 1');
        return array_map(fn($c) => $this->mapCourse($c), $this->all($this->courseListSql($w) . ' ORDER BY c.`Id` DESC'));
    }

    /** অ্যাডমিন কোর্স ব্লক / আনব্লক (কারণসহ) */
    public function SetCourseBlocked(int $id, bool $blocked, string $reason = ''): void
    {
        $this->EnsureCourseTables();
        $this->exec('UPDATE `Courses` SET `AdminBlocked` = :b, `BlockReason` = :r WHERE `Id` = :i', ['b' => $blocked ? 1 : 0, 'r' => $blocked && $reason !== '' ? $reason : null, 'i' => $id]);
    }

    /** অ্যাডমিনের কোর্স ডিটেইলস পেজের শিক্ষার্থী তালিকা — পেমেন্টের তথ্যসহ */
    public function GetCourseStudentsAdmin(int $courseId): array
    {
        $rows = $this->GetCourseStudents($courseId);
        $pay = [];
        $ids = array_values(array_filter(array_map(fn($r) => (int)($r->PaymentId ?? 0), $rows)));
        if ($ids) {
            try {
                foreach ($this->all('SELECT * FROM `PaymentRequests` WHERE `Id` IN (' . implode(',', $ids) . ')') as $p) $pay[(int)$p->Id] = $p;
            } catch (Throwable $e) { }
        }
        foreach ($rows as $r) $r->Payment = $pay[(int)($r->PaymentId ?? 0)] ?? null;
        return $rows;
    }

    public function SetCourseHidden(int $id, bool $hidden): void { $this->exec('UPDATE `Courses` SET `AdminHidden` = :h WHERE `Id` = :i', ['h' => $hidden ? 1 : 0, 'i' => $id]); }
    public function SetCourseStatus(int $id, string $st): void { $this->exec('UPDATE `Courses` SET `Status` = :s, `UpdatedAt` = :u WHERE `Id` = :i', ['s' => $st === 'published' ? 'published' : 'draft', 'u' => now(), 'i' => $id]); }

    public function CountCourseEnrollments(int $courseId): int
    {
        $this->EnsureCourseTables();
        return (int)$this->scalar('SELECT COUNT(*) FROM `CourseEnrollments` WHERE `CourseId` = :c', ['c' => $courseId]);
    }

    /** কেউ কেনেনি এমন কোর্স পুরো মুছে ফেলা */
    public function DeleteCourse(int $id): void
    {
        $this->EnsureCourseTables();
        foreach (['CourseSubmissions', 'CourseTasks', 'CourseProgress', 'CourseLessons'] as $t) $this->exec("DELETE FROM `$t` WHERE `CourseId` = :i", ['i' => $id]);
        $this->exec('DELETE FROM `Courses` WHERE `Id` = :i', ['i' => $id]);
    }

    // ============================================================ ক্লাস (Lesson)
    private function mapLesson(Obj $l): Obj
    {
        foreach (['Id', 'CourseId', 'SortOrder', 'DurationMin'] as $k) $l->$k = (int)($l->$k ?? 0);
        $l->RecordingMin = $l->RecordingMin !== null ? (int)$l->RecordingMin : null;
        $now = time();
        $l->UnlockTs = $l->UnlockAt ? strtotime((string)$l->UnlockAt) : 0;
        $l->StartTs = $l->StartsAt ? strtotime((string)$l->StartsAt) : 0;
        $l->Locked = $l->UnlockTs > $now;
        if ($l->Kind === 'live') {
            $end = $l->StartTs + max(30, $l->DurationMin) * 60;
            $l->LiveState = !$l->StartTs ? 'none' : ($now < $l->StartTs - 15 * 60 ? 'upcoming' : ($now <= $end + 3600 ? 'open' : 'ended'));
            $l->Locked = $l->LiveState === 'upcoming' || $l->LiveState === 'none';
        } else $l->LiveState = '';
        return $l;
    }

    public function GetLessons(int $courseId): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($l) => $this->mapLesson($l), $this->all('SELECT * FROM `CourseLessons` WHERE `CourseId` = :c ORDER BY `SortOrder`, `Id`', ['c' => $courseId]));
    }

    public function GetLesson(int $id): ?Obj
    {
        $this->EnsureCourseTables();
        $l = $this->one('SELECT * FROM `CourseLessons` WHERE `Id` = :i', ['i' => $id]);
        return $l ? $this->mapLesson($l) : null;
    }

    /** $d: Title, Description, Kind, UnlockAt, StartsAt, DurationMin */
    public function SaveLesson(int $id, int $courseId, array $d, int $by): int
    {
        $this->EnsureCourseTables();
        if ($id > 0) {
            $this->exec('UPDATE `CourseLessons` SET `Title` = :t, `Description` = :d, `UnlockAt` = :u, `StartsAt` = :s, `DurationMin` = :m WHERE `Id` = :i AND `CourseId` = :c',
                ['t' => $d['Title'], 'd' => $d['Description'] ?: null, 'u' => $d['UnlockAt'] ?: null, 's' => $d['StartsAt'] ?: null, 'm' => (int)$d['DurationMin'], 'i' => $id, 'c' => $courseId]);
            return $id;
        }
        $sort = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `CourseLessons` WHERE `CourseId` = :c', ['c' => $courseId]) + 1;
        $nid = (int)$this->insert('INSERT INTO `CourseLessons` (`CourseId`, `Title`, `Description`, `Kind`, `SortOrder`, `UnlockAt`, `StartsAt`, `DurationMin`, `CreatedBy`, `CreatedAt`)
            VALUES (:c, :t, :d, :k, :so, :u, :s, :m, :b, :ca)', ['c' => $courseId, 't' => $d['Title'], 'd' => $d['Description'] ?: null, 'k' => $d['Kind'] === 'live' ? 'live' : 'recorded',
            'so' => $sort, 'u' => $d['UnlockAt'] ?: null, 's' => $d['StartsAt'] ?: null, 'm' => (int)$d['DurationMin'], 'b' => $by, 'ca' => now()]);
        $this->exec('UPDATE `CourseLessons` SET `MeetingId` = :m WHERE `Id` = :i', ['m' => 'mm-c' . $courseId . '-l' . $nid . '-' . bin2hex(random_bytes(3)), 'i' => $nid]);
        return $nid;
    }

    public function DeleteLesson(int $id): void
    {
        $this->EnsureCourseTables();
        $this->exec('DELETE FROM `CourseSubmissions` WHERE `TaskId` IN (SELECT `Id` FROM `CourseTasks` WHERE `LessonId` = :i)', ['i' => $id]);
        $this->exec('DELETE FROM `CourseTasks` WHERE `LessonId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `CourseProgress` WHERE `LessonId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `CourseLessons` WHERE `Id` = :i', ['i' => $id]);
    }

    public function MoveLesson(int $courseId, int $id, int $dir): void
    {
        $ls = $this->GetLessons($courseId);
        $ids = array_map(fn($l) => $l->Id, $ls);
        $i = array_search($id, $ids, true);
        if ($i === false) return;
        $j = $i + ($dir < 0 ? -1 : 1);
        if ($j < 0 || $j >= count($ids)) return;
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        foreach ($ids as $k => $lid) $this->exec('UPDATE `CourseLessons` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => $k + 1, 'i' => $lid]);
    }

    /** নতুন করে রেকর্ড করতে — নতুন মিটিং আইডি, আগের রেকর্ডিং বাদ */
    public function ResetLessonRecording(int $id): void
    {
        $l = $this->GetLesson($id);
        if (!$l) return;
        $this->exec('UPDATE `CourseLessons` SET `MeetingId` = :m, `RecordingUrl` = NULL, `RecordingMin` = NULL, `RecCheckedAt` = NULL WHERE `Id` = :i',
            ['m' => 'mm-c' . $l->CourseId . '-l' . $id . '-' . bin2hex(random_bytes(3)), 'i' => $id]);
    }

    public function SetLessonLiveStarted(int $id): bool
    {
        return $this->exec('UPDATE `CourseLessons` SET `LiveStartedAt` = :t WHERE `Id` = :i AND (`LiveStartedAt` IS NULL OR `LiveStartedAt` < :old)',
            ['t' => now(), 'i' => $id, 'old' => date('Y-m-d H:i:s', time() - 3 * 3600)]) > 0;
    }

    /**
     * রেকর্ডিং তৈরি হয়েছে কিনা BigBlueButton থেকে দেখে নেয় (৩ মিনিটে একবার) — তৈরি হলে লিংক জমা হয়।
     * রিটার্ন: [lessonId => 'processing'] — যেগুলো এখনো তৈরি হচ্ছে
     */
    public function SyncLessonRecordings(array $lessons, bool $force = false): array
    {
        $need = [];
        foreach ($lessons as $l) {
            if (!$l->MeetingId || $l->RecordingUrl) continue;
            if ($l->Kind === 'live' && (!$l->LiveStartedAt)) continue;
            if (!$force && $l->RecCheckedAt && strtotime((string)$l->RecCheckedAt) > time() - 180) continue;
            $need[$l->MeetingId] = $l;
        }
        if (!$need || !function_exists('bbb_ready') || !bbb_ready()) return [];
        $recs = bbb_recordings(array_keys($need));
        $proc = [];
        foreach ($need as $mid => $l) {
            $best = null;
            foreach ($recs[$mid] ?? [] as $r) {
                if ($r['state'] === 'published' && $r['url'] !== '' && (!$best || $r['start'] > $best['start'])) $best = $r;
                elseif ($r['state'] !== 'published') $proc[$l->Id] = 'processing';
            }
            if ($best) {
                unset($proc[$l->Id]);
                $this->exec('UPDATE `CourseLessons` SET `RecordingUrl` = :u, `RecordingMin` = :m, `RecCheckedAt` = :t WHERE `Id` = :i', ['u' => $best['url'], 'm' => $best['minutes'], 't' => now(), 'i' => $l->Id]);
                $l->RecordingUrl = $best['url']; $l->RecordingMin = $best['minutes'];
            } else $this->exec('UPDATE `CourseLessons` SET `RecCheckedAt` = :t WHERE `Id` = :i', ['t' => now(), 'i' => $l->Id]);
        }
        return $proc;
    }

    // ============================================================ অ্যাসাইনমেন্ট / কুইজ
    private function mapTask(Obj $t): Obj
    {
        foreach (['Id', 'CourseId', 'MaxMarks'] as $k) $t->$k = (int)($t->$k ?? 0);
        $t->LessonId = $t->LessonId !== null ? (int)$t->LessonId : null;
        $t->PassPercent = $t->PassPercent !== null ? (int)$t->PassPercent : null;
        $q = json_decode((string)($t->Questions ?? ''), true);
        $t->QuestionList = is_array($q) ? $q : [];
        return $t;
    }

    public function GetTasks(int $courseId): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($t) => $this->mapTask($t), $this->all('SELECT * FROM `CourseTasks` WHERE `CourseId` = :c ORDER BY `Id`', ['c' => $courseId]));
    }

    public function GetTask(int $id): ?Obj
    {
        $this->EnsureCourseTables();
        $t = $this->one('SELECT * FROM `CourseTasks` WHERE `Id` = :i', ['i' => $id]);
        return $t ? $this->mapTask($t) : null;
    }

    /** $d: LessonId, Kind, Title, Instructions, DueAt, Questions (array), PassPercent, MaxMarks */
    public function SaveTask(int $id, int $courseId, array $d): int
    {
        $this->EnsureCourseTables();
        $v = ['l' => $d['LessonId'] ?: null, 'ti' => $d['Title'], 'in' => $d['Instructions'] ?: null, 'du' => $d['DueAt'] ?: null,
            'q' => $d['Kind'] === 'quiz' ? json_encode(array_values($d['Questions']), JSON_UNESCAPED_UNICODE) : null, 'pp' => $d['PassPercent'] ?? null, 'mm' => (int)($d['MaxMarks'] ?? 10)];
        if ($id > 0) {
            $this->exec('UPDATE `CourseTasks` SET `LessonId` = :l, `Title` = :ti, `Instructions` = :in, `DueAt` = :du, `Questions` = :q, `PassPercent` = :pp, `MaxMarks` = :mm WHERE `Id` = :id AND `CourseId` = :c', $v + ['id' => $id, 'c' => $courseId]);
            return $id;
        }
        return (int)$this->insert('INSERT INTO `CourseTasks` (`CourseId`, `LessonId`, `Kind`, `Title`, `Instructions`, `DueAt`, `Questions`, `PassPercent`, `MaxMarks`, `CreatedAt`)
            VALUES (:c, :l, :k, :ti, :in, :du, :q, :pp, :mm, :ca)', $v + ['c' => $courseId, 'k' => $d['Kind'] === 'quiz' ? 'quiz' : 'assignment', 'ca' => now()]);
    }

    public function DeleteTask(int $id): void
    {
        $this->EnsureCourseTables();
        $this->exec('DELETE FROM `CourseSubmissions` WHERE `TaskId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `CourseTasks` WHERE `Id` = :i', ['i' => $id]);
    }

    // ============================================================ জমা
    public function GetSubmission(int $taskId, int $userId): ?Obj
    {
        $this->EnsureCourseTables();
        return $this->one('SELECT * FROM `CourseSubmissions` WHERE `TaskId` = :t AND `UserId` = :u', ['t' => $taskId, 'u' => $userId]);
    }

    /** একজনের সব জমা — [TaskId => Obj] */
    public function GetUserSubmissions(int $courseId, int $userId): array
    {
        $this->EnsureCourseTables();
        $m = [];
        foreach ($this->all('SELECT * FROM `CourseSubmissions` WHERE `CourseId` = :c AND `UserId` = :u', ['c' => $courseId, 'u' => $userId]) as $s) $m[(int)$s->TaskId] = $s;
        return $m;
    }

    /** অ্যাসাইনমেন্ট জমা / কুইজের ফল সেভ (আগে থাকলে আপডেট — আবার জমা দেওয়া যায়) */
    public function SaveSubmission(Obj $task, int $userId, array $d): void
    {
        $this->EnsureCourseTables();
        $old = $this->GetSubmission($task->Id, $userId);
        $v = ['li' => $d['Link'] ?? null, 'no' => $d['Note'] ?? null, 'an' => isset($d['Answers']) ? json_encode($d['Answers']) : null,
            'sc' => $d['Score'] ?? null, 'to' => $d['Total'] ?? null, 'pa' => !empty($d['Passed']) ? 1 : 0];
        if ($old) {
            $passed = (int)$old->Passed === 1 || $v['pa'] === 1 ? 1 : 0;   // একবার পাস করলে পাসই থাকে
            // কুইজ: আগের সেরা স্কোরের চেয়ে কম হলে আগেরটাই থাকে (শুধু চেষ্টার সংখ্যা বাড়ে)
            if ($task->Kind === 'quiz' && $old->Score !== null && $v['sc'] !== null && (int)$v['sc'] < (int)$old->Score) { $v['an'] = $old->Answers; $v['sc'] = (int)$old->Score; $v['to'] = (int)$old->Total; }
            $this->exec("UPDATE `CourseSubmissions` SET `Link` = :li, `Note` = :no, `Answers` = :an, `Score` = :sc, `Total` = :to, `Passed` = :pa, `Status` = 'submitted', `Attempts` = `Attempts` + 1, `UpdatedAt` = :u WHERE `Id` = :i",
                array_merge($v, ['pa' => $passed, 'u' => now(), 'i' => (int)$old->Id]));
        } else {
            $this->insert('INSERT INTO `CourseSubmissions` (`TaskId`, `CourseId`, `UserId`, `Link`, `Note`, `Answers`, `Score`, `Total`, `Passed`, `Status`, `Attempts`, `CreatedAt`)
                VALUES (:t, :c, :u, :li, :no, :an, :sc, :to, :pa, \'submitted\', 1, :ca)', $v + ['t' => $task->Id, 'c' => $task->CourseId, 'u' => $userId, 'ca' => now()]);
        }
    }

    /** টিচারের রিভিউ (নম্বর + মন্তব্য) */
    public function ReviewSubmission(int $id, ?int $marks, string $feedback): ?Obj
    {
        $this->EnsureCourseTables();
        $this->exec("UPDATE `CourseSubmissions` SET `Marks` = :m, `Feedback` = :f, `Status` = 'reviewed', `UpdatedAt` = :u WHERE `Id` = :i", ['m' => $marks, 'f' => $feedback !== '' ? $feedback : null, 'u' => now(), 'i' => $id]);
        return $this->one('SELECT * FROM `CourseSubmissions` WHERE `Id` = :i', ['i' => $id]);
    }

    public function GetCourseSubmissions(int $courseId): array
    {
        $this->EnsureCourseTables();
        return $this->all('SELECT s.*, t.`Title` AS TaskTitle, t.`Kind` AS TaskKind, t.`MaxMarks`, u.`FullName`, u.`UserName` FROM `CourseSubmissions` s
            JOIN `CourseTasks` t ON t.`Id` = s.`TaskId` LEFT JOIN `Users` u ON u.`Id` = s.`UserId` WHERE s.`CourseId` = :c ORDER BY COALESCE(s.`UpdatedAt`, s.`CreatedAt`) DESC', ['c' => $courseId]);
    }

    // ============================================================ ভর্তি
    public function GetEnrollment(int $courseId, int $userId): ?Obj
    {
        $this->EnsureCourseTables();
        return $this->one('SELECT * FROM `CourseEnrollments` WHERE `CourseId` = :c AND `UserId` = :u', ['c' => $courseId, 'u' => $userId]);
    }

    public function IsEnrolled(int $courseId, int $userId): bool { return $this->GetEnrollment($courseId, $userId) !== null; }

    /** ভর্তি — দাম থেকে টিচার / অ্যাডমিনের ভাগ (অ্যাডমিনের নিজের কোর্স হলে পুরোটাই অ্যাডমিনের) */
    public function Enroll(int $courseId, int $userId, ?int $paymentId, int $price, string $source = 'payment'): bool
    {
        $this->EnsureCourseTables();
        if ($this->IsEnrolled($courseId, $userId)) return false;
        $c = $this->GetCourse($courseId);
        $adminOwned = $c && ($c->TeacherRole ?? '') === 'Admin';
        $adminShare = $adminOwned ? $price : (int)round($price * course_admin_pct() / 100);
        $this->insert('INSERT INTO `CourseEnrollments` (`CourseId`, `UserId`, `PaymentId`, `Price`, `TeacherShare`, `AdminShare`, `Source`, `CreatedAt`) VALUES (:c, :u, :p, :pr, :ts, :as, :s, :t)',
            ['c' => $courseId, 'u' => $userId, 'p' => $paymentId, 'pr' => $price, 'ts' => $price - $adminShare, 'as' => $adminShare, 's' => $source, 't' => now()]);
        return true;
    }

    /** আমার কেনা কোর্স (অগ্রগতি সহ) */
    public function GetMyCourses(int $userId): array
    {
        $this->EnsureCourseTables();
        $rows = $this->all($this->courseListSql('c.`Id` IN (SELECT `CourseId` FROM `CourseEnrollments` WHERE `UserId` = :u)') . ' ORDER BY c.`Id` DESC', ['u' => $userId]);
        return array_map(function ($c) use ($userId) { $c = $this->mapCourse($c); $c->Progress = $this->GetCourseProgress($c->Id, $userId); $c->Enrollment = $this->GetEnrollment($c->Id, $userId); return $c; }, $rows);
    }

    public function GetCourseStudents(int $courseId): array
    {
        $this->EnsureCourseTables();
        $rows = $this->all('SELECT e.*, u.`FullName`, u.`UserName`, u.`Email`, u.`MobileNumber`, u.`ProfileImageLink` FROM `CourseEnrollments` e LEFT JOIN `Users` u ON u.`Id` = e.`UserId`
            WHERE e.`CourseId` = :c ORDER BY e.`Id` DESC', ['c' => $courseId]);
        foreach ($rows as $r) $r->Progress = $this->GetCourseProgress($courseId, (int)$r->UserId);
        return $rows;
    }

    public function GetEnrolledUserIds(int $courseId): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($r) => (int)$r->UserId, $this->all('SELECT `UserId` FROM `CourseEnrollments` WHERE `CourseId` = :c', ['c' => $courseId]));
    }

    // ============================================================ অগ্রগতি + সার্টিফিকেট
    public function MarkLessonDone(int $courseId, int $lessonId, int $userId): void
    {
        $this->EnsureCourseTables();
        if ($this->scalar('SELECT COUNT(*) FROM `CourseProgress` WHERE `UserId` = :u AND `LessonId` = :l', ['u' => $userId, 'l' => $lessonId])) return;
        $this->exec('INSERT INTO `CourseProgress` (`UserId`, `LessonId`, `CourseId`, `DoneAt`) VALUES (:u, :l, :c, :t)', ['u' => $userId, 'l' => $lessonId, 'c' => $courseId, 't' => now()]);
    }

    public function GetDoneLessonIds(int $courseId, int $userId): array
    {
        $this->EnsureCourseTables();
        return array_map(fn($r) => (int)$r->LessonId, $this->all('SELECT `LessonId` FROM `CourseProgress` WHERE `CourseId` = :c AND `UserId` = :u', ['c' => $courseId, 'u' => $userId]));
    }

    /**
     * অগ্রগতি: ক্লাস (শেষ করা / মোট), অ্যাসাইনমেন্ট (জমা / মোট), কুইজ (পাস / মোট), শতাংশ, সব শেষ কিনা
     */
    public function GetCourseProgress(int $courseId, int $userId): array
    {
        $lessons = $this->GetLessons($courseId);
        $tasks = $this->GetTasks($courseId);
        $done = array_flip($this->GetDoneLessonIds($courseId, $userId));
        $subs = $this->GetUserSubmissions($courseId, $userId);
        $lt = count($lessons); $ld = 0;
        foreach ($lessons as $l) if (isset($done[$l->Id])) $ld++;
        $at = 0; $ad = 0; $qt = 0; $qd = 0; $marks = 0; $marksMax = 0;
        foreach ($tasks as $t) {
            $s = $subs[$t->Id] ?? null;
            if ($t->Kind === 'quiz') { $qt++; if ($s && (int)$s->Passed === 1) $qd++; }
            else { $at++; if ($s) $ad++; if ($s && $s->Marks !== null) { $marks += (int)$s->Marks; $marksMax += $t->MaxMarks; } }
        }
        $total = $lt + $at + $qt; $got = $ld + $ad + $qd;
        return ['lessons' => [$ld, $lt], 'assignments' => [$ad, $at], 'quizzes' => [$qd, $qt], 'marks' => [$marks, $marksMax],
            'percent' => $total > 0 ? (int)floor($got * 100 / $total) : 0, 'complete' => $lt > 0 && $got === $total];
    }

    /** সব শেষ হলে নিজে থেকে সার্টিফিকেট — নতুন দেওয়া হলে কোড ফেরত দেয় */
    public function CheckCertificate(int $courseId, int $userId): ?string
    {
        $e = $this->GetEnrollment($courseId, $userId);
        if (!$e || !empty($e->CertCode)) return null;
        if (!$this->GetCourseProgress($courseId, $userId)['complete']) return null;
        $code = 'MM-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        $n = $this->exec('UPDATE `CourseEnrollments` SET `CertCode` = :k, `CertIssuedAt` = :t WHERE `Id` = :i AND `CertCode` IS NULL', ['k' => $code, 't' => now(), 'i' => (int)$e->Id]);
        return $n ? $code : null;
    }

    public function GetCertificate(string $code): ?Obj
    {
        $this->EnsureCourseTables();
        return $this->one('SELECT e.*, u.`FullName`, u.`UserName`, c.`Title` AS CourseTitle, c.`TeacherId`, t.`FullName` AS TeacherName, t.`Role` AS TeacherRole FROM `CourseEnrollments` e
            JOIN `Users` u ON u.`Id` = e.`UserId` JOIN `Courses` c ON c.`Id` = e.`CourseId` LEFT JOIN `Users` t ON t.`Id` = c.`TeacherId` WHERE e.`CertCode` = :k', ['k' => strtoupper($code)]);
    }

    // ============================================================ আয় / টিচারকে টাকা
    /** টিচারের হিসাব: বিক্রি, মোট দাম, টিচারের ভাগ, অ্যাডমিনের ভাগ, পেয়েছে, বাকি + বিক্রির তালিকা */
    public function GetTeacherCourseEarnings(int $teacherId): array
    {
        $this->EnsureCourseTables();
        $sales = $this->all('SELECT e.*, c.`Title` AS CourseTitle, u.`FullName`, u.`UserName`, p.`MethodName`, p.`TxnId` FROM `CourseEnrollments` e JOIN `Courses` c ON c.`Id` = e.`CourseId`
            LEFT JOIN `Users` u ON u.`Id` = e.`UserId` LEFT JOIN `PaymentRequests` p ON p.`Id` = e.`PaymentId` WHERE c.`TeacherId` = :t AND e.`Price` > 0 ORDER BY e.`Id` DESC', ['t' => $teacherId]);
        $gross = 0; $mine = 0; $admin = 0;
        foreach ($sales as $s) { $gross += (int)$s->Price; $mine += (int)$s->TeacherShare; $admin += (int)$s->AdminShare; }
        $payouts = $this->all('SELECT * FROM `CoursePayouts` WHERE `TeacherId` = :t ORDER BY `Id` DESC', ['t' => $teacherId]);
        $paid = array_sum(array_map(fn($p) => (int)$p->Amount, $payouts));
        $pending = (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` p JOIN `Courses` c ON c.`Id` = p.`CourseId` WHERE c.`TeacherId` = :t AND p.`Status` = 'Pending'", ['t' => $teacherId]);
        return ['sales' => $sales, 'count' => count($sales), 'gross' => $gross, 'teacher' => $mine, 'admin' => $admin, 'paid' => $paid, 'due' => max(0, $mine - $paid), 'payouts' => $payouts, 'pending' => $pending];
    }

    public function SetCoursePublishPaid(int $id): void { $this->EnsureCourseTables(); $this->exec('UPDATE `Courses` SET `PublishPaid` = 1 WHERE `Id` = :i', ['i' => $id]); }

    /** [courseId => 'course'|'live'] */
    public function CourseKindMap(): array
    {
        static $m = null;
        if ($m !== null) return $m;
        $this->EnsureCourseTables();
        $m = [];
        foreach ($this->all('SELECT `Id`, `Kind` FROM `Courses`') as $r) $m[(int)$r->Id] = $r->Kind === 'live' ? 'live' : 'course';
        return $m;
    }

    /** অ্যাডমিন: সব টিচারের হিসাব */
    public function GetCourseLedger(string $kind = ''): array
    {
        $this->EnsureCourseTables();
        $kw = $kind === 'live' ? " AND c.`Kind` = 'live'" : ($kind === 'course' ? " AND c.`Kind` <> 'live'" : '');
        $pw = $kind === 'live' ? " WHERE `Kind` = 'live'" : ($kind === 'course' ? " WHERE `Kind` <> 'live'" : '');
        $rows = $this->all('SELECT c.`TeacherId`, u.`FullName`, u.`UserName`, u.`MobileNumber`, u.`Role`, COUNT(e.`Id`) AS Sales, COALESCE(SUM(e.`Price`), 0) AS Gross,
            COALESCE(SUM(e.`TeacherShare`), 0) AS TShare, COALESCE(SUM(e.`AdminShare`), 0) AS AShare
            FROM `CourseEnrollments` e JOIN `Courses` c ON c.`Id` = e.`CourseId` LEFT JOIN `Users` u ON u.`Id` = c.`TeacherId` WHERE e.`Price` > 0' . $kw . '
            GROUP BY c.`TeacherId`, u.`FullName`, u.`UserName`, u.`MobileNumber`, u.`Role` ORDER BY Gross DESC');
        $paid = [];
        foreach ($this->all("SELECT `TeacherId`, SUM(`Amount`) AS A FROM `CoursePayouts`$pw GROUP BY `TeacherId`") as $p) $paid[(int)$p->TeacherId] = (int)$p->A;
        foreach ($rows as $r) { $r->Paid = $paid[(int)$r->TeacherId] ?? 0; $r->Due = $r->Role === 'Admin' ? 0 : max(0, (int)$r->TShare - $r->Paid); }
        return $rows;
    }

    public function AddCoursePayout(int $teacherId, int $amount, string $method, string $note, int $by, string $kind = 'course'): void
    {
        $this->EnsureCourseTables();
        $this->insert('INSERT INTO `CoursePayouts` (`TeacherId`, `Amount`, `Method`, `Note`, `CreatedBy`, `CreatedAt`, `Kind`) VALUES (:t, :a, :m, :n, :b, :c, :k)',
            ['k' => $kind === 'live' ? 'live' : 'course', 't' => $teacherId, 'a' => $amount, 'm' => $method !== '' ? $method : null, 'n' => $note !== '' ? $note : null, 'b' => $by, 'c' => now()]);
    }

    // ============================================================ প্রিমিয়াম লাইভ প্যাকেজ
    public function TeacherHasLivePackage(int $userId): bool
    {
        $this->EnsurePackageTable(); $this->EnsurePaymentTables();
        $n = now();
        if ((int)$this->scalar("SELECT COUNT(*) FROM `UserSubscriptions` s JOIN `PaymentRequests` p ON p.`Id` = s.`PaymentId` JOIN `Packages` k ON k.`Id` = p.`PackageId`
            WHERE s.`UserId` = :u AND s.`Audience` = 'Teacher' AND s.`StartsAt` <= :n AND s.`EndsAt` > :n2 AND k.`IsLive` = 1", ['u' => $userId, 'n' => $n, 'n2' => $n]) > 0) return true;
        // অ্যাডমিনের দেওয়া Premium (User Role / Package Details থেকে)
        return (int)$this->scalar("SELECT COUNT(*) FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Teacher' AND `PaymentId` IS NULL AND `PackageName` LIKE :pn AND `StartsAt` <= :n AND `EndsAt` > :n2",
            ['u' => $userId, 'pn' => 'Premium%', 'n' => $n, 'n2' => $n]) > 0;
    }
}
