<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
$s = [
 'raised' => db_val("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='COMPLETED'"),
 'donations' => db_val("SELECT COUNT(*) FROM donations"),
 'users' => db_val("SELECT COUNT(*) FROM users"),
 'orgs' => db_val("SELECT COUNT(*) FROM organizations WHERE verification_status='VERIFIED'"),
 'pending' => db_val("SELECT COUNT(*) FROM organizations WHERE verification_status='PENDING'"),
 'projects' => db_val("SELECT COUNT(*) FROM projects"),
 'vols' => db_val("SELECT COUNT(*) FROM users WHERE role='VOLUNTEER'"),
 'expenses' => db_val("SELECT COALESCE(SUM(amount),0) FROM expenses"),
];
$recent = db_all("SELECT d.*, u.name AS donor, o.organization_name FROM donations d JOIN users u ON u.user_id=d.donor_id JOIN organizations o ON o.org_id=d.org_id ORDER BY d.created_at DESC LIMIT 6");
$byRole = db_all("SELECT role, COUNT(*) c FROM users GROUP BY role");
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="hero-banner"><h2>Hello, <?= e(current_user()['name']) ?> 👋</h2><p>Here is what is happening across Charity Hub today.</p></div>
<?php if ($s['pending'] > 0): ?><div class="alert alert-warn"><?= (int)$s['pending'] ?> organization(s) are waiting for verification. <a href="<?= url('admin/organizations.php') ?>"><b>Review now →</b></a></div><?php endif; ?>
<div class="grid grid-4" style="margin-bottom:22px">
  <div class="stat dark"><div class="label">Total donations</div><div class="value"><?= money($s['raised']) ?></div><div class="sub"><?= (int)$s['donations'] ?> gifts received</div></div>
  <div class="stat"><div class="label">Registered users</div><div class="value"><?= (int)$s['users'] ?></div><div class="sub"><?= (int)$s['vols'] ?> volunteers</div></div>
  <div class="stat"><div class="label">Verified organizations</div><div class="value"><?= (int)$s['orgs'] ?></div><div class="sub"><?= (int)$s['pending'] ?> pending</div></div>
  <div class="stat"><div class="label">Projects / Expenses</div><div class="value"><?= (int)$s['projects'] ?></div><div class="sub">Spent <?= money($s['expenses']) ?></div></div>
</div>
<div class="grid grid-2">
  <div class="card"><h2>Recent donations <a class="btn btn-sm btn-light" href="<?= url('admin/donations.php') ?>">View all</a></h2>
    <div class="table-wrap"><table><thead><tr><th>Donor</th><th>Organization</th><th>Amount</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($recent as $d): ?><tr><td><?= $d['anonymous'] ? 'Anonymous' : e($d['donor']) ?></td><td><?= e($d['organization_name']) ?></td><td><b><?= money($d['amount']) ?></b></td><td><?= fdate($d['created_at']) ?></td></tr>
    <?php endforeach; if (!$recent): ?><tr><td colspan="4" class="empty">No donations yet.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="card"><h2>Users by role</h2>
    <?php $max = max(array_column($byRole, 'c') ?: [1]); foreach ($byRole as $r): ?>
      <div class="bar-row"><span><?= e(ucfirst(strtolower($r['role']))) ?></span><div class="progress"><span style="width:<?= round($r['c']/$max*100) ?>%"></span></div><b><?= (int)$r['c'] ?></b></div>
    <?php endforeach; ?>
    <div class="actions" style="margin-top:18px"><a class="btn btn-sm" href="<?= url('admin/users.php') ?>">Manage users</a><a class="btn btn-sm btn-light" href="<?= url('admin/reports.php') ?>">Open reports</a><a class="btn btn-sm btn-light" href="<?= url('admin/announcements.php') ?>">Post announcement</a></div></div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
