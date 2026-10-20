<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (post('action') === 'delete') { db_exec("DELETE FROM events WHERE event_id=?", [(int)post('id')]); flash('success', 'Event removed.'); }
    redirect('admin/events.php'); }
$rows = db_all("SELECT e.*, o.organization_name, (SELECT COUNT(*) FROM volunteer_signups s WHERE s.event_id=e.event_id AND s.status='APPROVED') AS approved FROM events e JOIN organizations o ON o.org_id=e.org_id ORDER BY e.event_date DESC");
$pageTitle = 'All Events';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Events (<?= count($rows) ?>)</h2><div class="table-wrap"><table><thead><tr><th>Event</th><th>Organization</th><th>Date</th><th>Location</th><th>Volunteers</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $v): ?><tr><td><b><?= e($v['title']) ?></b></td><td><?= e($v['organization_name']) ?></td><td><?= fdate($v['event_date']) ?></td><td><?= e($v['location']) ?></td><td><?= (int)$v['approved'] ?> / <?= (int)$v['volunteers_needed'] ?></td><td><?= status_badge($v['status']) ?></td>
<td><form method="post" data-confirm="Remove this event?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $v['event_id'] ?>"><button class="btn btn-sm btn-danger">Remove</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="7" class="empty">No events.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
