<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php'; require_post(); verify_csrf();
$bookingId=filter_input(INPUT_POST,'booking_id',FILTER_VALIDATE_INT); $rating=filter_input(INPUT_POST,'rating',FILTER_VALIDATE_INT); $comment=trim((string)($_POST['comment']??'')); $userId=current_user_id();
if(!$bookingId||$rating<1||$rating>5||mb_strlen($comment)>2000){flash('danger','Enter a rating from 1 to 5 and a valid comment.');redirect('student/bookings.php');}
$stmt=$conn->prepare("SELECT booking_id FROM `SESSION_BOOKING` WHERE booking_id=? AND bookedByUser=? AND session_status='completed' LIMIT 1");$stmt->bind_param('ii',$bookingId,$userId);$stmt->execute();
if(!$stmt->get_result()->fetch_assoc()){http_response_code(403);exit('This booking is not eligible for your review.');}
$date=date('Y-m-d H:i:s');
try{$stmt=$conn->prepare("INSERT INTO `REVIEW` (booking_id,user_id,rating,comment,review_date) VALUES (?,?,?,NULLIF(?,''),?)");$stmt->bind_param('iiiss',$bookingId,$userId,$rating,$comment,$date);$stmt->execute();flash('success','Thank you for reviewing the session.');}catch(mysqli_sql_exception $exception){if((int)$exception->getCode()===1062){flash('warning','This booking already has a review.');}else{throw $exception;}}
redirect('student/booking_details.php?id='.$bookingId);
