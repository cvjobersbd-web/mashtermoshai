<?php
/**
 * BigBlueButton (live.mashtermoshai.com) — লাইভ ক্লাস আর ক্লাস রেকর্ডিং।
 * সার্ভারের ঠিকানা আর গোপন key (secret) অ্যাডমিন → Settings → Live Class এ বসানো থাকে (কোডে না, GitHub এ না)।
 * API: https://docs.bigbluebutton.org/development/api — প্রতিটা কলে checksum = sha1(callName + query + secret)
 */

/** ['url' => 'https://live.../bigbluebutton/', 'secret' => '...', 'on' => bool] */
function bbb_settings(): array
{
    static $s = null;
    if ($s !== null) return $s;
    $d = ['bbb_url' => 'https://live.mashtermoshai.com/bigbluebutton/', 'bbb_secret' => ''];
    try { $c = db()->GetSiteContent($d); } catch (Throwable $e) { $c = $d; }
    $url = trim((string)($c['bbb_url'] ?? ''));
    if ($url === '') $url = $d['bbb_url'];
    // টেস্টের জন্য — সার্ভারের পরিবেশ থেকে ঠিকানা বদলানো যায়
    if (($env = getenv('MM_BBB_URL')) !== false && $env !== '') $url = $env;
    $url = rtrim(preg_replace('#/api/?$#', '/', $url), '/') . '/';
    $secret = trim((string)($c['bbb_secret'] ?? ''));
    return $s = ['url' => $url, 'secret' => $secret, 'on' => $secret !== '' && preg_match('#^https?://#i', $url)];
}

function bbb_ready(): bool { return bbb_settings()['on']; }

/** API কলের পুরো URL (checksum সহ) */
function bbb_url(string $call, array $params): string
{
    $s = bbb_settings();
    $q = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    return $s['url'] . 'api/' . $call . '?' . ($q !== '' ? $q . '&' : '') . 'checksum=' . sha1($call . $q . $s['secret']);
}

/** API কল করে XML ফেরত দেয়; সমস্যা হলে null আর $err এ কারণ */
function bbb_call(string $call, array $params, ?string &$err = null): ?SimpleXMLElement
{
    $err = null;
    if (!bbb_ready()) { $err = 'লাইভ ক্লাসের সার্ভার এখনো সেট করা হয়নি (Admin → Live Class Server)।'; return null; }
    $url = bbb_url($call, $params);
    $body = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mashtermoshai/1.0']);
        $body = curl_exec($ch);
        if ($body === false) { $err = 'লাইভ সার্ভারে যোগাযোগ করা যায়নি: ' . curl_error($ch); $body = null; }
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true, 'header' => "User-Agent: Mashtermoshai/1.0\r\n"]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) { $err = 'লাইভ সার্ভারে যোগাযোগ করা যায়নি।'; $body = null; }
    }
    if ($body === null) { error_log('[bbb ' . $call . '] ' . $err); return null; }
    $prev = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    libxml_clear_errors(); libxml_use_internal_errors($prev);
    if (!$xml) { $err = 'লাইভ সার্ভার থেকে ঠিক উত্তর আসেনি।'; return null; }
    if ((string)$xml->returncode !== 'SUCCESS') {
        $key = (string)$xml->messageKey;
        $err = $key === 'checksumError' ? 'লাইভ সার্ভারের secret ভুল — Admin → Live Class Server এ ঠিক secret দিন।' : ('লাইভ সার্ভার: ' . ((string)$xml->message ?: $key ?: 'সমস্যা হয়েছে'));
        return $xml->messageKey ? $xml : null;
    }
    return $xml;
}

/** মিটিংয়ের পাসওয়ার্ড (মিটিং আইডি থেকে বানানো — কোথাও জমা রাখতে হয় না) */
function bbb_pw(string $meetingId, bool $moderator): string
{
    return substr(hash_hmac('sha256', ($moderator ? 'mod:' : 'att:') . $meetingId, bbb_settings()['secret'] . '|mm'), 0, 20);
}

/**
 * মিটিং তৈরি (আগে থেকে থাকলে BBB একই মিটিং ফেরত দেয়)।
 * $o: record(bool), autoStart(bool) — রেকর্ডিং নিজে থেকে শুরু, logoutURL, welcome, duration (মিনিট, 0 = সীমাহীন), meta (array)
 */
function bbb_create(string $meetingId, string $name, array $o = [], ?string &$err = null): bool
{
    $p = ['name' => mb_substr($name, 0, 200), 'meetingID' => $meetingId, 'attendeePW' => bbb_pw($meetingId, false), 'moderatorPW' => bbb_pw($meetingId, true),
        'record' => !empty($o['record']) ? 'true' : 'false', 'autoStartRecording' => !empty($o['autoStart']) ? 'true' : 'false',
        'allowStartStopRecording' => !empty($o['record']) ? 'true' : 'false', 'logoutURL' => $o['logoutURL'] ?? (site_public_url() . '/'),
        'welcome' => $o['welcome'] ?? ('<b>মাষ্টার মশাই</b> লাইভ ক্লাসে স্বাগতম — ' . htmlspecialchars($name, ENT_QUOTES)),
        'duration' => (string)max(0, (int)($o['duration'] ?? 0)), 'meetingKeepEvents' => 'false'];
    foreach (($o['meta'] ?? []) as $k => $v) $p['meta_' . preg_replace('/[^a-z0-9]/i', '', (string)$k)] = (string)$v;
    $x = bbb_call('create', $p, $err);
    return $x !== null && (string)$x->returncode === 'SUCCESS';
}

