<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php'; require_post(); verify_csrf();
$bookingId = filter_input(INPUT_POST,'booking_id',FILTER_VALIDATE_INT); $sessionStatus=(string)($_POST['session_status']??''); $paymentStatus=(string)($_POST['payment_status']??'unpaid'); $meetingLink=trim((string)($_POST['meeting_link']??'')); $userId=current_user_id();
if (!$bookingId || !in_array($sessionStatus,allowed_session_statuses(),true) || !in_array($paymentStatus,allowed_payment_statuses(),true) || ($meetingLink!==''&&(!filter_var($meetingLink,FILTER_VALIDATE_URL)||mb_strlen($meetingLink)>255))) { flash('danger','Invalid booking update.'); redirect('student/bookings.php'); }
$stmt=$conn->prepare('SELECT sb.bookedByUser,s.user_id AS owner_id FROM `SESSION_BOOKING` sb JOIN `SKILL` s ON s.skill_id=sb.skill_id WHERE sb.booking_id=? LIMIT 1'); $stmt->bind_param('i',$bookingId); $stmt->execute(); $booking=$stmt->get_result()->fetch_assoc();
if(!$booking){http_response_code(404);exit('Booking not found.');}
$isAdmin=($_SESSION['role']??'')==='admin'; $isOwner=(int)$booking['owner_id']===$userId; $isLearner=(int)$booking['bookedByUser']===$userId;
if(!$isAdmin&&!$isOwner&&!($isLearner&&$sessionStatus==='cancelled')){http_response_code(403);exit('You cannot update this booking.');}
if($isLearner&&!$isOwner&&!$isAdmin){$stmt=$conn->prepare("UPDATE `SESSION_BOOKING` SET session_status='cancelled' WHERE booking_id=? AND bookedByUser=?");$stmt->bind_param('ii',$bookingId,$userId);}else{$stmt=$conn->prepare("UPDATE `SESSION_BOOKING` SET session_status=?,payment_status=?,meeting_link=NULLIF(?,'') WHERE booking_id=?");$stmt->bind_param('sssi',$sessionStatus,$paymentStatus,$meetingLink,$bookingId);} $stmt->execute();
flash('success','Booking updated.'); redirect(($_SESSION['role']??'')==='admin'?'admin/bookings.php':'student/booking_details.php?id='.$bookingId);
