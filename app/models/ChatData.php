<?php
/**
 * স্টুডেন্ট ↔ টিচার চ্যাট (মেসেঞ্জারের মতো) — Db ক্লাসে trait হিসেবে যুক্ত।
 *
 * নিয়ম: টিচার স্টুডেন্টের টিউশন পোস্টে Apply করে → স্টুডেন্ট Accept করলে দুজনের চ্যাট খোলে।
 * Reject (বা Undo করে Pending) করলে চ্যাট বন্ধ — কেউ আর দেখতে বা মেসেজ পাঠাতে পারে না।
 * একটা চ্যাট = একটা Accepted অ্যাপ্লিকেশন (TuitionCircularApplications.Id)।
 */
trait ChatData
{
    private bool $chatReady = false;

    public function EnsureChatTables(): void
    {
        if ($this->chatReady) return;
        $this->EnsureCircularApplicationsTable();
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ChatMessages" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "AppId" INTEGER NOT NULL,
                "SenderId" INTEGER NOT NULL, "Body" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL, "ReadAt" TEXT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Chat_App" ON "ChatMessages" ("AppId", "Id")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ChatMessages` (`Id` INT NOT NULL AUTO_INCREMENT, `AppId` INT NOT NULL, `SenderId` INT NOT NULL,
                `Body` TEXT NOT NULL, `CreatedAt` DATETIME NOT NULL, `ReadAt` DATETIME NULL, PRIMARY KEY (`Id`), KEY `IX_Chat_App` (`AppId`, `Id`),
                KEY `IX_Chat_Unread` (`AppId`, `ReadAt`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->chatReady = true;
    }

    private const CHAT_THREAD_SELECT = "SELECT a.`Id` AS AppId, a.`TeacherId`, p.`UserId` AS StudentId, a.`UpdatedAt` AS AcceptedAt, a.`CreatedAt` AS AppliedAt,
            p.`Subjects`, p.`ClassLevel`, p.`PreferredArea`, p.`Content` AS PostContent,
            t.`FullName` AS TeacherName, t.`UserName` AS TeacherUser, t.`ProfileImageLink` AS TeacherPhoto,
            s.`FullName` AS StudentName, s.`UserName` AS StudentUser, s.`ProfileImageLink` AS StudentPhoto
        FROM `TuitionCircularApplications` a
        INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId`
        LEFT JOIN `Users` t ON t.`Id` = a.`TeacherId`
        LEFT JOIN `Users` s ON s.`Id` = p.`UserId`";

    private function mapChatThread(Obj $r, int $me): Obj
    {
        $r->AppId = (int)$r->AppId; $r->TeacherId = (int)$r->TeacherId; $r->StudentId = (int)$r->StudentId;
        $iAmTeacher = $r->TeacherId === $me;
        $r->OtherId = $iAmTeacher ? $r->StudentId : $r->TeacherId;
        $name = $iAmTeacher ? ($r->StudentName ?: $r->StudentUser) : ($r->TeacherName ?: $r->TeacherUser);
        $r->OtherName = !is_blank($name) ? (string)$name : 'User';
        $r->OtherPhoto = (string)(($iAmTeacher ? $r->StudentPhoto : $r->TeacherPhoto) ?? '');
        $r->OtherRole = $iAmTeacher ? 'Student' : 'Teacher';
        $r->Initials = initials($r->OtherName, 'U');
        $topic = array_filter([(string)($r->Subjects ?? ''), (string)($r->ClassLevel ?? '')], fn($v) => trim($v) !== '');
        $r->Topic = $topic ? implode(' · ', $topic) : str_preview((string)($r->PostContent ?? ''), 60);
        return $r;
    }

    /** চ্যাট খোলা আছে কিনা (Accepted আর আমি এই অ্যাপ্লিকেশনের টিচার বা স্টুডেন্ট) — না হলে null */
    public function GetChatThread(int $appId, int $me): ?Obj
    {
        $this->EnsureChatTables();
        $r = $this->one(self::CHAT_THREAD_SELECT . " WHERE a.`Id` = :id AND a.`Status` = 'Accepted' AND (a.`TeacherId` = :me OR p.`UserId` = :me2)",
            ['id' => $appId, 'me' => $me, 'me2' => $me]);
        return $r ? $this->mapChatThread($r, $me) : null;
    }

    /** আমার সব খোলা চ্যাট — শেষ মেসেজ আর না-পড়া সংখ্যা সহ, সাম্প্রতিকগুলো আগে */
    public function GetChatThreads(int $me): array
    {
        $this->EnsureChatTables();
        $rows = $this->all(self::CHAT_THREAD_SELECT . " WHERE a.`Status` = 'Accepted' AND (a.`TeacherId` = :me OR p.`UserId` = :me2)", ['me' => $me, 'me2' => $me]);
        if (!$rows) return [];
        $ids = implode(',', array_map(fn($r) => (int)$r->AppId, $rows));
        $last = []; $unread = [];
        foreach ($this->all("SELECT m.`AppId`, m.`Body`, m.`SenderId`, m.`CreatedAt` FROM `ChatMessages` m
                INNER JOIN (SELECT `AppId`, MAX(`Id`) AS MaxId FROM `ChatMessages` WHERE `AppId` IN ($ids) GROUP BY `AppId`) x ON x.`MaxId` = m.`Id`") as $m) $last[(int)$m->AppId] = $m;
        foreach ($this->all("SELECT `AppId`, COUNT(*) AS N FROM `ChatMessages` WHERE `AppId` IN ($ids) AND `SenderId` <> :me AND `ReadAt` IS NULL GROUP BY `AppId`", ['me' => $me]) as $u) $unread[(int)$u->AppId] = (int)$u->N;
        $out = [];
        foreach ($rows as $r) {
            $t = $this->mapChatThread($r, $me);
            $m = $last[$t->AppId] ?? null;
            $t->LastBody = $m ? (string)$m->Body : '';
            $t->LastMine = $m ? (int)$m->SenderId === $me : false;
            $t->LastAt = $m ? (string)$m->CreatedAt : (string)($t->AcceptedAt ?: $t->AppliedAt);
            $t->Unread = $unread[$t->AppId] ?? 0;
            $out[] = $t;
        }
        usort($out, fn($a, $b) => strcmp($b->LastAt, $a->LastAt));
        return $out;
    }

    /** @return Obj[] পুরোনো থেকে নতুন; $after দিলে শুধু তার পরের, $before দিলে তার আগের (পুরোনো মেসেজ লোড) */
    public function GetChatMessages(int $appId, int $after = 0, int $before = 0, int $limit = 50): array
    {
        $this->EnsureChatTables();
        $limit = max(1, min(100, $limit));
        if ($after > 0) {
            $rows = $this->all("SELECT `Id`, `SenderId`, `Body`, `CreatedAt`, `ReadAt` FROM `ChatMessages` WHERE `AppId` = :a AND `Id` > :x ORDER BY `Id` ASC LIMIT $limit", ['a' => $appId, 'x' => $after]);
        } else {
            $p = ['a' => $appId]; $w = '`AppId` = :a';
            if ($before > 0) { $w .= ' AND `Id` < :b'; $p['b'] = $before; }
            $rows = array_reverse($this->all("SELECT `Id`, `SenderId`, `Body`, `CreatedAt`, `ReadAt` FROM `ChatMessages` WHERE $w ORDER BY `Id` DESC LIMIT $limit", $p));
        }
        foreach ($rows as $r) { $r->Id = (int)$r->Id; $r->SenderId = (int)$r->SenderId; }
        return $rows;
    }

    public function AddChatMessage(int $appId, int $senderId, string $body): int
    {
        $this->EnsureChatTables();
        return (int)$this->insert('INSERT INTO `ChatMessages` (`AppId`, `SenderId`, `Body`, `CreatedAt`) VALUES (:a, :s, :b, :t)',
            ['a' => $appId, 's' => $senderId, 'b' => $body, 't' => now()]);
    }

    /** অন্যজনের পাঠানো মেসেজগুলো পড়া হয়েছে চিহ্নিত করে */
    public function MarkChatRead(int $appId, int $me): void
    {
        $this->EnsureChatTables();
        $this->exec('UPDATE `ChatMessages` SET `ReadAt` = :t WHERE `AppId` = :a AND `SenderId` <> :me AND `ReadAt` IS NULL', ['t' => now(), 'a' => $appId, 'me' => $me]);
    }

    /** আমার পাঠানো শেষ মেসেজের মধ্যে অন্যজন কোনটা পর্যন্ত পড়েছে (Seen দেখানোর জন্য) */
    public function GetChatSeenUpTo(int $appId, int $me): int
    {
        return (int)$this->scalar('SELECT COALESCE(MAX(`Id`), 0) FROM `ChatMessages` WHERE `AppId` = :a AND `SenderId` = :me AND `ReadAt` IS NOT NULL', ['a' => $appId, 'me' => $me]);
    }

    /** কতগুলো খোলা চ্যাটে না-পড়া মেসেজ আছে (নেভবারের ব্যাজ) */
    public function CountUnreadChats(int $me): int
    {
        try {
            $this->EnsureChatTables();
            return (int)$this->scalar("SELECT COUNT(DISTINCT m.`AppId`) FROM `ChatMessages` m
                INNER JOIN `TuitionCircularApplications` a ON a.`Id` = m.`AppId` AND a.`Status` = 'Accepted'
                INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId`
                WHERE m.`ReadAt` IS NULL AND m.`SenderId` <> :me AND (a.`TeacherId` = :me2 OR p.`UserId` = :me3)", ['me' => $me, 'me2' => $me, 'me3' => $me]);
        } catch (Throwable $e) { error_log('[CountUnreadChats] ' . $e->getMessage()); return 0; }
    }

    /** এই চ্যাটে অন্যজনের জন্য আগে থেকেই না-পড়া মেসেজ আছে কিনা (তাহলে আবার নোটিফিকেশন পাঠাই না) */
    public function HasUnreadFrom(int $appId, int $senderId): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `ChatMessages` WHERE `AppId` = :a AND `SenderId` = :s AND `ReadAt` IS NULL', ['a' => $appId, 's' => $senderId]) > 1;
    }

    public function CountRecentChatMessages(int $senderId, int $seconds): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `ChatMessages` WHERE `SenderId` = :s AND `CreatedAt` >= :t', ['s' => $senderId, 't' => date('Y-m-d H:i:s', time() - $seconds)]);
    }
}
