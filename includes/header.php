<?php
// App shell: sidebar + topbar. Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'Dashboard';
$u = current_user();
$menus = [
 'ADMIN' => [
   ['Overview', null], ['Dashboard','admin/dashboard.php','🏠'],
   ['Manage', null], ['Users','admin/users.php','👥'], ['Organizations','admin/organizations.php','🏢'],
   ['Donations','admin/donations.php','💚'], ['Projects','admin/projects.php','📁'], ['Events','admin/events.php','📅'],
   ['Insights', null], ['Reports','admin/reports.php','📊'], ['Announcements','admin/announcements.php','📣'],
   ['Account', null], ['My Profile','profile.php','👤'],
 ],
 'ORGANIZATION' => [
   ['Overview', null], ['Dashboard','organization/dashboard.php','🏠'],
   ['Programs', null], ['Projects','organization/projects.php','📁'], ['Events','organization/events.php','📅'],
   ['Volunteers','organization/volunteers.php','🙋'], ['Beneficiaries','organization/beneficiaries.php','🤝'],
   ['Money', null], ['Donations Received','organization/donations.php','💚'], ['Expenses','organization/expenses.php','🧾'],
   ['Insights', null], ['Reports','organization/reports.php','📊'], ['Announcements','announcements.php','📣'],
   ['Account', null], ['My Profile','profile.php','👤'],
 ],
 'DONOR' => [
   ['Overview', null], ['Dashboard','donor/dashboard.php','🏠'],
   ['Give', null], ['Browse Projects','donor/projects.php','🔎'], ['Make a Donation','donor/donate.php','💚'],
   ['My Giving', null], ['Donation History','donor/history.php','🧾'], ['Announcements','announcements.php','📣'],
   ['Account', null], ['My Profile','profile.php','👤'],
 ],
 'VOLUNTEER' => [
   ['Overview', null], ['Dashboard','volunteer/dashboard.php','🏠'],
   ['Volunteer', null], ['Open Events','volunteer/events.php','📅'], ['My Sign-ups','volunteer/my_events.php','✅'],
   ['Announcements','announcements.php','📣'],
   ['Account', null], ['My Profile','profile.php','👤'],
 ],
 'BENEFICIARY' => [
   ['Overview', null], ['Dashboard','beneficiary/dashboard.php','🏠'],
   ['Support', null], ['Request Assistance','beneficiary/request.php','🆘'], ['My Requests','beneficiary/requests.php','📋'],
   ['Announcements','announcements.php','📣'],
   ['Account', null], ['My Profile','profile.php','👤'],
 ],
];
$items = $menus[$u['role']] ?? [];
$self = str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Charity Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="shell">
<aside class="sidebar">
  <a class="logo" href="<?= url(dashboard_path($u['role'])) ?>" style="text-decoration:none"><span class="logo-mark">♥</span> Charity Hub</a>
  <nav>
    <?php foreach ($items as $it): ?>
      <?php if ($it[1] === null): ?><div class="nav-label"><?= e($it[0]) ?></div>
      <?php else: $active = (substr($self, -strlen($it[1])) === $it[1]) ? 'active' : ''; ?>
        <a href="<?= url($it[1]) ?>" class="<?= $active ?>"><span class="ic"><?= $it[2] ?></span><?= e($it[0]) ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>
  <div class="side-foot"><a href="<?= url('auth/logout.php') ?>" style="color:#d8f5e2">⎋ Log out</a><br><small><a href="<?= url('index.php') ?>" style="color:#9cc7ad">← Public website</a></small></div>
</aside>
<div class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px"><button class="menu-btn" id="menuBtn" aria-label="Menu">☰</button><h1><?= e($pageTitle) ?></h1></div>
    <div class="who"><span class="role-tag"><?= e($u['role']) ?></span><span class="name"><?= e($u['name']) ?></span><div class="avatar"><?= e(strtoupper(substr($u['name'],0,1))) ?></div></div>
  </div>
  <div class="content">
  <?php foreach (get_flash() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
  <?php endforeach; ?>
