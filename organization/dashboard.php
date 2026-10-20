<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
$in  = db_val("SELECT COALESCE(SUM(amount),0) FROM donations WHERE org_id=? AND status='COMPLETED'", [$orgId]);
$out = db_val("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE org_id=?", [$orgId]);
$np  = db_val("SELECT COUNT(*) FROM projects WHERE org_id=?", [$orgId]);
$ne  = db_val("SELECT COUNT(*) FROM events WHERE org_id=? AND event_date>=CURDATE()", [$orgId]);
$pendV = db_val("SELECT COUNT(*) FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id WHERE e.org_id=? AND s.status='PENDING'", [$orgId]);
$pendB = db_val("SELECT COUNT(*) FROM assistance_requests WHERE org_id=? AND status='SUBMITTED'", [$orgId]);
$recent = db_all("SELECT d.*, u.name donor FROM donations d JOIN users u ON u.user_id=d.donor_id WHERE d.org_id=? ORDER BY d.created_at DESC LIMIT 5", [$orgId]);
$pageTitle = 'Organization Dashboard';
include __DIR__ . '/../includes/header.php';
org_notice();
?>
<div class="hero-banner"><h2><?= e($org['organization_name']) ?></h2><p>Manage your projects, events, volunteers and beneficiaries from here. Status: <?= status_badge($org['verification_status']) ?></p></div>
<div class="grid grid-4" style="margin-bottom:22px">
  <div class="stat dark"><div class="label">Donations received</div><div class="value"><?= money($in) ?></div></div>
  <div class="stat"><div class="label">Expenses</div><div class="value"><?= money($out) ?></div><div class="sub">Balance <?= money($in - $out) ?></div></div>
  <div class="stat"><div class="label">Projects</div><div class="value"><?= (int)$np ?></div></div>
  <div class="stat"><div class="label">Upcoming events</div><div class="value"><?= (int)$ne ?></div></div>
</div>
<?php if ($pendV || $pendB): ?><div class="alert alert-info">Needs attention:
 <?php if ($pendV): ?><a href="<?= url('organization/volunteers.php') ?>"><b><?= (int)$pendV ?> volunteer request(s)</b></a> <?php endif; ?>
 <?php if ($pendB): ?><a href="<?= url('organization/beneficiaries.php') ?>"><b><?= (int)$pendB ?> assistance request(s)</b></a><?php endif; ?></div><?php endif; ?>
<div class="grid grid-2">
 <div class="card"><h2>Recent donations</h2><div class="table-wrap"><table><thead><tr><th>Donor</th><th>Amount</th><th>Date</th></tr></thead><tbody>
 <?php foreach ($recent as $d): ?><tr><td><?= $d['anonymous'] ? 'Anonymous' : e($d['donor']) ?></td><td><b><?= money($d['amount']) ?></b></td><td><?= fdate($d['created_at']) ?></td></tr>
 <?php endforeach; if (!$recent): ?><tr><td colspan="3" class="empty">No donations yet.</td></tr><?php endif; ?></tbody></table></div></div>
 <div class="card"><h2>Quick actions</h2><div class="actions" style="flex-direction:column;align-items:stretch">
  <a class="btn" href="<?= url('organization/projects.php') ?>">＋ Create a project</a><a class="btn btn-light" href="<?= url('organization/events.php') ?>">＋ Schedule an event</a>
  <a class="btn btn-light" href="<?= url('organization/expenses.php') ?>">＋ Record an expense</a><a class="btn btn-light" href="<?= url('organization/reports.php') ?>">📊 View reports</a></div></div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
