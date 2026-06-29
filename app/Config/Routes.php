<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', function () {
    session();
    // Jika sudah login sebagai admin, redirect ke dashboard
    if (isset($_SESSION['admin_id'])) {
        header('Location: /dashboard');
        exit;
    }
    include_once APPPATH . 'Controllers/LandingController.php';
    (new LandingController())->index();
});

// ─── Landing / Auth ────────────────────────────────────────────
$routes->get('/login', function () {
    session(); // init CI4 session so $_SESSION is available
    include_once APPPATH . 'Controllers/AuthController.php';
    (new AuthController())->showLogin();
});
$routes->post('/login', function () {
    session(); // init CI4 session so $_SESSION is available
    include_once APPPATH . 'Controllers/AuthController.php';
    (new AuthController())->processLogin();
});
$routes->get('/logout', function () {
    session();
    include_once APPPATH . 'Controllers/AuthController.php';
    (new AuthController())->logout();
});

// ─── Dashboard ─────────────────────────────────────────────────
$routes->get('/dashboard', function () {
    session();
    include_once APPPATH . 'Controllers/DashboardController.php';
    (new DashboardController())->index();
});

// ─── Layanan CRUD ──────────────────────────────────────────────
$routes->get('/layanan', function () {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->index();
});
$routes->get('/layanan/create', function () {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->create();
});
$routes->post('/layanan/store', function () {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->store();
});
$routes->get('/layanan/edit/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->edit((int) $id);
});
$routes->post('/layanan/update/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->update((int) $id);
});
$routes->post('/layanan/delete/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->delete((int) $id);
});
$routes->get('/layanan/archive', function () {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->archive();
});
$routes->post('/layanan/restore/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/LayananController.php';
    (new LayananController())->restore((int) $id);
});

// ─── Pelanggan CRUD (SOAL 01 — v2.0) ─────────────────────────
$routes->get('/pelanggan', function () {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->index();
});
$routes->get('/pelanggan/create', function () {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->create();
});
$routes->post('/pelanggan/store', function () {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->store();
});
$routes->get('/pelanggan/edit/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->edit((int) $id);
});
$routes->post('/pelanggan/update/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->update((int) $id);
});
$routes->post('/pelanggan/delete/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->delete((int) $id);
});
$routes->get('/pelanggan/archive', function () {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->archive();
});
$routes->post('/pelanggan/restore/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->restore((int) $id);
});

// ─── Cart Routes (SOAL 05 — v2.0) ───────────────────────────────
$routes->get('/cart', function () {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->index();
});
$routes->post('/cart/add/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->add((int) $id);
});
$routes->post('/cart/update/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->update((int) $id);
});
$routes->post('/cart/remove/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->remove((int) $id);
});
$routes->post('/cart/destroy', function () {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->destroy();
});

// ─── User Auth (Phase I — v3.0) ──────────────────────────────────
$routes->get('/daftar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->showRegister();
});
$routes->post('/daftar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->register();
});
$routes->get('/masuk', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->showLogin();
});
$routes->post('/masuk', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->login();
});
$routes->get('/keluar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->logout();
});

// ─── Cart Checkout & Pesanan Saya (Phase I — v3.0) ─────────────
$routes->post('/cart/checkout', function () {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->checkout();
});
$routes->get('/pesanan-saya/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->status((int) $id);
});

// ─── Admin Pesanan (Phase I — v3.0) ────────────────────────────
$routes->get('/pesanan', function () {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->index();
});
$routes->get('/pesanan/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->detail((int) $id);
});
$routes->post('/pesanan/update-status/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->updateStatus((int) $id);
});
$routes->get('/pesanan/export-pdf/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->exportPdf((int) $id);
});
$routes->get('/pesanan/print-struk/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->printStruk((int) $id);
});
