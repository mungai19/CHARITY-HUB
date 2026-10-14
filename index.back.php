<?php
// =============================================
// CHARITY HUB - MAIN DASHBOARD
// =============================================

require_once "db.php";


// =============================================
// 1. TOTAL DONATION AMOUNT
// =============================================

$totalResult = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM donations"
);

if (!$totalResult) {
    die("Unable to load donation total: " . $conn->error);
}

$totalRow = $totalResult->fetch_assoc();
$totalAmount = (float)$totalRow["total"];


// =============================================
// 2. TOTAL NUMBER OF DONATIONS
// =============================================

$countResult = $conn->query(
    "SELECT COUNT(*) AS total_count
     FROM donations"
);

if (!$countResult) {
    die("Unable to count donations: " . $conn->error);
}

$totalDonationsCount = (int)$countResult
    ->fetch_assoc()["total_count"];


// =============================================
// 3. TOTAL REGISTERED DONORS
// =============================================

$donorCountResult = $conn->query(
    "SELECT COUNT(*) AS total_donors
     FROM donors"
);

if (!$donorCountResult) {
    die("Unable to count donors: " . $conn->error);
}

$totalDonors = (int)$donorCountResult
    ->fetch_assoc()["total_donors"];


// =============================================
// 4. TOTAL REGISTERED BENEFICIARIES
// =============================================

$beneficiaryCountResult = $conn->query(
    "SELECT COUNT(*) AS total_beneficiaries
     FROM beneficiaries"
);

if (!$beneficiaryCountResult) {
    die("Unable to count beneficiaries: " . $conn->error);
}

$totalBeneficiaries = (int)$beneficiaryCountResult
    ->fetch_assoc()["total_beneficiaries"];


// =============================================
// 5. TOTAL ONGOING PROJECTS
// =============================================

$projectCountResult = $conn->query(
    "SELECT COUNT(*) AS total_projects
     FROM projects
     WHERE status = 'Ongoing'"
);

if (!$projectCountResult) {
    die("Unable to count projects: " . $conn->error);
}

$totalActiveProjects = (int)$projectCountResult
    ->fetch_assoc()["total_projects"];


// =============================================
// 6. FOUR MOST RECENT DONATIONS
// =============================================

$donationResult = $conn->query(
    "SELECT donor_name, amount, donation_date, status
     FROM donations
     ORDER BY id DESC
     LIMIT 4"
);

if (!$donationResult) {
    die("Unable to load recent donations: " . $conn->error);
}

$donations = [];

