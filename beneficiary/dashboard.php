<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('BENEFICIARY');
$id = current_user()['id'];
$c = ['all' => db_val("SELECT COUNT(*) FROM assistance_requests WHERE beneficiary_id=?", [$id]),
      'pending' => db_val("SELECT COUNT(*) FROM assistance_requests WHERE beneficiary_id=? AND status='SUBMITTED'", [$id]),
      'approved' => db_val("SELECT COUNT(*) FROM assistance_requests WHERE beneficiary_id=? AND status IN ('APPROVED','FULFILLED')", [$id])];
$rows = db_all("SELECT r.*, o.organization_name FROM assistance_requests r JOIN organizations o ON o.org_id=r.org_id WHERE r.beneficiary_id=? ORDER BY r.created_at DESC LIMIT 5", [$id]);
$news = db_all("SELECT * FROM announcements WHERE audience IN ('ALL','BENEFICIARY') ORDER BY created_at DESC LIMIT 3");
$pageTitle = 'Beneficiary Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="hero-banner"><h2>Welcome, <?= e(explode(' ', current_user()['name'])[0]) ?> 🤝</h2><p>You can request support from verified organizations and follow every step of your request here.</p><p style="margin-top:14px"><a class="btn btn-light" href="<?= url('beneficiary/request.php') ?>">Request assistance</a></p></div>
<div class="grid grid-3" style="margin-bottom:22px">
 <div class="stat dark"><div class="label">My requests</div><div class="value"><?= (int)$c['all'] ?></div></div>
 <div class="stat"><div class="label">Awaiting response</div><div class="value"><?= (int)$c['pending'] ?></div></div>
 <div class="stat"><div class="label">Approved / fulfilled</div><div class="value"><?= (int)$c['approved'] ?></div></div></div>
<div class="grid grid-2">
 <div class="card"><h2>Recent requests <a class="btn btn-sm btn-light" href="<?= url('beneficiary/requests.php') ?>">All</a></h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Organization</th><th>Status</th></tr></thead><tbody>
 <?php foreach ($rows as $r): ?><tr><td><?= e($r['assistance_type']) ?></td><td><?= e($r['organization_name']) ?></td><td><?= status_badge($r['status']) ?></td></tr>
 <?php endforeach; if (!$rows): ?><tr><td colspan="3" class="empty">No requests yet.</td></tr><?php endif; ?></tbody></table></div></div>
 <div class="card"><h2>Announcements</h2><?php foreach ($news as $a): ?><div class="ann"><h4><?= e($a['title']) ?></h4><p><?= e(mb_strimwidth($a['body'], 0, 120, '…')) ?></p></div><?php endforeach; if (!$news): ?><p class="empty">Nothing new.</p><?php endif; ?></div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
