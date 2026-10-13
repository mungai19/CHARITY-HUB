
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo">CHARITY HUB</div>

    <a href="index.php"
       class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
       Dashboard
    </a>

    <a href="donations.php"
       class="<?= $currentPage == 'donations.php' ? 'active' : '' ?>">
       Donations
    </a>

    <a href="donors.php"
       class="<?= $currentPage == 'donors.php' ? 'active' : '' ?>">
       Donors
    </a>

    <a href="beneficiaries.php"
       class="<?= $currentPage == 'beneficiaries.php' ? 'active' : '' ?>">
       Beneficiaries
    </a>

    <a href="projects.php"
       class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>">
       Projects
    </a>

    <a href="volunteers.php"
       class="<?= $currentPage == 'volunteers.php' ? 'active' : '' ?>">
       Volunteers
    </a>

    <a href="reports.php"
       class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>">
       Reports
    </a>

    <a href="settings.php"
       class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">
       Settings
    </a>

    <a href="users.php"
       class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
       User Management
    </a>
</div>
