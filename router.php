<?php

/**
 * Development server router for PHP built-in server.
 * Mimics .htaccess rewrite rules.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Strip /laundry-in prefix to get relative path within project
$relativePath = preg_replace('#^/laundry-in#', '', $uri);

// Handle static files (CSS, JS, etc.)
if (preg_match('#^/public/#', $relativePath)) {
    $filePath = __DIR__ . $relativePath;
    if (file_exists($filePath)) {
        // Set proper content type
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'woff2' => 'font/woff2',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($filePath);
        return true;
    }
}

// Route everything else through index.php
$_GET['url'] = ltrim($relativePath, '/');
include __DIR__ . '/index.php';
