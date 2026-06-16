<?php

/**
 * Require authentication. If not logged in, redirect to /login.
 * Include this at the top of every protected controller method.
 */
function requireAuth(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /laundry-in/login');
        exit;
    }
}

/**
 * Generate and verify CSRF tokens.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
