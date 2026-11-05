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
    // অ্যাডমিনের কাজ সফল হলে Activity Log এ লেখা (AdminController সেট করে)
    if (isset($GLOBALS['__on_json_success']) && is_callable($GLOBALS['__on_json_success']) && is_array($data) && ($data['success'] ?? false) === true) {
        $cb = $GLOBALS['__on_json_success']; unset($GLOBALS['__on_json_success']);
        try { $cb($data); } catch (Throwable $e) { error_log('[json success hook] ' . $e->getMessage()); }
    }
    // Meta Pixel: এই রিকোয়েস্টে কোনো ইভেন্ট হলে JSON এর সাথে পাঠানো হয় — ব্রাউজারে লেআউটের স্ক্রিপ্ট fbq() চালায়
    if (is_array($data) && ($px = pixel_take()) !== []) $data['_px'] = $px;
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo mask_teacher_photos((string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION));
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
function package_banner_defaults(): array
{
    return [
        'pk_banner'         => '',
        'pk_banner_enabled' => '0',
        'pk_banner_text'    => '0',
    ];
}
function package_banner(): array
{
    static $c = null;
    if ($c === null) {
        try {
            $c = db()->GetSiteContent(package_banner_defaults() + ['pk_noimg_v1' => '']);
            // অ্যাডমিনের কথামতো: প্যাকেজ পেজে ছবি নয়, শুধু লেখা (একবারই বন্ধ করা হয়; পরে অ্যাডমিন চাইলে আবার চালু করতে পারে)
            if ($c['pk_noimg_v1'] === '') { db()->SaveSiteContent(['pk_banner_enabled' => '0', 'pk_noimg_v1' => '1']); $c['pk_banner_enabled'] = '0'; }
        } catch (Throwable $e) { $c = package_banner_defaults(); }
    }
    return $c;
}
/**
 * হোম পেজের Key Features — ৬টা কার্ড। লেখা এখানে ঠিক করা; ছবি Admin → Settings → Home Page থেকে বদলানো যায় (kf_img_1..6)।
 * ছবি খালি থাকলে কার্ডে আইকন সহ রঙিন প্যানেল দেখায়।
 */
function key_features_defaults(): array
{
    return [
        1 => ['Verified Tutors', 'Connect with verified tutors you can trust.', 'Tutor profiles are verified to help students find reliable teachers.', 'fa-user-check', 'https://i.ibb.co.com/S40QJtbp/AI-Tutor.webp'],
        2 => ['Smart Tutor Search', 'Find tutors based on your needs.', 'Search by subject, class, curriculum, location, schedule and budget.', 'fa-magnifying-glass', ''],
        3 => ['Tuition Circular', 'Explore available tuition opportunities.', 'Browse tuition posts and find opportunities that match your requirements.', 'fa-bullhorn', 'https://i.ibb.co.com/dJKpVzDX/Adaptive-Learning.png'],
        4 => ['Post Your Tuition Requirement', 'Tell us what you need.', 'Post your tuition requirements and let suitable tutors discover your request.', 'fa-pen-to-square', ''],
        5 => ['Mutual Matching', 'Connect only when both sides agree.', 'Students and tutors can show interest, and communication opens after mutual acceptance.', 'fa-handshake', ''],
        6 => ['Secure Communication & Support', 'Learn and teach with confidence.', 'Get platform support throughout the matching and communication process.', 'fa-shield-halved', 'https://i.ibb.co.com/pB4DZS31/Progress-Tracking.png'],
    ];
}
/** @return array<int, array{title:string, tagline:string, desc:string, icon:string, image:string, defaultImage:string}> */
function key_features(): array
{
    static $c = null;
    if ($c !== null) return $c;
    $def = key_features_defaults(); $keys = [];
    foreach ($def as $i => $d) $keys['kf_img_' . $i] = $d[4];
    try { $saved = db()->GetSiteContent($keys); } catch (Throwable $e) { $saved = $keys; }
    $c = [];
    foreach ($def as $i => $d) {
        $c[$i] = ['title' => $d[0], 'tagline' => $d[1], 'desc' => $d[2], 'icon' => $d[3], 'image' => trim((string)($saved['kf_img_' . $i] ?? $d[4])), 'defaultImage' => $d[4]];
    }
    return $c;
}

/** Help Desk পেজের উপরের ব্যানার (ছবির ভেতরেই "Help Desk" লেখা আর ইলাস্ট্রেশন থাকে; বন্ধ করলে লেখার হেডার দেখায়) */
function helpdesk_banner_defaults(): array
{
    return [
        'hd_banner'         => 'https://i.ibb.co.com/MxqKZ182/Master-Moshai-Help-Desk-Banner.png',
        'hd_banner_enabled' => '1',
    ];
}
function helpdesk_banner(): array
{
    static $c = null;
    if ($c === null) { try { $c = db()->GetSiteContent(helpdesk_banner_defaults()); } catch (Throwable $e) { $c = helpdesk_banner_defaults(); } }
    return $c;
}

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
        if ($l === 0 && $m > 0) return bn_num($m) . ' মাস মেয়াদ';
        $t = $l > 0 ? bn_num($l) . 'টি পোস্ট' : 'আনলিমিটেড পোস্ট';
        return $m > 0 ? $t . ' · ' . bn_num($m) . ' মাস মেয়াদ' : $t;
    }
    return $m > 0 ? bn_num($m) . ' মাস' : 'আজীবন';
}

// ============================================================
// ===== পেমেন্ট মেথডের লোগো =====
// ============================================================
/**
 * মেথডের লোগো: অ্যাডমিন লোগো আপলোড করলে সেই ছবি, না হলে মেথডের রঙে নামসহ ব্যাজ।
 * $size = px (উচ্চতা)
 */
function payment_logo_html($m, int $size = 40): string
{
    $name = (string)($m->Name ?? '');
    if (!is_blank($m->LogoUrl ?? null)) {
        return '<span class="pm-logo img" style="height:' . $size . 'px;"><img src="' . e($m->LogoUrl) . '" alt="' . e($name) . '" loading="lazy" /></span>';
    }
    $icon = ['bank' => 'fa-building-columns', 'mobile' => 'fa-mobile-screen-button'][$m->Kind ?? ''] ?? 'fa-wallet';
    $color = preg_match('/^#[0-9A-Fa-f]{6}$/', (string)($m->Color ?? '')) ? $m->Color : '#475569';
    return '<span class="pm-logo" style="--pm:' . e($color) . ';height:' . $size . 'px;font-size:' . max(11, (int)round($size * 0.36)) . 'px;">'
        . '<i class="fas ' . $icon . '"></i><b>' . e($name) . '</b></span>';
}

/** লোগো ব্যাজের CSS (একবার পেজে বসাতে হয়) */
function payment_logo_css(): string
{
    return '<style>.pm-logo{display:inline-flex;align-items:center;gap:.45em;padding:0 .8em;border-radius:10px;background:var(--pm);color:#fff;font-weight:800;letter-spacing:.2px;white-space:nowrap;line-height:1;}'
        . '.pm-logo i{font-size:.95em;opacity:.95;}.pm-logo.img{padding:0;background:none;}.pm-logo.img img{height:100%;width:auto;max-width:140px;object-fit:contain;display:block;}</style>';
}

// ============================================================
// ===== নীল টিক (Subscription চালু থাকা শিক্ষক) =====
// ============================================================
/** কোন কোন শিক্ষকের Subscription চালু — এক রিকোয়েস্টে একবারই ডাটাবেস থেকে আনে */
function premium_teacher_map(): array
{
    static $map = null;
    if ($map === null) { try { $map = db()->GetActiveTeacherSubscriptionMap(); } catch (Throwable $e) { $map = []; } }
    return $map;
}

function is_premium_teacher($userId): bool { return $userId !== null && isset(premium_teacher_map()[(int)$userId]); }

/** নীল ভেরিফায়েড টিক (ঢেউ খেলানো ব্যাজের ভিতরে সাদা টিক) */
function blue_tick_html(string $title = 'Verified Teacher — Subscription চালু', string $cls = ''): string
{
    return '<span class="blue-tick' . ($cls !== '' ? ' ' . e($cls) : '') . '" title="' . e($title) . '" aria-label="' . e($title) . '" role="img">'
        . '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#1D9BF0" d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81C14.67 2.63 13.43 1.75 12 1.75s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91C2.63 9.33 1.75 10.57 1.75 12s.88 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"/>'
        . '<path fill="#fff" d="M10.54 16.2 6.8 12.46l1.41-1.41 2.26 2.26 4.8-5.23 1.47 1.36z"/></svg></span>';
}

