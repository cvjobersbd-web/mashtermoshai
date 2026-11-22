<?php
/**
 * মাষ্টার মশাই গেম — Zip, Mini Sudoku, Patches
 *
 * পাজলগুলো আগে থেকে তৈরি করা "পুল" (app/data/games/*.json) থেকে আসে — প্রতিটা পাজলের উত্তর ঠিক একটাই।
 * প্রতিবার খেলার সময় পাজলটা এলোমেলোভাবে ঘোরানো / উল্টানো / (Sudoku তে) সংখ্যা বদলানো হয় —
 * তাই একই প্যাটার্ন বারবার আসে না। উত্তর যাচাই হয় নিয়ম দিয়ে (সার্ভারে)।
 *
 * পাজলের গঠন (JSON):
 *  zip:     {n, nums: {"cellIndex": number, ...}}                 উত্তর: [cellIndex, ...] (১ থেকে শেষ সংখ্যা পর্যন্ত পথ)
 *  sudoku:  {n:6, grid:[36 টা সংখ্যা, 0 = খালি]}                  উত্তর: [36 টা সংখ্যা]
 *  patches: {n, clues:[{c: cellIndex, t: 'sq'|'tall'|'wide'|'any', s: size|null}]}  উত্তর: [[r,c,h,w], ...]
 */

const GAME_LIST = ['zip', 'sudoku', 'patches'];
const GAME_LEVELS = ['easy', 'medium', 'hard'];

/** গেমের নাম / বর্ণনা / রঙ (পেইজে দেখানোর জন্য) */
function game_meta(?string $g = null): array
{
    $m = [
        'zip' => ['name' => 'Zip', 'bn' => 'জিপ', 'icon' => 'fa-route', 'color' => '#F97316', 'bg' => '#FFF1E6',
            'tag' => 'সংখ্যাগুলো ক্রমানুসারে যুক্ত করুন — প্রতিটা ঘর পার হয়ে',
            'rules' => ['১ থেকে শুরু করে একটা টানা লাইন আঁকুন — ১ → ২ → ৩ … ক্রমানুসারে শেষ সংখ্যায় যান', 'লাইন শুধু পাশের ঘরে যায় — উপর, নিচ, ডান, বাম', 'প্রতিটা ঘর ঠিক একবার পার হতে হবে — একটা ঘরও ফাঁকা থাকলে শেষ হবে না']],
        'sudoku' => ['name' => 'Mini Sudoku', 'bn' => 'মিনি সুডোকু', 'icon' => 'fa-table-cells', 'color' => '#2563EB', 'bg' => '#EAF2FF',
            'tag' => 'প্রতিটা সারি, কলাম আর বক্সে ১-৬ একবার করে',
            'rules' => ['প্রতিটা খালি ঘরে ১ থেকে ৬ এর একটা সংখ্যা বসান', 'প্রতিটা সারি, কলাম আর মোটা দাগের বক্সে প্রতিটা সংখ্যা ঠিক একবার', 'ঘরে চাপ দিয়ে নিচের সংখ্যায় চাপুন (বা কিবোর্ডে টাইপ করুন)']],
        'patches' => ['name' => 'Patches', 'bn' => 'প্যাচেস', 'icon' => 'fa-shapes', 'color' => '#0D9488', 'bg' => '#E6FAF7',
            'tag' => 'প্রতিটা চিহ্ন ঘিরে আয়ত আঁকুন, পুরো গ্রিড ভরুন',
            'rules' => ['প্রতিটা রঙিন চিহ্ন ঘিরে একটা আয়ত / বর্গ আঁকুন — টেনে (ড্র্যাগ করে) আঁকুন', 'চিহ্নের আকৃতি মানতে হবে: বর্গ, লম্বা আয়ত, চওড়া আয়ত, বা যেকোনো', 'সংখ্যা থাকলে আকৃতিটা ঠিক ততগুলো ঘরের — আর পুরো গ্রিড ভরতে হবে']],
    ];
    return $g === null ? $m : ($m[$g] ?? []);
}

