<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('VOLUNTEER');
$uid = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $eid = (int)post('event_id');
  $ev = db_one("SELECT e.* FROM events e JOIN organizations o ON o.org_id=e.org_id WHERE e.event_id=? AND e.status='OPEN' AND e.event_date>=CURDATE() AND o.verification_status='VERIFIED'", [$eid]);
  if (!$ev) flash('error', 'That event is not available.');
  elseif (db_val("SELECT COUNT(*) FROM volunteer_signups WHERE event_id=? AND volunteer_id=?", [$eid, $uid])) flash('info', 'You have already signed up for this event.');
  else { db_exec("INSERT INTO volunteer_signups (event_id, volunteer_id) VALUES (?,?)", [$eid, $uid]); flash('success', 'You signed up! The organization will confirm your place.'); }
  redirect('volunteer/events.php');
}
$rows = db_all("SELECT e.*, o.organization_name,
  (SELECT COUNT(*) FROM volunteer_signups s WHERE s.event_id=e.event_id AND s.status='APPROVED') ap,
  (SELECT status FROM volunteer_signups s WHERE s.event_id=e.event_id AND s.volunteer_id=?) mystatus
  FROM events e JOIN organizations o ON o.org_id=e.org_id WHERE e.status='OPEN' AND e.event_date>=CURDATE() AND o.verification_status='VERIFIED' ORDER BY e.event_date", [$uid]);
$pageTitle = 'Open Events';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid grid-2">
<?php foreach ($rows as $v): ?>
 <div class="card"><h2><?= e($v['title']) ?> <?= $v['mystatus'] ? status_badge($v['mystatus']) : '' ?></h2>
  <p><?= e($v['description']) ?></p><br>
  <dl class="kv"><dt>Organization</dt><dd><?= e($v['organization_name']) ?></dd><dt>Date</dt><dd><?= fdate($v['event_date'], 'D, d M Y') ?><?= $v['start_time'] ? ' at ' . date('H:i', strtotime($v['start_time'])) : '' ?></dd><dt>Location</dt><dd>📍 <?= e($v['location']) ?></dd><dt>Spots</dt><dd><?= (int)$v['ap'] ?> / <?= (int)$v['volunteers_needed'] ?> filled</dd></dl><br>
  <?php if ($v['mystatus']): ?><button class="btn btn-light" disabled>Already signed up</button>
  <?php else: ?><form method="post"><?= csrf_field() ?><input type="hidden" name="event_id" value="<?= $v['event_id'] ?>"><button class="btn">🙋 Sign up to volunteer</button></form><?php endif; ?></div>
<?php endforeach; ?></div>
<?php if (!$rows): ?><div class="card"><p class="empty">No open events right now. Please check back soon.</p></div><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php';
