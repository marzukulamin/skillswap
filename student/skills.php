<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
$userId = current_user_id();
$stmt = $conn->prepare(
    'SELECT s.skill_id, s.user_id, s.title, s.description, s.level, s.status,
            s.category_name, COUNT(sb.booking_id) AS booking_count
     FROM `SKILL` s
     LEFT JOIN `SESSION_BOOKING` sb ON sb.skill_id = s.skill_id
     WHERE s.user_id = ?
     GROUP BY s.skill_id, s.user_id, s.title, s.description, s.level, s.status, s.category_name
     ORDER BY s.skill_id DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'My skills';
require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5"><div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4"><div><div class="eyebrow text-primary mb-1">Teach the community</div><h1 class="section-title h2 mb-0">My skills</h1></div><a class="btn btn-primary" href="<?= e(url('student/add_skill.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Add a skill</a></div>
    <div class="row g-4">
        <?php if (!$skills): ?><div class="col-12"><div class="surface-card empty-state"><i class="bi bi-lightbulb d-block mb-3"></i><h2 class="h5">You have not listed a skill yet</h2><p>Share something you know well and invite others to learn.</p><a class="btn btn-primary" href="<?= e(url('student/add_skill.php')) ?>">Create first skill</a></div></div><?php endif; ?>
        <?php foreach ($skills as $skill): ?>
            <div class="col-md-6 col-xl-4"><article class="surface-card skill-card h-100 p-4">
                <div class="d-flex justify-content-between mb-3"><span class="badge badge-soft rounded-pill"><?= e($skill['category_name'] ?: 'General') ?></span><span class="badge text-bg-<?= e(status_class($skill['status'])) ?>"><?= e(ucfirst($skill['status'])) ?></span></div>
                <h2 class="h5"><?= e($skill['title']) ?></h2><p class="text-secondary small"><?= e(mb_strimwidth((string) $skill['description'], 0, 145, '...')) ?></p>
                <div class="d-flex gap-3 text-secondary small mb-4"><span><i class="bi bi-bar-chart me-1"></i><?= e($skill['level'] ?: 'Any level') ?></span><span><i class="bi bi-calendar-check me-1"></i><?= (int) $skill['booking_count'] ?> bookings</span></div>
                <div class="d-flex gap-2 mt-auto"><a class="btn btn-sm btn-outline-primary" href="<?= e(url('student/edit_skill.php?id=' . (int) $skill['skill_id'])) ?>">Edit</a><form method="post" action="<?= e(url('actions/delete_skill.php')) ?>"><?= csrf_field() ?><input type="hidden" name="skill_id" value="<?= (int) $skill['skill_id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this skill? Existing bookings will prevent deletion.">Delete</button></form></div>
            </article></div>
        <?php endforeach; ?>
    </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
