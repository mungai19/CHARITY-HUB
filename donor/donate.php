<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('DONOR');
$uid = current_user()['id'];
$projects = db_all("SELECT p.project_id, p.title, p.org_id, o.organization_name FROM projects p JOIN organizations o ON o.org_id=p.org_id WHERE o.verification_status='VERIFIED' AND p.status IN ('ONGOING','PLANNED') ORDER BY o.organization_name, p.title");
$orgs = db_all("SELECT org_id, organization_name FROM organizations WHERE verification_status='VERIFIED' ORDER BY organization_name");
$sel = (int)($_POST['project'] ?? $_GET['project'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check();
  $target = post('target');           // "p:ID" project  or  "o:ID" general fund
  $amount = round((float)post('amount'), 2);
  $method = post('method'); $msg = post('message'); $anon = isset($_POST['anonymous']) ? 1 : 0;
  $projectId = null; $orgId = 0;
  if (strpos($target, 'p:') === 0) {
    $pr = db_one("SELECT p.project_id, p.org_id FROM projects p JOIN organizations o ON o.org_id=p.org_id WHERE p.project_id=? AND o.verification_status='VERIFIED'", [(int)substr($target, 2)]);
    if ($pr) { $projectId = (int)$pr['project_id']; $orgId = (int)$pr['org_id']; }
  } elseif (strpos($target, 'o:') === 0) {
    $orgId = (int)db_val("SELECT org_id FROM organizations WHERE org_id=? AND verification_status='VERIFIED'", [(int)substr($target, 2)]);
  }
  if (!$orgId) flash('error', 'Please choose where your donation should go.');
  elseif ($amount < 50) flash('error', 'The minimum donation is KES 50.');
  elseif ($amount > 5000000) flash('error', 'Please contact us for donations above KES 5,000,000.');
  elseif (!in_array($method, ['M-Pesa','Card','Bank Transfer'], true)) flash('error', 'Choose a payment method.');
  else {
    $ref = 'CH-' . strtoupper(bin2hex(random_bytes(4)));
    // NOTE: payment is simulated for this prototype (no real money moves).
    $id = db_exec("INSERT INTO donations (donor_id,org_id,project_id,amount,payment_method,reference,message,anonymous,status) VALUES (?,?,?,?,?,?,?,?, 'COMPLETED')",
                  [$uid, $orgId, $projectId, $amount, $method, $ref, $msg ?: null, $anon]);
    if ($id) { flash('success', 'Thank you! Your donation of ' . money($amount) . ' was received. Reference: ' . $ref); redirect('donor/receipt.php?id=' . $id); }
    flash('error', 'Something went wrong. Please try again.');
  }
}
$pageTitle = 'Make a Donation';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid grid-2">
<div class="card"><h2>Give with confidence</h2>
<form method="post"><?= csrf_field() ?>
  <div class="field"><label>Where should your gift go?</label><select name="target" required>
    <option value="">— Choose a project or organization —</option>
    <optgroup label="Projects"><?php foreach ($projects as $p): ?><option value="p:<?= $p['project_id'] ?>" <?= $sel==$p['project_id']?'selected':'' ?>><?= e($p['title']) ?> — <?= e($p['organization_name']) ?></option><?php endforeach; ?></optgroup>
    <optgroup label="General fund"><?php foreach ($orgs as $o): ?><option value="o:<?= $o['org_id'] ?>"><?= e($o['organization_name']) ?> (where needed most)</option><?php endforeach; ?></optgroup></select></div>
  <div class="field"><label>Amount (KES)</label><input type="number" name="amount" min="50" step="1" id="amt" value="<?= e($_POST['amount'] ?? '') ?>" required>
    <div class="actions" style="margin-top:8px"><?php foreach ([500,1000,2500,5000] as $q): ?><button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('amt').value=<?= $q ?>"><?= number_format($q) ?></button><?php endforeach; ?></div></div>
  <div class="field"><label>Payment method</label><select name="method"><option>M-Pesa</option><option>Card</option><option>Bank Transfer</option></select>
    <div class="hint">Prototype mode: payments are recorded but no real money is charged.</div></div>
  <div class="field"><label>Message (optional)</label><input name="message" maxlength="255" placeholder="A few kind words…"></div>
  <div class="field"><label style="font-weight:500"><input type="checkbox" name="anonymous" style="width:auto"> Give anonymously</label></div>
  <button class="btn btn-lg btn-block">💚 Donate now</button>
</form></div>
<div class="card" style="background:var(--g-50)"><h2>Where your money goes</h2>
  <p>✅ Only <b>verified organizations</b> receive donations.</p><br><p>🧾 You get an <b>instant receipt</b> with a reference number.</p><br><p>📊 Organizations report on how funds are spent.</p><br><p>🔒 You can give <b>anonymously</b> if you prefer.</p></div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
