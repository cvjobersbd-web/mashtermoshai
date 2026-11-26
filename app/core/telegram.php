<?php
/**
 * অ্যাডমিনের মোবাইলে Telegram নোটিফিকেশন।
 * অ্যাডমিনদের জন্য যত নোটিফিকেশন তৈরি হয় (নতুন পেমেন্ট, Verification, Withdraw ...) সেগুলোই বটের মাধ্যমে Telegram এ যায়।
 * বটের token আর কোন চ্যাটে যাবে — Admin → Telegram পেজে (SiteContents), কোডে / GitHub এ না।
 * পাঠানো হয় পেজের উত্তর দেওয়ার পরে (ইউজারকে অপেক্ষা করতে হয় না)।
 */

const TG_EVENTS = [
    'payment' => ['💰 নতুন পেমেন্ট', 'শিক্ষার্থী / টিচার প্যাকেজ আর কোর্সের পেমেন্ট'],
    'verify'  => ['🪪 Verification / টিচার আবেদন', 'Student Verification, Teacher Verification, নতুন টিচার আবেদন'],
    'withdraw' => ['💸 Withdraw আবেদন', 'রেফারেল বোনাসের টাকা তোলার আবেদন'],
    'post'    => ['📝 নতুন পোস্ট', 'Helpdesk এ প্রশ্ন, পড়াতে চাই, Tuition Circular এর পোস্ট'],
    'other'   => ['🔔 অন্যান্য', 'Helpdesk, রিভিউ, টিউশন, Edu Feed, ডিভাইস ইত্যাদি'],
    'daily'   => ['📊 ডেইলি রিপোর্ট (রাত ১১টা)', 'প্রতিদিন রাত ১১টায় — কতজন রেজিস্ট্রেশন করেছে, কতজন শিক্ষার্থী, কতজন টিচার'],
];

// ============================================================
// ডেইলি রিপোর্ট — প্রতিদিন রাত ১১টায় (Asia/Dhaka) Telegram এ
// আলাদা cron লাগে না: রাত ১১টার পর সাইটে প্রথম যেকোনো ভিজিটে (পেজের উত্তর দেওয়ার পরে) একবারই পাঠায়।
// সময় ঠিক ১১টায় চাইলে cPanel → Cron Jobs এ রাত ১১টায় সাইটের হোম পেজ খোলার একটা cron দিন (Admin → Telegram পেজে লেখা আছে)।
// হিসাবের সময়: আগের দিন রাত ১১টা → এই দিন রাত ১১টা (কোনো রেজিস্ট্রেশন বাদ পড়ে না)।
// ============================================================
const TG_DAILY_HOUR = 23;

/** এই মুহূর্তে কোন দিনের রিপোর্ট পাওনা (Y-m-d) — রাত ১১টার আগে হলে গতকালের */
function tg_daily_due_day(): string
{
    return (int)date('G') >= TG_DAILY_HOUR ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
}

