<?php
// =========================================
// CHARITY HUB - PROJECT MANAGEMENT
// =========================================

require_once "db.php";

$message = "";
$messageType = "";

// =========================================
// 1. REGISTER A NEW PROJECT
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $projectName = trim($_POST["project_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $budget = $_POST["budget"] ?? "";
    $startDate = $_POST["start_date"] ?? "";
    $endDate = $_POST["end_date"] ?? "";
    $status = $_POST["status"] ?? "Planned";

    $allowedStatuses = ["Planned", "Ongoing", "Completed"];

    if (
        $projectName === "" ||
        $budget === "" ||
        $startDate === "" ||
        $endDate === ""
    ) {
        $message = "Please fill in all required fields.";
        $messageType = "error";

    } elseif (!is_numeric($budget) || (float)$budget < 0) {
        $message = "Please enter a valid project budget.";
        $messageType = "error";

    } elseif (
        !in_array($status, $allowedStatuses, true)
    ) {
        $message = "Please select a valid project status.";
        $messageType = "error";

    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ||
        !checkdate(
            (int)substr($startDate, 5, 2),
            (int)substr($startDate, 8, 2),
            (int)substr($startDate, 0, 4)
        ) ||
        !checkdate(
            (int)substr($endDate, 5, 2),
            (int)substr($endDate, 8, 2),
            (int)substr($endDate, 0, 4)
        )
    ) {
        $message = "Please enter valid project dates.";
        $messageType = "error";

    } elseif ($endDate < $startDate) {
        $message = "The end date cannot be earlier than the start date.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO projects
            (project_name, description, budget, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            die("Unable to prepare project registration: " . $conn->error);
        }

        $budgetValue = (float)$budget;

        $stmt->bind_param(
            "ssdsss",
            $projectName,
            $description,
            $budgetValue,
            $startDate,
            $endDate,
            $status
        );

        if ($stmt->execute()) {
            header("Location: projects.php?success=1");
            exit;
        } else {
            $message = "Unable to save project: " . $stmt->error;
            $messageType = "error";
        }

        $stmt->close();
    }
}


// =========================================
// 2. DISPLAY SUCCESS MESSAGE
// =========================================

if (isset($_GET["success"])) {
    $message = "Project successfully registered!";
    $messageType = "success";
}


// =========================================
// 3. GET PROJECT STATISTICS
// =========================================

$totalProjectsResult = $conn->query(
    "SELECT COUNT(*) AS total FROM projects"
);

if (!$totalProjectsResult) {
    die("Unable to count projects: " . $conn->error);
}

$totalProjects = (int)$totalProjectsResult
    ->fetch_assoc()["total"];


$ongoingResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM projects
     WHERE status = 'Ongoing'"
);

if (!$ongoingResult) {
    die("Unable to count ongoing projects: " . $conn->error);
}

$ongoingProjects = (int)$ongoingResult
    ->fetch_assoc()["total"];


$completedResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM projects
     WHERE status = 'Completed'"
);

if (!$completedResult) {
    die("Unable to count completed projects: " . $conn->error);
}

$completedProjects = (int)$completedResult
    ->fetch_assoc()["total"];


// =========================================
// 4. GET ALL PROJECTS
// =========================================

$projectsResult = $conn->query(
    "SELECT * FROM projects ORDER BY id DESC"
);

if (!$projectsResult) {
    die("Unable to load projects: " . $conn->error);
}

$projects = [];

while ($row = $projectsResult->fetch_assoc()) {
    $projects[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CHARITY HUB - Projects</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7f5;
            color: #26352c;
        }

        .page-container {
            max-width: 1250px;
            margin: auto;
            padding: 30px;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .top-section h1 {
            color: #176b45;
            margin-bottom: 8px;
        }

        .top-section p {
            color: #777;
        }

        .back-link {
            background: #176b45;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .statistics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-box p {
            color: #777;
            margin-bottom: 12px;
        }

        .stat-box h2 {
            color: #176b45;
            font-size: 28px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 22px;
            align-items: start;
        }

        .panel {
            background: white;
            padding: 23px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .panel h2 {
            color: #176b45;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 85px;
        }

        .submit-button {
            width: 100%;
            background: #176b45;
            color: white;
            padding: 13px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .submit-button:hover {
            background: #105536;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .success {
            background: #e3f5e9;
            color: #176b45;
        }

        .error {
            background: #ffe8e8;
            color: #b42318;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f3f8f4;
            color: #176b45;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .planned {
            background: #fff3cd;
            color: #856404;
        }

        .ongoing {
            background: #d9f2e3;
            color: #176b45;
        }

        .completed {
            background: #dce8ff;
            color: #2454a6;
        }

        .empty-message {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        @media(max-width: 900px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 600px) {
            .page-container {
                padding: 15px;
            }

            .statistics {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<div class="page-container">

    <!-- PAGE HEADER -->

    <div class="top-section">

        <div>
            <h1>Projects Management</h1>

            <p>
                Register and monitor charity projects.
            </p>
        </div>

        <a href="index.php" class="back-link">
            Back to Dashboard
        </a>

    </div>


    <!-- SUCCESS OR ERROR MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </div>

    <?php endif; ?>


    <!-- PROJECT STATISTICS -->

    <div class="statistics">

        <div class="stat-box">
            <p>Total Projects</p>
            <h2><?php echo $totalProjects; ?></h2>
        </div>

        <div class="stat-box">
            <p>Ongoing Projects</p>
            <h2><?php echo $ongoingProjects; ?></h2>
        </div>

        <div class="stat-box">
            <p>Completed Projects</p>
            <h2><?php echo $completedProjects; ?></h2>
        </div>

    </div>


    <!-- FORM AND PROJECT TABLE -->

    <div class="content-grid">


        <!-- ADD PROJECT FORM -->

        <div class="panel">

            <h2>Add New Project</h2>

            <form method="POST" action="projects.php">

                <div class="form-group">

                    <label>Project Name *</label>

                    <input
                        type="text"
                        name="project_name"
                        placeholder="Enter project name"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Description</label>

                    <textarea
                        name="description"
                        placeholder="Describe the charity project"
                    ></textarea>

                </div>


                <div class="form-group">

                    <label>Project Budget (KSh) *</label>

                    <input
                        type="number"
                        name="budget"
                        min="0"
                        step="0.01"
                        placeholder="Enter project budget"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Start Date *</label>

                    <input
                        type="date"
                        name="start_date"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>End Date *</label>

                    <input
                        type="date"
                        name="end_date"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Project Status *</label>

                    <select name="status" required>

                        <option value="Planned">Planned</option>

                        <option value="Ongoing">Ongoing</option>

                        <option value="Completed">Completed</option>

                    </select>

                </div>


                <button type="submit" class="submit-button">
                    Register Project
                </button>

            </form>

        </div>


        <!-- PROJECTS TABLE -->

        <div class="panel">

            <h2>Registered Projects</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Project Name</th>
                            <th>Budget</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($projects) > 0): ?>

                            <?php foreach ($projects as $project): ?>

                                <?php
                                $statusClass = strtolower($project["status"]);
                                ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $project["project_name"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        </strong>
                                    </td>

                                    <td>
                                        KSh
                                        <?php
                                        echo number_format(
                                            (float)$project["budget"],
                                            2
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($project["start_date"])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($project["end_date"])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <span class="status <?php echo htmlspecialchars($statusClass, ENT_QUOTES, "UTF-8"); ?>">
                                            <?php
                                            echo htmlspecialchars(
                                                $project["status"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="empty-message">
                                    No projects registered yet.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>