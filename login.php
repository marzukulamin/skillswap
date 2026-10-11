<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_guest();
$pageTitle = 'Sign in';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container" style="max-width: 520px">
        <div class="surface-card p-4 p-lg-5">
            <span class="feature-icon mb-3"><i class="bi bi-person-lock"></i></span>
            <h1 class="section-title h2 mb-2">Welcome back</h1>
            <p class="text-secondary mb-4">Sign in with the phone number used during registration.</p>
            <form method="post" action="<?= e(url('actions/login_action.php')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" type="tel" maxlength="30" value="<?= old('phone') ?>" required autofocus></div>
                <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required></div>
                <button class="btn btn-primary w-100" type="submit">Sign in</button>
            </form>
            <p class="small text-secondary text-center mt-4 mb-0">New to SkillSwap? <a href="<?= e(url('register.php')) ?>">Create an account</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

