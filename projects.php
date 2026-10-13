
<?php
// ==================================================
// CHARITY HUB - PROJECTS MANAGEMENT MODULE
// ==================================================

require_once __DIR__ . "/db.php";

$message = "";
$messageType = "";
$editProject = null;

// ==================================================
// 1. REGISTER PROJECT
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["register_project"])
) {

    $project_name = trim($_POST["project_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $objectives = trim($_POST["objectives"] ?? "");
    $budget = $_POST["budget"] ?? "";
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $status = $_POST["status"] ?? "Planned";

    $allowedStatuses = [
        "Planned",
        "Ongoing",
        "Completed",
        "On Hold"
    ];

    if (
        $project_name === "" ||
        !is_numeric($budget) ||
        (float)$budget < 0 ||
        !in_array($status, $allowedStatuses, true)
    ) {

        header("Location: projects.php?status=invalid");
        exit;

    } elseif (
        ($start_date !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) ||
        ($end_date !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) ||
        ($start_date !== "" && $end_date !== "" && $end_date < $start_date)
    ) {

        header("Location: projects.php?status=invalid_date");
        exit;

    } else {

        $budget = (float)$budget;

        $stmt = $conn->prepare(
            "INSERT INTO projects
            (project_name, description, objectives, budget,
             start_date, end_date, status)
            VALUES (?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?)"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "sssdsss",
            $project_name,
            $description,
            $objectives,
            $budget,
            $start_date,
            $end_date,
            $status
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: projects.php?status=success");
            exit;
        }

        die("Unable to register project: " . $stmt->error);
    }
}

// ==================================================
// 2. UPDATE PROJECT
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_project"])
) {

    $id = filter_var(
        $_POST["project_id"] ?? "",
        FILTER_VALIDATE_INT
    );

    $project_name = trim($_POST["project_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $objectives = trim($_POST["objectives"] ?? "");
    $budget = $_POST["budget"] ?? "";
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $status = $_POST["status"] ?? "Planned";

    $allowedStatuses = [
        "Planned",
        "Ongoing",
        "Completed",
        "On Hold"
    ];

    if (
        !$id ||
        $id <= 0 ||
        $project_name === "" ||
        !is_numeric($budget) ||
        (float)$budget < 0 ||
        !in_array($status, $allowedStatuses, true)
    ) {

        header("Location: projects.php?status=invalid");
        exit;

    } elseif (
        ($start_date !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) ||
        ($end_date !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) ||
        ($start_date !== "" && $end_date !== "" && $end_date < $start_date)
    ) {

        header("Location: projects.php?status=invalid_date");
        exit;

    } else {

        $budget = (float)$budget;

        $stmt = $conn->prepare(
            "UPDATE projects
             SET project_name = ?,
                 description = ?,
                 objectives = ?,
                 budget = ?,
                 start_date = NULLIF(?, ''),
                 end_date = NULLIF(?, ''),
                 status = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "sssdsssi",
            $project_name,
            $description,
            $objectives,
            $budget,
            $start_date,
            $end_date,
            $status,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: projects.php?status=updated");
            exit;
        }

        die("Unable to update project: " . $stmt->error);
    }
}

// ==================================================
// 3. DELETE PROJECT
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_project"])
) {

    $id = filter_var(
        $_POST["project_id"] ?? "",
        FILTER_VALIDATE_INT
    );

    if ($id !== false && $id !== null && $id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM projects WHERE id = ?"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: projects.php?status=deleted");
            exit;
        }

        die("Unable to delete project: " . $stmt->error);
    }
}

// ==================================================
// 4. STATUS MESSAGES
// ==================================================

$statusMessage = $_GET["status"] ?? "";

if ($statusMessage === "success") {
    $message = "Project registered successfully!";
    $messageType = "success";

} elseif ($statusMessage === "updated") {
    $message = "Project updated successfully!";
    $messageType = "success";

} elseif ($statusMessage === "deleted") {
    $message = "Project deleted successfully!";
    $messageType = "success";

} elseif ($statusMessage === "invalid") {
    $message = "Please enter a valid project name, budget and status.";
    $messageType = "error";

} elseif ($statusMessage === "invalid_date") {
    $message = "Please enter valid dates. The end date cannot be before the start date.";
    $messageType = "error";
}

