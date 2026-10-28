<?php
/**
 * সহজ View renderer — Razor এর মতো layout + sections সাপোর্ট করে।
 * View ফাইলের ভিতরে পাওয়া যাবে: $Model, $ViewBag, $ViewData, $TempData
 */
class View
{
    public static function path(string $view): string
    {
        $p = APP_ROOT . '/app/views/' . $view . '.php';
        if (!is_file($p)) throw new RuntimeException("View not found: $view");
        return $p;
    }

    /**
     * @param string $view  যেমন 'Home/Index'
     * @param array  $vars  ['Model' => ..., 'ViewBag' => [...]] অথবা অন্য ভেরিয়েবল
     */
    public static function render(string $view, array $vars = [], ?string $layout = 'Shared/_Layout'): void
    {
        $GLOBALS['__sections'] = [];
        $GLOBALS['__section_stack'] = [];

        $ViewBag = $vars['ViewBag'] ?? new Bag();
        if (is_array($ViewBag)) $ViewBag = new Bag($ViewBag);
        $ViewData = $GLOBALS['__ViewData'] ?? new Bag();
        $GLOBALS['__ViewData'] = $ViewData;
        $GLOBALS['__ViewBag'] = $ViewBag;
        $TempData = tempdata();
        $Model = $vars['Model'] ?? null;
        unset($vars['ViewBag'], $vars['Model']);

        $body = self::capture(self::path($view), array_merge($vars, compact('Model', 'ViewBag', 'ViewData', 'TempData')));

        // View টি রেন্ডার হয়ে গেলে TempData "consumed" ধরা হয়
        unset($_SESSION['_tempdata']);

        if ($layout === null) {
            echo $body;
            return;
        }
        echo self::capture(self::path($layout), [
            'RenderBody' => $body,
            'ViewBag' => $ViewBag, 'ViewData' => $ViewData, 'TempData' => $TempData, 'Model' => $Model,
        ]);
    }

    public static function partial(string $view, array $vars = []): void
    {
        $vars += [
            'ViewBag'  => $GLOBALS['__ViewBag'] ?? new Bag(),
            'ViewData' => $GLOBALS['__ViewData'] ?? new Bag(),
            'TempData' => tempdata(),
        ];
        echo self::capture(self::path($view), $vars);
    }

    private static function capture(string $__file, array $__vars): string
    {
        extract($__vars, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }
}