/** নীল টিকের CSS — লেআউটে একবার বসানো আছে */
function blue_tick_css(): string
{
    return '<style>.blue-tick{display:inline-flex;width:1.05em;height:1.05em;vertical-align:-0.14em;flex-shrink:0;margin-left:.2em}.blue-tick svg{width:100%;height:100%;display:block}</style>';
}

// ============================================================
// ===== প্যাকেজের বিস্তারিত (সব প্যাকেজে যা পাবেন) — অ্যাডমিন এডিট করে =====
// ============================================================
function package_benefit_icons(): array
{
    return ['fa-certificate', 'fa-arrow-trend-up', 'fa-bullhorn', 'fa-clipboard-list', 'fa-users', 'fa-headset', 'fa-star', 'fa-bolt', 'fa-shield-halved',
        'fa-chalkboard-user', 'fa-user-graduate', 'fa-pen-to-square', 'fa-location-dot', 'fa-coins', 'fa-comments', 'fa-bell', 'fa-eye', 'fa-gift', 'fa-crown', 'fa-handshake'];
}

function package_benefits_defaults(): array
{
    return [
        'Teacher' => [
            ['i' => 'fa-certificate', 't' => 'প্রোফাইলে নীল টিক', 'd' => 'আপনার প্রোফাইল আর Find Tutor কার্ডে নীল ভেরিফায়েড টিক দেখাবে — শিক্ষার্থী ও অভিভাবকরা বেশি ভরসা পাবেন।'],
            ['i' => 'fa-arrow-trend-up', 't' => 'Find Tutor-এ আগে দেখাবে', 'd' => 'Subscription থাকা শিক্ষকরা টিউটর তালিকার উপরের দিকে দেখান, তাই বেশি শিক্ষার্থী আপনাকে খুঁজে পাবে।'],
            ['i' => 'fa-bullhorn', 't' => 'Teacher Wanted পোস্ট', 'd' => 'নিজের বিষয়, এলাকা আর বেতন জানিয়ে “পড়াতে চাই” পোস্ট করতে পারবেন।'],
            ['i' => 'fa-clipboard-list', 't' => 'Tuition Circular-এ Apply', 'd' => 'শিক্ষার্থীদের Tuition পোস্ট দেখে সরাসরি Apply ও যোগাযোগ করতে পারবেন।'],
            ['i' => 'fa-users', 't' => 'স্টুডেন্ট ও ক্লাস ম্যানেজমেন্ট', 'd' => 'নিজের স্টুডেন্ট, ক্লাসের সময় আর মাসিক পেমেন্টের হিসাব এক জায়গায় রাখুন।'],
            ['i' => 'fa-headset', 't' => 'অগ্রাধিকার সাপোর্ট', 'd' => 'কোনো সমস্যা হলে Helpdesk-এ আপনার প্রশ্ন আগে দেখা হবে।'],
        ],
        'Student' => [
            ['i' => 'fa-address-card', 't' => 'টিচারের ছবি ও যোগাযোগ', 'd' => 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর আর ইমেইল দেখতে পারবেন।'],
            ['i' => 'fa-paper-plane', 't' => 'Direct Request', 'd' => 'পছন্দের টিচারকে সরাসরি টিউশনের রিকোয়েস্ট পাঠাতে পারবেন।'],
            ['i' => 'fa-pen-to-square', 't' => 'Tuition Circular এ পোস্ট', 'd' => 'কোন বিষয়, কোন ক্লাস, কোথায়, বাজেট — জানিয়ে যত খুশি টিউশন পোস্ট করুন, যাচাই করা টিচাররা Apply করবেন।'],
            ['i' => 'fa-gift', 't' => 'ফ্রিতে যা পাবেন', 'd' => 'Edu Feed দেখা ও লাইক, টিচারদের তথ্য ও প্রোফাইল দেখা — Account খোলা সবসময় ফ্রি।'],
        ],
    ];
}

/** @return array{Teacher: array, Student: array} */
function package_benefits(): array
{
    $def = package_benefits_defaults();
    $out = $def;
    try {
        $v = db()->GetSiteContent(['pkg_benefits_teacher' => '', 'pkg_benefits_student' => '']);
        foreach (['Teacher' => 'pkg_benefits_teacher', 'Student' => 'pkg_benefits_student'] as $aud => $k) {
            if (($v[$k] ?? '') === '') continue;
            $list = json_decode((string)$v[$k], true);
            if (is_array($list)) $out[$aud] = array_values(array_filter($list, fn($b) => is_array($b) && ($b['t'] ?? '') !== ''));
        }
    } catch (Throwable $e) { }
    return $out;
}


// ============================================================
// হোম পেজের "Find Tutors by Subject / Class / Location" কার্ড
// প্রতিটা কার্ডের একটা slug আছে — /FindTutor?subject=mathematics এভাবে লিংক হয়।
// পরে আলাদা সাবজেক্ট পেজ বানালে এই slug দিয়েই বানানো যাবে।
// ============================================================
/** @return array<string, array{0:string,1:string,2:string[]}> slug => [লেবেল, আইকন, মিলবে এমন মান] */
function tutor_subject_groups(): array
{
    return [
        'mathematics' => ['Mathematics Tutor', 'fa-square-root-variable', ['Mathematics', 'Math', 'Maths', 'General Math', 'Higher Math']],
        'english'     => ['English Tutor', 'fa-spell-check', ['English', 'Spoken English']],
        'physics'     => ['Physics Tutor', 'fa-atom', ['Physics']],
        'chemistry'   => ['Chemistry Tutor', 'fa-flask', ['Chemistry']],
        'biology'     => ['Biology Tutor', 'fa-dna', ['Biology']],
        'ict'         => ['ICT Tutor', 'fa-laptop-code', ['ICT', 'Computer Science', 'Programming']],
        'bangla'      => ['Bangla Tutor', 'fa-book-open', ['Bangla', 'Bengali']],
        'accounting'  => ['Accounting Tutor', 'fa-calculator', ['Accounting']],
    ];
}

function tutor_class_groups(): array
{
    return [
        'primary'      => ['Primary School Tutor', 'fa-shapes', ['Play / Nursery', 'Nursery', 'KG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Primary']],
        'class-6-8'    => ['Class 6–8 Tutor', 'fa-book', ['Class 6', 'Class 7', 'Class 8']],
        'ssc'          => ['SSC Tutor', 'fa-school', ['SSC', 'Class 9', 'Class 10']],
        'hsc'          => ['HSC Tutor', 'fa-building-columns', ['HSC', 'Class 11', 'Class 12']],
        'university'   => ['University Tutor', 'fa-graduation-cap', ['University (Honours)', 'University', 'Honours', 'Masters']],
        'admission'    => ['Admission Tutor', 'fa-door-open', ['University Admission', 'Medical Admission', 'Engineering Admission', 'Admission']],
        'professional' => ['Professional Course Tutor', 'fa-briefcase', ['IELTS', 'Job Preparation', 'Diploma', 'Professional Course']],
    ];
}

/** লোকেশন কার্ড: slug => [নাম, ওই জেলা + তার এলাকাগুলো] */
function tutor_location_groups(): array
{
    $locs = tuition_options()['locations'];
    $out = [];
    foreach (['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Cumilla', 'Mymensingh', 'Rangpur', 'Barishal'] as $d) {
        $areas = [];
        foreach ($locs as $k => $list) if (strcasecmp($k, $d) === 0) $areas = (array)$list;
        $alias = ['Chattogram' => ['Chittagong'], 'Cumilla' => ['Comilla'], 'Barishal' => ['Barisal']][$d] ?? [];
        $out[strtolower($d)] = [$d, array_values(array_unique(array_merge([$d], $alias, $areas)))];
    }
    return $out;
}

/** টিউটর (GetTutorDirectory এর একটা) এই মানগুলোর কোনোটার সাথে মেলে কিনা — FindTutor পেজের JS এর মতো একই নিয়ম */
function tutor_matches_group(Obj $t, string $field, array $values): bool
{
    $vals = array_map(fn($v) => mb_strtolower(trim((string)$v)), $values);
    if ($field === 'location') {
        $loc = mb_strtolower((string)$t->Location);
        foreach ($vals as $v) if ($v !== '' && str_contains($loc, $v)) return true;
        return false;
    }
    $list = $field === 'subject' ? (array)$t->Subjects : array_merge((array)$t->Levels, (array)$t->Mediums);
    foreach ($list as $x) if (in_array(mb_strtolower(trim((string)$x)), $vals, true)) return true;
    return false;
}

/** Find Tutor পেজে ?subject= / ?class= / ?location= দিয়ে এলে আগে থেকে বাছাই করা ফিল্টার: field => [value, label] */
function find_tutor_preset(): array
{
    $out = [];
    $s = strtolower(trim((string)input_str('subject', '')));
    if ($s !== '') {
        $g = tutor_subject_groups()[$s] ?? null;
        $out['subject'] = $g ? [implode('|', $g[2]), preg_replace('/ Tutor$/', '', $g[0]) . ' (all)'] : [mb_substr(trim((string)input_str('subject')), 0, 40), mb_substr(trim((string)input_str('subject')), 0, 40)];
    }
    $c = strtolower(trim((string)input_str('class', '')));
    if ($c !== '') {
        $g = tutor_class_groups()[$c] ?? null;
        $out['class'] = $g ? [implode('|', $g[2]), preg_replace('/ Tutor$/', '', $g[0]) . ' (all)'] : [mb_substr(trim((string)input_str('class')), 0, 40), mb_substr(trim((string)input_str('class')), 0, 40)];
    }
    // ক্যাটাগরি পেজের "See All" → /FindTutor?cat={slug}
    $cat = strtolower(trim((string)input_str('cat', '')));
    if ($cat !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $cat)) {
        try {
            $co = db()->GetCategoryBySlug($cat);
            if ($co && $co->KeywordList) {
                $key = $co->MatchType === 'subject' ? 'subject' : 'class';
                $out[$key] = [implode('|', $co->KeywordList), mb_substr($co->Name, 0, 40) . ' (category)'];
            }
        } catch (Throwable $e) { error_log('[find_tutor_preset cat] ' . $e->getMessage()); }
    }
    $l = trim((string)input_str('location', ''));
    if ($l !== '') {
        $g = tutor_location_groups()[strtolower($l)] ?? null;
        $out['location'] = $g ? [$g[0], $g[0]] : [mb_substr($l, 0, 40), mb_substr($l, 0, 40)];
    }
    return $out;
}

