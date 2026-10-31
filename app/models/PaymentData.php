<?php
/**
 * ম্যানুয়াল পেমেন্ট সিস্টেম (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - PaymentMethods  : Bank Transfer, bKash, Nagad, Rocket, Other ... (অ্যাডমিন নতুন যোগ করতে পারে)
 *  - PaymentAccounts : প্রতিটা মেথডের অ্যাকাউন্ট/নম্বর আর স্ক্যান করার QR ছবি
 *  - PaymentRequests : ইউজারের পেমেন্টের প্রমাণ (Transaction ID, তারিখ, রিসিট) — অ্যাডমিন Accept/Reject করে
 *  - UserSubscriptions : Accept হলে শিক্ষকের Subscription / শিক্ষার্থীর পোস্ট ক্রেডিট
 * টেবিল না থাকলে নিজে থেকেই তৈরি হয়।
 */
trait PaymentData
{
    private bool $paymentReady = false;

    public const PAYMENT_KINDS = ['bank', 'mobile', 'other'];
    public const PAYMENT_STATUSES = ['Pending', 'Approved', 'Rejected'];
    public const DEFAULT_QR = 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png';
    public const DEFAULT_BANK = ['Title' => 'Pubali Bank — Khatunganj Branch', 'BankName' => 'PUBALI BANK PLC.', 'AccountName' => 'BENGOL BAY',
        'AccountNumber' => '0642901094586', 'Branch' => 'Khatunganj', 'RoutingNumber' => '175154278'];

