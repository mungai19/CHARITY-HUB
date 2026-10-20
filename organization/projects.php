<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
$cats = ['Education','Food Security','Health','Water','Environment','Shelter','General'];
$sts = ['PLANNED','ONGOING','COMPLETED','ON_HOLD'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $act = post('action'); $id = (int)post('id');
  if ($act === 'delete') { db_exec("DELETE FROM projects WHERE project_id=? AND org_id=?", [$id, $orgId]); flash('success', 'Project deleted.'); }
  else {
    $t = post('title'); $d = post('description'); $c = post('category'); $tg = (float)post('target_amount'); $st = post('status');
    $sd = post('start_date') ?: null; $ed = post('end_date') ?: null;
    if (!$t || !$d || $tg <= 0 || !in_array($c, $cats, true) || !in_array($st, $sts, true)) flash('error', 'Please fill in title, description and a target amount above 0.');
    elseif ($sd && $ed && $ed < $sd) flash('error', 'End date cannot be before the start date.');
    elseif ($act === 'update') { db_exec("UPDATE projects SET title=?,description=?,category=?,target_amount=?,start_date=?,end_date=?,status=? WHERE project_id=? AND org_id=?", [$t,$d,$c,$tg,$sd,$ed,$st,$id,$orgId]); flash('success', 'Project updated.'); }
    else { db_exec("INSERT INTO projects (org_id,title,description,category,target_amount,start_date,end_date,status) VALUES (?,?,?,?,?,?,?,?)", [$orgId,$t,$d,$c,$tg,$sd,$ed,$st]); flash('success', 'Project created.'); }
  }
  redirect('organization/projects.php');
}
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM projects WHERE project_id=? AND org_id=?", [(int)$_GET['edit'], $orgId]) : null;
$rows = db_all("SELECT p.*, (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.project_id=p.project_id AND d.status='COMPLETED') raised FROM projects p WHERE p.org_id=? ORDER BY p.created_at DESC", [$orgId]);
$pageTitle = 'Projects';
include __DIR__ . '/../includes/header.php'; org_notice();
?>
<div class="card"><h2><?= $edit ? 'Edit project' : 'Create a project' ?></h2>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= (int)($edit['project_id'] ?? 0) ?>">
 <div class="form-grid">
  <div class="field"><label>Title</label><input name="title" value="<?= e($edit['title'] ?? '') ?>" required></div>
  <div class="field"><label>Category</label><select name="category"><?php foreach ($cats as $c): ?><option <?= ($edit['category'] ?? '')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
  <div class="field full"><label>Description</label><textarea name="description" required><?= e($edit['description'] ?? '') ?></textarea></div>
  <div class="field"><label>Funding target (KES)</label><input type="number" step="0.01" min="1" name="target_amount" value="<?= e($edit['target_amount'] ?? '') ?>" required></div>
  <div class="field"><label>Status</label><select name="status"><?php foreach ($sts as $s): ?><option value="<?= $s ?>" <?= ($edit['status'] ?? 'ONGOING')===$s?'selected':'' ?>><?= ucwords(strtolower(str_replace('_',' ',$s))) ?></option><?php endforeach; ?></select></div>
  <div class="field"><label>Start date</label><input type="date" name="start_date" value="<?= e($edit['start_date'] ?? '') ?>"></div>
  <div class="field"><label>End date</label><input type="date" name="end_date" value="<?= e($edit['end_date'] ?? '') ?>"></div>
 </div><div class="actions"><button class="btn"><?= $edit ? 'Save changes' : 'Create project' ?></button><?php if ($edit): ?><a class="btn btn-light" href="<?= url('organization/projects.php') ?>">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>Your projects (<?= count($rows) ?>)</h2><div class="table-wrap"><table><thead><tr><th>Project</th><th>Category</th><th>Progress</th><th>Dates</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $p): $pct = $p['target_amount']>0 ? min(100, round($p['raised']/$p['target_amount']*100)) : 0; ?>
<tr><td><b><?= e($p['title']) ?></b></td><td><?= e($p['category']) ?></td><td style="min-width:170px"><div class="progress"><span style="width:<?= $pct ?>%"></span></div><small><?= money($p['raised']) ?> / <?= money($p['target_amount']) ?> (<?= $pct ?>%)</small></td>
<td><small><?= fdate($p['start_date']) ?> → <?= fdate($p['end_date']) ?></small></td><td><?= status_badge($p['status']) ?></td>
<td><div class="actions"><a class="btn btn-sm btn-light" href="?edit=<?= $p['project_id'] ?>">Edit</a>
<form method="post" data-confirm="Delete this project?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['project_id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No projects yet. Create your first one above.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
