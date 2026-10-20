<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$role = current_user()['role'];
$rows = db_all("SELECT a.*, u.name AS author FROM announcements a JOIN users u ON u.user_id=a.author_id
                WHERE a.audience IN ('ALL', ?) ORDER BY a.created_at DESC", [$role]);
$pageTitle = 'Announcements';
include __DIR__ . '/includes/header.php';
?>
<div class="card"><h2>Latest news &amp; updates</h2>
<?php if (!$rows): ?><p class="empty">No announcements yet.</p><?php endif; ?>
<?php foreach ($rows as $a): ?>
  <div class="ann"><h4><?= e($a['title']) ?></h4><p><?= nl2br(e($a['body'])) ?></p><small><?= e($a['author']) ?> · <?= fdate($a['created_at'], 'd M Y, H:i') ?></small></div>
<?php endforeach; ?></div>
<?php include __DIR__ . '/includes/footer.php';
