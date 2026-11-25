<?php
/**
 * রিফান্ড আবেদন — Help Desk → Contact এর "রিফান্ড আবেদন" ফর্ম থেকে আসে (Transaction ID সহ), অ্যাডমিন Admin → Refund Requests এ দেখেন।
 * আবেদনগুলো ব্যক্তিগত — Help Desk এর পোস্টের মতো সবাই দেখে না। টেবিল নিজে থেকেই তৈরি হয় (SQL ইমপোর্ট লাগে না)।
 */
trait RefundData
{
    private bool $refundReady = false;

    /** কারণ: key => [বাংলা, English] — রিফান্ড নীতির ৩ নম্বর অংশের সাথে মিল রেখে */
    public const REFUND_REASONS = [
        'duplicate' => ['একই পেমেন্ট দুইবার কাটা হয়েছে', 'Duplicate payment'],
        'not_activated' => ['টাকা দিয়েছি কিন্তু ২৪ ঘণ্টায় প্যাকেজ / কোর্স চালু হয়নি', 'Paid but nothing activated within 24 hours'],
        'tutor_rejected' => ['টিচার প্যাকেজ কিনেছি কিন্তু Become a Tutor আবেদন বাতিল হয়েছে', 'Tutor verification refused'],
        'wrong_package' => ['ভুল করে ভুল প্যাকেজ কিনেছি (২৪ ঘণ্টার মধ্যে, ব্যবহার করিনি)', 'Wrong package bought by mistake'],
        'outage' => ['সাইট টানা ৩ দিনের বেশি বন্ধ ছিল', 'Service unavailable for more than 3 days'],
        'other' => ['অন্য কারণ', 'Other reason'],
    ];

    public function EnsureRefundTable(): void
    {
        if ($this->refundReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "RefundRequests" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "PaymentId" INTEGER NULL,
                "TxnId" TEXT NOT NULL, "PackageName" TEXT NULL, "Amount" INTEGER NULL, "Reason" TEXT NOT NULL, "Details" TEXT NULL,
                "Status" TEXT NOT NULL DEFAULT \'Pending\', "AdminNote" TEXT NULL, "ReviewedBy" INTEGER NULL, "ReviewedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `RefundRequests` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `PaymentId` INT NULL,
                `TxnId` VARCHAR(60) NOT NULL, `PackageName` VARCHAR(100) NULL, `Amount` INT NULL, `Reason` VARCHAR(20) NOT NULL, `Details` VARCHAR(1500) NULL,
                `Status` VARCHAR(10) NOT NULL DEFAULT 'Pending', `AdminNote` VARCHAR(500) NULL, `ReviewedBy` INT NULL, `ReviewedAt` DATETIME NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), KEY `IX_RR_User` (`UserId`), KEY `IX_RR_Status` (`Status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->refundReady = true;
    }

    public function CreateRefundRequest(array $r): int
    {
        $this->EnsureRefundTable();
        return (int)$this->insert('INSERT INTO `RefundRequests` (`UserId`, `PaymentId`, `TxnId`, `PackageName`, `Amount`, `Reason`, `Details`, `Status`, `CreatedAt`)
            VALUES (:u, :p, :t, :pk, :a, :r, :d, \'Pending\', :c)', ['u' => $r['UserId'], 'p' => $r['PaymentId'] ?: null, 't' => $r['TxnId'], 'pk' => $r['PackageName'] ?: null,
            'a' => $r['Amount'] ?: null, 'r' => $r['Reason'], 'd' => $r['Details'] !== '' ? $r['Details'] : null, 'c' => now()]);
    }

    /** একই পেমেন্ট / Transaction ID তে আগের অপেক্ষমাণ আবেদন আছে কিনা */
    public function HasPendingRefund(int $userId, string $txn): bool
    {
        $this->EnsureRefundTable();
        return (int)$this->scalar("SELECT COUNT(*) FROM `RefundRequests` WHERE `UserId` = :u AND LOWER(`TxnId`) = LOWER(:t) AND `Status` = 'Pending'", ['u' => $userId, 't' => $txn]) > 0;
    }

    public function GetUserRefundRequests(int $userId): array
    {
        try { $this->EnsureRefundTable(); return $this->all('SELECT * FROM `RefundRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 20', ['u' => $userId]); } catch (Throwable $e) { return []; }
    }

    /** অ্যাডমিন: status = Pending|Approved|Rejected|all */
    public function GetRefundRequests(string $status = 'Pending'): array
    {
        $this->EnsureRefundTable();
        $w = $status === 'all' ? '' : ' WHERE r.`Status` = :s';
        return $this->all('SELECT r.*, u.`FullName`, u.`UserName`, u.`MobileNumber`, u.`Email`, u.`Role`, p.`MethodName`, p.`Status` AS PayStatus, p.`CreatedAt` AS PaidAt
            FROM `RefundRequests` r LEFT JOIN `Users` u ON u.`Id` = r.`UserId` LEFT JOIN `PaymentRequests` p ON p.`Id` = r.`PaymentId`' . $w . ' ORDER BY r.`Id` DESC LIMIT 300',
            $status === 'all' ? [] : ['s' => $status]);
    }

    public function GetRefundRequest(int $id): ?Obj
    {
        $this->EnsureRefundTable();
        return $this->one('SELECT * FROM `RefundRequests` WHERE `Id` = :i', ['i' => $id]);
    }

    public function CountPendingRefunds(): int
    {
        try { $this->EnsureRefundTable(); return (int)$this->scalar("SELECT COUNT(*) FROM `RefundRequests` WHERE `Status` = 'Pending'"); } catch (Throwable $e) { return 0; }
    }

    public function DecideRefund(int $id, string $status, string $note, int $adminId): void
    {
        $this->exec('UPDATE `RefundRequests` SET `Status` = :s, `AdminNote` = :n, `ReviewedBy` = :a, `ReviewedAt` = :t WHERE `Id` = :i',
            ['s' => $status, 'n' => $note !== '' ? $note : null, 'a' => $adminId, 't' => now(), 'i' => $id]);
    }
}
