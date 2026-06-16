<?php

require_once __DIR__ . '/../Models/LayananModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class LayananController
{
    private LayananModel $model;

    public function __construct()
    {
        $this->model = new LayananModel();
    }

    /** GET /layanan — List all active services */
    public function index(): void
    {
        requireAuth();

        $layanan = $this->model->all();

        $this->render('layanan/index.php', [
            'pageTitle'  => 'Jenis Layanan',
            'activePage' => 'layanan',
            'layanan'    => $layanan,
        ]);
    }

    /** GET /layanan/create — Show add form */
    public function create(): void
    {
        requireAuth();

        $this->render('layanan/create.php', [
            'pageTitle'  => 'Tambah Layanan',
            'activePage' => 'layanan',
            'errors'     => $_SESSION['form_errors'] ?? [],
            'old'        => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    /** POST /layanan/store — Process form submission */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan');
        }

        if (!verify_csrf()) {
            $this->redirect('/layanan/create', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect('/layanan/create');
        }

        if ($this->model->create($data)) {
            $this->redirect('/layanan', 'flash_success', 'Layanan baru berhasil ditambahkan.');
        } else {
            $this->redirect('/layanan/create', 'flash_error', 'Gagal menyimpan layanan. Coba lagi.');
        }
    }

    /** GET /layanan/edit/{id} — Show edit form */
    public function edit(int $id): void
    {
        requireAuth();

        $layanan = $this->model->findById($id);
        if (!$layanan) {
            $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
        }

        $this->render('layanan/edit.php', [
            'pageTitle'  => 'Edit Layanan',
            'activePage' => 'layanan',
            'layanan'    => $layanan,
            'errors'     => $_SESSION['form_errors'] ?? [],
            'old'        => $_SESSION['form_old'] ?? $layanan,
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    /** POST /layanan/update/{id} — Process edit form */
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan');
        }

        if (!verify_csrf()) {
            $this->redirect("/layanan/edit/{$id}", 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $layanan = $this->model->findById($id);
        if (!$layanan) {
            $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect("/layanan/edit/{$id}");
        }

        if ($this->model->update($id, $data)) {
            $this->redirect('/layanan', 'flash_success', 'Layanan berhasil diperbarui.');
        } else {
            $this->redirect("/layanan/edit/{$id}", 'flash_error', 'Tidak ada perubahan yang disimpan.');
        }
    }

    /**
     * POST /layanan/delete/{id}
     * SOFT DELETE: Sets deleted_at = NOW(). Does NOT use SQL DELETE.
     */
    public function delete(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan');
        }

        if (!verify_csrf()) {
            $this->redirect('/layanan', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $layanan = $this->model->findById($id);
        if (!$layanan) {
            $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
        }

        if ($this->model->softDelete($id)) {
            $this->redirect(
                '/layanan',
                'flash_success',
                "Layanan \"{$layanan['nama_layanan']}\" berhasil dipindahkan ke arsip."
            );
        } else {
            $this->redirect('/layanan', 'flash_error', 'Gagal menghapus layanan. Coba lagi.');
        }
    }

    /** GET /layanan/archive — Show archived (soft-deleted) services */
    public function archive(): void
    {
        requireAuth();

        $archived = $this->model->archived();

        $this->render('layanan/archive.php', [
            'pageTitle'  => 'Arsip Layanan',
            'activePage' => 'arsip',
            'archived'   => $archived,
        ]);
    }

    /**
     * POST /layanan/restore/{id}
     * RESTORE: Sets deleted_at = NULL, making the record active again.
     */
    public function restore(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan/archive');
        }

        if (!verify_csrf()) {
            $this->redirect('/layanan/archive', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        if ($this->model->restore($id)) {
            $this->redirect('/layanan', 'flash_success', 'Layanan berhasil dipulihkan.');
        } else {
            $this->redirect('/layanan/archive', 'flash_error', 'Gagal memulihkan layanan.');
        }
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'nama_layanan'    => trim($post['nama_layanan'] ?? ''),
            'kategori'        => trim($post['kategori'] ?? ''),
            'harga'           => trim($post['harga'] ?? ''),
            'satuan_harga'    => trim($post['satuan_harga'] ?? 'kg'),
            'estimasi_durasi' => trim($post['estimasi_durasi'] ?? ''),
            'deskripsi'       => trim($post['deskripsi'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['nama_layanan'])) {
            $errors['nama_layanan'] = 'Nama layanan wajib diisi.';
        } elseif (strlen($data['nama_layanan']) > 100) {
            $errors['nama_layanan'] = 'Nama layanan maksimal 100 karakter.';
        }

        if (!in_array($data['kategori'], ['express', 'reguler'])) {
            $errors['kategori'] = 'Kategori harus Express atau Reguler.';
        }

        if (!is_numeric($data['harga']) || (int)$data['harga'] <= 0) {
            $errors['harga'] = 'Harga harus berupa angka positif.';
        }

        if (!in_array($data['satuan_harga'], ['kg', 'item', 'paket'])) {
            $errors['satuan_harga'] = 'Satuan harga tidak valid.';
        }

        if (empty($data['estimasi_durasi'])) {
            $errors['estimasi_durasi'] = 'Estimasi durasi wajib diisi.';
        }

        return $errors;
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/main.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header("Location: /laundry-in{$path}");
        exit;
    }
}
