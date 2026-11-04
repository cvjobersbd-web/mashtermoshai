<?php
/**
 * Educational Feed (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - EduPosts: ইউজার/টিচারের পোস্ট (লেখা + ছবি/ভিডিও) — Status: Pending → অ্যাডমিন Approve করলে Approved (ফিডে দেখায়) / Rejected
 *  - EduPostLikes, EduPostComments: শুধু Approved পোস্টে
 * টেবিল না থাকলে নিজে থেকেই তৈরি হয় (SQL ইমপোর্ট লাগে না)।
 */
trait EduFeedData
{
    private bool $eduReady = false;

    public function EnsureEduFeedTables(): void
    {
        if ($this->eduReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "EduPosts" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Content" TEXT NULL,
                "Subject" TEXT NULL, "Media" TEXT NULL, "Status" TEXT NOT NULL DEFAULT \'Pending\', "RejectReason" TEXT NULL, "ReviewedBy" INTEGER NULL,
                "ReviewedAt" TEXT NULL, "CreatedAt" TEXT NOT NULL, "ApprovedAt" TEXT NULL)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Edu_Status" ON "EduPosts" ("Status", "Id")');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "EduPostLikes" ("PostId" INTEGER NOT NULL, "UserId" INTEGER NOT NULL, "CreatedAt" TEXT NOT NULL, PRIMARY KEY ("PostId", "UserId"))');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "EduPostComments" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL, "UserId" INTEGER NOT NULL,
                "Content" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `EduPosts` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Content` TEXT NULL,
                `Subject` VARCHAR(60) NULL, `Media` TEXT NULL, `Status` VARCHAR(10) NOT NULL DEFAULT 'Pending', `RejectReason` VARCHAR(300) NULL, `ReviewedBy` INT NULL,
                `ReviewedAt` DATETIME NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `ApprovedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_Edu_Status` (`Status`, `Id`), KEY `IX_Edu_User` (`UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `EduPostLikes` (`PostId` INT NOT NULL, `UserId` INT NOT NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`PostId`, `UserId`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `EduPostComments` (`Id` INT NOT NULL AUTO_INCREMENT, `PostId` INT NOT NULL, `UserId` INT NOT NULL,
                `Content` TEXT NOT NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), KEY `IX_EduC_Post` (`PostId`))
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->eduReady = true;
    }

    private function eduSelect(): string
    {
        return 'SELECT p.*, u.`FullName` AS AuthorName, u.`UserName` AS AuthorUser, u.`ProfileImageLink` AS AuthorPhoto, u.`Role` AS AuthorRole,
        (SELECT COUNT(*) FROM `EduPostLikes` l WHERE l.`PostId` = p.`Id`) AS LikeCount,
        (SELECT COUNT(*) FROM `EduPostComments` c WHERE c.`PostId` = p.`Id`) AS CommentCount
        FROM `EduPosts` p LEFT JOIN `Users` u ON u.`Id` = p.`UserId`';
    }

    /** @param Obj[] $rows */
    private function decorateEduPosts(array $rows, ?int $viewer): array
    {
        $liked = [];
        if ($viewer && $rows) {
            $ids = implode(',', array_map(fn($r) => (int)$r->Id, $rows));
            foreach ($this->all("SELECT `PostId` FROM `EduPostLikes` WHERE `UserId` = :u AND `PostId` IN ($ids)", ['u' => $viewer]) as $l) $liked[(int)$l->PostId] = true;
        }
        foreach ($rows as $r) {
            $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId;
            $r->LikeCount = (int)$r->LikeCount; $r->CommentCount = (int)$r->CommentCount;
            $r->Liked = isset($liked[$r->Id]);
            $m = json_decode((string)($r->Media ?? ''), true);
            $r->Images = is_array($m['images'] ?? null) ? array_values(array_filter($m['images'], 'is_string')) : [];
            $r->Video = is_string($m['video'] ?? null) ? $m['video'] : '';
            $r->YouTube = is_string($m['youtube'] ?? null) ? $m['youtube'] : '';
            $r->Content = (string)($r->Content ?? '');
            $r->Subject = (string)($r->Subject ?? '');
            $r->AuthorName = !is_blank($r->AuthorName ?? null) ? (string)$r->AuthorName : ((string)($r->AuthorUser ?? '') ?: 'Deleted user');
            $r->AuthorPhoto = (string)($r->AuthorPhoto ?? '');
            $r->AuthorRole = (string)($r->AuthorRole ?? '');
            $r->Initials = initials($r->AuthorName, 'U');
            $r->RejectReason = (string)($r->RejectReason ?? '');
            $r->Url = '/Feed/Post/' . $r->Id;
        }
        return $rows;
    }

