<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$q = trim((string) ($_GET['q'] ?? ''));
$stmt = $conn->prepare(
    "SELECT u.user_id, u.F_name, u.m_name, u.L_name, u.phone, u.location,
            u.join_date, u.role, u.status,
            COUNT(DISTINCT s.skill_id) AS skill_count,
            COUNT(DISTINCT sb.booking_id) AS booking_count
     FROM `USER` u
     LEFT JOIN `SKILL` s ON s.user_id = u.user_id
     LEFT JOIN `SESSION_BOOKING` sb ON sb.bookedByUser = u.user_id
     WHERE (? = '' OR CONCAT_WS(' ', u.F_name, u.m_name, u.L_name) LIKE CONCAT('%', ?, '%')
            OR u.phone LIKE CONCAT('%', ?, '%'))
     GROUP BY u.user_id, u.F_name, u.m_name, u.L_name, u.phone, u.location,
              u.join_date, u.role, u.status
     ORDER BY u.user_id DESC"
);
$stmt->bind_param('sss', $q, $q, $q);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage users';
require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
            <div><div class="eyebrow text-primary mb-1">Access control</div><h1 class="section-title h2 mb-0">Users</h1></div>
            <form class="d-flex gap-2" method="get"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Name or phone"><button class="btn btn-primary">Search</button></form>
        </div>
        <div class="surface-card overflow-hidden">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>User</th><th>Phone</th><th>Joined</th><th>Activity</th><th>Role & status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= e(full_name($user)) ?></strong><div class="small text-secondary"><?= e($user['location'] ?: 'No location') ?></div></td>
                            <td><?= e($user['phone']) ?></td>
                            <td><?= e(date('M j, Y', strtotime($user['join_date']))) ?></td>
                            <td><span class="small"><?= (int) $user['skill_count'] ?> skills / <?= (int) $user['booking_count'] ?> bookings</span></td>
                            <td><span class="badge badge-soft me-1"><?= e($user['role']) ?></span><span class="badge text-bg-<?= e(status_class($user['status'])) ?>"><?= e($user['status']) ?></span></td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUser<?= (int) $user['user_id'] ?>">Manage</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php foreach ($users as $user): ?>
    <div class="modal fade" id="editUser<?= (int) $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
            <form method="post" action="<?= e(url('actions/update_user_access.php')) ?>">
                <div class="modal-header"><h2 class="modal-title fs-5">Manage <?= e(full_name($user)) ?></h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
                    <div class="mb-3"><label class="form-label" for="role<?= (int) $user['user_id'] ?>">Role</label><select class="form-select" id="role<?= (int) $user['user_id'] ?>" name="role"><option value="student"<?= selected('student', $user['role']) ?>>Student</option><option value="admin"<?= selected('admin', $user['role']) ?>>Admin</option></select></div>
                    <div><label class="form-label" for="status<?= (int) $user['user_id'] ?>">Status</label><select class="form-select" id="status<?= (int) $user['user_id'] ?>" name="status"><option value="active"<?= selected('active', $user['status']) ?>>Active</option><option value="inactive"<?= selected('inactive', $user['status']) ?>>Inactive</option><option value="suspended"<?= selected('suspended', $user['status']) ?>>Suspended</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save access</button></div>
            </form>
        </div></div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
