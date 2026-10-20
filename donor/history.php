<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('DONOR');
$rows = db_all("SELECT d.*, o.organization_name, p.title FROM donations d JOIN organizations o ON o.org_id=d.org_id LEFT JOIN projects p ON p.project_id=d.project_id WHERE d.donor_id=? ORDER BY d.created_at DESC", [current_user()['id']]);
$pageTitle = 'Donation History';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>My donations <span class="chip">Total <?= money(array_sum(array_column($rows,'amount'))) ?></span></h2><div class="table-wrap"><table><thead><tr><th>Reference</th><th>Date</th><th>Cause</th><th>Method</th><th>Amount</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $d): ?><tr><td><code><?= e($d['reference']) ?></code></td><td><?= fdate($d['created_at']) ?></td><td><?= e($d['title'] ?: 'General fund') ?><br><small><?= e($d['organization_name']) ?></small></td><td><?= e($d['payment_method']) ?></td><td><b><?= money($d['amount']) ?></b></td><td><a class="btn btn-sm btn-light" href="<?= url('donor/receipt.php?id=' . $d['donation_id']) ?>">Receipt</a></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No donations yet. <a href="<?= url('donor/donate.php') ?>">Make your first one</a>.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
