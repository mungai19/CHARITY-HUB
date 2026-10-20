<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('DONOR');
$id = current_user()['id'];
$tot = db_val("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donor_id=? AND status='COMPLETED'", [$id]);
$cnt = db_val("SELECT COUNT(*) FROM donations WHERE donor_id=?", [$id]);
$causes = db_val("SELECT COUNT(DISTINCT COALESCE(project_id, -org_id)) FROM donations WHERE donor_id=?", [$id]);
$recent = db_all("SELECT d.*, o.organization_name, p.title FROM donations d JOIN organizations o ON o.org_id=d.org_id LEFT JOIN projects p ON p.project_id=d.project_id WHERE d.donor_id=? ORDER BY d.created_at DESC LIMIT 5", [$id]);
$feat = db_all("SELECT p.*, o.organization_name, (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.project_id=p.project_id AND d.status='COMPLETED') raised FROM projects p JOIN organizations o ON o.org_id=p.org_id WHERE o.verification_status='VERIFIED' AND p.status IN ('ONGOING','PLANNED') ORDER BY RAND() LIMIT 3");
$pageTitle = 'Donor Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="hero-banner"><h2>Thank you, <?= e(explode(' ', current_user()['name'])[0]) ?> 💚</h2><p>Your generosity changes lives. Pick a cause and give in under a minute.</p>
  <p style="margin-top:14px"><a class="btn btn-light" href="<?= url('donor/donate.php') ?>">Make a donation</a></p></div>
<div class="grid grid-3" style="margin-bottom:22px">
  <div class="stat dark"><div class="label">Total given</div><div class="value"><?= money($tot) ?></div></div>
  <div class="stat"><div class="label">Donations made</div><div class="value"><?= (int)$cnt ?></div></div>
  <div class="stat"><div class="label">Causes supported</div><div class="value"><?= (int)$causes ?></div></div>
</div>
<div class="card"><h2>Recent donations <a class="btn btn-sm btn-light" href="<?= url('donor/history.php') ?>">View all</a></h2><div class="table-wrap"><table><thead><tr><th>Date</th><th>Cause</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($recent as $d): ?><tr><td><?= fdate($d['created_at']) ?></td><td><?= e($d['title'] ?: $d['organization_name'] . ' (general)') ?></td><td><b><?= money($d['amount']) ?></b></td><td><?= status_badge($d['status']) ?></td><td><a class="btn btn-sm btn-light" href="<?= url('donor/receipt.php?id=' . $d['donation_id']) ?>">Receipt</a></td></tr>
<?php endforeach; if (!$recent): ?><tr><td colspan="5" class="empty">You haven't donated yet. Start with a project below 👇</td></tr><?php endif; ?></tbody></table></div></div>
<h3 style="font:600 20px var(--serif);margin:6px 0 14px">Suggested projects</h3>
<div class="grid grid-3"><?php foreach ($feat as $p): include __DIR__ . '/_pcard.php'; endforeach; ?></div>
<?php include __DIR__ . '/../includes/footer.php';
