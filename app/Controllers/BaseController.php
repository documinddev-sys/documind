<?php

namespace App\Controllers;

class BaseController
{
    protected $viewPath = __DIR__ . '/../../views';

    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data);
        
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View not found: $viewFile");
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        $layoutFile = $this->viewPath . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function abort(int $code): void
    {
        http_response_code($code);
        $errorView = $this->viewPath . "/errors/{$code}.php";
        
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo "<h1>Error $code</h1>";
        }
        exit;
    }
}
