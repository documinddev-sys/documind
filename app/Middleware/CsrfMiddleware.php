<?php

namespace App\Middleware;

use App\Helpers\Csrf;

class CsrfMiddleware
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!$token || !Csrf::validate($token)) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                exit;
            }

            // Regenerate token after successful validation
            Csrf::regenerate();
        }
    }
}
