<?php
/**
 * Global helper functions (Razor/ASP.NET এর সমতুল্য ছোট ছোট ফাংশন)
 */

// ------------------------------------------------------------
// Bag: ViewBag / ViewData / TempData — missing key হলে null দেয়
// ------------------------------------------------------------
class Bag implements ArrayAccess, IteratorAggregate
{
    private array $data = [];
    public function __construct(array $data = []) { $this->data = $data; }
    public function __get($k) { return $this->data[$k] ?? null; }
    public function __set($k, $v) { $this->data[$k] = $v; }
    public function __isset($k) { return isset($this->data[$k]); }
    public function __unset($k) { unset($this->data[$k]); }
    public function offsetExists($k): bool { return isset($this->data[$k]); }
    public function offsetGet($k): mixed { return $this->data[$k] ?? null; }
    public function offsetSet($k, $v): void { if ($k === null) $this->data[] = $v; else $this->data[$k] = $v; }
    public function offsetUnset($k): void { unset($this->data[$k]); }
    public function getIterator(): Iterator { return new ArrayIterator($this->data); }
    public function toArray(): array { return $this->data; }
}

/** Plain data object — missing property হলে warning ছাড়া null দেয় */
class Obj extends stdClass
{
    public function __construct(array $data = []) { foreach ($data as $k => $v) $this->$k = $v; }
    public function __get($k) { return null; }
}

function obj(array $data = []): Obj { return new Obj($data); }

/** stdClass/array → Obj (missing property safe) */
function to_obj($row): ?Obj
{
    if ($row === null || $row === false) return null;
    if ($row instanceof Obj) return $row;
    return new Obj(is_array($row) ? $row : get_object_vars($row));
}

// ------------------------------------------------------------
// App globals
// ------------------------------------------------------------
function config(string $key = null, $default = null)
{
    $cfg = $GLOBALS['__config'] ?? [];
    if ($key === null) return $cfg;
    $cur = $cfg;
    foreach (explode('.', $key) as $p) {
        if (!is_array($cur) || !array_key_exists($p, $cur)) return $default;
        $cur = $cur[$p];
    }
    return $cur;
}

/** Database model (DatabaseHelper এর PHP ভার্সন) */
function db(): Db
{
    static $inst = null;
    if ($inst === null) $inst = new Db(pdo());
    return $inst;
}

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    if (isset($GLOBALS['__pdo'])) return $pdo = $GLOBALS['__pdo'];
    $c = config('db');
    if (!empty($c['dsn'])) { // শুধু টেস্টের জন্য (যেমন sqlite)
        $pdo = new PDO($c['dsn'], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        return $pdo;
    }
    $dsn = "mysql:host={$c['host']};port=" . ($c['port'] ?? 3306) . ";dbname={$c['name']};charset=" . ($c['charset'] ?? 'utf8mb4');
    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ]);
    $offset = (new DateTime('now', new DateTimeZone(config('timezone', 'Asia/Dhaka'))))->format('P');
    $pdo->exec("SET time_zone = '$offset'");
    return $pdo;
}

