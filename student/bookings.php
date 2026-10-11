<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_login(); require_once __DIR__ . '/../includes/db.php';
$userId = current_user_id();
$stmt = $conn->prepare(
    "SELECT sb.*, s.title, s.user_id AS owner_id,
            owner.F_name AS owner_first, owner.L_name AS owner_last,
            learner.F_name AS learner_first, learner.L_name AS learner_last,
            r.booking_id AS reviewed
     FROM `SESSION_BOOKING` sb
     JOIN `SKILL` s ON s.skill_id = sb.skill_id
     JOIN `USER` owner ON owner.user_id = s.user_id
     JOIN `USER` learner ON learner.user_id = sb.bookedByUser
     LEFT JOIN `REVIEW` r ON r.booking_id = sb.booking_id
     WHERE sb.bookedByUser = ? OR s.user_id = ?
     ORDER BY sb.session_slot DESC"
); $stmt->bind_param('ii', $userId, $userId); $stmt->execute(); $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Sessions'; require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5"><div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4"><div><div class="eyebrow text-primary mb-1">Learning and mentoring</div><h1 class="section-title h2 mb-0">My sessions</h1></div><a class="btn btn-primary" href="<?= e(url('student/search_skills.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Request a session</a></div>
    <div class="surface-card overflow-hidden">
        <?php if (!$bookings): ?><div class="empty-state"><i class="bi bi-calendar2-week d-block mb-3"></i><h2 class="h5">No session history yet</h2><p>Find a skill and request a time with another student.</p><a class="btn btn-primary" href="<?= e(url('student/search_skills.php')) ?>">Browse skills</a></div><?php else: ?>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Skill</th><th>Your role</th><th>With</th><th>Session time</th><th>Status</th><th>Review</th><th></th></tr></thead><tbody>
        <?php foreach ($bookings as $booking): $isLearner = (int) $booking['bookedByUser'] === $userId; ?>
            <tr><td class="fw-semibold"><?= e($booking['title']) ?></td><td><?= $isLearner ? 'Learner' : 'Mentor' ?></td><td><?= e($isLearner ? trim($booking['owner_first'].' '.$booking['owner_last']) : trim($booking['learner_first'].' '.$booking['learner_last'])) ?></td><td><?= e(format_datetime($booking['session_slot'])) ?></td><td><span class="badge text-bg-<?= e(status_class($booking['session_status'])) ?>"><?= e(ucfirst($booking['session_status'])) ?></span></td><td><?= $booking['reviewed'] ? '<span class="text-success"><i class="bi bi-check-circle"></i> Done</span>' : '<span class="text-secondary">-</span>' ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= e(url('student/booking_details.php?id='.(int)$booking['booking_id'])) ?>">Details</a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
