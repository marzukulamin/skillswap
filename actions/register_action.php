<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_guest();
require_post();
verify_csrf();

$firstName = trim((string) ($_POST['F_name'] ?? ''));
$middleName = trim((string) ($_POST['m_name'] ?? ''));
$lastName = trim((string) ($_POST['L_name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmation = (string) ($_POST['password_confirmation'] ?? '');
$_SESSION['old'] = ['F_name' => $firstName, 'm_name' => $middleName, 'L_name' => $lastName, 'phone' => $phone];

$errors = [];
if ($firstName === '' || mb_strlen($firstName) > 100) $errors[] = 'Enter a valid first name.';
if (mb_strlen($middleName) > 100) $errors[] = 'Middle name must be 100 characters or fewer.';
if ($lastName === '' || mb_strlen($lastName) > 100) $errors[] = 'Enter a valid last name.';
if (!preg_match('/^[0-9+()\-\s]{7,30}$/', $phone)) $errors[] = 'Enter a valid phone number.';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($password !== $confirmation) $errors[] = 'The password confirmation does not match.';

$stmt = $conn->prepare('SELECT user_id FROM `USER` WHERE phone = ? LIMIT 1');
$stmt->bind_param('s', $phone);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) $errors[] = 'That phone number is already registered.';

if ($errors) {
    foreach ($errors as $error) flash('danger', $error);
    redirect('register.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'student';
$status = 'active';
$joinDate = date('Y-m-d');
$stmt = $conn->prepare(
    'INSERT INTO `USER` (F_name, m_name, L_name, phone, join_date, role, password, status)
     VALUES (?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssssss', $firstName, $middleName, $lastName, $phone, $joinDate, $role, $hash, $status);
$stmt->execute();

unset($_SESSION['old']);
flash('success', 'Account created. You can now sign in.');
redirect('login.php');
