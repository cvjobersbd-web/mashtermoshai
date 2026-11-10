<?php
/**
 * রেফারেল (Referral) — Db ক্লাসে trait হিসেবে যুক্ত।
 *  - প্রত্যেক ইউজারের একটা রেফারেল কোড: ReferralCodes (UserId, Code)
 *  - কারো লিংক দিয়ে নতুন কেউ সাইনআপ করলে: Referrals এ Status = Waiting (বোনাস ০)
 *  - সেই ইউজার প্রথম প্যাকেজ কিনলে (পেমেন্ট Approve হলে) → Status = Credited, Bonus = সেটিংয়ের পরিমাণ (ডিফল্ট ৳10)
 *  - অ্যাডমিন ভুয়া রেফারেল বাতিল করলে → Revoked
 *  - টাকা তোলার আবেদন: ReferralWithdrawals (Status = Pending | Paid | Rejected) — অ্যাডমিন টাকা পাঠিয়ে Paid করে
 *  ব্যালেন্স = মোট Credited বোনাস − (Pending + Paid উইথড্র)। Rejected হলে টাকা আবার ব্যালেন্সে ফিরে আসে।
 *  বোনাসের পরিমাণ / সর্বনিম্ন উইথড্র / চালু-বন্ধ: SiteContents (ref_bonus, ref_min_withdraw, ref_enabled)
 */
trait ReferralData
{
    private bool $refReady = false;
    public const REF_METHODS = ['bKash', 'Nagad', 'Rocket', 'Bank'];

    public function EnsureReferralTables(): void
    {
        if ($this->refReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ReferralCodes" ("UserId" INTEGER PRIMARY KEY, "Code" TEXT NOT NULL UNIQUE, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Referrals" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "ReferrerId" INTEGER NOT NULL, "ReferredUserId" INTEGER NOT NULL UNIQUE,
                "Bonus" INTEGER NOT NULL, "Status" TEXT NOT NULL DEFAULT \'Credited\', "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Ref_Referrer" ON "Referrals" ("ReferrerId")');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ReferralWithdrawals" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Amount" INTEGER NOT NULL,
                "Method" TEXT NOT NULL, "AccountName" TEXT NULL, "AccountNumber" TEXT NOT NULL, "BankName" TEXT NULL, "Branch" TEXT NULL,
                "Status" TEXT NOT NULL DEFAULT \'Pending\', "TxnId" TEXT NULL, "AdminNote" TEXT NULL, "ProcessedBy" INTEGER NULL, "ProcessedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_RefW_User" ON "ReferralWithdrawals" ("UserId")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ReferralCodes` (`UserId` INT NOT NULL, `Code` VARCHAR(20) NOT NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`UserId`), UNIQUE KEY `UX_RefCode` (`Code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Referrals` (`Id` INT NOT NULL AUTO_INCREMENT, `ReferrerId` INT NOT NULL, `ReferredUserId` INT NOT NULL, `Bonus` INT NOT NULL,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Credited', `Ip` VARCHAR(64) NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), UNIQUE KEY `UX_Ref_Referred` (`ReferredUserId`),
                KEY `IX_Ref_Referrer` (`ReferrerId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ReferralWithdrawals` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Amount` INT NOT NULL, `Method` VARCHAR(20) NOT NULL,
                `AccountName` VARCHAR(100) NULL, `AccountNumber` VARCHAR(40) NOT NULL, `BankName` VARCHAR(100) NULL, `Branch` VARCHAR(100) NULL,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending', `TxnId` VARCHAR(60) NULL, `AdminNote` VARCHAR(300) NULL, `ProcessedBy` INT NULL, `ProcessedAt` DATETIME NULL,
                `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_RefW_User` (`UserId`), KEY `IX_RefW_Status` (`Status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->refReady = true;
    }

    /** ['bonus' => int, 'min' => int, 'enabled' => bool] — bonus = রেফার করা ইউজার প্যাকেজ কিনলে রেফারার যা পায় */
    public function ReferralSettings(): array
    {
        $c = $this->GetSiteContent(['ref_bonus' => '10', 'ref_min_withdraw' => '50', 'ref_enabled' => '1', 'ref_model' => '']);
        if ($c['ref_model'] !== 'purchase') {
            // নিয়ম বদলেছে: সাইনআপে ৳30 → প্যাকেজ কিনলে ৳10 (একবারই সেট হয়, পরে অ্যাডমিন বদলাতে পারবেন)
            try { $this->SaveSiteContent(['ref_bonus' => '10', 'ref_model' => 'purchase']); $c['ref_bonus'] = '10'; } catch (Throwable $e) { }
        }
        return ['bonus' => max(0, (int)$c['ref_bonus']), 'min' => max(1, (int)$c['ref_min_withdraw']), 'enabled' => $c['ref_enabled'] !== '0'];
    }

