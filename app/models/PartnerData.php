<?php
/**
 * পার্টনার / এজেন্সি রেফারেল — Db ক্লাসে trait হিসেবে যুক্ত।
 *  - Partners        : প্রতিটা এজেন্সির একটা স্পেশাল আইডি (Id) + রেফারেল কোড (Code) + রেফারেল লিংক (?p=CODE)
 *  - PartnerClicks   : লিংকে কতবার ক্লিক — কোন প্ল্যাটফর্ম থেকে (?p=CODE&src=facebook)
 *  - PartnerSignups  : কে কোন পার্টনারের লিংক বা কোড দিয়ে সাইনআপ করেছে (Via = link | code)
 *  প্যাকেজ কেনা = ঐ ইউজারদের Approve হওয়া পেমেন্ট (PaymentRequests)
 *  ইউজার রেফারেল (MMxxxxxx কোড, বোনাস) থেকে আলাদা — দুটো একসাথেও চলতে পারে।
 * টেবিল নিজে থেকেই তৈরি হয় — SQL ইমপোর্ট লাগবে না।
 */
trait PartnerData
{
    private bool $partnerReady = false;

    /** প্ল্যাটফর্ম => [নাম, আইকন, রং] — লিংকে &src=key */
    public const PARTNER_SOURCES = [
        'facebook' => ['Facebook', 'fab fa-facebook', '#1877F2'],
        'messenger' => ['Messenger', 'fab fa-facebook-messenger', '#0084FF'],
        'whatsapp' => ['WhatsApp', 'fab fa-whatsapp', '#25D366'],
        'telegram' => ['Telegram', 'fab fa-telegram', '#229ED9'],
        'youtube' => ['YouTube', 'fab fa-youtube', '#FF0000'],
        'tiktok' => ['TikTok', 'fab fa-tiktok', '#111827'],
        'instagram' => ['Instagram', 'fab fa-instagram', '#E1306C'],
        'imo' => ['IMO', 'fas fa-comment-dots', '#1D4ED8'],
        'sms' => ['SMS', 'fas fa-comment-sms', '#0F766E'],
        'email' => ['Email', 'fas fa-envelope', '#B45309'],
        'qr' => ['QR / পোস্টার', 'fas fa-qrcode', '#334155'],
        'website' => ['Website', 'fas fa-globe', '#7C3AED'],
        'other' => ['অন্যান্য', 'fas fa-share-nodes', '#64748B'],
        'direct' => ['সাধারণ লিংক', 'fas fa-link', '#0EA5E9'],
        'code' => ['কোড দিয়ে', 'fas fa-ticket', '#CA8A04'],
    ];

    public static function partnerSource(string $s): array
    {
        return self::PARTNER_SOURCES[$s] ?? [ucfirst($s), 'fas fa-share-nodes', '#64748B'];
    }