/** হোম পেজের FAQ (FAQ সেকশন আর Google এর FAQ schema দুই জায়গাতেই ব্যবহার হয়) */
/** হোম পেজের FAQ — ডিফল্ট প্রশ্ন-উত্তর (অ্যাডমিন কিছু সেভ না করলে এগুলো দেখায়) */
function home_faq_defaults(): array
{
    return [
        ['How can I find a tutor in Bangladesh?', 'You can search for tutors based on subject, class, curriculum and location through মাষ্টার মশাই.'],
        ['How can I find a home tutor near me?', 'You can explore tutors based on your preferred location and subject requirements.'],
        ['Can I find a Mathematics tutor through মাষ্টার মশাই?', 'Yes. You can search for tutors based on subjects such as Mathematics, English, Physics, Chemistry and more.'],
        ['Can I post a tuition requirement?', 'Students and parents can post their tuition requirements through the Tuition Circular and receive suitable tutor applications.'],
        ['How can a tutor join মাষ্টার মশাই?', 'Tutors can create a profile and use the available tutor services to find suitable tuition opportunities.'],
    ];
}

/** FAQ সেটিং: ['enabled' => bool, 'items' => [[প্রশ্ন, উত্তর], ...]] — Admin → Settings থেকে বদলানো যায় */
function home_faq_settings(): array
{
    static $c = null;
    if ($c !== null) return $c;
    $items = home_faq_defaults();
    $enabled = true;
    try {
        $v = db()->GetSiteContent(['home_faqs' => '', 'home_faq_enabled' => '1']);
        $enabled = ($v['home_faq_enabled'] ?? '1') !== '0';
        if (($v['home_faqs'] ?? '') !== '') {
            $list = json_decode((string)$v['home_faqs'], true);
            if (is_array($list)) {
                $items = [];
                foreach ($list as $f) {
                    if (!is_array($f)) continue;
                    $q = trim((string)($f['q'] ?? '')); $a = trim((string)($f['a'] ?? ''));
                    if ($q !== '' && $a !== '') $items[] = [$q, $a];
                }
            }
        }
    } catch (Throwable $e) { }
    return $c = ['enabled' => $enabled, 'items' => $items];
}

/** হোম পেজে যে FAQ গুলো দেখাবে (বন্ধ থাকলে খালি) */
function home_faqs(): array
{
    $s = home_faq_settings();
    return $s['enabled'] ? $s['items'] : [];
}

// ============================================================
// ===== টিউশন ক্যাটাগরি (Categories পেজ) =====
// ============================================================

