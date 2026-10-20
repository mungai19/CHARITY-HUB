<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('DONOR');
$cat = $_GET['cat'] ?? '';
$sql = "SELECT p.*, o.organization_name, (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.project_id=p.project_id AND d.status='COMPLETED') raised FROM projects p JOIN organizations o ON o.org_id=p.org_id WHERE o.verification_status='VERIFIED' AND p.status IN ('ONGOING','PLANNED')";
$feat = $cat ? db_all($sql . " AND p.category=? ORDER BY p.created_at DESC", [$cat]) : db_all($sql . " ORDER BY p.created_at DESC");
$cats = db_all("SELECT DISTINCT category FROM projects");
$pageTitle = 'Browse Projects';
include __DIR__ . '/../includes/header.php';
?>
<div class="toolbar"><div class="actions"><a class="btn btn-sm <?= $cat?'btn-light':'' ?>" href="?">All</a><?php foreach ($cats as $c): ?><a class="btn btn-sm <?= $cat===$c['category']?'':'btn-light' ?>" href="?cat=<?= urlencode($c['category']) ?>"><?= e($c['category']) ?></a><?php endforeach; ?></div></div>
<div class="grid grid-3"><?php foreach ($feat as $p) include __DIR__ . '/_pcard.php'; ?></div>
<?php if (!$feat): ?><p class="empty">No projects found.</p><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php';
