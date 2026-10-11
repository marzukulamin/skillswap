<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_guest();
$pageTitle = 'Create your account';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container" style="max-width: 880px">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <div class="dashboard-welcome p-4 p-lg-5 h-100">
                    <div class="eyebrow text-warning mb-3">Join the exchange</div>
                    <h1 class="section-title h2">Turn what you know into someone else's breakthrough.</h1>
                    <p class="text-white-50 mb-4">Your phone number is your sign-in ID. SkillSwap never stores your password as plain text.</p>
                    <div class="d-flex gap-2 align-items-center"><i class="bi bi-shield-check text-warning fs-4"></i><span>Secure, student-focused access</span></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="surface-card p-4 p-lg-5">
                    <h2 class="h4 mb-4">Create an account</h2>
                    <form method="post" action="<?= e(url('actions/register_action.php')) ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-5"><label class="form-label" for="F_name">First name</label><input class="form-control" id="F_name" name="F_name" maxlength="100" value="<?= old('F_name') ?>" required></div>
                            <div class="col-md-3"><label class="form-label" for="m_name">Middle</label><input class="form-control" id="m_name" name="m_name" maxlength="100" value="<?= old('m_name') ?>"></div>
                            <div class="col-md-4"><label class="form-label" for="L_name">Last name</label><input class="form-control" id="L_name" name="L_name" maxlength="100" value="<?= old('L_name') ?>" required></div>
                            <div class="col-12"><label class="form-label" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" type="tel" maxlength="30" value="<?= old('phone') ?>" placeholder="e.g. 01700000000" required></div>
                            <div class="col-md-6"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="8" required><div class="form-text">At least 8 characters.</div></div>
                            <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required></div>
                        </div>
                        <button class="btn btn-primary w-100 mt-4" type="submit">Create my account</button>
                    </form>
                    <p class="small text-secondary text-center mt-3 mb-0">Already a member? <a href="<?= e(url('login.php')) ?>">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

