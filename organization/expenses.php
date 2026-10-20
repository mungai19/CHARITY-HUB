<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/org_guard.php';
$cats = ['Education','Food','Health','Water','Transport','Salaries','Administration','Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $act = post('action'); $id = (int)post('id');
  if ($act === 'delete') { db_exec("DELETE FROM expenses WHERE expense_id=? AND org_id=?", [$id, $orgId]); flash('success', 'Expense deleted.'); }
  else {
    $t = post('title'); $c = post('category'); $a = (float)post('amount'); $d = post('expense_date'); $n = post('notes'); $pid = (int)post('project_id') ?: null;
    if ($pid && !db_val("SELECT COUNT(*) FROM projects WHERE project_id=? AND org_id=?", [$pid, $orgId])) $pid = null;
    if (!$t || $a <= 0 || !$d || !in_array($c, $cats, true)) flash('error', 'Please enter a title, category, amount and date.');
    elseif ($act === 'update') { db_exec("UPDATE expenses SET project_id=?,title=?,category=?,amount=?,expense_date=?,notes=? WHERE expense_id=? AND org_id=?", [$pid,$t,$c,$a,$d,$n ?: null,$id,$orgId]); flash('success', 'Expense updated.'); }
    else { db_exec("INSERT INTO expenses (org_id,project_id,title,category,amount,expense_date,notes) VALUES (?,?,?,?,?,?,?)", [$orgId,$pid,$t,$c,$a,$d,$n ?: null]); flash('success', 'Expense recorded.'); }
  }
  redirect('organization/expenses.php');
}
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM expenses WHERE expense_id=? AND org_id=?", [(int)$_GET['edit'], $orgId]) : null;
$projects = db_all("SELECT project_id,title FROM projects WHERE org_id=? ORDER BY title", [$orgId]);
$rows = db_all("SELECT x.*, p.title ptitle FROM expenses x LEFT JOIN projects p ON p.project_id=x.project_id WHERE x.org_id=? ORDER BY x.expense_date DESC", [$orgId]);
$pageTitle = 'Expenses';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2><?= $edit ? 'Edit expense' : 'Record an expense' ?></h2>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= (int)($edit['expense_id'] ?? 0) ?>">
<div class="form-grid">
 <div class="field"><label>Title</label><input name="title" value="<?= e($edit['title'] ?? '') ?>" required></div>
 <div class="field"><label>Project (optional)</label><select name="project_id"><option value="">— none —</option><?php foreach ($projects as $p): ?><option value="<?= $p['project_id'] ?>" <?= ($edit['project_id'] ?? 0)==$p['project_id']?'selected':'' ?>><?= e($p['title']) ?></option><?php endforeach; ?></select></div>
 <div class="field"><label>Category</label><select name="category"><?php foreach ($cats as $c): ?><option <?= ($edit['category'] ?? '')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
 <div class="field"><label>Amount (KES)</label><input type="number" step="0.01" min="1" name="amount" value="<?= e($edit['amount'] ?? '') ?>" required></div>
 <div class="field"><label>Date</label><input type="date" name="expense_date" value="<?= e($edit['expense_date'] ?? date('Y-m-d')) ?>" required></div>
 <div class="field"><label>Notes</label><input name="notes" value="<?= e($edit['notes'] ?? '') ?>"></div>
</div><div class="actions"><button class="btn"><?= $edit ? 'Save changes' : 'Add expense' ?></button><?php if ($edit): ?><a class="btn btn-light" href="<?= url('organization/expenses.php') ?>">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>Expenses <span class="chip">Total <?= money(array_sum(array_column($rows,'amount'))) ?></span></h2><div class="table-wrap"><table><thead><tr><th>Title</th><th>Project</th><th>Category</th><th>Amount</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $x): ?><tr><td><b><?= e($x['title']) ?></b><br><small><?= e($x['notes']) ?></small></td><td><?= e($x['ptitle'] ?: '—') ?></td><td><?= e($x['category']) ?></td><td><b><?= money($x['amount']) ?></b></td><td><?= fdate($x['expense_date']) ?></td>
<td><div class="actions"><a class="btn btn-sm btn-light" href="?edit=<?= $x['expense_id'] ?>">Edit</a><form method="post" data-confirm="Delete this expense?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $x['expense_id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No expenses recorded.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
