<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pageTitle = 'Add a skill';
$skill = ['title' => '', 'description' => '', 'level' => 'Beginner friendly', 'status' => 'active', 'category_name' => ''];
$formAction = 'actions/add_skill.php';
$formHeading = 'Share a skill';
$submitLabel = 'Publish skill';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/skill_form.php';
require __DIR__ . '/../includes/footer.php';