/** URL-এর জন্য slug: "A Level ( AS )" → "a-level-as" */
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = str_replace('&', ' and ', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

/**
 * প্রথমবার যে ক্যাটাগরিগুলো বসে: গ্রুপ => [[নাম, আইকন, মেলানোর ধরন (class|subject), টিউটর মেলানোর শব্দ[]], ...]
 * ছবি নেই — অ্যাডমিন Admin → Categories থেকে প্রতিটার ছবি দিতে পারবে (ততক্ষণ আইকন দেখায়)।
 */
function category_seed_data(): array
{
    $std = [];
    for ($i = 1; $i <= 9; $i++) $std[] = ["Standard $i", ['fa-pencil', 'fa-book-open', 'fa-shapes', 'fa-ruler', 'fa-flask', 'fa-globe', 'fa-calculator', 'fa-microscope', 'fa-atom'][$i - 1], 'class', ["Class $i", "Standard $i"]];
    $cls = [];
    for ($i = 1; $i <= 10; $i++) $cls[] = ["Class $i", ['fa-pencil', 'fa-book-open', 'fa-shapes', 'fa-ruler', 'fa-flask', 'fa-globe', 'fa-calculator', 'fa-microscope', 'fa-atom', 'fa-school'][$i - 1], 'class', ["Class $i"]];
    return [
        'English Medium' => array_merge([
            ['Pre-Schooling', 'fa-child-reaching', 'class', ['Play / Nursery', 'Pre-Schooling']],
            ['Play', 'fa-cubes', 'class', ['Play / Nursery', 'Play']],
            ['Nursery', 'fa-baby', 'class', ['Play / Nursery', 'Nursery']],
            ['KG', 'fa-shapes', 'class', ['KG']],
        ], $std, [
            ['O Level', 'fa-graduation-cap', 'class', ['O Level']],
            ['A Level ( AS )', 'fa-user-graduate', 'class', ['A Level', 'AS Level']],
            ['A Level ( A2 )', 'fa-user-graduate', 'class', ['A Level', 'A2 Level']],
        ]),
        'Bangla Medium' => array_merge([
            ['Pre-Schooling', 'fa-child-reaching', 'class', ['Play / Nursery', 'Pre-Schooling']],
            ['Play', 'fa-cubes', 'class', ['Play / Nursery', 'Play']],
            ['Nursery', 'fa-baby', 'class', ['Play / Nursery', 'Nursery']],
            ['KG', 'fa-shapes', 'class', ['KG']],
        ], $cls, [
            ['HSC 1st Year', 'fa-building-columns', 'class', ['Class 11', 'HSC']],
            ['HSC 2nd Year', 'fa-building-columns', 'class', ['Class 12', 'HSC']],
        ]),
        'Arts & Crafts' => [
            ['Crafting', 'fa-scissors', 'subject', ['Crafting', 'Arts & Crafts']],
            ['Dance', 'fa-person-running', 'subject', ['Dance']],
            ['Instrumental Music', 'fa-drum', 'subject', ['Instrumental Music', 'Music']],
            ['Music', 'fa-music', 'subject', ['Music', 'Singing']],
            ['Drawing & Painting', 'fa-palette', 'subject', ['Drawing', 'Painting', 'Drawing & Painting']],
            ['Handwriting', 'fa-pen-nib', 'subject', ['Handwriting']],
            ['Guitar', 'fa-guitar', 'subject', ['Guitar']],
            ['Make Up Tutoring', 'fa-wand-magic-sparkles', 'subject', ['Make Up', 'Makeup']],
            ['Miming', 'fa-masks-theater', 'subject', ['Miming', 'Mime']],
            ['Film Direction', 'fa-film', 'subject', ['Film Direction', 'Film Making']],
            ['Reciting', 'fa-microphone', 'subject', ['Recitation', 'Reciting']],
            ['Acting & Modeling', 'fa-star', 'subject', ['Acting', 'Modeling']],
        ],
        'Religious & Moral Studies' => [
            ['Islamic Studies', 'fa-moon', 'subject', ['Islam Studies', 'Islamic Studies', 'Quran', 'Arabic']],
            ['Hinduism Studies', 'fa-om', 'subject', ['Hindu Religion', 'Hinduism']],
            ['Buddhism Studies', 'fa-dharmachakra', 'subject', ['Buddhism', 'Buddhist Religion']],
            ['Christianity Studies', 'fa-cross', 'subject', ['Christianity', 'Christian Religion']],
        ],
        'Admission Help' => [
            ['Public University Admission', 'fa-building-columns', 'class', ['University Admission', 'Public University Admission']],
            ['Private University Admission', 'fa-building', 'class', ['University Admission', 'Private University Admission']],
            ['Medical College Admission', 'fa-user-doctor', 'class', ['Medical Admission']],
            ['Engineering University Admission', 'fa-gears', 'class', ['Engineering Admission']],
            ['School Admission Test', 'fa-school', 'class', ['School Admission']],
            ['IBA Admission Test', 'fa-chart-line', 'class', ['IBA Admission', 'IBA']],
            ['MBA Admission Help', 'fa-briefcase', 'class', ['MBA Admission', 'MBA']],
            ['Cadet Admission Help', 'fa-person-military-rifle', 'class', ['Cadet Admission', 'Cadet College']],
        ],
        'Int. Exam Preparation' => [
            ['IELTS', 'fa-language', 'class', ['IELTS']],
            ['TOEFL', 'fa-comments', 'class', ['TOEFL']],
            ['GMAT', 'fa-chart-pie', 'class', ['GMAT']],
            ['GRE', 'fa-award', 'class', ['GRE']],
            ['SAT', 'fa-file-pen', 'class', ['SAT']],
            ['GED', 'fa-certificate', 'class', ['GED']],
        ],
    ];
}

/** গ্রুপের নাম দেখে ডিফল্ট লেখার ধরন */
function category_kind(string $groupName): string
{
    $g = strtolower($groupName);
    if (str_contains($g, 'art') || str_contains($g, 'craft') || str_contains($g, 'music')) return 'arts';
    if (str_contains($g, 'relig') || str_contains($g, 'moral')) return 'religion';
    if (str_contains($g, 'admission')) return 'admission';
    if (str_contains($g, 'exam') || str_contains($g, 'test prep')) return 'exam';
    return 'school';
}

/**
 * ক্যাটাগরির লেখা খালি থাকলে যে ডিফল্ট লেখা দেখায় (অ্যাডমিন এডিট পেজেও এগুলো আগে থেকে বসানো থাকে)।
 * @return array{Highlights:string, About:string, WhyChoose:string}
 */
function category_default_texts(string $name, string $groupName): array
{
    $brand = 'মাষ্টার মশাই';
    $kind = category_kind($groupName);
    $full = in_array($kind, ['school'], true) ? trim($groupName . ' ' . $name) : $name;
    $hl = [
        'school'    => ['Strong Foundation In Core Subjects.', 'Homework Help & Exam Preparation.', 'Concept-Based, Easy Learning.', 'Regular Progress Tracking.'],
        'arts'      => ['Step-By-Step Practical Lessons.', 'Creativity & Confidence Building.', 'Learn At Your Own Pace.', 'Flexible Home Or Online Classes.'],
        'religion'  => ['Correct Reading & Pronunciation.', 'Understanding Of Core Teachings.', 'Moral & Ethical Values.', 'Patient & Respectful Tutors.'],
        'admission' => ['Complete Syllabus Guideline.', 'Model Tests & Question Solving.', 'Time Management Strategy.', 'Weak Area Improvement.'],
        'exam'      => ['Section-Wise Preparation.', 'Mock Tests With Feedback.', 'Score Improvement Strategy.', 'Flexible Study Schedule.'],
    ][$kind];
    $about = [
        'school' => "## Are You Looking For An Experienced $full Tutor?\n**$brand** helps you find verified, experienced tutors for **$full**. Our tutors understand the curriculum and teach in a way that matches your child's pace and learning style.\n\n## How A $name Tutor Helps\n- Clear understanding of every chapter\n- Help with homework and class tests\n- Regular revision and exam preparation\n- Better reading, writing and problem-solving skills\n- Confidence and good study habits\n\n## Why Is A Good Tutor Important?\nEvery student learns differently. A dedicated tutor gives personal attention, finds weak areas early and keeps the student motivated — so learning becomes easier and more enjoyable for the whole family.",
        'arts' => "## Want To Learn $name From An Experienced Tutor?\n**$brand** connects you with skilled tutors for **$name**. Whether you are a beginner or want to improve further, you can learn step by step at home or online.\n\n## What You Can Expect\n- Practical, hands-on lessons\n- Proper basics and correct techniques\n- Regular practice with feedback\n- Projects or performances to build confidence\n\n## Who Is It For?\nChildren, students and adults — anyone who wants to explore creativity, develop a new skill or turn a hobby into a passion.",
        'religion' => "## Looking For A Trusted $name Tutor?\n**$brand** helps you find caring and knowledgeable tutors for **$name**. Learning is done with respect, patience and attention to correct understanding.\n\n## What Students Learn\n- Correct reading and pronunciation\n- Core teachings and their meaning\n- Daily practices and moral values\n- Good character and discipline\n\n## Why Choose A Personal Tutor?\nA personal tutor can teach at the student's own pace, answer questions clearly and guide the student with care — at home or online.",
        'admission' => "## Preparing For $name?\n**$brand** helps you find experienced tutors who know the admission test pattern well. With the right guideline and regular practice, you can prepare with confidence.\n\n## How The Tutor Helps\n- Complete syllabus and preparation plan\n- Previous year question analysis\n- Regular model tests and solutions\n- Time management and exam strategy\n- Extra care on weak topics\n\n## Start Early, Prepare Smart\nAdmission tests are competitive. A focused tutor keeps you on track, measures your progress and helps you reach your target institution.",
        'exam' => "## Planning To Take $name?\n**$brand** connects you with tutors who have strong experience in **$name** preparation. Get a clear study plan and practise every section with expert guidance.\n\n## What The Preparation Covers\n- Section-wise lessons and practice\n- Full-length mock tests\n- Detailed feedback on mistakes\n- Tips to improve your score quickly\n\n## Flexible Learning\nStudy at home or online, at a time that suits you — ideal for students and working professionals.",
    ][$kind];
    $why = "**$brand** — we bring you experienced tutors for **$full**. Through proven teaching methods, students learn faster, understand better and build real confidence.\n\n## Why Choose Us?\n- Verified Tutors In Your Area.\n- Subject Specialist Tutors.\n- Female Tutors Available.\n- Home & Online Tuition.\n- Easy Tutor Matching Support.\n- Helpdesk Support.\n\nClick **Hire A Tutor** below and post your requirement — suitable tutors will contact you soon.";
    return ['Highlights' => implode("\n", $hl), 'About' => $about, 'WhyChoose' => $why];
}

/** ক্যাটাগরির লেখা (খালি ফিল্ডে ডিফল্ট বসিয়ে) */
function category_texts(Obj $c): array
{
    $def = category_default_texts($c->Name, (string)($c->GroupName ?? ''));
    return [
        'Highlights' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $c->Highlights !== '' ? $c->Highlights : $def['Highlights'])), fn($x) => $x !== '')),
        'About' => $c->About !== '' ? $c->About : $def['About'],
        'WhyChoose' => $c->WhyChoose !== '' ? $c->WhyChoose : $def['WhyChoose'],
    ];
}

/**
 * অ্যাডমিনের লেখা সাধারণ টেক্সটকে নিরাপদ HTML বানায়:
 *  "## শিরোনাম" → শিরোনাম, "- লাইন" → টিক চিহ্নের তালিকা, **লেখা** → বোল্ড, ফাঁকা লাইন → নতুন অনুচ্ছেদ
 */
