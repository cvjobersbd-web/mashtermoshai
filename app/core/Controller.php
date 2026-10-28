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