/** মিটিংয়ে ঢোকার লিংক (ব্রাউজারকে এখানে পাঠাতে হয়) */
function bbb_join_url(string $meetingId, string $fullName, bool $moderator, int $userId): string
{
    return bbb_url('join', ['fullName' => mb_substr($fullName !== '' ? $fullName : 'User', 0, 80), 'meetingID' => $meetingId,
        'password' => bbb_pw($meetingId, $moderator), 'userID' => 'mm-' . $userId, 'redirect' => 'true']);
}

function bbb_running(string $meetingId): bool
{
    $x = bbb_call('isMeetingRunning', ['meetingID' => $meetingId]);
    return $x !== null && strtolower((string)$x->running) === 'true';
}

function bbb_end(string $meetingId): bool
{
    $x = bbb_call('end', ['meetingID' => $meetingId, 'password' => bbb_pw($meetingId, true)]);
    return $x !== null && (string)$x->returncode === 'SUCCESS';
}

/**
 * রেকর্ডিং — [meetingID => [['id', 'url', 'minutes', 'start', 'state'], ...]]
 * state = published (দেখা যায়) / processing (তৈরি হচ্ছে)
 */
function bbb_recordings(array $meetingIds): array
{
    $meetingIds = array_values(array_filter(array_unique($meetingIds)));
    if (!$meetingIds || !bbb_ready()) return [];
    $out = [];
    foreach (array_chunk($meetingIds, 25) as $chunk) {
        $x = bbb_call('getRecordings', ['meetingID' => implode(',', $chunk), 'state' => 'processing,processed,published']);
        if (!$x || !isset($x->recordings->recording)) continue;
        foreach ($x->recordings->recording as $r) {
            $url = '';
            foreach (($r->playback->format ?? []) as $f) { if ((string)$f->type === 'presentation' || $url === '') $url = (string)$f->url; }
            $state = (string)$r->state;
            $out[(string)$r->meetingID][] = ['id' => (string)$r->recordID, 'url' => $url, 'state' => $state === 'published' ? 'published' : 'processing',
                'start' => (int)floor(((int)$r->startTime) / 1000), 'minutes' => (int)round((((int)$r->endTime) - ((int)$r->startTime)) / 60000)];
        }
    }
    return $out;
}

/** টিচারের এখন "প্রিমিয়াম লাইভ" প্যাকেজ চালু আছে কিনা (চ্যাট থেকে সরাসরি লাইভ ক্লাস) */
function teacher_live_enabled(?int $userId): bool
{
    if (!$userId) return false;
    static $c = [];
    if (isset($c[$userId])) return $c[$userId];
    try { return $c[$userId] = db()->TeacherHasLivePackage($userId); } catch (Throwable $e) { return $c[$userId] = false; }
}

/** কোর্স বিক্রির কত % অ্যাডমিন রাখবে (ডিফল্ট ৫০%) */
function course_admin_pct(): int
{
    static $p = null;
    if ($p !== null) return $p;
    try { $v = (int)(db()->GetSiteContent(['course_admin_pct' => '50'])['course_admin_pct'] ?? 50); } catch (Throwable $e) { $v = 50; }
    return $p = max(0, min(100, $v));
}

/** কোর্সের থাম্বনেইল (ছবি না থাকলে নামের রঙিন কার্ড) */
function course_thumb_html(object $c): string
{
    $t = (string)($c->Title ?? '');
    $hue = abs(crc32($t)) % 360;
    $bg = "background:linear-gradient(135deg,hsl($hue 70% 28%),hsl(" . (($hue + 40) % 360) . " 80% 48%));";
    if (!empty($c->Thumbnail)) return '<div class="cx-thumb" style="' . $bg . '"><img src="' . e((string)$c->Thumbnail) . '" alt="' . e($t) . '" loading="lazy" onerror="this.remove()" /></div>';
    return '<div class="cx-thumb" style="' . $bg . '"><span class="ini">' . e(mb_strimwidth($t, 0, 42, '…')) . '</span></div>';
}

/** কোর্সের দাম — ফ্রি / ৳ / আগের দাম কাটা */
function course_price_html(object $c): string
{
    if ((int)$c->Price <= 0) return '<span class="cx-price free">ফ্রি</span>';
    return '<span class="cx-price">' . e(taka((int)$c->Price)) . (!empty($c->RegularPrice) ? '<s>' . e(taka((int)$c->RegularPrice)) . '</s>' : '') . '</span>';
}
