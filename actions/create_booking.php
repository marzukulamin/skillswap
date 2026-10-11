<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php'; require_post(); verify_csrf();
$skillId = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT); $slotInput = trim((string) ($_POST['session_slot'] ?? '')); $meetingLink = trim((string) ($_POST['meeting_link'] ?? '')); $userId = current_user_id();
$timestamp = strtotime($slotInput); $errors = [];
if (!$skillId) $errors[] = 'Choose a valid skill.';
if (!$timestamp || $timestamp <= time()) $errors[] = 'Choose a future session time.';
if ($meetingLink !== '' && (!filter_var($meetingLink, FILTER_VALIDATE_URL) || mb_strlen($meetingLink) > 255)) $errors[] = 'Enter a valid meeting link of 255 characters or fewer.';
$stmt = $conn->prepare("SELECT s.user_id FROM `SKILL` s JOIN `USER` u ON u.user_id = s.user_id WHERE s.skill_id = ? AND s.status = 'active' AND u.status = 'active' LIMIT 1"); $stmt->bind_param('i', $skillId); $stmt->execute(); $skill = $stmt->get_result()->fetch_assoc();
if (!$skill) $errors[] = 'That skill is not available.'; elseif ((int) $skill['user_id'] === $userId) $errors[] = 'You cannot book your own skill.';
if ($errors) { foreach ($errors as $error) flash('danger', $error); redirect('student/search_skills.php'); }
$slot = date('Y-m-d H:i:s', $timestamp); $payment = 'unpaid'; $status = 'pending';
$stmt = $conn->prepare("INSERT INTO `SESSION_BOOKING` (bookedByUser, skill_id, meeting_link, payment_status, session_status, session_slot) VALUES (?, ?, NULLIF(?, ''), ?, ?, ?)");
$stmt->bind_param('iissss', $userId, $skillId, $meetingLink, $payment, $status, $slot); $stmt->execute();
flash('success', 'Session request sent to the skill owner.'); redirect('student/bookings.php');
