<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('VOLUNTEER');
$uid = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  db_exec("DELETE FROM volunteer_signups WHERE signup_id=? AND volunteer_id=? AND status<>'APPROVED'", [(int)post('id'), $uid]); flash('success', 'Sign-up withdrawn.'); redirect('volunteer/my_events.php'); }
$rows = db_all("SELECT s.*, e.title, e.event_date, e.location, o.organization_name FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id JOIN organizations o ON o.org_id=e.org_id WHERE s.volunteer_id=? ORDER BY e.event_date DESC", [$uid]);
$pageTitle = 'My Sign-ups';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>My volunteering <span class="chip"><?= (float)array_sum(array_column($rows, 'hours')) ?> hours</span></h2><div class="table-wrap"><table><thead><tr><th>Event</th><th>Organization</th><th>Date</th><th>Status</th><th>Hours</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['title']) ?></b><br><small><?= e($r['location']) ?></small></td><td><?= e($r['organization_name']) ?></td><td><?= fdate($r['event_date']) ?></td><td><?= status_badge($r['status']) ?></td><td><?= (float)$r['hours'] ?></td>
<td><?php if ($r['status'] !== 'APPROVED'): ?><form method="post" data-confirm="Withdraw this sign-up?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['signup_id'] ?>"><button class="btn btn-sm btn-light">Withdraw</button></form><?php endif; ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">You haven't signed up for any events yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
