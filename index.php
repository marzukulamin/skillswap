<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Learn. Teach. Grow.';
$featured = [];
$result = $conn->query(
    "SELECT s.skill_id, s.title, s.level, s.category_name, s.description,
            u.F_name, u.m_name, u.L_name,
            COUNT(sb.booking_id) AS booking_count,
            ROUND(AVG(r.rating), 1) AS avg_rating
     FROM `SKILL` s
     JOIN `USER` u ON u.user_id = s.user_id
     LEFT JOIN `SESSION_BOOKING` sb ON sb.skill_id = s.skill_id
     LEFT JOIN `REVIEW` r ON r.booking_id = sb.booking_id
     WHERE s.status = 'active' AND u.status = 'active'
     GROUP BY s.skill_id, s.title, s.level, s.category_name, s.description,
              u.F_name, u.m_name, u.L_name
     ORDER BY booking_count DESC, s.skill_id DESC
     LIMIT 6"
);
$featured = $result->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<section class="hero py-5 py-lg-6">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="eyebrow mb-3 text-warning">Student-powered learning</div>
                <h1 class="hero-title mb-4">Your next skill is already nearby.</h1>
                <p class="hero-subtitle mb-4">Connect with students who can teach what you want to learn, share what you know, and turn campus talent into real collaboration.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(url(current_user_id() ? 'student/search_skills.php' : 'register.php')) ?>" class="btn btn-warning btn-lg px-4">
                        <?= current_user_id() ? 'Explore skills' : 'Start swapping skills' ?>
                    </a>
                    <a href="<?= e(url('student/search_skills.php')) ?>" class="btn btn-outline-light btn-lg px-4">Browse the community</a>
                    <a href="<?= e(url('demo.php')) ?>" class="btn btn-light btn-lg px-4">Test user workflows</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="surface-card text-dark p-4 p-lg-5">
                    <div class="d-flex gap-3 mb-4">
                        <span class="feature-icon"><i class="bi bi-search"></i></span>
                        <div><strong>Discover</strong><div class="text-secondary">Filter skills by topic and level.</div></div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <span class="feature-icon"><i class="bi bi-calendar2-check"></i></span>
                        <div><strong>Book</strong><div class="text-secondary">Request a session that fits your schedule.</div></div>
                    </div>
                    <div class="d-flex gap-3">
                        <span class="feature-icon"><i class="bi bi-stars"></i></span>
                        <div><strong>Grow</strong><div class="text-secondary">Review sessions and earn community badges.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
            <div>
                <div class="eyebrow text-primary mb-2">Trending now</div>
                <h2 class="section-title mb-0">Skills students are sharing</h2>
            </div>
            <a class="btn btn-outline-primary" href="<?= e(url('student/search_skills.php')) ?>">View all skills <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php if (!$featured): ?>
                <div class="col-12"><div class="surface-card empty-state"><i class="bi bi-lightbulb d-block mb-3"></i><h3 class="h5">The community is ready for its first skill</h3><p class="mb-0">Create an account and publish what you can teach.</p></div></div>
            <?php endif; ?>
            <?php foreach ($featured as $skill): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="surface-card skill-card h-100 p-4">
                        <div class="d-flex justify-content-between gap-2 mb-3">
                            <span class="badge badge-soft rounded-pill"><?= e($skill['category_name'] ?: 'General') ?></span>
                            <span class="small text-secondary"><i class="bi bi-people me-1"></i><?= (int) $skill['booking_count'] ?></span>
                        </div>
                        <h3 class="h5 mb-2"><?= e($skill['title']) ?></h3>
                        <p class="text-secondary small mb-3"><?= e(mb_strimwidth((string) $skill['description'], 0, 115, '...')) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="small fw-semibold"><?= e(full_name($skill)) ?></span>
                            <span class="small rating-stars"><i class="bi bi-star-fill"></i> <?= e($skill['avg_rating'] ?: 'New') ?></span>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

