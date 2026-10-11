<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
header('Cache-Control: no-store');
$roles = ['learner' => 'Learner', 'mentor' => 'Mentor', 'admin' => 'Administrator'];
$role = is_string($_GET['role'] ?? null) && isset($roles[$_GET['role']]) ? $_GET['role'] : 'learner';
$initial = ['status' => null, 'slot' => '', 'rating' => null, 'comment' => '', 'badge' => false];
if (!isset($_SESSION['demo'])) {
    $_SESSION['demo'] = $initial;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $demo = &$_SESSION['demo'];
    if ($action === 'reset') {
        $demo = $initial;
        flash('success', 'Your private demo has been reset. Choose any workflow to start again.');
    } elseif ($action === 'book' && $role === 'learner' && $demo['status'] === null) {
        $slot = is_string($_POST['slot'] ?? null) ? $_POST['slot'] : '';
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $slot, new DateTimeZone('Asia/Dhaka'));
        if (!$date || $date->format('Y-m-d\TH:i') !== $slot || $date <= new DateTimeImmutable('now', new DateTimeZone('Asia/Dhaka'))) {
            flash('danger', 'Choose a future session time in Bangladesh time.');
        } else {
            $demo['slot'] = $slot;
            $demo['status'] = 'pending';
            flash('success', 'Sample booking DEMO-001 created. It is now in the mentor inbox and admin dashboard.');
        }
    } elseif ($action === 'accept' && $role === 'mentor' && $demo['status'] === 'pending') {
        $demo['status'] = 'accepted';
        flash('success', 'Session accepted. The learner and administrator can now see the updated status.');
    } elseif ($action === 'complete' && $role === 'mentor' && $demo['status'] === 'accepted') {
        $demo['status'] = 'completed';
        flash('success', 'Session completed. Switch to the learner to leave a review.');
    } elseif ($action === 'review' && $role === 'learner' && $demo['status'] === 'completed' && $demo['rating'] === null) {
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
        $comment = is_string($_POST['comment'] ?? null) ? trim($_POST['comment']) : '';
        if (!$rating || $rating < 1 || $rating > 5 || strlen($comment) > 1000) {
            flash('danger', 'Choose a rating from 1 to 5 and keep your review under 1,000 bytes.');
        } else {
            $demo['rating'] = $rating;
            $demo['comment'] = $comment;
            flash('success', 'Review published in your demo. The administrator can now award a badge.');
        }
    } elseif ($action === 'badge' && $role === 'admin' && $demo['rating'] !== null && !$demo['badge']) {
        $demo['badge'] = true;
        flash('success', 'First Session badge awarded to Demo Learner. It is visible in the learner view.');
    } else {
        flash('warning', 'That step is not available yet. Follow the next action shown in the guide.');
    }
    redirect('demo.php?role=' . $role);
}
$demo = $_SESSION['demo'];
$step = $demo['status'] === null ? 0 : ($demo['status'] === 'pending' ? 1 : ($demo['status'] === 'accepted' ? 2 : ($demo['rating'] === null ? 3 : ($demo['badge'] ? 5 : 4))));
$steps = [
    ['learner', 'Place a sample booking', 'Choose a session time and request the sample skill.'],
    ['mentor', 'Observe the destination inbox', 'The same booking arrives here. Accept the learner’s request.'],
    ['mentor', 'Complete the session', 'Try the mentor’s completion action after accepting.'],
    ['learner', 'Review the result', 'See the completed session and publish a sample review.'],
    ['admin', 'Inspect and award a badge', 'Check the booking and review, then recognize the learner.'],
];
$pageTitle = 'Guided demo';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="eyebrow text-primary mb-2">Interactive product demo</div><h1>Try SkillSwap from every side</h1><p class="text-secondary mb-0">Demo accounts: learner, mentor, and administrator. No sign-up needed.</p></div>
        <div class="d-flex gap-2"><button class="btn btn-primary" type="button" data-demo-tour-replay>▶ Start guided tour</button><form method="post" action="<?= e(url('demo.php?role=' . $role)) ?>"><?= csrf_field() ?><button class="btn btn-outline-secondary" name="action" value="reset">Restart demo</button></form></div>
    </div>
    <div class="alert alert-info">Private sandbox · Changes are saved only in this browser session. Sample bookings, reviews, and badges never affect the live community.</div>
    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Demo workflows" data-tour="role-tabs">
        <?php foreach ($roles as $key => $label): ?>
            <a class="btn <?= $role === $key ? 'btn-primary' : 'btn-outline-primary' ?>" <?= $role === $key ? 'aria-current="page"' : '' ?> href="<?= e(url('demo.php?role=' . $key)) ?>">Test <?= e($label) ?> workflow</a>
        <?php endforeach; ?>
    </nav>
    <div class="row g-4">
        <aside class="col-lg-4"><div class="surface-card p-4" data-tour="guide">
            <h2 class="h5">Your guided tour</h2>
            <p class="small text-secondary">Your actions drive the tour. You can switch views at any time.</p>
            <progress class="w-100 mb-3" value="<?= $step ?>" max="5" aria-label="Tour progress"><?= $step ?>/5</progress>
            <ol class="ps-3">
                <?php foreach ($steps as $number => [$owner, $title, $help]): ?>
                    <li class="mb-3 <?= $number === $step ? 'fw-bold text-primary' : '' ?>" <?= $number === $step ? 'aria-current="step"' : '' ?>>
                        <a href="<?= e(url('demo.php?role=' . $owner)) ?>"><?= e($title) ?></a> <?= $number < $step ? '✓' : '' ?>
                        <div class="small text-secondary fw-normal"><?= e($help) ?></div>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php if ($step < 5): ?>
                <div class="alert alert-primary mb-0"><strong>Next: <?= e($steps[$step][1]) ?></strong><p class="small mb-2"><?= e($steps[$step][2]) ?></p><?php if ($role !== $steps[$step][0]): ?><a class="btn btn-primary btn-sm" href="<?= e(url('demo.php?role=' . $steps[$step][0])) ?>">Continue as <?= e($roles[$steps[$step][0]]) ?></a><?php endif; ?></div>
            <?php else: ?>
                <div class="alert alert-success mb-0"><strong>Tour complete!</strong><p class="small mb-2">You followed a booking from request to mentor, review, admin dashboard, and learner badge.</p><a href="<?= e(url('register.php')) ?>" class="btn btn-success btn-sm">Create your own account</a></div>
            <?php endif; ?>
        </div></aside>
        <section class="col-lg-8" aria-label="Demo dashboard">
            <div class="surface-card p-4 mb-4">
                <div class="eyebrow text-primary mb-2">Demo <?= e($roles[$role]) ?> account</div>
                <h2 class="h4"><?= $role === 'learner' ? 'Discover and book a skill' : ($role === 'mentor' ? 'Mentor session inbox' : 'Admin dashboard') ?></h2>
                <?php if ($role === 'learner' && $demo['status'] === null): ?>
                    <h3 class="h5 mt-4">Introduction to Web Development</h3><p class="text-secondary">Demo Mentor · Beginner · Technology</p><p>Learn how HTML, CSS, and JavaScript work together in a friendly sample session.</p>
                    <form method="post" action="<?= e(url('demo.php?role=learner')) ?>" data-tour="booking-form"><?= csrf_field() ?><label class="form-label" for="demo-slot">Session time (Asia/Dhaka)</label><input class="form-control mb-3" id="demo-slot" type="datetime-local" name="slot" required value="<?= e((new DateTimeImmutable('tomorrow 15:00', new DateTimeZone('Asia/Dhaka')))->format('Y-m-d\TH:i')) ?>"><button class="btn btn-primary" name="action" value="book">Place sample booking</button></form>
                <?php elseif ($demo['status'] === null): ?>
                    <p class="text-secondary my-4">No sample bookings yet. Start with the learner to see a request arrive here.</p><a class="btn btn-primary" href="<?= e(url('demo.php?role=learner')) ?>">Create a sample booking</a>
                <?php else: ?>
                    <?php if ($role === 'admin'): ?><div class="row g-3 my-2"><div class="col-sm-4"><strong>1</strong><div>Sample booking</div></div><div class="col-sm-4"><strong><?= $demo['rating'] === null ? 0 : 1 ?></strong><div>Published reviews</div></div><div class="col-sm-4"><strong><?= $demo['badge'] ? 1 : 0 ?></strong><div>Badges awarded</div></div></div><hr><?php endif; ?>
                    <h3 class="h5">Booking DEMO-001</h3>
                    <dl class="row mb-3"><dt class="col-sm-4">Skill</dt><dd class="col-sm-8">Introduction to Web Development</dd><dt class="col-sm-4">Learner → Mentor</dt><dd class="col-sm-8">Demo Learner → Demo Mentor</dd><dt class="col-sm-4">Session time</dt><dd class="col-sm-8"><?= e(format_datetime($demo['slot'])) ?> (Asia/Dhaka)</dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge text-bg-<?= e(status_class($demo['status'])) ?>"><?= e(ucfirst($demo['status'])) ?></span></dd><dt class="col-sm-4">Payment</dt><dd class="col-sm-8">Waived · Sample session</dd></dl>
                    <?php if (in_array($demo['status'], ['accepted', 'completed'], true)): ?><p class="small text-secondary">Meeting link: https://example.com/demo-session (sample only)</p><?php endif; ?>
                    <?php if ($role === 'mentor' && in_array($demo['status'], ['pending', 'accepted'], true)): ?>
                        <form method="post" action="<?= e(url('demo.php?role=mentor')) ?>" data-tour="mentor-action"><?= csrf_field() ?><button class="btn btn-primary" name="action" value="<?= $demo['status'] === 'pending' ? 'accept' : 'complete' ?>"><?= $demo['status'] === 'pending' ? 'Accept sample booking' : 'Mark session completed' ?></button></form>
                    <?php endif; ?>
                    <?php if ($demo['rating'] !== null): ?><div class="border rounded p-3 mt-3"><strong>Demo Learner’s review: <?= (int) $demo['rating'] ?>/5</strong><p class="mb-0 text-break"><?= e($demo['comment']) ?></p></div><?php endif; ?>
                    <?php if ($role === 'learner' && $demo['status'] === 'completed' && $demo['rating'] === null): ?>
                        <form class="mt-4" method="post" action="<?= e(url('demo.php?role=learner')) ?>" data-tour="review-form"><?= csrf_field() ?><h3 class="h5">How was your session?</h3><label class="form-label" for="demo-rating">Rating</label><select class="form-select mb-3" id="demo-rating" name="rating"><?php for ($n = 5; $n >= 1; $n--): ?><option value="<?= $n ?>"><?= $n ?>/5</option><?php endfor; ?></select><label class="form-label" for="demo-comment">Review</label><textarea class="form-control mb-3" id="demo-comment" name="comment" maxlength="250">A helpful introduction. I’m ready to build my first page!</textarea><button class="btn btn-primary" name="action" value="review">Publish sample review</button></form>
                    <?php endif; ?>
                    <?php if ($role === 'admin' && $demo['rating'] !== null && !$demo['badge']): ?><form class="mt-3" method="post" action="<?= e(url('demo.php?role=admin')) ?>" data-tour="badge-action"><?= csrf_field() ?><button class="btn btn-primary" name="action" value="badge">Award First Session badge</button></form><?php endif; ?>
                    <?php if ($demo['badge']): ?><div class="alert alert-success mt-3 mb-0"><i class="bi bi-award" aria-hidden="true"></i> First Session badge · Awarded to Demo Learner</div><?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="surface-card p-4"><h2 class="h5">See the result in another dashboard</h2><p class="text-secondary">Every view uses the same sample booking. Switch roles to follow its status, review, and badge.</p><div class="d-flex flex-wrap gap-2"><?php foreach ($roles as $key => $label): if ($key === $role) continue; ?><a class="btn btn-outline-primary" href="<?= e(url('demo.php?role=' . $key)) ?>">Open <?= e($label) ?> view</a><?php endforeach; ?></div></div>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
