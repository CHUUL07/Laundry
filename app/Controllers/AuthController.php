<?php

require_once __DIR__ . '/../Models/AdminModel.php';

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
            header('Location: /laundry-in/dashboard');
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
            header('Location: /laundry-in/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic input validation
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi.';
            header('Location: /laundry-in/login');
            exit;
        }

        $admin = $this->adminModel->findByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: /laundry-in/dashboard');
            exit;
        }

        // Generic error — do not reveal whether username or password was wrong
        $_SESSION['login_error'] = 'Username atau password salah.';
        header('Location: /laundry-in/login');
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
        header('Location: /laundry-in/login');
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
