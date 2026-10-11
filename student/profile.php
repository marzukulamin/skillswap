<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
refresh_session_user($conn);
$user = current_user();
$pageTitle = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<section class="py-4 py-lg-5">
    <div class="container" style="max-width: 980px">
        <div class="d-flex align-items-center gap-3 mb-4">
            <?php if (!empty($user['profile_pic'])): ?>
                <img class="profile-avatar" src="<?= e(url($user['profile_pic'])) ?>" alt="<?= e(full_name($user)) ?>">
            <?php else: ?>
                <div class="profile-avatar"><?= e(initials($user)) ?></div>
            <?php endif; ?>
            <div><div class="eyebrow text-primary mb-1">Member profile</div><h1 class="section-title h2 mb-1"><?= e(full_name($user)) ?></h1><div class="text-secondary">Joined <?= e(date('F Y', strtotime($user['join_date']))) ?></div></div>
        </div>
        <form class="surface-card p-4 p-lg-5" method="post" action="<?= e(url('actions/update_profile.php')) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <h2 class="h5 mb-3">Personal details</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-5"><label class="form-label" for="F_name">First name</label><input class="form-control" id="F_name" name="F_name" maxlength="100" value="<?= e($user['F_name']) ?>" required></div>
                <div class="col-md-3"><label class="form-label" for="m_name">Middle</label><input class="form-control" id="m_name" name="m_name" maxlength="100" value="<?= e($user['m_name']) ?>"></div>
                <div class="col-md-4"><label class="form-label" for="L_name">Last name</label><input class="form-control" id="L_name" name="L_name" maxlength="100" value="<?= e($user['L_name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" maxlength="30" value="<?= e($user['phone']) ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" maxlength="150" value="<?= e($user['location']) ?>" placeholder="Campus, city, or neighborhood"></div>
                <div class="col-12"><label class="form-label" for="bio">Bio</label><textarea class="form-control" id="bio" name="bio" rows="4" maxlength="1500" placeholder="What do you enjoy learning and teaching?"><?= e($user['bio']) ?></textarea></div>
            </div>
            <h2 class="h5 mb-3">Availability</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label" for="S_time">Available from</label><input class="form-control" id="S_time" name="S_time" type="time" value="<?= e($user['S_time']) ?>"></div>
                <div class="col-md-4"><label class="form-label" for="E_time">Available until</label><input class="form-control" id="E_time" name="E_time" type="time" value="<?= e($user['E_time']) ?>"></div>
                <div class="col-md-4"><label class="form-label" for="time_zone">Time zone</label><input class="form-control" id="time_zone" name="time_zone" maxlength="100" value="<?= e($user['time_zone']) ?>" placeholder="Asia/Dhaka"></div>
                <div class="col-12"><label class="form-label" for="profile_pic">Profile image</label><input class="form-control" id="profile_pic" name="profile_pic" type="file" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG, or WebP; maximum 2 MB.</div></div>
            </div>
            <button class="btn btn-primary" type="submit">Save profile</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
