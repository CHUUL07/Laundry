<?php

require_once __DIR__ . '/../Models/PelangganModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class PelangganController
{
    private PelangganModel $model;

    public function __construct()
    {
        $this->model = new PelangganModel();
    }

    // ----------------------------------------------------------------
    // READ — Daftar semua pelanggan aktif
    // ----------------------------------------------------------------
    public function index(): void
    {
        requireAuth();

        $pelanggan = $this->model->all();

        $this->render('pelanggan/index.php', [
            'pageTitle'  => 'Data Pelanggan',
            'activePage' => 'pelanggan',
            'pelanggan'  => $pelanggan,
        ]);
    }

    // ----------------------------------------------------------------
    // CREATE — Form tambah pelanggan
    // ----------------------------------------------------------------
    public function create(): void
    {
        requireAuth();

        $this->render('pelanggan/create.php', [
            'pageTitle'  => 'Tambah Pelanggan',
            'activePage' => 'pelanggan',
            'errors'     => $_SESSION['form_errors'] ?? [],
            'old'        => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    // ----------------------------------------------------------------
    // STORE — Proses simpan pelanggan baru
    // ----------------------------------------------------------------
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan/create', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect('/pelanggan/create');
        }

        $this->model->create($data);
        $this->redirect('/pelanggan', 'flash_success', 'Pelanggan berhasil ditambahkan.');
    }

    // ----------------------------------------------------------------
    // EDIT — Form edit pelanggan
    // ----------------------------------------------------------------
    public function edit(int $id): void
    {
        requireAuth();

        $pelanggan = $this->model->findById($id);
        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
        }

        $this->render('pelanggan/edit.php', [
            'pageTitle'  => 'Edit Pelanggan',
            'activePage' => 'pelanggan',
            'pelanggan'  => $pelanggan,
            'errors'     => $_SESSION['form_errors'] ?? [],
            'old'        => $_SESSION['form_old'] ?? $pelanggan,
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    // ----------------------------------------------------------------
    // UPDATE — Proses update data pelanggan
    // ----------------------------------------------------------------
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
        }

        if (!validate_csrf()) {
            $this->redirect("/pelanggan/edit/{$id}", 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $pelanggan = $this->model->findById($id);
        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->model->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect("/pelanggan/edit/{$id}");
        }

        if ($this->model->update($id, $data)) {
            $this->redirect('/pelanggan', 'flash_success', 'Data pelanggan berhasil diperbarui.');
        } else {
            $this->redirect("/pelanggan/edit/{$id}", 'flash_error', 'Tidak ada perubahan yang disimpan.');
        }
    }

    // ----------------------------------------------------------------
    // DELETE — Soft delete pelanggan
    // ----------------------------------------------------------------
    public function delete(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        // Cari pelanggan (termasuk yang mungkin sudah dihapus? — seharusnya masih aktif)
        $pelanggan = $this->model->findById($id);
        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
        }

        if ($this->model->softDelete($id)) {
            $this->redirect(
                '/pelanggan',
                'flash_success',
                "Pelanggan \"{$pelanggan['nama_pelanggan']}\" berhasil dihapus."
            );
        } else {
            $this->redirect('/pelanggan', 'flash_error', 'Gagal menghapus pelanggan. Coba lagi.');
        }
    }

    // ----------------------------------------------------------------
    // ARCHIVE — Daftar pelanggan yang sudah dihapus
    // ----------------------------------------------------------------
    public function archive(): void
    {
        requireAuth();

        $archived = $this->model->archived();

        $this->render('pelanggan/archive.php', [
            'pageTitle'  => 'Arsip Pelanggan',
            'activePage' => 'pelanggan',
            'archived'   => $archived,
        ]);
    }

    // ----------------------------------------------------------------
    // RESTORE — Pulihkan pelanggan dari arsip
    // ----------------------------------------------------------------
    public function restore(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan/archive');
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan/archive', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        if ($this->model->restore($id)) {
            $this->redirect('/pelanggan', 'flash_success', 'Pelanggan berhasil dipulihkan.');
        } else {
            $this->redirect('/pelanggan/archive', 'flash_error', 'Gagal memulihkan pelanggan.');
        }
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------

    private function sanitizeInput(array $post): array
    {
        return [
            'nama_pelanggan' => trim($post['nama_pelanggan'] ?? ''),
            'no_telp'        => trim($post['no_telp'] ?? ''),
            'email'          => trim($post['email'] ?? ''),
            'alamat'         => trim($post['alamat'] ?? ''),
        ];
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
        header("Location: {$path}");
        exit;
    }
}