    public function CreateEduPost(int $userId, string $content, string $subject, array $media, string $status = 'Pending'): int
    {
        $this->EnsureEduFeedTables();
        $now = now();
        return (int)$this->insert('INSERT INTO `EduPosts` (`UserId`, `Content`, `Subject`, `Media`, `Status`, `CreatedAt`, `ApprovedAt`) VALUES (:u, :c, :s, :m, :st, :t, :a)', [
            'u' => $userId, 'c' => $content !== '' ? $content : null, 's' => $subject !== '' ? $subject : null,
            'm' => $media ? json_encode($media, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null, 'st' => $status, 't' => $now, 'a' => $status === 'Approved' ? $now : null,
        ]);
    }

    public function GetEduPost(int $id, ?int $viewer = null): ?Obj
    {
        $this->EnsureEduFeedTables();
        $r = $this->one($this->eduSelect() . ' WHERE p.`Id` = :i', ['i' => $id]);
        return $r ? $this->decorateEduPosts([$r], $viewer)[0] : null;
    }

    /** ফিডের Approved পোস্ট (নতুনগুলো আগে); $before = এই id এর আগের গুলো */
    public function GetEduFeed(?int $viewer, ?int $before, int $limit, string $subject = '', string $q = ''): array
    {
        $this->EnsureEduFeedTables();
        $w = ["p.`Status` = 'Approved'"]; $p = [];
        if ($before) { $w[] = 'p.`Id` < :b'; $p['b'] = $before; }
        if ($subject !== '') { $w[] = 'p.`Subject` = :s'; $p['s'] = $subject; }
        if ($q !== '') { $w[] = '(p.`Content` LIKE :q OR p.`Subject` LIKE :q2 OR u.`FullName` LIKE :q3)'; $p['q'] = $p['q2'] = $p['q3'] = '%' . $q . '%'; }
        $rows = $this->all($this->eduSelect() . ' WHERE ' . implode(' AND ', $w) . ' ORDER BY p.`Id` DESC LIMIT ' . max(1, min(50, $limit)), $p);
        return $this->decorateEduPosts($rows, $viewer);
    }

    /** একজনের প্রকাশিত (Approved) পোস্ট — প্রোফাইল পেজের জন্য; মালিক নিজে দেখলে Pending/Rejected ও দেখায় */
    public function GetUserEduPosts(int $authorId, ?int $viewer, ?int $before, int $limit): array
    {
        $this->EnsureEduFeedTables();
        $p = ['u' => $authorId];
        $w = 'p.`UserId` = :u' . ($viewer === $authorId ? '' : " AND p.`Status` = 'Approved'");
        if ($before) { $w .= ' AND p.`Id` < :b'; $p['b'] = $before; }
        return $this->decorateEduPosts($this->all($this->eduSelect() . " WHERE $w ORDER BY p.`Id` DESC LIMIT " . max(1, min(50, $limit)), $p), $viewer);
    }

    /** ['posts' => প্রকাশিত পোস্ট, 'likes' => মোট লাইক] */
    public function GetUserEduStats(int $authorId): array
    {
        $this->EnsureEduFeedTables();
        $posts = (int)$this->scalar("SELECT COUNT(*) FROM `EduPosts` WHERE `UserId` = :u AND `Status` = 'Approved'", ['u' => $authorId]);
        $likes = (int)$this->scalar("SELECT COUNT(*) FROM `EduPostLikes` l INNER JOIN `EduPosts` p ON p.`Id` = l.`PostId` WHERE p.`UserId` = :u AND p.`Status` = 'Approved'", ['u' => $authorId]);
        return ['posts' => $posts, 'likes' => $likes];
    }

    /** নিজের সব পোস্ট (Pending/Approved/Rejected) */
    public function GetMyEduPosts(int $userId, ?int $before, int $limit): array
    {
        $this->EnsureEduFeedTables();
        $p = ['u' => $userId];
        $w = 'p.`UserId` = :u';
        if ($before) { $w .= ' AND p.`Id` < :b'; $p['b'] = $before; }
        return $this->decorateEduPosts($this->all($this->eduSelect() . " WHERE $w ORDER BY p.`Id` DESC LIMIT " . max(1, min(50, $limit)), $p), $userId);
    }

    /** অ্যাডমিন: status = Pending|Approved|Rejected|all */
    public function GetEduPostsAdmin(string $status, int $limit, int $offset): array
    {
        $this->EnsureEduFeedTables();
        $p = [];
        $w = '1=1';
        if (in_array($status, ['Pending', 'Approved', 'Rejected'], true)) { $w = 'p.`Status` = :s'; $p['s'] = $status; }
        // Pending গুলো পুরোনো আগে (যেটা আগে এসেছে সেটা আগে রিভিউ), বাকিগুলো নতুন আগে
        $order = $status === 'Pending' ? 'p.`Id` ASC' : 'p.`Id` DESC';
        return $this->decorateEduPosts($this->all($this->eduSelect() . " WHERE $w ORDER BY $order LIMIT " . max(1, min(100, $limit)) . ' OFFSET ' . max(0, $offset), $p), null);
    }

    /** ['Pending' => n, 'Approved' => n, 'Rejected' => n, 'all' => n] */
    public function CountEduPostsByStatus(): array
    {
        $out = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'all' => 0];
        try {
            $this->EnsureEduFeedTables();
            foreach ($this->all('SELECT `Status`, COUNT(*) AS N FROM `EduPosts` GROUP BY `Status`') as $r) {
                if (isset($out[$r->Status])) $out[$r->Status] = (int)$r->N;
                $out['all'] += (int)$r->N;
            }
        } catch (Throwable $e) { error_log('[CountEduPosts] ' . $e->getMessage()); }
        return $out;
    }

