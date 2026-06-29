<?php

require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class AuthController
{
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    /**
     * GET /login — Show login form
     */
    public function showLogin(): void
    {
        // Already logged in? Redirect to dashboard
        if (isset($_SESSION['admin_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $content = $this->renderView('auth/login.php', [
            'error' => $_SESSION['login_error'] ?? null,
        ]);
        unset($_SESSION['login_error']);

        ob_start();
        $pageVars = ['content' => $content];
        extract($pageVars);
        include __DIR__ . '/../Views/layouts/auth.php';
        ob_end_flush();
    }

    /**
     * POST /login — Process login credentials
     */
    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // CSRF validation
        if (!validate_csrf()) {
            $_SESSION['login_error'] = 'Token CSRF tidak valid. Silakan coba lagi.';
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic input validation
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi.';
            header('Location: /login');
            exit;
        }

        // Rate limiting — cek apakah terlalu banyak percobaan gagal
        if (!checkLoginRateLimit()) {
            $_SESSION['login_error'] = 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.';
            header('Location: /login');
            exit;
        }

        $admin = $this->adminModel->findByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            // Reset login attempts on success
            resetLoginAttempts();

            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: /dashboard');
            exit;
        }

        // Catat percobaan gagal untuk rate limiting
        recordFailedLoginAttempt();

        // Generic error — do not reveal whether username or password was wrong
        $_SESSION['login_error'] = 'Username atau password salah.';
        header('Location: /login');
        exit;
    }

    /**
     * GET /logout — Destroy session and redirect
     */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        header('Location: /login');
        exit;
    }

    /**
     * Render a view file and return it as a string.
     */
    private function renderView(string $viewPath, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $viewPath;
        return ob_get_clean();
    }
}
