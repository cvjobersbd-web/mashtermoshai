<?php
/**
 * "Who viewed your profile" (LinkedIn এর মতো) — Db ক্লাসে trait হিসেবে যুক্ত।
 *  - ProfileViews: কে কার প্রোফাইল কবে দেখেছে (একজন দর্শক দিনে একটা সারি, Hits = সেদিন কতবার)
 *  - ProfileViewSeen: মালিক শেষ কবে তালিকাটা দেখেছেন (নতুন দর্শকের ব্যাজের জন্য)
 * নিজের প্রোফাইল আর অ্যাডমিনের দেখা গোনা হয় না। টেবিল না থাকলে নিজে থেকে তৈরি হয়।
 */
trait ProfileViewData
{
    private bool $pvReady = false;

    public function EnsureProfileViewTables(): void
    {
        if ($this->pvReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfileViews" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "ProfileUserId" INTEGER NOT NULL, "ViewerId" INTEGER NOT NULL,
                "ViewDate" TEXT NOT NULL, "LastViewedAt" TEXT NOT NULL, "Hits" INTEGER NOT NULL DEFAULT 1)');
            $this->pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS "UX_PV_Day" ON "ProfileViews" ("ProfileUserId", "ViewerId", "ViewDate")');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfileViewSeen" ("UserId" INTEGER PRIMARY KEY, "SeenAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfileViews` (`Id` INT NOT NULL AUTO_INCREMENT, `ProfileUserId` INT NOT NULL, `ViewerId` INT NOT NULL,
                `ViewDate` DATE NOT NULL, `LastViewedAt` DATETIME NOT NULL, `Hits` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_PV_Day` (`ProfileUserId`, `ViewerId`, `ViewDate`), KEY `IX_PV_Profile` (`ProfileUserId`, `LastViewedAt`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfileViewSeen` (`UserId` INT NOT NULL, `SeenAt` DATETIME NOT NULL, PRIMARY KEY (`UserId`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->pvReady = true;
        $this->retireFriendsOnce();
    }

    /**
     * ফ্রেন্ড সিস্টেম বন্ধ — একবারই চলে: "Friends" প্রাইভেসির পুরোনো পোস্টগুলো "Only me" হয়ে যায়
     * (যাতে কারো ব্যক্তিগত পোস্ট হঠাৎ Public না হয়ে যায়)।
     */
    private function retireFriendsOnce(): void
    {
        try {
            if (($this->GetSiteContent(['friends_retired' => ''])['friends_retired'] ?? '') !== '') return;
            try { $this->exec("UPDATE `ProfilePosts` SET `Privacy` = 'OnlyMe' WHERE `Privacy` = 'Friends'"); } catch (Throwable $e) { /* টেবিল এখনো নেই */ }
            $this->SaveSiteContent(['friends_retired' => '1']);
        } catch (Throwable $e) { error_log('[retireFriends] ' . $e->getMessage()); }
    }

    /** কেউ প্রোফাইল দেখলে লিখে রাখে (একই দিনে আবার দেখলে Hits বাড়ে) */
    public function RecordProfileView(int $profileUserId, int $viewerId): void
    {
        if ($profileUserId === $viewerId || $profileUserId <= 0 || $viewerId <= 0) return;
        try {
            $this->EnsureProfileViewTables();
            $day = date('Y-m-d');
            $n = $this->exec('UPDATE `ProfileViews` SET `Hits` = `Hits` + 1, `LastViewedAt` = :t WHERE `ProfileUserId` = :p AND `ViewerId` = :v AND `ViewDate` = :d',
                ['t' => now(), 'p' => $profileUserId, 'v' => $viewerId, 'd' => $day]);
            if ($n === 0) {
                try {
                    $this->exec('INSERT INTO `ProfileViews` (`ProfileUserId`, `ViewerId`, `ViewDate`, `LastViewedAt`, `Hits`) VALUES (:p, :v, :d, :t, 1)',
                        ['p' => $profileUserId, 'v' => $viewerId, 'd' => $day, 't' => now()]);
                } catch (Throwable $e) { /* একই মুহূর্তে দুইবার — ঠিক আছে */ }
            }
        } catch (Throwable $e) { error_log('[RecordProfileView] ' . $e->getMessage()); }
    }

    private function pvSince(int $days): string { return date('Y-m-d', strtotime('-' . max(0, $days - 1) . ' days')); }

    /**
     * কারা দেখেছে (নতুন আগে) — $role: '' | 'Teacher' | 'User'
     * @return Obj[] Id, Name, Image, Initials, Role, Sub, LastViewedAt, Visits, IsNew
     */
    public function GetProfileViewers(int $userId, int $days = 90, string $role = '', int $limit = 30, int $offset = 0, ?string $seenAt = null): array
    {
        try {
            $this->EnsureProfileViewTables();
            $p = ['u' => $userId, 's' => $this->pvSince($days)];
            $roleSql = '';
            if ($role === 'Teacher') $roleSql = " AND u.`Role` = 'Teacher'";
            elseif ($role === 'User') $roleSql = " AND u.`Role` = 'User'";
            $rows = $this->all("SELECT v.`ViewerId`, MAX(v.`LastViewedAt`) AS LastAt, SUM(v.`Hits`) AS Visits,
                    u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`Role`, u.`InstitutionName`
                FROM `ProfileViews` v INNER JOIN `Users` u ON u.`Id` = v.`ViewerId`
                WHERE v.`ProfileUserId` = :u AND v.`ViewDate` >= :s AND u.`IsActive` = 1 AND u.`Role` <> 'Admin'$roleSql
                GROUP BY v.`ViewerId`, u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`Role`, u.`InstitutionName`
                ORDER BY LastAt DESC LIMIT " . max(1, min(100, $limit)) . ' OFFSET ' . max(0, $offset), $p);
        } catch (Throwable $e) { error_log('[GetProfileViewers] ' . $e->getMessage()); return []; }
        $susp = [];
        try { $susp = $this->GetActiveSuspensionsMap(); } catch (Throwable $e) { }
        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->ViewerId;
            if (isset($susp[$id])) continue;
            $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
            $headline = '';
            try { $x = $this->one('SELECT `Headline`, `City` FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $id]); if ($x) $headline = trim((string)($x->Headline ?? '')); } catch (Throwable $e) { }
            $out[] = obj([
                'Id' => $id, 'Name' => $name, 'Image' => (string)($r->ProfileImageLink ?? ''), 'Initials' => initials($name, 'U'),
                'Role' => $r->Role === 'Teacher' ? 'Teacher' : 'Student',
                'Sub' => $headline !== '' ? $headline : (!is_blank($r->InstitutionName) ? (string)$r->InstitutionName : ($r->Role === 'Teacher' ? 'Tutor' : 'Student')),
                'LastViewedAt' => (string)$r->LastAt, 'Visits' => (int)$r->Visits,
                'IsNew' => $seenAt === null || strtotime((string)$r->LastAt) > strtotime($seenAt),
            ]);
        }
        return $out;
    }

    /** পরিসংখ্যান: views7, prev7, views90, unique90, teachers90, students90, series (শেষ ৩০ দিন, প্রতিদিন কতজন) */
    public function GetProfileViewStats(int $userId): array
    {
        $st = ['views7' => 0, 'prev7' => 0, 'views90' => 0, 'unique90' => 0, 'teachers90' => 0, 'students90' => 0, 'series' => []];
        try {
            $this->EnsureProfileViewTables();
            $base = "FROM `ProfileViews` v INNER JOIN `Users` u ON u.`Id` = v.`ViewerId` WHERE v.`ProfileUserId` = :u AND u.`IsActive` = 1 AND u.`Role` <> 'Admin'";
            $st['views7'] = (int)$this->scalar("SELECT COUNT(*) $base AND v.`ViewDate` >= :s", ['u' => $userId, 's' => $this->pvSince(7)]);
            $st['prev7'] = (int)$this->scalar("SELECT COUNT(*) $base AND v.`ViewDate` >= :s AND v.`ViewDate` < :e", ['u' => $userId, 's' => $this->pvSince(14), 'e' => $this->pvSince(7)]);
            $st['views90'] = (int)$this->scalar("SELECT COUNT(*) $base AND v.`ViewDate` >= :s", ['u' => $userId, 's' => $this->pvSince(90)]);
            foreach ($this->all("SELECT u.`Role`, COUNT(DISTINCT v.`ViewerId`) AS N $base AND v.`ViewDate` >= :s GROUP BY u.`Role`", ['u' => $userId, 's' => $this->pvSince(90)]) as $r) {
                $st['unique90'] += (int)$r->N;
                if ($r->Role === 'Teacher') $st['teachers90'] += (int)$r->N; else $st['students90'] += (int)$r->N;
            }
            $by = [];
            foreach ($this->all("SELECT v.`ViewDate` AS D, COUNT(*) AS N $base AND v.`ViewDate` >= :s GROUP BY v.`ViewDate`", ['u' => $userId, 's' => $this->pvSince(30)]) as $r) $by[substr((string)$r->D, 0, 10)] = (int)$r->N;
            for ($i = 29; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i days")); $st['series'][] = ['date' => $d, 'n' => $by[$d] ?? 0]; }
        } catch (Throwable $e) { error_log('[GetProfileViewStats] ' . $e->getMessage()); }
        return $st;
    }

    public function GetProfileViewsSeenAt(int $userId): ?string
    {
        try { $this->EnsureProfileViewTables(); $v = $this->scalar('SELECT `SeenAt` FROM `ProfileViewSeen` WHERE `UserId` = :u', ['u' => $userId]); return $v ? (string)$v : null; }
        catch (Throwable $e) { return null; }
    }

    /** শেষবার তালিকা দেখার পর কতজন নতুন করে দেখেছে (নেভবারের ব্যাজ) */
    public function CountNewProfileViewers(int $userId): int
    {
        try {
            $this->EnsureProfileViewTables();
            $seen = $this->GetProfileViewsSeenAt($userId) ?? date('Y-m-d H:i:s', strtotime('-30 days'));
            return (int)$this->scalar("SELECT COUNT(DISTINCT v.`ViewerId`) FROM `ProfileViews` v INNER JOIN `Users` u ON u.`Id` = v.`ViewerId`
                WHERE v.`ProfileUserId` = :u AND v.`LastViewedAt` > :s AND u.`IsActive` = 1 AND u.`Role` <> 'Admin'", ['u' => $userId, 's' => $seen]);
        } catch (Throwable $e) { return 0; }
    }

    public function MarkProfileViewsSeen(int $userId): void
    {
        try {
            $this->EnsureProfileViewTables();
            if ($this->exec('UPDATE `ProfileViewSeen` SET `SeenAt` = :t WHERE `UserId` = :u', ['t' => now(), 'u' => $userId]) === 0) {
                $this->exec('INSERT INTO `ProfileViewSeen` (`UserId`, `SeenAt`) VALUES (:u, :t)', ['u' => $userId, 't' => now()]);
            }
        } catch (Throwable $e) { error_log('[MarkProfileViewsSeen] ' . $e->getMessage()); }
    }

    /** ইউজার মুছে ফেললে তার দেখা/দেখানোর রেকর্ডও মুছে যায় */
    public function DeleteProfileViewsOfUser(int $userId): void
    {
        try {
            $this->EnsureProfileViewTables();
            $this->exec('DELETE FROM `ProfileViews` WHERE `ProfileUserId` = :a OR `ViewerId` = :b', ['a' => $userId, 'b' => $userId]);
            $this->exec('DELETE FROM `ProfileViewSeen` WHERE `UserId` = :u', ['u' => $userId]);
        } catch (Throwable $e) { error_log('[DeleteProfileViewsOfUser] ' . $e->getMessage()); }
    }
}