    public function SetEduPostStatus(int $id, string $status, string $reason, int $adminId): void
    {
        $this->EnsureEduFeedTables();
        $this->exec('UPDATE `EduPosts` SET `Status` = :s, `RejectReason` = :r, `ReviewedBy` = :a, `ReviewedAt` = :t,
            `ApprovedAt` = CASE WHEN :s2 = \'Approved\' THEN COALESCE(`ApprovedAt`, :t2) ELSE `ApprovedAt` END WHERE `Id` = :i', [
            's' => $status, 'r' => $status === 'Rejected' && $reason !== '' ? $reason : null, 'a' => $adminId, 't' => now(), 's2' => $status, 't2' => now(), 'i' => $id,
        ]);
    }

    /** পোস্ট + লাইক + কমেন্ট মুছে দেয় (ফাইল মোছা কন্ট্রোলারের কাজ) */
    public function DeleteEduPost(int $id): void
    {
        $this->EnsureEduFeedTables();
        $this->exec('DELETE FROM `EduPostLikes` WHERE `PostId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `EduPostComments` WHERE `PostId` = :i', ['i' => $id]);
        $this->exec('DELETE FROM `EduPosts` WHERE `Id` = :i', ['i' => $id]);
    }

    /** লাইক দেওয়া/তুলে নেওয়া → [liked, count] */
    public function ToggleEduLike(int $postId, int $userId): array
    {
        $this->EnsureEduFeedTables();
        $has = (int)$this->scalar('SELECT COUNT(*) FROM `EduPostLikes` WHERE `PostId` = :p AND `UserId` = :u', ['p' => $postId, 'u' => $userId]) > 0;
        if ($has) $this->exec('DELETE FROM `EduPostLikes` WHERE `PostId` = :p AND `UserId` = :u', ['p' => $postId, 'u' => $userId]);
        else $this->exec('INSERT INTO `EduPostLikes` (`PostId`, `UserId`, `CreatedAt`) VALUES (:p, :u, :t)', ['p' => $postId, 'u' => $userId, 't' => now()]);
        return [!$has, (int)$this->scalar('SELECT COUNT(*) FROM `EduPostLikes` WHERE `PostId` = :p', ['p' => $postId])];
    }

