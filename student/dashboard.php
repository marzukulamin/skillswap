<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
refresh_session_user($conn);

$userId = current_user_id();
$user = current_user();
$skillCount = (int) query_value($conn, 'SELECT COUNT(*) FROM `SKILL` WHERE user_id = ?', [$userId], 'i');
$learningCount = (int) query_value($conn, 'SELECT COUNT(*) FROM `SESSION_BOOKING` WHERE bookedByUser = ?', [$userId], 'i');
$mentorCount = (int) query_value(
    $conn,
    'SELECT COUNT(*) FROM `SESSION_BOOKING` sb JOIN `SKILL` s ON s.skill_id = sb.skill_id WHERE s.user_id = ?',
    [$userId],
    'i'
);
$badgeCount = (int) query_value($conn, 'SELECT COUNT(*) FROM `USER_BADGE` WHERE user_id = ?', [$userId], 'i');

$stmt = $conn->prepare(
    "SELECT sb.booking_id, sb.session_slot, sb.session_status, s.title,
            owner.F_name AS owner_first, owner.L_name AS owner_last,
            learner.F_name AS learner_first, learner.L_name AS learner_last,
            CASE WHEN sb.bookedByUser = ? THEN 'learning' ELSE 'mentoring' END AS perspective
     FROM `SESSION_BOOKING` sb
     JOIN `SKILL` s ON s.skill_id = sb.skill_id
     JOIN `USER` owner ON owner.user_id = s.user_id
     JOIN `USER` learner ON learner.user_id = sb.bookedByUser
     WHERE (sb.bookedByUser = ? OR s.user_id = ?)
       AND sb.session_status IN ('pending', 'accepted')
     ORDER BY sb.session_slot ASC
     LIMIT 6"
);
$stmt->bind_param('iii', $userId, $userId, $userId);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="dashboard-welcome p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="eyebrow text-warning mb-2">Your learning exchange</div>
                    <h1 class="section-title mb-2">Hello, <?= e($user['F_name']) ?>.</h1>
                    <p class="text-white-50 mb-0">Share a skill, discover a mentor, or keep your next session moving.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="btn btn-warning" href="<?= e(url('student/search_skills.php')) ?>"><i class="bi bi-search me-1"></i> Find a skill</a>
                    <a class="btn btn-outline-light ms-1" href="<?= e(url('student/add_skill.php')) ?>"><i class="bi bi-plus-lg me-1"></i> Add skill</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <?php foreach ([
                ['My skills', $skillCount, 'bi-lightbulb'],
                ['Learning requests', $learningCount, 'bi-journal-check'],
                ['Mentoring requests', $mentorCount, 'bi-person-video3'],
                ['Badges earned', $badgeCount, 'bi-award'],
            ] as [$label, $value, $icon]): ?>
                <div class="col-6 col-lg-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3"><span class="stat-icon"><i class="bi <?= e($icon) ?>"></i></span><div><div class="h3 mb-0 fw-bold"><?= (int) $value ?></div><div class="text-secondary small"><?= e($label) ?></div></div></div></div></div>
            <?php endforeach; ?>
        </div>

        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><div class="eyebrow text-primary mb-1">Schedule</div><h2 class="h4 section-title mb-0">Upcoming and pending sessions</h2></div>
                <a class="btn btn-sm btn-outline-primary" href="<?= e(url('student/bookings.php')) ?>">All sessions</a>
            </div>
            <?php if (!$upcoming): ?>
                <div class="empty-state py-5"><i class="bi bi-calendar2-week d-block mb-3"></i><h3 class="h6">Nothing scheduled yet</h3><p class="mb-0">Browse community skills to request your first session.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Skill</th><th>Role</th><th>With</th><th>When</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($upcoming as $booking): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($booking['title']) ?></td>
                                <td><?= $booking['perspective'] === 'learning' ? 'Learner' : 'Mentor' ?></td>
                                <td><?= e($booking['perspective'] === 'learning' ? trim($booking['owner_first'] . ' ' . $booking['owner_last']) : trim($booking['learner_first'] . ' ' . $booking['learner_last'])) ?></td>
                                <td><?= e(format_datetime($booking['session_slot'])) ?></td>
                                <td><span class="badge text-bg-<?= e(status_class($booking['session_status'])) ?>"><?= e(ucfirst($booking['session_status'])) ?></span></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= e(url('student/booking_details.php?id=' . (int) $booking['booking_id'])) ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

