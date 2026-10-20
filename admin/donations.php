<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
$rows = db_all("SELECT d.*, u.name AS donor, o.organization_name, p.title FROM donations d JOIN users u ON u.user_id=d.donor_id JOIN organizations o ON o.org_id=d.org_id LEFT JOIN projects p ON p.project_id=d.project_id ORDER BY d.created_at DESC");
$total = array_sum(array_column($rows, 'amount'));
$pageTitle = 'All Donations';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Donations <span class="chip">Total <?= money($total) ?></span></h2>
<div class="toolbar"><input class="input" placeholder="Search donations…" data-search="dtbl"><a class="btn btn-sm btn-light" href="<?= url('admin/reports.php?export=donations') ?>">⬇ Export CSV</a></div>
<div class="table-wrap"><table id="dtbl"><thead><tr><th>Reference</th><th>Donor</th><th>Organization</th><th>Project</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($rows as $d): ?><tr><td><code><?= e($d['reference']) ?></code></td><td><?= e($d['donor']) ?><?= $d['anonymous'] ? ' <small>(anon)</small>' : '' ?></td><td><?= e($d['organization_name']) ?></td><td><?= e($d['title'] ?: 'General fund') ?></td><td><?= e($d['payment_method']) ?></td><td><b><?= money($d['amount']) ?></b></td><td><?= status_badge($d['status']) ?></td><td><?= fdate($d['created_at']) ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="8" class="empty">No donations yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
