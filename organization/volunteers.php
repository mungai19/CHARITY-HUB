<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $id = (int)post('id'); $act = post('action');
  $own = db_one("SELECT s.signup_id FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id WHERE s.signup_id=? AND e.org_id=?", [$id, $orgId]);
  if (!$own) flash('error', 'Request not found.');
  elseif ($act === 'approve') { db_exec("UPDATE volunteer_signups SET status='APPROVED' WHERE signup_id=?", [$id]); flash('success', 'Volunteer approved.'); }
  elseif ($act === 'decline') { db_exec("UPDATE volunteer_signups SET status='DECLINED' WHERE signup_id=?", [$id]); flash('success', 'Request declined.'); }
  elseif ($act === 'hours') { $h = max(0, min(999, (float)post('hours'))); db_exec("UPDATE volunteer_signups SET hours=? WHERE signup_id=?", [$h, $id]); flash('success', 'Hours recorded.'); }
  redirect('organization/volunteers.php');
}
$rows = db_all("SELECT s.*, u.name, u.email, u.phone, e.title, e.event_date FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id JOIN users u ON u.user_id=s.volunteer_id WHERE e.org_id=? ORDER BY FIELD(s.status,'PENDING','APPROVED','DECLINED'), e.event_date DESC", [$orgId]);
$pageTitle = 'Volunteers';
include __DIR__ . '/../includes/header.php'; org_notice();
?>
<div class="card"><h2>Volunteer sign-ups</h2><div class="toolbar"><input class="input" placeholder="Search…" data-search="vtbl"></div>
<div class="table-wrap"><table id="vtbl"><thead><tr><th>Volunteer</th><th>Contact</th><th>Event</th><th>Status</th><th>Hours</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['name']) ?></b></td><td><small><?= e($r['email']) ?><br><?= e($r['phone']) ?></small></td><td><?= e($r['title']) ?><br><small><?= fdate($r['event_date']) ?></small></td><td><?= status_badge($r['status']) ?></td>
<td><?php if ($r['status']==='APPROVED'): ?><form method="post" class="actions"><?= csrf_field() ?><input type="hidden" name="action" value="hours"><input type="hidden" name="id" value="<?= $r['signup_id'] ?>"><input class="input" style="width:80px" type="number" step="0.5" min="0" name="hours" value="<?= e($r['hours']) ?>"><button class="btn btn-sm btn-light">Save</button></form><?php else: ?>—<?php endif; ?></td>
<td><?php if ($r['status']==='PENDING'): ?><div class="actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['signup_id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-sm">Approve</button></form>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['signup_id'] ?>"><input type="hidden" name="action" value="decline"><button class="btn btn-sm btn-light">Decline</button></form></div><?php endif; ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No volunteer sign-ups yet. Create events so volunteers can join.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
