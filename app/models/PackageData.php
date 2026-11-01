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
    private const TEACHER_FEATURES_V2 = "প্রোফাইলে নীল ভেরিফায়েড টিক\nFind Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন";

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
        // প্রথমবার ডিফল্ট প্যাকেজ (অ্যাডমিন সব মুছে দিলে আবার বসবে না)
        try {
            $seeded = $this->GetSiteContent(['pkg_seeded' => ''])['pkg_seeded'] ?? '';
            if ($seeded === '' && (int)$this->scalar('SELECT COUNT(*) FROM `Packages`') === 0) {
                foreach (self::defaultPackages() as $i => $p) $this->insertPackage($p + ['SortOrder' => $i + 1]);
            }
            if ($seeded === '') $this->SaveSiteContent(['pkg_seeded' => '1', 'pkg_feat_v2' => '1']);
            elseif (($this->GetSiteContent(['pkg_feat_v2' => ''])['pkg_feat_v2'] ?? '') === '') {
                // আগের ডিফল্ট সুবিধার তালিকায় নীল টিক যোগ (অ্যাডমিন নিজে বদলে থাকলে হাত দেয় না)
                $this->exec("UPDATE `Packages` SET `Features` = :n WHERE `Audience` = 'Teacher' AND `Features` = :o", ['n' => self::TEACHER_FEATURES_V2, 'o' => self::TEACHER_FEATURES_V1]);
                $this->SaveSiteContent(['pkg_feat_v2' => '1']);
            }
        } catch (Throwable $e) { error_log('[packages seed] ' . $e->getMessage()); }
    }

    private static function defaultPackages(): array
    {
        $t = self::TEACHER_FEATURES_V2;
        $s = "কোন বিষয় পড়াতে হবে\nকোন ক্লাসের শিক্ষার্থী\nকোথায় পড়াতে হবে\nকতদিন / কত ঘণ্টা পড়াতে হবে\nপারিশ্রমিক বা বাজেট\nঅন্যান্য প্রয়োজনীয় তথ্য";
        return [
            ['Audience' => 'Teacher', 'Name' => '১ মাস', 'DurationMonths' => 1, 'PostLimit' => 0, 'Price' => 100, 'RegularPrice' => 1000, 'Badge' => '90% ছাড়', 'Description' => 'শুরু করার জন্য সবচেয়ে সহজ', 'Features' => $t, 'IsFeatured' => 0],
            ['Audience' => 'Teacher', 'Name' => '৩ মাস', 'DurationMonths' => 3, 'PostLimit' => 0, 'Price' => 300, 'RegularPrice' => null, 'Badge' => '', 'Description' => 'নিয়মিত টিউশনের জন্য', 'Features' => $t, 'IsFeatured' => 0],
            ['Audience' => 'Teacher', 'Name' => '৬ মাস', 'DurationMonths' => 6, 'PostLimit' => 0, 'Price' => 500, 'RegularPrice' => null, 'Badge' => 'জনপ্রিয়', 'Description' => 'কম খরচে বেশি সময়', 'Features' => $t, 'IsFeatured' => 1],
            ['Audience' => 'Teacher', 'Name' => '১২ মাস', 'DurationMonths' => 12, 'PostLimit' => 0, 'Price' => 1000, 'RegularPrice' => null, 'Badge' => 'সেরা মূল্য', 'Description' => 'পুরো বছর নিশ্চিন্ত', 'Features' => $t, 'IsFeatured' => 0],
            ['Audience' => 'Student', 'Name' => 'Tuition Post', 'DurationMonths' => 0, 'PostLimit' => 1, 'Price' => 100, 'RegularPrice' => null, 'Badge' => '', 'Description' => '“Teacher Needed / Tutor Required” পোস্ট করে শিক্ষক খুঁজুন', 'Features' => $s, 'IsFeatured' => 1],
        ];
    }

    private function insertPackage(array $p): int
    {
        return (int)$this->insert('INSERT INTO `Packages` (`Audience`, `Name`, `DurationMonths`, `PostLimit`, `Price`, `RegularPrice`, `Badge`, `Description`, `Features`, `IsActive`, `IsFeatured`, `SortOrder`, `CreatedAt`)
            VALUES (:a, :n, :d, :l, :p, :r, :b, :ds, :f, :ac, :fe, :s, :t)', [
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
        $p->IsActive = (bool)(int)$p->IsActive; $p->IsFeatured = (bool)(int)$p->IsFeatured;
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
                `Description` = :ds, `Features` = :f, `IsActive` = :ac, `IsFeatured` = :fe, `UpdatedAt` = :t WHERE `Id` = :i', [
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
