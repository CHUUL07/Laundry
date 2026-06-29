<?php

require_once __DIR__ . '/../Libraries/Cart.php';
require_once __DIR__ . '/../Models/LayananModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class CartController
{
    private Cart         $cart;
    private LayananModel $layananModel;

    public function __construct()
    {
        $this->cart         = new Cart();
        $this->layananModel = new LayananModel();
    }

    // ----------------------------------------------------------------
    // INDEX — Halaman tampil isi cart
    // ----------------------------------------------------------------
    public function index(): void
    {
        requireUserAuth();

        $data = [
            'pageTitle'  => 'Keranjang Belanja',
            'activePage' => 'cart',
            'items'      => $this->cart->getItems(),
            'total'      => $this->cart->total(),
            'count'      => $this->cart->count(),
            'flash'      => $this->getFlash(),
        ];

        $this->render('cart/index', $data);
    }

    // ----------------------------------------------------------------
    // ADD — Tambah item ke cart (dari halaman layanan)
    // ----------------------------------------------------------------
    public function add(int $id): void
    {
        requireUserAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan');
        }

        if (!validate_csrf()) {
            $this->redirect('/layanan', 'flash_error', 'Token CSRF tidak valid.');
        }

        // Ambil data layanan dari database untuk memastikan valid
        $layanan = $this->layananModel->findById($id);

        if (!$layanan) {
            $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
        }

        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // Panggil Cart::insert()
        $this->cart->insert($id, [
            'nama_layanan' => $layanan['nama_layanan'],
            'harga'        => (int)$layanan['harga'],
            'satuan_harga' => $layanan['satuan_harga'],
            'quantity'     => $qty,
        ]);

        $this->redirect('/cart', 'flash_success', '"' . $layanan['nama_layanan'] . '" ditambahkan ke keranjang.');
    }

    // ----------------------------------------------------------------
    // UPDATE — Ubah quantity item di cart
    // ----------------------------------------------------------------
    public function update(int $id): void
    {
        requireUserAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
        }

        $qty = (int)($_POST['quantity'] ?? 0);

        // Panggil Cart::update()
        $this->cart->update($id, $qty);

        if ($qty <= 0) {
            $this->redirect('/cart', 'flash_success', 'Item dihapus dari keranjang.');
        } else {
            $this->redirect('/cart', 'flash_success', 'Jumlah item diperbarui.');
        }
    }

    // ----------------------------------------------------------------
    // REMOVE — Hapus satu item dari cart
    // ----------------------------------------------------------------
    public function remove(int $id): void
    {
        requireUserAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
        }

        // Panggil Cart::remove()
        $this->cart->remove($id);

        $this->redirect('/cart', 'flash_success', 'Item dihapus dari keranjang.');
    }

    // ----------------------------------------------------------------
    // DESTROY — Kosongkan seluruh cart
    // ----------------------------------------------------------------
    public function destroy(): void
    {
        requireUserAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
        }

        // Panggil Cart::destroy()
        $this->cart->destroy();

        $this->redirect('/cart', 'flash_success', 'Keranjang berhasil dikosongkan.');
    }

    // ----------------------------------------------------------------
    // CHECKOUT — POST /cart/checkout (Simpan cart ke database pesanan)
    // ----------------------------------------------------------------
    public function checkout(): void
    {
        requireUserAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
        }

        $items = $this->cart->getItems();
        if (empty($items)) {
            $this->redirect('/cart', 'flash_error', 'Keranjang masih kosong.');
        }

        $metode = $_POST['metode_pengiriman'] ?? 'diambil';
        if (!in_array($metode, ['diantar', 'diambil'])) {
            $metode = 'diambil';
        }
        $catatan = trim($_POST['catatan'] ?? '');

        $totalHarga = $this->cart->total();
        $kodePesanan = 'LND-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $now = date('Y-m-d H:i:s');

        // Panggil PesananModel
        require_once __DIR__ . '/../Models/PesananModel.php';
        $pesananModel = new PesananModel();

        $pesananId = $pesananModel->create([
            'user_id'            => (int)$_SESSION['user_id'],
            'kode_pesanan'       => $kodePesanan,
            'status'             => 'diterima',
            'metode_pengiriman'  => $metode,
            'total_harga'        => $totalHarga,
            'catatan'            => $catatan ?: null,
            'tanggal_pesan'      => $now,
            'created_at'         => $now,
        ]);

        // Simpan detail items
        foreach ($items as $item) {
            $pesananModel->addDetail($pesananId, [
                'nama_layanan' => $item['nama_layanan'],
                'harga_satuan' => (int)$item['harga'],
                'satuan_harga' => $item['satuan_harga'] ?? 'kg',
                'quantity'     => (int)$item['quantity'],
                'subtotal'     => (int)$item['subtotal'],
            ]);
        }

        // Kosongkan cart
        $this->cart->destroy();

        // Redirect ke halaman status pesanan
        $_SESSION['flash_success'] = 'Pesanan berhasil dibuat! Kode: ' . $kodePesanan;
        header("Location: /pesanan-saya/{$pesananId}");
        exit;
    }

    // ----------------------------------------------------------------
    // STATUS — GET /pesanan-saya/{id} (Lihat status pesanan user)
    // ----------------------------------------------------------------
    public function status(int $id): void
    {
        requireUserAuth();

        require_once __DIR__ . '/../Models/PesananModel.php';
        $pesananModel = new PesananModel();

        $pesanan = $pesananModel->findById($id);
        if (!$pesanan || (int)$pesanan['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(404);
            echo '<h2>Pesanan tidak ditemukan.</h2><a href="/">Kembali ke Beranda</a>';
            exit;
        }

        $detail = $pesananModel->getDetail($id);

        $this->render('pesanan/user-status', [
            'pageTitle' => 'Status Pesanan',
            'pesanan'   => $pesanan,
            'detail'    => $detail,
            'flash'     => $this->getFlash(),
        ]);
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------

    private function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layouts/user.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header("Location: {$path}");
        exit;
    }

    private function getFlash(): array
    {
        $flash = [];
        foreach (['flash_success', 'flash_error'] as $key) {
            if (isset($_SESSION[$key])) {
                $flash[$key] = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
        }
        return $flash;
    }
}
