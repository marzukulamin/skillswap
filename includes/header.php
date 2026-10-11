<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'SkillSwap';
$pageDescription = $pageDescription ?? 'Learn something. Teach something. Grow together.';
$flashes = pull_flashes();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> | SkillSwap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<?php require __DIR__ . '/navbar.php'; ?>
<main class="flex-grow-1">
    <?php if ($flashes): ?>
        <div class="container app-alerts pt-3">
            <?php foreach ($flashes as $message): ?>
                <div class="alert alert-<?= e($message['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
