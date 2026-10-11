<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_admin();require_once __DIR__.'/../includes/db.php';require_post();verify_csrf();
$targetId=filter_input(INPUT_POST,'user_id',FILTER_VALIDATE_INT);$role=(string)($_POST['role']??'');$status=(string)($_POST['status']??'');
if(!$targetId||!in_array($role,['student','admin'],true)||!in_array($status,['active','inactive','suspended'],true)){flash('danger','Invalid access settings.');redirect('admin/users.php');}
if($targetId===current_user_id()&&($role!=='admin'||$status!=='active')){flash('warning','You cannot remove your own active admin access.');redirect('admin/users.php');}
$stmt=$conn->prepare('UPDATE `USER` SET role=?,status=? WHERE user_id=?');$stmt->bind_param('ssi',$role,$status,$targetId);$stmt->execute();flash('success','User access updated.');redirect('admin/users.php');
