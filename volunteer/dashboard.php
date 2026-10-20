<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('VOLUNTEER');
$id = current_user()['id'];
$hrs = db_val("SELECT COALESCE(SUM(hours),0) FROM volunteer_signups WHERE volunteer_id=? AND status='APPROVED'", [$id]);
$ap = db_val("SELECT COUNT(*) FROM volunteer_signups WHERE volunteer_id=? AND status='APPROVED'", [$id]);
$pe = db_val("SELECT COUNT(*) FROM volunteer_signups WHERE volunteer_id=? AND status='PENDING'", [$id]);
$mine = db_all("SELECT s.*, e.title, e.event_date, e.location, o.organization_name FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id JOIN organizations o ON o.org_id=e.org_id WHERE s.volunteer_id=? AND e.event_date>=CURDATE() ORDER BY e.event_date LIMIT 5", [$id]);
$pageTitle = 'Volunteer Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="hero-banner"><h2>Hello, <?= e(explode(' ', current_user()['name'])[0]) ?> 🙋</h2><p>Your time is a gift. Find an event and join a team making a difference.</p><p style="margin-top:14px"><a class="btn btn-light" href="<?= url('volunteer/events.php') ?>">Browse open events</a></p></div>
<div class="grid grid-3" style="margin-bottom:22px">
 <div class="stat dark"><div class="label">Hours volunteered</div><div class="value"><?= (float)$hrs ?></div></div>
 <div class="stat"><div class="label">Approved events</div><div class="value"><?= (int)$ap ?></div></div>
 <div class="stat"><div class="label">Awaiting approval</div><div class="value"><?= (int)$pe ?></div></div></div>
<div class="card"><h2>My upcoming events</h2><div class="table-wrap"><table><thead><tr><th>Event</th><th>Organization</th><th>Date</th><th>Location</th><th>Status</th></tr></thead><tbody>
<?php foreach ($mine as $m): ?><tr><td><b><?= e($m['title']) ?></b></td><td><?= e($m['organization_name']) ?></td><td><?= fdate($m['event_date']) ?></td><td><?= e($m['location']) ?></td><td><?= status_badge($m['status']) ?></td></tr>
<?php endforeach; if (!$mine): ?><tr><td colspan="5" class="empty">You have no upcoming events. <a href="<?= url('volunteer/events.php') ?>">Find one</a>.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