/** একটা দিনের হিসাব: [from, to, total, student, teacher, undecided, kycStudent, kycTeacher, allUsers] */
function tg_daily_stats(string $day): array
{
    $to = $day . ' ' . sprintf('%02d', TG_DAILY_HOUR) . ':00:00';
    $from = date('Y-m-d H:i:s', strtotime($to . ' -1 day'));
    $db = db();
    $r = ['from' => $from, 'to' => $to, 'total' => 0, 'student' => 0, 'teacher' => 0, 'undecided' => 0, 'kycS' => null, 'kycT' => null, 'all' => 0];
    $role = "COALESCE(`IntendedRole`, '')";
    try { $db->GetIntendedRole(0); } catch (Throwable $e) { }   // কলাম না থাকলে তৈরি
    foreach ($db->all("SELECT CASE WHEN `Role` = 'Teacher' OR $role = 'teacher' THEN 't' WHEN $role = 'student' THEN 's' ELSE 'u' END AS K, COUNT(*) AS N
        FROM `Users` WHERE `Role` <> 'Admin' AND `CreatedAt` >= :f AND `CreatedAt` < :t GROUP BY K", ['f' => $from, 't' => $to]) as $x) {
        $n = (int)$x->N; $r['total'] += $n;
        if ($x->K === 't') $r['teacher'] += $n; elseif ($x->K === 's') $r['student'] += $n; else $r['undecided'] += $n;
    }
    // এই সময়ে Verification Approve হয়ে যারা শিক্ষার্থী / টিচার হয়েছে
    try {
        foreach ($db->all("SELECT `Kind`, COUNT(*) AS N FROM `KycRequests` WHERE `Status` = 'Approved' AND `ReviewedAt` >= :f AND `ReviewedAt` < :t GROUP BY `Kind`", ['f' => $from, 't' => $to]) as $x) {
            if ($x->Kind === 'student') $r['kycS'] = (int)$x->N; elseif ($x->Kind === 'teacher') $r['kycT'] = (int)$x->N;
        }
        $r['kycS'] ??= 0; $r['kycT'] ??= 0;
    } catch (Throwable $e) { }
    try { $r['all'] = (int)$db->scalar("SELECT COUNT(*) FROM `Users` WHERE `Role` <> 'Admin'"); } catch (Throwable $e) { }
    return $r;
}

function tg_daily_text(string $day): string
{
    $s = tg_daily_stats($day);
    $bn = fn($n) => bn_num((string)$n);
    $mon = ['', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
    $ts = strtotime($day);
    $date = $bn((int)date('j', $ts)) . ' ' . $mon[(int)date('n', $ts)] . ' ' . $bn(date('Y', $ts));
    $t = "<b>📊 ডেইলি রিপোর্ট — $date</b>\n<i>(গতকাল রাত ১১টা থেকে আজ রাত ১১টা)</i>\n\n"
        . "🆕 মোট রেজিস্ট্রেশন: <b>" . $bn($s['total']) . " জন</b>\n"
        . "🎓 শিক্ষার্থী: <b>" . $bn($s['student']) . " জন</b>\n"
        . "👨‍🏫 টিচার: <b>" . $bn($s['teacher']) . " জন</b>";
    if ($s['undecided']) $t .= "\n❔ শিক্ষার্থী / টিচার বাছাই করেনি: <b>" . $bn($s['undecided']) . " জন</b>";
    if ($s['kycS'] !== null) $t .= "\n\n✅ Verification Approve হয়েছে — শিক্ষার্থী: <b>" . $bn($s['kycS']) . "</b> · টিচার: <b>" . $bn($s['kycT']) . "</b>";
    if ($s['all']) $t .= "\n\n👥 এ পর্যন্ত মোট ইউজার: <b>" . $bn($s['all']) . " জন</b>";
    $t .= "\n\n👉 <a href=\"" . htmlspecialchars(site_public_url() . '/Admin/UserOverview?nf=' . $day . '&nt=' . $day . '#newUsers', ENT_QUOTES) . "\">নতুন ইউজারদের তালিকা দেখুন</a>";
    return $t;
}

/** সব চ্যাটে পাঠানো — কতটা চ্যাটে গেছে */
function tg_send_all(string $html): int
{
    $s = tg_settings();
    $ok = 0;
    foreach ($s['chats'] as $c) { $err = null; if (tg_call($s['token'], 'sendMessage', ['chat_id' => (string)$c['id'], 'text' => $html, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true], $err, 6)) $ok++; elseif ($err) error_log('[telegram daily] ' . $err); }
    return $ok;
}

/** প্রতি রিকোয়েস্টে (index.php) — রিপোর্ট পাওনা থাকলে পেজের উত্তরের পরে একবারই পাঠায় (একসাথে দুইটা রিকোয়েস্ট এলেও দুইবার যায় না) */
function tg_daily_tick(): void
{
    try {
        $s = tg_settings();
        if (!$s['on'] || in_array('daily', $s['off'], true)) return;
        $due = tg_daily_due_day();
        $last = (string)(db()->GetSiteContent(['tg_daily_last' => ''])['tg_daily_last'] ?? '');
        if ($last === '') { db()->SaveSiteContent(['tg_daily_last' => $due]); return; }   // প্রথমবার: আজ রাত থেকে শুরু
        if ($last >= $due) return;
        // যে রিকোয়েস্ট আগে মান বদলাতে পারবে শুধু সে-ই পাঠাবে
        if (db()->exec('UPDATE `SiteContents` SET `ContentValue` = :n, `UpdatedAt` = :t WHERE `ContentKey` = \'tg_daily_last\' AND `ContentValue` = :o', ['n' => $due, 'o' => $last, 't' => now()]) !== 1) return;
        register_shutdown_function(function () use ($due) {
            if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
            try { tg_send_all(tg_daily_text($due)); } catch (Throwable $e) { error_log('[telegram daily] ' . $e->getMessage()); }
        });
    } catch (Throwable $e) { error_log('[telegram daily tick] ' . $e->getMessage()); }
}

function tg_api_base(): string
{
    $e = getenv('MM_TG_API');
    return rtrim($e !== false && $e !== '' ? $e : 'https://api.telegram.org', '/');
}

/** ['token' => '', 'chats' => [['id' => '123', 'name' => '...']], 'off' => ['other'], 'on' => bool] */
function tg_settings(bool $fresh = false): array
{
    static $s = null;
    if ($s !== null && !$fresh) return $s;
    $c = ['tg_token' => '', 'tg_chats' => '[]', 'tg_off' => '[]'];
    try { $c = db()->GetSiteContent($c); } catch (Throwable $e) { }
    $chats = json_decode((string)($c['tg_chats'] ?? '[]'), true);
    $off = json_decode((string)($c['tg_off'] ?? '[]'), true);
    $tok = trim((string)($c['tg_token'] ?? ''));
    $s = ['token' => $tok, 'chats' => is_array($chats) ? $chats : [], 'off' => is_array($off) ? $off : []];
    $s['on'] = $tok !== '' && $s['chats'];
    return $s;
}

/** Telegram Bot API কল — সফল হলে result, না হলে null আর $err */
function tg_call(string $token, string $method, array $params = [], ?string &$err = null, int $timeout = 6)
{
    $err = null;
    if (!preg_match('/^\d{5,15}:[A-Za-z0-9_-]{30,60}$/', $token)) { $err = 'Token টা ঠিক নেই।'; return null; }
    $url = tg_api_base() . '/bot' . $token . '/' . $method;
    $body = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 4]);
        $body = curl_exec($ch);
        if ($body === false) { $err = 'Telegram এ যোগাযোগ করা যায়নি: ' . curl_error($ch); $body = null; }
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => json_encode($params, JSON_UNESCAPED_UNICODE), 'timeout' => $timeout, 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) { $err = 'Telegram এ যোগাযোগ করা যায়নি।'; $body = null; }
    }
    if ($body === null) return null;
    $j = json_decode((string)$body, true);
    if (!is_array($j) || empty($j['ok'])) {
        $d = (string)($j['description'] ?? '');
        $err = str_contains($d, 'Unauthorized') ? 'Token ভুল বা বাতিল করা হয়েছে — BotFather থেকে নতুন token নিন।' : ('Telegram: ' . ($d !== '' ? $d : 'সমস্যা হয়েছে'));
        return null;
    }
    return $j['result'] ?? true;
}

