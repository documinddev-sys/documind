<?php

namespace App\Helpers;

class URLHelper
{
    /**
     * Get the base URL of the application
     */
    public static function baseUrl(): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
    }

    /**
     * Generate a full URL for a route
     * @param string $path The path (e.g., '/dashboard', '/documents/123')
     * @return string The full URL
     */
    public static function url(string $path = ''): string
    {
        $base = self::baseUrl();
        $path = ltrim($path, '/');
        return $path ? "{$base}/{$path}" : $base;
    }

    /**
     * Generate a relative path for routes (used in href and fetch)
     * @param string $path The path (e.g., 'dashboard', 'documents/123')
     * @return string The relative path (e.g., '/dashboard', '/documents/123')
     */
    public static function route(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return $path ? "/{$path}" : '/';
    }

    /**
     * Generate an asset URL
     * @param string $asset The asset path (e.g., 'css/style.css')
     * @return string The full asset URL
     */
    public static function asset(string $asset): string
    {
        return self::url('assets/' . ltrim($asset, '/'));
    }
}
