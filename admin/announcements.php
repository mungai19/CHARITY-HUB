<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (post('action') === 'delete') { db_exec("DELETE FROM announcements WHERE announcement_id=?", [(int)post('id')]); flash('success', 'Announcement deleted.'); }
    else { $t = post('title'); $b = post('body'); $a = post('audience');
      if (!$t || !$b || !in_array($a, ['ALL','DONOR','VOLUNTEER','BENEFICIARY','ORGANIZATION'], true)) flash('error', 'Title and message are required.');
      else { db_exec("INSERT INTO announcements (author_id,title,body,audience) VALUES (?,?,?,?)", [current_user()['id'], $t, $b, $a]); flash('success', 'Announcement published.'); } }
    redirect('admin/announcements.php'); }
$rows = db_all("SELECT * FROM announcements ORDER BY created_at DESC");
$pageTitle = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Post an announcement</h2><form method="post"><?= csrf_field() ?>
  <div class="form-grid"><div class="field"><label>Title</label><input name="title" required></div>
  <div class="field"><label>Audience</label><select name="audience"><option value="ALL">Everyone</option><option value="DONOR">Donors</option><option value="VOLUNTEER">Volunteers</option><option value="BENEFICIARY">Beneficiaries</option><option value="ORGANIZATION">Organizations</option></select></div>
  <div class="field full"><label>Message</label><textarea name="body" required></textarea></div></div><button class="btn">Publish</button></form></div>
<div class="card"><h2>Published</h2>
<?php foreach ($rows as $a): ?><div class="ann"><div style="display:flex;justify-content:space-between;gap:10px"><h4><?= e($a['title']) ?> <span class="chip"><?= e($a['audience']) ?></span></h4>
 <form method="post" data-confirm="Delete?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $a['announcement_id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div>
 <p><?= nl2br(e($a['body'])) ?></p><small><?= fdate($a['created_at'], 'd M Y, H:i') ?></small></div>
<?php endforeach; if (!$rows): ?><p class="empty">Nothing yet.</p><?php endif; ?></div>
<?php include __DIR__ . '/../includes/footer.php';
