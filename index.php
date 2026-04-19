<?php
// Secure and robust redirect to the public entry point
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base = ($uri === '/' || $uri === '\\') ? '' : $uri;

header("Location: http://$host$base/public/library");
exit;
