<?php
/**
 * টিউশন ক্যাটাগরি (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - CategoryGroups: English Medium, Bangla Medium, Arts & Crafts ... (ক্যাটাগরি পেজের শিরোনাম)
 *  - Categories: Play, KG, Guitar ... — ছবি, ব্যানার, "What you will learn", About, Why Choose, টিউটর মেলানোর শব্দ
 * অ্যাডমিন প্যানেল (Admin → Categories) থেকে যোগ/এডিট/সাজানো যায়।
 * টেবিল না থাকলে নিজে থেকেই তৈরি হয়, আর প্রথমবার ডিফল্ট ক্যাটাগরিগুলো বসে যায়।
 */
trait CategoryData
{
    private bool $categoriesReady = false;

    public function EnsureCategoryTables(): void
    {
        if ($this->categoriesReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if ($sqlite) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "CategoryGroups" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "Name" TEXT NOT NULL,
                "SortOrder" INTEGER NOT NULL DEFAULT 0, "IsActive" INTEGER NOT NULL DEFAULT 1, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Categories" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "GroupId" INTEGER NOT NULL, "Name" TEXT NOT NULL,
                "Slug" TEXT NOT NULL, "Icon" TEXT NULL, "ImageUrl" TEXT NULL, "BannerUrl" TEXT NULL, "Highlights" TEXT NULL, "About" TEXT NULL, "WhyChoose" TEXT NULL,
                "MatchType" TEXT NOT NULL DEFAULT \'class\', "Keywords" TEXT NULL, "IsActive" INTEGER NOT NULL DEFAULT 1, "SortOrder" INTEGER NOT NULL DEFAULT 0,
                "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS "UX_Cat_Slug" ON "Categories" ("Slug")');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `CategoryGroups` (`Id` INT NOT NULL AUTO_INCREMENT, `Name` VARCHAR(80) NOT NULL,
                `SortOrder` INT NOT NULL DEFAULT 0, `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Categories` (`Id` INT NOT NULL AUTO_INCREMENT, `GroupId` INT NOT NULL, `Name` VARCHAR(80) NOT NULL,
                `Slug` VARCHAR(120) NOT NULL, `Icon` VARCHAR(60) NULL, `ImageUrl` VARCHAR(500) NULL, `BannerUrl` VARCHAR(500) NULL, `Highlights` TEXT NULL,
                `About` MEDIUMTEXT NULL, `WhyChoose` MEDIUMTEXT NULL, `MatchType` VARCHAR(10) NOT NULL DEFAULT 'class', `Keywords` VARCHAR(500) NULL,
                `IsActive` TINYINT(1) NOT NULL DEFAULT 1, `SortOrder` INT NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_Cat_Slug` (`Slug`), KEY `IX_Cat_Group` (`GroupId`, `SortOrder`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->categoriesReady = true;
        // প্রথমবার ডিফল্ট ক্যাটাগরি (অ্যাডমিন সব মুছে দিলে আবার বসবে না)
        try {
            $seeded = $this->GetSiteContent(['cat_seeded' => ''])['cat_seeded'] ?? '';
            if ($seeded === '') {
                if ((int)$this->scalar('SELECT COUNT(*) FROM `CategoryGroups`') === 0) $this->seedCategories();
                $this->SaveSiteContent(['cat_seeded' => '1']);
            }
        } catch (Throwable $e) { error_log('[categories seed] ' . $e->getMessage()); }
    }

    private function seedCategories(): void
    {
        $gi = 0;
        foreach (category_seed_data() as $groupName => $items) {
            $gid = $this->insert('INSERT INTO `CategoryGroups` (`Name`, `SortOrder`, `IsActive`, `CreatedAt`) VALUES (:n, :s, 1, :t)',
                ['n' => $groupName, 's' => ++$gi, 't' => now()]);
            foreach ($items as $i => [$name, $icon, $type, $keywords]) {
                $this->insertCategory([
                    'GroupId' => $gid, 'Name' => $name, 'Slug' => $this->uniqueCategorySlug($name, $groupName, 0), 'Icon' => $icon,
                    'ImageUrl' => '', 'BannerUrl' => '', 'Highlights' => '', 'About' => '', 'WhyChoose' => '',
                    'MatchType' => $type, 'Keywords' => implode(', ', $keywords), 'IsActive' => 1, 'SortOrder' => $i + 1,
                ]);
            }
        }
    }

    private function insertCategory(array $c): int
    {
        return (int)$this->insert('INSERT INTO `Categories` (`GroupId`, `Name`, `Slug`, `Icon`, `ImageUrl`, `BannerUrl`, `Highlights`, `About`, `WhyChoose`,
                `MatchType`, `Keywords`, `IsActive`, `SortOrder`, `CreatedAt`)
            VALUES (:g, :n, :sl, :ic, :im, :bn, :hl, :ab, :wc, :mt, :kw, :ac, :so, :t)', [
            'g' => (int)$c['GroupId'], 'n' => $c['Name'], 'sl' => $c['Slug'], 'ic' => $c['Icon'] ?: null, 'im' => $c['ImageUrl'] ?: null,
            'bn' => $c['BannerUrl'] ?: null, 'hl' => $c['Highlights'] ?: null, 'ab' => $c['About'] ?: null, 'wc' => $c['WhyChoose'] ?: null,
            'mt' => $c['MatchType'] === 'subject' ? 'subject' : 'class', 'kw' => $c['Keywords'] ?: null, 'ac' => (int)$c['IsActive'],
            'so' => (int)$c['SortOrder'], 't' => now(),
        ]);
    }

    /** নাম থেকে ইউনিক slug — একই নাম অন্য গ্রুপে থাকলে গ্রুপের নাম জুড়ে দেয় (english-medium-play) */
    public function uniqueCategorySlug(string $name, string $groupName, int $exceptId): string
    {
        $taken = fn($s) => (int)$this->scalar('SELECT COUNT(*) FROM `Categories` WHERE `Slug` = :s AND `Id` <> :i', ['s' => $s, 'i' => $exceptId]) > 0;
        $base = slugify($name) ?: 'category';
        if (!$taken($base)) return $base;
        $base = slugify($groupName . ' ' . $name) ?: $base;
        $s = $base; $n = 2;
        while ($taken($s)) $s = $base . '-' . $n++;
        return $s;
    }

    private function decorateCategory(Obj $c): Obj
    {
        $c->Id = (int)$c->Id; $c->GroupId = (int)$c->GroupId; $c->IsActive = (bool)(int)$c->IsActive; $c->SortOrder = (int)$c->SortOrder;
        foreach (['Icon', 'ImageUrl', 'BannerUrl', 'Highlights', 'About', 'WhyChoose', 'Keywords'] as $k) $c->$k = (string)($c->$k ?? '');
        $c->Icon = $c->Icon !== '' ? $c->Icon : 'fa-book-open';
        $c->KeywordList = array_values(array_filter(array_map('trim', explode(',', $c->Keywords)), fn($x) => $x !== ''));
        $c->Url = '/Categories/Details/' . rawurlencode($c->Slug);
        return $c;
    }

    /** @return Obj[] গ্রুপ, প্রত্যেকটার ভেতরে ->Categories; $activeOnly হলে শুধু চালু গুলো */
    public function GetCategoryGroups(bool $activeOnly = false): array
    {
        try {
            $this->EnsureCategoryTables();
            $groups = $this->all('SELECT * FROM `CategoryGroups`' . ($activeOnly ? ' WHERE `IsActive` = 1' : '') . ' ORDER BY `SortOrder`, `Id`');
            $cats = $this->all('SELECT * FROM `Categories`' . ($activeOnly ? ' WHERE `IsActive` = 1' : '') . ' ORDER BY `SortOrder`, `Id`');
            $by = [];
            foreach ($cats as $c) $by[(int)$c->GroupId][] = $this->decorateCategory($c);
            foreach ($groups as $g) {
                $g->Id = (int)$g->Id; $g->IsActive = (bool)(int)$g->IsActive;
                $g->Categories = $by[$g->Id] ?? [];
            }
            return $groups;
        } catch (Throwable $e) { error_log('[GetCategoryGroups] ' . $e->getMessage()); return []; }
    }

    public function GetCategoryBySlug(string $slug): ?Obj
    {
        $this->EnsureCategoryTables();
        $c = $this->one('SELECT c.*, g.`Name` AS GroupName, g.`IsActive` AS GroupActive FROM `Categories` c
            INNER JOIN `CategoryGroups` g ON g.`Id` = c.`GroupId` WHERE c.`Slug` = :s', ['s' => $slug]);
        return $c ? $this->decorateCategory($c) : null;
    }

    public function GetCategory(int $id): ?Obj
    {
        $this->EnsureCategoryTables();
        $c = $this->one('SELECT c.*, g.`Name` AS GroupName FROM `Categories` c INNER JOIN `CategoryGroups` g ON g.`Id` = c.`GroupId` WHERE c.`Id` = :i', ['i' => $id]);
        return $c ? $this->decorateCategory($c) : null;
    }

    /** @return Obj[] একই গ্রুপের অন্য চালু ক্যাটাগরি */
    public function GetRelatedCategories(Obj $c, int $limit = 12): array
    {
        $this->EnsureCategoryTables();
        $rows = $this->all('SELECT * FROM `Categories` WHERE `GroupId` = :g AND `Id` <> :i AND `IsActive` = 1 ORDER BY `SortOrder`, `Id`', ['g' => $c->GroupId, 'i' => $c->Id]);
        return array_slice(array_map(fn($r) => $this->decorateCategory($r), $rows), 0, $limit);
    }

    /** নতুন (id=0) বা পুরোনো ক্যাটাগরি সেভ; ফেরত দেয় id */
    public function SaveCategory(int $id, array $c): int
    {
        $this->EnsureCategoryTables();
        $groupName = (string)$this->scalar('SELECT `Name` FROM `CategoryGroups` WHERE `Id` = :g', ['g' => (int)$c['GroupId']]);
        $c['Slug'] = $this->uniqueCategorySlug($c['Slug'] !== '' ? $c['Slug'] : $c['Name'], $groupName, $id);
        if ($id <= 0) {
            $c['SortOrder'] = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Categories` WHERE `GroupId` = :g', ['g' => (int)$c['GroupId']]) + 1;
            return $this->insertCategory($c);
        }
        $old = $this->one('SELECT `GroupId` FROM `Categories` WHERE `Id` = :i', ['i' => $id]);
        $sort = null;
        if ($old && (int)$old->GroupId !== (int)$c['GroupId']) {
            $sort = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `Categories` WHERE `GroupId` = :g', ['g' => (int)$c['GroupId']]) + 1;
        }
        $this->exec('UPDATE `Categories` SET `GroupId` = :g, `Name` = :n, `Slug` = :sl, `Icon` = :ic, `ImageUrl` = :im, `BannerUrl` = :bn, `Highlights` = :hl,
                `About` = :ab, `WhyChoose` = :wc, `MatchType` = :mt, `Keywords` = :kw, `IsActive` = :ac, `UpdatedAt` = :t' . ($sort !== null ? ', `SortOrder` = ' . $sort : '') . ' WHERE `Id` = :i', [
            'g' => (int)$c['GroupId'], 'n' => $c['Name'], 'sl' => $c['Slug'], 'ic' => $c['Icon'] ?: null, 'im' => $c['ImageUrl'] ?: null,
            'bn' => $c['BannerUrl'] ?: null, 'hl' => $c['Highlights'] ?: null, 'ab' => $c['About'] ?: null, 'wc' => $c['WhyChoose'] ?: null,
            'mt' => $c['MatchType'] === 'subject' ? 'subject' : 'class', 'kw' => $c['Keywords'] ?: null, 'ac' => (int)$c['IsActive'], 't' => now(), 'i' => $id,
        ]);
        return $id;
    }

