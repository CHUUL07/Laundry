<?php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class UserAuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ----------------------------------------------------------------
    // SHOW REGISTER — GET /daftar
    // ----------------------------------------------------------------
    public function showRegister(): void
    {
        // Jika sudah login, redirect ke beranda
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $this->renderAuth('auth/user-register', [
            'pageTitle' => 'Daftar Akun',
            'errors'    => $_SESSION['form_errors'] ?? [],
            'old'       => $_SESSION['form_old'] ?? [],
        ]);
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }

    // ----------------------------------------------------------------
    // REGISTER — POST /daftar
    // ----------------------------------------------------------------
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /daftar');
            exit;
        }

        if (!validate_csrf()) {
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            header('Location: /daftar');
            exit;
        }

        $nama    = trim($_POST['nama'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $no_telp = trim($_POST['no_telp'] ?? '');
        $alamat  = trim($_POST['alamat'] ?? '');

        // Validasi
        $errors = [];
        if ($nama === '') {
            $errors['nama'] = 'Nama lengkap wajib diisi.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email tidak valid.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        }
        if ($no_telp !== '' && (!ctype_digit($no_telp) || strlen($no_telp) < 10 || strlen($no_telp) > 15)) {
            $errors['no_telp'] = 'Nomor telepon harus 10-15 digit angka.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = compact('nama', 'email', 'no_telp', 'alamat');
            header('Location: /daftar');
            exit;
        }

        // Cek email sudah terdaftar
        $existing = $this->userModel->findByEmail($email);
        if ($existing) {
            $_SESSION['form_errors'] = ['email' => 'Email sudah terdaftar.'];
            $_SESSION['form_old']    = compact('nama', 'email', 'no_telp', 'alamat');
            header('Location: /daftar');
            exit;
        }

        // Simpan user
        $userId = $this->userModel->create([
            'nama'     => $nama,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'no_telp'  => $no_telp ?: null,
            'alamat'   => $alamat ?: null,
        ]);

        // Auto login setelah registrasi
        $_SESSION['user_id']    = $userId;
        $_SESSION['user_nama']  = $nama;
        $_SESSION['user_email'] = $email;
        session_regenerate_id(true);

        header('Location: /');
        exit;
    }

    // ----------------------------------------------------------------
    // SHOW LOGIN — GET /masuk
    // ----------------------------------------------------------------
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $this->renderAuth('auth/user-login', [
            'pageTitle' => 'Masuk',
            'flash'     => $this->getFlash(),
        ]);
    }

    // ----------------------------------------------------------------
    // LOGIN — POST /masuk
    // ----------------------------------------------------------------
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /masuk');
            exit;
        }

        if (!validate_csrf()) {
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            header('Location: /masuk');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['flash_error'] = 'Email dan password wajib diisi.';
            header('Location: /masuk');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            header('Location: /masuk');
            exit;
        }

        // Login berhasil
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_nama']  = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        session_regenerate_id(true);

        header('Location: /');
        exit;
    }

    // ----------------------------------------------------------------
    // LOGOUT — GET /keluar
    // ----------------------------------------------------------------
    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_nama'], $_SESSION['user_email']);
        header('Location: /');
        exit;
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------
    private function renderAuth(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layouts/auth.php';
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