    public function EnsurePaymentTables(): void
    {
        if ($this->paymentReady) return;
        $this->EnsurePackageTable();
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PaymentMethods" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Name" TEXT NOT NULL, "Kind" TEXT NOT NULL DEFAULT \'other\',
                "LogoUrl" TEXT NULL, "Color" TEXT NULL, "TxnRequired" INTEGER NOT NULL DEFAULT 1, "Instructions" TEXT NULL, "IsActive" INTEGER NOT NULL DEFAULT 1,
                "SortOrder" INTEGER NOT NULL DEFAULT 0, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PaymentAccounts" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "MethodId" INTEGER NOT NULL, "Title" TEXT NOT NULL,
                "AccountName" TEXT NULL, "AccountNumber" TEXT NULL, "BankName" TEXT NULL, "Branch" TEXT NULL, "RoutingNumber" TEXT NULL, "AccountType" TEXT NULL,
                "QrImageUrl" TEXT NULL, "IsActive" INTEGER NOT NULL DEFAULT 1, "SortOrder" INTEGER NOT NULL DEFAULT 0, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PaymentRequests" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "PackageId" INTEGER NULL,
                "PackageName" TEXT NOT NULL, "Audience" TEXT NOT NULL, "Amount" INTEGER NOT NULL, "DurationMonths" INTEGER NOT NULL DEFAULT 0, "PostLimit" INTEGER NOT NULL DEFAULT 0,
                "MethodId" INTEGER NULL, "MethodName" TEXT NOT NULL, "AccountId" INTEGER NULL, "AccountInfo" TEXT NULL, "TxnId" TEXT NULL, "PayDate" TEXT NOT NULL,
                "ReceiptUrl" TEXT NOT NULL, "Note" TEXT NULL, "Status" TEXT NOT NULL DEFAULT \'Pending\', "AdminNote" TEXT NULL, "ReviewedBy" INTEGER NULL,
                "ReviewedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserSubscriptions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "PaymentId" INTEGER NULL,
                "PackageName" TEXT NOT NULL, "Audience" TEXT NOT NULL, "StartsAt" TEXT NOT NULL, "EndsAt" TEXT NULL, "PostsTotal" INTEGER NOT NULL DEFAULT 0,
                "PostsUsed" INTEGER NOT NULL DEFAULT 0, "CreatedAt" TEXT NOT NULL)');
        } else {
            $e = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PaymentMethods` (`Id` INT NOT NULL AUTO_INCREMENT, `Name` VARCHAR(40) NOT NULL, `Kind` VARCHAR(10) NOT NULL DEFAULT 'other',
                `LogoUrl` VARCHAR(500) NULL, `Color` VARCHAR(9) NULL, `TxnRequired` TINYINT(1) NOT NULL DEFAULT 1, `Instructions` VARCHAR(1000) NULL, `IsActive` TINYINT(1) NOT NULL DEFAULT 1,
                `SortOrder` INT NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PaymentAccounts` (`Id` INT NOT NULL AUTO_INCREMENT, `MethodId` INT NOT NULL, `Title` VARCHAR(80) NOT NULL,
                `AccountName` VARCHAR(100) NULL, `AccountNumber` VARCHAR(40) NULL, `BankName` VARCHAR(100) NULL, `Branch` VARCHAR(100) NULL, `RoutingNumber` VARCHAR(20) NULL,
                `AccountType` VARCHAR(40) NULL, `QrImageUrl` VARCHAR(500) NULL, `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `SortOrder` INT NOT NULL DEFAULT 0,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), KEY `IX_PA_Method` (`MethodId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PaymentRequests` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `PackageId` INT NULL,
                `PackageName` VARCHAR(80) NOT NULL, `Audience` VARCHAR(10) NOT NULL, `Amount` INT NOT NULL, `DurationMonths` INT NOT NULL DEFAULT 0, `PostLimit` INT NOT NULL DEFAULT 0,
                `MethodId` INT NULL, `MethodName` VARCHAR(40) NOT NULL, `AccountId` INT NULL, `AccountInfo` VARCHAR(200) NULL, `TxnId` VARCHAR(40) NULL, `PayDate` DATE NOT NULL,
                `ReceiptUrl` VARCHAR(300) NOT NULL, `Note` VARCHAR(500) NULL, `Status` VARCHAR(10) NOT NULL DEFAULT 'Pending', `AdminNote` VARCHAR(300) NULL, `ReviewedBy` INT NULL,
                `ReviewedAt` DATETIME NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`), KEY `IX_PR_User` (`UserId`), KEY `IX_PR_Status` (`Status`), KEY `IX_PR_Txn` (`TxnId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserSubscriptions` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `PaymentId` INT NULL,
                `PackageName` VARCHAR(80) NOT NULL, `Audience` VARCHAR(10) NOT NULL, `StartsAt` DATETIME NOT NULL, `EndsAt` DATETIME NULL, `PostsTotal` INT NOT NULL DEFAULT 0,
                `PostsUsed` INT NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), KEY `IX_US_User` (`UserId`, `Audience`)) $e");
        }
        $this->paymentReady = true;
        try {
            $seeded = $this->GetSiteContent(['pay_seeded' => ''])['pay_seeded'] ?? '';
            if ($seeded === '') {
                if ((int)$this->scalar('SELECT COUNT(*) FROM `PaymentMethods`') === 0) $this->seedPaymentMethods();
                $this->SaveSiteContent(['pay_seeded' => '1', 'pay_bank_v2' => '1']);
            } elseif (($this->GetSiteContent(['pay_bank_v2' => ''])['pay_bank_v2'] ?? '') === '') {
                // আগের ভার্সনে বসানো খালি ব্যাংক অ্যাকাউন্টে আসল তথ্য বসায় (একবারই)
                $b = self::DEFAULT_BANK;
                $this->exec("UPDATE `PaymentAccounts` SET `Title` = :t, `BankName` = :b, `AccountName` = :a, `AccountNumber` = :n, `Branch` = :br, `RoutingNumber` = :r
                    WHERE (`AccountNumber` IS NULL OR `AccountNumber` = '') AND `Title` = :old
                      AND `MethodId` IN (SELECT `Id` FROM `PaymentMethods` WHERE `Kind` = 'bank')",
                    ['t' => $b['Title'], 'b' => $b['BankName'], 'a' => $b['AccountName'], 'n' => $b['AccountNumber'], 'br' => $b['Branch'], 'r' => $b['RoutingNumber'], 'old' => 'Bangla QR — স্ক্যান করে পেমেন্ট']);
                $this->SaveSiteContent(['pay_bank_v2' => '1']);
            }
        } catch (Throwable $e) { error_log('[payment seed] ' . $e->getMessage()); }
    }

    private function seedPaymentMethods(): void
    {
        $scan = "নিচের QR কোডটি অ্যাপের “Scan QR” দিয়ে স্ক্যান করুন, প্যাকেজের ঠিক টাকার পরিমাণ লিখে পেমেন্ট করুন। তারপর Transaction ID কপি করে পেমেন্ট সফল হওয়ার স্ক্রিনশট নিন।";
        $methods = [
            ['Bank Transfer', 'bank', '#1D7FE5', 0, "ব্যাংক অ্যাপ বা যেকোনো Bangla QR সাপোর্টেড অ্যাপ দিয়ে QR স্ক্যান করে পেমেন্ট করুন, অথবা ব্যাংকে গিয়ে জমা দিন। Deposit slip / receipt এর ছবি অবশ্যই আপলোড করুন।"],
            ['bKash', 'mobile', '#E2136E', 1, "bKash অ্যাপ খুলে “Scan QR” চাপুন। " . $scan],
            ['Nagad', 'mobile', '#F6921E', 1, "Nagad অ্যাপ খুলে “Scan QR” চাপুন। " . $scan],
            ['Rocket', 'mobile', '#8C3494', 1, "Rocket অ্যাপ খুলে “Scan QR” চাপুন। " . $scan],
            ['Other', 'other', '#475569', 1, "যেকোনো Bangla QR সাপোর্টেড অ্যাপ (Upay, Cellfin, ব্যাংক অ্যাপ ইত্যাদি) দিয়ে QR স্ক্যান করে পেমেন্ট করুন। " . $scan],
        ];
        foreach ($methods as $i => [$name, $kind, $color, $txn, $ins]) {
            $mid = $this->insert('INSERT INTO `PaymentMethods` (`Name`, `Kind`, `Color`, `TxnRequired`, `Instructions`, `IsActive`, `SortOrder`, `CreatedAt`) VALUES (:n, :k, :c, :t, :i, 1, :s, :d)',
                ['n' => $name, 'k' => $kind, 'c' => $color, 't' => $txn, 'i' => $ins, 's' => $i + 1, 'd' => now()]);
            if ($kind === 'bank') {
                $b = self::DEFAULT_BANK;
                $this->insert('INSERT INTO `PaymentAccounts` (`MethodId`, `Title`, `BankName`, `AccountName`, `AccountNumber`, `Branch`, `RoutingNumber`, `QrImageUrl`, `IsActive`, `SortOrder`, `CreatedAt`)
                    VALUES (:m, :t, :b, :a, :n, :br, :r, :q, 1, 1, :d)',
                    ['m' => $mid, 't' => $b['Title'], 'b' => $b['BankName'], 'a' => $b['AccountName'], 'n' => $b['AccountNumber'], 'br' => $b['Branch'], 'r' => $b['RoutingNumber'], 'q' => self::DEFAULT_QR, 'd' => now()]);
                continue;
            }
            $this->insert('INSERT INTO `PaymentAccounts` (`MethodId`, `Title`, `AccountName`, `QrImageUrl`, `IsActive`, `SortOrder`, `CreatedAt`) VALUES (:m, :t, :a, :q, 1, 1, :d)',
                ['m' => $mid, 't' => 'Bangla QR — স্ক্যান করে পেমেন্ট', 'a' => 'BENGOL BAY', 'q' => self::DEFAULT_QR, 'd' => now()]);
        }
    }

    // ============================================================
    // ===== মেথড ও অ্যাকাউন্ট =====
    // ============================================================
    private function decorateMethod(Obj $m): Obj
    {
        $m->Id = (int)$m->Id; $m->TxnRequired = (bool)(int)$m->TxnRequired; $m->IsActive = (bool)(int)$m->IsActive; $m->SortOrder = (int)$m->SortOrder;
        $m->Kind = in_array($m->Kind, self::PAYMENT_KINDS, true) ? $m->Kind : 'other';
        $m->Color = preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$m->Color) ? $m->Color : '#475569';
        return $m;
    }

    private function decorateAccount(Obj $a): Obj
    {
        $a->Id = (int)$a->Id; $a->MethodId = (int)$a->MethodId; $a->IsActive = (bool)(int)$a->IsActive; $a->SortOrder = (int)$a->SortOrder;
        return $a;
    }

    /** @return Obj[] মেথড (প্রতিটার ভিতরে ->Accounts) */
    public function GetPaymentMethods(bool $activeOnly = false): array
    {
        try {
            $this->EnsurePaymentTables();
            $methods = array_map(fn($m) => $this->decorateMethod($m), $this->all('SELECT * FROM `PaymentMethods`' . ($activeOnly ? ' WHERE `IsActive` = 1' : '') . ' ORDER BY `SortOrder`, `Id`'));
            $accs = array_map(fn($a) => $this->decorateAccount($a), $this->all('SELECT * FROM `PaymentAccounts`' . ($activeOnly ? ' WHERE `IsActive` = 1' : '') . ' ORDER BY `SortOrder`, `Id`'));
            foreach ($methods as $m) $m->Accounts = array_values(array_filter($accs, fn($a) => $a->MethodId === $m->Id));
            if ($activeOnly) $methods = array_values(array_filter($methods, fn($m) => count($m->Accounts) > 0));
            return $methods;
        } catch (Throwable $e) { error_log('[GetPaymentMethods] ' . $e->getMessage()); return []; }
    }

    public function GetPaymentMethod(int $id): ?Obj
    {
        $this->EnsurePaymentTables();
        $m = $this->one('SELECT * FROM `PaymentMethods` WHERE `Id` = :i', ['i' => $id]);
        return $m ? $this->decorateMethod($m) : null;
    }

    public function GetPaymentAccount(int $id): ?Obj
    {
        $this->EnsurePaymentTables();
        $a = $this->one('SELECT * FROM `PaymentAccounts` WHERE `Id` = :i', ['i' => $id]);
        return $a ? $this->decorateAccount($a) : null;
    }

    public function SavePaymentMethod(int $id, array $d): int
    {
        $this->EnsurePaymentTables();
        $args = ['n' => $d['Name'], 'k' => $d['Kind'], 'c' => $d['Color'], 't' => (int)$d['TxnRequired'], 'i' => $d['Instructions'] !== '' ? $d['Instructions'] : null, 'a' => (int)$d['IsActive']];
        if ($id <= 0) {
            $args['s'] = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `PaymentMethods`') + 1;
            $args['d'] = now(); $args['l'] = $d['LogoUrl'];
            return $this->insert('INSERT INTO `PaymentMethods` (`Name`, `Kind`, `Color`, `TxnRequired`, `Instructions`, `IsActive`, `SortOrder`, `CreatedAt`, `LogoUrl`)
                VALUES (:n, :k, :c, :t, :i, :a, :s, :d, :l)', $args);
        }
        $args['id'] = $id;
        $this->exec('UPDATE `PaymentMethods` SET `Name` = :n, `Kind` = :k, `Color` = :c, `TxnRequired` = :t, `Instructions` = :i, `IsActive` = :a WHERE `Id` = :id', $args);
        if (array_key_exists('LogoUrl', $d)) $this->exec('UPDATE `PaymentMethods` SET `LogoUrl` = :l WHERE `Id` = :id', ['l' => $d['LogoUrl'], 'id' => $id]);
        return $id;
    }

    /** মুছে ফেলে; ফেরত দেয় মুছে যাওয়া ফাইলগুলোর URL (লোগো/QR) */
    public function DeletePaymentMethod(int $id): array
    {
        $this->EnsurePaymentTables();
        $files = [];
        $m = $this->GetPaymentMethod($id);
        if ($m && $m->LogoUrl) $files[] = $m->LogoUrl;
        foreach ($this->all('SELECT `QrImageUrl` FROM `PaymentAccounts` WHERE `MethodId` = :i', ['i' => $id]) as $a) if ($a->QrImageUrl) $files[] = $a->QrImageUrl;
        $this->exec('DELETE FROM `PaymentAccounts` WHERE `MethodId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `PaymentMethods` WHERE `Id` = :i', ['i' => $id]);
        return $files;
    }

    public function MovePaymentMethod(int $id, int $dir): void
    {
        $this->EnsurePaymentTables();
        $ids = array_map(fn($r) => (int)$r->Id, $this->all('SELECT `Id` FROM `PaymentMethods` ORDER BY `SortOrder`, `Id`'));
        $i = array_search($id, $ids, true);
        $j = $i === false ? false : $i + ($dir < 0 ? -1 : 1);
        if ($i === false || $j < 0 || $j >= count($ids)) return;
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        foreach ($ids as $n => $mid) $this->exec('UPDATE `PaymentMethods` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => $n + 1, 'i' => $mid]);
    }

    public function SavePaymentAccount(int $id, array $d): int
    {
        $this->EnsurePaymentTables();
        $cols = ['Title', 'AccountName', 'AccountNumber', 'BankName', 'Branch', 'RoutingNumber', 'AccountType'];
        $args = [];
        foreach ($cols as $c) $args[$c] = ($d[$c] ?? '') !== '' ? $d[$c] : null;
        $args['IsActive'] = (int)$d['IsActive'];
        if ($id <= 0) {
            $args['MethodId'] = (int)$d['MethodId'];
            $args['QrImageUrl'] = $d['QrImageUrl'] ?? null;
            $args['SortOrder'] = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `PaymentAccounts` WHERE `MethodId` = :m', ['m' => $args['MethodId']]) + 1;
            $args['CreatedAt'] = now();
            $keys = array_keys($args);
            return $this->insert('INSERT INTO `PaymentAccounts` (`' . implode('`, `', $keys) . '`) VALUES (:' . implode(', :', $keys) . ')', $args);
        }
        if (array_key_exists('QrImageUrl', $d)) $args['QrImageUrl'] = $d['QrImageUrl'];
        $set = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($args)));
        $args['id'] = $id;
        $this->exec("UPDATE `PaymentAccounts` SET $set WHERE `Id` = :id", $args);
        return $id;
    }

    public function DeletePaymentAccount(int $id): ?string
    {
        $this->EnsurePaymentTables();
        $a = $this->GetPaymentAccount($id);
        $this->exec('DELETE FROM `PaymentAccounts` WHERE `Id` = :i', ['i' => $id]);
        return $a?->QrImageUrl;
    }

    // ============================================================
    // ===== পেমেন্ট রিকোয়েস্ট =====
    // ============================================================
    public function CreatePaymentRequest(array $d): int
    {
        $this->EnsurePaymentTables();
        $d['Status'] = 'Pending'; $d['CreatedAt'] = now();
        $keys = array_keys($d);
        return $this->insert('INSERT INTO `PaymentRequests` (`' . implode('`, `', $keys) . '`) VALUES (:' . implode(', :', $keys) . ')', $d);
    }

    private function decoratePayment(Obj $p): Obj
    {
        foreach (['Id', 'UserId', 'PackageId', 'Amount', 'DurationMonths', 'PostLimit', 'MethodId', 'AccountId'] as $k) if ($p->$k !== null) $p->$k = (int)$p->$k;
        $p->PayDate = substr((string)$p->PayDate, 0, 10);
        $p->IsPdf = str_ends_with(strtolower((string)$p->ReceiptUrl), '.pdf');
        $p->Initials = initials($p->FullName ?? '');
        return $p;
    }

    private const PAYMENT_SELECT = 'SELECT r.*, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`, u.`MobileNumber`, u.`Email`
        FROM `PaymentRequests` r LEFT JOIN `Users` u ON u.`Id` = r.`UserId`';

    /** @return Obj[] status = Pending|Approved|Rejected|null(সব) */
    public function GetPaymentRequests(?string $status = null, int $limit = 200): array
    {
        try {
            $this->EnsurePaymentTables();
            $limit = max(1, min(500, $limit));
            $rows = $status ? $this->all(self::PAYMENT_SELECT . " WHERE r.`Status` = :s ORDER BY r.`Id` DESC LIMIT $limit", ['s' => $status])
                : $this->all(self::PAYMENT_SELECT . " ORDER BY CASE WHEN r.`Status` = 'Pending' THEN 0 ELSE 1 END, r.`Id` DESC LIMIT $limit");
            return array_map(fn($r) => $this->decoratePayment($r), $rows);
        } catch (Throwable $e) { error_log('[GetPaymentRequests] ' . $e->getMessage()); return []; }
    }

    public function GetPaymentRequest(int $id): ?Obj
    {
        $this->EnsurePaymentTables();
        $r = $this->one(self::PAYMENT_SELECT . ' WHERE r.`Id` = :i', ['i' => $id]);
        return $r ? $this->decoratePayment($r) : null;
    }

    /** @return Obj[] */
    public function GetUserPayments(int $userId): array
    {
        try {
            $this->EnsurePaymentTables();
            return array_map(fn($r) => $this->decoratePayment($r), $this->all(self::PAYMENT_SELECT . ' WHERE r.`UserId` = :u ORDER BY r.`Id` DESC LIMIT 100', ['u' => $userId]));
        } catch (Throwable $e) { return []; }
    }

    public function PaymentStats(): array
    {
        $o = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Total' => 0, 'Revenue' => 0];
        try {
            $this->EnsurePaymentTables();
            foreach ($this->all('SELECT `Status`, COUNT(*) AS N, SUM(`Amount`) AS A FROM `PaymentRequests` GROUP BY `Status`') as $r) {
                if (isset($o[$r->Status])) $o[$r->Status] = (int)$r->N;
                $o['Total'] += (int)$r->N;
                if ($r->Status === 'Approved') $o['Revenue'] = (int)$r->A;
            }
        } catch (Throwable $e) { }
        return $o;
    }

    public function CountPendingPayments(): int
    {
        try { $this->EnsurePaymentTables(); return (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `Status` = 'Pending'"); } catch (Throwable $e) { return 0; }
    }

    public function CountUserPendingPayments(int $userId): int
    {
        $this->EnsurePaymentTables();
        return (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `UserId` = :u AND `Status` = 'Pending'", ['u' => $userId]);
    }

    /** একই Transaction ID আগে কেউ জমা দিয়েছে কিনা (Reject হওয়াগুলো বাদে) */
    public function PaymentTxnUsed(string $txn): bool
    {
        $this->EnsurePaymentTables();
        return (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE UPPER(`TxnId`) = UPPER(:t) AND `Status` <> 'Rejected'", ['t' => $txn]) > 0;
    }

    /**
     * Accept: স্ট্যাটাস Approved + Subscription/ক্রেডিট চালু।
     * শিক্ষকের আগের Subscription চালু থাকলে তার শেষ দিন থেকে নতুন মেয়াদ যোগ হয়।
     */
    public function ApprovePayment(int $id, int $adminId, string $note = ''): ?Obj
    {
        $this->EnsurePaymentTables();
        $p = $this->GetPaymentRequest($id);
        if (!$p || $p->Status !== 'Pending') return null;
        $this->pdo->beginTransaction();
        try {
            $n = $this->exec("UPDATE `PaymentRequests` SET `Status` = 'Approved', `AdminNote` = :n, `ReviewedBy` = :a, `ReviewedAt` = :t WHERE `Id` = :i AND `Status` = 'Pending'",
                ['n' => $note !== '' ? $note : null, 'a' => $adminId, 't' => now(), 'i' => $id]);
            if (!$n) { $this->pdo->rollBack(); return null; }
            $start = now(); $end = null;
            if ($p->Audience === 'Teacher') {
                $cur = $this->GetActiveTeacherSubscription($p->UserId);
                if ($cur && $cur->EndsAt && $cur->EndsAt > $start) $start = $cur->EndsAt;
                $end = (new DateTime($start))->modify('+' . max(1, $p->DurationMonths) . ' months')->format('Y-m-d H:i:s');
            } elseif ($p->DurationMonths > 0) {
                $end = (new DateTime($start))->modify('+' . $p->DurationMonths . ' months')->format('Y-m-d H:i:s');
            }
            $this->insert('INSERT INTO `UserSubscriptions` (`UserId`, `PaymentId`, `PackageName`, `Audience`, `StartsAt`, `EndsAt`, `PostsTotal`, `PostsUsed`, `CreatedAt`)
                VALUES (:u, :p, :n, :a, :s, :e, :pt, 0, :c)', [
                'u' => $p->UserId, 'p' => $id, 'n' => $p->PackageName, 'a' => $p->Audience, 's' => $start, 'e' => $end,
                'pt' => $p->Audience === 'Student' ? $p->PostLimit : 0, 'c' => now(),
            ]);
            $this->pdo->commit();
        } catch (Throwable $e) { $this->pdo->rollBack(); throw $e; }
        return $this->GetPaymentRequest($id);
    }

    public function RejectPayment(int $id, int $adminId, string $note): ?Obj
    {
        $this->EnsurePaymentTables();
        $n = $this->exec("UPDATE `PaymentRequests` SET `Status` = 'Rejected', `AdminNote` = :n, `ReviewedBy` = :a, `ReviewedAt` = :t WHERE `Id` = :i AND `Status` = 'Pending'",
            ['n' => $note !== '' ? $note : null, 'a' => $adminId, 't' => now(), 'i' => $id]);
        return $n ? $this->GetPaymentRequest($id) : null;
    }

    public function DeletePaymentRequest(int $id): ?string
    {
        $this->EnsurePaymentTables();
        $p = $this->GetPaymentRequest($id);
        if (!$p) return null;
        $this->exec('DELETE FROM `PaymentRequests` WHERE `Id` = :i', ['i' => $id]);
        return $p->ReceiptUrl;
    }

    // ============================================================
    // ===== Subscription / ক্রেডিট =====
    // ============================================================
    /** শিক্ষকের চালু Subscription (সবচেয়ে দেরিতে শেষ হওয়াটা) */
    public function GetActiveTeacherSubscription(int $userId): ?Obj
    {
        try {
            $this->EnsurePaymentTables();
            return $this->one("SELECT * FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Teacher' AND `EndsAt` > :n ORDER BY `EndsAt` DESC LIMIT 1", ['u' => $userId, 'n' => now()]);
        } catch (Throwable $e) { return null; }
    }

    /** @return array<int,string> userId => EndsAt — যাদের Subscription চালু */
    public function GetActiveTeacherSubscriptionMap(): array
    {
        try {
            $this->EnsurePaymentTables();
            $o = [];
            foreach ($this->all("SELECT `UserId`, MAX(`EndsAt`) AS E FROM `UserSubscriptions` WHERE `Audience` = 'Teacher' AND `EndsAt` > :n GROUP BY `UserId`", ['n' => now()]) as $r) $o[(int)$r->UserId] = $r->E;
            return $o;
        } catch (Throwable $e) { return []; }
    }

    /** শিক্ষার্থীর বাকি পোস্ট ক্রেডিট: -1 = আনলিমিটেড */
    public function GetStudentPostCredits(int $userId): int
    {
        try {
            $this->EnsurePaymentTables();
            $left = 0;
            foreach ($this->all("SELECT `PostsTotal`, `PostsUsed` FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Student' AND (`EndsAt` IS NULL OR `EndsAt` > :n)", ['u' => $userId, 'n' => now()]) as $r) {
                if ((int)$r->PostsTotal === 0) return -1;
                $left += max(0, (int)$r->PostsTotal - (int)$r->PostsUsed);
            }
            return $left;
        } catch (Throwable $e) { return 0; }
    }

    /** একটা পোস্ট ক্রেডিট খরচ (আগে যেটার মেয়াদ আগে শেষ হবে সেটা থেকে) */
    public function ConsumeStudentPostCredit(int $userId): void
    {
        $this->EnsurePaymentTables();
        $rows = $this->all("SELECT `Id`, `PostsTotal`, `PostsUsed` FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Student' AND (`EndsAt` IS NULL OR `EndsAt` > :n)
            ORDER BY CASE WHEN `PostsTotal` = 0 THEN 1 ELSE 0 END, CASE WHEN `EndsAt` IS NULL THEN 1 ELSE 0 END, `EndsAt`, `Id`", ['u' => $userId, 'n' => now()]);
        foreach ($rows as $r) {
            if ((int)$r->PostsTotal === 0 || (int)$r->PostsUsed < (int)$r->PostsTotal) {
                $this->exec('UPDATE `UserSubscriptions` SET `PostsUsed` = `PostsUsed` + 1 WHERE `Id` = :i', ['i' => $r->Id]);
                return;
            }
        }
    }

    /** @return Obj[] ইউজারের সব Subscription */
    public function GetUserSubscriptions(int $userId): array
    {
        try {
            $this->EnsurePaymentTables();
            return $this->all('SELECT * FROM `UserSubscriptions` WHERE `UserId` = :u ORDER BY `Id` DESC', ['u' => $userId]);
        } catch (Throwable $e) { return []; }
    }

    // ============================================================
    // ===== নিয়ম: পেমেন্ট বাধ্যতামূলক কিনা (অ্যাডমিন চালু/বন্ধ করে) =====
    // ============================================================
    public function PaymentRules(): array
    {
        $v = $this->GetSiteContent(['pay_enforce_teacher' => '', 'pay_enforce_student' => '']);
        return ['teacher' => ($v['pay_enforce_teacher'] ?? '') === '1', 'student' => ($v['pay_enforce_student'] ?? '') === '1'];
    }

    /** শিক্ষক এখন কাজ করতে পারবে কিনা (নিয়ম বন্ধ থাকলে সবসময় হ্যাঁ) */
    public function TeacherCanWork(int $userId): bool
    {
        return !$this->PaymentRules()['teacher'] || $this->GetActiveTeacherSubscription($userId) !== null;
    }
}
