<?php

/**
 * Router for PHP's built-in web server
 * Use: php -S localhost:8000 router.php
 */

$url = parse_url($_SERVER["REQUEST_URI"]);
$file = __DIR__ . $url["path"];

// Serve static files directly
if (is_file($file)) {
    return false;
}

// Route everything else to index.php
require __DIR__ . '/index.php';
