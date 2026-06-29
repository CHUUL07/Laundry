<?php

/**
 * Laundry-IN — Root Bootstrap
 * Redirects ke CI4 entry point di public/index.php
 * Semua routing ditangani oleh app/Config/Routes.php
 */

// Jika diakses langsung via http://localhost/laundry-in/index.php
// redirect ke public/ (CI4 front controller)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

header('Location: ' . $protocol . '://' . $host . $path . '/public/');
exit;