    /** ইউজারের রেফারেল কোড (না থাকলে বানায়) — যেমন MM7K2QX */
    public function GetReferralCode(int $userId): string
    {
        $this->EnsureReferralTables();
        $c = $this->scalar('SELECT `Code` FROM `ReferralCodes` WHERE `UserId` = :u', ['u' => $userId]);
        if ($c) return (string)$c;
        $abc = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($try = 0; $try < 8; $try++) {
            $code = 'MM';
            for ($i = 0; $i < 6; $i++) $code .= $abc[random_int(0, strlen($abc) - 1)];
            try {
                $this->exec('INSERT INTO `ReferralCodes` (`UserId`, `Code`, `CreatedAt`) VALUES (:u, :c, :t)', ['u' => $userId, 'c' => $code, 't' => now()]);
                return $code;
            } catch (Throwable $e) {
                $c = $this->scalar('SELECT `Code` FROM `ReferralCodes` WHERE `UserId` = :u', ['u' => $userId]);
                if ($c) return (string)$c; // একই সময়ে অন্য রিকোয়েস্ট বানিয়ে ফেলেছে
            }
        }
        throw new RuntimeException('Referral code could not be created');
    }

    public function GetUserIdByReferralCode(string $code): ?int
    {
        $this->EnsureReferralTables();
        $id = $this->scalar('SELECT `UserId` FROM `ReferralCodes` WHERE `Code` = :c', ['c' => strtoupper($code)]);
        return $id ? (int)$id : null;
    }

    /**
     * নতুন ইউজার কারো লিংক দিয়ে সাইনআপ করলে রেফারেল রেকর্ড হয় (Waiting — এখনো টাকা না)।
     * নিজেকে নিজে, অ্যাডমিন অ্যাকাউন্ট, বা দিনে ২০ জনের বেশি — রেকর্ড হয় না। রিটার্ন: রেফারারের Id
     */
    public function CreditReferral(string $code, int $newUserId, string $ip): ?int
    {
        try {
            $set = $this->ReferralSettings();
            if (!$set['enabled']) return null;
            $ref = $this->GetUserIdByReferralCode($code);
            if (!$ref || $ref === $newUserId) return null;
            $u = $this->GetUserById($ref);
            if (!$u || $u->Role === 'Admin' || !(int)$u->IsActive) return null;
            if ((int)$this->scalar('SELECT COUNT(*) FROM `Referrals` WHERE `ReferrerId` = :r AND `CreatedAt` >= :t', ['r' => $ref, 't' => date('Y-m-d H:i:s', time() - 86400)]) >= 20) return null;
            $this->exec('INSERT INTO `Referrals` (`ReferrerId`, `ReferredUserId`, `Bonus`, `Status`, `Ip`, `CreatedAt`) VALUES (:r, :n, 0, \'Waiting\', :ip, :t)',
                ['r' => $ref, 'n' => $newUserId, 'ip' => mb_substr($ip, 0, 64), 't' => now()]);
            return $ref;
        } catch (Throwable $e) { error_log('[CreditReferral] ' . $e->getMessage()); return null; }
    }