    public function AddEduComment(int $postId, int $userId, string $content): int
    {
        $this->EnsureEduFeedTables();
        return (int)$this->insert('INSERT INTO `EduPostComments` (`PostId`, `UserId`, `Content`, `CreatedAt`) VALUES (:p, :u, :c, :t)',
            ['p' => $postId, 'u' => $userId, 'c' => $content, 't' => now()]);
    }

    private function decorateEduComments(array $rows): array
    {
        foreach ($rows as $c) {
            $c->Id = (int)$c->Id; $c->PostId = (int)$c->PostId; $c->UserId = (int)$c->UserId;
            $c->AuthorName = !is_blank($c->AuthorName ?? null) ? (string)$c->AuthorName : ((string)($c->AuthorUser ?? '') ?: 'Deleted user');
            $c->AuthorPhoto = (string)($c->AuthorPhoto ?? '');
            $c->AuthorRole = (string)($c->AuthorRole ?? '');
            $c->Initials = initials($c->AuthorName, 'U');
        }
        return $rows;
    }

    private function eduCommentSelect(): string
    {
        return 'SELECT c.*, u.`FullName` AS AuthorName, u.`UserName` AS AuthorUser, u.`ProfileImageLink` AS AuthorPhoto, u.`Role` AS AuthorRole
        FROM `EduPostComments` c LEFT JOIN `Users` u ON u.`Id` = c.`UserId`';
    }

    /** @return Obj[] পুরোনো আগে */
    public function GetEduComments(int $postId, int $limit = 200): array
    {
        $this->EnsureEduFeedTables();
        return $this->decorateEduComments($this->all($this->eduCommentSelect() . ' WHERE c.`PostId` = :p ORDER BY c.`Id` ASC LIMIT ' . max(1, $limit), ['p' => $postId]));
    }

    public function GetEduComment(int $id): ?Obj
    {
        $this->EnsureEduFeedTables();
        $r = $this->one($this->eduCommentSelect() . ' WHERE c.`Id` = :i', ['i' => $id]);
        return $r ? $this->decorateEduComments([$r])[0] : null;
    }

    public function DeleteEduComment(int $id): void
    {
        $this->EnsureEduFeedTables();
        $this->exec('DELETE FROM `EduPostComments` WHERE `Id` = :i', ['i' => $id]);
    }

    /** Approved পোস্টে যে বিষয়গুলো আছে (বেশি পোস্ট আগে) */
    public function GetEduSubjects(int $limit = 14): array
    {
        try {
            $this->EnsureEduFeedTables();
            return array_map(fn($r) => (string)$r->Subject, $this->all("SELECT `Subject`, COUNT(*) AS N FROM `EduPosts` WHERE `Status` = 'Approved' AND `Subject` IS NOT NULL AND `Subject` <> ''
                GROUP BY `Subject` ORDER BY N DESC, `Subject` LIMIT " . max(1, $limit)));
        } catch (Throwable $e) { return []; }
    }

    /** গত $minutes মিনিটে কতগুলো পোস্ট / কমেন্ট (স্প্যাম ঠেকাতে) */
    public function CountRecentEdu(string $what, int $userId, int $minutes): int
    {
        $this->EnsureEduFeedTables();
        $table = $what === 'comment' ? 'EduPostComments' : 'EduPosts';
        $since = date('Y-m-d H:i:s', time() - $minutes * 60);
        return (int)$this->scalar("SELECT COUNT(*) FROM `$table` WHERE `UserId` = :u AND `CreatedAt` >= :s", ['u' => $userId, 's' => $since]);
    }

    /** সব অ্যাডমিনের id (নতুন পোস্ট এলে নোটিফিকেশন পাঠাতে) */
    public function GetAdminUserIds(): array
    {
        try { return array_map(fn($r) => (int)$r->Id, $this->all("SELECT `Id` FROM `Users` WHERE `Role` = 'Admin' AND `IsActive` = 1")); }
        catch (Throwable $e) { return []; }
    }
}