function category_rich_text(string $text): string
{
    $lines = preg_split('/\r?\n/', trim($text));
    $html = ''; $para = []; $list = [];
    $inline = fn($s) => preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', e($s));
    $flushP = function () use (&$para, &$html, $inline) { if ($para) { $html .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>'; $para = []; } };
    $flushL = function () use (&$list, &$html, $inline) { if ($list) { $html .= '<ul>' . implode('', array_map(fn($x) => '<li>' . $inline($x) . '</li>', $list)) . '</ul>'; $list = []; } };
    foreach ($lines as $ln) {
        $t = trim($ln);
        if ($t === '') { $flushP(); $flushL(); continue; }
        if (preg_match('/^#{2,3}\s*(.+)$/u', $t, $m)) { $flushP(); $flushL(); $html .= '<h3>' . $inline($m[1]) . '</h3>'; continue; }
        if (preg_match('/^(?:[-*•✓✔]\s+)(.+)$/u', $t, $m)) { $flushP(); $list[] = $m[1]; continue; }
        $flushL(); $para[] = $t;
    }
    $flushP(); $flushL();
    return $html;
}

/** @return Obj[] এই ক্যাটাগরির সাথে মেলে এমন টিউটর (সাবস্ক্রাইবড ও বেশি রেটিং আগে) */
function category_tutors(Obj $c, array $tutors): array
{
    $kw = array_map(fn($x) => mb_strtolower(trim($x)), $c->KeywordList);
    if (!$kw) return [];
    $out = array_values(array_filter($tutors, function ($t) use ($c, $kw) {
        $vals = $c->MatchType === 'subject' ? (array)$t->Subjects : array_merge((array)$t->Levels, (array)$t->Mediums);
        foreach ($vals as $v) if (in_array(mb_strtolower(trim((string)$v)), $kw, true)) return true;
        return false;
    }));
    usort($out, fn($a, $b) => [(int)$b->IsPremium, $b->Rating, $b->TotalReviews] <=> [(int)$a->IsPremium, $a->Rating, $a->TotalReviews]);
    return $out;
}

/** ক্যাটাগরির ছবি না থাকলে আইকনের রঙ (নাম অনুযায়ী একই রঙ থাকে) */
function category_tint(string $name): array
{
    $p = [['#E3F6FD', '#028EA5'], ['#E7F8EF', '#157347'], ['#FFF4D6', '#B7791F'], ['#F1EAFE', '#6D28D9'], ['#FDECEE', '#C2185B'], ['#EAF1FE', '#1D63E0']];
    return $p[abs(crc32($name)) % count($p)];
}

/** Admin → Settings এর সাব-মেনু: key => title, short, desc, icon, রঙ (Settings পেজ আর সাইডবার দুই জায়গায় ব্যবহার হয়) */
function admin_settings_groups(): array
{
    $g = admin_settings_groups_all();
    if (function_exists('admin_is_super') && !admin_is_super()) unset($g['email']); // SMTP পাসওয়ার্ড — শুধু Super Admin
    return $g;
}

function admin_settings_groups_all(): array
{
    return [
        'general' => ['title' => 'Branding & Theme', 'short' => 'Branding', 'icon' => 'fa-palette', 'bg' => '#F1EAFE', 'fg' => '#6D28D9',
            'desc' => 'সাইটের নাম, নেভবার ও ফুটারের লোগো, আর পুরো সাইটের থিম কালার।'],
        'home' => ['title' => 'Home Page', 'short' => 'Home Page', 'icon' => 'fa-house', 'bg' => '#E3F6FD', 'fg' => '#028EA5',
            'desc' => 'কোন সেকশন দেখাবে (চালু/বন্ধ), Explore Categories এ কোন ক্যাটাগরি, হিরো ছবি ও স্লাইডার, Key Features এর ছবি, Become a Tutor আর FAQ।'],
        'banners' => ['title' => 'Page Banners', 'short' => 'Banners', 'icon' => 'fa-panorama', 'bg' => '#FFF4D6', 'fg' => '#B7791F',
            'desc' => 'Tuition Circular, Find Tutor, Package আর Help Desk পেজের উপরের ব্যানার ছবি।'],
        'profile' => ['title' => 'Profile Pages', 'short' => 'Profiles', 'icon' => 'fa-id-card', 'bg' => '#E7F8EF', 'fg' => '#157347',
            'desc' => 'স্টুডেন্ট ও টিচার প্রোফাইলের বাম পাশের প্রোমো ছবি।'],
        'tuition' => ['title' => 'Tuition Options', 'short' => 'Tuition Options', 'icon' => 'fa-list-check', 'bg' => '#E0F2FE', 'fg' => '#0369A1',
            'desc' => 'টিউশন পোস্ট আর Find Tutor এর লোকেশন, ক্লাস ও বিষয়ের তালিকা।'],
        'email' => ['title' => 'Email (SMTP)', 'short' => 'Email', 'icon' => 'fa-envelope', 'bg' => '#FEE2E2', 'fg' => '#B91C1C',
            'desc' => 'Forgot Password এর ইমেইল পাঠানোর SMTP সেটআপ আর টেস্ট ইমেইল।'],
        'social' => ['title' => 'Footer & Social Links', 'short' => 'Social Links', 'icon' => 'fa-share-nodes', 'bg' => '#EEF2FF', 'fg' => '#4F46E5',
            'desc' => 'ফুটারে দেখানো Facebook, YouTube, WhatsApp ইত্যাদি সোশ্যাল মিডিয়া লিংক।'],
    ];
}

/**
 * হোম পেজের সেকশনগুলো (Admin → Settings → Home Page থেকে চালু/বন্ধ করা যায়)
 * key => [নাম, ছোট ব্যাখ্যা, আইকন]। 'become' আর 'faq' এর নিজস্ব চালু/বন্ধ ঘর আছে (bt_enabled, home_faq_enabled) — সেটাই ব্যবহার হয়।
 */
function home_section_list(): array
{
    return [
        'hero'        => ['Hero (একদম উপরের অংশ)', 'বড় শিরোনাম, Find a Tutor বাটন আর ছবি/স্লাইডার', 'fa-house-flag'],
        'how'         => ['How It Works', 'ধাপে ধাপে কীভাবে টিউটর পাওয়া যায়', 'fa-shoe-prints'],
        'categories'  => ['Explore Categories', 'বাছাই করা ক্যাটাগরির কার্ড', 'fa-layer-group'],
        'tuitions'    => ['Latest Tuition Circulars', 'হোমে দেখানোর জন্য বাছাই করা টিউশন পোস্ট', 'fa-clipboard-list'],
        'feed'        => ['Educational Feed', 'সর্বশেষ ৩টা Approve হওয়া পোস্ট', 'fa-graduation-cap'],
        'become'      => ['Become a Tutor', 'শিক্ষক হওয়ার আহ্বান — নিচের "Become a Tutor" কার্ডেও একই সুইচ', 'fa-chalkboard-user'],
        'features'    => ['Key Features', 'প্ল্যাটফর্মের মূল সুবিধাগুলো', 'fa-star'],
        'packages'    => ['Our Packages', 'শিক্ষক ও শিক্ষার্থীর প্যাকেজ', 'fa-box-open'],
        'why'         => ['Why Choose মাষ্টার মশাই', '৪টা কারণের কার্ড', 'fa-circle-check'],
        'reviews'     => ['Reviews', 'Approve করা রিভিউ (রিভিউ না থাকলে এমনিতেই দেখায় না)', 'fa-comment-dots'],
        'findteacher' => ['Find Your Teacher (ছবি)', 'নিচের দিকের ক্লিকযোগ্য বড় ছবি', 'fa-image'],
        'faq'         => ['FAQ', 'প্রশ্ন-উত্তর — নিচের FAQ কার্ডেও একই সুইচ', 'fa-circle-question'],
    ];
}

/** কোন সেকশনগুলো বন্ধ — key => true */
function home_sections_off(): array
{
    static $off = null;
    if ($off !== null) return $off;
    $off = [];
    try {
        $v = db()->GetSiteContent(['home_sections_off' => '']);
        $list = json_decode((string)($v['home_sections_off'] ?? ''), true);
        if (is_array($list)) foreach ($list as $k) if (is_string($k)) $off[$k] = true;
    } catch (Throwable $e) { }
    return $off;
}

/** হোম পেজের সেকশন চালু কিনা (become / faq এর জন্য তাদের নিজস্ব সেটিং দেখা হয়) */
function home_section_on(string $key): bool
{
    if ($key === 'become') return (become_tutor_content()['bt_enabled'] ?? '1') !== '0';
    if ($key === 'faq') return function_exists('home_faq_settings') ? home_faq_settings()['enabled'] : true;
    return !isset(home_sections_off()[$key]);
}

/** Explore Categories এ অ্যাডমিনের বাছাই করা ক্যাটাগরির id (ক্রম অনুযায়ী) — খালি = অটোমেটিক */
function home_category_ids(): array
{
    try {
        $v = db()->GetSiteContent(['home_cats' => '']);
        $list = json_decode((string)($v['home_cats'] ?? ''), true);
        return is_array($list) ? array_values(array_unique(array_filter(array_map('intval', $list), fn($x) => $x > 0))) : [];
    } catch (Throwable $e) { return []; }
}

// ============================================================
// ===== SUPER ADMIN / ADMIN অনুমতি =====
// ============================================================
/** সাধারণ অ্যাডমিনকে যে অংশগুলোর অনুমতি দেওয়া যায়: key => [নাম, আইকন, ব্যাখ্যা] */
function admin_permissions(): array
{
    return [
        'users'      => ['Users & Teachers', 'fa-users', 'ইউজার/টিচার দেখা, Suspend, চালু/বন্ধ, পাসওয়ার্ড বদল'],
        'teachers'   => ['Teacher Requests', 'fa-user-check', 'শিক্ষক হওয়ার আবেদন Approve / Reject'],
        'tuition'    => ['Tuition', 'fa-clipboard-list', 'Tuition Posts, Applications, Tutor Requests'],
        'notices'    => ['News & Announcements', 'fa-newspaper', 'নোটিশ যোগ / এডিট / মুছে ফেলা'],
        'edufeed'    => ['Educational Feed', 'fa-graduation-cap', 'পোস্ট Approve / Reject'],
        'reviews'    => ['Reviews', 'fa-star', 'রিভিউ Approve / মুছে ফেলা'],
        'payments'   => ['Payments', 'fa-money-bill-wave', 'পেমেন্ট যাচাই — Approve / Reject'],
        'categories' => ['Categories', 'fa-layer-group', 'ক্যাটাগরি যোগ / এডিট'],
        'settings'   => ['Site Settings', 'fa-cog', 'লোগো, হোম পেজ, ব্যানার ইত্যাদি (Email ছাড়া)'],
    ];
}

/** AdminController এর প্রতিটা অ্যাকশন কোন অনুমতির অধীনে — 'always' = সব অ্যাডমিন, 'super' = শুধু Super Admin; তালিকায় না থাকলে 'super' */
function admin_action_rule(string $action): string
{
    static $map = null;
    if ($map === null) {
        $map = ['Dashboard' => 'always', 'ChangeMyPassword' => 'always'];
        $groups = [
            'users' => ['TeacherOverview', 'UserOverview', 'UserDetails', 'SuspendUser', 'UnsuspendUser', 'ToggleUserStatus', 'UpdatePassword'],
            'teachers' => ['TeacherRequests', 'ProcessTeacherRequest', 'DeleteTeacherAccount', 'GetTeacherRequestDetails'],
            'tuition' => ['TuitionPosts', 'AddTuitionPost', 'EditTuitionPost', 'ToggleTuitionStatus', 'ToggleShowOnHome', 'DeleteTuitionPost', 'TuitionApplications',
                'UpdateApplicationStatus', 'DeleteTuitionApplication', 'TutorRequests', 'UpdateTutorRequestStatus', 'DeleteTutorRequest'],
            'notices' => ['ManageNotices', 'AddNotice', 'EditNotice', 'DeleteNotice', 'UploadNoticeImage'],
            'edufeed' => ['EduFeed', 'EduFeedStatus'],
            'reviews' => ['Reviews', 'UpdateReviewStatus', 'DeleteReview'],
            'payments' => ['Payments', 'ApprovePayment', 'RejectPayment', 'DeletePayment', 'Subscriptions', 'SaveSubAlertDays', 'Referrals', 'SaveReferralSettings', 'ProcessWithdrawal', 'SetReferralStatus'],
            'categories' => ['Categories', 'EditCategory', 'SaveCategory', 'DeleteCategory', 'ToggleCategory', 'ReorderCategories', 'SaveCategoryGroup',
                'DeleteCategoryGroup', 'ReorderCategoryGroups', 'UploadCategoryImage', 'CategoryDefaultText'],
            'settings' => ['Settings', 'SaveSettings', 'SaveBecomeTutorSection', 'SaveHomeFaqs', 'SaveHomeSections', 'SaveHomeCategories', 'SaveSocialLinks',
                'UploadHeroImage', 'UploadTutorSectionImage', 'UploadProfilePromo', 'SaveProfilePromo', 'SaveCircularBanner', 'SaveTuitionOptions', 'UploadLogo', 'SaveKeyFeatures'],
        ];
        foreach ($groups as $k => $acts) foreach ($acts as $a) $map[$a] = $k;
        // বাকি সব (Admins, ActivityLog, Packages, PaymentMethods, Email, DeleteUser ...) শুধু Super Admin
    }
    return $map[$action] ?? 'super';
}

/** এখনকার অ্যাডমিনের অনুমতি (রিকোয়েস্টে একবারই ডাটাবেস দেখে) */
function current_admin_access(): ?array
{
    static $c = false;
    if ($c !== false) return $c;
    $c = null;
    if (session_get('UserRole') === 'Admin' && ($id = current_user_id()) !== null) { try { $c = db()->GetAdminAccess($id); } catch (Throwable $e) { $c = null; } }
    return $c;
}
function admin_is_super(): bool { $a = current_admin_access(); return $a !== null && $a['super']; }
/** $key = অনুমতির key, 'always' বা 'super' */
function admin_can(string $key): bool
{
    $a = current_admin_access();
    if ($a === null || !$a['active']) return false;
    if ($a['super'] || $key === 'always') return true;
    if ($key === 'super') return false;
    return in_array($key, $a['perms'], true);
}

/** প্রতিটা রিকোয়েস্টে: অ্যাডমিনকে বন্ধ করা হলে বা অ্যাডমিন থেকে সরালে সাথে সাথে লগআউট */
function validate_admin_session(): void
{
    if (session_get('UserRole') !== 'Admin') return;
    $a = current_admin_access();
    if ($a !== null && $a['active']) return;
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) { session_regenerate_id(true); }
}

/** অ্যাডমিনের পাসওয়ার্ড শক্ত কিনা — ঠিক থাকলে '' নাহলে কী লাগবে তা ফেরত দেয় */
function admin_password_problem(string $pw): string
{
    $miss = [];
    if (mb_strlen($pw) < 8) $miss[] = 'কমপক্ষে ৮ অক্ষর';
    if (!preg_match('/[A-Z]/', $pw)) $miss[] = 'একটা বড় হাতের অক্ষর (A-Z)';
    if (!preg_match('/[a-z]/', $pw)) $miss[] = 'একটা ছোট হাতের অক্ষর (a-z)';
    if (!preg_match('/\d/', $pw)) $miss[] = 'একটা সংখ্যা (0-9)';
    if (!preg_match('/[^A-Za-z0-9]/', $pw)) $miss[] = 'একটা চিহ্ন (যেমন @ # !)';
    return $miss ? 'অ্যাডমিনের পাসওয়ার্ডে লাগবে: ' . implode(', ', $miss) . '।' : '';
}


// ============================================================
// ===== META (FACEBOOK) PIXEL + CONVERSIONS API =====
// ============================================================
// সেটিং (SiteContents): Admin → Meta Pixel পেজ থেকে বদলানো যায়
//  px_enabled, meta_pixel_id, px_capi_token (সার্ভার থেকে পাঠানো — ঐচ্ছিক), px_test_code, px_adv_match, px_ev_{Event}
const PIXEL_DEFAULT_ID = '1125687780363494';
/** ইভেন্ট => [বাংলা বর্ণনা, Meta standard ইভেন্ট কিনা] */
const PIXEL_EVENTS = [
    'PageView'             => ['যেকোনো পেজ খুললে', true],
    'ViewContent'          => ['টিউটরের প্রোফাইল / Full Details, টিউশন পোস্টের ডিটেইলস বা Package পেজ দেখলে', true],
    'Search'               => ['Tutor List এ জেলা ফিল্টার, Tutor ID দিয়ে খোঁজা, বা Tuition Circular এ সার্চ / ফিল্টার করলে', true],
    'CompleteRegistration' => ['নতুন ইউজার সাইনআপ করে ইমেইল (OTP) ভেরিফাই করলে', true],
    'Lead'                 => ['স্টুডেন্ট টিউটর রিকোয়েস্ট পাঠালে, বা টিচার টিউশন পোস্টে Apply করলে', true],
    'PostRequest'          => ['স্টুডেন্ট টিউশনের পোস্ট দিলে বা টিচার "পড়াতে চাই" পোস্ট দিলে (Custom ইভেন্ট)', false],
];

function px_settings(bool $fresh = false): array
{
    static $c = null;
    if ($c !== null && !$fresh) return $c;
    $def = ['px_enabled' => '1', 'meta_pixel_id' => PIXEL_DEFAULT_ID, 'px_capi_token' => '', 'px_test_code' => '', 'px_adv_match' => '1'];
    foreach (PIXEL_EVENTS as $ev => $_) $def['px_ev_' . $ev] = '1';
    try { $c = db()->GetSiteContent($def); } catch (Throwable $e) { $c = $def; }
    if (!preg_match('/^\d{6,20}$/', trim((string)$c['meta_pixel_id']))) $c['meta_pixel_id'] = '';
    $c['meta_pixel_id'] = trim((string)$c['meta_pixel_id']);
    return $c;
}

/** Pixel চালু থাকলে তার ID, না হলে '' */
function meta_pixel_id(): string
{
    $c = px_settings();
    return $c['px_enabled'] !== '0' ? $c['meta_pixel_id'] : '';
}

function px_event_on(string $event): bool
{
    $c = px_settings();
    return ($c['px_ev_' . $event] ?? '1') !== '0';
}

/** এই ভিজিটরকে ট্র্যাক করা হবে কিনা — অ্যাডমিন / স্টাফ কখনো না */
function px_trackable(): bool
{
    return meta_pixel_id() !== '' && !is_admin();
}

/**
 * Pixel ইভেন্ট কিউতে রাখে — পরের পেজ রেন্ডারে (লেআউট) বা এই JSON রেসপন্সে ব্রাউজারে যায়।
 * Conversions API token থাকলে একই event_id দিয়ে সার্ভার থেকেও যায় (Meta duplicate বাদ দেয়)।
 */
function pixel_track(string $event, array $params = []): void
{
    if (session_status() !== PHP_SESSION_ACTIVE || !px_trackable() || !px_event_on($event)) return;
    $id = bin2hex(random_bytes(8));
    $q = $_SESSION['_px'] ?? [];
    if (count($q) < 20) { $q[] = ['e' => $event, 'p' => $params, 'id' => $id]; $_SESSION['_px'] = $q; }
    capi_queue($event, $params, $id);
}

/** কিউতে থাকা ইভেন্টগুলো নিয়ে খালি করে */
function pixel_take(): array
{
    $q = $_SESSION['_px'] ?? [];
    unset($_SESSION['_px']);
    return is_array($q) ? $q : [];
}

function px_norm_phone(string $p): string
{
    $d = preg_replace('/\D/', '', $p);
    if ($d === '') return '';
    if (str_starts_with($d, '01') && strlen($d) === 11) $d = '88' . $d;   // বাংলাদেশি নম্বর → 8801XXXXXXXXX
    return $d;
}

/** লগইন করা ইউজারের email / phone / id — SHA-256 hash (Advanced matching) */
function px_user_data(): array
{
    if (px_settings()['px_adv_match'] === '0') return [];
    $uid = current_user_id();
    if ($uid === null) return [];
    static $ud = null;
    if ($ud !== null) return $ud;
    $ud = ['external_id' => hash('sha256', 'mm-' . $uid)];
    $email = strtolower(trim((string)(session_get('UserEmail') ?? '')));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) $ud['em'] = hash('sha256', $email);
    try {
        $ph = px_norm_phone((string)(db()->scalar('SELECT `MobileNumber` FROM `Users` WHERE `Id` = :u', ['u' => $uid]) ?? ''));
        if ($ph !== '') $ud['ph'] = hash('sha256', $ph);
    } catch (Throwable $e) { }
    return $ud;
}

