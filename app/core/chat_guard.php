<?php
/**
 * চ্যাটের নিয়ম:
 *  ১) মোবাইল নম্বর, ইমেইল, যেকোনো লিংক, সোশ্যাল মিডিয়া, Zoom / Google Meet ইত্যাদি পাঠানো যায় না (দুই পক্ষেই)
 *     — যোগাযোগ সাইটের বাইরে নিয়ে যাওয়া আটকাতে। চাতুরী করে লেখাও (০১৭ ১২৩.., "zero one seven", "fb dot com") ধরা হয়।
 *  ২) প্যাকেজ ছাড়া টিচার: কে মেসেজ দিয়েছে দেখে, কিন্তু মেসেজের লেখা আর শিক্ষার্থীর ছবি দেখে না, উত্তরও দিতে পারে না।
 *     লেখাটা ব্রাউজারে পাঠানোই হয় না — তাই পেজের কোড খুলেও পড়া যায় না।
 */

/** মেসেজে নিষিদ্ধ কিছু থাকলে কারণ (বাংলা), না থাকলে null */
function chat_contact_block(string $text): ?string
{
    $t = mb_strtolower($text);
    // বাংলা / আরবি সংখ্যা → ইংরেজি
    $t = strtr($t, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    // কথায় লেখা সংখ্যা (zero one seven / শূন্য এক সাত) → অঙ্ক
    $words = ['zero' => '0', 'oh' => '0', 'one' => '1', 'two' => '2', 'three' => '3', 'four' => '4', 'five' => '5', 'six' => '6', 'seven' => '7', 'eight' => '8', 'nine' => '9',
        'শূন্য' => '0', 'শুন্য' => '0', 'জিরো' => '0', 'এক' => '1', 'দুই' => '2', 'তিন' => '3', 'চার' => '4', 'পাঁচ' => '5', 'পাচ' => '5', 'ছয়' => '6', 'ছয়' => '6', 'সাত' => '7', 'আট' => '8', 'নয়' => '9', 'নয়' => '9'];
    $n = preg_replace_callback('/(?<![\p{L}\p{M}])(' . implode('|', array_map(fn($w) => preg_quote($w, '/'), array_keys($words))) . ')(?![\p{L}\p{M}])/u', fn($m) => $words[$m[1]], $t);

    // ===== মোবাইল নম্বর: ১০+ অঙ্কের ধারা (মাঝে স্পেস / - / . থাকলেও) বা 01XXXXXXXXX =====
    if (preg_match_all('/\+?\d[\d\s\-\.\/_\(\)\*,]{7,}\d/u', $n, $mm)) {
        foreach ($mm[0] as $run) {
            $d = preg_replace('/\D/', '', $run);
            if (strlen($d) >= 10 || preg_match('/01[3-9]\d{6,}/', $d)) return 'মোবাইল নম্বর পাঠানো যাবে না।';
        }
    }
    // ===== ইমেইল =====
    if (preg_match('/[a-z0-9._%+\-]+\s*(@|\(at\)|\[at\]|\sat\s)\s*[a-z0-9\-]+\s*(\.|\(dot\)|\[dot\]|\sdot\s)\s*[a-z]{2,}/iu', $t)
        || preg_match('/\b(gmail|yahoo|hotmail|outlook|icloud)\b/iu', $t)) return 'ইমেইল ঠিকানা পাঠানো যাবে না।';
    // ===== লিংক / ওয়েবসাইট =====
    if (preg_match('#https?://|\bwww\s*\.|\b[a-z0-9\-]{2,}\s*(\.|\(dot\)|\[dot\]|\sdot\s)\s*(com|net|org|io|me|bd|co|in|info|xyz|link|ly|gg|us|app|site|online|live|page|to|be)\b#iu', $t)) return 'লিংক বা ওয়েবসাইটের ঠিকানা পাঠানো যাবে না।';
    // ===== সোশ্যাল মিডিয়া / অন্য অ্যাপ / মিটিং লিংক =====
    $en = ['whats\s*app', 'wa\.me', 'imo', 'telegram', 't\.me', 'viber', 'messenger', 'facebook', 'fb', 'f\.b', 'instagram', 'insta', 'tiktok', 'tik\s*tok', 'snapchat', 'discord',
        'linkedin', 'twitter', 'skype', 'zoom', 'google\s*meet', 'g\s*meet', 'meet\.google', 'microsoft\s*teams', 'ms\s*teams', 'botim'];
    $bn = ['হোয়াটস\s*অ্যাপ', 'হোয়াটস\s*অ্যাপ', 'হোয়াটস\s*এপ', 'হোয়াটস\s*এপ', 'ওয়াটস\s*অ্যাপ', 'ওয়াটস\s*অ্যাপ', 'ইমো', 'টেলিগ্রাম', 'ভাইবার', 'মেসেঞ্জার', 'ফেসবুক', 'ফেইসবুক', 'এফবি', 'ইনস্টাগ্রাম', 'ইন্সটাগ্রাম', 'ইনস্টা',
        'টিকটক', 'স্কাইপ', 'জুম', 'গুগল\s*মিট', 'গুগল\s*মিটিং', 'লিংকডইন', 'লিংকড\s*ইন'];
    if (preg_match('/(?<![\p{L}\p{M}0-9])(' . implode('|', $en) . ')(?![\p{L}\p{M}0-9])/iu', $t)
        // বাংলা নামের সাথে বিভক্তি (ফেসবুকে, জুমে, ইমোতে, হোয়াটসঅ্যাপের ...) — কিন্তু "জুমা" এর মতো অন্য শব্দ না
        || preg_match('/(?<![\p{L}\p{M}])(' . implode('|', $bn) . ')(ে|এ|তে|য়|য়|এর|ের|র|ও|টা|টি|ই)?(?![\p{L}\p{M}])/u', $t))
        return 'WhatsApp, Facebook, Zoom, Google Meet সহ কোনো সোশ্যাল মিডিয়া বা অ্যাপের নাম / আইডি দেওয়া যাবে না।';
    // @username (Instagram / Telegram আইডি)
    if (preg_match('/(?<![a-z0-9])@[a-z0-9_.]{3,}/iu', $t)) return 'সোশ্যাল মিডিয়ার আইডি (@...) পাঠানো যাবে না।';
    return null;
}

function chat_block_message(string $why): string
{
    return '🔒 ' . $why . ' নিরাপত্তার জন্য চ্যাটে মোবাইল নম্বর, ইমেইল, লিংক, সোশ্যাল মিডিয়া বা Zoom / Google Meet দেওয়া যায় না — পড়ানোর বিষয়, সময় আর বেতন নিয়ে কথা বলুন।';
}

/** এই চ্যাটে আমি (টিচার) প্যাকেজ ছাড়া কিনা — তাহলে অন্যদের মেসেজ, শিক্ষার্থীর ছবি দেখা যায় না, উত্তরও দেওয়া যায় না */
function chat_locked_for(Obj $t, int $me): bool
{
    if (!empty($t->AdminChat) || is_admin()) return false;
    if ((int)$t->TeacherId !== $me || current_role() !== 'Teacher') return false;
    if (chat_thread_free($t)) return false;
    return !is_premium_teacher($me);
}

/** অ্যাডমিন এই টিচার-শিক্ষার্থী জোড়াকে ফ্রি করে দিয়েছে কিনা (গ্রুপ বাদে) */
function chat_thread_free(Obj $t): bool
{
    if (($t->Kind ?? '') === 'grp' || empty($t->TeacherId) || empty($t->StudentId)) return false;
    try { return db()->IsChatFree((int)$t->TeacherId, (int)$t->StudentId); } catch (Throwable $e) { return false; }
}

/** এই চ্যাট থেকে আমি (টিচার) লাইভ ক্লাস নিতে পারব কিনা — লাইভ প্যাকেজ, বা অ্যাডমিনের ফ্রি জোড়া */
function chat_live_allowed(Obj $t, int $me): bool
{
    if (teacher_live_enabled($me)) return true;
    return (int)$t->TeacherId === $me && chat_thread_free($t);
}

/** কোনো ইউজার (নোটিফিকেশন পাবে এমন) প্যাকেজ ছাড়া টিচার কিনা — তাহলে নোটিফিকেশনে মেসেজের লেখা যায় না */
function chat_user_locked(int $userId): bool
{
    static $c = [];
    if (isset($c[$userId])) return $c[$userId];
    try { $u = db()->GetUserById($userId); } catch (Throwable $e) { $u = null; }
    return $c[$userId] = $u && $u->Role === 'Teacher' && !is_premium_teacher($userId);
}

/** টিচারের Teacher ID (TCH-1234) — Approve হওয়া আবেদনের নম্বর থেকে */
function teacher_code(int $userId): string
{
    static $c = [];
    if (isset($c[$userId])) return $c[$userId];
    try { $id = (int)db()->scalar("SELECT MAX(`Id`) FROM `TeacherRequests` WHERE `UserId` = :u AND `Status` = 'Approved'", ['u' => $userId]); } catch (Throwable $e) { $id = 0; }
    return $c[$userId] = $id ? 'TCH-' . (1000 + $id) : '';
}

/** চ্যাটের ডান পাশের কার্ডের জন্য টিচারের তথ্য (যোগাযোগের কোনো তথ্য না) */
function chat_teacher_card(int $teacherId): array
{
    $db = db();
    $u = $db->GetUserById($teacherId);
    $d = [];
    try { $d = $db->GetTeacherDetails($teacherId); } catch (Throwable $e) { }
    $rating = null;
    try { $r = $db->one('SELECT AVG(`Rating`) AS A, COUNT(*) AS N FROM `TeacherReviews` WHERE `TeacherId` = :t', ['t' => $teacherId]); if ($r && (int)$r->N > 0) $rating = [round((float)$r->A, 1), (int)$r->N]; } catch (Throwable $e) { }
    $loc = array_filter([trim((string)($d['livingArea'] ?? '')), trim((string)($d['livingCity'] ?? ''))]);
    $j = fn($a) => implode(', ', array_slice(array_values(array_filter((array)$a)), 0, 6));
    return ['code' => teacher_code($teacherId), 'user' => (string)($u->UserName ?? ''), 'rating' => $rating,
        'rows' => array_filter(['বিষয়' => $j($d['subjects'] ?? []), 'মিডিয়াম' => $j($d['mediums'] ?? []), 'ক্লাস' => $j($d['classes'] ?? []), 'এলাকা' => implode(', ', $loc)], fn($v) => $v !== '')];
}
