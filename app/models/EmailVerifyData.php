<?php
/**
 * সাইনআপের পর ইমেইল ভেরিফিকেশন (OTP)
 *  - Users.EmailVerified : 1 = ভেরিফাইড (পুরোনো সব অ্যাকাউন্ট ডিফল্টে 1), 0 = নতুন সাইনআপ, OTP দেয়নি
 *  - Users.PendingRef    : রেফারেল কোড — ভেরিফাই হলে তবেই বোনাস যায়
 *  - EmailOtps           : ৬ ডিজিটের কোড (ডাটাবেসে শুধু hash), ১০ মিনিট মেয়াদ, ৫ বার ভুল হলে বাতিল
 * টেবিল / কলাম নিজে থেকেই তৈরি হয় — SQL ইমপোর্ট লাগবে না।
 */
trait EmailVerifyData
{
    private bool $otpReady = false;
    public const OTP_MINUTES = 10;
    public const OTP_MAX_TRIES = 5;

    public function EnsureEmailVerify(): void
    {
        if ($this->otpReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        try { $this->pdo->query('SELECT `EmailVerified` FROM `Users` LIMIT 1'); }
        catch (Throwable $e) {
            try { $this->pdo->exec('ALTER TABLE `Users` ADD COLUMN `EmailVerified` ' . ($sqlite ? 'INTEGER' : 'TINYINT(1)') . ' NOT NULL DEFAULT 1'); }
            catch (Throwable $e2) { error_log('[EmailVerified col] ' . $e2->getMessage()); }
        }
        try { $this->pdo->query('SELECT `PendingRef` FROM `Users` LIMIT 1'); }
        catch (Throwable $e) {
            try { $this->pdo->exec('ALTER TABLE `Users` ADD COLUMN `PendingRef` ' . ($sqlite ? 'TEXT' : 'VARCHAR(16)') . ' NULL'); }
            catch (Throwable $e2) { error_log('[PendingRef col] ' . $e2->getMessage()); }
        }
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "EmailOtps" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "CodeHash" TEXT NOT NULL,
                "ExpiresAt" TEXT NOT NULL, "Tries" INTEGER NOT NULL DEFAULT 0, "UsedAt" TEXT NULL, "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `EmailOtps` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `UserId` INT NOT NULL,
                `CodeHash` CHAR(64) NOT NULL,
                `ExpiresAt` DATETIME NOT NULL,
                `Tries` INT NOT NULL DEFAULT 0,
                `UsedAt` DATETIME NULL,
                `Ip` VARCHAR(64) NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`),
                KEY `IX_EmailOtps_User` (`UserId`, `CreatedAt`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->otpReady = true;
    }

    private function otpHash(int $userId, string $code): string
    {
        return hash('sha256', $userId . ':' . $code . ':' . (defined('APP_KEY') ? APP_KEY : 'mm-otp'));
    }

    public function IsEmailVerified(int $userId): bool
    {
        $this->EnsureEmailVerify();
        $v = $this->scalar('SELECT `EmailVerified` FROM `Users` WHERE `Id` = :u', ['u' => $userId]);
        return $v === null || $v === false || (int)$v === 1;
    }

    /** নতুন সাইনআপ — ভেরিফাই না হওয়া পর্যন্ত লগইন হবে না; রেফারেল কোড জমা থাকে */
    public function MarkEmailUnverified(int $userId, string $refCode = ''): void
    {
        $this->EnsureEmailVerify();
        $this->exec('UPDATE `Users` SET `EmailVerified` = 0, `PendingRef` = :r WHERE `Id` = :u', ['u' => $userId, 'r' => $refCode !== '' ? $refCode : null]);
    }

    /** ভেরিফাইড করে; জমা থাকা রেফারেল কোড ফেরত দেয় (একবারই) */
    public function MarkEmailVerified(int $userId): string
    {
        $this->EnsureEmailVerify();
        $ref = (string)($this->scalar('SELECT `PendingRef` FROM `Users` WHERE `Id` = :u AND `EmailVerified` = 0', ['u' => $userId]) ?? '');
        $this->exec('UPDATE `Users` SET `EmailVerified` = 1, `PendingRef` = NULL WHERE `Id` = :u', ['u' => $userId]);
        $this->exec('UPDATE `EmailOtps` SET `UsedAt` = :t WHERE `UserId` = :u AND `UsedAt` IS NULL', ['t' => now(), 'u' => $userId]);
        return $ref;
    }

    /** নতুন OTP বানায় (আগেরগুলো বাতিল) — আসল ৬ ডিজিটের কোড রিটার্ন করে */
    public function CreateEmailOtp(int $userId, string $ip): string
    {
        $this->EnsureEmailVerify();
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->exec('UPDATE `EmailOtps` SET `UsedAt` = :t WHERE `UserId` = :u AND `UsedAt` IS NULL', ['t' => now(), 'u' => $userId]);
        $this->insert('INSERT INTO `EmailOtps` (`UserId`, `CodeHash`, `ExpiresAt`, `Ip`, `CreatedAt`) VALUES (:u, :h, :x, :ip, :c)', [
            'u' => $userId, 'h' => $this->otpHash($userId, $code), 'x' => date('Y-m-d H:i:s', time() + self::OTP_MINUTES * 60),
            'ip' => mb_substr($ip, 0, 64), 'c' => now(),
        ]);
        return $code;
    }

    /** শেষ OTP কত সেকেন্ড আগে পাঠানো হয়েছে (না থাকলে null) */
    public function SecondsSinceLastOtp(int $userId): ?int
    {
        $this->EnsureEmailVerify();
        $t = $this->scalar('SELECT MAX(`CreatedAt`) FROM `EmailOtps` WHERE `UserId` = :u', ['u' => $userId]);
        return $t ? max(0, time() - strtotime((string)$t)) : null;
    }

    public function CountRecentOtps(int $userId, int $minutes): int
    {
        $this->EnsureEmailVerify();
        return (int)$this->scalar('SELECT COUNT(*) FROM `EmailOtps` WHERE `UserId` = :u AND `CreatedAt` >= :s',
            ['u' => $userId, 's' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
    }

    /** 'ok' | 'wrong' | 'expired' (মেয়াদ শেষ / কোড নেই) | 'locked' (৫ বার ভুল) */
    public function CheckEmailOtp(int $userId, string $code): string
    {
        $this->EnsureEmailVerify();
        $r = $this->one('SELECT `Id`, `CodeHash`, `ExpiresAt`, `Tries` FROM `EmailOtps` WHERE `UserId` = :u AND `UsedAt` IS NULL ORDER BY `Id` DESC LIMIT 1', ['u' => $userId]);
        if (!$r || strtotime((string)$r->ExpiresAt) < time()) return 'expired';
        if ((int)$r->Tries >= self::OTP_MAX_TRIES) return 'locked';
        if (!preg_match('/^\d{6}$/', $code) || !hash_equals((string)$r->CodeHash, $this->otpHash($userId, $code))) {
            $this->exec('UPDATE `EmailOtps` SET `Tries` = `Tries` + 1 WHERE `Id` = :id', ['id' => (int)$r->Id]);
            return (int)$r->Tries + 1 >= self::OTP_MAX_TRIES ? 'locked' : 'wrong';
        }
        return 'ok';
    }
}
