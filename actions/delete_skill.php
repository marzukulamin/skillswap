<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php'; require_post(); verify_csrf();
$skillId = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT); $userId = current_user_id();
if (!$skillId) { flash('danger', 'Invalid skill.'); redirect('student/skills.php'); }
$stmt = $conn->prepare('SELECT COUNT(*) FROM `SESSION_BOOKING` sb JOIN `SKILL` s ON s.skill_id = sb.skill_id WHERE s.skill_id = ? AND s.user_id = ?');
$stmt->bind_param('ii', $skillId, $userId); $stmt->execute(); $bookingCount = (int) $stmt->get_result()->fetch_row()[0];
if ($bookingCount > 0) { flash('warning', 'This skill has booking history and cannot be deleted. Set it to inactive instead.'); redirect('student/skills.php'); }
$stmt = $conn->prepare('DELETE FROM `SKILL` WHERE skill_id = ? AND user_id = ?'); $stmt->bind_param('ii', $skillId, $userId); $stmt->execute();
flash($stmt->affected_rows ? 'success' : 'danger', $stmt->affected_rows ? 'Skill deleted.' : 'Skill not found or not owned by you.'); redirect('student/skills.php');
