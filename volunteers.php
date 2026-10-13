
<?php
// ============================================
// CHARITY HUB - VOLUNTEERS MODULE
// ============================================

// Connect to the database
require_once __DIR__ . "/db.php";

$message = "";
$messageType = "";

// ============================================
// 1. REGISTER A NEW VOLUNTEER
// ============================================

if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["register_volunteer"])) {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $interest = trim($_POST["interest"] ?? "");
    $availability = trim($_POST["availability"] ?? "");

    if (
        $full_name === "" ||
        $email === "" ||
        $phone === ""
    ) {
        header("Location: volunteers.php?status=empty");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: volunteers.php?status=invalid_email");
        exit;
    }

    $sql = "INSERT INTO volunteers
            (full_name, email, phone, interest, availability)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "sssss",
        $full_name,
        $email,
        $phone,
        $interest,
        $availability
    );

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: volunteers.php?status=success");
        exit;
    } else {
        $stmt->close();
        die("Unable to register volunteer: " . $conn->error);
    }
}

// ============================================
// 2. DELETE A VOLUNTEER
// ============================================

if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_volunteer"])) {

    $id = filter_var(
        $_POST["volunteer_id"] ?? "",
        FILTER_VALIDATE_INT
    );

    if ($id !== false && $id !== null && $id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM volunteers WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: volunteers.php?status=deleted");
        exit;
    }
}

// ============================================
// 3. DISPLAY SUCCESS OR ERROR MESSAGES
// ============================================

$status = $_GET["status"] ?? "";

if ($status === "success") {
    $message = "Volunteer registered successfully!";
    $messageType = "success";
} elseif ($status === "deleted") {
    $message = "Volunteer record deleted successfully!";
    $messageType = "success";
} elseif ($status === "empty") {
    $message = "Please fill in all required fields.";
    $messageType = "error";
} elseif ($status === "invalid_email") {
    $message = "Please enter a valid email address.";
    $messageType = "error";
}

// ============================================
// 4. GET TOTAL NUMBER OF VOLUNTEERS
// ============================================

$totalResult = $conn->query(
    "SELECT COUNT(*) AS total FROM volunteers"
);

$totalVolunteers = $totalResult->fetch_assoc()["total"];

// ============================================
// 5. GET VOLUNTEER RECORDS
// ============================================

$result = $conn->query(
    "SELECT * FROM volunteers ORDER BY date_registered DESC"
);

if (!$result) {
    die("Unable to retrieve volunteers: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteers | CHARITY HUB</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #173b35;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 25px 15px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 35px;
            font-size: 23px;
        }

        .sidebar a {
            display: block;
            padding: 13px 15px;
            margin-bottom: 8px;
            text-decoration: none;
            color: white;
            border-radius: 6px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2e7565;
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .topbar h1 {
            color: #173b35;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card h3 {
            color: #777;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .stat-card p {
            color: #173b35;
            font-size: 30px;
            font-weight: bold;
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .content-card h2 {
            color: #173b35;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2e7565;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-primary {
            background: #26745f;
            color: white;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background: #1c5949;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 12px;
        }

        .search-box {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #173b35;
            color: white;
        }

        tr:hover {
            background: #f8faf9;
        }

        .alert {
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-message {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        @media (max-width: 800px) {
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

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h2>CHARITY HUB</h2>

    <a href="index.php">Dashboard</a>
    <a href="donors.php">Donors</a>
    <a href="donations.php">Donations</a>
    <a href="volunteers.php" class="active">Volunteers</a>

</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="topbar">
        <h1>Volunteers Management</h1>
        <p>Register and manage charity volunteers.</p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- VOLUNTEER STATISTICS -->
    <div class="stats">

        <div class="stat-card">
            <h3>Total Registered Volunteers</h3>
            <p><?php echo (int)$totalVolunteers; ?></p>
        </div>

    </div>

    <!-- REGISTRATION FORM -->
    <div class="content-card">

        <h2>Register New Volunteer</h2>

        <form method="POST" action="volunteers.php">

            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter full name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="Enter email address"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input
                        type="tel"
                        name="phone"
                        placeholder="Enter phone number"
                        maxlength="20"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Area of Interest</label>
                    <select name="interest">
                        <option value="">Select area of interest</option>
                        <option value="Community Outreach">Community Outreach</option>
                        <option value="Fundraising">Fundraising</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Education">Education</option>
                        <option value="Food Distribution">Food Distribution</option>
                        <option value="Administration">Administration</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="">Select availability</option>
                        <option value="Weekdays">Weekdays</option>
                        <option value="Weekends">Weekends</option>
                        <option value="Both">Both weekdays and weekends</option>
                        <option value="Flexible">Flexible</option>
                    </select>
                </div>

            </div>

            <button
                type="submit"
                name="register_volunteer"
                class="btn btn-primary"
            >
                Register Volunteer
            </button>

        </form>

    </div>

    <!-- VOLUNTEER RECORDS -->
    <div class="content-card">

        <h2>Registered Volunteers</h2>

        <input
            type="text"
            id="searchInput"
            class="search-box"
            placeholder="Search volunteers by name, email or phone..."
        >

        <div class="table-container">

            <table id="volunteersTable">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Interest</th>
                        <th>Availability</th>
                        <th>Date Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo (int)$row["id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["full_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["email"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["phone"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["interest"] ?? ""); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["availability"] ?? ""); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["date_registered"]); ?>
                            </td>

                            <td>
                                <form method="POST"
                                      action="volunteers.php"
                                      onsubmit="return confirm('Are you sure you want to delete this volunteer?');">

                                    <input
                                        type="hidden"
                                        name="volunteer_id"
                                        value="<?php echo (int)$row["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_volunteer"
                                        class="btn btn-delete"
                                    >
                                        Delete
                                    </button>

                                </form>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="empty-message">
                            No volunteers registered yet.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>
// SEARCH VOLUNTEERS
document.getElementById("searchInput").addEventListener("keyup", function () {

    let searchValue = this.value.toLowerCase();

    let rows = document.querySelectorAll("#volunteersTable tbody tr");

    rows.forEach(function (row) {

        let rowText = row.textContent.toLowerCase();

        row.style.display = rowText.includes(searchValue) ? "" : "none";

    });

});
</script>

</body>
</html>