// ------------------------------------------------------------
// Output helpers
// ------------------------------------------------------------
/** HTML encode (Razor @value এর মতো) */
function e($v): string
{
    if ($v === null) return '';
    if (is_bool($v)) return $v ? 'True' : 'False';
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** JS string literal (JSON encoded) — <script> এর ভিতরে নিরাপদ */
function js($v): string
{
    return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function now(): string { return date('Y-m-d H:i:s'); }

/** string → DateTime (null/empty হলে null) */
function to_dt($v): ?DateTime
{
    if ($v instanceof DateTime) return $v;
    if ($v === null || $v === '' || $v === '0000-00-00 00:00:00') return null;
    try { return new DateTime((string)$v); } catch (Throwable $e) { return null; }
}

/**
 * তারিখ ফরম্যাট (PHP date() format)।
 * C# → PHP: "dd MMM yyyy" → "d M Y", "hh:mm tt" → "h:i A", "HH:mm" → "H:i",
 *           "MMMM dd, yyyy" → "F d, Y", "yyyy-MM-dd" → "Y-m-d", "o" → "c"
 */
function fmt_date($v, string $format = 'd M Y', string $fallback = ''): string
{
    $d = to_dt($v);
    return $d ? $d->format($format) : $fallback;
}

/** "just now", "5m ago", "3h ago", "2d ago", "1w ago", অথবা "dd MMM yyyy" */
function time_ago($v, string $oldFormat = 'd M Y'): string
{
    $d = to_dt($v);
    if (!$d) return '';
    $secs = time() - $d->getTimestamp();
    if ($secs < 60) return 'just now';
    $mins = intdiv($secs, 60);
    if ($mins < 60) return $mins . 'm ago';
    $hours = intdiv($secs, 3600);
    if ($hours < 24) return $hours . 'h ago';
    $days = intdiv($secs, 86400);
    if ($days < 7) return $days . 'd ago';
    if ($days < 30) return intdiv($days, 7) . 'w ago';
    return $d->format($oldFormat);
}

/** নামের আদ্যক্ষর: "Rahim Uddin" → "RU" */
function initials(?string $name, string $default = 'U'): string
{
    $name = trim((string)$name);
    if ($name === '') return $default;
    $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($words) >= 2) return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    return mb_strtoupper(mb_substr($words[0], 0, 1));
}

/** WhatsApp লিংক (01XXXXXXXXX → https://wa.me/8801XXXXXXXXX) */
function whatsapp_link(?string $wa, ?string $phone = null): string
{
    $num = trim((string)(($wa !== null && $wa !== '') ? $wa : ($phone ?? '')));
    if ($num === '') return '';
    if (str_starts_with($num, '0')) $num = '88' . $num;
    elseif (!str_starts_with($num, '88')) $num = '880' . ltrim($num, '+');
    return 'https://wa.me/' . $num;
}

function str_preview(?string $s, int $len, string $empty = '(No text)'): string
{
    $s = trim((string)$s);
    if ($s === '') return $empty;
    return mb_strlen($s) <= $len ? $s : mb_substr($s, 0, $len) . '...';
}

function is_blank($v): bool { return $v === null || trim((string)$v) === ''; }

/** পোস্ট/কমেন্টের রিয়েকশন: key => [ইমোজি, লেবেল, রঙ] */
function reaction_meta(): array
{
    // শুধু Like (অন্য কোনো রিয়েকশন নেই)
    return ['like' => [like_badge_html(), 'Like', '#0A66C2']];
}

/** Like বাটনের আইকন (নিজস্ব SVG — ইমোজি নয়) */
function like_icon_html(bool $on): string
{
    $path = $on
        ? 'M2 21h3.2c.44 0 .8-.36.8-.8V9.8c0-.44-.36-.8-.8-.8H2v12zm20.7-9.8c0-1.1-.9-2-2-2h-5.6l.86-4.15c.07-.33-.01-.68-.22-.95L14.6 2.6 8.5 8.7c-.32.32-.5.75-.5 1.2V19c0 1.1.9 2 2 2h8.2c.8 0 1.52-.48 1.84-1.21l2.48-5.8c.12-.27.18-.56.18-.86V11.2z'
        : 'M13.11 5.72l-.57 2.89c-.12.59.04 1.2.42 1.66.38.46.94.73 1.54.73H20v1.08L17.43 18H9.34c-.18 0-.34-.16-.34-.34V9.82l4.11-4.1M14 2L7.59 8.41C7.21 8.79 7 9.3 7 9.83v7.83C7 18.95 8.05 20 9.34 20h8.1c.71 0 1.36-.37 1.72-.97l2.67-6.15c.11-.25.17-.52.17-.8V11c0-1.1-.9-2-2-2h-5.5l.92-4.65c.05-.22.02-.46-.08-.66-.23-.45-.52-.86-.88-1.22L14 2zM4 9H2v11h2c.55 0 1-.45 1-1v-9c0-.55-.45-1-1-1z';
    return '<svg class="lk-svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="' . $path . '"/></svg>';
}

/** লাইক সংখ্যার পাশে ছোট গোল ব্যাজ */
function like_badge_html(): string
{
    return '<i class="lk-badge" aria-hidden="true">' . like_icon_html(true) . '</i>';
}

/** পোস্টের প্রাইভেসি: key => [আইকন, লেবেল, বাংলা ব্যাখ্যা] */
function post_privacy_meta(): array
{
    return [
        'Public'  => ['fa-earth-asia', 'Public', 'সবাই দেখতে পারবে'],
        'Friends' => ['fa-user-group', 'Friends', 'শুধু আপনার ফ্রেন্ডরা'],
        'OnlyMe'  => ['fa-lock', 'Only me', 'শুধু আপনি'],
    ];
}

/** টেক্সট escape করে লিংকগুলো ক্লিকযোগ্য করে (http/https ছাড়া অন্য কিছু না) */
function linkify_text(?string $text): string
{
    $html = e((string)$text);
    return preg_replace_callback('~\bhttps?://[^\s<>"\']+~i', function ($m) {
        $url = rtrim($m[0], '.,!?;:)');
        $tail = substr($m[0], strlen($url));
        return '<a href="' . $url . '" target="_blank" rel="noopener nofollow ugc">' . $url . '</a>' . $tail;
    }, $html);
}

/**
 * কমেন্টের লেখা → HTML: escape + লিংক + @[নাম|id] মেনশনকে প্রোফাইল লিংক বানায়।
 * পুরোনো রিপ্লাইয়ের শুরুর "@[নাম] " (id ছাড়া) শুধু নীল লেখা হয়।
 */
function comment_text_html(?string $text): string
{
    $html = linkify_text($text);
    $html = preg_replace_callback('/@\[([^\]|<>]{1,120})\|(\d{1,10})\]/u', fn($m) =>
        '<a class="cm-mention" href="/Profile/Show/' . (int)$m[2] . '">' . $m[1] . '</a>', $html) ?? $html;
    $html = preg_replace('/^@\[([^\]|<>]{1,120})\]\s?/u', '<span class="cm-mention">$1</span> ', $html) ?? $html;
    return $html;
}

/** এডিট বক্সের জন্য: @[নাম|id] → @নাম, আর নাম→id ম্যাপ */
function mention_plain(?string $text): array
{
    $map = [];
    $plain = preg_replace_callback('/@\[([^\]|]{1,80})\|(\d{1,10})\]/u', function ($m) use (&$map) { $map[$m[1]] = (int)$m[2]; return '@' . $m[1]; }, (string)$text);
    return [$plain ?? (string)$text, $map];
}

/**
 * ফ্রেন্ড বাটন (সম্পর্ক অনুযায়ী)। JS এর frRenderActions() একই HTML বানায় —
 * একটা বদলালে অন্যটাও বদলাতে হবে।
 * $rel: none | sent | received | friends | self
 */
function friend_actions_html(int $uid, string $rel): string
{
    $b = fn(string $act, string $cls, string $icon, string $label) =>
        '<button type="button" class="fr-btn ' . $cls . '" data-act="' . $act . '" data-uid="' . $uid . '"><i class="fas ' . $icon . '"></i><span>' . $label . '</span></button>';
    switch ($rel) {
        case 'friends':
            return '<span class="fr-btn fr-is-friend"><i class="fas fa-user-check"></i><span>Friends</span></span>'
                . $b('unfriend', 'fr-ghost fr-icon-only', 'fa-user-xmark', 'Unfriend');
        case 'sent':
            return '<span class="fr-btn fr-is-sent"><i class="fas fa-clock"></i><span>Requested</span></span>'
                . $b('cancel', 'fr-ghost', 'fa-xmark', 'Cancel');
        case 'received':
            return $b('accept', 'fr-primary', 'fa-check', 'Confirm') . $b('decline', 'fr-ghost', 'fa-xmark', 'Delete');
        case 'self':
            return '<a class="fr-btn fr-ghost" href="/Profile"><i class="fas fa-pen"></i><span>Edit Profile</span></a>';
        default:
            return $b('send', 'fr-primary', 'fa-user-plus', 'Add Friend');
    }
}

// ------------------------------------------------------------
// Request helpers (ASP.NET model binding এর মতো — case-insensitive)
// ------------------------------------------------------------
function request_params(): array
{
    static $merged = null;
    if ($merged !== null) return $merged;
    $merged = [];
    foreach ($_GET as $k => $v) $merged[strtolower($k)] = $v;
    foreach (($GLOBALS['__route_values'] ?? []) as $k => $v) $merged[strtolower($k)] = $v;
    foreach ($_POST as $k => $v) $merged[strtolower($k)] = $v;
    return $merged;
}

/** form/route/query থেকে মান (string) */
function input(string $name, $default = null)
{
    $p = request_params();
    $k = strtolower($name);
    if (!array_key_exists($k, $p)) return $default;
    $v = $p[$k];
    if (is_array($v)) $v = end($v);
    return $v;
}

function input_str(string $name, ?string $default = null): ?string
{
    $v = input($name, null);
    if ($v === null) return $default;
    return (string)$v;
}

function input_int(string $name, ?int $default = 0): ?int
{
    $v = input($name, null);
    if ($v === null || $v === '' || !is_numeric($v)) return $default;
    return (int)$v;
}

function input_bool(string $name, bool $default = false): bool
{
    $v = input($name, null);
    if ($v === null) return $default;
    // checkbox + hidden "false" দুইটা এলে, যেকোনো একটা true হলে true
    $all = $_POST[$name] ?? null;
    if (is_array($all)) {
        foreach ($all as $x) if (in_array(strtolower((string)$x), ['true', 'on', '1', 'yes'], true)) return true;
        return false;
    }
    return in_array(strtolower(trim((string)$v)), ['true', 'on', '1', 'yes'], true);
}

function has_input(string $name): bool
{
    return array_key_exists(strtolower($name), request_params());
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

/** আপলোড ফাইল (না থাকলে null) — case-insensitive নাম */
function upload_file(string $name): ?array
{
    foreach ($_FILES as $k => $f) {
        if (strcasecmp($k, $name) === 0) {
            if (is_array($f['name'])) {
                $f = ['name' => $f['name'][0], 'type' => $f['type'][0], 'tmp_name' => $f['tmp_name'][0], 'error' => $f['error'][0], 'size' => $f['size'][0]];
            }
            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($f['size'] ?? 0) <= 0) return null;
            return $f;
        }
    }
    return null;
}

/**
 * ফাইল সেভ করে public URL ফেরত দেয় (যেমন /uploads/profiles/abc.jpg)
 */
function save_upload(array $file, string $subDir, string $fileNameWithoutExt, ?string $ext = null): string
{
    $ext = $ext ?? strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
    $dir = APP_ROOT . '/uploads/' . trim($subDir, '/');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $name = $fileNameWithoutExt . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        // টেস্ট পরিবেশে move_uploaded_file কাজ না করলে
        if (!@rename($file['tmp_name'], $dest) && !@copy($file['tmp_name'], $dest)) {
            throw new RuntimeException('Failed to save uploaded file.');
        }
    }
    @chmod($dest, 0644);
    return '/uploads/' . trim($subDir, '/') . '/' . $name;
}

