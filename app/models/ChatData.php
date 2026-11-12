<?php
/**
 * স্টুডেন্ট ↔ টিচার চ্যাট (মেসেঞ্জারের মতো) — Db ক্লাসে trait হিসেবে যুক্ত।
 *
 * দুই ভাবে চ্যাট খোলে:
 *  1) টিচার স্টুডেন্টের টিউশন পোস্টে Apply করে → স্টুডেন্ট Accept করে   (চ্যাট কী: "12"  = TuitionCircularApplications.Id)
 *  2) স্টুডেন্ট নির্দিষ্ট টিচারকে Request পাঠায় → টিচার Accept করে       (চ্যাট কী: "r5"  = TutorRequests.Id)
 * Reject (বা আবার Pending) করলে চ্যাট বন্ধ — কেউ আর দেখতে বা মেসেজ পাঠাতে পারে না। আবার Accept করলে আগের মেসেজ ফিরে আসে।
 */
trait ChatData
{
    private bool $chatReady = false;

    public function EnsureChatTables(): void
    {
        if ($this->chatReady) return;
        $this->EnsureCircularApplicationsTable();
        $this->EnsureTutorRequestUserColumns();
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatMessages" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Kind" TEXT NOT NULL DEFAULT \'app\', "AppId" INTEGER NOT NULL,
                "SenderId" INTEGER NOT NULL, "Body" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL, "ReadAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatMessages` (`Id` INT NOT NULL AUTO_INCREMENT, `Kind` VARCHAR(8) NOT NULL DEFAULT 'app', `AppId` INT NOT NULL, `SenderId` INT NOT NULL,
                `Body` TEXT NOT NULL, `CreatedAt` DATETIME NOT NULL, `ReadAt` DATETIME NULL, PRIMARY KEY (`Id`), KEY `IX_Chat_Thread` (`Kind`, `AppId`, `Id`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // আগের ভার্সনে বানানো টেবিলে Kind কলাম না থাকলে যোগ করা
        try { $this->pdo->query('SELECT `Kind` FROM `ChatMessages` LIMIT 1'); }
        catch (Throwable $e) { $this->pdo->exec("ALTER TABLE `ChatMessages` ADD COLUMN `Kind` VARCHAR(8) NOT NULL DEFAULT 'app'"); }
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Chat_Thread" ON "ChatMessages" ("Kind", "AppId", "Id")');
        // অনলাইন ক্লাস (Zoom / Google Meet / Teams) — চ্যাটে কার্ড হিসেবে দেখায়
        try { $this->pdo->query('SELECT `ClassId` FROM `ChatMessages` LIMIT 1'); }
        catch (Throwable $e) { $this->pdo->exec('ALTER TABLE `ChatMessages` ADD COLUMN `ClassId` INT NULL'); }
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatClasses" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Kind" TEXT NOT NULL, "AppId" INTEGER NOT NULL, "TeacherId" INTEGER NOT NULL,
                "Title" TEXT NOT NULL, "Link" TEXT NOT NULL, "Platform" TEXT NOT NULL, "StartsAt" TEXT NOT NULL, "DurationMin" INTEGER NOT NULL DEFAULT 60,
                "Status" TEXT NOT NULL DEFAULT \'Scheduled\', "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_ChatClass_Thread" ON "ChatClasses" ("Kind", "AppId", "StartsAt")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatClasses` (`Id` INT NOT NULL AUTO_INCREMENT, `Kind` VARCHAR(8) NOT NULL, `AppId` INT NOT NULL, `TeacherId` INT NOT NULL,
                `Title` VARCHAR(150) NOT NULL, `Link` VARCHAR(500) NOT NULL, `Platform` VARCHAR(20) NOT NULL, `StartsAt` DATETIME NOT NULL, `DurationMin` INT NOT NULL DEFAULT 60,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Scheduled', `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_ChatClass_Thread` (`Kind`, `AppId`, `StartsAt`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // সরাসরি চ্যাট: প্যাকেজ নেওয়া শিক্ষার্থী টিচারের প্রোফাইল থেকে চ্যাট আনলক করে (key "d{Id}")
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatDirect" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "StudentId" INTEGER NOT NULL, "TeacherId" INTEGER NOT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("StudentId", "TeacherId"))');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatDirect` (`Id` INT NOT NULL AUTO_INCREMENT, `StudentId` INT NOT NULL, `TeacherId` INT NOT NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_ChatDirect` (`StudentId`, `TeacherId`), KEY `IX_ChatDirect_T` (`TeacherId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // টিচারের গ্রুপ চ্যাট (key "g{Id}") — টিচার নিজের স্টুডেন্টদের নিয়ে গ্রুপ বানায়; টিচারও সদস্য হিসেবে থাকে
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatGroups" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "TeacherId" INTEGER NOT NULL, "Name" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatGroupMembers" ("GroupId" INTEGER NOT NULL, "UserId" INTEGER NOT NULL, "LastReadId" INTEGER NOT NULL DEFAULT 0, "AddedAt" TEXT NOT NULL, PRIMARY KEY ("GroupId", "UserId"))');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatGroups` (`Id` INT NOT NULL AUTO_INCREMENT, `TeacherId` INT NOT NULL, `Name` VARCHAR(80) NOT NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), KEY `IX_ChatGroups_T` (`TeacherId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatGroupMembers` (`GroupId` INT NOT NULL, `UserId` INT NOT NULL, `LastReadId` INT NOT NULL DEFAULT 0, `AddedAt` DATETIME NOT NULL,
                PRIMARY KEY (`GroupId`, `UserId`), KEY `IX_ChatGM_U` (`UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->chatReady = true;
    }

    // ---------- গ্রুপ চ্যাট ----------
    /** টিচারের যেসব স্টুডেন্টের সাথে চ্যাট আছে (গ্রুপে যোগ করা যায় শুধু এদের) — [Id => Obj(Id, Name, UserName, Photo)] */
    public function TeacherChatContacts(int $teacherId): array
    {
        $this->EnsureChatTables();
        $out = [];
        foreach ($this->GetChatThreads($teacherId) as $t) {
            if ($t->Kind === 'grp' || $t->TeacherId !== $teacherId || $t->StudentId <= 0) continue;
            $out[$t->StudentId] = obj(['Id' => $t->StudentId, 'Name' => $t->OtherName, 'Photo' => $t->OtherPhoto, 'Initials' => $t->Initials]);
        }
        uasort($out, fn($a, $b) => strcasecmp($a->Name, $b->Name));
        return $out;
    }

    public function CreateChatGroup(int $teacherId, string $name, array $studentIds): int
    {
        $this->EnsureChatTables();
        $gid = (int)$this->insert('INSERT INTO `ChatGroups` (`TeacherId`, `Name`, `CreatedAt`) VALUES (:t, :n, :c)', ['t' => $teacherId, 'n' => $name, 'c' => now()]);
        $this->AddChatGroupMembers($gid, array_merge([$teacherId], $studentIds));
        return $gid;
    }

    public function AddChatGroupMembers(int $gid, array $userIds): int
    {
        $this->EnsureChatTables();
        $n = 0;
        foreach (array_unique(array_map('intval', $userIds)) as $u) {
            if ($u <= 0 || $this->scalar('SELECT 1 FROM `ChatGroupMembers` WHERE `GroupId` = :g AND `UserId` = :u', ['g' => $gid, 'u' => $u])) continue;
            $last = (int)$this->scalar("SELECT COALESCE(MAX(`Id`), 0) FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` = :g", ['g' => $gid]);
            $this->exec('INSERT INTO `ChatGroupMembers` (`GroupId`, `UserId`, `LastReadId`, `AddedAt`) VALUES (:g, :u, :l, :c)', ['g' => $gid, 'u' => $u, 'l' => $last, 'c' => now()]);
            $n++;
        }
        return $n;
    }

    public function RemoveChatGroupMember(int $gid, int $userId): void
    {
        $this->exec('DELETE FROM `ChatGroupMembers` WHERE `GroupId` = :g AND `UserId` = :u', ['g' => $gid, 'u' => $userId]);
    }

    public function RenameChatGroup(int $gid, string $name): void
    {
        $this->exec('UPDATE `ChatGroups` SET `Name` = :n WHERE `Id` = :g', ['n' => $name, 'g' => $gid]);
    }

    public function DeleteChatGroup(int $gid): void
    {
        $this->exec('DELETE FROM `ChatGroupMembers` WHERE `GroupId` = :g', ['g' => $gid]);
        $this->exec("DELETE FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` = :g", ['g' => $gid]);
        $this->exec("DELETE FROM `ChatClasses` WHERE `Kind` = 'grp' AND `AppId` = :g", ['g' => $gid]);
        $this->exec('DELETE FROM `ChatGroups` WHERE `Id` = :g', ['g' => $gid]);
    }

    /** @return Obj[] (Id, Name, Photo, Initials, IsTeacher) — টিচার আগে */
    public function GetChatGroupMembers(int $gid): array
    {
        $this->EnsureChatTables();
        $tid = (int)$this->scalar('SELECT `TeacherId` FROM `ChatGroups` WHERE `Id` = :g', ['g' => $gid]);
        $rows = $this->all('SELECT u.`Id`, u.`FullName`, u.`UserName`, u.`ProfileImageLink` FROM `ChatGroupMembers` m INNER JOIN `Users` u ON u.`Id` = m.`UserId` WHERE m.`GroupId` = :g ORDER BY m.`AddedAt`, u.`Id`', ['g' => $gid]);
        $out = array_map(function ($r) use ($tid) {
            $n = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
            return obj(['Id' => (int)$r->Id, 'Name' => $n, 'Photo' => (string)($r->ProfileImageLink ?? ''), 'Initials' => initials($n, 'U'), 'IsTeacher' => (int)$r->Id === $tid]);
        }, $rows);
        usort($out, fn($a, $b) => $b->IsTeacher <=> $a->IsTeacher);
        return $out;
    }

    /** গ্রুপের থ্রেড অবজেক্ট (অন্য চ্যাটের মতো একই ফিল্ড) — আমি সদস্য না হলে null */
    private function groupThread(int $gid, int $me): ?Obj
    {
        $g = $this->one('SELECT g.*, (SELECT COUNT(*) FROM `ChatGroupMembers` x WHERE x.`GroupId` = g.`Id`) AS N, m.`LastReadId`
            FROM `ChatGroups` g INNER JOIN `ChatGroupMembers` m ON m.`GroupId` = g.`Id` AND m.`UserId` = :me WHERE g.`Id` = :g', ['g' => $gid, 'me' => $me]);
        if (!$g) return null;
        return obj(['Kind' => 'grp', 'RefId' => (int)$g->Id, 'AppId' => (int)$g->Id, 'Key' => 'g' . (int)$g->Id, 'TeacherId' => (int)$g->TeacherId, 'StudentId' => 0, 'OtherId' => 0,
            'OtherName' => (string)$g->Name, 'OtherPhoto' => '', 'OtherRole' => 'Group', 'Initials' => initials((string)$g->Name, 'G'),
            'Topic' => bn_num((int)$g->N) . ' জন সদস্য', 'Members' => (int)$g->N, 'LastReadId' => (int)$g->LastReadId,
            'AcceptedAt' => (string)$g->CreatedAt, 'StartedAt' => (string)$g->CreatedAt]);
    }

    /** গ্রুপে প্রত্যেক সদস্যের (আমি বাদে) কাছে নতুন মেসেজ কতটা না-পড়া — [UserId => count] */
    public function GroupUnreadFor(int $gid, int $exceptUser): array
    {
        $out = [];
        foreach ($this->all("SELECT m.`UserId`, (SELECT COUNT(*) FROM `ChatMessages` c WHERE c.`Kind` = 'grp' AND c.`AppId` = m.`GroupId` AND c.`Id` > m.`LastReadId` AND c.`SenderId` <> m.`UserId`) AS N
            FROM `ChatGroupMembers` m WHERE m.`GroupId` = :g AND m.`UserId` <> :me", ['g' => $gid, 'me' => $exceptUser]) as $r) $out[(int)$r->UserId] = (int)$r->N;
        return $out;
    }

    /** শিক্ষার্থী ↔ টিচার সরাসরি চ্যাট খোলে (আগে থাকলে সেটাই) — চ্যাট key ("d12") ফেরত দেয় */
    public function OpenDirectChat(int $studentId, int $teacherId): string
    {
        $this->EnsureChatTables();
        $id = (int)$this->scalar('SELECT `Id` FROM `ChatDirect` WHERE `StudentId` = :s AND `TeacherId` = :t', ['s' => $studentId, 't' => $teacherId]);
        if (!$id) {
            try { $id = (int)$this->insert('INSERT INTO `ChatDirect` (`StudentId`, `TeacherId`, `CreatedAt`) VALUES (:s, :t, :c)', ['s' => $studentId, 't' => $teacherId, 'c' => now()]); }
            catch (Throwable $e) { $id = (int)$this->scalar('SELECT `Id` FROM `ChatDirect` WHERE `StudentId` = :s AND `TeacherId` = :t', ['s' => $studentId, 't' => $teacherId]); }
        }
        return 'd' . $id;
    }

    /** এই শিক্ষার্থী আর টিচারের সরাসরি চ্যাট আগে থেকে আছে কিনা — থাকলে key */
    public function FindDirectChat(int $studentId, int $teacherId): ?string
    {
        try {
            $this->EnsureChatTables();
            $id = (int)$this->scalar('SELECT `Id` FROM `ChatDirect` WHERE `StudentId` = :s AND `TeacherId` = :t', ['s' => $studentId, 't' => $teacherId]);
            return $id ? 'd' . $id : null;
        } catch (Throwable $e) { return null; }
    }

    /** TutorRequests এ কোন স্টুডেন্ট (অ্যাকাউন্ট) পাঠিয়েছে আর কোন টিচারের কাছে গেছে — চ্যাটের জন্য দরকার */
    private bool $trColsReady = false;
    public function EnsureTutorRequestUserColumns(): void
    {
        if ($this->trColsReady) return;
        $this->trColsReady = true;
        try { $this->pdo->query('SELECT `StudentUserId`, `TeacherUserId` FROM `TutorRequests` LIMIT 1'); return; } catch (Throwable $e) { }
        try {
            $this->pdo->exec('ALTER TABLE `TutorRequests` ADD COLUMN `StudentUserId` INT NULL');
        } catch (Throwable $e) { }
        try {
            $this->pdo->exec('ALTER TABLE `TutorRequests` ADD COLUMN `TeacherUserId` INT NULL');
        } catch (Throwable $e) { }
        // পুরোনো রিকোয়েস্ট: টিচার = TutorId (TCH-{1000+TeacherRequests.Id}); স্টুডেন্ট = একই ইমেইলের স্টুডেন্ট অ্যাকাউন্ট (একজনই থাকলে)
        try {
            foreach ($this->all('SELECT `Id`, `TutorId`, `Email` FROM `TutorRequests`') as $r) {
                $tid = null; $sid = null;
                if (preg_match('/^TCH-(\d+)$/', (string)$r->TutorId, $m)) {
                    $tid = $this->scalar('SELECT `UserId` FROM `TeacherRequests` WHERE `Id` = :i', ['i' => (int)$m[1] - 1000]);
                }
                if (!is_blank($r->Email)) {
                    $us = $this->all("SELECT `Id` FROM `Users` WHERE LOWER(`Email`) = LOWER(:e) AND `Role` = 'User'", ['e' => trim((string)$r->Email)]);
                    if (count($us) === 1) $sid = (int)$us[0]->Id;
                }
                $this->exec('UPDATE `TutorRequests` SET `StudentUserId` = :s, `TeacherUserId` = :t WHERE `Id` = :i', ['s' => $sid, 't' => $tid ? (int)$tid : null, 'i' => (int)$r->Id]);
            }
        } catch (Throwable $e) { error_log('[TutorRequest backfill] ' . $e->getMessage()); }
    }

    // ---------- চ্যাট কী ----------
    /** "12" → ['app', 12], "r5" → ['req', 5]; ভুল হলে null */
    public static function parseChatKey(string $key): ?array
    {
        if (preg_match('/^\d+$/', $key)) return ['app', (int)$key];
        if (preg_match('/^r(\d+)$/', $key, $m)) return ['req', (int)$m[1]];
        if (preg_match('/^d(\d+)$/', $key, $m)) return ['dir', (int)$m[1]];
        if (preg_match('/^g(\d+)$/', $key, $m)) return ['grp', (int)$m[1]];
        return null;
    }

    private const CHAT_APP_SELECT = "SELECT 'app' AS Kind, a.`Id` AS RefId, a.`TeacherId`, p.`UserId` AS StudentId, a.`UpdatedAt` AS AcceptedAt, a.`CreatedAt` AS StartedAt,
            p.`Subjects` AS Subj, p.`ClassLevel` AS Cls, p.`Content` AS Extra,
            t.`FullName` AS TeacherName, t.`UserName` AS TeacherUser, t.`ProfileImageLink` AS TeacherPhoto,
            s.`FullName` AS StudentName, s.`UserName` AS StudentUser, s.`ProfileImageLink` AS StudentPhoto
        FROM `TuitionCircularApplications` a
        INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId`
        LEFT JOIN `Users` t ON t.`Id` = a.`TeacherId`
        LEFT JOIN `Users` s ON s.`Id` = p.`UserId`";

    private const CHAT_REQ_SELECT = "SELECT 'req' AS Kind, r.`Id` AS RefId, r.`TeacherUserId` AS TeacherId, r.`StudentUserId` AS StudentId, r.`UpdatedAt` AS AcceptedAt, r.`CreatedAt` AS StartedAt,
            r.`RequiredSubject` AS Subj, r.`StudentClass` AS Cls, r.`AdditionalNotes` AS Extra,
            t.`FullName` AS TeacherName, t.`UserName` AS TeacherUser, t.`ProfileImageLink` AS TeacherPhoto,
            s.`FullName` AS StudentName, s.`UserName` AS StudentUser, s.`ProfileImageLink` AS StudentPhoto
        FROM `TutorRequests` r
        INNER JOIN `Users` t ON t.`Id` = r.`TeacherUserId`
        INNER JOIN `Users` s ON s.`Id` = r.`StudentUserId`";

    private const CHAT_DIR_SELECT = "SELECT 'dir' AS Kind, d.`Id` AS RefId, d.`TeacherId`, d.`StudentId`, d.`CreatedAt` AS AcceptedAt, d.`CreatedAt` AS StartedAt,
            '' AS Subj, '' AS Cls, 'সরাসরি চ্যাট' AS Extra,
            t.`FullName` AS TeacherName, t.`UserName` AS TeacherUser, t.`ProfileImageLink` AS TeacherPhoto,
            s.`FullName` AS StudentName, s.`UserName` AS StudentUser, s.`ProfileImageLink` AS StudentPhoto
        FROM `ChatDirect` d
        INNER JOIN `Users` t ON t.`Id` = d.`TeacherId`
        INNER JOIN `Users` s ON s.`Id` = d.`StudentId`";

    private function mapChatThread(Obj $r, int $me): Obj
    {
        $r->RefId = (int)$r->RefId; $r->TeacherId = (int)$r->TeacherId; $r->StudentId = (int)$r->StudentId;
        $r->Key = ($r->Kind === 'req' ? 'r' : ($r->Kind === 'dir' ? 'd' : '')) . $r->RefId;
        $r->AppId = $r->RefId; // পুরোনো কোডের সাথে মিল রাখতে
        $iAmTeacher = $r->TeacherId === $me;
        $r->OtherId = $iAmTeacher ? $r->StudentId : $r->TeacherId;
        $name = $iAmTeacher ? ($r->StudentName ?: $r->StudentUser) : ($r->TeacherName ?: $r->TeacherUser);
        $r->OtherName = !is_blank($name) ? (string)$name : 'User';
        $r->OtherPhoto = (string)(($iAmTeacher ? $r->StudentPhoto : $r->TeacherPhoto) ?? '');
        $r->OtherRole = $iAmTeacher ? 'Student' : 'Teacher';
        $r->Initials = initials($r->OtherName, 'U');
        $topic = array_filter([(string)($r->Subj ?? ''), (string)($r->Cls ?? '')], fn($v) => trim($v) !== '');
        $r->Topic = $topic ? implode(' · ', $topic) : str_preview((string)($r->Extra ?? ''), 60);
        return $r;
    }

    /** চ্যাট খোলা আছে কিনা (Accepted আর আমি এর টিচার বা স্টুডেন্ট) — না হলে null */
    public function GetChatThread(string $key, int $me): ?Obj
    {
        $this->EnsureChatTables();
        $k = self::parseChatKey($key);
        if (!$k) return null;
        if ($k[0] === 'grp') return $this->groupThread($k[1], $me);
        if ($k[0] === 'app') {
            $r = $this->one(self::CHAT_APP_SELECT . " WHERE a.`Id` = :id AND a.`Status` = 'Accepted' AND (a.`TeacherId` = :me OR p.`UserId` = :me2)", ['id' => $k[1], 'me' => $me, 'me2' => $me]);
        } elseif ($k[0] === 'dir') {
            $r = $this->one(self::CHAT_DIR_SELECT . " WHERE d.`Id` = :id AND (d.`TeacherId` = :me OR d.`StudentId` = :me2)", ['id' => $k[1], 'me' => $me, 'me2' => $me]);
        } else {
            $r = $this->one(self::CHAT_REQ_SELECT . " WHERE r.`Id` = :id AND r.`Status` = 'Accepted' AND (r.`TeacherUserId` = :me OR r.`StudentUserId` = :me2)", ['id' => $k[1], 'me' => $me, 'me2' => $me]);
        }
        return $r ? $this->mapChatThread($r, $me) : null;
    }

    /** আমার সব খোলা চ্যাট — শেষ মেসেজ আর না-পড়া সংখ্যা সহ, সাম্প্রতিকগুলো আগে */
    public function GetChatThreads(int $me): array
    {
        $this->EnsureChatTables();
        $rows = array_merge(
            $this->all(self::CHAT_APP_SELECT . " WHERE a.`Status` = 'Accepted' AND (a.`TeacherId` = :me OR p.`UserId` = :me2)", ['me' => $me, 'me2' => $me]),
            $this->all(self::CHAT_REQ_SELECT . " WHERE r.`Status` = 'Accepted' AND (r.`TeacherUserId` = :me OR r.`StudentUserId` = :me2)", ['me' => $me, 'me2' => $me]),
            $this->all(self::CHAT_DIR_SELECT . " WHERE d.`TeacherId` = :me OR d.`StudentId` = :me2", ['me' => $me, 'me2' => $me])
        );
        // গ্রুপ — শেষ মেসেজ আর আমার না-পড়া (LastReadId এর পরে অন্যদের মেসেজ)
        $groups = [];
        foreach ($this->all('SELECT `GroupId` FROM `ChatGroupMembers` WHERE `UserId` = :me', ['me' => $me]) as $gm) {
            $g = $this->groupThread((int)$gm->GroupId, $me);
            if (!$g) continue;
            $m = $this->one("SELECT `Body`, `SenderId`, `CreatedAt` FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` = :g ORDER BY `Id` DESC LIMIT 1", ['g' => $g->RefId]);
            $g->LastBody = $m ? (string)$m->Body : ''; $g->LastMine = $m ? (int)$m->SenderId === $me : false;
            $g->LastAt = $m ? (string)$m->CreatedAt : $g->AcceptedAt;
            $g->Unread = (int)$this->scalar("SELECT COUNT(*) FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` = :g AND `Id` > :l AND `SenderId` <> :me", ['g' => $g->RefId, 'l' => $g->LastReadId, 'me' => $me]);
            $groups[] = $g;
        }
        if (!$rows && !$groups) return [];
        $last = []; $unread = [];
        foreach (['app', 'req', 'dir'] as $kind) {
            $ids = array_map(fn($r) => (int)$r->RefId, array_filter($rows, fn($r) => $r->Kind === $kind));
            if (!$ids) continue;
            $in = implode(',', $ids);
            foreach ($this->all("SELECT m.`AppId`, m.`Body`, m.`SenderId`, m.`CreatedAt` FROM `ChatMessages` m
                    INNER JOIN (SELECT MAX(`Id`) AS MaxId FROM `ChatMessages` WHERE `Kind` = :k AND `AppId` IN ($in) GROUP BY `AppId`) x ON x.`MaxId` = m.`Id`", ['k' => $kind]) as $m) $last[$kind . (int)$m->AppId] = $m;
            foreach ($this->all("SELECT `AppId`, COUNT(*) AS N FROM `ChatMessages` WHERE `Kind` = :k AND `AppId` IN ($in) AND `SenderId` <> :me AND `ReadAt` IS NULL GROUP BY `AppId`", ['k' => $kind, 'me' => $me]) as $u) $unread[$kind . (int)$u->AppId] = (int)$u->N;
        }
        $out = [];
        foreach ($rows as $r) {
            $t = $this->mapChatThread($r, $me);
            $m = $last[$t->Kind . $t->RefId] ?? null;
            $t->LastBody = $m ? (string)$m->Body : '';
            $t->LastMine = $m ? (int)$m->SenderId === $me : false;
            $t->LastAt = $m ? (string)$m->CreatedAt : (string)($t->AcceptedAt ?: $t->StartedAt);
            $t->Unread = $unread[$t->Kind . $t->RefId] ?? 0;
            $out[] = $t;
        }
        $out = array_merge($out, $groups);
        usort($out, fn($a, $b) => strcmp($b->LastAt, $a->LastAt));
        return $out;
    }

    /** @return Obj[] পুরোনো থেকে নতুন; $after দিলে শুধু তার পরের, $before দিলে তার আগের (পুরোনো মেসেজ লোড) */
    public function GetChatMessages(Obj $t, int $after = 0, int $before = 0, int $limit = 50): array
    {
        $this->EnsureChatTables();
        $limit = max(1, min(100, $limit));
        $p = ['k' => $t->Kind, 'a' => $t->RefId];
        if ($after > 0) {
            $p['x'] = $after;
            $rows = $this->all("SELECT `Id`, `SenderId`, `Body`, `CreatedAt`, `ReadAt`, `ClassId` FROM `ChatMessages` WHERE `Kind` = :k AND `AppId` = :a AND `Id` > :x ORDER BY `Id` ASC LIMIT $limit", $p);
        } else {
            $w = '`Kind` = :k AND `AppId` = :a';
            if ($before > 0) { $w .= ' AND `Id` < :b'; $p['b'] = $before; }
            $rows = array_reverse($this->all("SELECT `Id`, `SenderId`, `Body`, `CreatedAt`, `ReadAt`, `ClassId` FROM `ChatMessages` WHERE $w ORDER BY `Id` DESC LIMIT $limit", $p));
        }
        foreach ($rows as $r) { $r->Id = (int)$r->Id; $r->SenderId = (int)$r->SenderId; $r->ClassId = (int)($r->ClassId ?? 0); }
        return $rows;
    }

    public function AddChatMessage(Obj $t, int $senderId, string $body, int $classId = 0): int
    {
        $this->EnsureChatTables();
        return (int)$this->insert('INSERT INTO `ChatMessages` (`Kind`, `AppId`, `SenderId`, `Body`, `CreatedAt`, `ClassId`) VALUES (:k, :a, :s, :b, :t, :c)',
            ['k' => $t->Kind, 'a' => $t->RefId, 's' => $senderId, 'b' => $body, 't' => now(), 'c' => $classId > 0 ? $classId : null]);
    }

    // ---------- অনলাইন ক্লাস ----------
    public function CreateChatClass(Obj $t, int $teacherId, string $title, string $link, string $platform, string $startsAt, int $duration): int
    {
        $this->EnsureChatTables();
        return (int)$this->insert('INSERT INTO `ChatClasses` (`Kind`, `AppId`, `TeacherId`, `Title`, `Link`, `Platform`, `StartsAt`, `DurationMin`, `Status`, `CreatedAt`)
            VALUES (:k, :a, :tid, :ti, :l, :p, :s, :d, \'Scheduled\', :c)', ['k' => $t->Kind, 'a' => $t->RefId, 'tid' => $teacherId, 'ti' => $title, 'l' => $link, 'p' => $platform, 's' => $startsAt, 'd' => $duration, 'c' => now()]);
    }

    /** এই চ্যাটের সব ক্লাস (নতুন গুলো পরে) */
    public function GetChatClasses(Obj $t): array
    {
        $this->EnsureChatTables();
        $rows = $this->all('SELECT * FROM `ChatClasses` WHERE `Kind` = :k AND `AppId` = :a ORDER BY `StartsAt` ASC, `Id` ASC', ['k' => $t->Kind, 'a' => $t->RefId]);
        foreach ($rows as $r) { $r->Id = (int)$r->Id; $r->TeacherId = (int)$r->TeacherId; $r->DurationMin = (int)$r->DurationMin; }
        return $rows;
    }

    public function GetChatClass(int $id): ?Obj
    {
        $this->EnsureChatTables();
        $r = $this->one('SELECT * FROM `ChatClasses` WHERE `Id` = :i', ['i' => $id]);
        if ($r) { $r->Id = (int)$r->Id; $r->TeacherId = (int)$r->TeacherId; $r->AppId = (int)$r->AppId; $r->DurationMin = (int)$r->DurationMin; }
        return $r;
    }

    public function CancelChatClass(int $id): void
    {
        $this->exec("UPDATE `ChatClasses` SET `Status` = 'Cancelled' WHERE `Id` = :i", ['i' => $id]);
    }

    /** অন্যজনের পাঠানো মেসেজগুলো পড়া হয়েছে চিহ্নিত করে */
    public function MarkChatRead(Obj $t, int $me): void
    {
        if ($t->Kind === 'grp') {
            $this->exec("UPDATE `ChatGroupMembers` SET `LastReadId` = (SELECT COALESCE(MAX(`Id`), 0) FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` = :g) WHERE `GroupId` = :g2 AND `UserId` = :me",
                ['g' => $t->RefId, 'g2' => $t->RefId, 'me' => $me]);
            return;
        }
        $this->exec('UPDATE `ChatMessages` SET `ReadAt` = :t WHERE `Kind` = :k AND `AppId` = :a AND `SenderId` <> :me AND `ReadAt` IS NULL', ['t' => now(), 'k' => $t->Kind, 'a' => $t->RefId, 'me' => $me]);
    }

    /** আমার পাঠানো মেসেজের মধ্যে অন্যজন কোনটা পর্যন্ত পড়েছে (Seen দেখানোর জন্য) */
    public function GetChatSeenUpTo(Obj $t, int $me): int
    {
        // গ্রুপ: সবাই কোন মেসেজ পর্যন্ত পড়েছে (সবচেয়ে কম যতদূর)
        if ($t->Kind === 'grp') return (int)$this->scalar('SELECT COALESCE(MIN(`LastReadId`), 0) FROM `ChatGroupMembers` WHERE `GroupId` = :g AND `UserId` <> :me', ['g' => $t->RefId, 'me' => $me]);
        return (int)$this->scalar('SELECT COALESCE(MAX(`Id`), 0) FROM `ChatMessages` WHERE `Kind` = :k AND `AppId` = :a AND `SenderId` = :me AND `ReadAt` IS NOT NULL', ['k' => $t->Kind, 'a' => $t->RefId, 'me' => $me]);
    }

    /** কতগুলো খোলা চ্যাটে না-পড়া মেসেজ আছে (নেভবারের ব্যাজ) */
    public function CountUnreadChats(int $me): int
    {
        try {
            return count(array_filter($this->GetChatThreads($me), fn($t) => $t->Unread > 0));
        } catch (Throwable $e) { error_log('[CountUnreadChats] ' . $e->getMessage()); return 0; }
    }

    /** এই চ্যাটে অন্যজনের জন্য আগে থেকেই না-পড়া মেসেজ আছে কিনা (তাহলে আবার নোটিফিকেশন পাঠাই না) */
    public function HasUnreadFrom(Obj $t, int $senderId): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `ChatMessages` WHERE `Kind` = :k AND `AppId` = :a AND `SenderId` = :s AND `ReadAt` IS NULL', ['k' => $t->Kind, 'a' => $t->RefId, 's' => $senderId]) > 1;
    }

    public function CountRecentChatMessages(int $senderId, int $seconds): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `ChatMessages` WHERE `SenderId` = :s AND `CreatedAt` >= :t', ['s' => $senderId, 't' => date('Y-m-d H:i:s', time() - $seconds)]);
    }

    // ---------- টিচারের কাছে আসা Direct Request ----------
    /** টিচারের নিজের কাছে আসা রিকোয়েস্ট (TeacherUserId অথবা পুরোনো গুলোতে নাম মিলিয়ে) */
    public function GetTeacherRequestsFor(int $teacherId, string $fullName): array
    {
        $this->EnsureTutorRequestUserColumns();
        try {
            return array_map([self::class, 'mapTutorRequest'], $this->all('SELECT * FROM `TutorRequests` WHERE `TeacherUserId` = :t OR (`TeacherUserId` IS NULL AND `TutorName` = :n) ORDER BY `CreatedAt` DESC, `Id` DESC',
                ['t' => $teacherId, 'n' => $fullName]));
        } catch (Throwable $e) { error_log('[GetTeacherRequestsFor] ' . $e->getMessage()); return []; }
    }

    /** টিচার Accept / Reject / Pending করে — নিজের রিকোয়েস্ট না হলে null */
    public function TeacherDecideRequest(int $reqId, int $teacherId, string $fullName, string $status): ?Obj
    {
        $this->EnsureTutorRequestUserColumns();
        $r = $this->one('SELECT * FROM `TutorRequests` WHERE `Id` = :i AND (`TeacherUserId` = :t OR (`TeacherUserId` IS NULL AND `TutorName` = :n))', ['i' => $reqId, 't' => $teacherId, 'n' => $fullName]);
        if (!$r) return null;
        $this->exec('UPDATE `TutorRequests` SET `Status` = :s, `TeacherUserId` = :t, `UpdatedAt` = :u WHERE `Id` = :i', ['s' => $status, 't' => $teacherId, 'u' => now(), 'i' => $reqId]);
        $r->Status = $status; $r->TeacherUserId = $teacherId;
        return self::mapTutorRequest($r);
    }

    /** এই শিক্ষার্থী এই টিচারকে শেষ কবে রিকোয়েস্ট পাঠিয়েছে (এখনো Pending) — ২৪ ঘণ্টার নিয়মের জন্য */
    public function LastPendingRequestAt(int $studentId, int $teacherId): ?string
    {
        $this->EnsureTutorRequestUserColumns();
        $v = $this->scalar("SELECT MAX(`CreatedAt`) FROM `TutorRequests` WHERE `StudentUserId` = :s AND `TeacherUserId` = :t AND `Status` = 'Pending'", ['s' => $studentId, 't' => $teacherId]);
        return $v ? (string)$v : null;
    }

    public function SetTutorRequestUsers(int $reqId, ?int $studentId, ?int $teacherId): void
    {
        $this->EnsureTutorRequestUserColumns();
        $this->exec('UPDATE `TutorRequests` SET `StudentUserId` = :s, `TeacherUserId` = :t WHERE `Id` = :i', ['s' => $studentId, 't' => $teacherId, 'i' => $reqId]);
    }
}
