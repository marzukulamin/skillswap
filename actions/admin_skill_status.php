<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_admin();require_once __DIR__.'/../includes/db.php';require_post();verify_csrf();$skillId=filter_input(INPUT_POST,'skill_id',FILTER_VALIDATE_INT);$status=(string)($_POST['status']??'');if(!$skillId||!in_array($status,['active','inactive'],true)){flash('danger','Invalid skill update.');redirect('admin/skills.php');}$stmt=$conn->prepare('UPDATE `SKILL` SET status=? WHERE skill_id=?');$stmt->bind_param('si',$status,$skillId);$stmt->execute();flash('success','Skill status updated.');redirect('admin/skills.php');
