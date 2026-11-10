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
    'other'   => ['🔔 অন্যান্য', 'Helpdesk, রিভিউ, টিউশন, Edu Feed, ডিভাইস ইত্যাদি'],
];

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
    if (preg_match('#/Admin/(Student|Teacher|Course)?Payments#', $l)) return 'payment';
    if (str_contains($l, '/Admin/Kyc') || str_contains($l, '/Admin/TeacherRequests')) return 'verify';
    if (str_contains($l, '/Admin/Withdrawals') || str_contains($l, '/Admin/Referrals')) return 'withdraw';
    return 'other';
}

/** অ্যাডমিন নোটিফিকেশন Telegram এর জন্য জমা রাখা (একই নোটিফিকেশন কয়েকজন অ্যাডমিনকে গেলেও Telegram এ একবারই) */
function tg_queue(?string $title, ?string $message, ?string $link): void
{
    $s = tg_settings();
    if (!$s['on']) return;
    $ev = tg_event_of($link);
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
        $t .= "\n\n👉 <a href=\"" . $h($url) . '">অ্যাডমিন প্যানেলে দেখুন</a>';
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
