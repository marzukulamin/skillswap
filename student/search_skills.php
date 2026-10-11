<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$query = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$level = trim((string) ($_GET['level'] ?? ''));
$viewerId = current_user_id() ?? 0;

$stmt = $conn->prepare(
    "SELECT s.skill_id, s.user_id, s.title, s.description, s.level, s.category_name,
            u.F_name, u.m_name, u.L_name, u.location, u.profile_pic, u.S_time, u.E_time, u.time_zone,
            COUNT(DISTINCT sb.booking_id) AS booking_count,
            ROUND(AVG(r.rating), 1) AS avg_rating,
            COUNT(r.booking_id) AS review_count
     FROM `SKILL` s
     JOIN `USER` u ON u.user_id = s.user_id
     LEFT JOIN `SESSION_BOOKING` sb ON sb.skill_id = s.skill_id
     LEFT JOIN `REVIEW` r ON r.booking_id = sb.booking_id
     WHERE s.status = 'active' AND u.status = 'active' AND s.user_id <> ?
       AND (? = '' OR s.title LIKE CONCAT('%', ?, '%') OR s.description LIKE CONCAT('%', ?, '%') OR s.category_name LIKE CONCAT('%', ?, '%'))
       AND (? = '' OR s.category_name = ?)
       AND (? = '' OR s.level = ?)
     GROUP BY s.skill_id, s.user_id, s.title, s.description, s.level, s.category_name,
              u.F_name, u.m_name, u.L_name, u.location, u.profile_pic, u.S_time, u.E_time, u.time_zone
     ORDER BY booking_count DESC, avg_rating DESC, s.skill_id DESC"
);
$stmt->bind_param('issssssss', $viewerId, $query, $query, $query, $query, $category, $category, $level, $level);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT DISTINCT category_name FROM `SKILL` WHERE status = 'active' AND category_name IS NOT NULL AND category_name <> '' ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Explore skills';
require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5"><div class="container">
    <div class="text-center mb-4"><div class="eyebrow text-primary mb-2">Community knowledge</div><h1 class="section-title display-6 mb-2">What would you like to learn?</h1><p class="text-secondary">Find students ready to share practical skills and experience.</p></div>
    <form class="surface-card p-3 p-lg-4 mb-4" method="get">
        <div class="row g-2">
            <div class="col-lg-6"><label class="visually-hidden" for="q">Search</label><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span><input class="form-control border-start-0" id="q" name="q" value="<?= e($query) ?>" placeholder="Search by skill, description, or category"></div></div>
            <div class="col-sm-5 col-lg-2"><label class="visually-hidden" for="category">Category</label><select class="form-select" id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $item): ?><option value="<?= e($item['category_name']) ?>"<?= selected($item['category_name'], $category) ?>><?= e($item['category_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-sm-4 col-lg-2"><label class="visually-hidden" for="level">Level</label><select class="form-select" id="level" name="level"><option value="">All levels</option><?php foreach (['Beginner friendly','Intermediate','Advanced','All levels'] as $option): ?><option<?= selected($option, $level) ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
            <div class="col-sm-3 col-lg-2"><button class="btn btn-primary w-100 h-100" type="submit">Search</button></div>
        </div>
    </form>
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0"><?= count($skills) ?> skill<?= count($skills) === 1 ? '' : 's' ?> found</h2><?php if ($query || $category || $level): ?><a class="small" href="<?= e(url('student/search_skills.php')) ?>">Clear filters</a><?php endif; ?></div>
    <div class="row g-4">
        <?php if (!$skills): ?><div class="col-12"><div class="surface-card empty-state"><i class="bi bi-search d-block mb-3"></i><h2 class="h5">No matching skills</h2><p class="mb-0">Try a broader search or remove a filter.</p></div></div><?php endif; ?>
        <?php foreach ($skills as $skill): ?>
            <div class="col-md-6 col-xl-4"><article class="surface-card skill-card h-100 p-4 d-flex flex-column">
                <div class="d-flex justify-content-between gap-2 mb-3"><span class="badge badge-soft rounded-pill"><?= e($skill['category_name'] ?: 'General') ?></span><span class="small rating-stars"><i class="bi bi-star-fill"></i> <?= e($skill['avg_rating'] ?: 'New') ?><?php if ($skill['review_count']): ?> <span class="text-secondary">(<?= (int) $skill['review_count'] ?>)</span><?php endif; ?></span></div>
                <h2 class="h5 mb-2"><?= e($skill['title']) ?></h2><p class="text-secondary small flex-grow-1"><?= e(mb_strimwidth((string) $skill['description'], 0, 160, '...')) ?></p>
                <div class="d-flex align-items-center gap-2 py-3 border-top border-bottom mb-3"><div class="profile-avatar profile-avatar-sm"><?= e(initials($skill)) ?></div><div><div class="small fw-semibold"><?= e(full_name($skill)) ?></div><div class="text-secondary small"><?= e($skill['location'] ?: 'Location not set') ?></div></div></div>
                <div class="d-flex justify-content-between align-items-center"><span class="small text-secondary"><i class="bi bi-bar-chart me-1"></i><?= e($skill['level'] ?: 'Any level') ?></span>
                    <?php if (current_user_id()): ?><button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#bookSkill<?= (int) $skill['skill_id'] ?>">Request session</button><?php else: ?><a class="btn btn-primary btn-sm" href="<?= e(url('login.php')) ?>">Sign in to book</a><?php endif; ?>
                </div>
            </article></div>
            <?php if (current_user_id()): ?>
            <div class="modal fade" id="bookSkill<?= (int) $skill['skill_id'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow"><form method="post" action="<?= e(url('actions/create_booking.php')) ?>"><div class="modal-header"><h2 class="modal-title fs-5">Request <?= e($skill['title']) ?></h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><?= csrf_field() ?><input type="hidden" name="skill_id" value="<?= (int) $skill['skill_id'] ?>"><div class="mb-3"><label class="form-label" for="slot<?= (int) $skill['skill_id'] ?>">Preferred session time</label><input class="form-control" id="slot<?= (int) $skill['skill_id'] ?>" name="session_slot" type="datetime-local" min="<?= e(date('Y-m-d\TH:i')) ?>" required></div><div><label class="form-label" for="link<?= (int) $skill['skill_id'] ?>">Meeting link <span class="text-secondary fw-normal">(optional)</span></label><input class="form-control" id="link<?= (int) $skill['skill_id'] ?>" name="meeting_link" type="url" maxlength="255" placeholder="https://..."></div><?php if ($skill['S_time'] || $skill['E_time']): ?><div class="form-text mt-3">Mentor availability: <?= e($skill['S_time'] ?: '?') ?> to <?= e($skill['E_time'] ?: '?') ?> <?= e($skill['time_zone']) ?></div><?php endif; ?></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Send request</button></div></form></div></div></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
