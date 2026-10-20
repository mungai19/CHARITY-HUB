<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
$sts = ['OPEN','CLOSED','COMPLETED','CANCELLED'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $act = post('action'); $id = (int)post('id');
  if ($act === 'delete') { db_exec("DELETE FROM events WHERE event_id=? AND org_id=?", [$id, $orgId]); flash('success', 'Event deleted.'); }
  else {
    $t = post('title'); $d = post('description'); $l = post('location'); $dt = post('event_date'); $tm = post('start_time') ?: null; $n = (int)post('volunteers_needed'); $st = post('status');
    if (!$t || !$d || !$l || !$dt || $n < 1 || !in_array($st, $sts, true)) flash('error', 'Please complete all event fields.');
    elseif ($act === 'update') { db_exec("UPDATE events SET title=?,description=?,location=?,event_date=?,start_time=?,volunteers_needed=?,status=? WHERE event_id=? AND org_id=?", [$t,$d,$l,$dt,$tm,$n,$st,$id,$orgId]); flash('success', 'Event updated.'); }
    else { db_exec("INSERT INTO events (org_id,title,description,location,event_date,start_time,volunteers_needed,status) VALUES (?,?,?,?,?,?,?,?)", [$orgId,$t,$d,$l,$dt,$tm,$n,$st]); flash('success', 'Event scheduled.'); }
  }
  redirect('organization/events.php');
}
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM events WHERE event_id=? AND org_id=?", [(int)$_GET['edit'], $orgId]) : null;
$rows = db_all("SELECT e.*, (SELECT COUNT(*) FROM volunteer_signups s WHERE s.event_id=e.event_id AND s.status='APPROVED') ap FROM events e WHERE e.org_id=? ORDER BY e.event_date DESC", [$orgId]);
$pageTitle = 'Events';
include __DIR__ . '/../includes/header.php'; org_notice();
?>
<div class="card"><h2><?= $edit ? 'Edit event' : 'Schedule an event' ?></h2>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= (int)($edit['event_id'] ?? 0) ?>">
 <div class="form-grid">
  <div class="field"><label>Event title</label><input name="title" value="<?= e($edit['title'] ?? '') ?>" required></div>
  <div class="field"><label>Location</label><input name="location" value="<?= e($edit['location'] ?? '') ?>" required></div>
  <div class="field full"><label>Description</label><textarea name="description" required><?= e($edit['description'] ?? '') ?></textarea></div>
  <div class="field"><label>Date</label><input type="date" name="event_date" value="<?= e($edit['event_date'] ?? '') ?>" required></div>
  <div class="field"><label>Start time</label><input type="time" name="start_time" value="<?= e(substr($edit['start_time'] ?? '', 0, 5)) ?>"></div>
  <div class="field"><label>Volunteers needed</label><input type="number" min="1" name="volunteers_needed" value="<?= e($edit['volunteers_needed'] ?? 10) ?>" required></div>
  <div class="field"><label>Status</label><select name="status"><?php foreach ($sts as $s): ?><option <?= ($edit['status'] ?? 'OPEN')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
 </div><div class="actions"><button class="btn"><?= $edit ? 'Save changes' : 'Schedule event' ?></button><?php if ($edit): ?><a class="btn btn-light" href="<?= url('organization/events.php') ?>">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>Your events (<?= count($rows) ?>)</h2><div class="table-wrap"><table><thead><tr><th>Event</th><th>Date</th><th>Location</th><th>Volunteers</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $v): ?><tr><td><b><?= e($v['title']) ?></b></td><td><?= fdate($v['event_date']) ?></td><td><?= e($v['location']) ?></td><td><?= (int)$v['ap'] ?> / <?= (int)$v['volunteers_needed'] ?></td><td><?= status_badge($v['status']) ?></td>
<td><div class="actions"><a class="btn btn-sm btn-light" href="?edit=<?= $v['event_id'] ?>">Edit</a><form method="post" data-confirm="Delete this event?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $v['event_id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No events yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