function px_current_url(): string
{
    $path = (string)($_SERVER['REQUEST_URI'] ?? '/');
    return site_public_url() . (str_starts_with($path, '/') ? $path : '/' . $path);
}

/** সার্ভার (Conversions API) এ পাঠানোর জন্য জমা রাখে — রেসপন্স পাঠানোর পরে একসাথে যায় */
function capi_queue(string $event, array $params, string $eventId, ?string $sourceUrl = null): void
{
    $c = px_settings();
    if (trim((string)$c['px_capi_token']) === '' || meta_pixel_id() === '') return;
    $isPage = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
    $url = $sourceUrl ?? ($isPage || $ref === '' ? px_current_url() : $ref);
    $user = array_filter([
        'client_ip_address' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
        'client_user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        'fbp' => preg_match('/^fb\.\d\.\d+\.\d+$/', (string)($_COOKIE['_fbp'] ?? '')) ? $_COOKIE['_fbp'] : '',
        'fbc' => px_fbc(),
    ], 'strlen');
    foreach (px_user_data() as $k => $v) $user[$k] = [$v];
    $ev = ['event_name' => $event, 'event_time' => time(), 'event_id' => $eventId, 'action_source' => 'website', 'event_source_url' => $url, 'user_data' => (object)$user];
    if ($params) $ev['custom_data'] = $params;
    if (empty($GLOBALS['__capi'])) {
        $GLOBALS['__capi'] = [];
        register_shutdown_function('capi_flush');
    }
    $GLOBALS['__capi'][] = $ev;
}

