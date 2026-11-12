<?php
/**
 * Super Admin আর সাধারণ Admin (অনুমতি সহ) + Activity Log — Db ক্লাসে trait হিসেবে যুক্ত।
 *  - AdminAccess: কোন অ্যাডমিন Super, আর সাধারণ অ্যাডমিন কোন কোন অংশে কাজ করতে পারবে (Perms = JSON)
 *  - AdminActivity: কোন অ্যাডমিন কখন কী করেছে
 * প্রথমবার (টেবিল খালি থাকলে) এখনকার সব অ্যাডমিন Super Admin হয়ে যায় — তাই কেউ অ্যাক্সেস হারায় না।
 * পাসওয়ার্ড কোডে কোথাও লেখা নেই — অ্যাডমিন প্যানেল থেকে বানানো হয়, ডাটাবেসে hash হয়ে থাকে।
 */
trait AdminAccessData
{
    private bool $aaReady = false;

    public function EnsureAdminAccessTables(): void
    {
        if ($this->aaReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "AdminAccess" ("UserId" INTEGER PRIMARY KEY, "IsSuper" INTEGER NOT NULL DEFAULT 0, "Perms" TEXT NULL,
                "CreatedBy" INTEGER NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "AdminActivity" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "AdminId" INTEGER NOT NULL, "Action" TEXT NOT NULL,
                "Detail" TEXT NULL, "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_AA_Admin" ON "AdminActivity" ("AdminId", "Id")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `AdminAccess` (`UserId` INT NOT NULL, `IsSuper` TINYINT(1) NOT NULL DEFAULT 0, `Perms` TEXT NULL,
                `CreatedBy` INT NULL, `UpdatedAt` DATETIME NULL, PRIMARY KEY (`UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `AdminActivity` (`Id` INT NOT NULL AUTO_INCREMENT, `AdminId` INT NOT NULL, `Action` VARCHAR(80) NOT NULL,
                `Detail` VARCHAR(500) NULL, `Ip` VARCHAR(64) NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_AA_Admin` (`AdminId`, `Id`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->aaReady = true;
        // প্রথমবার: এখনকার সব অ্যাডমিন = Super Admin
        try {
            if ((int)$this->scalar('SELECT COUNT(*) FROM `AdminAccess`') === 0) {
                foreach ($this->all("SELECT `Id` FROM `Users` WHERE `Role` = 'Admin'") as $u) {
                    $this->exec('INSERT INTO `AdminAccess` (`UserId`, `IsSuper`, `Perms`, `UpdatedAt`) VALUES (:u, 1, NULL, :t)', ['u' => (int)$u->Id, 't' => now()]);
                }
            }
        } catch (Throwable $e) { error_log('[AdminAccess bootstrap] ' . $e->getMessage()); }
    }

    /** ['super' => bool, 'perms' => string[]] — অ্যাডমিন না হলে null */
    public function GetAdminAccess(int $userId): ?array
    {
        try {
            $this->EnsureAdminAccessTables();
            $u = $this->one("SELECT u.`Role`, u.`IsActive`, a.`IsSuper`, a.`Perms` FROM `Users` u LEFT JOIN `AdminAccess` a ON a.`UserId` = u.`Id` WHERE u.`Id` = :u", ['u' => $userId]);
            if (!$u || $u->Role !== 'Admin') return null;
            $perms = json_decode((string)($u->Perms ?? ''), true);
            return ['super' => (int)($u->IsSuper ?? 0) === 1, 'perms' => is_array($perms) ? array_values(array_filter($perms, 'is_string')) : [], 'active' => (int)$u->IsActive === 1];
        } catch (Throwable $e) { error_log('[GetAdminAccess] ' . $e->getMessage()); return null; }
    }

    /** Super Admin: ইউজারের রোল বদল (User / Teacher / Admin) — Admin বানালে কোনো পেজের অনুমতি ছাড়া (Admins পেজ থেকে দেওয়া যায়) */
    public function SetUserRoleBySuper(int $userId, string $role): void
    {
        $this->EnsureAdminAccessTables();
        $this->exec('UPDATE `Users` SET `Role` = :r WHERE `Id` = :u', ['r' => $role, 'u' => $userId]);
        if ($role === 'Admin') {
            if (!(int)$this->scalar('SELECT COUNT(*) FROM `AdminAccess` WHERE `UserId` = :u', ['u' => $userId]))
                $this->exec("INSERT INTO `AdminAccess` (`UserId`, `IsSuper`, `Perms`) VALUES (:u, 0, '[]')", ['u' => $userId]);
        } else {
            $this->exec('DELETE FROM `AdminAccess` WHERE `UserId` = :u', ['u' => $userId]);
        }
    }

    /** সব অ্যাডমিন (Super আগে) — Admins পেজের জন্য */
    public function GetAdminList(): array
    {
        $this->EnsureAdminAccessTables();
        $rows = $this->all("SELECT u.`Id`, u.`UserName`, u.`FullName`, u.`Email`, u.`IsActive`, u.`ProfileImageLink`, u.`LastLoginAt`, u.`CreatedAt`, a.`IsSuper`, a.`Perms`
            FROM `Users` u LEFT JOIN `AdminAccess` a ON a.`UserId` = u.`Id` WHERE u.`Role` = 'Admin' ORDER BY COALESCE(a.`IsSuper`, 0) DESC, u.`Id`");
        foreach ($rows as $r) {
            $r->Id = (int)$r->Id; $r->IsSuper = (int)($r->IsSuper ?? 0) === 1; $r->IsActive = (int)$r->IsActive === 1;
            $p = json_decode((string)($r->Perms ?? ''), true); $r->PermList = is_array($p) ? $p : [];
            $r->Name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
            $r->Initials = initials($r->Name, 'A');
        }
        return $rows;
    }

    public function CountActiveSuperAdmins(int $exceptId = 0): int
    {
        $this->EnsureAdminAccessTables();
        return (int)$this->scalar("SELECT COUNT(*) FROM `AdminAccess` a INNER JOIN `Users` u ON u.`Id` = a.`UserId`
            WHERE a.`IsSuper` = 1 AND u.`Role` = 'Admin' AND u.`IsActive` = 1 AND a.`UserId` <> :x", ['x' => $exceptId]);
    }

    public function SetAdminAccess(int $userId, bool $super, array $perms, int $by): void
    {
        $this->EnsureAdminAccessTables();
        $json = $super ? null : json_encode(array_values(array_unique($perms)));
        if ($this->exec('UPDATE `AdminAccess` SET `IsSuper` = :s, `Perms` = :p, `UpdatedAt` = :t WHERE `UserId` = :u', ['s' => $super ? 1 : 0, 'p' => $json, 't' => now(), 'u' => $userId]) === 0
            && !(int)$this->scalar('SELECT COUNT(*) FROM `AdminAccess` WHERE `UserId` = :u', ['u' => $userId])) {
            $this->exec('INSERT INTO `AdminAccess` (`UserId`, `IsSuper`, `Perms`, `CreatedBy`, `UpdatedAt`) VALUES (:u, :s, :p, :b, :t)',
                ['u' => $userId, 's' => $super ? 1 : 0, 'p' => $json, 'b' => $by, 't' => now()]);
        }
    }

    /** নতুন অ্যাডমিন অ্যাকাউন্ট (ইমেইলে আগে অ্যাকাউন্ট থাকলে সেটাকেই অ্যাডমিন বানায়) → [id, wasExisting] */
    public function CreateOrPromoteAdmin(string $fullName, string $email, string $password): array
    {
        $this->EnsureAdminAccessTables();
        $ex = $this->one('SELECT `Id`, `UserName` FROM `Users` WHERE LOWER(`Email`) = LOWER(:e) ORDER BY `Id` LIMIT 1', ['e' => $email]);
        if ($ex) {
            $this->exec("UPDATE `Users` SET `Role` = 'Admin', `IsActive` = 1, `Password` = :p" . ($fullName !== '' ? ', `FullName` = :f' : '') . ' WHERE `Id` = :i',
                array_merge(['p' => password_hash($password, PASSWORD_DEFAULT), 'i' => (int)$ex->Id], $fullName !== '' ? ['f' => $fullName] : []));
            return [(int)$ex->Id, true];
        }
        $base = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', strstr($email, '@', true) ?: 'admin')) ?: 'admin';
        $un = $base; $n = 1;
        while ((int)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE LOWER(`UserName`) = LOWER(:u)', ['u' => $un])) $un = $base . (++$n);
        $this->query("INSERT INTO `Users` (`UserName`, `Password`, `FullName`, `Email`, `Role`, `IsActive`, `CreatedAt`) VALUES (:u, :p, :f, :e, 'Admin', 1, :c)",
            ['u' => $un, 'p' => password_hash($password, PASSWORD_DEFAULT), 'f' => $fullName !== '' ? $fullName : $un, 'e' => strtolower($email), 'c' => now()]);
        return [(int)$this->pdo->lastInsertId(), false];
    }

    /** অ্যাডমিন থেকে সাধারণ ইউজার বানানো (অ্যাকাউন্ট মোছে না) */
    public function RemoveAdmin(int $userId): void
    {
        $this->EnsureAdminAccessTables();
        $this->exec("UPDATE `Users` SET `Role` = 'User' WHERE `Id` = :u AND `Role` = 'Admin'", ['u' => $userId]);
        $this->exec('DELETE FROM `AdminAccess` WHERE `UserId` = :u', ['u' => $userId]);
    }

    public function SetUserActive(int $userId, bool $on): void
    {
        $this->exec('UPDATE `Users` SET `IsActive` = :a WHERE `Id` = :u', ['a' => $on ? 1 : 0, 'u' => $userId]);
    }

    public function LogAdminActivity(int $adminId, string $action, string $detail = ''): void
    {
        try {
            $this->EnsureAdminAccessTables();
            $this->exec('INSERT INTO `AdminActivity` (`AdminId`, `Action`, `Detail`, `Ip`, `CreatedAt`) VALUES (:a, :ac, :d, :ip, :t)', [
                'a' => $adminId, 'ac' => mb_substr($action, 0, 80), 'd' => $detail !== '' ? mb_substr($detail, 0, 500) : null,
                'ip' => mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64), 't' => now(),
            ]);
        } catch (Throwable $e) { error_log('[LogAdminActivity] ' . $e->getMessage()); }
    }

    /** @return Obj[] নতুন আগে; $adminId = 0 হলে সবার */
    public function GetAdminActivity(int $adminId, int $limit, int $offset, string $date = ''): array
    {
        $this->EnsureAdminAccessTables();
        $w = []; $p = [];
        if ($adminId > 0) { $w[] = 'l.`AdminId` = :a'; $p['a'] = $adminId; }
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $w[] = 'l.`CreatedAt` >= :d1 AND l.`CreatedAt` < :d2'; $p['d1'] = $date . ' 00:00:00'; $p['d2'] = date('Y-m-d', strtotime($date . ' +1 day')) . ' 00:00:00'; }
        return $this->all('SELECT l.*, u.`FullName`, u.`UserName` FROM `AdminActivity` l LEFT JOIN `Users` u ON u.`Id` = l.`AdminId`'
            . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY l.`Id` DESC LIMIT ' . max(1, min(200, $limit)) . ' OFFSET ' . max(0, $offset), $p);
    }
}
