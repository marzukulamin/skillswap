<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_post();
verify_csrf();

$userId = current_user_id();
$firstName = trim((string) ($_POST['F_name'] ?? ''));
$middleName = trim((string) ($_POST['m_name'] ?? ''));
$lastName = trim((string) ($_POST['L_name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$location = trim((string) ($_POST['location'] ?? ''));
$bio = trim((string) ($_POST['bio'] ?? ''));
$startTime = trim((string) ($_POST['S_time'] ?? ''));
$endTime = trim((string) ($_POST['E_time'] ?? ''));
$timeZone = trim((string) ($_POST['time_zone'] ?? ''));

$errors = [];
if ($firstName === '' || mb_strlen($firstName) > 100) $errors[] = 'Enter a valid first name.';
if (mb_strlen($middleName) > 100) $errors[] = 'Middle name must be 100 characters or fewer.';
if ($lastName === '' || mb_strlen($lastName) > 100) $errors[] = 'Enter a valid last name.';
if (!preg_match('/^[0-9+()\-\s]{7,30}$/', $phone)) $errors[] = 'Enter a valid phone number.';
if (mb_strlen($location) > 150) $errors[] = 'Location must be 150 characters or fewer.';
if (mb_strlen($bio) > 1500) $errors[] = 'Bio must be 1,500 characters or fewer.';
if (mb_strlen($timeZone) > 100) $errors[] = 'Time zone must be 100 characters or fewer.';
foreach (['Start time' => $startTime, 'End time' => $endTime] as $label => $timeValue) {
    if ($timeValue !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeValue)) $errors[] = "$label must use a valid 24-hour time.";
}

$stmt = $conn->prepare('SELECT user_id FROM `USER` WHERE phone = ? AND user_id <> ? LIMIT 1');
$stmt->bind_param('si', $phone, $userId);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) $errors[] = 'That phone number is already used by another account.';

$profilePath = current_user()['profile_pic'] ?? null;
if (!$errors && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'The profile image must upload successfully and be no larger than 2 MB.';
    } else {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors[] = 'Use a JPG, PNG, or WebP profile image.';
        } else {
            $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
            $destination = __DIR__ . '/../assets/images/profiles/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = 'The profile image could not be saved.';
            } else {
                $profilePath = 'assets/images/profiles/' . $filename;
            }
        }
    }
}

if ($errors) {
    foreach ($errors as $error) flash('danger', $error);
    redirect('student/profile.php');
}

$stmt = $conn->prepare(
    "UPDATE `USER` SET F_name = ?, m_name = NULLIF(?, ''), L_name = ?, phone = ?,
        location = NULLIF(?, ''), bio = NULLIF(?, ''), profile_pic = ?,
        S_time = NULLIF(?, ''), E_time = NULLIF(?, ''), time_zone = NULLIF(?, '')
     WHERE user_id = ?"
);
$stmt->bind_param('ssssssssssi', $firstName, $middleName, $lastName, $phone, $location, $bio, $profilePath, $startTime, $endTime, $timeZone, $userId);
$stmt->execute();
refresh_session_user($conn);
flash('success', 'Your profile has been updated.');
redirect('student/profile.php');