/** নোটিফিকেশনের লিংক দেখে কোন ধরনের ঘটনা */
function tg_event_of(?string $link): string
{
    $l = (string)$link;
    if (preg_match('#/Admin/(Student|Teacher|Course|Live)?Payments#', $l)) return 'payment';
    if (str_contains($l, '/Admin/Kyc') || str_contains($l, '/Admin/TeacherRequests')) return 'verify';
    if (str_contains($l, '/Admin/Withdrawals') || str_contains($l, '/Admin/Referrals')) return 'withdraw';
    return 'other';
}

/** অ্যাডমিন নোটিফিকেশন Telegram এর জন্য জমা রাখা (একই নোটিফিকেশন কয়েকজন অ্যাডমিনকে গেলেও Telegram এ একবারই) */
function tg_queue(?string $title, ?string $message, ?string $link, ?string $event = null): void
{
    $s = tg_settings();
    if (!$s['on']) return;
    $ev = $event ?? tg_event_of($link);
    if (in_array($ev, $s['off'], true)) return;
    static $seen = [], $hooked = false;
    $key = md5($title . '|' . $message . '|' . $link);
    if (isset($seen[$key])) return;
    $seen[$key] = true;
    $GLOBALS['__tg_queue'][] = [$title, $message, $link];
    if (!$hooked) { $hooked = true; register_shutdown_function('tg_flush'); }
}

