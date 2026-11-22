<?php
require_once APP_ROOT . '/app/core/games.php';

/**
 * গেম — Zip, Mini Sudoku, Patches
 *  GET  /Game                      → সব গেম, আজকের পয়েন্ট, লিডারবোর্ডের ঝলক, ব্রেইন স্কোর
 *  GET  /Game/Play/{zip|sudoku|patches}?level=easy|medium|hard
 *  POST /Game/New     (game, level)            → নতুন পাজল (উত্তর ছাড়া)
 *  POST /Game/Submit  (id, token, answer JSON) → সার্ভারে যাচাই, সময় আর পয়েন্ট
 *  GET  /Game/Leaderboard?tab=all|student|teacher
 */
class GameController extends Controller
{
    /** অ্যাডমিন গেম বন্ধ রাখলে — পেইজ খোলে না (অ্যাডমিন নিজে দেখতে পারেন) */
    private function guard(bool $json = false): void
    {
        if (games_enabled() || is_admin()) return;
        if ($json) json_response(['success' => false, 'message' => 'গেম এখন বন্ধ আছে।']);
        not_found();
    }

    private function group(?string $role = null): string
    {
        $role ??= (string)current_role();
        return $role === 'Teacher' ? 'teacher' : 'student';
    }

    /** রাত ১২টা পর্যন্ত কত সময় বাকি (লিডারবোর্ড রিসেট) */
    private function resetIn(): int
    {
        return max(0, strtotime('tomorrow') - time());
    }

    public function Index(): void
    {
        $this->guard(false);
        $me = current_user_id(); $day = date('Y-m-d');
        $bag = ['Meta' => game_meta(), 'Me' => $me, 'Top' => [], 'Today' => null, 'Brain' => null, 'Streak' => null, 'Vs' => null, 'Best' => [], 'ResetIn' => $this->resetIn()];
        try {
            $bag['Top'] = $this->db->GameLeaderboard($day, 'all', 5);
            $bag['Vs'] = $this->db->GameTeamVs($day);
            if ($me !== null) {
                $bag['Today'] = $this->db->GameUserDay($me, $day, 'all');
                $bag['Brain'] = $this->db->GameBrainScore($me);
                $bag['Streak'] = $this->db->GameStreak($me);
                $bag['Best'] = $this->db->GameBestTimes($me);
            }
        } catch (Throwable $e) { error_log('[Game Index] ' . $e->getMessage()); }
        $this->view(null, null, $bag);
    }

    public function Play(): void
    {
        $this->guard(false);
        $game = strtolower((string)($GLOBALS['__route']['id'] ?? input_str('game', '')));
        if (!in_array($game, GAME_LIST, true)) redirect('/Game');
        $level = (string)input_str('level', 'easy');
        if (!in_array($level, GAME_LEVELS, true)) $level = 'easy';
        // খেলতে লগইন লাগবে
        if (current_user_id() === null) {
            tempdata_set('Error', 'গেম খেলতে আগে লগইন করুন।');
            redirect('/Account/Login?returnUrl=' . rawurlencode('/Game/Play/' . $game . '?level=' . $level));
        }
        $this->view(null, null, ['Game' => $game, 'Level' => $level, 'Meta' => game_meta($game), 'Me' => current_user_id()]);
    }

    public function New_POST(): void
    {
        $this->guard(true);
        $game = (string)input_str('game', ''); $level = (string)input_str('level', 'easy');
        if (!in_array($game, GAME_LIST, true) || !in_array($level, GAME_LEVELS, true)) json_response(['success' => false, 'message' => 'ভুল গেম।']);
        $me = current_user_id();
        if ($me === null) json_response(['success' => false, 'login' => true, 'message' => 'গেম খেলতে আগে লগইন করুন।']);
        try {
            $avoid = $this->db->GameRecentPool($me, $game, $level);
            $pick = game_pick($game, $level, $avoid);
            if (!$pick) json_response(['success' => false, 'message' => 'পাজল পাওয়া যায়নি — একটু পরে চেষ্টা করুন।']);
            [$idx, $puzzle] = $pick;
            $token = bin2hex(random_bytes(12));
            $id = $this->db->GameNewRound($me, $game, $level, $idx, $puzzle, $token);
            json_response(['success' => true, 'id' => $id, 'token' => $token, 'game' => $game, 'level' => $level, 'puzzle' => game_public($puzzle)]);
        } catch (Throwable $e) {
            error_log('[Game New] ' . $e->getMessage());
            json_response(['success' => false, 'message' => 'পাজল তৈরি করা যায়নি — আবার চেষ্টা করুন।']);
        }
    }

