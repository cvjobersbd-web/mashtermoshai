<?php
/**
 * Base Controller
 */
abstract class Controller
{
    protected Db $db;

    public function __construct()
    {
        $this->db = db();
        // অ্যাডমিনকে বন্ধ করা / অ্যাডমিন থেকে সরানো হলে সাথে সাথে লগআউট
        if (function_exists('validate_admin_session')) validate_admin_session();
        // প্যাকেজের মেয়াদ: রিমাইন্ডার আর মেয়াদ শেষে টিচার → ইউজার (প্রতি ৩০ মিনিটে একবার)
        try { $this->db->RunSubscriptionHousekeeping(); } catch (Throwable $e) { }
        // অ্যাডমিন Approve / সরানো বা মেয়াদ শেষে রোল বদলালে — আবার লগইন ছাড়াই সাথে সাথে কার্যকর
        $sr = session_get('UserRole');
        if (($sr === 'Teacher' || $sr === 'User') && ($uid = current_user_id()) !== null) {
            try {
                $dbRole = $this->db->scalar('SELECT `Role` FROM `Users` WHERE `Id` = :i', ['i' => (int)$uid]);
                if (($dbRole === 'Teacher' || $dbRole === 'User') && $dbRole !== $sr) session_set('UserRole', $dbRole);
            } catch (Throwable $e) { }
        }
        // রেফারেল লিংক (?ref=CODE) — কোড মনে রাখা হয় (সেশন + ৩০ দিনের কুকি), সাইনআপের সময় রেফারার বোনাস পায়
        $ref = $_GET['ref'] ?? null;
        if (is_string($ref) && preg_match('/^MM[A-Z0-9]{6}$/', strtoupper($ref)) && current_user_id() === null) {
            $ref = strtoupper($ref);
            $_SESSION['ref_code'] = $ref;
            if (!headers_sent()) setcookie('mm_ref', $ref, ['expires' => time() + 30 * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
        }
    }

    /** View রেন্ডার করে। $view না দিলে বর্তমান Controller/Action ব্যবহার হয় */
    protected function view(?string $view = null, $model = null, array $viewBag = []): void
    {
        if ($view === null) $view = $GLOBALS['__route']['controller'] . '/' . $GLOBALS['__route']['action'];
        elseif (!str_contains($view, '/')) $view = $GLOBALS['__route']['controller'] . '/' . $view;
        View::render($view, ['Model' => $model, 'ViewBag' => $viewBag]);
        exit;
    }

    protected function json($data, int $status = 200): void { json_camel($data, $status); }

    protected function redirectToAction(string $action, ?string $controller = null, array $params = []): void
    {
        redirect(url_action($action, $controller ?? $GLOBALS['__route']['controller'], $params));
    }

    protected function requireLogin(): int
    {
        $id = current_user_id();
        if ($id === null) $this->redirectToAction('Login', 'Account');
        return $id;
    }
}
