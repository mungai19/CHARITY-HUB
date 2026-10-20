<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (!empty($_SESSION['user'])) redirect(dashboard_path($_SESSION['user']['role']));

$error = '';
$allowed = ['DONOR','VOLUNTEER','BENEFICIARY','ORGANIZATION'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = post('name'); $email = strtolower(post('email')); $phone = post('phone');
    $role = post('role'); $org = post('organization_name');
    $pw = $_POST['password'] ?? ''; $pw2 = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $role === '' || $pw === '') $error = 'Please complete all required fields.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
    elseif (!in_array($role, $allowed, true)) $error = 'Please choose an account type.';
    elseif ($role === 'ORGANIZATION' && $org === '') $error = 'Please enter your organization name.';
    elseif (strlen($pw) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($pw !== $pw2) $error = 'Your passwords do not match.';
    elseif (db_val("SELECT COUNT(*) FROM users WHERE email = ?", [$email]) > 0) $error = 'An account with this email already exists.';
    else {
        global $conn;
        $conn->begin_transaction();
        $uid = db_exec("INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)",
                       [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $phone, $role]);
        $ok = $uid !== false && $uid > 0;
        if ($ok && $role === 'ORGANIZATION')
            $ok = db_exec("INSERT INTO organizations (user_id, organization_name) VALUES (?,?)", [$uid, $org]) !== false;
        if ($ok) {
            $conn->commit();
            $user = db_one("SELECT * FROM users WHERE user_id = ?", [$uid]);
            login_user($user);                                   // log in automatically
            flash('success', $role === 'ORGANIZATION'
                ? 'Account created! Your organization is awaiting verification by the administrator.'
                : 'Account created! Welcome to Charity Hub.');
            redirect(dashboard_path($role));                     // go straight to the dashboard
        } else { $conn->rollback(); $error = 'Registration failed. Please try again.'; }
    }
}
$sel = $_POST['role'] ?? ($_GET['role'] ?? '');
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create account | Charity Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>"><link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head><body>
<div class="auth">
  <div class="auth-side">
    <a class="brand" href="<?= url('index.php') ?>"><span class="logo-mark">♥</span> Charity Hub</a>
    <div>
      <h2>Join a community that <em>gives back</em>.</h2>
      <p>Pick how you want to make a difference. You can start in under a minute.</p>
      <ul class="auth-points">
        <li><span class="dot">💚</span> Donors give to verified projects</li>
        <li><span class="dot">🙋</span> Volunteers join real community events</li>
        <li><span class="dot">🤝</span> Beneficiaries request support with dignity</li>
        <li><span class="dot">🏢</span> Organizations manage everything in one place</li>
      </ul>
    </div>
    <small>© <?= date('Y') ?> Charity Hub · Designed for Kenya</small>
  </div>
  <div class="auth-main"><div class="auth-box">
    <h1>Create your account</h1>
    <p class="sub">It's free. Choose your account type first.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="">
      <?= csrf_field() ?>
      <div class="field"><label>I want to join as</label>
        <div class="role-pick">
          <?php foreach (['DONOR'=>['💚 Donor','Give to causes'],'VOLUNTEER'=>['🙋 Volunteer','Give your time'],'BENEFICIARY'=>['🤝 Beneficiary','Request support'],'ORGANIZATION'=>['🏢 Organization','Run programs']] as $k=>$v): ?>
            <label><input type="radio" name="role" value="<?= $k ?>" <?= $sel===$k?'checked':'' ?> required><span><?= $v[0] ?><small><?= $v[1] ?></small></span></label>
          <?php endforeach; ?>
        </div></div>
      <div class="field" id="orgField" style="display:none"><label for="organization_name">Organization name</label>
        <input id="organization_name" name="organization_name" maxlength="200" value="<?= e($_POST['organization_name'] ?? '') ?>" placeholder="e.g. Hope Foundation"></div>
      <div class="field"><label for="name">Full name</label><input id="name" name="name" maxlength="150" value="<?= e($_POST['name'] ?? '') ?>" required></div>
      <div class="form-grid">
        <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" maxlength="150" value="<?= e($_POST['email'] ?? '') ?>" required></div>
        <div class="field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" maxlength="20" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="07XX XXX XXX" required></div>
        <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" minlength="8" placeholder="Min. 8 characters" required></div>
        <div class="field"><label for="confirm_password">Confirm password</label><input type="password" id="confirm_password" name="confirm_password" required></div>
      </div>
      <button class="btn btn-block btn-lg" type="submit">Create account</button>
    </form>
    <p class="switch">Already have an account? <a href="<?= url('auth/login.php') ?>"><b>Log in</b></a></p>
  </div></div>
</div>
<script>
 var radios=document.querySelectorAll('input[name=role]'),f=document.getElementById('orgField'),i=document.getElementById('organization_name');
 function upd(){var v=document.querySelector('input[name=role]:checked');var on=v&&v.value==='ORGANIZATION';f.style.display=on?'block':'none';i.required=on;}
 radios.forEach(function(r){r.addEventListener('change',upd)});upd();
</script>
</body></html>