    public function DeleteCategory(int $id): void
    {
        $this->EnsureCategoryTables();
        $this->exec('DELETE FROM `Categories` WHERE `Id` = :i', ['i' => $id]);
    }

    public function SetCategoryActive(int $id, bool $on): void
    {
        $this->EnsureCategoryTables();
        $this->exec('UPDATE `Categories` SET `IsActive` = :v, `UpdatedAt` = :t WHERE `Id` = :i', ['v' => $on ? 1 : 0, 't' => now(), 'i' => $id]);
    }

    /** গ্রুপের ভেতরের ক্যাটাগরিগুলো নতুন ক্রমে সাজায় */
    public function ReorderCategories(int $groupId, array $ids): void
    {
        $this->EnsureCategoryTables();
        $n = 1;
        foreach ($ids as $id) $this->exec('UPDATE `Categories` SET `SortOrder` = :s WHERE `Id` = :i AND `GroupId` = :g', ['s' => $n++, 'i' => (int)$id, 'g' => $groupId]);
    }

    public function SaveCategoryGroup(int $id, string $name, bool $active): int
    {
        $this->EnsureCategoryTables();
        if ($id <= 0) {
            $s = (int)$this->scalar('SELECT COALESCE(MAX(`SortOrder`), 0) FROM `CategoryGroups`') + 1;
            return (int)$this->insert('INSERT INTO `CategoryGroups` (`Name`, `SortOrder`, `IsActive`, `CreatedAt`) VALUES (:n, :s, :a, :t)',
                ['n' => $name, 's' => $s, 'a' => $active ? 1 : 0, 't' => now()]);
        }
        $this->exec('UPDATE `CategoryGroups` SET `Name` = :n, `IsActive` = :a WHERE `Id` = :i', ['n' => $name, 'a' => $active ? 1 : 0, 'i' => $id]);
        return $id;
    }

    /** গ্রুপ আর তার ভেতরের সব ক্যাটাগরি মুছে ফেলে */
    public function DeleteCategoryGroup(int $id): void
    {
        $this->EnsureCategoryTables();
        $this->exec('DELETE FROM `Categories` WHERE `GroupId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `CategoryGroups` WHERE `Id` = :i', ['i' => $id]);
    }

    public function ReorderCategoryGroups(array $ids): void
    {
        $this->EnsureCategoryTables();
        $n = 1;
        foreach ($ids as $id) $this->exec('UPDATE `CategoryGroups` SET `SortOrder` = :s WHERE `Id` = :i', ['s' => $n++, 'i' => (int)$id]);
    }
}