/** _fbc কুকি, না থাকলে URL এর fbclid থেকে */
function px_fbc(): string
{
    $c = (string)($_COOKIE['_fbc'] ?? '');
    if (preg_match('/^fb\.\d\.\d+\.[A-Za-z0-9_\-]+$/', $c)) return $c;
    $id = (string)($_GET['fbclid'] ?? '');
    return preg_match('/^[A-Za-z0-9_\-]{10,500}$/', $id) ? 'fb.1.' . (int)(microtime(true) * 1000) . '.' . $id : '';
}

/** Graph API তে ইভেন্ট পাঠায়। রিটার্ন: [ok, HTTP code, Meta এর উত্তর] */
function capi_send(array $events, ?string $testCode = null): array
{
    $c = px_settings();
    $pixel = meta_pixel_id(); $token = trim((string)$c['px_capi_token']);
    if ($pixel === '' || $token === '') return [false, 0, 'Pixel ID / Access token নেই'];
    $body = ['data' => array_values($events)];
    $tc = trim((string)($testCode ?? $c['px_test_code']));
    if ($tc !== '') $body['test_event_code'] = $tc;
    $base = getenv('MM_CAPI_BASE') ?: 'https://graph.facebook.com/v21.0';
    $url = rtrim($base, '/') . '/' . $pixel . '/events?access_token=' . rawurlencode($token);
    $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 8]);
        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($res === false) return [false, 0, $err ?: 'কানেক্ট করা যায়নি'];
    } else {
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $json, 'timeout' => 8, 'ignore_errors' => true]]);
        $res = @file_get_contents($url, false, $ctx);
        $code = 0;
        foreach ($http_response_header ?? [] as $h) if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) $code = (int)$m[1];
        if ($res === false) return [false, 0, 'কানেক্ট করা যায়নি'];
    }
    return [$code >= 200 && $code < 300, $code, (string)$res];
}

/** রিকোয়েস্ট শেষে (ইউজারকে পেজ পাঠানোর পরে) জমা ইভেন্টগুলো Conversions API তে পাঠায় */
function capi_flush(): void
{
    $events = $GLOBALS['__capi'] ?? [];
    $GLOBALS['__capi'] = [];
    if (!$events) return;
    // সেশন আগে সেভ করে ছেড়ে দেওয়া — না হলে পরের পেজ (redirect) সেশন লকের জন্য আটকে থাকত
    if (session_status() === PHP_SESSION_ACTIVE) @session_write_close();
    if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
    elseif (function_exists('litespeed_finish_request')) @litespeed_finish_request();
    try {
        [$ok, $code, $res] = capi_send($events);
        if (!$ok) error_log('[Meta CAPI] HTTP ' . $code . ' ' . mb_substr($res, 0, 300));
    } catch (Throwable $e) { error_log('[Meta CAPI] ' . $e->getMessage()); }
}


