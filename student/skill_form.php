<section class="py-4 py-lg-5"><div class="container" style="max-width: 760px">
    <div class="mb-4"><div class="eyebrow text-primary mb-1">Skill listing</div><h1 class="section-title h2 mb-0"><?= e($formHeading) ?></h1></div>
    <form class="surface-card p-4 p-lg-5" method="post" action="<?= e(url($formAction)) ?>">
        <?= csrf_field() ?>
        <?php if (!empty($skill['skill_id'])): ?><input type="hidden" name="skill_id" value="<?= (int) $skill['skill_id'] ?>"><?php endif; ?>
        <div class="mb-3"><label class="form-label" for="title">Skill title</label><input class="form-control" id="title" name="title" maxlength="150" value="<?= e($skill['title']) ?>" placeholder="e.g. Python fundamentals" required></div>
        <div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="5" maxlength="2500" placeholder="What will someone learn with you?"><?= e($skill['description']) ?></textarea></div>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><label class="form-label" for="category_name">Category</label><input class="form-control" id="category_name" name="category_name" maxlength="100" value="<?= e($skill['category_name']) ?>" placeholder="Programming"></div>
            <div class="col-md-4"><label class="form-label" for="level">Level</label><select class="form-select" id="level" name="level"><option<?= selected('Beginner friendly', $skill['level']) ?>>Beginner friendly</option><option<?= selected('Intermediate', $skill['level']) ?>>Intermediate</option><option<?= selected('Advanced', $skill['level']) ?>>Advanced</option><option<?= selected('All levels', $skill['level']) ?>>All levels</option></select></div>
            <div class="col-md-4"><label class="form-label" for="status">Visibility</label><select class="form-select" id="status" name="status"><option value="active"<?= selected('active', $skill['status']) ?>>Active</option><option value="inactive"<?= selected('inactive', $skill['status']) ?>>Inactive</option></select></div>
        </div>
        <div class="d-flex gap-2"><button class="btn btn-primary" type="submit"><?= e($submitLabel) ?></button><a class="btn btn-outline-secondary" href="<?= e(url('student/skills.php')) ?>">Cancel</a></div>
    </form>
</div></section>
