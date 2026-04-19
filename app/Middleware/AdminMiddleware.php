<?php

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (($_SESSION['user_role'] ?? null) !== 'admin') {
            http_response_code(403);
            include __DIR__ . '/../../views/errors/403.php';
            exit;
        }
    }
}