    public function EnsurePartnerTables(): void
    {
        if ($this->partnerReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Partners" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Name" TEXT NOT NULL, "Code" TEXT NOT NULL UNIQUE,
                "Contact" TEXT NULL, "Note" TEXT NULL, "Landing" TEXT NOT NULL DEFAULT \'register\', "ReportToken" TEXT NOT NULL, "IsActive" INTEGER NOT NULL DEFAULT 1,
                "CreatedBy" INTEGER NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PartnerClicks" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PartnerId" INTEGER NOT NULL, "Source" TEXT NOT NULL,
                "Visitor" TEXT NOT NULL, "Ip" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_PC_Partner" ON "PartnerClicks" ("PartnerId", "CreatedAt")');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PartnerSignups" ("UserId" INTEGER PRIMARY KEY, "PartnerId" INTEGER NOT NULL, "Via" TEXT NOT NULL,
                "Source" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_PS_Partner" ON "PartnerSignups" ("PartnerId")');
        } else {
            $e = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Partners` (`Id` INT NOT NULL AUTO_INCREMENT, `Name` VARCHAR(100) NOT NULL, `Code` VARCHAR(30) NOT NULL,
                `Contact` VARCHAR(150) NULL, `Note` VARCHAR(300) NULL, `Landing` VARCHAR(10) NOT NULL DEFAULT 'register', `ReportToken` VARCHAR(40) NOT NULL,
                `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `CreatedBy` INT NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), UNIQUE KEY `UX_Partner_Code` (`Code`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PartnerClicks` (`Id` INT NOT NULL AUTO_INCREMENT, `PartnerId` INT NOT NULL, `Source` VARCHAR(30) NOT NULL,
                `Visitor` VARCHAR(64) NOT NULL, `Ip` VARCHAR(64) NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`), KEY `IX_PC_Partner` (`PartnerId`, `CreatedAt`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PartnerSignups` (`UserId` INT NOT NULL, `PartnerId` INT NOT NULL, `Via` VARCHAR(10) NOT NULL,
                `Source` VARCHAR(30) NOT NULL, `CreatedAt` DATETIME NOT NULL, PRIMARY KEY (`UserId`), KEY `IX_PS_Partner` (`PartnerId`)) $e");
        }
        $this->partnerReady = true;
    }

    /** কোডের নিয়ম: ৩–২০ অক্ষর, ইংরেজি অক্ষর / সংখ্যা / - _ ; ইউজার রেফারেল কোডের (MMxxxxxx) মতো হবে না */
    public function ValidPartnerCode(string $code): ?string
    {
        if (!preg_match('/^[A-Z0-9][A-Z0-9_-]{2,19}$/', $code)) return 'কোড ৩–২০ অক্ষরের হবে — শুধু ইংরেজি অক্ষর, সংখ্যা, - বা _ (যেমন: ABC10)।';
        if (preg_match('/^MM[A-Z0-9]{6}$/', $code)) return 'MM দিয়ে শুরু ৮ অক্ষরের কোড ইউজার রেফারেলের জন্য — অন্য কোড দিন।';
        return null;
    }

    public function GeneratePartnerCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $base = substr($base !== '' ? $base : 'AGENCY', 0, 8);
        for ($i = 0; $i < 20; $i++) {
            $c = $base . random_int(10, 99) . ($i > 5 ? random_int(0, 9) : '');
            if ($this->ValidPartnerCode($c) === null && !$this->PartnerCodeTaken($c)) return $c;
        }
        return 'P' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function PartnerCodeTaken(string $code, int $exceptId = 0): bool
    {
        $this->EnsurePartnerTables();
        if ((int)$this->scalar('SELECT COUNT(*) FROM `Partners` WHERE UPPER(`Code`) = :c AND `Id` <> :i', ['c' => strtoupper($code), 'i' => $exceptId]) > 0) return true;
        try { $this->EnsureReferralTables(); return (bool)$this->scalar('SELECT COUNT(*) FROM `ReferralCodes` WHERE `Code` = :c', ['c' => strtoupper($code)]); } catch (Throwable $e) { return false; }
    }

    public function CreatePartner(string $name, string $code, string $contact, string $note, string $landing, int $adminId): int
    {
        $this->EnsurePartnerTables();
        return (int)$this->insert('INSERT INTO `Partners` (`Name`, `Code`, `Contact`, `Note`, `Landing`, `ReportToken`, `IsActive`, `CreatedBy`, `CreatedAt`)
            VALUES (:n, :c, :ct, :no, :l, :t, 1, :a, :at)', ['n' => $name, 'c' => strtoupper($code), 'ct' => $contact !== '' ? $contact : null, 'no' => $note !== '' ? $note : null,
            'l' => $landing === 'home' ? 'home' : 'register', 't' => bin2hex(random_bytes(16)), 'a' => $adminId, 'at' => now()]);
    }

    public function UpdatePartner(int $id, string $name, string $code, string $contact, string $note, string $landing): void
    {
        $this->exec('UPDATE `Partners` SET `Name` = :n, `Code` = :c, `Contact` = :ct, `Note` = :no, `Landing` = :l WHERE `Id` = :i',
            ['n' => $name, 'c' => strtoupper($code), 'ct' => $contact !== '' ? $contact : null, 'no' => $note !== '' ? $note : null, 'l' => $landing === 'home' ? 'home' : 'register', 'i' => $id]);
    }

    public function SetPartnerActive(int $id, bool $on): void { $this->exec('UPDATE `Partners` SET `IsActive` = :a WHERE `Id` = :i', ['a' => $on ? 1 : 0, 'i' => $id]); }

    public function ResetPartnerToken(int $id): string
    {
        $t = bin2hex(random_bytes(16));
        $this->exec('UPDATE `Partners` SET `ReportToken` = :t WHERE `Id` = :i', ['t' => $t, 'i' => $id]);
        return $t;
    }

    /** পার্টনার মুছলে ক্লিকের হিসাবও মুছে যায়; ইউজাররা থাকে, শুধু "পার্টনার থেকে এসেছে" চিহ্ন উঠে যায় */
    public function DeletePartner(int $id): void
    {
        $this->EnsurePartnerTables();
        $this->exec('DELETE FROM `PartnerClicks` WHERE `PartnerId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `PartnerSignups` WHERE `PartnerId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `Partners` WHERE `Id` = :i', ['i' => $id]);
    }

    /**
     * একটাই স্থায়ী এজেন্সি লিংক + কোড — প্রথম পার্টনারটা (না থাকলে তৈরি হয়, কোড: MASHTER)।
     * এটা সবসময় চালু থাকে; যেকোনো এজেন্সিকে এই লিংক / কোড দেওয়া হয়।
     */
    public function GetDefaultPartner(): Obj
    {
        $this->EnsurePartnerTables();
        $p = $this->one('SELECT * FROM `Partners` ORDER BY `Id` LIMIT 1');
        if (!$p) {
            $code = 'MASHTER';
            if ($this->PartnerCodeTaken($code)) $code = $this->GeneratePartnerCode('MASHTER');
            try { $this->CreatePartner('Agency', $code, '', '', 'register', 0); } catch (Throwable $e) { error_log('[default partner] ' . $e->getMessage()); }
            $p = $this->one('SELECT * FROM `Partners` ORDER BY `Id` LIMIT 1');
        }
        if (!(int)$p->IsActive) { $this->SetPartnerActive((int)$p->Id, true); $p->IsActive = 1; }
        return $p;
    }

    public function GetPartner(int $id): ?Obj
    {
        $this->EnsurePartnerTables();
        return $this->one('SELECT * FROM `Partners` WHERE `Id` = :i', ['i' => $id]);
    }

    public function GetPartnerByCode(string $code, bool $activeOnly = true): ?Obj
    {
        if ($code === '' || strlen($code) > 30) return null;
        try {
            $this->EnsurePartnerTables();
            return $this->one('SELECT * FROM `Partners` WHERE UPPER(`Code`) = :c' . ($activeOnly ? ' AND `IsActive` = 1' : ''), ['c' => strtoupper($code)]);
        } catch (Throwable $e) { error_log('[GetPartnerByCode] ' . $e->getMessage()); return null; }
    }

    public function GetPartnerByToken(string $token): ?Obj
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) return null;
        $this->EnsurePartnerTables();
        return $this->one('SELECT * FROM `Partners` WHERE `ReportToken` = :t', ['t' => $token]);
    }

    /** লিংকে ক্লিক — একই ভিজিটর একই প্ল্যাটফর্ম থেকে ৩০ মিনিটের মধ্যে আবার এলে আলাদা ক্লিক ধরা হয় না */
    public function RecordPartnerClick(int $partnerId, string $source, string $visitor, string $ip): bool
    {
        try {
            $this->EnsurePartnerTables();
            $recent = (int)$this->scalar('SELECT COUNT(*) FROM `PartnerClicks` WHERE `PartnerId` = :p AND `Source` = :s AND `Visitor` = :v AND `CreatedAt` >= :t',
                ['p' => $partnerId, 's' => $source, 'v' => $visitor, 't' => date('Y-m-d H:i:s', time() - 1800)]);
            if ($recent > 0) return false;
            $this->exec('INSERT INTO `PartnerClicks` (`PartnerId`, `Source`, `Visitor`, `Ip`, `CreatedAt`) VALUES (:p, :s, :v, :ip, :t)',
                ['p' => $partnerId, 's' => $source, 'v' => $visitor, 'ip' => mb_substr($ip, 0, 64), 't' => now()]);
            return true;
        } catch (Throwable $e) { error_log('[RecordPartnerClick] ' . $e->getMessage()); return false; }
    }

    public function RecordPartnerSignup(int $userId, int $partnerId, string $via, string $source): void
    {
        try {
            $this->EnsurePartnerTables();
            if ($this->scalar('SELECT COUNT(*) FROM `PartnerSignups` WHERE `UserId` = :u', ['u' => $userId])) return;
            $this->exec('INSERT INTO `PartnerSignups` (`UserId`, `PartnerId`, `Via`, `Source`, `CreatedAt`) VALUES (:u, :p, :v, :s, :t)',
                ['u' => $userId, 'p' => $partnerId, 'v' => $via === 'code' ? 'code' : 'link', 's' => $source, 't' => now()]);
        } catch (Throwable $e) { error_log('[RecordPartnerSignup] ' . $e->getMessage()); }
    }

    /** [UserId => Obj(PartnerId, PartnerName, Code, Via, Source)] — অ্যাডমিন ড্যাশবোর্ডের "Joined via" */
    public function GetPartnerSignupMap(): array
    {
        try {
            $this->EnsurePartnerTables();
            $m = [];
            foreach ($this->all('SELECT s.`UserId`, s.`PartnerId`, s.`Via`, s.`Source`, p.`Name` AS PartnerName, p.`Code` FROM `PartnerSignups` s JOIN `Partners` p ON p.`Id` = s.`PartnerId`') as $r) $m[(int)$r->UserId] = $r;
            return $m;
        } catch (Throwable $e) { error_log('[GetPartnerSignupMap] ' . $e->getMessage()); return []; }
    }

    private function partnerRange(?string $from, ?string $to, string $col, array &$p): string
    {
        $w = '';
        if ($from) { $w .= " AND $col >= :from"; $p['from'] = $from . ' 00:00:00'; }
        if ($to) { $w .= " AND $col <= :to"; $p['to'] = $to . ' 23:59:59'; }
        return $w;
    }

    /** সব পার্টনার + মোট হিসাব (ক্লিক, ইউনিক ভিজিটর, সাইনআপ, ভেরিফাইড, কিনেছে, টাকা) */
    public function GetPartnersWithTotals(): array
    {
        $this->EnsurePartnerTables();
        $this->EnsurePaymentTables();
        $rows = $this->all('SELECT * FROM `Partners` ORDER BY `IsActive` DESC, `Id` DESC');
        foreach ($rows as $r) {
            $id = (int)$r->Id;
            $r->Clicks = (int)$this->scalar('SELECT COUNT(*) FROM `PartnerClicks` WHERE `PartnerId` = :p', ['p' => $id]);
            $r->Visitors = (int)$this->scalar('SELECT COUNT(DISTINCT `Visitor`) FROM `PartnerClicks` WHERE `PartnerId` = :p', ['p' => $id]);
            $r->Signups = (int)$this->scalar('SELECT COUNT(*) FROM `PartnerSignups` s JOIN `Users` u ON u.`Id` = s.`UserId` WHERE s.`PartnerId` = :p', ['p' => $id]);
            $r->Buyers = (int)$this->scalar("SELECT COUNT(DISTINCT pr.`UserId`) FROM `PaymentRequests` pr JOIN `PartnerSignups` s ON s.`UserId` = pr.`UserId` WHERE s.`PartnerId` = :p AND pr.`Status` = 'Approved'", ['p' => $id]);
            $r->Revenue = (int)$this->scalar("SELECT COALESCE(SUM(pr.`Amount`), 0) FROM `PaymentRequests` pr JOIN `PartnerSignups` s ON s.`UserId` = pr.`UserId` WHERE s.`PartnerId` = :p AND pr.`Status` = 'Approved'", ['p' => $id]);
        }
        return $rows;
    }

    /**
     * একটা পার্টনারের পুরো রিপোর্ট (তারিখ ফিল্টার সহ)।
     * ক্লিক = ঐ সময়ের ক্লিক, সাইনআপ = ঐ সময়ে যারা সাইনআপ করেছে, কেনা = ঐ সময়ে Approve হওয়া পেমেন্ট (পার্টনারের সব ইউজারের)
     * রিটার্ন: totals, sources[src => [...]], daily[Y-m-d => [...]], users[]
     */
    public function GetPartnerReport(int $partnerId, ?string $from, ?string $to): array
    {
        $this->EnsurePartnerTables();
        $this->EnsurePaymentTables();
        try { $this->EnsureEmailVerify(); } catch (Throwable $e) { }
        $blank = fn() => ['clicks' => 0, 'visitors' => 0, 'signups' => 0, 'verified' => 0, 'buyers' => 0, 'orders' => 0, 'revenue' => 0, 'pending' => 0];
        $sources = []; $daily = [];

        // ক্লিক
        $p = ['p' => $partnerId];
        $w = $this->partnerRange($from, $to, '`CreatedAt`', $p);
        $vis = []; $visAll = [];
        foreach ($this->all("SELECT `Source`, `Visitor`, `CreatedAt` FROM `PartnerClicks` WHERE `PartnerId` = :p $w", $p) as $c) {
            $s = (string)$c->Source; $d = substr((string)$c->CreatedAt, 0, 10);
            $sources[$s] ??= $blank(); $daily[$d] ??= $blank();
            $sources[$s]['clicks']++; $daily[$d]['clicks']++;
            $vis[$s][$c->Visitor] = 1; $visAll[$c->Visitor] = 1;
        }
        foreach ($vis as $s => $v) $sources[$s]['visitors'] = count($v);

        // সাইনআপ — ইউজার তালিকা সহ
        $p = ['p' => $partnerId];
        $w = $this->partnerRange($from, $to, 's.`CreatedAt`', $p);
        $users = $this->all("SELECT s.`UserId`, s.`Via`, s.`Source`, s.`CreatedAt` AS SignedUp, u.`FullName`, u.`UserName`, u.`Email`, u.`MobileNumber`, u.`Role`, u.`EmailVerified`, u.`IsActive`
            FROM `PartnerSignups` s JOIN `Users` u ON u.`Id` = s.`UserId` WHERE s.`PartnerId` = :p $w ORDER BY s.`CreatedAt` DESC", $p);
        $srcOf = [];
        foreach ($this->all('SELECT `UserId`, `Source` FROM `PartnerSignups` WHERE `PartnerId` = :p', ['p' => $partnerId]) as $x) $srcOf[(int)$x->UserId] = (string)$x->Source;

        // সব পেমেন্ট (পার্টনারের সব ইউজারের)
        $pays = [];
        foreach ($this->all("SELECT pr.`UserId`, pr.`Amount`, pr.`Status`, pr.`PackageName`, pr.`Audience`, pr.`CreatedAt`, pr.`ReviewedAt` FROM `PaymentRequests` pr
            JOIN `PartnerSignups` s ON s.`UserId` = pr.`UserId` WHERE s.`PartnerId` = :p ORDER BY pr.`Id`", ['p' => $partnerId]) as $pay) $pays[(int)$pay->UserId][] = $pay;

        foreach ($users as $u) {
            $s = (string)$u->Source; $d = substr((string)$u->SignedUp, 0, 10);
            $sources[$s] ??= $blank(); $daily[$d] ??= $blank();
            $sources[$s]['signups']++; $daily[$d]['signups']++;
            $u->Verified = !isset($u->EmailVerified) || (int)$u->EmailVerified === 1;
            if ($u->Verified) $sources[$s]['verified']++;
            $u->Orders = 0; $u->Paid = 0; $u->Pending = 0; $u->LastPackage = ''; $u->FirstBuy = null;
            foreach ($pays[(int)$u->UserId] ?? [] as $pay) {
                if ($pay->Status === 'Approved') { $u->Orders++; $u->Paid += (int)$pay->Amount; $u->LastPackage = (string)$pay->PackageName; $u->FirstBuy ??= ($pay->ReviewedAt ?: $pay->CreatedAt); }
                elseif ($pay->Status === 'Pending') $u->Pending++;
            }
        }

        // কেনা — ঐ সময়ে Approve হওয়া পেমেন্ট
        $buyers = []; $buyersBySrc = [];
        foreach ($pays as $uid => $list) {
            foreach ($list as $pay) {
                $when = (string)($pay->ReviewedAt ?: $pay->CreatedAt);
                $day = substr($when, 0, 10);
                if (($from && $day < $from) || ($to && $day > $to)) continue;
                $s = $srcOf[$uid] ?? 'other';
                $sources[$s] ??= $blank(); $daily[$day] ??= $blank();
                if ($pay->Status === 'Approved') {
                    $sources[$s]['orders']++; $sources[$s]['revenue'] += (int)$pay->Amount;
                    $daily[$day]['orders']++; $daily[$day]['revenue'] += (int)$pay->Amount;
                    $buyers[$uid] = 1; $buyersBySrc[$s][$uid] = 1;
                    $daily[$day]['buyersSet'][$uid] = 1;
                } elseif ($pay->Status === 'Pending') { $sources[$s]['pending']++; $daily[$day]['pending']++; }
            }
        }
        foreach ($buyersBySrc as $s => $b) $sources[$s]['buyers'] = count($b);
        foreach ($daily as $d => $x) { $daily[$d]['buyers'] = count($x['buyersSet'] ?? []); unset($daily[$d]['buyersSet']); }

        $tot = $blank();
        foreach ($sources as $x) foreach (['clicks', 'signups', 'verified', 'orders', 'revenue', 'pending'] as $k) $tot[$k] += $x[$k];
        $tot['visitors'] = count($visAll);
        $tot['buyers'] = count($buyers);

        // প্ল্যাটফর্ম ক্রম: বেশি ক্লিক / সাইনআপ আগে
        uasort($sources, fn($a, $b) => [$b['signups'], $b['clicks']] <=> [$a['signups'], $a['clicks']]);
        krsort($daily);
        return ['totals' => $tot, 'sources' => $sources, 'daily' => $daily, 'users' => $users];
    }
}
