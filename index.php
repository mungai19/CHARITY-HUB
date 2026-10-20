<?php
require_once __DIR__ . '/includes/bootstrap.php';
$u = current_user();

// Live numbers from the database
$raised   = (float)db_val("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='COMPLETED'");
$donors   = (int)db_val("SELECT COUNT(*) FROM users WHERE role='DONOR'");
$vols     = (int)db_val("SELECT COUNT(*) FROM users WHERE role='VOLUNTEER'");
$helped   = (int)db_val("SELECT COUNT(DISTINCT beneficiary_id) FROM assistance_requests WHERE status IN ('APPROVED','FULFILLED')");
$nprojects= (int)db_val("SELECT COUNT(*) FROM projects p JOIN organizations o ON o.org_id=p.org_id WHERE o.verification_status='VERIFIED'");
$recent30 = (float)db_val("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='COMPLETED' AND created_at >= NOW() - INTERVAL 90 DAY");

$projects = db_all("SELECT p.*, o.organization_name,
      (SELECT COALESCE(SUM(d.amount),0) FROM donations d WHERE d.project_id=p.project_id AND d.status='COMPLETED') AS raised
    FROM projects p JOIN organizations o ON o.org_id=p.org_id
    WHERE o.verification_status='VERIFIED' AND p.status IN ('ONGOING','PLANNED')
    ORDER BY p.created_at DESC LIMIT 6");
$events = db_all("SELECT e.*, o.organization_name FROM events e JOIN organizations o ON o.org_id=e.org_id
    WHERE e.status='OPEN' AND e.event_date >= CURDATE() ORDER BY e.event_date LIMIT 3");

$icons = ['Education'=>'📚','Food Security'=>'🌽','Health'=>'🩺','Water'=>'💧','Environment'=>'🌱','Shelter'=>'🏠','General'=>'💚'];
$cta = $u ? url(dashboard_path($u['role'])) : url('auth/register.php');
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Charity Hub – Simple giving for causes that matter most</title>
<meta name="description" content="Charity Hub connects donors, volunteers, beneficiaries and verified organizations in one transparent platform.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>"><link rel="stylesheet" href="<?= asset('css/landing.css') ?>">
</head><body>

<header class="nav" id="nav"><div class="container">
  <a class="brand" href="#top"><span class="logo-mark">♥</span> Charity Hub</a>
  <nav class="nav-links" id="navLinks">
    <a class="lnk" href="#how">How it works</a><a class="lnk" href="#projects">Projects</a>
    <a class="lnk" href="#roles">Who it's for</a><a class="lnk" href="#events">Events</a><a class="lnk" href="#faq">FAQ</a>
    <div class="nav-cta">
      <?php if ($u): ?><a class="btn" href="<?= url(dashboard_path($u['role'])) ?>">My Dashboard</a>
      <?php else: ?><a class="btn btn-light" href="<?= url('auth/login.php') ?>">Log in</a><a class="btn" href="<?= url('auth/register.php') ?>">Get started</a><?php endif; ?>
    </div>
  </nav>
  <div class="nav-cta">
    <?php if ($u): ?><a class="btn" href="<?= url(dashboard_path($u['role'])) ?>">My Dashboard</a>
    <?php else: ?><a class="btn btn-light" href="<?= url('auth/login.php') ?>">Log in</a><a class="btn" href="<?= url('auth/register.php') ?>">Get started</a><?php endif; ?>
  </div>
  <button class="burger" id="burger" aria-label="Menu">☰</button>
</div></header>

<main id="top">
<section class="hero" style="padding:0"><div class="container">
  <div>
    <span class="pill"><i></i> <?= money($recent30) ?> raised in the last 90 days</span>
    <h1>Simple giving for causes <em>that matter most.</em></h1>
    <p class="lead">One transparent platform where <b>donors, volunteers, beneficiaries and verified organizations</b> work together — so every shilling and every hour reaches the people who need it.</p>
    <div class="hero-cta">
      <a class="btn btn-lg" href="<?= $u ? url('donor/projects.php') : url('auth/register.php?role=DONOR') ?>">💚 Start giving</a>
      <a class="btn btn-lg btn-light" href="<?= url('auth/register.php?role=VOLUNTEER') ?>">🙋 Volunteer with us</a>
    </div>
    <div class="trust">
      <div class="faces"><span style="background:#bbf7d0">🙂</span><span style="background:#86efac">😊</span><span style="background:#4ade80">😄</span><span style="background:#22c55e">🤗</span></div>
      <div>Join <b><?= number_format($donors + $vols) ?>+</b> donors &amp; volunteers already making a difference</div>
    </div>
  </div>
  <div class="collage" aria-hidden="true">
    <div class="tile t1"><span class="big">🌍</span></div>
    <div class="tile t2"><span class="big">📚</span></div>
    <div class="tile t3"><span class="big">🩺</span></div>
    <div class="tile t4"><span class="big">🌱</span></div>
    <div class="fcard fc1">Donations made<b data-count="<?= (int)db_val("SELECT COUNT(*) FROM donations WHERE status='COMPLETED'") ?>">0</b></div>
    <div class="fcard fc2">Raised so far<b><?= money($raised) ?></b><div class="progress"><span style="width:72%"></span></div></div>
  </div>
</div></section>

<div class="marquee"><div>
  <?php for ($i=0;$i<2;$i++) foreach (['Education','Food Security','Clean Water','Healthcare','Environment','Shelter','Youth Empowerment','Women Support','Disaster Relief'] as $c): ?><span><?= $c ?></span><?php endforeach; ?>
</div></div>

<section class="impact"><div class="container"><div class="grid">
  <div class="imp"><b data-count="<?= (int)$raised ?>" data-prefix="KES ">0</b><span>Total raised</span></div>
  <div class="imp"><b data-count="<?= $donors ?>">0</b><span>Generous donors</span></div>
  <div class="imp"><b data-count="<?= $vols ?>">0</b><span>Active volunteers</span></div>
  <div class="imp"><b data-count="<?= $nprojects ?>">0</b><span>Verified projects</span></div>
  <div class="imp"><b data-count="<?= $helped ?>">0</b><span>People supported</span></div>
</div></div></section>

<section id="how"><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">How it works</span><h2>Giving made clear in three steps</h2><p>No confusion, no middlemen. You can see where your support goes.</p></div>
  <div class="grid steps">
    <div class="step reveal"><div class="num">1</div><h3>Create your account</h3><p>Sign up as a donor, volunteer, beneficiary or organization. It takes less than a minute.</p></div>
    <div class="step reveal"><div class="num">2</div><h3>Choose a cause</h3><p>Browse projects and events from verified organizations and pick what you care about.</p></div>
    <div class="step reveal"><div class="num">3</div><h3>Make an impact</h3><p>Donate, sign up to volunteer or request support — then follow the progress in real time.</p></div>
  </div>
</div></section>

<section id="projects" class="projects-bg"><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">Featured projects</span><h2>Causes that need you right now</h2><p>Every project is run by an organization verified by our administrators.</p></div>
  <?php if (!$projects): ?><p class="empty">No projects yet. Check back soon!</p><?php endif; ?>
  <div class="grid pgrid">
  <?php foreach ($projects as $i => $p): $pct = $p['target_amount'] > 0 ? min(100, round($p['raised'] / $p['target_amount'] * 100)) : 0; ?>
    <article class="pc reveal">
      <div class="ph gr<?= $i % 4 ?>"><span class="cat"><?= e($p['category']) ?></span><?= $icons[$p['category']] ?? '💚' ?></div>
      <div class="pb">
        <h3><?= e($p['title']) ?></h3>
        <small style="color:var(--g-700);font-weight:600"><?= e($p['organization_name']) ?></small>
        <p><?= e(mb_strimwidth($p['description'], 0, 110, '…')) ?></p>
        <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
        <div class="row"><span><b><?= money($p['raised']) ?></b> raised</span><span><?= $pct ?>% of <?= money($p['target_amount']) ?></span></div>
        <a class="btn btn-block" href="<?= url('donor/donate.php?project=' . (int)$p['project_id']) ?>">Donate now</a>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
</div></section>

<section id="roles"><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">Who it's for</span><h2>A place for everyone in the giving chain</h2><p>Pick your role and get a dashboard built for what you need to do.</p></div>
  <div class="grid roles">
    <div class="role r1 reveal"><div class="ico">💚</div><h3>Donors</h3><p>Give securely to verified projects, get instant receipts and track the impact of every donation.</p><a href="<?= url('auth/register.php?role=DONOR') ?>">Become a donor →</a></div>
    <div class="role r2 reveal"><div class="ico">🙋</div><h3>Volunteers</h3><p>Find community events near you, sign up in a click and keep a record of your volunteer hours.</p><a href="<?= url('auth/register.php?role=VOLUNTEER') ?>">Start volunteering →</a></div>
    <div class="role r3 reveal"><div class="ico">🤝</div><h3>Beneficiaries</h3><p>Request education, food, health or other support and follow the status of every request.</p><a href="<?= url('auth/register.php?role=BENEFICIARY') ?>">Request support →</a></div>
    <div class="role r4 reveal"><div class="ico">🏢</div><h3>Organizations</h3><p>Manage projects, events, volunteers, beneficiaries, expenses and reports from one place.</p><a href="<?= url('auth/register.php?role=ORGANIZATION') ?>">Register your NGO →</a></div>
  </div>
</div></section>

<section class="events-bg" id="events"><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">Upcoming events</span><h2>Give your time, change a life</h2><p>Sign up to volunteer at these upcoming community events.</p></div>
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(310px,1fr))">
  <?php if (!$events): ?><p class="empty">No upcoming events right now.</p><?php endif; ?>
  <?php foreach ($events as $ev): ?>
    <div class="ev reveal"><div class="date"><b><?= date('d', strtotime($ev['event_date'])) ?></b><small><?= date('M', strtotime($ev['event_date'])) ?></small></div>
      <div><h4><?= e($ev['title']) ?></h4><p>📍 <?= e($ev['location']) ?> · <?= e($ev['organization_name']) ?></p>
      <a href="<?= url('volunteer/events.php') ?>"><b>Volunteer →</b></a></div></div>
  <?php endforeach; ?>
  </div>
</div></section>

<section><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">Why Charity Hub</span><h2>Built for trust, built for Kenya</h2></div>
  <div class="grid features">
    <div class="feat reveal"><div class="fi">🔒</div><div><h3>Secure by design</h3><p>Hashed passwords, role-based access and protected forms keep every account safe.</p></div></div>
    <div class="feat reveal"><div class="fi">✅</div><div><h3>Verified organizations</h3><p>Only organizations approved by an administrator can publish projects to donors.</p></div></div>
    <div class="feat reveal"><div class="fi">📱</div><div><h3>M-Pesa friendly</h3><p>Record gifts by M-Pesa, card or bank transfer with a unique receipt reference.</p></div></div>
    <div class="feat reveal"><div class="fi">📊</div><div><h3>Clear reports</h3><p>Donation, volunteer and expense reports help organizations stay accountable.</p></div></div>
    <div class="feat reveal"><div class="fi">📣</div><div><h3>Stay connected</h3><p>Announcements keep donors, volunteers and beneficiaries informed.</p></div></div>
    <div class="feat reveal"><div class="fi">💻</div><div><h3>Works on any device</h3><p>Fully responsive on phones, tablets and computers.</p></div></div>
  </div>
</div></section>

<section id="faq" style="background:var(--g-50)"><div class="container">
  <div class="sec-head reveal"><span class="eyebrow">FAQ</span><h2>Questions? We've got answers</h2></div>
  <div class="faq reveal">
    <details><summary>Is it free to join?</summary><p>Yes. Creating an account as a donor, volunteer, beneficiary or organization is free.</p></details>
    <details><summary>How do I know an organization is genuine?</summary><p>Organizations must be verified by an administrator before their projects appear publicly to donors.</p></details>
    <details><summary>Can I donate anonymously?</summary><p>Yes. Tick "Give anonymously" when donating and your name will not be shown publicly.</p></details>
    <details><summary>How do I volunteer?</summary><p>Create a volunteer account, open the Events page and sign up. The organization will approve your request.</p></details>
    <details><summary>I need help. How do I request assistance?</summary><p>Register as a beneficiary, choose an organization and submit a request. You can track its status from your dashboard.</p></details>
  </div>
</div></section>

<section class="cta" style="padding-top:88px"><div class="container"><div class="cta-box reveal">
  <h2>Ready to make a difference today?</h2><p>Join Charity Hub and be part of a community that gives with trust.</p>
  <a class="btn btn-lg" href="<?= $cta ?>"><?= $u ? 'Go to my dashboard' : 'Create free account' ?></a>
  <?php if (!$u): ?><a class="btn btn-lg btn-outline" href="<?= url('auth/login.php') ?>">Log in</a><?php endif; ?>
</div></div></section>
</main>

<footer class="site"><div class="container">
  <div class="fgrid">
    <div><a class="brand" href="#top"><span class="logo-mark">♥</span> Charity Hub</a><p>A centralized web-based charity platform that improves coordination, transparency and resource distribution.</p></div>
    <div><h5>Platform</h5><a href="#how">How it works</a><a href="#projects">Projects</a><a href="#events">Events</a></div>
    <div><h5>Join</h5><a href="<?= url('auth/register.php?role=DONOR') ?>">As a donor</a><a href="<?= url('auth/register.php?role=VOLUNTEER') ?>">As a volunteer</a><a href="<?= url('auth/register.php?role=ORGANIZATION') ?>">As an organization</a></div>
    <div><h5>Account</h5><a href="<?= url('auth/login.php') ?>">Log in</a><a href="<?= url('auth/register.php') ?>">Register</a></div>
  </div>
  <div class="copy"><span>© <?= date('Y') ?> Charity Hub NGO Management System. Designed for Kenya.</span><span>Strathmore University · BBIT Project</span></div>
</div></footer>

<script>
(function(){
  var nav=document.getElementById('nav');addEventListener('scroll',function(){nav.classList.toggle('scrolled',scrollY>10)});
  document.getElementById('burger').onclick=function(){document.getElementById('navLinks').classList.toggle('open')};
  document.querySelectorAll('#navLinks a.lnk').forEach(function(a){a.onclick=function(){document.getElementById('navLinks').classList.remove('open')}});
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:.12});
  document.querySelectorAll('.reveal').forEach(function(el,i){el.style.transitionDelay=(i%4)*70+'ms';io.observe(el)});
  function count(el){var t=+el.dataset.count,p=el.dataset.prefix||'',s=performance.now(),d=1400;
    (function f(n){var k=Math.min((n-s)/d,1),v=Math.floor(t*(1-Math.pow(1-k,3)));el.textContent=p+v.toLocaleString();if(k<1)requestAnimationFrame(f)})(s)}
  var co=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){count(e.target);co.unobserve(e.target)}})},{threshold:.4});
  document.querySelectorAll('[data-count]').forEach(function(el){co.observe(el)});
})();
</script>
</body></html>
