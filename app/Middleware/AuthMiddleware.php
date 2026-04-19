<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /documind/public/login');
            exit;
        }
    }
}
