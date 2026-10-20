<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$u = current_user();
$row = db_one("SELECT * FROM users WHERE user_id=?", [$u['id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action');
    if ($act === 'profile') {
        $name = post('name'); $phone = post('phone');
        if ($name === '' || $phone === '') flash('error', 'Name and phone are required.');
        else { db_exec("UPDATE users SET name=?, phone=? WHERE user_id=?", [$name, $phone, $u['id']]);
               $_SESSION['user']['name'] = $name; flash('success', 'Profile updated.'); }
    } elseif ($act === 'password') {
        if (!password_verify($_POST['current'] ?? '', $row['password'])) flash('error', 'Current password is incorrect.');
        elseif (strlen($_POST['new'] ?? '') < 8) flash('error', 'New password must be at least 8 characters.');
        elseif (($_POST['new'] ?? '') !== ($_POST['confirm'] ?? '')) flash('error', 'New passwords do not match.');
        else { db_exec("UPDATE users SET password=? WHERE user_id=?", [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); flash('success', 'Password changed.'); }
    } elseif ($act === 'org' && $u['role'] === 'ORGANIZATION') {
        db_exec("UPDATE organizations SET organization_name=?, description=?, location=? WHERE user_id=?",
                [post('organization_name'), post('description'), post('location'), $u['id']]);
        flash('success', 'Organization details updated.');
    }
    redirect('profile.php');
}
$org = $u['role'] === 'ORGANIZATION' ? my_org() : null;
$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
?>
<div class="grid grid-2">
  <div class="card"><h2>Personal details</h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
      <div class="field"><label>Full name</label><input name="name" value="<?= e($row['name']) ?>" required></div>
      <div class="field"><label>Email (cannot be changed)</label><input value="<?= e($row['email']) ?>" disabled></div>
      <div class="field"><label>Phone</label><input name="phone" value="<?= e($row['phone']) ?>" required></div>
      <div class="field"><label>Role</label><input value="<?= e($row['role']) ?>" disabled></div>
      <button class="btn">Save changes</button></form></div>
  <div class="card"><h2>Change password</h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="password">
      <div class="field"><label>Current password</label><input type="password" name="current" required></div>
      <div class="field"><label>New password</label><input type="password" name="new" minlength="8" required></div>
      <div class="field"><label>Confirm new password</label><input type="password" name="confirm" required></div>
      <button class="btn">Update password</button></form></div>
</div>
<?php if ($org): ?>
<div class="card"><h2>Organization details <?= status_badge($org['verification_status']) ?></h2>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="org">
    <div class="form-grid">
      <div class="field"><label>Organization name</label><input name="organization_name" value="<?= e($org['organization_name']) ?>" required></div>
      <div class="field"><label>Location</label><input name="location" value="<?= e($org['location']) ?>"></div>
      <div class="field full"><label>About your organization</label><textarea name="description"><?= e($org['description']) ?></textarea></div>
    </div><button class="btn">Save organization</button></form></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php';