while ($row = $donationResult->fetch_assoc()) {
    $donations[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CHARITY HUB | Dashboard</title>

    <!-- DASHBOARD CSS -->
    <link rel="stylesheet" href="css/dashboard.css">

</head>

<body>

<div class="dashboard-container">


    <!-- =====================================
         SIDEBAR
    ====================================== -->

    <aside class="sidebar">

        <div class="logo">

            <span class="logo-icon">❤</span>

            <span>CHARITY HUB</span>

        </div>


        <p class="menu-heading">MAIN MENU</p>


        <nav class="navigation">

            <a href="index.php" class="nav-link active">
                <span>⌂</span>
                Dashboard
            </a>

            <a href="donations.php" class="nav-link">
                <span>♥</span>
                Donations
            </a>

            <a href="donors.php" class="nav-link">
                <span>♙</span>
                Donors
            </a>

            <a href="beneficiaries.php" class="nav-link">
                <span>♧</span>
                Beneficiaries
            </a>

            <a href="projects.php" class="nav-link">
                <span>▣</span>
                Projects
            </a>

            <a href="volunteers.php" class="nav-link">
                <span>♧</span>
                Volunteers
            </a>

            <a href="reports.php" class="nav-link">
                <span>▤</span>
                Reports
            </a>

            <a href="settings.php" class="nav-link">
                <span>⚙</span>
                Settings
            </a>

            <a href="users.php" class="nav-link">
                <span>♙</span>
                User Management
            </a>

        </nav>


        <div class="sidebar-footer">

            <p>CHARITY HUB</p>

            <small>Making a difference together.</small>

        </div>

    </aside>



    <!-- =====================================
         MAIN CONTENT
    ====================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <header class="topbar">

            <div>

                <h2>CHARITY HUB</h2>

                <p class="welcome-text">
                    Charity Management System
                </p>

            </div>


            <div class="admin-profile">

                <div class="profile-icon">
                    A
                </div>

                <div>

                    <strong>Administrator</strong>

                    <small>System Admin</small>

                </div>

            </div>

        </header>



        <!-- =====================================
             DASHBOARD CONTENT
        ====================================== -->

        <section class="content">


            <!-- PAGE HEADING -->

            <div class="page-heading">

                <div>

                    <h1>Dashboard Overview</h1>

                    <p>
                        Welcome back! Here is what is happening
                        in your charity organization.
                    </p>

                </div>


                <div class="today-date">

                    <?php echo date("d M Y"); ?>

                </div>

            </div>



            <!-- =====================================
                 STATISTICS CARDS
            ====================================== -->

            <div class="stats-grid">


                <!-- TOTAL DONATIONS -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Donations
                        </span>

                        <span class="stat-icon">
                            ♥
                        </span>

                    </div>

                    <h2>
                        KSh <?php
                        echo number_format($totalAmount, 2);
                        ?>
                    </h2>

                    <p>
                        <?php echo number_format($totalDonationsCount); ?>
                        donation records
                    </p>

                </div>



                <!-- TOTAL DONORS -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Donors
                        </span>

                        <span class="stat-icon">
                            ♙
                        </span>

                    </div>

                    <h2>
                        <?php echo number_format($totalDonors); ?>
                    </h2>

                    <p>
                        Registered donors
                    </p>

                </div>



                <!-- TOTAL BENEFICIARIES -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Beneficiaries
                        </span>

                        <span class="stat-icon">
                            ♧
                        </span>

                    </div>

                    <h2>
                        <?php echo number_format($totalBeneficiaries); ?>
                    </h2>

                    <p>
                        Registered beneficiaries
                    </p>

                </div>



                <!-- ACTIVE PROJECTS -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Active Projects
                        </span>

                        <span class="stat-icon">
                            ▣
                        </span>

                    </div>

                    <h2>
                        <?php echo number_format($totalActiveProjects); ?>
                    </h2>

                    <p>
                        Ongoing charity projects
                    </p>

                </div>


            </div>



            <!-- =====================================
                 DASHBOARD LOWER SECTION
            ====================================== -->

            <div class="dashboard-grid">


                <!-- RECENT DONATIONS TABLE -->

                <section class="panel">


                    <div class="panel-heading">

                        <div>

                            <h3>Recent Donations</h3>

                            <p>
                                Overview of the latest donations
                            </p>

                        </div>


                        <a href="donations.php">

                            <button
                                class="view-button"
                                type="button"
                            >
                                View All
                            </button>

                        </a>

                    </div>



                    <div class="table-container">

                        <table>

                            <thead>

                                <tr>

                                    <th>Donor Name</th>

                                    <th>Amount</th>

                                    <th>Date</th>

                                    <th>Status</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php if (count($donations) > 0): ?>

                                    <?php foreach ($donations as $donation): ?>

                                        <tr>

                                            <td>

                                                <?php

                                                echo htmlspecialchars(
                                                    $donation["donor_name"] ?? "",
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </td>


                                            <td>

                                                KSh

                                                <?php

                                                echo number_format(
                                                    (float)$donation["amount"],
                                                    2
                                                );

                                                ?>

                                            </td>


                                            <td>

                                                <?php

                                                $date = $donation["donation_date"] ?? "";

                                                $timestamp = strtotime($date);

                                                echo $timestamp
                                                    ? date("d M Y", $timestamp)
                                                    : "N/A";

                                                ?>

                                            </td>


                                            <td>

                                                <?php

                                                echo htmlspecialchars(
                                                    $donation["status"] ?? "Pending",
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>


                                <?php else: ?>

                                    <tr>

                                        <td colspan="4">

                                            No donation records available yet.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>


                </section>



                <!-- RECENT ACTIVITIES -->

                <section class="panel">


                    <div class="panel-heading">

                        <div>

                            <h3>Recent Activities</h3>

                            <p>
                                Latest system activities
                            </p>

                        </div>

                    </div>



                    <div class="activity-list">


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                CHARITY HUB dashboard is ready.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Donation management is available.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Donor management is available.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Beneficiary management is available.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Project management is available.
                            </p>

                        </div>


                    </div>


                </section>


            </div>



            <!-- =====================================
                 FOOTER
            ====================================== -->

            <footer class="footer">

                <p>

                    &copy; <?php echo date("Y"); ?>

                    CHARITY HUB. All Rights Reserved.

                </p>

            </footer>


        </section>

    </main>

</div>

</body>

</html>