/** অ্যাডমিন প্যানেল: ইউজার কীভাবে অ্যাকাউন্ট খুলেছে — "Referral: নাম" লিংক অথবা "Direct" ব্যাজ (HTML) */
function joined_via_html(?object $ref, bool $long = false): string
{
    if (!$ref) return '<span class="jv-badge jv-direct" title="নিজে সরাসরি সাইনআপ করেছেন"><i class="fas fa-user-plus"></i> Direct</span>';
    $st = ['Credited' => ' · ৳' . (int)$ref->Bonus . ' বোনাস দেওয়া হয়েছে', 'Waiting' => ' · প্যাকেজ কেনেনি', 'Revoked' => ' · বাতিল'][(string)$ref->Status] ?? '';
    $role = ($ref->ReferrerRole ?? '') === 'Teacher' ? 'Teacher' : (($ref->ReferrerRole ?? '') === 'Admin' ? 'Admin' : 'Student');
    $who = '<a href="/Profile/Show/' . (int)$ref->ReferrerId . '" target="_blank" rel="noopener">' . e((string)$ref->ReferrerName) . '</a>';
    return '<span class="jv-badge jv-ref" title="' . e('রেফার করেছেন: ' . $ref->ReferrerName . ' (' . $role . ')' . $st) . '"><i class="fas fa-gift"></i> ' . ($long ? 'রেফার করেছেন: ' : 'Ref: ') . $who
        . ($long ? ' <small>(' . $role . $st . ')</small>' : '') . '</span>';
}

/** joined_via_html() এর স্টাইল — পেজে একবার */
function joined_via_css(): string
{
    return '<style>.jv-badge{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;border-radius:999px;padding:3px 10px;white-space:nowrap;line-height:1.5}'
        . '.jv-badge i{font-size:11px}.jv-direct{background:#F1F5F9;color:#475569}.jv-ref{background:#FEF3C7;color:#92400E}.jv-ref a{color:#92400E;text-decoration:underline}.jv-ref small{font-weight:600;opacity:.85}'
        . '.jv-filter{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}.jv-filter button{border:1.5px solid #DCE5EE;background:#fff;color:#334155;border-radius:999px;padding:6px 14px;font-weight:700;font-size:13px;cursor:pointer}'
        . '.jv-filter button.on{background:#134856;border-color:#134856;color:#fff}</style>';
}


// ============================================================
// ===== শিক্ষার্থীর প্যাকেজ — কী কী খোলে =====
// ============================================================
// ফ্রি: Edu Feed দেখা + লাইক, টিচারদের তথ্য ও প্রোফাইল দেখা
// প্যাকেজ লাগে: টিচারের প্রোফাইল ছবি / মোবাইল / ইমেইল দেখা, Direct Request, Tuition Circular এ পোস্ট
// (Admin → Packages এ "শিক্ষার্থীদের জন্য ফ্রি" চালু করলে বা Payment Methods এ নিয়ম বন্ধ করলে সব ফ্রি)

/** শিক্ষার্থীদের জন্য প্যাকেজ বাধ্যতামূলক কিনা */
function student_package_required(): bool
{
    static $r = null;
    if ($r === null) {
        try { db()->EnsurePackageTable(); $r = (bool)(db()->PaymentRules()['student'] ?? true); } catch (Throwable $e) { $r = true; }
    }
    return $r;
}

/** এই শিক্ষার্থীর প্যাকেজ চালু আছে কিনা (মেয়াদ বাকি / পোস্ট ক্রেডিট বাকি) */
function student_has_package(int $userId): bool
{
    static $c = [];
    if (!isset($c[$userId])) { try { $c[$userId] = db()->GetStudentPostCredits($userId) !== 0; } catch (Throwable $e) { $c[$userId] = false; } }
    return $c[$userId];
}

/** বর্তমান ভিজিটর টিচারের সাথে যোগাযোগ করতে পারবে কিনা (ছবি, মোবাইল, ইমেইল, Direct Request) */
function viewer_can_contact_teachers(): bool
{
    if (is_admin()) return true;
    $uid = current_user_id();
    if ($uid === null) return false;
    if (current_role() !== 'User') return false;   // টিচার অন্য টিচারকে রিকোয়েস্ট পাঠায় না
    return !student_package_required() || student_has_package($uid);
}

/** বর্তমান ভিজিটর টিচারদের প্রোফাইল ছবি দেখতে পারবে কিনা — অ্যাডমিন, টিচার আর প্যাকেজ নেওয়া শিক্ষার্থী */
function viewer_sees_teacher_photos(): bool
{
    if (is_admin() || current_role() === 'Teacher') return true;
    if (current_user_id() === null) return !student_package_required();
    return viewer_can_contact_teachers();
}

/** প্যাকেজ ছাড়া দেখালে টিচারের ছবির জায়গায় এই ছবি */
function teacher_photo_placeholder(): string
{
    return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" fill="#E2E8F0"/><circle cx="60" cy="46" r="22" fill="#94A3B8"/><path d="M18 112c4-24 22-38 42-38s38 14 42 38z" fill="#94A3B8"/></svg>');
}

/**
 * পেজ / JSON পাঠানোর ঠিক আগে: যে ভিজিটর টিচারের ছবি দেখতে পারবে না, তার জন্য সব টিচারের ছবির লিংক বদলে placeholder।
 * (সব পেজে, ফিড, কার্ড, কমেন্ট — যেখানেই ছবি থাকুক, একসাথে ধরা পড়ে)
 */
function mask_teacher_photos(string $out): string
{
    static $map = null;
    if ($out === '' || viewer_sees_teacher_photos()) return $out;
    if ($map === null) {
        $map = [];
        try {
            $me = (int)(current_user_id() ?? 0);
            $ph = teacher_photo_placeholder();
            foreach (db()->all("SELECT `Id`, `ProfileImageLink` FROM `Users` WHERE `Role` = 'Teacher' AND `ProfileImageLink` IS NOT NULL AND `ProfileImageLink` <> ''") as $r) {
                if ((int)$r->Id === $me) continue;
                $u = trim((string)$r->ProfileImageLink);
                if (strlen($u) < 8) continue;
                $map[$u] = $ph;
                $map[htmlspecialchars($u, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')] = $ph;
                $map[str_replace('/', '\/', $u)] = $ph;   // JSON এ escape করা "/" থাকলে
                $map[substr(js($u), 1, -1)] = $ph;          // js() দিয়ে পেজের <script> এ বসানো হলে
            }
        } catch (Throwable $e) { error_log('[mask_teacher_photos] ' . $e->getMessage()); }
    }
    return $map ? strtr($out, $map) : $out;
}

/** প্যাকেজ ছাড়া যোগাযোগের বাটনের জায়গায় দেখানোর লিংক */
function student_package_url(): string
{
    return current_user_id() === null ? '/Account/Login?returnUrl=' . rawurlencode((string)($_SERVER['REQUEST_URI'] ?? '/')) : '/Package?for=student';
}

/** Edu Feed, পড়াতে চাই আর Tuition Circular এ কমেন্ট — বন্ধ (শুধু লাইক)। আবার চালু করতে true ফেরত দিন। */
function feed_comments_on(): bool
{
    return false;
}


/**
 * টিচারের প্রোফাইলে "চ্যাট" বাটন — প্যাকেজ নেওয়া শিক্ষার্থী চ্যাট আনলক করে, না থাকলে প্যাকেজের লিংক (লক)।
 * টিচার / অ্যাডমিন / নিজের প্রোফাইলে কিছু দেখায় না। $cls = বাটনের CSS class
 */
function teacher_chat_button(int $teacherId, string $cls): string
{
    $me = current_user_id();
    if ($me !== null && ((int)$me === $teacherId || current_role() !== 'User')) return '';
    if (!viewer_can_contact_teachers()) {
        return '<a class="' . e($cls) . '" href="' . e(student_package_url()) . '" title="টিচারের সাথে চ্যাট করতে শিক্ষার্থী প্যাকেজ নিন"><i class="fas fa-lock"></i> '
            . ($me === null ? 'লগইন করে চ্যাট' : 'প্যাকেজ নিয়ে চ্যাট আনলক') . '</a>';
    }
    $key = db()->FindDirectChat((int)$me, $teacherId);
    if ($key) return '<a class="' . e($cls) . '" href="/Chat?c=' . e($key) . '"><i class="fas fa-comment-dots"></i> চ্যাট খুলুন</a>';
    return '<form method="post" action="/Chat/Start" style="display:inline;margin:0;">' . csrf_field() . '<input type="hidden" name="teacherId" value="' . $teacherId . '" />'
        . '<button type="submit" class="' . e($cls) . '"><i class="fas fa-comment-dots"></i> চ্যাট আনলক করুন</button></form>';
}
