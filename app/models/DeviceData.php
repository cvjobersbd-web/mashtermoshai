<?php
/**
 * এক ডিভাইসে লগইন — শিক্ষার্থী আর টিচার একসাথে একটার বেশি ডিভাইসে লগইন থাকতে পারে না।
 *  - প্রত্যেক ব্রাউজারে একটা গোপন কুকি (mm_dev); ডাটাবেসে শুধু তার hash থাকে
 *  - UserDevices.Status: Active (এই ডিভাইসে চালু) | Pending (নতুন ডিভাইসের অনুরোধ) | Revoked (অ্যাডমিন লগআউট করেছেন) | Rejected
 *  - প্রথমবার লগইন করা ডিভাইসটাই Active হয়। অন্য ডিভাইস থেকে লগইন করতে চাইলে অ্যাডমিনের কাছে অনুরোধ যায়;
 *    অ্যাডমিন Accept করলে নতুন ডিভাইস Active হয় আর আগেরটা নিজে থেকেই লগআউট হয়ে যায়।
 * টেবিল নিজে থেকেই তৈরি হয় — SQL ইমপোর্ট লাগবে না।
 */
trait DeviceData
{
    private bool $devReady = false;

    public function EnsureDeviceTable(): void
    {
        if ($this->devReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserDevices" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "DeviceHash" TEXT NOT NULL,
                "Label" TEXT NULL, "Ip" TEXT NULL, "Status" TEXT NOT NULL DEFAULT \'Active\', "Note" TEXT NULL, "CreatedAt" TEXT NOT NULL, "LastSeenAt" TEXT NULL,
                "DecidedBy" INTEGER NULL, "DecidedAt" TEXT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_UD_User" ON "UserDevices" ("UserId", "Status")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserDevices` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `DeviceHash` CHAR(64) NOT NULL,
                `Label` VARCHAR(120) NULL, `Ip` VARCHAR(64) NULL, `Status` VARCHAR(10) NOT NULL DEFAULT 'Active', `Note` VARCHAR(300) NULL, `CreatedAt` DATETIME NOT NULL,
                `LastSeenAt` DATETIME NULL, `DecidedBy` INT NULL, `DecidedAt` DATETIME NULL, PRIMARY KEY (`Id`), KEY `IX_UD_User` (`UserId`, `Status`), KEY `IX_UD_Hash` (`DeviceHash`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // অটো Approve এর ইতিহাস — কে, কবে, কোন ডিভাইস চালু হলো আর কোনটা লগআউট হলো
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "DeviceAutoLog" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "NewLabel" TEXT NULL, "OldLabel" TEXT NULL, "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `DeviceAutoLog` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `NewLabel` VARCHAR(120) NULL, `OldLabel` VARCHAR(120) NULL,
                `Ip` VARCHAR(64) NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_DAL_User` (`UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->devReady = true;
    }

    /** একসাথে কয়টা ডিভাইসে লগইন থাকা যাবে — ১ (ডিফল্ট) বা ২ (Admin → Devices এর "২টা ডিভাইসে লগইন" সুইচ) */
    public function DeviceMax(): int
    {
        return ($this->GetSiteContent(['dev_max' => '1'])['dev_max'] ?? '1') === '2' ? 2 : 1;
    }

    /** এই ইউজারের চালু ডিভাইসগুলো (শেষ ব্যবহার অনুযায়ী — পুরোনোটা আগে) */
    public function GetActiveDevices(int $userId): array
    {
        $this->EnsureDeviceTable();
        return $this->all("SELECT * FROM `UserDevices` WHERE `UserId` = :u AND `Status` = 'Active' ORDER BY COALESCE(`LastSeenAt`, `CreatedAt`) ASC, `Id` ASC", ['u' => $userId]);
    }

    /** নতুন ডিভাইস চালুর জন্য জায়গা খালি করা — সীমা ভরা থাকলে সবচেয়ে কম ব্যবহার হওয়া (পুরোনো) ডিভাইস লগআউট; লগআউট হওয়া ডিভাইসগুলো ফেরত */
    private function makeRoomForDevice(int $userId, int $adminId, int $exceptId = 0): array
    {
        $act = array_values(array_filter($this->GetActiveDevices($userId), fn($x) => (int)$x->Id !== $exceptId));
        $out = [];
        while (count($act) >= $this->DeviceMax()) {
            $old = array_shift($act); $out[] = $old;
            $this->exec("UPDATE `UserDevices` SET `Status` = 'Revoked', `DecidedBy` = :a, `DecidedAt` = :t WHERE `Id` = :i", ['a' => $adminId, 't' => now(), 'i' => (int)$old->Id]);
        }
        return $out;
    }

    /** অটো Approve চালু কিনা (Admin → Devices, ডিফল্ট বন্ধ) — চালু থাকলে অনুরোধ দিলেই নতুন ডিভাইস চালু, আগেরটা লগআউট */
    public function DeviceAutoOn(): bool
    {
        return false;   // অটো Approve সরানো হয়েছে
    }

    /** অনুরোধ নিজে থেকেই Approve — আগের ডিভাইস লগআউট, ইতিহাসে লেখা থাকে */
    public function AutoApproveDevice(int $requestId): ?Obj
    {
        $this->EnsureDeviceTable();
        $d = $this->one('SELECT * FROM `UserDevices` WHERE `Id` = :i', ['i' => $requestId]);
        if (!$d || $d->Status !== 'Pending') return null;
        $d = $this->ApproveDevice($requestId, 0);   // DecidedBy 0 = সিস্টেম (অটো)
        if (!$d) return null;
        $olds = array_filter(array_map(fn($x) => (string)$x->Label, $d->Revoked ?? []));
        $this->insert('INSERT INTO `DeviceAutoLog` (`UserId`, `NewLabel`, `OldLabel`, `Ip`, `CreatedAt`) VALUES (:u, :n, :o, :ip, :t)',
            ['u' => (int)$d->UserId, 'n' => $d->Label, 'o' => $olds ? implode(', ', $olds) : null, 'ip' => $d->Ip, 't' => now()]);
        return $d;
    }

    public function CountDeviceAutoLog(): int
    {
        try { $this->EnsureDeviceTable(); return (int)$this->scalar('SELECT COUNT(*) FROM `DeviceAutoLog`'); } catch (Throwable $e) { return 0; }
    }

    /** অটো Approve এর তালিকা (নতুন আগে) — $q = নাম / username / ইমেইল / মোবাইল */
    public function GetDeviceAutoLog(string $q = ''): array
    {
        $this->EnsureDeviceTable();
        $w = '1 = 1'; $a = [];
        if ($q !== '') { $w = '(u.`FullName` LIKE :q OR u.`UserName` LIKE :q2 OR u.`Email` LIKE :q3 OR u.`MobileNumber` LIKE :q4)'; $a = ['q' => "%$q%", 'q2' => "%$q%", 'q3' => "%$q%", 'q4' => "%$q%"]; }
        $rows = $this->all("SELECT l.*, u.`FullName`, u.`UserName`, u.`Role`, u.`MobileNumber` FROM `DeviceAutoLog` l LEFT JOIN `Users` u ON u.`Id` = l.`UserId` WHERE $w ORDER BY l.`Id` DESC", $a);
        foreach ($rows as $r) { $r->UserId = (int)$r->UserId; $r->Name = !is_blank($r->FullName) ? (string)$r->FullName : ((string)$r->UserName ?: 'Deleted user'); }
        return $rows;
    }

    /** ডিভাইসের সীমা চালু কিনা — শুধু Admin → Devices এর "২টা ডিভাইসে লগইন" সুইচ (বন্ধ থাকলে কোনো সীমা নেই)।
     *  আগের "এক ডিভাইসে লগইনের নিয়ম" আর "অটো Approve" অ্যাডমিনের কথায় সরানো হয়েছে। */
    public function DeviceLockOn(): bool
    {
        return $this->DeviceMax() === 2;
    }

    public function GetActiveDevice(int $userId): ?Obj
    {
        $this->EnsureDeviceTable();
        return $this->one("SELECT * FROM `UserDevices` WHERE `UserId` = :u AND `Status` = 'Active' ORDER BY `Id` DESC LIMIT 1", ['u' => $userId]);
    }

    private function deviceRow(int $userId, string $hash): ?Obj
    {
        return $this->one('SELECT * FROM `UserDevices` WHERE `UserId` = :u AND `DeviceHash` = :h ORDER BY `Id` DESC LIMIT 1', ['u' => $userId, 'h' => $hash]);
    }

    /**
     * লগইনের সময় (পাসওয়ার্ড ঠিক হওয়ার পরে) — 'ok' (এই ডিভাইসে লগইন করা যাবে) | 'pending' | 'rejected' | 'blocked' (অন্য ডিভাইসে চালু, অনুরোধ এখনো পাঠানো হয়নি)
     * কোনো Active ডিভাইস না থাকলে এই ডিভাইসটাই Active হয়ে যায়।
     */
    public function CheckLoginDevice(int $userId, string $hash, string $label, string $ip): string
    {
        $this->EnsureDeviceTable();
        $mine = $this->deviceRow($userId, $hash);
        if ($mine && $mine->Status === 'Active') {
            $this->exec('UPDATE `UserDevices` SET `LastSeenAt` = :t, `Ip` = :ip, `Label` = :l WHERE `Id` = :i', ['t' => now(), 'ip' => $ip, 'l' => $label, 'i' => (int)$mine->Id]);
            return 'ok';
        }
        if (count($this->GetActiveDevices($userId)) < $this->DeviceMax()) {
            // প্রথম ডিভাইস / সীমার মধ্যে (২টা ডিভাইস চালু থাকলে ২য়টা) / অ্যাডমিন আগেরটা লগআউট করেছেন — এটাও চালু ডিভাইস
            if ($mine) $this->exec("UPDATE `UserDevices` SET `Status` = 'Active', `LastSeenAt` = :t, `Ip` = :ip, `Label` = :l WHERE `Id` = :i", ['t' => now(), 'ip' => $ip, 'l' => $label, 'i' => (int)$mine->Id]);
            else $this->insert("INSERT INTO `UserDevices` (`UserId`, `DeviceHash`, `Label`, `Ip`, `Status`, `CreatedAt`, `LastSeenAt`) VALUES (:u, :h, :l, :ip, 'Active', :t, :t2)",
                ['u' => $userId, 'h' => $hash, 'l' => $label, 'ip' => $ip, 't' => now(), 't2' => now()]);
            return 'ok';
        }
        // ২টা ডিভাইসের নিয়মে ৩য় ডিভাইস একদম বন্ধ — অনুরোধও পাঠানো যায় না
        if ($this->DeviceMax() > 1) return 'full';
        if ($mine && $mine->Status === 'Pending') return 'pending';
        if ($mine && $mine->Status === 'Rejected' && strtotime((string)$mine->DecidedAt) > time() - 86400) return 'rejected';
        return 'blocked';
    }

    /** নতুন ডিভাইসের অনুরোধ (আগে থাকলে আপডেট) — অনুরোধের Id */
    public function RequestDevice(int $userId, string $hash, string $label, string $ip, string $note): int
    {
        $this->EnsureDeviceTable();
        $mine = $this->deviceRow($userId, $hash);
        if ($mine && $mine->Status !== 'Active') {
            $this->exec("UPDATE `UserDevices` SET `Status` = 'Pending', `Label` = :l, `Ip` = :ip, `Note` = :n, `CreatedAt` = :t, `DecidedBy` = NULL, `DecidedAt` = NULL WHERE `Id` = :i",
                ['l' => $label, 'ip' => $ip, 'n' => $note !== '' ? $note : null, 't' => now(), 'i' => (int)$mine->Id]);
            return (int)$mine->Id;
        }
        return (int)$this->insert("INSERT INTO `UserDevices` (`UserId`, `DeviceHash`, `Label`, `Ip`, `Status`, `Note`, `CreatedAt`) VALUES (:u, :h, :l, :ip, 'Pending', :n, :t)",
            ['u' => $userId, 'h' => $hash, 'l' => $label, 'ip' => $ip, 'n' => $note !== '' ? $note : null, 't' => now()]);
    }

    /** লগইন থাকা অবস্থায় প্রতি রিকোয়েস্টে: এই ডিভাইস কি এখনো চালু? (আগে কোনো ডিভাইস না থাকলে এটাকেই চালু ধরা হয়) */
    public function IsDeviceStillActive(int $userId, string $hash, string $label, string $ip): bool
    {
        $this->EnsureDeviceTable();
        $mine = $this->deviceRow($userId, $hash);
        if ($mine && $mine->Status === 'Active') {
            if (!$mine->LastSeenAt || strtotime((string)$mine->LastSeenAt) < time() - 300) $this->exec('UPDATE `UserDevices` SET `LastSeenAt` = :t, `Ip` = :ip WHERE `Id` = :i', ['t' => now(), 'ip' => $ip, 'i' => (int)$mine->Id]);
            return true;
        }
        // এই নিয়ম চালুর আগে থেকে লগইন থাকা (কোনো ডিভাইস রেকর্ড নেই) — এই ডিভাইসটাকেই চালু ধরা হয়
        if (!$mine && count($this->GetActiveDevices($userId)) < $this->DeviceMax()) { $this->CheckLoginDevice($userId, $hash, $label, $ip); return true; }
        return false;   // লগআউট করা / অনুরোধ অপেক্ষায় / অন্য ডিভাইস চালু
    }

    /** অ্যাডমিন: অনুরোধ Accept — নতুন ডিভাইস চালু, আগের সব ডিভাইস লগআউট */
    public function ApproveDevice(int $id, int $adminId): ?Obj
    {
        $this->EnsureDeviceTable();
        $d = $this->one('SELECT * FROM `UserDevices` WHERE `Id` = :i', ['i' => $id]);
        if (!$d || $d->Status !== 'Pending') return null;
        // সীমা (১ বা ২) ভরা থাকলে সবচেয়ে পুরোনো চালু ডিভাইস লগআউট — বাকিটা চালু থাকে
        $d->Revoked = $this->makeRoomForDevice((int)$d->UserId, $adminId, $id);
        $this->exec("UPDATE `UserDevices` SET `Status` = 'Active', `DecidedBy` = :a, `DecidedAt` = :t WHERE `Id` = :i", ['a' => $adminId, 't' => now(), 'i' => $id]);
        return $d;
    }

    public function RejectDevice(int $id, int $adminId): ?Obj
    {
        $this->EnsureDeviceTable();
        $d = $this->one('SELECT * FROM `UserDevices` WHERE `Id` = :i', ['i' => $id]);
        if (!$d || $d->Status !== 'Pending') return null;
        $this->exec("UPDATE `UserDevices` SET `Status` = 'Rejected', `DecidedBy` = :a, `DecidedAt` = :t WHERE `Id` = :i", ['a' => $adminId, 't' => now(), 'i' => $id]);
        return $d;
    }

    /** অ্যাডমিন: চালু ডিভাইস থেকে লগআউট (পরের বার যে ডিভাইসে লগইন করবে সেটাই চালু হবে) */
    public function RevokeDevice(int $id, int $adminId): ?Obj
    {
        $this->EnsureDeviceTable();
        $d = $this->one('SELECT * FROM `UserDevices` WHERE `Id` = :i', ['i' => $id]);
        if (!$d || $d->Status !== 'Active') return null;
        $this->exec("UPDATE `UserDevices` SET `Status` = 'Revoked', `DecidedBy` = :a, `DecidedAt` = :t WHERE `Id` = :i", ['a' => $adminId, 't' => now(), 'i' => $id]);
        return $d;
    }

    public function CountPendingDevices(): int
    {
        return 0;   // ২টা ডিভাইসের নিয়মে অনুরোধ নেই — সাইডবারে পুরোনো অনুরোধের সংখ্যা দেখায় না
    }

    /** অ্যাডমিন পেজ: $status = Pending|Active|all, $q = নাম / username / ইমেইল / মোবাইল */
    public function GetDevicesAdmin(string $status, string $q = '', int $limit = 300): array
    {
        $this->EnsureDeviceTable();
        $w = ["u.`Role` IN ('User', 'Teacher')"]; $a = [];
        if ($status === 'Pending' || $status === 'Active') { $w[] = 'd.`Status` = :s'; $a['s'] = $status; }
        else $w[] = "d.`Status` IN ('Active', 'Pending', 'Revoked', 'Rejected')";
        if ($q !== '') { $w[] = '(u.`FullName` LIKE :q OR u.`UserName` LIKE :q2 OR u.`Email` LIKE :q3 OR u.`MobileNumber` LIKE :q4)'; $a += ['q' => "%$q%", 'q2' => "%$q%", 'q3' => "%$q%", 'q4' => "%$q%"]; }
        $rows = $this->all('SELECT d.*, u.`FullName`, u.`UserName`, u.`Role`, u.`Email`, u.`MobileNumber`,
                (SELECT GROUP_CONCAT(x.`Label`) FROM `UserDevices` x WHERE x.`UserId` = d.`UserId` AND x.`Status` = \'Active\' AND x.`Id` <> d.`Id`) AS CurrentLabel,
                (SELECT x.`LastSeenAt` FROM `UserDevices` x WHERE x.`UserId` = d.`UserId` AND x.`Status` = \'Active\' AND x.`Id` <> d.`Id` ORDER BY x.`Id` DESC LIMIT 1) AS CurrentSeen
            FROM `UserDevices` d INNER JOIN `Users` u ON u.`Id` = d.`UserId` WHERE ' . implode(' AND ', $w) . '
            ORDER BY CASE d.`Status` WHEN \'Pending\' THEN 0 WHEN \'Active\' THEN 1 ELSE 2 END, COALESCE(d.`LastSeenAt`, d.`CreatedAt`) DESC LIMIT ' . max(1, min(1000, $limit)), $a);
        foreach ($rows as $r) { $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; $r->Name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName; }
        return $rows;
    }
}