/** লেভেল অনুযায়ী বেস পয়েন্ট আর "দ্রুত" সময় (সেকেন্ড) — এর চেয়ে দ্রুত হলে পুরো বোনাস */
function game_points_rule(string $game, string $level): array
{
    $base = ['easy' => 10, 'medium' => 20, 'hard' => 30][$level] ?? 10;
    $par = [
        'zip' => ['easy' => 45, 'medium' => 90, 'hard' => 150],
        'sudoku' => ['easy' => 120, 'medium' => 200, 'hard' => 300],
        'patches' => ['easy' => 60, 'medium' => 110, 'hard' => 180],
    ][$game][$level] ?? 120;
    $min = ['zip' => 4, 'sudoku' => 8, 'patches' => 4][$game] ?? 4;   // এর চেয়ে কম সময় মানুষের পক্ষে সম্ভব না → গণ্য হবে না
    return ['base' => $base, 'par' => $par, 'min' => $min];
}

/** পয়েন্ট = বেস + দ্রুততার বোনাস (সর্বোচ্চ বেসের সমান)। par এর ৩ গুণ সময় পেরোলে বোনাস ০ */
function game_points(string $game, string $level, int $seconds): int
{
    $r = game_points_rule($game, $level);
    $b = $r['base'];
    if ($seconds <= $r['par']) return $b * 2;
    $over = ($seconds - $r['par']) / ($r['par'] * 2);
    return $b + (int)round($b * max(0, 1 - $over));
}

// ============================================================ পুল থেকে পাজল + এলোমেলো রূপান্তর

function game_pool(string $game, string $level): array
{
    static $cache = [];
    $k = "$game/$level";
    if (!isset($cache[$k])) {
        $f = dirname(__DIR__) . "/data/games/{$game}_{$level}.json";
        $d = is_file($f) ? json_decode((string)file_get_contents($f), true) : null;
        $cache[$k] = is_array($d) ? $d : [];
    }
    return $cache[$k];
}

/** পাজল থেকে উত্তর সরিয়ে ফেলা — ব্রাউজারে পাঠানোর আগে */
function game_public(array $p): array
{
    unset($p['sol']);
    return $p;
}

/** পুল থেকে একটা পাজল (আগের $avoid তালিকার গুলো বাদ দিয়ে) + এলোমেলো রূপান্তর → [poolIndex, puzzle] */
function game_pick(string $game, string $level, array $avoid = []): ?array
{
    $pool = game_pool($game, $level);
    if (!$pool) return null;
    $ids = array_values(array_diff(array_keys($pool), $avoid));
    if (!$ids) $ids = array_keys($pool);
    $i = $ids[random_int(0, count($ids) - 1)];
    return [$i, game_transform($game, $pool[$i])];
}

/** n×n গ্রিডের ৮টা প্রতিসাম্য: সেল ইনডেক্স → নতুন ইনডেক্স, আর ট্রান্সপোজ হয়েছে কিনা */
function game_sym_map(int $n, int $t): array
{
    $map = [];
    for ($r = 0; $r < $n; $r++) for ($c = 0; $c < $n; $c++) {
        [$rr, $cc] = match ($t) {
            0 => [$r, $c], 1 => [$r, $n - 1 - $c], 2 => [$n - 1 - $r, $c], 3 => [$n - 1 - $r, $n - 1 - $c],
            4 => [$c, $r], 5 => [$c, $n - 1 - $r], 6 => [$n - 1 - $c, $r], default => [$n - 1 - $c, $n - 1 - $r],
        };
        $map[$r * $n + $c] = $rr * $n + $cc;
    }
    return [$map, $t >= 4];
}

