
<?php
// ==========================================
// CHARITY HUB - MAIN DASHBOARD
// ==========================================

require_once "db.php";

// ==========================================
// 1. TOTAL DONATIONS
// ==========================================

$totalDonations = 0;

$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM donations"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalDonations = $row['total'];
}

// ==========================================
// 2. TOTAL DONORS
// ==========================================

$totalDonors = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM donors"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalDonors = $row['total'];
}

// ==========================================
// 3. TOTAL VOLUNTEERS
// ==========================================

$totalVolunteers = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM volunteers"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalVolunteers = $row['total'];
}

// ==========================================
// 4. RECENT DONATIONS
// ==========================================

$recentDonations = $conn->query(
    "SELECT * FROM donations
     ORDER BY id DESC
     LIMIT 5"
);

// ==========================================
// 5. RECENT VOLUNTEERS
// ==========================================

$recentVolunteers = $conn->query(
    "SELECT * FROM volunteers
     ORDER BY id DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>CHARITY HUB | Dashboard</title>

    <link rel="stylesheet" href="style.css">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #163b35;
            position: fixed;
            left: 0;
            top: 0;
            padding: 25px 15px;
            color: white;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 35px;
            color: #ffffff;
        }

        .sidebar a {
            display: block;
            padding: 14px;
            margin-bottom: 10px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2d7161;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #163b35;
        }

        .header p {
            color: #777;
            margin-top: 5px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .card h3 {
            color: #777;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .card h2 {
            font-size: 28px;
            color: #163b35;
        }

        .sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .table-container h2 {
            margin-bottom: 20px;
            color: #163b35;
            font-size: 19px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        th {
            background: #f4f6f9;
            color: #163b35;
        }

        .empty {
            color: #888;
            padding: 15px;
        }

        .view-link {
            display: inline-block;
            margin-top: 15px;
            color: #26745f;
            text-decoration: none;
            font-weight: bold;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        @media(max-width: 900px) {
            .cards {
                grid-template-columns: 1fr 1fr;
            }

            .sections {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 600px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .sidebar a {
                display: inline-block;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .header {
                display: block;
            }
        }

    </style>

</head>

<body>

<!-- ======================================
     SIDEBAR NAVIGATION
====================================== -->

<div class="sidebar">

    <h2>CHARITY HUB</h2>

    <a href="index.php" class="active">
        Dashboard
    </a>

    <a href="donors.php">
        Donors
    </a>

    <a href="donations.php">
        Donations
    </a>

    <a href="volunteers.php">
        Volunteers
    </a>

    <a href="campaigns.php">
        Campaigns
    </a>

    <a href="reports.php">
        Reports
    </a>

</div>


<!-- ======================================
     MAIN DASHBOARD
====================================== -->

<div class="main-content">

    <div class="header">

        <div>
            <h1>Dashboard</h1>

            <p>
                Welcome to CHARITY HUB Management System
            </p>
        </div>

        <div>
            <strong>
                Administrator
            </strong>
        </div>

    </div>


    <!-- ======================================
         DASHBOARD STATISTICS
    ====================================== -->

    <div class="cards">

        <div class="card">

            <h3>Total Donations</h3>

            <h2>
                KSh <?php echo number_format(
                    (float)$totalDonations, 2
                ); ?>
            </h2>

        </div>


        <div class="card">

            <h3>Registered Donors</h3>

            <h2>
                <?php echo $totalDonors; ?>
            </h2>

        </div>


        <div class="card">

            <h3>Registered Volunteers</h3>

            <h2>
                <?php echo $totalVolunteers; ?>
            </h2>

        </div>

    </div>


    <!-- ======================================
         RECENT RECORDS
    ====================================== -->

    <div class="sections">


        <!-- RECENT DONATIONS -->

        <div class="table-container">

            <h2>Recent Donations</h2>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($recentDonations &&
                          $recentDonations->num_rows > 0): ?>

                    <?php while (
                        $donation = $recentDonations->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars(
                                    $donation['id']
                                ); ?>
                            </td>

                            <td>
                                KSh <?php echo number_format(
                                    (float)$donation['amount'], 2
                                ); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="2" class="empty">
                            No donations recorded yet.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

            <a href="donations.php" class="view-link">
                View All Donations →
            </a>

        </div>


        <!-- RECENT VOLUNTEERS -->

        <div class="table-container">

            <h2>Recent Volunteers</h2>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($recentVolunteers &&
                          $recentVolunteers->num_rows > 0): ?>

                    <?php while (
                        $volunteer = $recentVolunteers->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars(
                                    $volunteer['id']
                                ); ?>
                            </td>

                            <td>
                                <?php
                                $name = $volunteer['full_name']
                                    ?? $volunteer['name']
                                    ?? 'Volunteer';

                                echo htmlspecialchars($name);
                                ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="2" class="empty">
                            No volunteers registered yet.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

            <a href="volunteers.php" class="view-link">
                View All Volunteers →
            </a>

        </div>

    </div>

</div>

</body>
</html>
