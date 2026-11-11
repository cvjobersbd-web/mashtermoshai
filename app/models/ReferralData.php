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
        $c = $this->GetSiteContent(['ref_bonus' => '10', 'ref_min_withdraw' => '100', 'ref_enabled' => '1', 'ref_model' => '', 'ref_min_v100' => '']);
        if ($c['ref_min_v100'] === '') {
            // নিয়ম: ব্যালেন্স ৳100 হলে টাকা তোলা যায় (কম হলে প্যাকেজে ছাড় হিসেবে ব্যবহার) — একবারই সেট হয়, পরে অ্যাডমিন বদলাতে পারবেন
            try { $this->SaveSiteContent(['ref_min_withdraw' => '100', 'ref_min_v100' => '1']); $c['ref_min_withdraw'] = '100'; } catch (Throwable $e) { }
        }
        if ($c['ref_model'] !== 'purchase') {
            // নিয়ম বদলেছে: সাইনআপে ৳30 → প্যাকেজ কিনলে ৳10 (একবারই সেট হয়, পরে অ্যাডমিন বদলাতে পারবেন)
            try { $this->SaveSiteContent(['ref_bonus' => '10', 'ref_model' => 'purchase']); $c['ref_bonus'] = '10'; } catch (Throwable $e) { }
        }
        return ['bonus' => max(0, (int)$c['ref_bonus']), 'min' => max(1, (int)$c['ref_min_withdraw']), 'enabled' => $c['ref_enabled'] !== '0'];
    }

    // ---------- বোনাস ছাড়া রেফারার (অ্যাডমিন বেছে দেন) — এদের লিংক / কোডে কেউ এলে ও কিনলে গোনা হয়, কিন্তু ৳ বোনাস পায় না ----------
    /** @return int[] */
    public function RefNoBonusIds(): array
    {
        static $c = null;
        if ($c !== null) return $c;
        try { $j = json_decode((string)$this->GetSiteContent(['ref_nobonus' => '[]'])['ref_nobonus'], true); } catch (Throwable $e) { $j = []; }
        return $c = array_values(array_unique(array_map('intval', is_array($j) ? $j : [])));
    }

    public function IsRefNoBonus(int $userId): bool { return in_array($userId, $this->RefNoBonusIds(), true); }

    public function SetRefNoBonus(int $userId, bool $on): void
    {
        $ids = json_decode((string)$this->GetSiteContent(['ref_nobonus' => '[]'])['ref_nobonus'], true);
        $ids = array_values(array_unique(array_map('intval', is_array($ids) ? $ids : [])));
        $ids = $on ? array_values(array_unique(array_merge($ids, [$userId]))) : array_values(array_diff($ids, [$userId]));
        $this->SaveSiteContent(['ref_nobonus' => json_encode($ids)]);
    }

    /** এই রেফারার প্রতি কেনায় কত পায় (বোনাস ছাড়া তালিকায় থাকলে ০) */
    public function ReferralBonusFor(int $userId): int
    {
        return $this->IsRefNoBonus($userId) ? 0 : $this->ReferralSettings()['bonus'];
    }

    /** @return Obj[] বোনাস ছাড়া রেফারারদের তালিকা */
    public function GetRefNoBonusUsers(): array
    {
        $ids = $this->RefNoBonusIds();
        if (!$ids) return [];
        $this->EnsureReferralTables();
        return $this->all('SELECT u.`Id`, u.`FullName`, u.`UserName`, u.`Email`, u.`Role`, c.`Code` FROM `Users` u LEFT JOIN `ReferralCodes` c ON c.`UserId` = u.`Id` WHERE u.`Id` IN (' . implode(',', $ids) . ') ORDER BY u.`FullName`');
    }

    /** অ্যাডমিন: নাম / ইউজারনেম / ইমেইল / মোবাইল / রেফারেল কোড দিয়ে ইউজার খোঁজা */
    public function SearchUsersForReferral(string $q, int $limit = 10): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) return [];
        $this->EnsureReferralTables();
        $like = '%' . str_replace(['%', '_'], '', $q) . '%';
        return $this->all("SELECT u.`Id`, u.`FullName`, u.`UserName`, u.`Email`, u.`Role`, c.`Code` FROM `Users` u LEFT JOIN `ReferralCodes` c ON c.`UserId` = u.`Id`
            WHERE u.`Role` <> 'Admin' AND (u.`FullName` LIKE :a OR u.`UserName` LIKE :b OR u.`Email` LIKE :c OR u.`MobileNumber` LIKE :d OR c.`Code` = :e)
            ORDER BY u.`Id` DESC LIMIT " . max(1, min(30, $limit)), ['a' => $like, 'b' => $like, 'c' => $like, 'd' => $like, 'e' => strtoupper($q)]);
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
    public function CreditReferral(string $code, int $newUserId, string $ip, string $src = 'link'): ?int
    {
        try {
            $set = $this->ReferralSettings();
            if (!$set['enabled']) return null;
            $ref = $this->GetUserIdByReferralCode($code);
            if (!$ref || $ref === $newUserId) return null;
            $u = $this->GetUserById($ref);
            if (!$u || $u->Role === 'Admin' || !(int)$u->IsActive) return null;
            if ((int)$this->scalar('SELECT COUNT(*) FROM `Referrals` WHERE `ReferrerId` = :r AND `CreatedAt` >= :t', ['r' => $ref, 't' => date('Y-m-d H:i:s', time() - 86400)]) >= 20) return null;
            $this->EnsureRefCodeCols();
            $src = $src === 'code' ? 'code' : (self::CleanRefSrc($src) ?: 'link');
            $this->exec('INSERT INTO `Referrals` (`ReferrerId`, `ReferredUserId`, `Bonus`, `Status`, `Ip`, `CreatedAt`, `Via`, `Src`) VALUES (:r, :n, 0, \'Waiting\', :ip, :t, :v, :s)',
                ['r' => $ref, 'n' => $newUserId, 'ip' => mb_substr($ip, 0, 64), 't' => now(), 'v' => $src === 'code' ? 'signupcode' : 'link', 's' => $src]);
            return $ref;
        } catch (Throwable $e) { error_log('[CreditReferral] ' . $e->getMessage()); return null; }
    }

    // ---------- প্যাকেজ কেনার সময় রেফারেল কোড ----------
    private bool $refCodeColReady = false;
    /** PaymentRequests.RefCode (কেনার সময় দেওয়া কোড), Referrals.Via (link | code) */
    public function EnsureRefCodeCols(): void
    {
        if ($this->refCodeColReady) return;
        $this->refCodeColReady = true;
        $this->EnsureReferralTables();
        $this->EnsurePaymentTables();
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        foreach ([['PaymentRequests', 'RefCode', 20], ['PaymentRequests', 'RefDiscount', 0], ['Referrals', 'Via', 10], ['Referrals', 'Src', 12]] as [$t, $c, $n]) {
            if ($n === 0) {   // সংখ্যার কলাম
                try { $this->pdo->query("SELECT `$c` FROM `$t` LIMIT 1"); }
                catch (Throwable $e) { try { $this->pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` INT NOT NULL DEFAULT 0"); } catch (Throwable $e2) { error_log("[refcode col $t.$c] " . $e2->getMessage()); } }
                continue;
            }
            try { $this->pdo->query("SELECT `$c` FROM `$t` LIMIT 1"); }
            catch (Throwable $e) { try { $this->pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` " . ($sqlite ? 'TEXT' : "VARCHAR($n)") . ' NULL'); } catch (Throwable $e2) { error_log("[refcode col $t.$c] " . $e2->getMessage()); } }
        }
        // শেয়ার বাটনে ক্লিক (share) আর লিংক খুলে সাইটে আসা (click) — কোন প্ল্যাটফর্মে
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ReferralEvents" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Kind" TEXT NOT NULL, "Src" TEXT NOT NULL, "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_RefEv_User" ON "ReferralEvents" ("UserId")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ReferralEvents` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Kind` VARCHAR(10) NOT NULL, `Src` VARCHAR(12) NOT NULL,
                `Ip` VARCHAR(64) NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_RefEv_User` (`UserId`, `Kind`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    /** শেয়ারের জায়গা — key => [নাম, আইকন, রং] */
    public const REF_SOURCES = [
        'wa' => ['WhatsApp', 'fab fa-whatsapp', '#25D366'], 'fb' => ['Facebook', 'fab fa-facebook-f', '#1877F2'], 'ms' => ['Messenger', 'fab fa-facebook-messenger', '#A033FF'],
        'ig' => ['Instagram', 'fab fa-instagram', '#D6249F'], 'tg' => ['Telegram', 'fab fa-telegram', '#229ED9'], 'x' => ['X (Twitter)', 'fab fa-x-twitter', '#0F172A'],
        'li' => ['LinkedIn', 'fab fa-linkedin-in', '#0A66C2'], 'more' => ['অন্য অ্যাপ (Share)', 'fas fa-share-nodes', '#64748B'], 'copy' => ['লিংক কপি করে', 'far fa-copy', '#0369A1'],
        'code' => ['রেফারেল কোড', 'fas fa-ticket', '#B45309'], 'link' => ['লিংক (জায়গা জানা নেই)', 'fas fa-link', '#94A3B8'],
    ];

    public static function CleanRefSrc(?string $s): string
    {
        $s = strtolower(trim((string)$s));
        return isset(self::REF_SOURCES[$s]) && !in_array($s, ['code', 'link'], true) ? $s : '';
    }

    /** শেয়ার বাটনে ক্লিক বা লিংক খুলে আসা রেকর্ড (একই IP থেকে একই ঘটনা ঘণ্টায় একবারই গোনা হয়) */
    public function LogReferralEvent(int $userId, string $kind, string $src, string $ip): void
    {
        try {
            $this->EnsureRefCodeCols();
            if (!in_array($kind, ['share', 'click'], true)) return;
            $src = $kind === 'share' && $src === 'code' ? 'code' : (self::CleanRefSrc($src) ?: 'link');
            if ((int)$this->scalar('SELECT COUNT(*) FROM `ReferralEvents` WHERE `UserId` = :u AND `Kind` = :k AND `Src` = :s AND `Ip` = :ip AND `CreatedAt` >= :t',
                ['u' => $userId, 'k' => $kind, 's' => $src, 'ip' => mb_substr($ip, 0, 64), 't' => date('Y-m-d H:i:s', time() - 3600)]) > 0) return;
            $this->exec('INSERT INTO `ReferralEvents` (`UserId`, `Kind`, `Src`, `Ip`, `CreatedAt`) VALUES (:u, :k, :s, :ip, :t)',
                ['u' => $userId, 'k' => $kind, 's' => $src, 'ip' => mb_substr($ip, 0, 64), 't' => now()]);
        } catch (Throwable $e) { error_log('[LogReferralEvent] ' . $e->getMessage()); }
    }

    // ---------- রেফারেল ব্যালেন্স দিয়ে প্যাকেজে ছাড় ----------
    /** পেমেন্ট জমার সময় ছাড়ের টাকা আটকে রাখা (ReferralWithdrawals, Method = Discount, Pending) — Accept হলে Paid, বাতিল হলে টাকা ফেরত */
    public function ReserveReferralDiscount(int $userId, int $paymentId, int $amount): void
    {
        $this->EnsureRefCodeCols();
        $this->exec('UPDATE `PaymentRequests` SET `RefDiscount` = :d WHERE `Id` = :i', ['d' => $amount, 'i' => $paymentId]);
        $this->exec("INSERT INTO `ReferralWithdrawals` (`UserId`, `Amount`, `Method`, `AccountName`, `AccountNumber`, `Status`, `CreatedAt`) VALUES (:u, :a, 'Discount', :n, :no, 'Pending', :t)",
            ['u' => $userId, 'a' => $amount, 'n' => 'প্যাকেজে ছাড়', 'no' => 'PAY-' . $paymentId, 't' => now()]);
    }

    /** পেমেন্ট Accept → ছাড় Paid (খরচ হলো); Reject / Delete → Rejected (ব্যালেন্সে ফেরত) */
    public function SettleReferralDiscount(int $paymentId, bool $used): void
    {
        try {
            $this->EnsureRefCodeCols();
            $this->exec("UPDATE `ReferralWithdrawals` SET `Status` = :s, `ProcessedAt` = :t, `AdminNote` = :n WHERE `Method` = 'Discount' AND `AccountNumber` = :no AND `Status` = 'Pending'",
                ['s' => $used ? 'Paid' : 'Rejected', 't' => now(), 'n' => $used ? 'প্যাকেজ কেনায় ব্যবহার হয়েছে' : 'পেমেন্ট বাতিল — টাকা ব্যালেন্সে ফেরত', 'no' => 'PAY-' . $paymentId]);
        } catch (Throwable $e) { error_log('[SettleReferralDiscount] ' . $e->getMessage()); }
    }

    /**
     * প্যাকেজ কেনার সময় দেওয়া রেফারেল কোড যাচাই — ঠিক থাকলে কোডের মালিকের Obj (Id, Name), না হলে null আর $err এ কারণ।
     * নিয়ম: নিজের কোড না, অ্যাডমিনের না, আর শুধু প্রথম কেনায় (আগে কেনা থাকলে / আগেই বোনাস দেওয়া হয়ে থাকলে চলে না)।
     */
    public function CheckPurchaseReferralCode(string $code, int $buyerId, ?string &$err = null): ?Obj
    {
        $err = null;
        $code = strtoupper(trim($code));
        try {
            if (!$this->ReferralSettings()['enabled']) { $err = 'এখন রেফারেল চালু নেই।'; return null; }
            if (!preg_match('/^MM[A-Z0-9]{4,10}$/', $code)) { $err = 'রেফারেল কোডটা সঠিক নয় — যেমন MM7K2QXA'; return null; }
            $ref = $this->GetUserIdByReferralCode($code);
            if (!$ref) { $err = 'এই রেফারেল কোড পাওয়া যায়নি — বানান মিলিয়ে দেখুন।'; return null; }
            if ($ref === $buyerId) { $err = 'নিজের রেফারেল কোড দেওয়া যাবে না।'; return null; }
            $u = $this->GetUserById($ref);
            if (!$u || $u->Role === 'Admin' || !(int)$u->IsActive) { $err = 'এই রেফারেল কোড এখন চালু নেই।'; return null; }
            $this->EnsureRefCodeCols();
            if ((int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `ReferredUserId` = :u AND `Status` <> 'Waiting'", ['u' => $buyerId]) > 0
                || (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `UserId` = :u AND `Status` = 'Approved' AND `Amount` > 0", ['u' => $buyerId]) > 0) {
                $err = 'রেফারেল কোড শুধু প্রথমবার প্যাকেজ কেনার সময় কাজ করে — আপনি আগেই কিনেছেন।'; return null;
            }
            return obj(['Id' => $ref, 'Code' => $code, 'Name' => !is_blank($u->FullName) ? (string)$u->FullName : (string)$u->UserName]);
        } catch (Throwable $e) { error_log('[CheckPurchaseReferralCode] ' . $e->getMessage()); $err = 'কোড যাচাই করা যায়নি — আবার চেষ্টা করুন।'; return null; }
    }

    /** পেমেন্টে দেওয়া রেফারেল কোড সেভ */
    public function SetPaymentRefCode(int $paymentId, string $code): void
    {
        $this->EnsureRefCodeCols();
        $this->exec('UPDATE `PaymentRequests` SET `RefCode` = :c WHERE `Id` = :i', ['c' => strtoupper($code), 'i' => $paymentId]);
    }

    /** [Code => নাম] — অ্যাডমিনের পেমেন্ট পেজে কোডের মালিক দেখানোর জন্য */
    public function RefCodeOwnerNames(array $codes): array
    {
        $codes = array_values(array_unique(array_filter(array_map(fn($c) => strtoupper((string)$c), $codes))));
        if (!$codes) return [];
        try {
            $this->EnsureReferralTables();
            $ph = implode(',', array_fill(0, count($codes), '?'));
            $st = $this->pdo->prepare("SELECT c.`Code`, c.`UserId`, u.`FullName`, u.`UserName` FROM `ReferralCodes` c LEFT JOIN `Users` u ON u.`Id` = c.`UserId` WHERE c.`Code` IN ($ph)");
            $st->execute($codes);
            $m = [];
            foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) $m[$r->Code] = ['id' => (int)$r->UserId, 'name' => (string)($r->FullName ?: $r->UserName)];
            return $m;
        } catch (Throwable $e) { return []; }
    }

    /**
     * রেফার করা ইউজারের পেমেন্ট Approve হলে (প্রথম প্যাকেজ কেনা) রেফারার বোনাস পায় — একজন ইউজারের জন্য একবারই।
     * কেনার সময় রেফারেল কোড দিলে ($code) সেই কোডের মালিক রেফারার হয় (লিংক দিয়ে আসা রেফারারের চেয়ে কোড আগে)।
     * বোনাস দিলে রেফারারকে নোটিফিকেশন যায়। রিটার্ন: ['referrer' => id, 'bonus' => ৳] অথবা null
     */
    public function CreditReferralPurchase(int $buyerId, string $code = ''): ?array
    {
        try {
            $this->EnsureReferralTables();
            $set = $this->ReferralSettings();
            if (!$set['enabled'] || $set['bonus'] <= 0) return null;
            if ($code !== '') {
                $this->EnsureRefCodeCols();
                $ref = $this->GetUserIdByReferralCode($code);
                $ru = $ref ? $this->GetUserById($ref) : null;
                $done = (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `ReferredUserId` = :u AND `Status` <> 'Waiting'", ['u' => $buyerId]) > 0;
                if ($ref && $ref !== $buyerId && $ru && $ru->Role !== 'Admin' && (int)$ru->IsActive && !$done) {
                    $w = $this->one("SELECT `Id` FROM `Referrals` WHERE `ReferredUserId` = :u AND `Status` = 'Waiting'", ['u' => $buyerId]);
                    if ($w) $this->exec("UPDATE `Referrals` SET `ReferrerId` = :r, `Via` = 'code', `Src` = 'code' WHERE `Id` = :i", ['r' => $ref, 'i' => (int)$w->Id]);
                    else $this->exec("INSERT INTO `Referrals` (`ReferrerId`, `ReferredUserId`, `Bonus`, `Status`, `Ip`, `CreatedAt`, `Via`, `Src`) VALUES (:r, :n, 0, 'Waiting', NULL, :t, 'code', 'code')",
                        ['r' => $ref, 'n' => $buyerId, 't' => now()]);
                }
            }
            $r = $this->one("SELECT `Id`, `ReferrerId` FROM `Referrals` WHERE `ReferredUserId` = :u AND `Status` = 'Waiting'", ['u' => $buyerId]);
            if (!$r) return null;
            if ($this->IsRefNoBonus((int)$r->ReferrerId)) {
                // বোনাস ছাড়া রেফারার — "কিনেছে" হিসেবে গোনা হয়, টাকা ০
                $this->exec("UPDATE `Referrals` SET `Status` = 'Credited', `Bonus` = 0 WHERE `Id` = :i AND `Status` = 'Waiting'", ['i' => (int)$r->Id]);
                return null;
            }
            $n = $this->exec("UPDATE `Referrals` SET `Status` = 'Credited', `Bonus` = :b WHERE `Id` = :i AND `Status` = 'Waiting'", ['b' => $set['bonus'], 'i' => (int)$r->Id]);
            if (!$n) return null;
            $buyer = $this->GetUserById($buyerId);
            $name = $buyer ? (!is_blank($buyer->FullName) ? (string)$buyer->FullName : (string)$buyer->UserName) : 'আপনার রেফার করা ইউজার';
            $bonus = $set['bonus'];
            try { $this->CreateNotification((int)$r->ReferrerId, 'Referral Bonus 🎉', "{$name} " . ($code !== '' ? 'আপনার রেফারেল কোড দিয়ে ' : '') . "প্যাকেজ কিনেছেন — রেফার করার জন্য আপনি ৳{$bonus} বোনাস পেয়েছেন!", 'success', '/Referral', 'fa-gift'); } catch (Throwable $e) { }
            return ['referrer' => (int)$r->ReferrerId, 'bonus' => $bonus];
        } catch (Throwable $e) { error_log('[CreditReferralPurchase] ' . $e->getMessage()); return null; }
    }

    /** ['earned', 'withdrawn' (Paid), 'pending', 'balance', 'count'] */
    public function GetReferralSummary(int $userId): array
    {
        $this->EnsureReferralTables();
        $earned = (int)$this->scalar("SELECT COALESCE(SUM(`Bonus`), 0) FROM `Referrals` WHERE `ReferrerId` = :u AND `Status` = 'Credited'", ['u' => $userId]);
        $count = (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `ReferrerId` = :u AND `Status` <> 'Revoked'", ['u' => $userId]);
        $paid = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Paid' AND `Method` <> 'Discount'", ['u' => $userId]);
        $pending = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Pending' AND `Method` <> 'Discount'", ['u' => $userId]);
        $dUsed = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Paid' AND `Method` = 'Discount'", ['u' => $userId]);
        $dHold = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Pending' AND `Method` = 'Discount'", ['u' => $userId]);
        return ['earned' => $earned, 'withdrawn' => $paid, 'pending' => $pending, 'discount' => $dUsed, 'discountHold' => $dHold,
            'balance' => max(0, $earned - $paid - $pending - $dUsed - $dHold), 'count' => $count];
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
        $w = "WHERE w.`Method` <> 'Discount'" . (in_array($status, ['Pending', 'Paid', 'Rejected'], true) ? ' AND w.`Status` = :s' : '');
        return $this->all("SELECT w.*, u.`FullName`, u.`UserName`, u.`Role`, u.`MobileNumber` FROM `ReferralWithdrawals` w LEFT JOIN `Users` u ON u.`Id` = w.`UserId` $w
            ORDER BY CASE WHEN w.`Status` = 'Pending' THEN 0 ELSE 1 END, w.`Id` DESC LIMIT " . max(1, min(500, $limit)), in_array($status, ['Pending', 'Paid', 'Rejected'], true) ? ['s' => $status] : []);
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

    /**
     * অ্যাডমিন: সব রেফারারের সারসংক্ষেপ — যার কোড আছে বা কোনো কার্যক্রম আছে
     * @return Obj[] (UserId, Name, UserName, Role, Code, Shares, Clicks, Signups, Buyers, Earned, Balance)
     */
    public function GetReferrerOverview(): array
    {
        $this->EnsureRefCodeCols();
        $rows = [];
        $add = function (int $uid) use (&$rows) { $rows[$uid] ??= obj(['UserId' => $uid, 'Shares' => 0, 'Clicks' => 0, 'Signups' => 0, 'Buyers' => 0, 'Earned' => 0]); return $rows[$uid]; };
        foreach ($this->all("SELECT `UserId`, `Kind`, COUNT(*) AS N FROM `ReferralEvents` GROUP BY `UserId`, `Kind`") as $r) { $o = $add((int)$r->UserId); if ($r->Kind === 'share') $o->Shares = (int)$r->N; else $o->Clicks = (int)$r->N; }
        foreach ($this->all("SELECT `ReferrerId`, COUNT(*) AS N, SUM(CASE WHEN `Status` = 'Credited' THEN 1 ELSE 0 END) AS B, SUM(CASE WHEN `Status` = 'Credited' THEN `Bonus` ELSE 0 END) AS E
            FROM `Referrals` WHERE `Status` <> 'Revoked' GROUP BY `ReferrerId`") as $r) { $o = $add((int)$r->ReferrerId); $o->Signups = (int)$r->N; $o->Buyers = (int)$r->B; $o->Earned = (int)$r->E; }
        foreach ($this->RefNoBonusIds() as $nb) $add((int)$nb);
        if (!$rows) return [];
        $ids = implode(',', array_map('intval', array_keys($rows)));
        $info = [];
        foreach ($this->all("SELECT u.`Id`, u.`FullName`, u.`UserName`, u.`Role`, c.`Code` FROM `Users` u LEFT JOIN `ReferralCodes` c ON c.`UserId` = u.`Id` WHERE u.`Id` IN ($ids)") as $r) $info[(int)$r->Id] = $r;
        foreach ($rows as $uid => $o) {
            $i = $info[$uid] ?? null;
            $o->Name = $i ? ((string)($i->FullName ?: $i->UserName)) : 'Deleted user';
            $o->UserName = (string)($i->UserName ?? ''); $o->Role = (string)($i->Role ?? ''); $o->Code = (string)($i->Code ?? '');
            $o->Balance = $this->GetReferralSummary($uid)['balance'];
            $o->NoBonus = $this->IsRefNoBonus($uid);
        }
        $rows = array_values($rows);
        usort($rows, fn($a, $b) => [$b->Buyers, $b->Signups, $b->Clicks, $b->Shares] <=> [$a->Buyers, $a->Signups, $a->Clicks, $a->Shares]);
        return $rows;
    }

    /** অ্যাডমিন: একজন রেফারারের সব কিছু — কোথায় শেয়ার, কত জন এসেছে, কত জন কিনেছে, কাকে এনেছে, টাকা তোলা / ছাড় */
    public function GetReferrerDetail(int $userId): array
    {
        $this->EnsureRefCodeCols();
        $src = [];
        foreach (array_keys(self::REF_SOURCES) as $k) $src[$k] = ['share' => 0, 'click' => 0, 'signup' => 0, 'buy' => 0];
        foreach ($this->all('SELECT `Kind`, `Src`, COUNT(*) AS N FROM `ReferralEvents` WHERE `UserId` = :u GROUP BY `Kind`, `Src`', ['u' => $userId]) as $r) {
            $k = isset($src[$r->Src]) ? $r->Src : 'link'; $src[$k][$r->Kind === 'share' ? 'share' : 'click'] += (int)$r->N;
        }
        $people = $this->all('SELECT r.*, b.`FullName`, b.`UserName`, b.`Email`, b.`Role`, b.`CreatedAt` AS JoinedAt,
                (SELECT COUNT(*) FROM `PaymentRequests` p WHERE p.`UserId` = r.`ReferredUserId` AND p.`Status` = \'Approved\') AS Orders,
                (SELECT COALESCE(SUM(p.`Amount` + COALESCE(p.`RefDiscount`, 0)), 0) FROM `PaymentRequests` p WHERE p.`UserId` = r.`ReferredUserId` AND p.`Status` = \'Approved\') AS Spent
            FROM `Referrals` r LEFT JOIN `Users` b ON b.`Id` = r.`ReferredUserId` WHERE r.`ReferrerId` = :u ORDER BY r.`Id` DESC', ['u' => $userId]);
        foreach ($people as $p) {
            if ($p->Status === 'Revoked') continue;
            $k = (string)($p->Src ?? '') !== '' && isset($src[$p->Src]) ? $p->Src : (($p->Via ?? '') === 'code' ? 'code' : 'link');
            $src[$k]['signup']++;
            if ($p->Status === 'Credited') $src[$k]['buy']++;
        }
        $src = array_filter($src, fn($x) => array_sum($x) > 0);
        return [
            'Code' => $this->GetReferralCode($userId), 'Summary' => $this->GetReferralSummary($userId), 'Sources' => $src, 'People' => $people,
            'Totals' => ['share' => array_sum(array_column($src, 'share')), 'click' => array_sum(array_column($src, 'click')), 'signup' => array_sum(array_column($src, 'signup')), 'buy' => array_sum(array_column($src, 'buy'))],
            'Withdrawals' => $this->GetMyWithdrawals($userId),
            'LastShare' => $this->scalar("SELECT MAX(`CreatedAt`) FROM `ReferralEvents` WHERE `UserId` = :u AND `Kind` = 'share'", ['u' => $userId]),
        ];
    }

    public function SetReferralStatus(int $id, string $status): void
    {
        // "আবার চালু": বোনাস আগে পায়নি (এখনো প্যাকেজ কেনেনি) হলে Waiting এ ফেরে
        if ($status === 'Credited' && (int)$this->scalar('SELECT `Bonus` FROM `Referrals` WHERE `Id` = :i', ['i' => $id]) <= 0) {
            // বোনাস ছাড়া রেফারারের কেনা ইউজার হলে "কিনেছে" থাকে
            $r = $this->one('SELECT `ReferrerId`, `ReferredUserId` FROM `Referrals` WHERE `Id` = :i', ['i' => $id]);
            $bought = $r && $this->IsRefNoBonus((int)$r->ReferrerId) && (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `UserId` = :u AND `Status` = 'Approved'", ['u' => (int)$r->ReferredUserId]) > 0;
            if (!$bought) $status = 'Waiting';
        }
        $this->exec('UPDATE `Referrals` SET `Status` = :s WHERE `Id` = :i', ['s' => $status, 'i' => $id]);
    }

    public function ReferralAdminStats(): array
    {
        $this->EnsureReferralTables();
        return [
            'referrals' => (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `Status` <> 'Revoked'"),
            'bought' => (int)$this->scalar("SELECT COUNT(*) FROM `Referrals` WHERE `Status` = 'Credited'"),
            'earned' => (int)$this->scalar("SELECT COALESCE(SUM(`Bonus`), 0) FROM `Referrals` WHERE `Status` = 'Credited'"),
            'paid' => (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `Status` = 'Paid' AND `Method` <> 'Discount'"),
            'discount' => (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `Status` = 'Paid' AND `Method` = 'Discount'"),
            'pendingCount' => (int)$this->scalar("SELECT COUNT(*) FROM `ReferralWithdrawals` WHERE `Status` = 'Pending' AND `Method` <> 'Discount'"),
            'pendingAmount' => (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `ReferralWithdrawals` WHERE `Status` = 'Pending' AND `Method` <> 'Discount'"),
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
