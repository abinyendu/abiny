<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $params = []): string
    {
        $viewPath = BASE_PATH . '/resources/views/' . trim($view, '/ ') . '.php';
        if (!is_file($viewPath)) {
            return '<h1>View not found: ' . htmlspecialchars($view) . '</h1>';
        }
        extract($params, EXTR_SKIP);
        ob_start();
        include BASE_PATH . '/resources/views/layouts/app_start.php';
        include $viewPath;
        include BASE_PATH . '/resources/views/layouts/app_end.php';
        return (string) ob_get_clean();
    }
}
