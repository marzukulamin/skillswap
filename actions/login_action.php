<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_guest();
require_post();
verify_csrf();

$phone = trim((string) ($_POST['phone'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$_SESSION['old'] = ['phone' => $phone];

$stmt = $conn->prepare('SELECT * FROM `USER` WHERE phone = ? ORDER BY user_id ASC LIMIT 1');
$stmt->bind_param('s', $phone);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    flash('danger', 'The phone number or password is incorrect.');
    redirect('login.php');
}

if (in_array(strtolower((string) $user['status']), ['inactive', 'suspended'], true)) {
    flash('danger', 'This account is not currently active.');
    redirect('login.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['role'] = $user['role'];
$_SESSION['user'] = $user;
unset($_SESSION['old']);

flash('success', 'Welcome back, ' . $user['F_name'] . '!');
redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
