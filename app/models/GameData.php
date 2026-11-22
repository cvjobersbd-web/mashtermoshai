<?php
/**
 * গেম (Zip / Mini Sudoku / Patches) — প্রতিটা খেলা একটা "রাউন্ড"।
 * লিডারবোর্ড: আজকের (রাত ১২টা থেকে) মোট পয়েন্ট। ব্রেইন স্কোর: শেষ ৬০ দিনের গড় পয়েন্ট অন্যদের সাথে তুলনা (পারসেন্টাইল)।
 */
trait GameData
{
    private bool $gameReady = false;

    public function EnsureGameTables(): void
    {
        if ($this->gameReady) return;
        $this->gameReady = true;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "GameRounds" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NULL, "Game" TEXT NOT NULL, "Level" TEXT NOT NULL,
                "PoolIdx" INTEGER NOT NULL DEFAULT 0, "Puzzle" TEXT NOT NULL, "Token" TEXT NOT NULL, "StartedAt" TEXT NOT NULL, "FinishedAt" TEXT NULL,
                "Seconds" INTEGER NULL, "Points" INTEGER NOT NULL DEFAULT 0, "Solved" INTEGER NOT NULL DEFAULT 0, "Counted" INTEGER NOT NULL DEFAULT 0, "PlayDay" TEXT NULL, "Hints" INTEGER NOT NULL DEFAULT 0)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Game_Day" ON "GameRounds" ("PlayDay", "Counted", "UserId")');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS "IX_Game_User" ON "GameRounds" ("UserId", "Solved")');
        } else {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS `GameRounds` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NULL, `Game` VARCHAR(10) NOT NULL, `Level` VARCHAR(8) NOT NULL,
                `PoolIdx` INT NOT NULL DEFAULT 0, `Puzzle` TEXT NOT NULL, `Token` VARCHAR(40) NOT NULL, `StartedAt` DATETIME NOT NULL, `FinishedAt` DATETIME NULL,
                `Seconds` INT NULL, `Points` INT NOT NULL DEFAULT 0, `Solved` TINYINT(1) NOT NULL DEFAULT 0, `Counted` TINYINT(1) NOT NULL DEFAULT 0, `PlayDay` DATE NULL, `Hints` INT NOT NULL DEFAULT 0,
                PRIMARY KEY (`Id`), KEY `IX_Game_Day` (`PlayDay`, `Counted`, `UserId`), KEY `IX_Game_User` (`UserId`, `Solved`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
        // হিন্টের হিসাব (পরে যোগ হওয়া কলাম — পুরোনো টেবিলে না থাকলে যোগ)
        try { $this->pdo->query('SELECT `Hints` FROM `GameRounds` LIMIT 1'); }
        catch (Throwable $e) { try { $this->pdo->exec('ALTER TABLE `GameRounds` ADD COLUMN `Hints` INT NOT NULL DEFAULT 0'); } catch (Throwable $e2) { error_log('[GameRounds Hints] ' . $e2->getMessage()); } }
    }

    public function GameNewRound(?int $userId, string $game, string $level, int $poolIdx, array $puzzle, string $token): int
    {
        $this->EnsureGameTables();
        // লগইন ছাড়া খেলা পুরোনো রাউন্ড মাঝে মাঝে পরিষ্কার
        if (random_int(1, 50) === 1) {
            try { $this->exec('DELETE FROM `GameRounds` WHERE `UserId` IS NULL AND `StartedAt` < :d', ['d' => date('Y-m-d H:i:s', time() - 2 * 86400)]); } catch (Throwable $e) { }
        }
        return $this->insert('INSERT INTO `GameRounds` (`UserId`, `Game`, `Level`, `PoolIdx`, `Puzzle`, `Token`, `StartedAt`) VALUES (:u, :g, :l, :p, :z, :t, :s)',
            ['u' => $userId, 'g' => $game, 'l' => $level, 'p' => $poolIdx, 'z' => json_encode($puzzle), 't' => $token, 's' => now()]);
    }

    public function GameGetRound(int $id): ?Obj
    {
        $this->EnsureGameTables();
        $r = $this->one('SELECT * FROM `GameRounds` WHERE `Id` = :i', ['i' => $id]);
        if ($r) { $r->Id = (int)$r->Id; $r->UserId = $r->UserId === null ? null : (int)$r->UserId; $r->Solved = (bool)$r->Solved; $r->Counted = (bool)$r->Counted; $r->Points = (int)$r->Points; $r->Hints = (int)($r->Hints ?? 0); }
        return $r;
    }

    /** হিন্ট নেওয়া হলো (শেষ না হওয়া রাউন্ডে) — মোট কয়টা হলো */
    public function GameAddHint(int $id): int
    {
        $this->exec('UPDATE `GameRounds` SET `Hints` = `Hints` + 1 WHERE `Id` = :i AND `Solved` = 0', ['i' => $id]);
        return (int)$this->scalar('SELECT `Hints` FROM `GameRounds` WHERE `Id` = :i', ['i' => $id]);
    }

    public function GameFinishRound(int $id, int $seconds, int $points, bool $counted): bool
    {
        return $this->exec('UPDATE `GameRounds` SET `FinishedAt` = :f, `Seconds` = :s, `Points` = :p, `Solved` = 1, `Counted` = :c, `PlayDay` = :d WHERE `Id` = :i AND `Solved` = 0',
            ['f' => now(), 's' => $seconds, 'p' => $points, 'c' => $counted ? 1 : 0, 'd' => date('Y-m-d'), 'i' => $id]) > 0;
    }

    /** এই ইউজার সম্প্রতি যে পাজলগুলো পেয়েছে (আবার না দেওয়ার জন্য) */
    public function GameRecentPool(int $userId, string $game, string $level, int $limit = 60): array
    {
        $this->EnsureGameTables();
        return array_map(fn($r) => (int)$r->PoolIdx, $this->all('SELECT `PoolIdx` FROM `GameRounds` WHERE `UserId` = :u AND `Game` = :g AND `Level` = :l ORDER BY `Id` DESC LIMIT ' . (int)$limit,
            ['u' => $userId, 'g' => $game, 'l' => $level]));
    }

    /** লিডারবোর্ডের দল: all | student | teacher (অ্যাডমিন কখনো না) */
    private function gameGroupSql(string $group): string
    {
        return match ($group) {
            'teacher' => " AND u.`Role` = 'Teacher'",
            'student' => " AND u.`Role` NOT IN ('Teacher', 'Admin')",
            default => " AND u.`Role` <> 'Admin'",
        };
    }

    /** আজকের লিডারবোর্ড — মোট পয়েন্ট বেশি আগে, সমান হলে যে আগে পৌঁছেছে */
    public function GameLeaderboard(string $day, string $group = 'all', int $limit = 50): array
    {
        $this->EnsureGameTables();
        $rows = $this->all('SELECT g.`UserId`, SUM(g.`Points`) AS Pts, COUNT(*) AS Solves, MAX(g.`FinishedAt`) AS LastAt, u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`Role`
            FROM `GameRounds` g JOIN `Users` u ON u.`Id` = g.`UserId`
            WHERE g.`PlayDay` = :d AND g.`Counted` = 1' . $this->gameGroupSql($group) . '
            GROUP BY g.`UserId`, u.`FullName`, u.`UserName`, u.`ProfileImageLink`, u.`Role`
            ORDER BY Pts DESC, LastAt ASC LIMIT ' . (int)$limit, ['d' => $day]);
        foreach ($rows as $i => $r) { $r->Rank = $i + 1; $r->UserId = (int)$r->UserId; $r->Pts = (int)$r->Pts; $r->Solves = (int)$r->Solves; }
        return $rows;
    }

    /** আজ ইউজারের পয়েন্ট, কয়টা সমাধান, দলে কত নম্বর, দলে মোট কতজন */
    public function GameUserDay(int $userId, string $day, string $group = 'all'): Obj
    {
        $this->EnsureGameTables();
        $me = $this->one('SELECT COALESCE(SUM(`Points`), 0) AS Pts, COUNT(*) AS Solves, MAX(`FinishedAt`) AS LastAt FROM `GameRounds` WHERE `UserId` = :u AND `PlayDay` = :d AND `Counted` = 1',
            ['u' => $userId, 'd' => $day]);
        $pts = (int)$me->Pts; $rank = null;
        $players = (int)$this->scalar('SELECT COUNT(DISTINCT g.`UserId`) FROM `GameRounds` g JOIN `Users` u ON u.`Id` = g.`UserId` WHERE g.`PlayDay` = :d AND g.`Counted` = 1' . $this->gameGroupSql($group), ['d' => $day]);
        if ((int)$me->Solves > 0) {
            $above = (int)$this->scalar('SELECT COUNT(*) FROM (SELECT g.`UserId`, SUM(g.`Points`) AS Pts, MAX(g.`FinishedAt`) AS LastAt FROM `GameRounds` g JOIN `Users` u ON u.`Id` = g.`UserId`
                WHERE g.`PlayDay` = :d AND g.`Counted` = 1' . $this->gameGroupSql($group) . ' GROUP BY g.`UserId`) t WHERE t.Pts > :p OR (t.Pts = :p2 AND t.LastAt < :la)',
                ['d' => $day, 'p' => $pts, 'p2' => $pts, 'la' => (string)$me->LastAt]);
            $rank = $above + 1;
        }
        return obj(['Pts' => $pts, 'Solves' => (int)$me->Solves, 'Rank' => $rank, 'Players' => $players]);
    }

    /** টিচার বনাম শিক্ষার্থী — আজ প্রতি জনের গড় পয়েন্ট */
    public function GameTeamVs(string $day): Obj
    {
        $this->EnsureGameTables();
        $out = ['teacher' => [0, 0], 'student' => [0, 0]];
        foreach (['teacher', 'student'] as $g) {
            $r = $this->one('SELECT COUNT(DISTINCT g.`UserId`) AS P, COALESCE(SUM(g.`Points`), 0) AS S FROM `GameRounds` g JOIN `Users` u ON u.`Id` = g.`UserId`
                WHERE g.`PlayDay` = :d AND g.`Counted` = 1' . $this->gameGroupSql($g), ['d' => $day]);
            $out[$g] = [(int)$r->P, (int)$r->P > 0 ? round((int)$r->S / (int)$r->P, 1) : 0];
        }
        return obj(['TeacherPlayers' => $out['teacher'][0], 'TeacherAvg' => $out['teacher'][1], 'StudentPlayers' => $out['student'][0], 'StudentAvg' => $out['student'][1]]);
    }

    /**
     * ব্রেইন স্কোর: শেষ ৬০ দিনে কমপক্ষে ৩টা সমাধান থাকলে — নিজের গড় পয়েন্ট বাকি সবার (কমপক্ষে ৩টা) সাথে তুলনা।
     * ফেরত: null (এখনো ৩টা হয়নি) অথবা {Pct (১-৯৯), Avg, Games, Need}
     */
    public function GameBrainScore(int $userId): ?Obj
    {
        $this->EnsureGameTables();
        $since = date('Y-m-d', time() - 60 * 86400);
        // প্রতিটা খেলার দক্ষতা ০-১: পয়েন্ট ÷ ওই লেভেলের সর্বোচ্চ পয়েন্ট, কঠিন লেভেলে একটু বেশি ওজন (সহজ ০.৮, মাঝারি ০.৯, কঠিন ১)
        $all = $this->all("SELECT `UserId`, AVG(`Points` * CASE `Level` WHEN 'easy' THEN 0.04 WHEN 'medium' THEN 0.0225 ELSE 0.0166667 END) AS A, COUNT(*) AS N
            FROM `GameRounds` WHERE `Counted` = 1 AND `PlayDay` >= :s GROUP BY `UserId`", ['s' => $since]);
        $mine = null; $others = [];
        foreach ($all as $r) {
            if ((int)$r->N < 3) { if ((int)$r->UserId === $userId) $mine = obj(['Avg' => (float)$r->A, 'Games' => (int)$r->N, 'Pct' => null, 'Need' => 3 - (int)$r->N]); continue; }
            if ((int)$r->UserId === $userId) $mine = obj(['Avg' => (float)$r->A, 'Games' => (int)$r->N, 'Pct' => null, 'Need' => 0]);
            else $others[] = (float)$r->A;
        }
        if (!$mine) return null;
        if ($mine->Need > 0) return $mine;
        // কম প্লেয়ার থাকলে একদম ১% / ৯৯% না দেখিয়ে মাঝামাঝি রাখা (প্লেয়ার বাড়লে আসল অবস্থান)
        $lower = 0; $eq = 0;
        foreach ($others as $a) { if ($a < $mine->Avg - 0.001) $lower++; elseif (abs($a - $mine->Avg) <= 0.001) $eq++; }
        $mine->Pct = max(1, min(99, (int)round(100 * ($lower + 0.5 * $eq + 1) / (count($others) + 2))));
        return $mine;
    }

    /** টানা কতদিন খেলেছে (আজ বা গতকাল পর্যন্ত) আর মোট কয়টা সমাধান */
    public function GameStreak(int $userId): Obj
    {
        $this->EnsureGameTables();
        $days = array_map(fn($r) => (string)$r->PlayDay, $this->all('SELECT DISTINCT `PlayDay` FROM `GameRounds` WHERE `UserId` = :u AND `Solved` = 1 AND `PlayDay` IS NOT NULL ORDER BY `PlayDay` DESC LIMIT 400', ['u' => $userId]));
        $total = (int)$this->scalar('SELECT COUNT(*) FROM `GameRounds` WHERE `UserId` = :u AND `Solved` = 1', ['u' => $userId]);
        $streak = 0; $set = array_flip($days);
        $d = isset($set[date('Y-m-d')]) ? time() : time() - 86400;
        while (isset($set[date('Y-m-d', $d)])) { $streak++; $d -= 86400; }
        return obj(['Streak' => $streak, 'Total' => $total]);
    }

    /** প্রতিটা গেমে ইউজারের সেরা সময় (লেভেল অনুযায়ী) */
    public function GameBestTimes(int $userId): array
    {
        $this->EnsureGameTables();
        $out = [];
        foreach ($this->all('SELECT `Game`, `Level`, MIN(`Seconds`) AS Best, COUNT(*) AS N FROM `GameRounds` WHERE `UserId` = :u AND `Solved` = 1 GROUP BY `Game`, `Level`', ['u' => $userId]) as $r)
            $out[$r->Game][$r->Level] = ['best' => (int)$r->Best, 'n' => (int)$r->N];
        return $out;
    }
}
