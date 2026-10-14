
<?php
// ==================================================
// CHARITY HUB - MAIN DASHBOARD
// ==================================================

require_once __DIR__ . "/db.php";

// ==================================================
// 1. TOTAL DONATIONS AND DONATION COUNT
// ==================================================

$totalResult = $conn->query(
    "SELECT
        COALESCE(SUM(amount), 0) AS total,
        COUNT(*) AS total_count
     FROM donations"
);

if (!$totalResult) {
    die("Unable to load donation information: " . $conn->error);
}

$donationData = $totalResult->fetch_assoc();

$totalAmount = (float)$donationData["total"];
$totalDonationsCount = (int)$donationData["total_count"];

// ==================================================
// 2. TOTAL REGISTERED DONORS
// ==================================================

$donorCountResult = $conn->query(
    "SELECT COUNT(*) AS total_donors FROM donors"
);

if (!$donorCountResult) {
    die("Unable to count donors: " . $conn->error);
}

$totalDonors = (int)$donorCountResult
    ->fetch_assoc()["total_donors"];

// ==================================================
// 3. TOTAL REGISTERED BENEFICIARIES
// ==================================================

$beneficiaryCountResult = $conn->query(
    "SELECT COUNT(*) AS total_beneficiaries
     FROM beneficiaries"
);

if (!$beneficiaryCountResult) {
    die("Unable to count beneficiaries: " . $conn->error);
}

$totalBeneficiaries = (int)$beneficiaryCountResult
    ->fetch_assoc()["total_beneficiaries"];

// ==================================================
// 4. TOTAL ONGOING PROJECTS
// ==================================================

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

// ==================================================
// 5. TOTAL REGISTERED VOLUNTEERS
// ==================================================

$volunteerCountResult = $conn->query(
    "SELECT COUNT(*) AS total_volunteers
     FROM volunteers"
);

if (!$volunteerCountResult) {
    die("Unable to count volunteers: " . $conn->error);
}

$totalVolunteers = (int)$volunteerCountResult
    ->fetch_assoc()["total_volunteers"];

// ==================================================
// 6. TOTAL EXPENSES AND EXPENSE COUNT
// ==================================================

$expenseResult = $conn->query(
    "SELECT
        COALESCE(SUM(amount), 0) AS total_expenses,
        COUNT(*) AS total_records
     FROM expenses"
);

if (!$expenseResult) {
    die("Unable to load expenses. Please confirm that the expenses table exists: " . $conn->error);
}

$expenseData = $expenseResult->fetch_assoc();

$totalExpenses = (float)$expenseData["total_expenses"];
$totalExpenseRecords = (int)$expenseData["total_records"];

// ==================================================
// 7. AVAILABLE BALANCE
// ==================================================

$availableBalance = $totalAmount - $totalExpenses;

// ==================================================
// 8. FOUR MOST RECENT DONATIONS
// ==================================================

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

// ==================================================
// 9. FOUR MOST RECENT EXPENSES
// ==================================================

$recentExpenseResult = $conn->query(
    "SELECT expense_title, category, amount, expense_date
     FROM expenses
     ORDER BY id DESC
     LIMIT 4"
);

if (!$recentExpenseResult) {
    die("Unable to load recent expenses: " . $conn->error);
}

$recentExpenses = [];

while ($row = $recentExpenseResult->fetch_assoc()) {
    $recentExpenses[] = $row;
}

// ==================================================
// 10. SAFE HTML OUTPUT FUNCTION
// ==================================================

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

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

            <!-- NEW EXPENSES MODULE -->

            <a href="expenses.php" class="nav-link">
                <span>▤</span>
                Expenses
            </a>

            <!-- REPORTS MODULE -->

            <a href="reports.php" class="nav-link">
                <span>▥</span>
                Reports and Analytics
            </a>

            <!-- SETTINGS -->

            <a href="settings.php" class="nav-link">
                <span>⚙</span>
                Settings
            </a>

            <!-- USER MANAGEMENT -->

            <a href="users.php" class="nav-link">
                <span>♙</span>
                User Management
            </a>

            <!-- LOGOUT -->

            <a href="logout.php" class="nav-link">
                <span>↪</span>
                Logout
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


                <!-- TOTAL VOLUNTEERS -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Volunteers
                        </span>

                        <span class="stat-icon">
                            ♧
                        </span>

                    </div>

                    <h2>
                        <?php echo number_format($totalVolunteers); ?>
                    </h2>

                    <p>
                        Registered volunteers
                    </p>

                </div>


                <!-- TOTAL EXPENSES -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Expenses
                        </span>

                        <span class="stat-icon">
                            ▤
                        </span>

                    </div>

                    <h2>
                        KSh <?php echo number_format($totalExpenses, 2); ?>
                    </h2>

                    <p>
                        <?php echo number_format($totalExpenseRecords); ?>
                        expense records
                    </p>

                </div>


                <!-- AVAILABLE BALANCE -->

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Available Balance
                        </span>

                        <span class="stat-icon">
                            $
                        </span>

                    </div>

                    <h2>
                        KSh <?php
                        echo number_format($availableBalance, 2);
                        ?>
                    </h2>

                    <p>
                        Donations minus recorded expenses
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
                                                <?php echo e($donation["donor_name"]); ?>
                                            </td>

                                            <td>
                                                KSh <?php
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
                                                <?php echo e($donation["status"] ?? "Pending"); ?>
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


                <!-- RECENT EXPENSES TABLE -->

                <section class="panel">

                    <div class="panel-heading">

                        <div>

                            <h3>Recent Expenses</h3>

                            <p>
                                Overview of the latest expenditure
                            </p>

                        </div>

                        <a href="expenses.php">

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

                                    <th>Expense</th>

                                    <th>Category</th>

                                    <th>Amount</th>

                                    <th>Date</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($recentExpenses) > 0): ?>

                                    <?php foreach ($recentExpenses as $expense): ?>

                                        <tr>

                                            <td>
                                                <?php echo e($expense["expense_title"]); ?>
                                            </td>

                                            <td>
                                                <?php echo e($expense["category"]); ?>
                                            </td>

                                            <td>
                                                KSh <?php
                                                echo number_format(
                                                    (float)$expense["amount"],
                                                    2
                                                );
                                                ?>
                                            </td>

                                            <td>

                                                <?php

                                                $expenseDate = $expense["expense_date"] ?? "";

                                                $expenseTimestamp = strtotime($expenseDate);

                                                echo $expenseTimestamp
                                                    ? date("d M Y", $expenseTimestamp)
                                                    : "N/A";

                                                ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4">
                                            No expense records available yet.
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
                                CHARITY HUB system modules
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


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Volunteer management is available.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Expenses management is available.
                            </p>

                        </div>


                        <div class="activity-item">

                            <span class="activity-dot"></span>

                            <p>
                                Reports and analytics are available.
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
