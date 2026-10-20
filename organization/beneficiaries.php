<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $id = (int)post('id'); $st = post('status'); $note = post('org_note');
  if (in_array($st, ['APPROVED','REJECTED','FULFILLED'], true) && db_val("SELECT COUNT(*) FROM assistance_requests WHERE request_id=? AND org_id=?", [$id, $orgId])) {
    db_exec("UPDATE assistance_requests SET status=?, org_note=? WHERE request_id=?", [$st, $note ?: null, $id]); flash('success', 'Request updated.');
  } else flash('error', 'Could not update that request.');
  redirect('organization/beneficiaries.php');
}
$rows = db_all("SELECT r.*, u.name, u.phone, u.email FROM assistance_requests r JOIN users u ON u.user_id=r.beneficiary_id WHERE r.org_id=? ORDER BY FIELD(r.status,'SUBMITTED','APPROVED','FULFILLED','REJECTED'), r.created_at DESC", [$orgId]);
$pageTitle = 'Beneficiaries';
include __DIR__ . '/../includes/header.php'; org_notice();
?>
<div class="card"><h2>Assistance requests &amp; beneficiary tracking</h2><div class="toolbar"><input class="input" placeholder="Search…" data-search="btbl"></div>
<div class="table-wrap"><table id="btbl"><thead><tr><th>Beneficiary</th><th>Type</th><th>Request</th><th>Status</th><th>Respond</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['name']) ?></b><br><small><?= e($r['phone']) ?></small></td><td><?= e($r['assistance_type']) ?></td><td style="max-width:280px"><?= e($r['description']) ?><br><small><?= fdate($r['created_at']) ?></small></td><td><?= status_badge($r['status']) ?><?php if ($r['org_note']): ?><br><small><?= e($r['org_note']) ?></small><?php endif; ?></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['request_id'] ?>"><input class="input" name="org_note" placeholder="Note to beneficiary" value="<?= e($r['org_note']) ?>" style="margin-bottom:6px;min-width:180px">
<div class="actions"><button name="status" value="APPROVED" class="btn btn-sm">Approve</button><button name="status" value="FULFILLED" class="btn btn-sm btn-outline">Fulfilled</button><button name="status" value="REJECTED" class="btn btn-sm btn-light">Reject</button></div></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5" class="empty">No requests yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
