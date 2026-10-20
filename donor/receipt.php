<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('DONOR');
$d = db_one("SELECT d.*, o.organization_name, p.title, u.name donor, u.email FROM donations d JOIN organizations o ON o.org_id=d.org_id JOIN users u ON u.user_id=d.donor_id LEFT JOIN projects p ON p.project_id=d.project_id WHERE d.donation_id=? AND d.donor_id=?", [(int)($_GET['id'] ?? 0), current_user()['id']]);
if (!$d) { flash('error', 'Receipt not found.'); redirect('donor/history.php'); }
$pageTitle = 'Donation Receipt';
include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:640px;margin:auto"><div style="text-align:center;margin-bottom:18px"><div class="logo-mark" style="margin:0 auto 10px;width:52px;height:52px;font-size:26px">♥</div><h2 style="justify-content:center;font-size:24px">Donation Receipt</h2><p class="hint">Thank you for your generosity!</p></div>
<dl class="kv"><dt>Reference</dt><dd><b><?= e($d['reference']) ?></b></dd><dt>Date</dt><dd><?= fdate($d['created_at'], 'd M Y, H:i') ?></dd><dt>Donor</dt><dd><?= e($d['donor']) ?> (<?= e($d['email']) ?>)</dd>
<dt>Organization</dt><dd><?= e($d['organization_name']) ?></dd><dt>Project</dt><dd><?= e($d['title'] ?: 'General fund') ?></dd><dt>Payment method</dt><dd><?= e($d['payment_method']) ?></dd><dt>Status</dt><dd><?= status_badge($d['status']) ?></dd>
<dt>Amount</dt><dd style="font:700 26px var(--serif);color:var(--g-800)"><?= money($d['amount']) ?></dd></dl>
<div class="actions no-print" style="margin-top:22px"><button class="btn" onclick="window.print()">🖨 Print / Save PDF</button><a class="btn btn-light" href="<?= url('donor/history.php') ?>">Back to history</a></div></div>
<?php include __DIR__ . '/../includes/footer.php';
