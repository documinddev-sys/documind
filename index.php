<?php
// Redirect to the application entry point
// This handles both local (/documind/public) and production (root /) setups

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Check if we're in a /public subdirectory (local dev)
// If so, redirect to /public/library
// If we're at root (production), redirect to /library
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/public') !== false) {
    // Local development: redirect to /public/library
    $path = preg_replace('#^.*/public#', '/public/library', $uri);
} else {
    // Production: redirect to /library
    $path = '/library';
}

header("Location: $protocol://$host$path");
exit;
