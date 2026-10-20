<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('BENEFICIARY');
$rows = db_all("SELECT r.*, o.organization_name FROM assistance_requests r JOIN organizations o ON o.org_id=r.org_id WHERE r.beneficiary_id=? ORDER BY r.created_at DESC", [current_user()['id']]);
$pageTitle = 'My Requests';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>My assistance requests <a class="btn btn-sm" href="<?= url('beneficiary/request.php') ?>">＋ New request</a></h2><div class="table-wrap"><table><thead><tr><th>Submitted</th><th>Type</th><th>Organization</th><th>Details</th><th>Status</th><th>Response</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= fdate($r['created_at']) ?></td><td><?= e($r['assistance_type']) ?></td><td><?= e($r['organization_name']) ?></td><td style="max-width:260px"><?= e($r['description']) ?></td><td><?= status_badge($r['status']) ?></td><td><?= e($r['org_note'] ?: '—') ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No requests yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