/** /uploads/... URL এর লোকাল ফাইল ডিলিট */
function delete_upload(?string $url): void
{
    if (!$url || !str_starts_with($url, '/uploads/')) return;
    $path = realpath(APP_ROOT . $url);
    $root = realpath(APP_ROOT . '/uploads');
    if ($path && $root && str_starts_with($path, $root) && is_file($path)) @unlink($path);
}

/** C# DateTime.Ticks এর মতো ইউনিক সংখ্যা */
function ticks(): string
{
    return sprintf('%d%07d', time() + 62135596800, (int)(fmod(microtime(true), 1) * 10000000));
}

// ------------------------------------------------------------
// Session helpers
// ------------------------------------------------------------
function session_get(string $key): ?string
{
    $v = $_SESSION[$key] ?? null;
    return $v === null ? null : (string)$v;
}
function session_set(string $key, $value): void { $_SESSION[$key] = $value === null ? null : (string)$value; }
function session_clear(): void
{
    $csrf = $_SESSION['_csrf'] ?? null;
    $_SESSION = [];
    session_regenerate_id(true);
    if ($csrf) $_SESSION['_csrf'] = $csrf;
}
function current_user_id(): ?int
{
    $v = session_get('UserId');
    return ($v !== null && is_numeric($v)) ? (int)$v : null;
}
function current_role(): ?string { return session_get('UserRole'); }
function is_admin(): bool { return session_get('UserRole') === 'Admin'; }
function is_logged_in(): bool { return !is_blank(session_get('UserId')); }

