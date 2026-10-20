<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ADMIN');
$me = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)post('id'); $act = post('action');
    if ($id === $me) flash('error', 'You cannot change your own account here.');
    elseif ($act === 'suspend') { db_exec("UPDATE users SET status='SUSPENDED' WHERE user_id=?", [$id]); flash('success', 'User suspended.'); }
    elseif ($act === 'activate') { db_exec("UPDATE users SET status='ACTIVE' WHERE user_id=?", [$id]); flash('success', 'User activated.'); }
    elseif ($act === 'delete') { db_exec("DELETE FROM users WHERE user_id=?", [$id]); flash('success', 'User deleted.'); }
    elseif ($act === 'create') {
        $n = post('name'); $em = strtolower(post('email')); $ph = post('phone'); $ro = post('role'); $pw = post('password');
        if (!$n || !filter_var($em, FILTER_VALIDATE_EMAIL) || !$ph || strlen($pw) < 8 || !in_array($ro, ['ADMIN','DONOR','VOLUNTEER','BENEFICIARY'], true)) flash('error', 'Fill in all fields (password min. 8 characters).');
        elseif (db_val("SELECT COUNT(*) FROM users WHERE email=?", [$em])) flash('error', 'That email is already registered.');
        else { db_exec("INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)", [$n,$em,password_hash($pw, PASSWORD_DEFAULT),$ph,$ro]); flash('success', 'User created.'); }
    }
    redirect('admin/users.php');
}
$role = $_GET['role'] ?? '';
$users = $role ? db_all("SELECT * FROM users WHERE role=? ORDER BY created_at DESC", [$role]) : db_all("SELECT * FROM users ORDER BY created_at DESC");
$pageTitle = 'User Management';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h2>Add a user (admin, donor, volunteer or beneficiary)</h2>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="create"><input type="hidden" name="id" value="0">
 <div class="form-grid">
  <div class="field"><label>Full name</label><input name="name" required></div>
  <div class="field"><label>Email</label><input type="email" name="email" required></div>
  <div class="field"><label>Phone</label><input name="phone" required></div>
  <div class="field"><label>Role</label><select name="role"><option>DONOR</option><option>VOLUNTEER</option><option>BENEFICIARY</option><option>ADMIN</option></select></div>
  <div class="field"><label>Temporary password</label><input type="password" name="password" minlength="8" required></div>
 </div><button class="btn">Create user</button></form></div>
<div class="card"><h2>All users (<?= count($users) ?>)</h2>
<div class="toolbar"><input class="input" placeholder="Search users…" data-search="utbl">
  <form method="get"><select class="input" name="role" onchange="this.form.submit()"><option value="">All roles</option>
   <?php foreach (['ADMIN','ORGANIZATION','DONOR','VOLUNTEER','BENEFICIARY'] as $r): ?><option <?= $role===$r?'selected':'' ?>><?= $r ?></option><?php endforeach; ?></select></form></div>
<div class="table-wrap"><table id="utbl"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($users as $x): ?><tr><td><b><?= e($x['name']) ?></b></td><td><?= e($x['email']) ?></td><td><?= e($x['phone']) ?></td><td><span class="chip"><?= e($x['role']) ?></span></td><td><?= status_badge($x['status']) ?></td><td><?= fdate($x['created_at']) ?></td>
 <td><?php if ($x['user_id'] != $me): ?><div class="actions">
  <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['user_id'] ?>"><input type="hidden" name="action" value="<?= $x['status']==='ACTIVE'?'suspend':'activate' ?>"><button class="btn btn-sm btn-light"><?= $x['status']==='ACTIVE'?'Suspend':'Activate' ?></button></form>
  <form method="post" data-confirm="Delete this user and all their records?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['user_id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-danger">Delete</button></form></div><?php else: ?><small>You</small><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php';
