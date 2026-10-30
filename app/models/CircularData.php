<?php
/**
 * Tuition Circular পেজের নতুন ডাটা (Db ক্লাসে trait হিসেবে যুক্ত)।
 *  - পোস্টে নতুন ঘর: Title, Medium (Bangla|English|Both), TuitionType (Home|Online|Both), VideoUrl, ViewCount
 *  - ফিল্টার (Location / Class / Subject / Medium / Tuition Type) + Sort (Recent / Views / Comments)
 * নতুন কলাম দরকার হলে নিজে থেকেই যোগ হয় — আলাদা SQL Import লাগে না।
 */
trait CircularData
{
    private bool $circularExtrasReady = false;

    public const CIRCULAR_MEDIUMS = ['Bangla', 'English', 'Both'];
    public const CIRCULAR_TYPES = ['Home', 'Online', 'Both'];

    public function EnsureCircularExtras(): void
    {
        if ($this->circularExtrasReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        $cols = [
            'Title' => $sqlite ? 'TEXT NULL' : 'VARCHAR(150) NULL',
            'Medium' => $sqlite ? 'TEXT NULL' : 'VARCHAR(10) NULL',
            'TuitionType' => $sqlite ? 'TEXT NULL' : 'VARCHAR(10) NULL',
            'VideoUrl' => $sqlite ? 'TEXT NULL' : 'VARCHAR(500) NULL',
            'ViewCount' => 'INT NOT NULL DEFAULT 0',
        ];
        foreach ($cols as $c => $def) {
            if (!$this->columnExists('TuitionCircularPosts', $c)) {
                try { $this->pdo->exec("ALTER TABLE `TuitionCircularPosts` ADD COLUMN `$c` $def"); } catch (Throwable $e) { error_log("[circular $c] " . $e->getMessage()); }
            }
        }
        $this->circularExtrasReady = true;
    }

    /** ফিল্টারের SQL অংশ */
    private function circularWhere(array $f, array &$p): string
    {
        $w = ['1=1'];
        $like = function (string $col, string $key, ?string $v) use (&$w, &$p) {
            $v = trim((string)$v);
            if ($v === '') return;
            $w[] = "LOWER(COALESCE(p.`$col`, '')) LIKE :$key";
            $p[$key] = '%' . mb_strtolower($v) . '%';
        };
        $like('PreferredArea', 'fl', $f['location'] ?? '');
        $like('ClassLevel', 'fc', $f['class'] ?? '');
        $like('Subjects', 'fs', $f['subject'] ?? '');
        foreach ([['Medium', 'medium', self::CIRCULAR_MEDIUMS, 'fm'], ['TuitionType', 'type', self::CIRCULAR_TYPES, 'ft']] as [$col, $key, $allowed, $pre]) {
            $sel = array_values(array_intersect((array)($f[$key] ?? []), $allowed));
            if (!$sel) continue;
            // যেমন Bangla বাছলে "Both" পোস্টও আসবে
            $vals = $sel;
            if (array_diff($sel, ['Both'])) $vals[] = 'Both';
            $vals = array_values(array_unique($vals));
            $ph = [];
            foreach ($vals as $i => $v) { $ph[] = ":{$pre}{$i}"; $p[$pre . $i] = $v; }
            $w[] = "p.`$col` IN (" . implode(',', $ph) . ')';
        }
        if (!is_blank($f['q'] ?? null)) {
            $w[] = "(LOWER(COALESCE(p.`Title`, '')) LIKE :q1 OR LOWER(p.`Content`) LIKE :q2)";
            $p['q1'] = $p['q2'] = '%' . mb_strtolower(trim((string)$f['q'])) . '%';
        }
        return implode(' AND ', $w);
    }

    /**
     * ফিল্টার করা সার্কুলার পোস্ট (কমেন্টসহ)।
     * @return array{posts: Obj[], total: int}
     */
    public function GetCircularFeed(array $f, string $sort, $viewerId, int $limit = 15): array
    {
        try {
            $this->EnsureCircularExtras();
            $p = [];
            $where = $this->circularWhere($f, $p);
            $order = match ($sort) {
                'views' => 'p.`ViewCount` DESC, p.`Id` DESC',
                'comments' => 'Cm DESC, p.`Id` DESC',
                default => 'p.`Id` DESC',
            };
            $limit = max(1, min(200, $limit));
            $total = (int)$this->scalar("SELECT COUNT(*) FROM `TuitionCircularPosts` p WHERE $where", $p);
            $rows = $this->all("SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`PreferredArea`, p.`ClassLevel`, p.`Subjects`, p.`Budget`,
                    p.`LikeCount`, p.`CreatedAt`, p.`Title`, p.`Medium`, p.`TuitionType`, p.`VideoUrl`, p.`ViewCount`,
                    u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`Role` AS AuthorRole,
                    (SELECT COUNT(*) FROM `TuitionCircularComments` c WHERE c.`PostId` = p.`Id`) AS Cm
                FROM `TuitionCircularPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id`
                WHERE $where ORDER BY $order LIMIT $limit", $p);
        } catch (Throwable $e) { error_log('[GetCircularFeed] ' . $e->getMessage()); return ['posts' => [], 'total' => 0]; }
        $posts = [];
        $liked = $this->GetLikedPostIds('circular', $viewerId);
        foreach ($rows as $r) {
            $cm = (int)$r->Cm; unset($r->Cm);
            $post = $this->mapFeedPost($r, 'Student');
            $post->CommentCount = $cm;
            $post->ViewCount = (int)($r->ViewCount ?? 0);
            $post->IsLikedByMe = isset($liked[$post->Id]);
            $post->Comments = $this->GetTuitionCircularComments($post->Id, $viewerId);
            $posts[] = $post;
        }
        return ['posts' => $posts, 'total' => $total];
    }

    /** ফিল্টারের ড্রপডাউনের জন্য পোস্টে থাকা এলাকা / ক্লাস / বিষয় */
    public function CircularFilterValues(): array
    {
        $out = ['location' => [], 'class' => [], 'subject' => []];
        try {
            $this->EnsureCircularExtras();
            foreach (['location' => 'PreferredArea', 'class' => 'ClassLevel', 'subject' => 'Subjects'] as $k => $col) {
                foreach ($this->all("SELECT `$col` AS V, COUNT(*) AS N FROM `TuitionCircularPosts` WHERE `$col` IS NOT NULL AND `$col` <> '' GROUP BY `$col` ORDER BY N DESC LIMIT 40") as $r) {
                    foreach (preg_split('/\s*,\s*/u', trim((string)$r->V)) as $v) {
                        $v = mb_substr(trim($v), 0, 40);
                        if ($v !== '' && !in_array(mb_strtolower($v), array_map('mb_strtolower', $out[$k]), true)) $out[$k][] = $v;
                    }
                }
            }
        } catch (Throwable $e) { }
        return $out;
    }

    /** পোস্ট কতবার দেখা হয়েছে (একই সেশনে একবারই গোনা হয়) */
    public function AddCircularViews(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($i) => $i > 0)));
        if (!$ids) return;
        try {
            $this->EnsureCircularExtras();
            $this->exec('UPDATE `TuitionCircularPosts` SET `ViewCount` = `ViewCount` + 1 WHERE `Id` IN (' . implode(',', $ids) . ')');
        } catch (Throwable $e) { }
    }

    /** পোস্ট মুছে ফেলা (কমেন্ট, লাইক, অ্যাপ্লিকেশন, সেভ সহ)। ফাইলগুলো কন্ট্রোলার মুছে দেয়। */
    public function DeleteCircularPost(int $postId): bool
    {
        try {
            $this->exec('DELETE FROM `TuitionCircularCommentLikes` WHERE `CommentId` IN (SELECT `Id` FROM `TuitionCircularComments` WHERE `PostId` = :p)', ['p' => $postId]);
            $this->exec('DELETE FROM `TuitionCircularComments` WHERE `PostId` = :p', ['p' => $postId]);
            $this->exec('DELETE FROM `TuitionCircularLikes` WHERE `PostId` = :p', ['p' => $postId]);
            try { $this->EnsureCircularApplicationsTable(); $this->exec('DELETE FROM `TuitionCircularApplications` WHERE `PostId` = :p', ['p' => $postId]); } catch (Throwable $e) { }
            try { $this->exec("DELETE FROM `SavedItems` WHERE `ItemType` = 'circular' AND `ItemId` = :p", ['p' => $postId]); } catch (Throwable $e) { }
            return $this->exec('DELETE FROM `TuitionCircularPosts` WHERE `Id` = :p', ['p' => $postId]) > 0;
        } catch (Throwable $e) { error_log('[DeleteCircularPost] ' . $e->getMessage()); return false; }
    }

    /** শেষ X মিনিটে কয়টা সার্কুলার পোস্ট (স্প্যাম ঠেকাতে) */
    public function CountRecentCircularPosts(int $userId, int $minutes): int
    {
        try {
            return (int)$this->scalar('SELECT COUNT(*) FROM `TuitionCircularPosts` WHERE `UserId` = :u AND `CreatedAt` >= :t',
                ['u' => $userId, 't' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
        } catch (Throwable $e) { return 0; }
    }

    // ============================================================
    // কমেন্ট এডিট / ডিলিট (Tuition Circular আর Find Tutor — দুটোতেই)
    // ============================================================
    private function feedCommentTables(string $kind): array
    {
        return $kind === 'teach'
            ? ['TeachWantedComments', 'TeachWantedCommentLikes', 'TeachWantedPosts']
            : ['TuitionCircularComments', 'TuitionCircularCommentLikes', 'TuitionCircularPosts'];
    }

    /** কমেন্ট + পোস্টের মালিক */
    public function GetFeedComment(string $kind, int $id): ?Obj
    {
        [$ct, , $pt] = $this->feedCommentTables($kind);
        try {
            $r = $this->one("SELECT c.`Id`, c.`PostId`, c.`UserId`, c.`Content`, c.`ParentId`, p.`UserId` AS PostOwnerId
                FROM `$ct` c LEFT JOIN `$pt` p ON p.`Id` = c.`PostId` WHERE c.`Id` = :id", ['id' => $id]);
            if (!$r) return null;
            foreach (['Id', 'PostId', 'UserId', 'PostOwnerId'] as $k) $r->$k = (int)$r->$k;
            return $r;
        } catch (Throwable $e) { return null; }
    }

    public function UpdateFeedComment(string $kind, int $id, string $content): bool
    {
        [$ct] = $this->feedCommentTables($kind);
        try { $this->exec("UPDATE `$ct` SET `Content` = :c WHERE `Id` = :id", ['c' => $content, 'id' => $id]); return true; }
        catch (Throwable $e) { return false; }
    }

    /** কমেন্ট আর এর নিচের সব রিপ্লাই মুছে ফেলে। @return int[] মুছে যাওয়া কমেন্টের Id */
    public function DeleteFeedCommentTree(string $kind, int $id): array
    {
        [$ct, $lt] = $this->feedCommentTables($kind);
        try {
            $c = $this->one("SELECT `PostId` FROM `$ct` WHERE `Id` = :id", ['id' => $id]);
            if (!$c) return [];
            $children = [];
            foreach ($this->all("SELECT `Id`, `ParentId` FROM `$ct` WHERE `PostId` = :p", ['p' => (int)$c->PostId]) as $r) {
                if ($r->ParentId !== null) $children[(int)$r->ParentId][] = (int)$r->Id;
            }
            $ids = [$id]; $queue = [$id];
            while ($queue) { $x = array_shift($queue); foreach ($children[$x] ?? [] as $ch) if (!in_array($ch, $ids, true)) { $ids[] = $ch; $queue[] = $ch; } }
            $in = implode(',', array_map('intval', $ids));
            $this->exec("DELETE FROM `$lt` WHERE `CommentId` IN ($in)");
            $this->exec("DELETE FROM `$ct` WHERE `Id` IN ($in)");
            return $ids;
        } catch (Throwable $e) { error_log('[DeleteFeedCommentTree] ' . $e->getMessage()); return []; }
    }
}
