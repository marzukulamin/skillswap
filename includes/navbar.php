<?php
$navUser = current_user();
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('index.php')) ?>">
            <span class="brand-mark"><i class="bi bi-arrow-left-right"></i></span>
            <span>SkillSwap</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('demo.php')) ?>">Try guided demo</a></li>
                <?php if (!$navUser): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('student/search_skills.php')) ?>">Explore skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('login.php')) ?>">Sign in</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-light btn-sm px-3" href="<?= e(url('register.php')) ?>">Join SkillSwap</a></li>
                <?php elseif ($isAdmin): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/dashboard.php')) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/users.php')) ?>">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/skills.php')) ?>">Skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/bookings.php')) ?>">Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/reviews.php')) ?>">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/badges.php')) ?>">Badges</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="<?= e(url('logout.php')) ?>">Sign out</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('student/dashboard.php')) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('student/search_skills.php')) ?>">Find skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('student/skills.php')) ?>">My skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('student/bookings.php')) ?>">Sessions</a></li>
                    <li class="nav-item dropdown ms-lg-2">
                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= e(initials($navUser)) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><h6 class="dropdown-header"><?= e(full_name($navUser)) ?></h6></li>
                            <li><a class="dropdown-item" href="<?= e(url('student/profile.php')) ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('student/reviews.php')) ?>"><i class="bi bi-star me-2"></i>Reviews</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('student/badges.php')) ?>"><i class="bi bi-award me-2"></i>Badges</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= e(url('logout.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
