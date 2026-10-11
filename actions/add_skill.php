<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_post(); verify_csrf();
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$level = trim((string) ($_POST['level'] ?? ''));
$status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'inactive';
$category = trim((string) ($_POST['category_name'] ?? ''));
if ($title === '' || mb_strlen($title) > 150 || mb_strlen($description) > 2500 || mb_strlen($level) > 50 || mb_strlen($category) > 100) { flash('danger', 'Check the skill title, level, category, and description lengths.'); redirect('student/add_skill.php'); }
$userId = current_user_id();
$stmt = $conn->prepare("INSERT INTO `SKILL` (user_id, title, description, level, status, category_name) VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, NULLIF(?, ''))");
$stmt->bind_param('isssss', $userId, $title, $description, $level, $status, $category);
$stmt->execute();
flash('success', 'Your skill is now listed.'); redirect('student/skills.php');
