<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath(__DIR__ . '/../' . ltrim($path, '/'));
$root = realpath(__DIR__ . '/..');

if ($file && $root && strpos($file, $root) === 0 && is_file($file)) {
    return false;
}

require $root . '/index.php';