// ------------------------------------------------------------
// TempData (পরের রিকোয়েস্ট পর্যন্ত থাকে)
// ------------------------------------------------------------
function tempdata(): Bag
{
    return $GLOBALS['__TempData'];
}
function tempdata_set(string $key, $value): void
{
    $GLOBALS['__TempData'][$key] = $value;
    $_SESSION['_tempdata'][$key] = $value;
}

// ------------------------------------------------------------
// CSRF (ASP.NET AntiForgeryToken এর মতো)
// ------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string
{
    return '<input name="__RequestVerificationToken" type="hidden" value="' . e(csrf_token()) . '" />';
}
function csrf_valid(): bool
{
    $sent = $_POST['__RequestVerificationToken']
        ?? ($_SERVER['HTTP_REQUESTVERIFICATIONTOKEN'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_SERVER['HTTP_X_XSRF_TOKEN'] ?? null)));
    if (is_array($sent)) $sent = reset($sent);
    return !empty($_SESSION['_csrf']) && is_string($sent) && hash_equals($_SESSION['_csrf'], $sent);
}

// ------------------------------------------------------------
// Responses
// ------------------------------------------------------------
function redirect(string $url): void
{
    header('Location: ' . $url, true, 302);
    exit;
}

/** Url.Action এর মতো: url_action('Index','Home') → "/" */
function url_action(string $action = 'Index', string $controller = 'Home', array $params = []): string
{
    $id = null;
    if (array_key_exists('id', $params)) { $id = $params['id']; unset($params['id']); }
    if (strcasecmp($controller, 'Home') === 0 && strcasecmp($action, 'Index') === 0 && $id === null) $url = '/';
    elseif (strcasecmp($action, 'Index') === 0 && $id === null) $url = '/' . $controller;
    else $url = '/' . $controller . '/' . $action . ($id !== null && $id !== '' ? '/' . rawurlencode((string)$id) : '');
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    if ($params) $url .= '?' . http_build_query($params);
    return $url;
}

function redirect_to(string $action, string $controller, array $params = []): void
{
    redirect(url_action($action, $controller, $params));
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    exit;
}

/** ASP.NET Json() — PascalCase key গুলোকে camelCase করে দেয় (nested সহ) */
function camel_keys($data)
{
    if ($data instanceof Bag) $data = $data->toArray();
    if (is_object($data)) $data = get_object_vars($data);
    if (!is_array($data)) return $data;
    $isList = array_is_list($data);
    $out = [];
    foreach ($data as $k => $v) {
        $nk = (!$isList && is_string($k) && $k !== '') ? lcfirst_camel($k) : $k;
        $out[$nk] = camel_keys($v);
    }
    return $out;
}
function lcfirst_camel(string $k): string
{
    // "IsCSEStudent" → "isCSEStudent", "URL" → "url", "Id" → "id"
    if (preg_match('/^[A-Z]+$/', $k)) return strtolower($k);
    if (preg_match('/^([A-Z]+)([A-Z][a-z].*)$/', $k, $m)) return strtolower($m[1]) . $m[2];
    return lcfirst($k);
}

function json_camel($data, int $status = 200): void { json_response(camel_keys($data), $status); }