    /**
     * রেফার করা ইউজারের পেমেন্ট Approve হলে (প্রথম প্যাকেজ কেনা) রেফারার বোনাস পায় — একজন ইউজারের জন্য একবারই।
     * বোনাস দিলে রেফারারকে নোটিফিকেশন যায়। রিটার্ন: ['referrer' => id, 'bonus' => ৳] অথবা null
     */
    public function CreditReferralPurchase(int $buyerId): ?array
    {
        try {
            $this->EnsureReferralTables();
            $set = $this->ReferralSettings();
            if (!$set['enabled'] || $set['bonus'] <= 0) return null;
            $r = $this->one("SELECT `Id`, `ReferrerId` FROM `Referrals` WHERE `ReferredUserId` = :u AND `Status` = 'Waiting'", ['u' => $buyerId]);
            if (!$r) return null;
            $n = $this->exec("UPDATE `Referrals` SET `Status` = 'Credited', `Bonus` = :b WHERE `Id` = :i AND `Status` = 'Waiting'", ['b' => $set['bonus'], 'i' => (int)$r->Id]);
            if (!$n) return null;
            $buyer = $this->GetUserById($buyerId);
            $name = $buyer ? (!is_blank($buyer->FullName) ? (string)$buyer->FullName : (string)$buyer->UserName) : 'আপনার রেফার করা ইউজার';
            $bonus = $set['bonus'];
            try { $this->CreateNotification((int)$r->ReferrerId, 'Referral Bonus 🎉', "{$name} প্যাকেজ কিনেছেন — রেফার করার জন্য আপনি ৳{$bonus} বোনাস পেয়েছেন!", 'success', '/Referral', 'fa-gift'); } catch (Throwable $e) { }
            return ['referrer' => (int)$r->ReferrerId, 'bonus' => $bonus];
        } catch (Throwable $e) { error_log('[CreditReferralPurchase] ' . $e->getMessage()); return null; }
    }

