<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
$rows = db_all("SELECT d.*, u.name donor, p.title FROM donations d JOIN users u ON u.user_id=d.donor_id LEFT JOIN projects p ON p.project_id=d.project_id WHERE d.org_id=? ORDER BY d.created_at DESC", [$orgId]);
$pageTitle = 'Donations Received';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Donations <span class="chip">Total <?= money(array_sum(array_column($rows,'amount'))) ?></span></h2>
<div class="toolbar"><input class="input" placeholder="Search…" data-search="dtbl"><a class="btn btn-sm btn-light" href="<?= url('organization/reports.php?export=donations') ?>">⬇ Export CSV</a></div>
<div class="table-wrap"><table id="dtbl"><thead><tr><th>Reference</th><th>Donor</th><th>Project</th><th>Method</th><th>Amount</th><th>Message</th><th>Date</th></tr></thead><tbody>
<?php foreach ($rows as $d): ?><tr><td><code><?= e($d['reference']) ?></code></td><td><?= $d['anonymous'] ? 'Anonymous' : e($d['donor']) ?></td><td><?= e($d['title'] ?: 'General fund') ?></td><td><?= e($d['payment_method']) ?></td><td><b><?= money($d['amount']) ?></b></td><td><?= e($d['message']) ?></td><td><?= fdate($d['created_at']) ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="7" class="empty">No donations yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
