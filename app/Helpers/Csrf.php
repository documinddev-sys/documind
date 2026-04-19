<?php

namespace App\Helpers;

class Csrf
{
    private const TOKEN_LENGTH = 32;

    public static function generate(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::generate()) . '">';
    }

    public static function regenerate(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
}