function not_found(string $msg = 'Not Found'): void
{
    http_response_code(404);
    if (is_ajax()) json_response(['success' => false, 'message' => $msg], 404);
    View::render('Shared/Error', ['Model' => obj(['RequestId' => null, 'Message' => $msg, 'StatusCode' => 404])]);
    exit;
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

/** Local URL কিনা (open redirect প্রতিরোধ) */
function is_local_url(?string $url): bool
{
    if (is_blank($url)) return false;
    if ($url[0] !== '/') return false;
    if (strlen($url) > 1 && ($url[1] === '/' || $url[1] === '\\')) return false;
    return true;
}

// ------------------------------------------------------------
// View sections (@section Scripts { ... })
// ------------------------------------------------------------
function section_start(string $name): void
{
    $GLOBALS['__section_stack'][] = $name;
    ob_start();
}
function section_end(): void
{
    $name = array_pop($GLOBALS['__section_stack']);
    $GLOBALS['__sections'][$name] = ($GLOBALS['__sections'][$name] ?? '') . ob_get_clean();
}
function render_section(string $name): string { return $GLOBALS['__sections'][$name] ?? ''; }
function has_section(string $name): bool { return isset($GLOBALS['__sections'][$name]); }

/** একটি partial view রেন্ডার করে (Html.PartialAsync এর মতো) */
function partial(string $view, array $vars = []): void
{
    View::partial($view, $vars);
}

/** Site settings (layout ইত্যাদিতে ব্যবহার) */
function site_settings(): SiteSettings
{
    static $s = null;
    if ($s === null) {
        try { $s = db()->GetSiteSettings(); } catch (Throwable $e) { $s = new SiteSettings(); }
    }
    return $s;
}

// ------------------------------------------------------------
// Model validation errors (asp-validation-for এর মতো)
// ------------------------------------------------------------
function field_error(string $field): string
{
    $msg = $GLOBALS['__ModelErrors'][$field] ?? null;
    return $msg ? e($msg) : '';
}
function has_field_error(string $field): bool { return !empty($GLOBALS['__ModelErrors'][$field]); }

// ============================================================
// ===== হোম পেজের "Become a Tutor" সেকশনের লেখা =====
// ============================================================
function become_tutor_defaults(): array
{
    return [
        'bt_enabled' => '1',
        'bt_badge'   => 'For Teachers',
        'bt_kicker'  => 'Want to Become a',
        'bt_title'   => 'TUT[O]R?',
        'bt_line1'   => 'Be a [master],',
        'bt_line2'   => 'to grow faster.',
        'bt_perks'   => "টিউশন পোস্টে Apply\nসরাসরি স্টুডেন্ট রিকোয়েস্ট\nসম্পূর্ণ ফ্রি",
    ];
}

function become_tutor_content(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(become_tutor_defaults());
    return $c;
}

/** প্রোফাইল পেজের বাম পাশের প্রোমো ছবি (অ্যাডমিন Settings থেকে বদলানো যায়) */
function profile_promo_defaults(): array
{
    return [
        'pp_image'   => 'https://i.ibb.co.com/ksWwwdSY/Student-Study-Desk-with-Tutor-Message.png',
        'pp_link'    => '',   // খালি = স্টুডেন্টকে Find Tutor, টিচারকে Tuition Circular এ নেয়
        'pp_enabled' => '1',
    ];
}

function profile_promo(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(profile_promo_defaults());
    return $c;
}

/** Tuition Circular পেজের উপরের ব্যানার ছবি (অ্যাডমিন সেটিংস থেকে বদলানো যায়) */
function circular_banner_defaults(): array
{
    return [
        'tc_banner'         => 'https://i.ibb.co.com/JRf4zfwV/Tuition-Circular-Find-Your-Perfect-Tutor.png',
        'tc_banner_enabled' => '1',
    ];
}

function circular_banner(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(circular_banner_defaults());
    return $c;
}

/** Find Tutor পেজের উপরের ব্যানার ছবি (অ্যাডমিন সেটিংস থেকে বদলানো যায়) */
function findtutor_banner_defaults(): array
{
    return [
        'ft_banner'         => 'https://i.ibb.co.com/TDB39bQ8/Find-Your-Perfect-Tutor-Banner.png',
        'ft_banner_enabled' => '1',
    ];
}

function findtutor_banner(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(findtutor_banner_defaults());
    return $c;
}

/** টিচার প্রোফাইলের বাম পাশের প্রোমো (ছবি খালি = ডিজাইন করা কার্ড "আরও বেশি শিক্ষার্থীর কাছে পৌঁছান") */
function teacher_promo_defaults(): array
{
    return [
        'tp_image'   => '',
        'tp_link'    => '',   // খালি = /FindTutor (Create Tuition Post)
        'tp_enabled' => '1',
    ];
}

function teacher_promo(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(teacher_promo_defaults());
    return $c;
}

/** লেখা escape করে [শব্দ] অংশটুকু হাইলাইট span এ মুড়ে দেয় */
function highlight_brackets(?string $text, string $class): string
{
    $safe = e((string)$text);
    return preg_replace('/\[([^\[\]]{1,60})\]/u', '<span class="' . e($class) . '">$1</span>', $safe);
}

// ============================================================
// ===== ফুটারের Social Media লিংক (অ্যাডমিন সেটিং থেকে) =====
// ============================================================
/** যেসব সোশ্যাল মিডিয়া অ্যাড করা যাবে: key => [নাম, আইকন class, placeholder, রং, টাইপ] */
function social_platforms(): array
{
    return [
        'facebook'  => ['Facebook',  'fa-brands fa-facebook-f',       'https://facebook.com/yourpage',      '#1877F2', 'url'],
        'messenger' => ['Messenger', 'fa-brands fa-facebook-messenger','https://m.me/yourpage',             '#0084FF', 'url'],
        'youtube'   => ['YouTube',   'fa-brands fa-youtube',          'https://youtube.com/@yourchannel',   '#FF0000', 'url'],
        'instagram' => ['Instagram', 'fa-brands fa-instagram',        'https://instagram.com/yourname',     '#E1306C', 'url'],
        'whatsapp'  => ['WhatsApp',  'fa-brands fa-whatsapp',         '01XXXXXXXXX অথবা https://wa.me/8801…', '#25D366', 'whatsapp'],
        'telegram'  => ['Telegram',  'fa-brands fa-telegram',         'https://t.me/yourchannel',           '#26A5E4', 'url'],
        'linkedin'  => ['LinkedIn',  'fa-brands fa-linkedin-in',      'https://linkedin.com/company/…',     '#0A66C2', 'url'],
        'x'         => ['X (Twitter)','fa-brands fa-x-twitter',       'https://x.com/yourname',             '#000000', 'url'],
        'tiktok'    => ['TikTok',    'fa-brands fa-tiktok',           'https://tiktok.com/@yourname',       '#010101', 'url'],
        'threads'   => ['Threads',   'fa-brands fa-threads',          'https://threads.net/@yourname',      '#000000', 'url'],
        'pinterest' => ['Pinterest', 'fa-brands fa-pinterest-p',      'https://pinterest.com/yourname',     '#E60023', 'url'],
        'github'    => ['GitHub',    'fa-brands fa-github',           'https://github.com/yourname',        '#181717', 'url'],
        'website'   => ['Website',   'fa-solid fa-globe',             'https://example.com',                '#02B8F5', 'url'],
        'email'     => ['Email',     'fa-solid fa-envelope',          'info@mashtermoshai.com',             '#EA4335', 'email'],
        'phone'     => ['Phone',     'fa-solid fa-phone',             '01XXXXXXXXX',                        '#16A34A', 'phone'],
    ];
}

/**
 * অ্যাডমিনের দেওয়া লেখা থেকে নিরাপদ লিংক বানায়। ভুল হলে null.
 * (javascript: ইত্যাদি কখনো পাস হবে না — শুধু http/https/mailto/tel)
 */
function social_normalize_url(string $platform, string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '' || mb_strlen($raw) > 300) return null;
    $type = social_platforms()[$platform][4] ?? 'url';
    if ($type === 'email') {
        $m = preg_replace('/^mailto:/i', '', $raw);
        return filter_var($m, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $m : null;
    }
    if ($type === 'phone') {
        $d = preg_replace('/[^\d+]/', '', preg_replace('/^tel:/i', '', $raw));
        return preg_match('/^\+?\d{6,15}$/', $d) ? 'tel:' . $d : null;
    }
    if ($type === 'whatsapp' && !preg_match('#^https?://#i', $raw)) {
        $d = preg_replace('/\D/', '', $raw);
        if (strlen($d) === 11 && $d[0] === '0') $d = '88' . $d;   // 01XXXXXXXXX → 8801XXXXXXXXX
        return preg_match('/^\d{8,15}$/', $d) ? 'https://wa.me/' . $d : null;
    }
    if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $raw)) $raw = 'https://' . ltrim($raw, '/');
    if (!preg_match('#^https?://#i', $raw)) return null;
    $u = filter_var($raw, FILTER_VALIDATE_URL);
    if (!$u) return null;
    $host = parse_url($u, PHP_URL_HOST);
    return ($host && str_contains($host, '.')) ? $u : null;
}

