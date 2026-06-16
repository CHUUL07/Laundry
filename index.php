<?php

declare(strict_types=1);

// Start session
session_start();

// Load all helpers and base classes
require_once __DIR__ . '/app/Helpers/auth.php';
require_once __DIR__ . '/app/Models/BaseModel.php';

// Get URL from query string (set by .htaccess)
$url = trim($_GET['url'] ?? '', '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

$segment1 = $parts[0] ?? '';  // e.g. 'layanan', 'dashboard', 'login'
$segment2 = $parts[1] ?? '';  // e.g. 'create', 'edit', 'delete', 'archive', 'restore'
$segment3 = $parts[2] ?? '';  // e.g. '5' (ID)

// =============================================
//  ROUTER
// =============================================

// Auth routes
if ($segment1 === '' || $segment1 === 'login') {
    require_once __DIR__ . '/app/Controllers/AuthController.php';
    $controller = new AuthController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->processLogin();
    } else {
        $controller->showLogin();
    }
    exit;
}

if ($segment1 === 'logout') {
    require_once __DIR__ . '/app/Controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

// Dashboard route
if ($segment1 === 'dashboard') {
    require_once __DIR__ . '/app/Controllers/DashboardController.php';
    (new DashboardController())->index();
    exit;
}

// Layanan routes
if ($segment1 === 'layanan') {
    require_once __DIR__ . '/app/Controllers/LayananController.php';
    $c = new LayananController();

    $id = !empty($segment3) && is_numeric($segment3) ? (int)$segment3 : null;

    switch ($segment2) {
        case '':
            $c->index();
            break;
        case 'create':
            $c->create();
            break;
        case 'store':
            $c->store();
            break;
        case 'edit':
            $id ? $c->edit($id) : header('Location: /laundry-in/layanan');
            break;
        case 'update':
            $id ? $c->update($id) : header('Location: /laundry-in/layanan');
            break;
        case 'delete':
            $id ? $c->delete($id) : header('Location: /laundry-in/layanan');
            break;
        case 'archive':
            $c->archive();
            break;
        case 'restore':
            $id ? $c->restore($id) : header('Location: /laundry-in/layanan/archive');
            break;
        default:
            http_response_code(404);
            echo '<h1>404 — Halaman tidak ditemukan</h1>';
    }
    exit;
}

// 404 Fallback
http_response_code(404);
echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:4rem;">
<h1 style="color:#EF4444;">404</h1><p>Halaman tidak ditemukan.</p>
<a href="/laundry-in/dashboard">Kembali ke Dashboard</a></body></html>';
