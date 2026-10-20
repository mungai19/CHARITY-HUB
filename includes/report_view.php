<?php
// Shared report page. Set $reportOrg (int org_id, or null for ALL) before including.
$w  = $reportOrg ? " AND d.org_id = " . (int)$reportOrg : "";
$we = $reportOrg ? " AND org_id = " . (int)$reportOrg : "";
$base = $reportOrg ? 'organization/reports.php' : 'admin/reports.php';

// CSV export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    if ($type === 'donations') {
        $rows = db_all("SELECT d.reference, u.name donor, o.organization_name organization, COALESCE(p.title,'General fund') project, d.payment_method, d.amount, d.status, d.created_at
            FROM donations d JOIN users u ON u.user_id=d.donor_id JOIN organizations o ON o.org_id=d.org_id LEFT JOIN projects p ON p.project_id=d.project_id WHERE 1=1 $w ORDER BY d.created_at DESC");
    } else {
        $rows = db_all("SELECT title, category, amount, expense_date, notes FROM expenses WHERE 1=1 $we ORDER BY expense_date DESC");
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="charityhub_' . preg_replace('/\W/', '', $type) . '_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $r) fputcsv($out, $r); }
    fclose($out); exit;
}

$totalIn  = (float)db_val("SELECT COALESCE(SUM(d.amount),0) FROM donations d WHERE d.status='COMPLETED' $w");
$totalOut = (float)db_val("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE 1=1 $we");
$nDon     = (int)db_val("SELECT COUNT(*) FROM donations d WHERE d.status='COMPLETED' $w");
$avg      = $nDon ? $totalIn / $nDon : 0;
$monthly  = db_all("SELECT DATE_FORMAT(d.created_at,'%Y-%m') m, SUM(d.amount) t, COUNT(*) c FROM donations d WHERE d.status='COMPLETED' $w GROUP BY m ORDER BY m DESC LIMIT 6");
$byProj   = db_all("SELECT COALESCE(p.title,'General fund') title, SUM(d.amount) t FROM donations d LEFT JOIN projects p ON p.project_id=d.project_id WHERE d.status='COMPLETED' $w GROUP BY title ORDER BY t DESC LIMIT 8");
$byMethod = db_all("SELECT d.payment_method m, SUM(d.amount) t FROM donations d WHERE d.status='COMPLETED' $w GROUP BY m ORDER BY t DESC");
$byCat    = db_all("SELECT category, SUM(amount) t FROM expenses WHERE 1=1 $we GROUP BY category ORDER BY t DESC");
$vol = db_one("SELECT COUNT(DISTINCT s.volunteer_id) vols, COALESCE(SUM(s.hours),0) hrs FROM volunteer_signups s JOIN events e ON e.event_id=s.event_id WHERE s.status='APPROVED'" . ($reportOrg ? " AND e.org_id=" . (int)$reportOrg : ""));
$ben = db_all("SELECT r.status, COUNT(*) c FROM assistance_requests r WHERE 1=1" . ($reportOrg ? " AND r.org_id=" . (int)$reportOrg : "") . " GROUP BY r.status");

function bars($rows, $labelKey, $valKey) {
    if (!$rows) { echo '<p class="empty">No data yet.</p>'; return; }
    $max = max(array_map(fn($r) => (float)$r[$valKey], $rows)) ?: 1;
    foreach ($rows as $r) echo '<div class="bar-row"><span title="' . e($r[$labelKey]) . '">' . e(mb_strimwidth($r[$labelKey], 0, 18, '…')) . '</span><div class="progress"><span style="width:' . round($r[$valKey] / $max * 100) . '%"></span></div><b>' . money($r[$valKey]) . '</b></div>';
}
$pageTitle = 'Reports & Analytics';
include __DIR__ . '/header.php';
?>
<div class="toolbar no-print"><p class="hint">Generated <?= date('d M Y, H:i') ?></p>
  <div class="actions"><a class="btn btn-sm btn-light" href="<?= url($base . '?export=donations') ?>">⬇ Donations CSV</a><a class="btn btn-sm btn-light" href="<?= url($base . '?export=expenses') ?>">⬇ Expenses CSV</a><button class="btn btn-sm" onclick="window.print()">🖨 Print / Save PDF</button></div></div>
<div class="grid grid-4" style="margin-bottom:22px">
  <div class="stat dark"><div class="label">Donations received</div><div class="value"><?= money($totalIn) ?></div><div class="sub"><?= $nDon ?> gifts</div></div>
  <div class="stat"><div class="label">Expenses</div><div class="value"><?= money($totalOut) ?></div></div>
  <div class="stat"><div class="label">Available balance</div><div class="value"><?= money($totalIn - $totalOut) ?></div></div>
  <div class="stat"><div class="label">Average gift</div><div class="value"><?= money($avg) ?></div></div>
</div>
<div class="grid grid-2">
  <div class="card"><h2>Donations per month</h2><?php bars(array_reverse($monthly), 'm', 't'); ?></div>
  <div class="card"><h2>Donations by project</h2><?php bars($byProj, 'title', 't'); ?></div>
  <div class="card"><h2>Payment methods</h2><?php bars($byMethod, 'm', 't'); ?></div>
  <div class="card"><h2>Expenses by category</h2><?php bars($byCat, 'category', 't'); ?></div>
  <div class="card"><h2>Volunteering</h2><dl class="kv"><dt>Approved volunteers</dt><dd><b><?= (int)$vol['vols'] ?></b></dd><dt>Total hours logged</dt><dd><b><?= (float)$vol['hrs'] ?></b></dd></dl></div>
  <div class="card"><h2>Assistance requests</h2><?php if (!$ben): ?><p class="empty">No requests yet.</p><?php endif; ?><dl class="kv"><?php foreach ($ben as $b): ?><dt><?= status_badge($b['status']) ?></dt><dd><b><?= (int)$b['c'] ?></b></dd><?php endforeach; ?></dl></div>
</div>
<?php include __DIR__ . '/footer.php';
