<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
$skillId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = current_user_id();
$stmt = $conn->prepare('SELECT * FROM `SKILL` WHERE skill_id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $skillId, $userId);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();
if (!$skill) { http_response_code(404); exit('Skill not found.'); }
$pageTitle = 'Edit skill';
$formAction = 'actions/update_skill.php';
$formHeading = 'Edit your skill';
$submitLabel = 'Save changes';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/skill_form.php';
require __DIR__ . '/../includes/footer.php';