/** @return array<int,array{p:string,url:string}> ফুটারে দেখানোর লিংক (ক্রম অনুযায়ী) */
function social_links(): array
{
    static $c = null;
    if ($c !== null) return $c;
    $raw = db()->GetSiteContent(['social_links' => ''])['social_links'];
    $c = [];
    $list = json_decode((string)$raw, true);
    if (is_array($list)) {
        $pl = social_platforms();
        foreach ($list as $it) {
            if (is_array($it) && isset($pl[$it['p'] ?? '']) && is_string($it['url'] ?? null) && $it['url'] !== '') {
                $c[] = ['p' => $it['p'], 'url' => $it['url']];
            }
        }
    }
    return $c;
}

// ============================================================
// ===== ফোন নম্বর ও ইমেইল (SMTP) সেটিং =====
// ============================================================
/** ফোন নম্বর থেকে শুধু ডিজিট; বাংলাদেশি নম্বর হলে 01XXXXXXXXX ফরম্যাটে। নম্বর না হলে '' */
function phone_digits(string $v): string
{
    $v = strtr(trim($v), ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']);
    if (!preg_match('/^\+?[\d\s\-()]{6,20}$/', $v)) return '';
    $d = preg_replace('/\D/', '', $v);
    if (str_starts_with($d, '880') && strlen($d) === 13) $d = '0' . substr($d, 3);
    return strlen($d) >= 6 ? $d : '';
}

function mail_settings_defaults(): array
{
    return [
        'mail_site_url'   => 'https://mashtermoshai.com',
        'mail_host'       => '',
        'mail_port'       => '465',
        'mail_secure'     => 'ssl',
        'mail_user'       => '',
        'mail_pass'       => '',
        'mail_from_email' => '',
        'mail_from_name'  => '',
    ];
}

function mail_settings(): array
{
    static $c = null;
    if ($c === null) $c = db()->GetSiteContent(mail_settings_defaults());
    return $c;
}

/** ইমেইলের লিংকের জন্য সাইটের ঠিকানা (Host header থেকে না — নিরাপত্তার জন্য সেটিং থেকে) */
function site_public_url(): string
{
    $u = rtrim(trim(mail_settings()['mail_site_url'] ?? ''), '/');
    return preg_match('#^https?://[^\s/]+#i', $u) ? $u : 'https://mashtermoshai.com';
}

// ============================================================
// ===== USER SUSPENSION helpers =====
// ============================================================
/** লগইন পেজে দেখানোর জন্য Suspension এর তথ্য */
function suspension_info(Obj $s): array
{
    $until = $s->IsPermanent ? null : (string)$s->SuspendedUntil;
    $ts = $until ? strtotime($until) : null;
    return [
        'reason'    => (string)($s->Reason ?? ''),
        'permanent' => (bool)$s->IsPermanent,
        'until'     => $until,
        'untilText' => $ts ? date('d M Y, h:i A', $ts) : null,
        'untilIso'  => $ts ? gmdate('Y-m-d\TH:i:s\Z', $ts) : null,
        'since'     => $s->CreatedAt ? date('d M Y, h:i A', strtotime((string)$s->CreatedAt)) : null,
    ];
}

/**
 * Tuition Circular / Find Tutor কমেন্ট এডিট-ডিলিট (POST: commentId, content)।
 * এডিট: শুধু যে লিখেছে। ডিলিট: যে লিখেছে, পোস্টের মালিক, অথবা অ্যাডমিন।
 */
function feed_comment_action(string $kind, string $action): void
{
    $me = current_user_id();
    if ($me === null) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
    $c = db()->GetFeedComment($kind, (int)(input_int('commentId', 0) ?? 0));
    if (!$c) json_response(['success' => false, 'message' => 'কমেন্টটি পাওয়া যায়নি — হয়তো আগেই মুছে গেছে।']);
    $isAdmin = current_role() === 'Admin';
    if ($action === 'edit') {
        if ($c->UserId !== $me) json_response(['success' => false, 'message' => 'শুধু নিজের কমেন্ট এডিট করা যায়।']);
        $content = trim(str_replace("\r", '', (string)input_str('content', '')));
        if ($content === '' || preg_replace('/^@\[[^\]]+\]\s*/u', '', $content) === '') json_response(['success' => false, 'message' => 'কমেন্ট খালি রাখা যাবে না।']);
        if (mb_strlen($content) > 2000) json_response(['success' => false, 'message' => 'কমেন্ট সর্বোচ্চ ২০০০ অক্ষর।']);
        if (!db()->UpdateFeedComment($kind, $c->Id, $content)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি।']);
        json_response(['success' => true, 'content' => $content, 'message' => 'কমেন্ট আপডেট হয়েছে।']);
    }
    if ($c->UserId !== $me && $c->PostOwnerId !== $me && !$isAdmin) json_response(['success' => false, 'message' => 'এই কমেন্ট মোছার অনুমতি নেই।']);
    $ids = db()->DeleteFeedCommentTree($kind, $c->Id);
    if (!$ids) json_response(['success' => false, 'message' => 'মোছা যায়নি। আবার চেষ্টা করুন।']);
    json_response(['success' => true, 'ids' => $ids, 'message' => 'কমেন্ট মুছে ফেলা হয়েছে।']);
}

/** কমেন্টের নিচে Edit / Delete বাটন (যার যেটা অনুমতি আছে) */
function feed_comment_buttons(Obj $c, int $postOwnerId): string
{
    $me = current_user_id();
    if ($me === null) return '';
    $h = '';
    if ((int)$c->UserId === $me) $h .= '<button type="button" data-cm-edit>Edit</button>';
    if ((int)$c->UserId === $me || $postOwnerId === $me || current_role() === 'Admin') $h .= '<button type="button" data-cm-del class="fbc-del">Delete</button>';
    return $h;
}


// ============================================================
// টিউশনের অপশন: লোকেশন (জেলা → এলাকা), ক্লাস, বিষয়
// অ্যাডমিন সেটিংস থেকে যোগ / বাদ দেওয়া যায় (SiteContents এ JSON হিসেবে থাকে)
// ============================================================
function default_bd_locations(): array
{
    $divisions = [
        'Dhaka' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Narsingdi', 'Manikganj', 'Munshiganj', 'Tangail', 'Kishoreganj', 'Faridpur', 'Madaripur', 'Shariatpur', 'Rajbari', 'Gopalganj'],
        'Chattogram' => ['Chattogram', "Cox's Bazar", 'Cumilla', 'Feni', 'Noakhali', 'Lakshmipur', 'Chandpur', 'Brahmanbaria', 'Rangamati', 'Khagrachhari', 'Bandarban'],
        'Rajshahi' => ['Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Natore', 'Naogaon', 'Chapainawabganj', 'Joypurhat'],
        'Khulna' => ['Khulna', 'Jashore', 'Satkhira', 'Bagerhat', 'Kushtia', 'Jhenaidah', 'Magura', 'Narail', 'Chuadanga', 'Meherpur'],
        'Barishal' => ['Barishal', 'Patuakhali', 'Bhola', 'Pirojpur', 'Barguna', 'Jhalokathi'],
        'Sylhet' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'],
        'Rangpur' => ['Rangpur', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Nilphamari', 'Lalmonirhat', 'Thakurgaon', 'Panchagarh'],
        'Mymensingh' => ['Mymensingh', 'Jamalpur', 'Sherpur', 'Netrokona'],
    ];
    $areas = [
        'Dhaka' => ['Dhanmondi', 'Mirpur', 'Uttara', 'Mohammadpur', 'Gulshan', 'Banani', 'Bashundhara', 'Badda', 'Rampura', 'Motijheel', 'Farmgate', 'Tejgaon',
            'Khilgaon', 'Jatrabari', 'Old Dhaka', 'Lalbagh', 'Wari', 'Shyamoli', 'Malibagh', 'Mohakhali', 'Banasree', 'Khilkhet', 'Savar', 'Keraniganj'],
        'Chattogram' => ['Chawkbazar', 'Agrabad', 'Nasirabad', 'Khulshi', 'GEC', 'Panchlaish', 'Halishahar', 'Bahaddarhat', 'Muradpur', 'Pahartali', 'Kotwali',
            'Patenga', 'Chandgaon', 'Hathazari', 'Oxygen', 'Lalkhan Bazar'],
        'Gazipur' => ['Tongi', 'Joydebpur', 'Board Bazar', 'Chandra', 'Kaliakair'],
        'Narayanganj' => ['Chashara', 'Fatullah', 'Siddhirganj', 'Rupganj'],
        'Cumilla' => ['Kandirpar', 'Tomsom Bridge', 'Kotbari', 'Laksam'],
        "Cox's Bazar" => ['Kolatoli', 'Teknaf', 'Ukhia'],
        'Sylhet' => ['Zindabazar', 'Amberkhana', 'Shibganj', 'Subidbazar', 'Tilagor', 'Mirabazar'],
        'Rajshahi' => ['Shaheb Bazar', 'Kazla', 'Binodpur', 'Laxmipur', 'Talaimari'],
        'Khulna' => ['Sonadanga', 'Boyra', 'Khalishpur', 'Daulatpur', 'Gollamari'],
        'Barishal' => ['Nathullabad', 'Band Road', 'Rupatali', 'Chowmatha'],
        'Rangpur' => ['Jahaj Company More', 'Modern More', 'Lalbag (Rangpur)'],
        'Mymensingh' => ['Ganginar Par', 'Charpara', 'Kachari'],
        'Bogura' => ['Satmatha', 'Sherpur (Bogura)'],
    ];
    $out = ['Online' => []];
    foreach ($divisions as $districts) foreach ($districts as $d) $out[$d] = $areas[$d] ?? [];
    return $out;
}

function default_tuition_classes(): array
{
    return ['Play / Nursery', 'KG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'SSC',
        'Class 11', 'Class 12', 'HSC', 'O Level', 'A Level', 'University Admission', 'Medical Admission', 'Engineering Admission', 'Diploma',
        'University (Honours)', 'Masters', 'IELTS', 'Job Preparation'];
}

function default_tuition_subjects(): array
{
    return ['All Subjects', 'Bangla', 'English', 'Mathematics', 'General Math', 'Higher Math', 'Science', 'Physics', 'Chemistry', 'Biology', 'ICT',
        'Bangladesh & Global Studies', 'Religion', 'Islam Studies', 'Hindu Religion', 'Accounting', 'Finance & Banking', 'Business Studies', 'Economics',
        'Agriculture', 'Geography', 'History', 'Civics', 'Statistics', 'Arabic', 'Quran', 'Computer Science', 'Programming', 'Spoken English', 'IELTS',
        'Drawing', 'Music'];
}

/** @return array{locations: array<string,string[]>, classes: string[], subjects: string[]} */
function tuition_options(): array
{
    static $c = null;
    if ($c !== null) return $c;
    $raw = db()->GetSiteContent(['opt_locations' => '', 'opt_classes' => '', 'opt_subjects' => '']);
    $dec = function ($v) { $j = json_decode((string)$v, true); return is_array($j) ? $j : null; };
    $loc = $dec($raw['opt_locations']);
    $c = [
        'locations' => is_array($loc) && $loc ? $loc : default_bd_locations(),
        'classes' => $dec($raw['opt_classes']) ?: default_tuition_classes(),
        'subjects' => $dec($raw['opt_subjects']) ?: default_tuition_subjects(),
    ];
    return $c;
}

/**
 * লোকেশনের <select> অপশন (জেলা অনুযায়ী গ্রুপ করা)। $extra = তালিকায় নেই এমন মান (পোস্ট/টিউটরের)।
 */
function location_select_options(string $selected = '', array $extra = []): string
{
    $html = '';
    $known = [];
    foreach (tuition_options()['locations'] as $district => $areas) {
        $known[mb_strtolower($district)] = true;
        if (!$areas) {
            $html .= '<option value="' . e($district) . '"' . (strcasecmp($selected, $district) === 0 ? ' selected' : '') . '>' . e($district) . '</option>';
            continue;
        }
        $html .= '<optgroup label="' . e($district) . '">';
        $html .= '<option value="' . e($district) . '"' . (strcasecmp($selected, $district) === 0 ? ' selected' : '') . '>' . e($district) . ' (সব এলাকা)</option>';
        foreach ($areas as $a) {
            $known[mb_strtolower($a)] = true;
            $html .= '<option value="' . e($a) . '"' . (strcasecmp($selected, $a) === 0 ? ' selected' : '') . '>' . e($a) . '</option>';
        }
        $html .= '</optgroup>';
    }
    $other = array_values(array_filter(array_unique($extra), fn($x) => $x !== '' && !isset($known[mb_strtolower($x)])));
    if ($selected !== '' && !isset($known[mb_strtolower($selected)]) && !in_array($selected, $other, true)) $other[] = $selected;
    if ($other) {
        $html .= '<optgroup label="অন্যান্য">';
        foreach ($other as $o) $html .= '<option value="' . e($o) . '"' . (strcasecmp($selected, $o) === 0 ? ' selected' : '') . '>' . e($o) . '</option>';
        $html .= '</optgroup>';
    }
    return $html;
}

/** লোকেশনের datalist অপশন ("এলাকা, জেলা") — লেখার ঘরে সাজেশন */
function location_datalist_options(): string
{
    $html = '';
    foreach (tuition_options()['locations'] as $district => $areas) {
        $html .= '<option value="' . e($district) . '">';
        foreach ($areas as $a) $html .= '<option value="' . e($a . ', ' . $district) . '">';
    }
    return $html;
}


// ============================================================
// ===== প্যাকেজ / Subscription =====
// ============================================================
/** ইংরেজি সংখ্যা → বাংলা সংখ্যা */
function bn_num($n): string { return strtr((string)$n, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']); }

/** ৳1,000 আকারে টাকা */
function taka($n): string { return '৳' . number_format((int)$n); }

/** প্যাকেজের মেয়াদ/পরিমাণ লেখা: "১ মাস", "১২ মাস", "১টি পোস্ট", "৩টি পোস্ট · ১ মাস মেয়াদ" */
function package_duration_label($p): string
{
    $m = (int)($p->DurationMonths ?? 0); $l = (int)($p->PostLimit ?? 0);
    if (($p->Audience ?? '') === 'Student') {
        $t = $l > 0 ? bn_num($l) . 'টি পোস্ট' : 'আনলিমিটেড পোস্ট';
        return $m > 0 ? $t . ' · ' . bn_num($m) . ' মাস মেয়াদ' : $t;
    }
    return $m > 0 ? bn_num($m) . ' মাস' : 'আজীবন';
}