    public function Submit_POST(): void
    {
        $this->guard(true);
        $id = (int)input_int('id', 0); $token = (string)input_str('token', '');
        $r = $id > 0 ? $this->db->GameGetRound($id) : null;
        $me = current_user_id();
        if ($me === null) json_response(['success' => false, 'login' => true, 'message' => 'আপনি লগআউট হয়ে গেছেন — আবার লগইন করুন।']);
        if (!$r || !hash_equals((string)$r->Token, $token) || $r->UserId !== $me)
            json_response(['success' => false, 'message' => 'খেলাটা পাওয়া যায়নি — নতুন খেলা শুরু করুন।']);
        $puzzle = json_decode((string)$r->Puzzle, true) ?: [];
        if (!$r->Solved) {
            $ans = json_decode((string)input_str('answer', ''), true);
            if (!game_check($r->Game, $puzzle, $ans)) json_response(['success' => false, 'wrong' => true, 'message' => 'এখনো মেলেনি — আবার দেখুন!']);
            $seconds = max(1, time() - (int)strtotime((string)$r->StartedAt));
            $rule = game_points_rule($r->Game, $r->Level);
            $points = game_points($r->Game, $r->Level, $seconds);
            $counted = $r->UserId !== null && current_role() !== 'Admin' && $seconds >= $rule['min'];
            $this->db->GameFinishRound($r->Id, $seconds, $points, $counted);
            $r = $this->db->GameGetRound($r->Id);
        }
        $seconds = (int)$r->Seconds; $points = (int)$r->Points;
        $out = ['success' => true, 'seconds' => $seconds, 'points' => $points, 'counted' => (bool)$r->Counted, 'loggedIn' => $me !== null,
            'game' => $r->Game, 'level' => $r->Level, 'isAdmin' => current_role() === 'Admin', 'resetIn' => $this->resetIn()];
        if ($me !== null) {
            $day = date('Y-m-d'); $grp = $this->group();
            $all = $this->db->GameUserDay($me, $day, 'all');
            $mine = current_role() === 'Admin' ? null : $this->db->GameUserDay($me, $day, $grp);
            $brain = $this->db->GameBrainScore($me);
            $st = $this->db->GameStreak($me);
            $best = $this->db->GameBestTimes($me)[$r->Game][$r->Level]['best'] ?? $seconds;
            $out += ['todayPts' => $all->Pts, 'todaySolves' => $all->Solves, 'rank' => $all->Rank, 'players' => $all->Players,
                'groupRank' => $mine->Rank ?? null, 'groupPlayers' => $mine->Players ?? 0, 'group' => $grp,
                'brain' => $brain ? ['pct' => $brain->Pct, 'need' => $brain->Need, 'games' => $brain->Games] : null,
                'streak' => $st->Streak, 'best' => $best, 'newBest' => $best === $seconds];
        }
        json_response($out);
    }

    public function Leaderboard(): void
    {
        $this->guard(false);
        $tab = (string)input_str('tab', 'all');
        if (!in_array($tab, ['all', 'student', 'teacher'], true)) $tab = 'all';
        $me = current_user_id(); $day = date('Y-m-d');
        $rows = []; $mine = null; $vs = null; $yday = [];
        try {
            $rows = $this->db->GameLeaderboard($day, $tab, 100);
            $vs = $this->db->GameTeamVs($day);
            $yday = $this->db->GameLeaderboard(date('Y-m-d', time() - 86400), 'all', 3);
            if ($me !== null && current_role() !== 'Admin') $mine = $this->db->GameUserDay($me, $day, $tab);
        } catch (Throwable $e) { error_log('[Game Leaderboard] ' . $e->getMessage()); }
        $this->view(null, $rows, ['Tab' => $tab, 'Me' => $me, 'Mine' => $mine, 'Vs' => $vs, 'Yesterday' => $yday, 'ResetIn' => $this->resetIn()]);
    }
}
