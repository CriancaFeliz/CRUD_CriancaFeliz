<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$normalizedPath = '/' . ltrim(str_replace('\\', '/', $path), '/');
$firstSegment = strtolower(explode('/', trim($normalizedPath, '/'))[0] ?? '');
$blockedDirectories = ['app', 'data', 'database', 'docker', 'docs', 'tests', 'tools', 'var', '.git', '.github'];
$blockedExtension = preg_match('/\.(?:env|ini|log|sql|sh|md|zip|tar|gz|bak|ya?ml|json|ps1|txt)$/i', $normalizedPath);
$hiddenPath = preg_match('#/(?:\.[^/]+)(?:/|$)#', $normalizedPath);

if (in_array($firstSegment, $blockedDirectories, true) || $blockedExtension || $hiddenPath) {
    http_response_code(404);
    header('X-Content-Type-Options: nosniff');
    exit;
}

$file = realpath(__DIR__ . '/../' . ltrim($path, '/'));
$root = realpath(__DIR__ . '/..');

if ($file && $root && ($file === $root || strpos($file, $root . DIRECTORY_SEPARATOR) === 0) && is_file($file)) {
    return false;
}

require $root . '/index.php';