// ==================================================
// 5. FETCH PROJECT FOR EDITING
// ==================================================

if (isset($_GET["edit"])) {

    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT
    );

    if ($editId !== false && $editId !== null && $editId > 0) {

        $stmt = $conn->prepare(
            "SELECT * FROM projects WHERE id = ?"
        );

        $stmt->bind_param("i", $editId);
        $stmt->execute();

        $editProject = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$editProject) {
            $message = "Project not found.";
            $messageType = "error";
        }
    }
}

// ==================================================
// 6. PROJECT STATISTICS
// ==================================================

$statistics = $conn->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'Ongoing'), 0) AS ongoing,
        COALESCE(SUM(status = 'Completed'), 0) AS completed,
        COALESCE(SUM(status = 'Planned'), 0) AS planned
     FROM projects"
);

if (!$statistics) {
    die("Unable to retrieve project statistics: " . $conn->error);
}

$stats = $statistics->fetch_assoc();

// ==================================================
// 7. FETCH PROJECT RECORDS
// ==================================================

$result = $conn->query(
    "SELECT * FROM projects ORDER BY created_at DESC"
);

if (!$result) {
    die("Unable to retrieve projects: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Projects | CHARITY HUB</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    background: #f4f6f9;
    color: #333;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    width: 230px;
    height: 100vh;
    background: #173b35;
    padding: 25px 15px;
    color: white;
    overflow-y: auto;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    padding: 13px;
    margin-bottom: 8px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #2e7565;
}

/* MAIN CONTENT */

.main {
    margin-left: 230px;
    padding: 30px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 9px;
    margin-bottom: 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

h1, h2 {
    color: #173b35;
}

/* STATISTICS */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.stat-card {
    background: white;
    padding: 22px;
    border-radius: 9px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

.stat-card h3 {
    color: #777;
    font-size: 14px;
}

.stat-card p {
    font-size: 28px;
    color: #26745f;
    font-weight: bold;
    margin-bottom: 0;
}

/* FORM */

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 12px;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #26745f;
}

/* BUTTONS */

button, .btn {
    display: inline-block;
    padding: 11px 17px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.primary {
    background: #26745f;
    color: white;
}

.edit {
    background: #e5a000;
    color: white;
}

.delete {
    background: #dc3545;
    color: white;
}

.cancel {
    background: #777;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-buttons form {
    margin: 0;
}

/* TABLE */

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #173b35;
    color: white;
}

/* ALERTS */

.alert {
    padding: 14px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

/* SEARCH */

.search-box {
    margin-bottom: 18px;
}

/* RESPONSIVE */

@media(max-width: 1000px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 800px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .sidebar a {
        display: inline-block;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
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
    <a href="volunteers.php">Volunteers</a>
    <a href="beneficiaries.php">Beneficiaries</a>
    <a href="projects.php" class="active">Projects</a>

</div>

<!-- MAIN CONTENT -->

<div class="main">

    <div class="card">

        <h1>Projects and Programs Management</h1>

        <p>Register, monitor and manage charity projects.</p>

    </div>

    <!-- MESSAGES -->

    <?php if ($message !== ""): ?>

        <div class="alert <?php echo htmlspecialchars($messageType); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>

    <!-- PROJECT STATISTICS -->

    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total Projects</h3>
            <p><?php echo (int)$stats["total"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Ongoing Projects</h3>
            <p><?php echo (int)$stats["ongoing"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Completed Projects</h3>
            <p><?php echo (int)$stats["completed"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Planned Projects</h3>
            <p><?php echo (int)$stats["planned"]; ?></p>
        </div>

    </div>

    <!-- PROJECT REGISTRATION / EDIT FORM -->

    <div class="card">

        <h2>
            <?php echo $editProject ? "Edit Project" : "Register New Project"; ?>
        </h2>

        <form method="POST" action="projects.php">

            <?php if ($editProject): ?>

                <input
                    type="hidden"
                    name="project_id"
                    value="<?php echo (int)$editProject["id"]; ?>"
                >

            <?php endif; ?>

            <div class="form-grid">

                <div>
                    <label>Project Name *</label>

                    <input
                        type="text"
                        name="project_name"
                        maxlength="150"
                        placeholder="Enter project name"
                        value="<?php echo htmlspecialchars($editProject["project_name"] ?? ""); ?>"
                        required
                    >
                </div>

                <div>
                    <label>Project Budget *</label>

                    <input
                        type="number"
                        name="budget"
                        min="0"
                        step="0.01"
                        placeholder="Enter budget"
                        value="<?php echo htmlspecialchars((string)($editProject["budget"] ?? "0.00")); ?>"
                        required
                    >
                </div>

                <div>
                    <label>Start Date</label>

                    <input
                        type="date"
                        name="start_date"
                        value="<?php echo htmlspecialchars($editProject["start_date"] ?? ""); ?>"
                    >
                </div>

                <div>
                    <label>End Date</label>

                    <input
                        type="date"
                        name="end_date"
                        value="<?php echo htmlspecialchars($editProject["end_date"] ?? ""); ?>"
                    >
                </div>

                <div>
                    <label>Project Status</label>

                    <select name="status">

                        <?php
                        $statuses = [
                            "Planned",
                            "Ongoing",
                            "Completed",
                            "On Hold"
                        ];
                        ?>

                        <?php foreach ($statuses as $option): ?>

                            <option
                                value="<?php echo htmlspecialchars($option); ?>"
                                <?php echo (($editProject["status"] ?? "Planned") === $option) ? "selected" : ""; ?>
                            >

                                <?php echo htmlspecialchars($option); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div>
                    <label>Project Description</label>

                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Describe the project"
                    ><?php echo htmlspecialchars($editProject["description"] ?? ""); ?></textarea>
                </div>

            </div>

            <div>
                <label>Project Objectives</label>

                <textarea
                    name="objectives"
                    rows="3"
                    placeholder="Enter the objectives of the project"
                ><?php echo htmlspecialchars($editProject["objectives"] ?? ""); ?></textarea>
            </div>

            <?php if ($editProject): ?>

                <button
                    type="submit"
                    name="update_project"
                    class="primary"
                >
                    Update Project
                </button>

                <a href="projects.php" class="btn cancel">
                    Cancel
                </a>

            <?php else: ?>

                <button
                    type="submit"
                    name="register_project"
                    class="primary"
                >
                    Save Project
                </button>

            <?php endif; ?>

        </form>

    </div>

    <!-- PROJECT RECORDS -->

    <div class="card">

        <h2>Registered Projects</h2>

        <input
            type="text"
            id="search"
            class="search-box"
            placeholder="Search projects..."
        >

        <div class="table-wrap">

            <table id="projectsTable">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Budget</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
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
                                    <?php echo htmlspecialchars($row["project_name"]); ?>
                                </td>

                                <td>
                                    KSh <?php echo number_format((float)$row["budget"], 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["start_date"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["end_date"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["status"]); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="projects.php?edit=<?php echo (int)$row["id"]; ?>"
                                            class="btn edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="projects.php"
                                            onsubmit="return confirm('Are you sure you want to delete this project?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="project_id"
                                                value="<?php echo (int)$row["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_project"
                                                class="delete"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" style="text-align:center;">
                                No projects registered yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- SEARCH FUNCTION -->

<script>

document.getElementById("search").addEventListener("input", function () {

    const value = this.value.toLowerCase();

    document.querySelectorAll("#projectsTable tbody tr").forEach(row => {

        row.style.display =
            row.textContent.toLowerCase().includes(value) ? "" : "none";

    });

});

</script>

</body>
</html>
