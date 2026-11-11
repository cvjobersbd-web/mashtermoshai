<?php
/**
 * প্যাকেজ / Subscription প্ল্যান (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - Teacher: মেয়াদভিত্তিক Subscription (১/৩/৬/১২ মাস ...)
 *  - Student: "Teacher Needed" টিউশন পোস্ট করার প্যাকেজ (প্রতি পোস্ট ...)
 * অ্যাডমিন প্যানেল থেকে ইচ্ছামতো যোগ/এডিট/সাজানো যায়। (পেমেন্ট সিস্টেম পরে যুক্ত হবে)
 * টেবিল না থাকলে নিজে থেকেই তৈরি হয়, আর প্রথমবার ডিফল্ট প্যাকেজগুলো বসে যায়।
 */
trait PackageData
{
    private bool $packagesReady = false;

    public const PACKAGE_AUDIENCES = ['Teacher', 'Student'];
    private const TEACHER_FEATURES_V1 = "প্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন";
    private const STUDENT_FEATURES_V2 = "টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন";
    private const TEACHER_FEATURES_V2 = "Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন";

    public function EnsurePackageTable(): void
    {
        if ($this->packagesReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Packages" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Audience" TEXT NOT NULL, "Name" TEXT NOT NULL,
                "DurationMonths" INTEGER NOT NULL DEFAULT 0, "PostLimit" INTEGER NOT NULL DEFAULT 0, "Price" INTEGER NOT NULL DEFAULT 0, "RegularPrice" INTEGER NULL,
                "Badge" TEXT NULL, "Description" TEXT NULL, "Features" TEXT NULL, "IsActive" INTEGER NOT NULL DEFAULT 1, "IsFeatured" INTEGER NOT NULL DEFAULT 0,
                "SortOrder" INTEGER NOT NULL DEFAULT 0, "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Packages` (`Id` INT NOT NULL AUTO_INCREMENT, `Audience` VARCHAR(10) NOT NULL, `Name` VARCHAR(80) NOT NULL,
                `DurationMonths` INT NOT NULL DEFAULT 0, `PostLimit` INT NOT NULL DEFAULT 0, `Price` INT NOT NULL DEFAULT 0, `RegularPrice` INT NULL,
                `Badge` VARCHAR(40) NULL, `Description` VARCHAR(300) NULL, `Features` TEXT NULL, `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `IsFeatured` TINYINT(1) NOT NULL DEFAULT 0,
                `SortOrder` INT NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_Pkg_Aud` (`Audience`, `SortOrder`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->packagesReady = true;
        // প্রিমিয়াম লাইভ ক্লাস ফিচার (টিচার — চ্যাট থেকে সরাসরি লাইভ ক্লাস)
        try { $this->pdo->query('SELECT `IsLive` FROM `Packages` LIMIT 1'); }
        catch (Throwable $e) { try { $this->pdo->exec('ALTER TABLE `Packages` ADD COLUMN `IsLive` ' . ($sqlite ? 'INTEGER' : 'TINYINT(1)') . ' NOT NULL DEFAULT 0'); } catch (Throwable $e2) { error_log('[IsLive col] ' . $e2->getMessage()); } }
        // প্রথমবার ডিফল্ট প্যাকেজ (অ্যাডমিন সব মুছে দিলে আবার বসবে না)
        try {
            $seeded = $this->GetSiteContent(['pkg_seeded' => ''])['pkg_seeded'] ?? '';
            if ($seeded === '' && (int)$this->scalar('SELECT COUNT(*) FROM `Packages`') === 0) {
                foreach (self::defaultPackages() as $i => $p) $this->insertPackage($p + ['SortOrder' => $i + 1]);
            }
            if ($seeded === '') $this->SaveSiteContent(['pkg_seeded' => '1', 'pkg_feat_v2' => '1', 'pkg_student_v2' => '1', 'pay_enforce_student' => '1', 'pkg_student_free' => '']);
            elseif (($this->GetSiteContent(['pkg_feat_v2' => ''])['pkg_feat_v2'] ?? '') === '') {
                // আগের ডিফল্ট সুবিধার তালিকায় নীল টিক যোগ (অ্যাডমিন নিজে বদলে থাকলে হাত দেয় না)
                $this->exec("UPDATE `Packages` SET `Features` = :n WHERE `Audience` = 'Teacher' AND `Features` = :o", ['n' => self::TEACHER_FEATURES_V2, 'o' => self::TEACHER_FEATURES_V1]);
                $this->SaveSiteContent(['pkg_feat_v2' => '1']);
            }
            if (($this->GetSiteContent(['pkg_no_tick_v1' => ''])['pkg_no_tick_v1'] ?? '') === '') {
                // নীল টিক আর প্যাকেজের সুবিধা না (Become a Tutor Approve হলে আসে) — প্যাকেজের লেখা আর সুবিধার তালিকা থেকে সরানো
                foreach ($this->all("SELECT `Id`, `Features` FROM `Packages` WHERE `Features` LIKE '%নীল%টিক%'") as $r) {
                    $lines = array_filter(preg_split('/\r?\n/', (string)$r->Features), fn($l) => !preg_match('/নীল.*টিক/u', $l));
                    $this->exec('UPDATE `Packages` SET `Features` = :f WHERE `Id` = :i', ['f' => implode("\n", $lines), 'i' => (int)$r->Id]);
                }
                $bt = $this->GetSiteContent(['pkg_benefits_teacher' => ''])['pkg_benefits_teacher'] ?? '';
                $bl = json_decode((string)$bt, true);
                if (is_array($bl)) $this->SaveSiteContent(['pkg_benefits_teacher' => json_encode(array_values(array_filter($bl, fn($b) => ($b['i'] ?? '') !== 'fa-certificate' && !preg_match('/নীল.*টিক/u', (string)($b['t'] ?? '')))), JSON_UNESCAPED_UNICODE)]);
                $this->SaveSiteContent(['pkg_no_tick_v1' => '1']);
            }
            if ($seeded !== '' && ($this->GetSiteContent(['pkg_student_v2' => ''])['pkg_student_v2'] ?? '') === '') {
                // নতুন নিয়ম: শিক্ষার্থীর প্যাকেজ মেয়াদভিত্তিক (১ মাস ৳50, ৩ মাস ৳100) — আগের পোস্ট প্যাকেজ বন্ধ (মুছে না — পুরোনো পেমেন্টের রেকর্ড থাকে)
                $this->exec("UPDATE `Packages` SET `IsActive` = 0, `IsFeatured` = 0 WHERE `Audience` = 'Student'");
                $n = (int)$this->scalar("SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Packages` WHERE `Audience` = 'Student'");
                foreach (self::studentPackagesV2() as $i => $p) $this->insertPackage($p + ['SortOrder' => $n + $i + 1]);
                // প্যাকেজ ছাড়া শিক্ষার্থী পোস্ট / টিচারের সাথে যোগাযোগ করতে পারবে না
                $this->SaveSiteContent(['pkg_student_v2' => '1', 'pay_enforce_student' => '1', 'pkg_student_free' => '']);
            }
            // নতুন: টিচারের ২টা প্রিমিয়াম লাইভ প্যাকেজ (১ মাস ৳200, ৩ মাস ৳500) — একবারই বসে
            if (($this->GetSiteContent(['pkg_live_v1' => ''])['pkg_live_v1'] ?? '') === '') {
                $n = (int)$this->scalar("SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Packages` WHERE `Audience` = 'Teacher'");
                foreach (self::livePackages() as $i => $p) $this->insertPackage($p + ['SortOrder' => $n + $i + 1]);
                $this->SaveSiteContent(['pkg_live_v1' => '1']);
            }
            // নতুন: টিচারের নরমাল ৬ মাস ৳500 (লাইভ ক্লাস ছাড়া) — একবারই; আগে থেকে থাকলে চালু করে দেয়
            if (($this->GetSiteContent(['pkg_t6_v1' => ''])['pkg_t6_v1'] ?? '') === '') {
                $ex = $this->one("SELECT `Id` FROM `Packages` WHERE `Audience` = 'Teacher' AND `DurationMonths` = 6 AND `Price` = 500 AND COALESCE(`IsLive`, 0) = 0 ORDER BY `IsActive` DESC, `Id` DESC LIMIT 1");
                if ($ex) $this->exec('UPDATE `Packages` SET `IsActive` = 1 WHERE `Id` = :i', ['i' => (int)$ex->Id]);
                else {
                    $n = (int)$this->scalar("SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Packages` WHERE `Audience` = 'Teacher' AND COALESCE(`IsLive`, 0) = 0");
                    $this->insertPackage(['Audience' => 'Teacher', 'Name' => '৬ মাস', 'DurationMonths' => 6, 'PostLimit' => 0, 'Price' => 500, 'RegularPrice' => null, 'Badge' => 'নতুন',
                        'Description' => 'কম খরচে বেশি সময় — ৬ মাস (লাইভ ক্লাস ছাড়া)', 'Features' => self::TEACHER_FEATURES_V2, 'IsFeatured' => 0, 'IsLive' => 0, 'SortOrder' => $n + 1]);
                }
                // নরমাল (লাইভ নয়) প্যাকেজের সুবিধার তালিকা থেকে লাইভ ক্লাসের লাইন সরানো — লাইভ ক্লাস শুধু প্রিমিয়ামে
                foreach ($this->all("SELECT `Id`, `Features` FROM `Packages` WHERE `Audience` = 'Teacher' AND COALESCE(`IsLive`, 0) = 0 AND `Features` LIKE :q", ['q' => '%লাইভ%']) as $r) {
                    $keep = array_filter(preg_split('/\r?\n/', (string)$r->Features), fn($l) => !preg_match('/লাইভ|Zoom|Google Meet|Join বাটন/u', $l));
                    $this->exec('UPDATE `Packages` SET `Features` = :f WHERE `Id` = :i', ['f' => implode("\n", $keep) ?: null, 'i' => (int)$r->Id]);
                }
                // সাজানো: আগে নরমাল (কম মেয়াদ আগে), তারপর Premium
                $i = 0;
                foreach ($this->all("SELECT `Id` FROM `Packages` WHERE `Audience` = 'Teacher' ORDER BY COALESCE(`IsLive`, 0), `DurationMonths`, `Price`, `Id`") as $r) $this->exec('UPDATE `Packages` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => ++$i, 'i' => (int)$r->Id]);
                $this->SaveSiteContent(['pkg_t6_v1' => '1']);
            }
            // স্পেশাল প্যাকেজ (একবারই): টিচার ৬ মাস ৳500 (লাইভ ক্লাস ছাড়া), শিক্ষার্থী ৬ মাস ৳200।
            // আগের নরমাল ৬ মাসের টিচার প্যাকেজ বন্ধ (মুছে না — পুরোনো পেমেন্টের রেকর্ড থাকে)
            if (($this->GetSiteContent(['pkg_special_v1' => ''])['pkg_special_v1'] ?? '') === '') {
                $this->exec("UPDATE `Packages` SET `IsActive` = 0, `IsFeatured` = 0 WHERE `Audience` = 'Teacher' AND `DurationMonths` = 6 AND COALESCE(`IsLive`, 0) = 0 AND `Name` NOT LIKE :sp", ['sp' => '%স্পেশাল%']);
                $this->insertPackage(['Audience' => 'Teacher', 'Name' => 'স্পেশাল ৬ মাস', 'DurationMonths' => 6, 'PostLimit' => 0, 'Price' => 500, 'RegularPrice' => null, 'Badge' => 'স্পেশাল',
                    'Description' => 'স্পেশাল অফার — ৬ মাস মাত্র ৳500 (লাইভ ক্লাস ছাড়া)', 'Features' => self::TEACHER_FEATURES_V2, 'IsFeatured' => 1, 'IsLive' => 0]);
                $this->insertPackage(['Audience' => 'Student', 'Name' => 'স্পেশাল ৬ মাস', 'DurationMonths' => 6, 'PostLimit' => 0, 'Price' => 200, 'RegularPrice' => null, 'Badge' => 'স্পেশাল',
                    'Description' => 'স্পেশাল অফার — ৬ মাস মাত্র ৳200', 'Features' => self::STUDENT_FEATURES_V2, 'IsFeatured' => 0]);
                // সাজানো: টিচার — নরমাল (কম মেয়াদ আগে) তারপর Premium; শিক্ষার্থী — কম মেয়াদ আগে
                foreach (['Teacher', 'Student'] as $aud) {
                    $i = 0;
                    foreach ($this->all("SELECT `Id` FROM `Packages` WHERE `Audience` = :a ORDER BY COALESCE(`IsLive`, 0), `DurationMonths`, `Price`, `Id`", ['a' => $aud]) as $r)
                        $this->exec('UPDATE `Packages` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => ++$i, 'i' => (int)$r->Id]);
                }
                $this->SaveSiteContent(['pkg_special_v1' => '1']);
            }
            // নতুন দাম (একবারই) — টিচার: ১ মাস ৳300, ৩ মাস ৳600, ৬ মাস ৳1200, ১ বছর ৳2000; শিক্ষার্থী: ৩ মাস ৳300, ৬ মাস ৳500, ১ বছর ৳900।
            // স্পেশাল অফার (৬ মাস টিচার ৳500 / শিক্ষার্থী ৳200) এখন আলাদা প্যাকেজ না — কুপন কোড দিলে ৬ মাসের প্যাকেজ ঐ দামে।
            // আগের নরমাল প্যাকেজ বন্ধ (মুছে না — পুরোনো পেমেন্টের রেকর্ড থাকে); Premium (লাইভ ক্লাস) যেমন আছে থাকে।
            if (($this->GetSiteContent(['pkg_v3' => ''])['pkg_v3'] ?? '') === '') {
                $plan = [
                    'Teacher' => [[1, '১ মাস', 300, ''], [3, '৩ মাস', 600, ''], [6, '৬ মাস', 1200, 'জনপ্রিয়'], [12, '১ বছর', 2000, 'সেরা মূল্য']],
                    'Student' => [[3, '৩ মাস', 300, ''], [6, '৬ মাস', 500, 'জনপ্রিয়'], [12, '১ বছর', 900, 'সেরা মূল্য']],
                ];
                foreach ($plan as $aud => $list) {
                    // আগের সুবিধার তালিকা (অ্যাডমিন বদলে থাকলে সেটাই) রাখা হয়
                    $old = $this->one("SELECT `Features` FROM `Packages` WHERE `Audience` = :a AND COALESCE(`IsLive`, 0) = 0 AND `IsActive` = 1 AND `Features` IS NOT NULL AND `Name` NOT LIKE :sp ORDER BY `Id` DESC LIMIT 1", ['a' => $aud, 'sp' => '%স্পেশাল%']);
                    $feat = $old && !is_blank($old->Features) ? (string)$old->Features : ($aud === 'Teacher' ? self::TEACHER_FEATURES_V2 : self::STUDENT_FEATURES_V2);
                    $this->exec("UPDATE `Packages` SET `IsActive` = 0, `IsFeatured` = 0 WHERE `Audience` = :a AND COALESCE(`IsLive`, 0) = 0", ['a' => $aud]);
                    foreach ($list as $i => [$m, $nm, $price, $badge]) {
                        $this->insertPackage(['Audience' => $aud, 'Name' => $nm, 'DurationMonths' => $m, 'PostLimit' => 0, 'Price' => $price, 'RegularPrice' => null, 'Badge' => $badge,
                            'Description' => $aud === 'Teacher' ? ($m >= 12 ? 'পুরো বছর নিশ্চিন্ত' : 'টিউশন পেতে ' . $nm) : 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ' . $nm,
                            'Features' => $feat, 'IsFeatured' => $m === 6 ? 1 : 0, 'IsLive' => 0, 'SortOrder' => $i + 1]);
                    }
                    $i = 0;
                    foreach ($this->all("SELECT `Id` FROM `Packages` WHERE `Audience` = :a ORDER BY `IsActive` DESC, COALESCE(`IsLive`, 0), `DurationMonths`, `Price`, `Id`", ['a' => $aud]) as $r)
                        $this->exec('UPDATE `Packages` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => ++$i, 'i' => (int)$r->Id]);
                }
                // স্পেশাল অফারের কুপন — অ্যাডমিন Admin → Coupons থেকে কোড / দাম / শেষ তারিখ বদলাতে পারবেন
                try {
                    $this->EnsureCouponTable();
                    if ((int)$this->scalar('SELECT COUNT(*) FROM `Coupons`') === 0) {
                        $this->SaveCoupon(null, ['Code' => 'TEACHER500', 'Audience' => 'Teacher', 'DurationMonths' => 6, 'Price' => 500, 'EndsAt' => null, 'MaxUses' => 0, 'IsActive' => 1, 'Note' => 'স্পেশাল অফার — টিচার ৬ মাস ৳500']);
                        $this->SaveCoupon(null, ['Code' => 'STUDENT200', 'Audience' => 'Student', 'DurationMonths' => 6, 'Price' => 200, 'EndsAt' => null, 'MaxUses' => 0, 'IsActive' => 1, 'Note' => 'স্পেশাল অফার — শিক্ষার্থী ৬ মাস ৳200']);
                    }
                } catch (Throwable $e) { error_log('[coupon seed] ' . $e->getMessage()); }
                $this->SaveSiteContent(['pkg_v3' => '1', 'pkg_special_v1' => '1', 'pkg_t6_v1' => '1']);
            }
        } catch (Throwable $e) { error_log('[packages seed] ' . $e->getMessage()); }
    }

    // ============================================================ কুপন কোড (স্পেশাল অফার)
    private bool $couponReady = false;
    public function EnsureCouponTable(): void
    {
        if ($this->couponReady) return;
        $this->couponReady = true;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Coupons" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Code" TEXT NOT NULL UNIQUE, "Audience" TEXT NOT NULL, "DurationMonths" INTEGER NOT NULL DEFAULT 0,
                "Price" INTEGER NOT NULL, "EndsAt" TEXT NULL, "MaxUses" INTEGER NOT NULL DEFAULT 0, "IsActive" INTEGER NOT NULL DEFAULT 1, "Note" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Coupons` (`Id` INT NOT NULL AUTO_INCREMENT, `Code` VARCHAR(30) NOT NULL, `Audience` VARCHAR(10) NOT NULL, `DurationMonths` INT NOT NULL DEFAULT 0,
                `Price` INT NOT NULL, `EndsAt` DATETIME NULL, `MaxUses` INT NOT NULL DEFAULT 0, `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `Note` VARCHAR(200) NULL, `CreatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_Coupon_Code` (`Code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->EnsurePaymentTables();
        try { $this->pdo->query('SELECT `CouponCode` FROM `PaymentRequests` LIMIT 1'); }
        catch (Throwable $e) { try { $this->pdo->exec('ALTER TABLE `PaymentRequests` ADD COLUMN `CouponCode` ' . ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'TEXT' : 'VARCHAR(30)') . ' NULL'); } catch (Throwable $e2) { error_log('[CouponCode col] ' . $e2->getMessage()); } }
    }

    /** @return Obj[] সব কুপন + কতবার ব্যবহার হয়েছে (বাতিল পেমেন্ট বাদে) */
    public function GetCoupons(): array
    {
        $this->EnsureCouponTable();
        $used = [];
        foreach ($this->all("SELECT UPPER(`CouponCode`) AS C, SUM(CASE WHEN `Status` = 'Approved' THEN 1 ELSE 0 END) AS A, SUM(CASE WHEN `Status` = 'Pending' THEN 1 ELSE 0 END) AS P FROM `PaymentRequests` WHERE `CouponCode` IS NOT NULL GROUP BY UPPER(`CouponCode`)") as $r) $used[(string)$r->C] = [(int)$r->A, (int)$r->P];
        $out = $this->all('SELECT * FROM `Coupons` ORDER BY `Id` DESC');
        foreach ($out as $c) { [$c->UsedApproved, $c->UsedPending] = $used[strtoupper((string)$c->Code)] ?? [0, 0]; $c->Id = (int)$c->Id; $c->IsActive = (bool)(int)$c->IsActive; }
        return $out;
    }

    public function GetCouponByCode(string $code): ?Obj
    {
        $this->EnsureCouponTable();
        return $this->one('SELECT * FROM `Coupons` WHERE UPPER(`Code`) = :c', ['c' => strtoupper(trim($code))]);
    }

    public function SaveCoupon(?int $id, array $c): int
    {
        $this->EnsureCouponTable();
        $p = ['c' => strtoupper($c['Code']), 'a' => $c['Audience'], 'd' => (int)$c['DurationMonths'], 'p' => (int)$c['Price'], 'e' => $c['EndsAt'] ?: null,
            'm' => (int)$c['MaxUses'], 'ac' => (int)$c['IsActive'], 'n' => ($c['Note'] ?? '') !== '' ? $c['Note'] : null];
        if ($id) { $this->exec('UPDATE `Coupons` SET `Code` = :c, `Audience` = :a, `DurationMonths` = :d, `Price` = :p, `EndsAt` = :e, `MaxUses` = :m, `IsActive` = :ac, `Note` = :n WHERE `Id` = :i', $p + ['i' => $id]); return $id; }
        return (int)$this->insert('INSERT INTO `Coupons` (`Code`, `Audience`, `DurationMonths`, `Price`, `EndsAt`, `MaxUses`, `IsActive`, `Note`, `CreatedAt`) VALUES (:c, :a, :d, :p, :e, :m, :ac, :n, :t)', $p + ['t' => now()]);
    }

    public function DeleteCoupon(int $id): void { $this->EnsureCouponTable(); $this->exec('DELETE FROM `Coupons` WHERE `Id` = :i', ['i' => $id]); }

    /**
     * কুপন যাচাই — এই প্যাকেজে চলে কিনা। চললে কুপনের পর দাম (int), না চললে null আর $err এ কারণ।
     * $selfId = এই ইউজার আগে একই কুপন ব্যবহার করে থাকলে আবার চলে না।
     */
    public function CheckCoupon(string $code, Obj $pkg, int $userId, ?string &$err = null, ?Obj &$coupon = null): ?int
    {
        $err = null; $coupon = null;
        $code = strtoupper(preg_replace('/\s+/', '', $code));
        if ($code === '') { $err = 'কুপন কোড লিখুন।'; return null; }
        $c = $this->GetCouponByCode($code);
        if (!$c || !(int)$c->IsActive) { $err = 'এই কুপন কোডটা সঠিক নয় বা এখন চালু নেই।'; return null; }
        if ($c->EndsAt && $c->EndsAt < now()) { $err = 'এই কুপনের মেয়াদ শেষ হয়ে গেছে।'; return null; }
        if ($c->Audience !== ($pkg->Audience ?? '') || ((int)$c->DurationMonths > 0 && (int)$c->DurationMonths !== (int)$pkg->DurationMonths) || !empty($pkg->IsLive)) {
            $err = 'এই কুপন এই প্যাকেজে চলে না — ' . ($c->Audience === 'Teacher' ? 'টিচারের' : 'শিক্ষার্থীর') . ((int)$c->DurationMonths > 0 ? ' ' . bn_num((int)$c->DurationMonths) . ' মাসের' : '') . ' প্যাকেজে চলে।';
            return null;
        }
        $this->EnsureCouponTable();
        if ((int)$c->MaxUses > 0 && (int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE UPPER(`CouponCode`) = :c AND `Status` <> 'Rejected'", ['c' => $code]) >= (int)$c->MaxUses) { $err = 'এই কুপন যত জন ব্যবহার করতে পারে, সব শেষ হয়ে গেছে।'; return null; }
        if ((int)$this->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE UPPER(`CouponCode`) = :c AND `UserId` = :u AND `Status` <> 'Rejected'", ['c' => $code, 'u' => $userId]) > 0) { $err = 'আপনি এই কুপন আগেই ব্যবহার করেছেন।'; return null; }
        $coupon = $c;
        return min((int)$pkg->Price, max(0, (int)$c->Price));
    }

    /** প্যাকেজ পেজে দেখানোর জন্য: এই প্যাকেজে কোনো চালু কুপন অফার আছে কিনা (কোড দেখায় না) — অফারের দাম বা null */
    public function CouponOfferFor(Obj $pkg): ?Obj
    {
        try {
            $this->EnsureCouponTable();
            if (!empty($pkg->IsLive)) return null;
            $c = $this->one("SELECT * FROM `Coupons` WHERE `IsActive` = 1 AND `Audience` = :a AND (`DurationMonths` = 0 OR `DurationMonths` = :d) AND (`EndsAt` IS NULL OR `EndsAt` > :n) AND `Price` < :p ORDER BY `Price` LIMIT 1",
                ['a' => $pkg->Audience, 'd' => (int)$pkg->DurationMonths, 'n' => now(), 'p' => (int)$pkg->Price]);
            return $c ?: null;
        } catch (Throwable $e) { return null; }
    }

    /** প্রিমিয়াম: সাধারণ টিচার প্যাকেজের সব সুবিধা + সাইট থেকেই লাইভ ক্লাস (বৃষ্টি / বন্যায় শিক্ষার্থীর বাসায় যেতে না পারলে) */
    private static function livePackages(): array
    {
        $f = "সাইট থেকেই সরাসরি লাইভ ক্লাস — Zoom / Google Meet লাগবে না\nবৃষ্টি, বন্যা বা যেতে না পারলে শিক্ষার্থীকে অনলাইনে পড়ান\nচ্যাট থেকে এক ক্লিকে ক্লাস শুরু — শিক্ষার্থী Join বাটন পাবে\n" . self::TEACHER_FEATURES_V2;
        return [
            ['Audience' => 'Teacher', 'Name' => 'Premium ১ মাস', 'DurationMonths' => 1, 'PostLimit' => 0, 'Price' => 200, 'RegularPrice' => null, 'Badge' => 'লাইভ ক্লাস', 'Description' => 'সব সুবিধা + সাইট থেকে লাইভ ক্লাস — ১ মাস', 'Features' => $f, 'IsFeatured' => 0, 'IsLive' => 1],
            ['Audience' => 'Teacher', 'Name' => 'Premium ৩ মাস', 'DurationMonths' => 3, 'PostLimit' => 0, 'Price' => 500, 'RegularPrice' => 600, 'Badge' => 'লাইভ ক্লাস', 'Description' => 'সব সুবিধা + সাইট থেকে লাইভ ক্লাস — ৩ মাস', 'Features' => $f, 'IsFeatured' => 1, 'IsLive' => 1],
        ];
    }

    private static function studentPackagesV2(): array
    {
        $s = self::STUDENT_FEATURES_V2;
        return [
            ['Audience' => 'Student', 'Name' => '১ মাস', 'DurationMonths' => 1, 'PostLimit' => 0, 'Price' => 50, 'RegularPrice' => null, 'Badge' => '', 'Description' => 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ১ মাস', 'Features' => $s, 'IsFeatured' => 0],
            ['Audience' => 'Student', 'Name' => '৩ মাস', 'DurationMonths' => 3, 'PostLimit' => 0, 'Price' => 100, 'RegularPrice' => 150, 'Badge' => 'সেরা মূল্য', 'Description' => 'কম খরচে বেশি সময় — ৩ মাস', 'Features' => $s, 'IsFeatured' => 1],
        ];
    }

    private static function defaultPackages(): array
    {
        $t = self::TEACHER_FEATURES_V2;
        return array_merge([
            ['Audience' => 'Teacher', 'Name' => '১ মাস', 'DurationMonths' => 1, 'PostLimit' => 0, 'Price' => 100, 'RegularPrice' => 1000, 'Badge' => '90% ছাড়', 'Description' => 'শুরু করার জন্য সবচেয়ে সহজ', 'Features' => $t, 'IsFeatured' => 0],
            ['Audience' => 'Teacher', 'Name' => '৩ মাস', 'DurationMonths' => 3, 'PostLimit' => 0, 'Price' => 300, 'RegularPrice' => null, 'Badge' => '', 'Description' => 'নিয়মিত টিউশনের জন্য', 'Features' => $t, 'IsFeatured' => 0],
            ['Audience' => 'Teacher', 'Name' => '৬ মাস', 'DurationMonths' => 6, 'PostLimit' => 0, 'Price' => 500, 'RegularPrice' => null, 'Badge' => 'জনপ্রিয়', 'Description' => 'কম খরচে বেশি সময়', 'Features' => $t, 'IsFeatured' => 1],
            ['Audience' => 'Teacher', 'Name' => '১২ মাস', 'DurationMonths' => 12, 'PostLimit' => 0, 'Price' => 1000, 'RegularPrice' => null, 'Badge' => 'সেরা মূল্য', 'Description' => 'পুরো বছর নিশ্চিন্ত', 'Features' => $t, 'IsFeatured' => 0],
        ], self::studentPackagesV2());
    }

    private function insertPackage(array $p): int
    {
        return (int)$this->insert('INSERT INTO `Packages` (`Audience`, `Name`, `DurationMonths`, `PostLimit`, `Price`, `RegularPrice`, `Badge`, `Description`, `Features`, `IsActive`, `IsFeatured`, `IsLive`, `SortOrder`, `CreatedAt`)
            VALUES (:a, :n, :d, :l, :p, :r, :b, :ds, :f, :ac, :fe, :lv, :s, :t)', [ 'lv' => (int)($p['IsLive'] ?? 0),
            'a' => $p['Audience'], 'n' => $p['Name'], 'd' => (int)$p['DurationMonths'], 'l' => (int)$p['PostLimit'], 'p' => (int)$p['Price'],
            'r' => $p['RegularPrice'] ?? null, 'b' => ($p['Badge'] ?? '') !== '' ? $p['Badge'] : null, 'ds' => ($p['Description'] ?? '') !== '' ? $p['Description'] : null,
            'f' => ($p['Features'] ?? '') !== '' ? $p['Features'] : null, 'ac' => (int)($p['IsActive'] ?? 1), 'fe' => (int)($p['IsFeatured'] ?? 0),
            's' => (int)($p['SortOrder'] ?? 0), 't' => now(),
        ]);
    }

    /** প্যাকেজের দেখানোর তথ্য যোগ করে (মেয়াদ লেখা, ছাড় %, ফিচার লিস্ট) */
    private function decoratePackage(Obj $p): Obj
    {
        $p->Id = (int)$p->Id; $p->Price = (int)$p->Price; $p->DurationMonths = (int)$p->DurationMonths; $p->PostLimit = (int)$p->PostLimit;
        $p->RegularPrice = $p->RegularPrice !== null && (int)$p->RegularPrice > (int)$p->Price ? (int)$p->RegularPrice : null;
        $p->IsActive = (bool)(int)$p->IsActive; $p->IsFeatured = (bool)(int)$p->IsFeatured; $p->IsLive = (bool)(int)($p->IsLive ?? 0);
        $p->Discount = $p->RegularPrice ? (int)round(100 - $p->Price * 100 / $p->RegularPrice) : 0;
        $p->FeatureList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)$p->Features))));
        $p->DurationLabel = package_duration_label($p);
        $p->PerMonth = $p->Audience === 'Teacher' && $p->DurationMonths > 1 ? (int)round($p->Price / $p->DurationMonths) : null;
        return $p;
    }

    /** @return Obj[] audience = Teacher|Student|null(সব), শুধু চালু প্যাকেজ চাইলে $activeOnly */
    public function GetPackages(?string $audience = null, bool $activeOnly = false): array
    {
        try {
            $this->EnsurePackageTable();
            $w = []; $args = [];
            if ($audience !== null) { $w[] = '`Audience` = :a'; $args['a'] = $audience; }
            if ($activeOnly) $w[] = '`IsActive` = 1';
            $rows = $this->all('SELECT * FROM `Packages`' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY `SortOrder`, `Id`', $args);
            return array_map(fn($r) => $this->decoratePackage($r), $rows);
        } catch (Throwable $e) { error_log('[GetPackages] ' . $e->getMessage()); return []; }
    }

    public function GetPackage(int $id): ?Obj
    {
        $this->EnsurePackageTable();
        $r = $this->one('SELECT * FROM `Packages` WHERE `Id` = :i', ['i' => $id]);
        return $r ? $this->decoratePackage($r) : null;
    }

    /** নতুন (id=0) বা পুরোনো প্যাকেজ সেভ; ফেরত দেয় id */
    public function SavePackage(int $id, array $p): int
    {
        $this->EnsurePackageTable();
        if ($id <= 0) {
            $p['SortOrder'] = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Packages` WHERE `Audience` = :a', ['a' => $p['Audience']]) + 1;
            return $this->insertPackage($p);
        }
        $this->exec('UPDATE `Packages` SET `Audience` = :a, `Name` = :n, `DurationMonths` = :d, `PostLimit` = :l, `Price` = :p, `RegularPrice` = :r, `Badge` = :b,
                `Description` = :ds, `Features` = :f, `IsActive` = :ac, `IsFeatured` = :fe, `IsLive` = :lv, `UpdatedAt` = :t WHERE `Id` = :i', [ 'lv' => (int)($p['IsLive'] ?? 0),
            'a' => $p['Audience'], 'n' => $p['Name'], 'd' => (int)$p['DurationMonths'], 'l' => (int)$p['PostLimit'], 'p' => (int)$p['Price'],
            'r' => $p['RegularPrice'], 'b' => $p['Badge'] !== '' ? $p['Badge'] : null, 'ds' => $p['Description'] !== '' ? $p['Description'] : null,
            'f' => $p['Features'] !== '' ? $p['Features'] : null, 'ac' => (int)$p['IsActive'], 'fe' => (int)$p['IsFeatured'], 't' => now(), 'i' => $id,
        ]);
        return $id;
    }

    public function DeletePackage(int $id): void
    {
        $this->EnsurePackageTable();
        $this->exec('DELETE FROM `Packages` WHERE `Id` = :i', ['i' => $id]);
    }

    public function SetPackageFlag(int $id, string $flag, bool $on): void
    {
        if (!in_array($flag, ['IsActive', 'IsFeatured'], true)) return;
        $this->EnsurePackageTable();
        $this->exec("UPDATE `Packages` SET `$flag` = :v, `UpdatedAt` = :t WHERE `Id` = :i", ['v' => $on ? 1 : 0, 't' => now(), 'i' => $id]);
    }

    /** একই দলের (Teacher/Student) প্যাকেজগুলো নতুন ক্রমে সাজায় */
    public function ReorderPackages(string $audience, array $ids): void
    {
        $this->EnsurePackageTable();
        $n = 1;
        foreach ($ids as $id) {
            $this->exec('UPDATE `Packages` SET `SortOrder` = :s WHERE `Id` = :i AND `Audience` = :a', ['s' => $n++, 'i' => (int)$id, 'a' => $audience]);
        }
    }
}
