<?php

/**
 * Require authentication. If not logged in, redirect to /login.
 * Include this at the top of every protected controller method.
 */
function requireAuth(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /login');
        exit;
    }
}

/**
 * Require user authentication. If not logged in as user, redirect to /masuk.
 */
function requireUserAuth(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /masuk');
        exit;
    }
}

/**
 * Generate and verify CSRF tokens.
 * Renamed to avoid conflict with CI4's built-in csrf_token().
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    $valid = hash_equals($_SESSION['csrf_token'] ?? '', $token);
    // Regenerate token after successful use (Rules.md §7.1)
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $valid;
}

/**
 * Simple session-based rate limiting untuk login form.
 * Mencegah brute force attack dengan membatasi jumlah percobaan dalam periode tertentu.
 *
 * @param int $maxAttempts   Maksimum percobaan yang diizinkan (default: 5)
 * @param int $windowSeconds Jendela waktu dalam detik (default: 300 = 5 menit)
 * @return bool              true jika diizinkan, false jika dibatasi
 */
function checkLoginRateLimit(int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $now = time();
    $attempts = $_SESSION['login_attempts'] ?? [];

    // Filter hanya attempts dalam window waktu
    $attempts = array_filter($attempts, fn($timestamp) => ($now - $timestamp) <= $windowSeconds);
    $_SESSION['login_attempts'] = array_values($attempts); // re-index

    return count($attempts) < $maxAttempts;
}

/**
 * Catat satu percobaan login yang gagal.
 */
function recordFailedLoginAttempt(): void
{
    $_SESSION['login_attempts'][] = time();
}

/**
 * Reset semua catatan percobaan login setelah berhasil login.
 */
function resetLoginAttempts(): void
{
    unset($_SESSION['login_attempts']);
}