function game_transform(string $game, array $p): array
{
    if ($game === 'zip') {
        $n = (int)$p['n'];
        [$map] = game_sym_map($n, random_int(0, 7));
        $nums = [];
        foreach ($p['nums'] as $cell => $num) $nums[$map[(int)$cell]] = (int)$num;
        $sol = array_map(fn($c) => $map[(int)$c], $p['sol'] ?? []);
        if (random_int(0, 1)) {   // দিক উল্টানো: শেষ সংখ্যা → ১
            $mx = max($nums);
            foreach ($nums as $c => $v) $nums[$c] = $mx + 1 - $v;
            $sol = array_reverse($sol);
        }
        ksort($nums);
        return ['n' => $n, 'nums' => (object)$nums, 'sol' => $sol];
    }
    if ($game === 'patches') {
        $n = (int)$p['n'];
        [$map, $tr] = game_sym_map($n, random_int(0, 7));
        $clues = [];
        foreach ($p['clues'] as $cl) {
            $t = $cl['t'];
            if ($tr && $t === 'tall') $t = 'wide'; elseif ($tr && $t === 'wide') $t = 'tall';
            $clues[] = ['c' => $map[(int)$cl['c']], 't' => $t, 's' => $cl['s'] ?? null];
        }
        usort($clues, fn($a, $b) => $a['c'] <=> $b['c']);
        $sol = [];
        foreach ($p['sol'] ?? [] as [$r, $c, $h, $w]) {   // আয়তের দুই কোণা রূপান্তর → নতুন আয়ত
            $a = $map[$r * $n + $c]; $b = $map[($r + $h - 1) * $n + $c + $w - 1];
            $r1 = min(intdiv($a, $n), intdiv($b, $n)); $r2 = max(intdiv($a, $n), intdiv($b, $n));
            $c1 = min($a % $n, $b % $n); $c2 = max($a % $n, $b % $n);
            $sol[] = [$r1, $c1, $r2 - $r1 + 1, $c2 - $c1 + 1];
        }
        return ['n' => $n, 'clues' => $clues, 'sol' => $sol];
    }
    // sudoku 6×6 (বক্স ২ সারি × ৩ কলাম): সংখ্যা বদল, ব্যান্ড / স্ট্যাক / সারি / কলাম অদলবদল, উল্টানো
    $g = array_map('intval', $p['grid']);
    $digits = range(1, 6); shuffle($digits);
    $rows = []; $bands = [0, 1, 2]; shuffle($bands);
    foreach ($bands as $b) { $rr = [0, 1]; shuffle($rr); foreach ($rr as $x) $rows[] = $b * 2 + $x; }
    $cols = []; $stacks = [0, 1]; shuffle($stacks);
    foreach ($stacks as $s) { $cc = [0, 1, 2]; shuffle($cc); foreach ($cc as $x) $cols[] = $s * 3 + $x; }
    if (random_int(0, 1)) $rows = array_reverse($rows);
    if (random_int(0, 1)) $cols = array_reverse($cols);
    $sg = array_map('intval', $p['sol'] ?? []);
    $out = []; $sol = [];
    foreach ($rows as $r) foreach ($cols as $c) {
        $v = $g[$r * 6 + $c]; $out[] = $v ? $digits[$v - 1] : 0;
        if ($sg) $sol[] = $digits[$sg[$r * 6 + $c] - 1];
    }
    return ['n' => 6, 'grid' => $out, 'sol' => $sol];
}

// ============================================================ উত্তর যাচাই (নিয়ম দিয়ে)

