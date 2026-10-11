<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php'; require_post(); verify_csrf();
$skillId = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT); $userId = current_user_id();
$title = trim((string) ($_POST['title'] ?? '')); $description = trim((string) ($_POST['description'] ?? '')); $level = trim((string) ($_POST['level'] ?? '')); $category = trim((string) ($_POST['category_name'] ?? ''));
$status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'inactive';
if (!$skillId || $title === '' || mb_strlen($title) > 150 || mb_strlen($description) > 2500 || mb_strlen($level) > 50 || mb_strlen($category) > 100) { flash('danger', 'Check the submitted skill details.'); redirect('student/skills.php'); }
$ownerStmt = $conn->prepare('SELECT skill_id FROM `SKILL` WHERE skill_id = ? AND user_id = ? LIMIT 1');
$ownerStmt->bind_param('ii', $skillId, $userId);
$ownerStmt->execute();
if (!$ownerStmt->get_result()->fetch_assoc()) { http_response_code(403); exit('This skill was not found or is not owned by you.'); }
$stmt = $conn->prepare("UPDATE `SKILL` SET title = ?, description = NULLIF(?, ''), level = NULLIF(?, ''), status = ?, category_name = NULLIF(?, '') WHERE skill_id = ? AND user_id = ?");
$stmt->bind_param('sssssii', $title, $description, $level, $status, $category, $skillId, $userId); $stmt->execute();
flash('success', 'Skill updated.'); redirect('student/skills.php');
