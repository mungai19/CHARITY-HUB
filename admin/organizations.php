<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)post('id'); $st = post('status');
    if (in_array($st, ['VERIFIED','REJECTED','PENDING'], true)) {
        db_exec("UPDATE organizations SET verification_status=? WHERE org_id=?", [$st, $id]);
        flash('success', 'Organization marked ' . strtolower($st) . '.');
    }
    redirect('admin/organizations.php');
}
$orgs = db_all("SELECT o.*, u.name AS contact, u.email, u.phone,
   (SELECT COUNT(*) FROM projects p WHERE p.org_id=o.org_id) AS projects,
   (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.org_id=o.org_id) AS raised
   FROM organizations o JOIN users u ON u.user_id=o.user_id ORDER BY FIELD(o.verification_status,'PENDING','VERIFIED','REJECTED'), o.created_at DESC");
$pageTitle = 'Organizations';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Organization verification</h2>
<div class="table-wrap"><table><thead><tr><th>Organization</th><th>Contact</th><th>Location</th><th>Projects</th><th>Raised</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($orgs as $o): ?><tr><td><b><?= e($o['organization_name']) ?></b><br><small><?= e(mb_strimwidth($o['description'] ?? '', 0, 60, '…')) ?></small></td>
<td><?= e($o['contact']) ?><br><small><?= e($o['email']) ?> · <?= e($o['phone']) ?></small></td><td><?= e($o['location'] ?: '—') ?></td><td><?= (int)$o['projects'] ?></td><td><?= money($o['raised']) ?></td><td><?= status_badge($o['verification_status']) ?></td>
<td><div class="actions"><?php foreach (['VERIFIED'=>'Verify','REJECTED'=>'Reject','PENDING'=>'Reset'] as $k=>$l): if ($o['verification_status']!==$k): ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $o['org_id'] ?>"><input type="hidden" name="status" value="<?= $k ?>"><button class="btn btn-sm <?= $k==='VERIFIED'?'':'btn-light' ?>"><?= $l ?></button></form><?php endif; endforeach; ?></div></td></tr>
<?php endforeach; if (!$orgs): ?><tr><td colspan="7" class="empty">No organizations yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