function tg_format(?string $title, ?string $message, ?string $link): string
{
    $h = fn($x) => htmlspecialchars((string)$x, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $t = '<b>' . $h($title ?: 'মাষ্টার মশাই') . '</b>';
    if (!is_blank($message)) $t .= "\n" . $h($message);
    if (!is_blank($link)) {
        $url = preg_match('#^https?://#', (string)$link) ? (string)$link : site_public_url() . (string)$link;
        $t .= "\n\n👉 <a href=\"" . $h($url) . '">' . (str_contains((string)$link, '/Admin') ? 'অ্যাডমিন প্যানেলে দেখুন' : 'সাইটে দেখুন') . '</a>';
    }
    return mb_substr($t, 0, 3900);
}

/** জমা থাকা নোটিফিকেশন পাঠানো — পেজের উত্তর ইউজারের কাছে চলে যাওয়ার পর */
function tg_flush(): void
{
    $q = $GLOBALS['__tg_queue'] ?? [];
    $GLOBALS['__tg_queue'] = [];
    if (!$q) return;
    if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
    $s = tg_settings();
    foreach ($q as [$title, $message, $link]) {
        $text = tg_format($title, $message, $link);
        foreach ($s['chats'] as $c) {
            $err = null;
            tg_call($s['token'], 'sendMessage', ['chat_id' => (string)$c['id'], 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true], $err, 5);
            if ($err) error_log('[telegram] ' . $err);
        }
    }
}

/** এই ইউজার অ্যাডমিন কিনা (একবারই পড়া) */
function tg_is_admin_id(int $userId): bool
{
    static $ids = null;
    if ($ids === null) { $ids = []; try { foreach (db()->GetAdmins() as $a) $ids[(int)$a->Id] = true; } catch (Throwable $e) { } }
    return isset($ids[$userId]);
}

/**
 * সাইটের নোটিফিকেশন ছাড়াই শুধু Telegram এ খবর (যেমন নতুন পোস্ট) — Admin → Telegram এ ঘটনাটা চালু থাকলে যায়।
 * $who = যে পোস্ট করেছে; অ্যাডমিন নিজে পোস্ট করলে পাঠানো হয় না।
 */
function tg_notify_post(string $title, string $message, string $link): void
{
    try {
        if (is_admin()) return;
        $msg = mb_strlen($message) > 300 ? mb_substr($message, 0, 300) . '…' : $message;
        if (($aid = notify_actor_id()) !== null) $msg .= "\n🔗 কার রেফারেন্সে: " . joined_via_text($aid);
        tg_queue($title, $msg, $link, 'post');
    } catch (Throwable $e) { error_log('[tg post] ' . $e->getMessage()); }
}

/** এখন যে ইউজার লগইন আছে তার নাম (Telegram মেসেজের জন্য) */
function tg_who(): string
{
    return (string)(session_get('UserFullName') ?: session_get('UserName') ?: 'একজন ইউজার');
}