/** Zip: পথ প্রতিটা ঘর একবার, পাশাপাশি, ১ দিয়ে শুরু, সংখ্যাগুলো ক্রমে, শেষ সংখ্যায় শেষ */
function game_check_zip(array $p, $ans): bool
{
    $n = (int)$p['n']; $L = $n * $n;
    if (!is_array($ans) || count($ans) !== $L) return false;
    $nums = [];
    foreach ((array)$p['nums'] as $c => $v) $nums[(int)$c] = (int)$v;
    $seen = []; $next = 1; $max = max($nums);
    foreach (array_values($ans) as $i => $c) {
        if (!is_int($c) && !(is_string($c) && ctype_digit($c))) return false;
        $c = (int)$c;
        if ($c < 0 || $c >= $L || isset($seen[$c])) return false;
        $seen[$c] = true;
        if ($i > 0) {
            $pr = (int)$ans[$i - 1];
            if (abs(intdiv($pr, $n) - intdiv($c, $n)) + abs($pr % $n - $c % $n) !== 1) return false;
        }
        if (isset($nums[$c])) { if ($nums[$c] !== $next) return false; $next++; }
        elseif ($i === 0) return false;
    }
    return $next === $max + 1 && ($nums[(int)$ans[$L - 1]] ?? 0) === $max;
}

/** Sudoku: দেওয়া সংখ্যা ঠিক রেখে প্রতিটা সারি / কলাম / বক্সে ১-৬ */
function game_check_sudoku(array $p, $ans): bool
{
    if (!is_array($ans) || count($ans) !== 36) return false;
    $g = array_map('intval', array_values($ans));
    foreach ($p['grid'] as $i => $v) if ((int)$v && (int)$v !== $g[$i]) return false;
    foreach ($g as $v) if ($v < 1 || $v > 6) return false;
    for ($k = 0; $k < 6; $k++) {
        $row = $col = $box = [];
        for ($j = 0; $j < 6; $j++) {
            $row[] = $g[$k * 6 + $j]; $col[] = $g[$j * 6 + $k];
            $br = intdiv($k, 2) * 2 + intdiv($j, 3); $bc = ($k % 2) * 3 + $j % 3; $box[] = $g[$br * 6 + $bc];
        }
        foreach ([$row, $col, $box] as $set) if (count(array_unique($set)) !== 6) return false;
    }
    return true;
}

/** Patches: আয়তগুলো পুরো গ্রিড ঢাকে, একটার উপর আরেকটা না, প্রতিটায় ঠিক একটা চিহ্ন আর চিহ্নের শর্ত মানে */
function game_check_patches(array $p, $ans): bool
{
    $n = (int)$p['n'];
    if (!is_array($ans) || !$ans || count($ans) > $n * $n) return false;
    $clueAt = [];
    foreach ($p['clues'] as $cl) $clueAt[(int)$cl['c']] = $cl;
    $cov = array_fill(0, $n * $n, false); $used = 0;
    foreach ($ans as $rect) {
        if (!is_array($rect) || count($rect) !== 4) return false;
        [$r, $c, $h, $w] = array_map('intval', array_values($rect));
        if ($h < 1 || $w < 1 || $r < 0 || $c < 0 || $r + $h > $n || $c + $w > $n) return false;
        $inside = [];
        for ($i = $r; $i < $r + $h; $i++) for ($j = $c; $j < $c + $w; $j++) {
            $k = $i * $n + $j;
            if ($cov[$k]) return false;
            $cov[$k] = true;
            if (isset($clueAt[$k])) $inside[] = $clueAt[$k];
        }
        if (count($inside) !== 1 || !game_patch_fits($inside[0], $h, $w)) return false;
        $used++;
    }
    return !in_array(false, $cov, true) && $used === count($clueAt);
}

function game_patch_fits(array $cl, int $h, int $w): bool
{
    $t = $cl['t'] ?? 'any';
    if ($t === 'sq' && $h !== $w) return false;
    if ($t === 'tall' && $h <= $w) return false;
    if ($t === 'wide' && $w <= $h) return false;
    if (!empty($cl['s']) && (int)$cl['s'] !== $h * $w) return false;
    return true;
}

function game_check(string $game, array $p, $ans): bool
{
    return match ($game) {
        'zip' => game_check_zip($p, $ans),
        'sudoku' => game_check_sudoku($p, $ans),
        'patches' => game_check_patches($p, $ans),
        default => false,
    };
}

