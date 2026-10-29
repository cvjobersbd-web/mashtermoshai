<?php
/**
 * =============================================================
 *  Smart Tutor Hub — Front Controller
 *  সব রিকোয়েস্ট এখানে আসে (.htaccess এর মাধ্যমে) এবং
 *  /{Controller}/{Action}/{id?} অনুযায়ী Controller এ যায়।
 * =============================================================
 */
define('APP_ROOT', __DIR__);

// লোকাল টেস্ট সার্ভার (php -S localhost:8000 index.php) এ স্ট্যাটিক ফাইল সরাসরি দেখানো
if (PHP_SAPI === 'cli-server') {
    $__p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($__p !== '/' && is_file(__DIR__ . $__p) && !preg_match('/\.(php|sql|md|ini)$/i', $__p) && !str_starts_with($__p, '/app/')) return false;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

$GLOBALS['__config'] = file_exists(APP_ROOT . '/config.local.php')
    ? require APP_ROOT . '/config.local.php'
    : require APP_ROOT . '/config.php';

require APP_ROOT . '/app/core/helpers.php';
require APP_ROOT . '/app/core/Mailer.php';
require APP_ROOT . '/app/core/View.php';
require APP_ROOT . '/app/core/Controller.php';
require APP_ROOT . '/app/models/SiteSettings.php';
require APP_ROOT . '/app/models/Db.php';

date_default_timezone_set(config('timezone', 'Asia/Dhaka'));
mb_internal_encoding('UTF-8');

$debug = (bool)config('debug', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED));

// ---------- Session (60 মিনিট idle timeout, HttpOnly cookie) ----------
$sessionMinutes = (int)config('session_minutes', 60);
ini_set('session.gc_maxlifetime', (string)($sessionMinutes * 60));
session_name('SmartTutorHub');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);
session_start();
if (isset($_SESSION['_last']) && time() - $_SESSION['_last'] > $sessionMinutes * 60) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['_last'] = time();

// TempData লোড (আগের রিকোয়েস্ট থেকে)
$GLOBALS['__TempData'] = new Bag($_SESSION['_tempdata'] ?? []);

// ✅ লগইন থাকা ইউজার Suspend হলে সাথে সাথে লগআউট (প্রতি ২০ সেকেন্ডে একবার যাচাই)
if (!empty($_SESSION['UserId']) && ($_SESSION['UserRole'] ?? '') !== 'Admin'
    && (time() - (int)($_SESSION['_susp_chk'] ?? 0)) >= 20) {
    $_SESSION['_susp_chk'] = time();
    $__susp = null;
    try { $__susp = db()->GetActiveSuspension((int)$_SESSION['UserId']); } catch (Throwable $e) { $__susp = null; }
    if ($__susp) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['__suspension'] = suspension_info($__susp);
        $_SESSION['_last'] = time();
        if (is_ajax() || !empty($_SERVER['HTTP_REQUESTVERIFICATIONTOKEN'])) {
            json_response(['success' => false, 'message' => 'আপনার অ্যাকাউন্ট Suspend করা হয়েছে।', 'suspended' => true], 403);
        }
        redirect('/Account/Login');
    }
}

// ✅ Learning streak: লগইন করা স্টুডেন্ট/টিচার দিনে প্রথমবার এলে আজকের দিনটা অ্যাক্টিভ ধরা হয়
if (!empty($_SESSION['UserId']) && ($_SESSION['UserRole'] ?? '') !== 'Admin' && ($_SESSION['_act_day'] ?? '') !== date('Y-m-d')) {
    $_SESSION['_act_day'] = date('Y-m-d');
    try { db()->TouchActivity((int)$_SESSION['UserId'], 1); } catch (Throwable $e) { }
}
// টিচার "অনলাইন" দেখানোর জন্য (প্রতি মিনিটে একবার)
if (!empty($_SESSION['UserId']) && ($_SESSION['UserRole'] ?? '') === 'Teacher' && (int)($_SESSION['_seen_at'] ?? 0) < time() - 60) {
    $_SESSION['_seen_at'] = time();
    try { db()->TouchLastSeen((int)$_SESSION['UserId']); } catch (Throwable $e) { }
}

