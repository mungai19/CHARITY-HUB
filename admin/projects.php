<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
    if (post('action') === 'delete') { db_exec("DELETE FROM projects WHERE project_id=?", [(int)post('id')]); flash('success', 'Project removed.'); }
    redirect('admin/projects.php'); }
$rows = db_all("SELECT p.*, o.organization_name, (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.project_id=p.project_id) AS raised FROM projects p JOIN organizations o ON o.org_id=p.org_id ORDER BY p.created_at DESC");
$pageTitle = 'All Projects';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Projects (<?= count($rows) ?>)</h2><div class="toolbar"><input class="input" placeholder="Search…" data-search="ptbl"></div>
<div class="table-wrap"><table id="ptbl"><thead><tr><th>Project</th><th>Organization</th><th>Category</th><th>Progress</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $p): $pct = $p['target_amount']>0 ? min(100, round($p['raised']/$p['target_amount']*100)) : 0; ?>
<tr><td><b><?= e($p['title']) ?></b></td><td><?= e($p['organization_name']) ?></td><td><?= e($p['category']) ?></td>
<td style="min-width:170px"><div class="progress"><span style="width:<?= $pct ?>%"></span></div><small><?= money($p['raised']) ?> / <?= money($p['target_amount']) ?></small></td><td><?= status_badge($p['status']) ?></td>
<td><form method="post" data-confirm="Remove this project?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['project_id'] ?>"><button class="btn btn-sm btn-danger">Remove</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No projects.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
