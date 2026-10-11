<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/functions.php';

function require_guest(): void
{
    if (current_user_id()) {
        redirect(($_SESSION['role'] ?? 'student') === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
    }
}

function require_login(): void
{
    if (!current_user_id()) {
        flash('warning', 'Please sign in to continue.');
        redirect('login.php');
    }

    // Load the current database record before any role check. This makes
    // deactivation, suspension, and role changes effective immediately even
    // when the browser still has an older authenticated session.
    global $conn;
    require_once __DIR__ . '/db.php';
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Administrator access is required.');
    }
}
