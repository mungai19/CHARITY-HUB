<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Already logged in? Go straight to your dashboard.
if (!empty($_SESSION['user'])) redirect(dashboard_path($_SESSION['user']['role']));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(post('email'));
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $user = db_one("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Incorrect email or password.';
        } elseif ($user['status'] !== 'ACTIVE') {
            $error = 'Your account is suspended. Please contact the administrator.';
        } else {
            login_user($user);
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect(dashboard_path($user['role']));      // <-- sends you to YOUR dashboard
        }
    }
}
$pageTitle = 'Log in';
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in | Charity Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>"><link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head><body>
<div class="auth">
  <div class="auth-side">
    <a class="brand" href="<?= url('index.php') ?>"><span class="logo-mark">♥</span> Charity Hub</a>
    <div>
      <h2>Giving that is <em>simple, transparent</em> and shared.</h2>
      <p>One place where donors, volunteers, beneficiaries and organizations work together.</p>
      <ul class="auth-points">
        <li><span class="dot">✓</span> Track every donation from payment to impact</li>
        <li><span class="dot">✓</span> Volunteer for verified community events</li>
        <li><span class="dot">✓</span> Request and follow up on assistance easily</li>
      </ul>
    </div>
    <small>© <?= date('Y') ?> Charity Hub · Designed for Kenya</small>
  </div>
  <div class="auth-main"><div class="auth-box">
    <h1>Welcome back</h1>
    <p class="sub">Log in to continue to your dashboard.</p>
    <?php foreach (get_flash() as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="">
      <?= csrf_field() ?>
      <div class="field"><label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com" required autofocus></div>
      <div class="field"><label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required></div>
      <button class="btn btn-block btn-lg" type="submit">Log in</button>
    </form>
    <p class="switch">New here? <a href="<?= url('auth/register.php') ?>"><b>Create an account</b></a></p>
    <div class="demo"><b>Demo accounts</b> (password for all: <b>Password@123</b>)
      <table>
        <tr><td>Admin</td><td>admin@charityhub.org</td></tr>
        <tr><td>Organization</td><td>org@charityhub.org</td></tr>
        <tr><td>Donor</td><td>donor@charityhub.org</td></tr>
        <tr><td>Volunteer</td><td>volunteer@charityhub.org</td></tr>
        <tr><td>Beneficiary</td><td>beneficiary@charityhub.org</td></tr>
      </table></div>
  </div></div>
</div></body></html>