// ============================================================ হিন্ট
/**
 * এখনকার অবস্থা ($state) দেখে একটা হিন্ট — উত্তর থেকে পরের এক ধাপ (ভুল থাকলে আগে সেটা ঠিক করে)।
 *  zip:     state = পথ [cell...]          → ['path' => নতুন পথ, 'fixed' => ভুল কেটে দেওয়া হয়েছে কিনা]
 *  sudoku:  state = ৩৬টা সংখ্যা            → ['cell' => i, 'value' => v, 'fixed' => ভুল সংখ্যা ছিল কিনা]
 *  patches: state = [[r,c,h,w], ...]      → ['remove' => rect] অথবা ['add' => rect]
 */
function game_hint(string $game, array $p, $state): ?array
{
    $sol = $p['sol'] ?? null;
    if (!$sol) return null;
    if ($game === 'zip') {
        $path = is_array($state) ? array_values(array_map('intval', $state)) : [];
        $k = 0;
        while ($k < count($path) && $k < count($sol) && $path[$k] === (int)$sol[$k]) $k++;
        if ($k >= count($sol)) return null;
        $add = $k === 0 ? 3 : 2;   // পরের কয়েকটা ঘর
        return ['path' => array_map('intval', array_slice($sol, 0, min(count($sol), $k + $add))), 'fixed' => $k < count($path)];
    }
    if ($game === 'sudoku') {
        $g = is_array($state) && count($state) === 36 ? array_map('intval', array_values($state)) : array_map('intval', $p['grid']);
        foreach ($g as $i => $v) if ($v && !(int)$p['grid'][$i] && $v !== (int)$sol[$i]) return ['cell' => $i, 'value' => (int)$sol[$i], 'fixed' => true];
        // খালি ঘরগুলোর মধ্যে যেটার সম্ভাব্য সংখ্যা সবচেয়ে কম — যুক্তি দিয়ে বের করার মতো ঘর
        $best = -1; $bc = 9;
        foreach ($g as $i => $v) {
            if ($v) continue;
            $r = intdiv($i, 6); $c = $i % 6; $used = [];
            for ($k = 0; $k < 6; $k++) { $used[$g[$r * 6 + $k]] = 1; $used[$g[$k * 6 + $c]] = 1; }
            $br = intdiv($r, 2) * 2; $bcl = intdiv($c, 3) * 3;
            for ($a = 0; $a < 2; $a++) for ($b = 0; $b < 3; $b++) $used[$g[($br + $a) * 6 + $bcl + $b]] = 1;
            $cnt = 6 - count(array_filter(array_keys($used)));
            if ($cnt < $bc) { $bc = $cnt; $best = $i; }
        }
        return $best < 0 ? null : ['cell' => $best, 'value' => (int)$sol[$best], 'fixed' => false];
    }
    // patches
    $have = [];
    foreach (is_array($state) ? $state : [] as $r) if (is_array($r) && count($r) === 4) $have[] = array_map('intval', array_values($r));
    $solSet = array_map(fn($r) => implode(',', array_map('intval', $r)), $sol);
    foreach ($have as $r) if (!in_array(implode(',', $r), $solSet, true)) return ['remove' => $r];
    $haveSet = array_map(fn($r) => implode(',', $r), $have);
    $miss = array_values(array_filter($sol, fn($r) => !in_array(implode(',', array_map('intval', $r)), $haveSet, true)));
    if (!$miss) return null;
    usort($miss, fn($a, $b) => $a[2] * $a[3] <=> $b[2] * $b[3]);
    return ['add' => array_map('intval', $miss[0])];
}

/** হিন্ট নিলে পয়েন্ট কমে — প্রতিটা হিন্টে ২০%, সর্বনিম্ন ১০% */
function game_hint_penalty(int $points, int $hints): int
{
    if ($hints <= 0) return $points;
    return max(1, (int)round($points * max(0.1, 1 - 0.2 * $hints)));
}
