<?php
$icons = ['Education'=>'📚','Food Security'=>'🌽','Health'=>'🩺','Water'=>'💧','Environment'=>'🌱','Shelter'=>'🏠','General'=>'💚'];
$pct = $p['target_amount'] > 0 ? min(100, round($p['raised'] / $p['target_amount'] * 100)) : 0; ?>
<div class="pcard"><div class="pimg"><?= $icons[$p['category']] ?? '💚' ?></div><div class="pbody">
  <div><span class="chip"><?= e($p['category']) ?></span></div><h3><?= e($p['title']) ?></h3>
  <small style="color:var(--g-700);font-weight:600"><?= e($p['organization_name']) ?></small>
  <p style="color:var(--muted);font-size:14px;flex:1"><?= e(mb_strimwidth($p['description'], 0, 100, '…')) ?></p>
  <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
  <div class="meta"><span><b><?= money($p['raised']) ?></b> raised</span><span><?= $pct ?>%</span></div>
  <a class="btn btn-block" href="<?= url('donor/donate.php?project=' . (int)$p['project_id']) ?>">Donate</a></div></div>
