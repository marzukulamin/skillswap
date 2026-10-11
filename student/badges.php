<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';require_login();require_once __DIR__ . '/../includes/db.php';$userId=current_user_id();
$stmt=$conn->prepare('SELECT b.*,ub.earned_date FROM `USER_BADGE` ub JOIN `BADGE` b ON b.badge_id=ub.badge_id WHERE ub.user_id=? ORDER BY ub.earned_date DESC');$stmt->bind_param('i',$userId);$stmt->execute();$badges=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle='Badges';require __DIR__.'/../includes/header.php';
?>
<section class="py-4 py-lg-5"><div class="container"><div class="mb-4"><div class="eyebrow text-primary mb-1">Milestones</div><h1 class="section-title h2">My badges</h1><p class="text-secondary">Recognition for the ways you learn, teach, and contribute.</p></div><div class="row g-4"><?php if(!$badges):?><div class="col-12"><div class="surface-card empty-state"><i class="bi bi-award d-block mb-3"></i><h2 class="h5">Your first badge is waiting</h2><p class="mb-0">Keep sharing skills and completing sessions.</p></div></div><?php endif;?><?php foreach($badges as $badge):?><div class="col-md-6 col-lg-4"><article class="surface-card h-100 p-4 text-center"><span class="feature-icon mb-3"><i class="bi <?=e($badge['badge_icon']?:'bi-award')?>"></i></span><h2 class="h5"><?=e($badge['badge_name'])?></h2><p class="text-secondary small"><?=e($badge['description'])?></p><div class="small text-secondary">Earned <?=e(date('M j, Y',strtotime($badge['earned_date'])))?></div></article></div><?php endforeach;?></div></div></section>
<?php require __DIR__.'/../includes/footer.php';?>