    /** ['earned', 'withdrawn' (Paid), 'pending', 'balance', 'count'] */
    public function GetReferralSummary(int $userId): array
    {
        $this->EnsureReferralTables();
        $earned = (int)$this->scalar("SELECT COALESCE(SUM(`Bonus`), 0) FROM `Referrals` WHERE `ReferrerId` = :u AND `Status` = 'Credited'", ['u' => $userId]);
        $count = (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `ReferrerId` = :u AND `Status` <> 'Revoked'", ['u' => $userId]);
        $paid = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Paid'", ['u' => $userId]);
        $pending = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Pending'", ['u' => $userId]);
        return ['earned' => $earned, 'withdrawn' => $paid, 'pending' => $pending, 'balance' => max(0, $earned - $paid - $pending), 'count' => $count];
    }

    /** আমার রেফার করা ইউজাররা (নতুন আগে) */
    public function GetMyReferrals(int $userId, int $limit = 100): array
    {
        $this->EnsureReferralTables();
        return $this->all('SELECT r.*, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink` FROM `Referrals` r LEFT JOIN `Users` u ON u.`Id` = r.`ReferredUserId`
            WHERE r.`ReferrerId` = :u ORDER BY r.`Id` DESC LIMIT ' . max(1, min(500, $limit)), ['u' => $userId]);
    }

    public function GetMyWithdrawals(int $userId): array
    {
        $this->EnsureReferralTables();
        return $this->all('SELECT * FROM `ReferralWithdrawals` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 100', ['u' => $userId]);
    }

    public function CreateWithdrawal(int $userId, int $amount, string $method, string $name, string $number, string $bank, string $branch): int
    {
        $this->EnsureReferralTables();
        return (int)$this->insert('INSERT INTO `ReferralWithdrawals` (`UserId`, `Amount`, `Method`, `AccountName`, `AccountNumber`, `BankName`, `Branch`, `Status`, `CreatedAt`)
            VALUES (:u, :a, :m, :n, :no, :b, :br, \'Pending\', :t)', ['u' => $userId, 'a' => $amount, 'm' => $method, 'n' => $name ?: null, 'no' => $number, 'b' => $bank ?: null, 'br' => $branch ?: null, 't' => now()]);
    }

    // ---------- অ্যাডমিন ----------
    public function GetWithdrawalsAdmin(string $status, int $limit = 200): array
    {
        $this->EnsureReferralTables();
        $w = in_array($status, ['Pending', 'Paid', 'Rejected'], true) ? 'WHERE w.`Status` = :s' : '';
        return $this->all("SELECT w.*, u.`FullName`, u.`UserName`, u.`Role`, u.`MobileNumber` FROM `ReferralWithdrawals` w LEFT JOIN `Users` u ON u.`Id` = w.`UserId` $w
            ORDER BY CASE WHEN w.`Status` = 'Pending' THEN 0 ELSE 1 END, w.`Id` DESC LIMIT " . max(1, min(500, $limit)), $w ? ['s' => $status] : []);
    }

    public function GetWithdrawal(int $id): ?Obj
    {
        $this->EnsureReferralTables();
        return $this->one('SELECT * FROM `ReferralWithdrawals` WHERE `Id` = :i', ['i' => $id]);
    }

    public function SetWithdrawalStatus(int $id, string $status, string $txn, string $note, int $adminId): void
    {
        $this->exec('UPDATE `ReferralWithdrawals` SET `Status` = :s, `TxnId` = :t, `AdminNote` = :n, `ProcessedBy` = :a, `ProcessedAt` = :p WHERE `Id` = :i',
            ['s' => $status, 't' => $txn !== '' ? $txn : null, 'n' => $note !== '' ? $note : null, 'a' => $adminId, 'p' => now(), 'i' => $id]);
    }

    public function GetReferralsAdmin(int $limit = 200): array
    {
        $this->EnsureReferralTables();
        return $this->all('SELECT r.*, a.`FullName` AS ReferrerName, a.`UserName` AS ReferrerUser, a.`Role` AS ReferrerRole, a.`ProfileImageLink` AS ReferrerPhoto,
            (SELECT k.`Code` FROM `ReferralCodes` k WHERE k.`UserId` = r.`ReferrerId` LIMIT 1) AS RefCode,
            b.`FullName` AS ReferredName, b.`UserName` AS ReferredUser, b.`Email` AS ReferredEmail, b.`Role` AS ReferredRole
            FROM `Referrals` r LEFT JOIN `Users` a ON a.`Id` = r.`ReferrerId` LEFT JOIN `Users` b ON b.`Id` = r.`ReferredUserId` ORDER BY r.`Id` DESC LIMIT ' . max(1, min(2000, $limit)));
    }

    public function SetReferralStatus(int $id, string $status): void
    {
        // "আবার চালু": বোনাস আগে পায়নি (এখনো প্যাকেজ কেনেনি) হলে Waiting এ ফেরে
        if ($status === 'Credited' && (int)$this->scalar('SELECT `Bonus` FROM `Referrals` WHERE `Id` = :i', ['i' => $id]) <= 0) $status = 'Waiting';
        $this->exec('UPDATE `Referrals` SET `Status` = :s WHERE `Id` = :i', ['s' => $status, 'i' => $id]);
    }

    public function ReferralAdminStats(): array
    {
        $this->EnsureReferralTables();
        return [
            'referrals' => (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `Status` <> 'Revoked'"),
            'bought' => (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `Status` = 'Credited'"),
            'earned' => (int)$this->scalar("SELECT COALESCE(SUM(`Bonus`), 0) FROM `Referrals` WHERE `Status` = 'Credited'"),
            'paid' => (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `Status` = 'Paid'"),
            'pendingCount' => (int)$this->scalar("SELECT COUNT(*) FROM `ReferralWithdrawals` WHERE `Status` = 'Pending'"),
            'pendingAmount' => (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `Status` = 'Pending'"),
        ];
    }

    /**
     * কে কার রেফারেন্সে অ্যাকাউন্ট খুলেছে — [নতুন ইউজারের Id => Obj(ReferrerId, ReferrerName, ReferrerRole, Status, Bonus, CreatedAt)]
     * এই তালিকায় না থাকলে = ডাইরেক্ট সাইনআপ
     */
    public function GetReferralSourceMap(): array
    {
        try {
            $this->EnsureReferralTables();
            $map = [];
            foreach ($this->all('SELECT r.`ReferredUserId`, r.`ReferrerId`, r.`Status`, r.`Bonus`, r.`CreatedAt`, a.`FullName` AS ReferrerName, a.`UserName` AS ReferrerUser, a.`Role` AS ReferrerRole,
                (SELECT k.`Code` FROM `ReferralCodes` k WHERE k.`UserId` = r.`ReferrerId` LIMIT 1) AS RefCode
                FROM `Referrals` r LEFT JOIN `Users` a ON a.`Id` = r.`ReferrerId`') as $r) {
                $r->ReferrerName = !is_blank($r->ReferrerName) ? (string)$r->ReferrerName : ((string)($r->ReferrerUser ?? '') ?: 'Deleted user');
                $map[(int)$r->ReferredUserId] = $r;
            }
            return $map;
        } catch (Throwable $e) { error_log('[GetReferralSourceMap] ' . $e->getMessage()); return []; }
    }

    private ?array $refSrcCache = null;

    public function GetReferralSource(int $userId): ?Obj
    {
        $this->refSrcCache ??= $this->GetReferralSourceMap();   // এক পেজে অনেকবার ডাকলেও একবারই পড়ে
        return $this->refSrcCache[$userId] ?? null;
    }
}
