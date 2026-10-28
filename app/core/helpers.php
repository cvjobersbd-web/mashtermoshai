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