// ---------- Routing ----------
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$base = str_ends_with($scriptName, 'index.php') ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/') : '';
if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
$path = trim(rawurldecode($path), '/');
if (strcasecmp($path, 'index.php') === 0) $path = '';

// ✅ ব্রাউজার ও Google /favicon.ico খোঁজে — সাইটের লোগোতে পাঠিয়ে দাও
if (in_array(strtolower($path), ['favicon.ico', 'apple-touch-icon.png', 'apple-touch-icon-precomposed.png'], true)) {
    header('Cache-Control: public, max-age=86400');
    header('Location: ' . site_settings()->GetFaviconUrl(), true, 301);
    exit;
}

$segments = $path === '' ? [] : explode('/', $path);
$controllerName = $segments[0] ?? 'Home';
$actionName = $segments[1] ?? 'Index';
$routeId = $segments[2] ?? null;
if ($controllerName === '') $controllerName = 'Home';
if ($actionName === '') $actionName = 'Index';

// Controller ফাইল খোঁজা (case-insensitive)
$controllerClass = null;
$controllerDisplay = null;
if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $controllerName)) {
    foreach (glob(APP_ROOT . '/app/controllers/*Controller.php') as $file) {
        $cbase = basename($file, 'Controller.php');
        if (strcasecmp($cbase, $controllerName) === 0) {
            require_once $file;
            $controllerClass = $cbase . 'Controller';
            $controllerDisplay = $cbase;
            break;
        }
    }
}

$isPost = is_post();
$method = null;
if ($controllerClass && preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $actionName)) {
    $ref = new ReflectionClass($controllerClass);
    // POST হলে আগে "{Action}_POST" মেথড খোঁজা হয়
    $candidates = $isPost ? [$actionName . '_POST', $actionName] : [$actionName];
    foreach ($candidates as $cand) {
        if ($ref->hasMethod($cand)) {
            $m = $ref->getMethod($cand);
            if ($m->isPublic() && !$m->isStatic() && !$m->isConstructor() && $m->getDeclaringClass()->getName() === $controllerClass) {
                // GET দিয়ে POST-only অ্যাকশন কল করা যাবে না
                if (!$isPost && str_ends_with(strtoupper($m->getName()), '_POST')) continue;
                $doc = (string)$m->getDocComment();
                if ($isPost === false && str_contains($doc, '@HttpPost')) continue;
                if ($isPost && str_contains($doc, '@HttpGet')) continue;
                $method = $m->getName();
                break;
            }
        }
    }
}

$GLOBALS['__route_values'] = $routeId !== null ? ['id' => $routeId] : [];
$GLOBALS['__route'] = [
    'controller' => $controllerDisplay ?? $controllerName,
    'action'     => $method ? preg_replace('/_POST$/i', '', $method) : $actionName,
    'id'         => $routeId,
];

try {
    if (!$method) {
        not_found();
    }

    // সব POST রিকোয়েস্টে CSRF যাচাই ([ValidateAntiForgeryToken] এর মতো)
    if ($isPost && !csrf_valid()) {
        http_response_code(400);
        if (is_ajax() || !empty($_SERVER['HTTP_REQUESTVERIFICATIONTOKEN'])) {
            json_response(['success' => false, 'message' => 'Invalid or expired security token. Please refresh the page.'], 400);
        }
        echo '<h3 style="font-family:sans-serif;padding:30px">400 — Security token expired. Please go back, refresh the page and try again.</h3>';
        exit;
    }

    $controller = new $controllerClass();
    $controller->$method();
} catch (Throwable $ex) {
    error_log('[SmartTutorHub] ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    if (!headers_sent()) http_response_code(500);
    while (ob_get_level() > 0) ob_end_clean();
    $msg = $debug ? ($ex->getMessage() . ' @ ' . basename($ex->getFile()) . ':' . $ex->getLine()) : 'An unexpected error occurred.';
    if (is_ajax() || $isPost && str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'json')) {
        json_response(['success' => false, 'message' => $msg], 500);
    }
    try {
        View::render('Shared/Error', ['Model' => obj(['RequestId' => bin2hex(random_bytes(4)), 'Message' => $msg, 'StatusCode' => 500])], null);
    } catch (Throwable $e2) {
        echo '<h3 style="font-family:sans-serif;padding:30px">Error: ' . e($msg) . '</h3>';
    }
}
