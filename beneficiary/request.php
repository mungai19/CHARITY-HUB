<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('BENEFICIARY');
$orgs = db_all("SELECT org_id, organization_name, location FROM organizations WHERE verification_status='VERIFIED' ORDER BY organization_name");
$types = ['Education Support','Food Assistance','Medical Support','Shelter / Housing','Clean Water','Livelihood / Business Support','Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $o = (int)post('org_id'); $t = post('assistance_type'); $d = post('description');
  if (!db_val("SELECT COUNT(*) FROM organizations WHERE org_id=? AND verification_status='VERIFIED'", [$o]) || !in_array($t, $types, true) || strlen($d) < 10) flash('error', 'Choose an organization, a type of help and describe your need (at least 10 characters).');
  else { db_exec("INSERT INTO assistance_requests (beneficiary_id,org_id,assistance_type,description) VALUES (?,?,?,?)", [current_user()['id'], $o, $t, $d]); flash('success', 'Your request was submitted. You can track it under My Requests.'); redirect('beneficiary/requests.php'); }
}
$pageTitle = 'Request Assistance';
include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:720px"><h2>Tell us what you need</h2>
<form method="post"><?= csrf_field() ?>
 <div class="field"><label>Organization</label><select name="org_id" required><option value="">— Select —</option><?php foreach ($orgs as $o): ?><option value="<?= $o['org_id'] ?>"><?= e($o['organization_name']) ?><?= $o['location'] ? ' · ' . e($o['location']) : '' ?></option><?php endforeach; ?></select></div>
 <div class="field"><label>Type of assistance</label><select name="assistance_type" required><?php foreach ($types as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select></div>
 <div class="field"><label>Describe your situation</label><textarea name="description" required minlength="10" placeholder="Explain what help you need and why."></textarea></div>
 <button class="btn btn-lg">Submit request</button></form></div>
<?php include __DIR__ . '/../includes/footer.php';
