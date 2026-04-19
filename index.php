<?php
// This file should not be accessed in production.
// Docker + Apache serves from /var/www/html/public/ directly.
// If you reach here, something is misconfigured.
header('HTTP/1.1 403 Forbidden');
echo 'Access denied. Please access the application through the proper domain.';
exit;
