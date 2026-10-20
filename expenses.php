
<?php
// =============================================
// CHARITY HUB - EXPENSES MANAGEMENT
// =============================================

require_once __DIR__ . "/db.php";

$message = "";
$messageType = "success";
$editExpense = null;

// ---------------------------------------------
// 1. DELETE EXPENSE
// ---------------------------------------------

if (isset($_GET["delete"])) {

    $id = filter_var($_GET["delete"], FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $conn->prepare(
            "DELETE FROM expenses WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: expenses.php?message=deleted");
            exit;
        }

        $message = "Unable to delete the expense.";
        $messageType = "error";
    }
}

// ---------------------------------------------
// 2. READ SUCCESS MESSAGES
// ---------------------------------------------

if (isset($_GET["message"])) {
    if ($_GET["message"] === "added") {
        $message = "Expense recorded successfully.";
    } elseif ($_GET["message"] === "updated") {
        $message = "Expense updated successfully.";
    } elseif ($_GET["message"] === "deleted") {
        $message = "Expense deleted successfully.";
    }
}

// ---------------------------------------------
// 3. ADD OR UPDATE EXPENSE
// ---------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    $id = isset($_POST["id"])
        ? (int) $_POST["id"]
        : 0;

    $expenseTitle = trim($_POST["expense_title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $amount = $_POST["amount"] ?? "";
    $expenseDate = trim($_POST["expense_date"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $projectInput = $_POST["project_id"] ?? "";
    $projectId = ($projectInput === "")
        ? null
        : filter_var($projectInput, FILTER_VALIDATE_INT);

    $allowedCategories = [
        "Food",
        "Transport",
        "Medical",
        "Education",
        "Office Supplies",
        "Events",
        "Administration",
        "Other"
    ];

    $validAmount = is_numeric($amount)
        && (float) $amount > 0;

    $validDate = DateTime::createFromFormat(
        "Y-m-d",
        $expenseDate
    );

    $validDate = $validDate
        && $validDate->format("Y-m-d") === $expenseDate;

    $validProject = (
        $projectId === null ||
        ($projectId !== false && $projectId > 0)
    );

    if (
        $expenseTitle === "" ||
        !in_array($category, $allowedCategories, true) ||
        !$validAmount ||
        !$validDate ||
        !$validProject
    ) {
        $message = "Please enter valid information in all required fields.";
        $messageType = "error";

    } else {

        $amount = (float) $amount;

        // Confirm that the selected project exists.
        if ($projectId !== null) {

            $checkProject = $conn->prepare(
                "SELECT id FROM projects WHERE id = ?"
            );

            $checkProject->bind_param("i", $projectId);
            $checkProject->execute();

            $projectResult = $checkProject->get_result();

            if ($projectResult->num_rows === 0) {
                $validProject = false;
            }

            $checkProject->close();
        }

        if (!$validProject) {

            $message = "The selected project does not exist.";
            $messageType = "error";

        } elseif ($action === "add") {

            $stmt = $conn->prepare(
                "INSERT INTO expenses
                (project_id, expense_title, category,
                 amount, expense_date, description)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "issdss",
                $projectId,
                $expenseTitle,
                $category,
                $amount,
                $expenseDate,
                $description
            );

            if ($stmt->execute()) {
                header("Location: expenses.php?message=added");
                exit;
            }

            $message = "Unable to record expense: " . $stmt->error;
            $messageType = "error";

            $stmt->close();

        } elseif ($action === "update" && $id > 0) {

            $stmt = $conn->prepare(
                "UPDATE expenses SET
                    project_id = ?,
                    expense_title = ?,
                    category = ?,
                    amount = ?,
                    expense_date = ?,
                    description = ?
                WHERE id = ?"
            );

            $stmt->bind_param(
                "issdssi",
                $projectId,
                $expenseTitle,
                $category,
                $amount,
                $expenseDate,
                $description,
                $id
            );

            if ($stmt->execute()) {
                header("Location: expenses.php?message=updated");
                exit;
            }

            $message = "Unable to update expense: " . $stmt->error;
            $messageType = "error";

            $stmt->close();
        }
    }
}

// ---------------------------------------------
// 4. GET EXPENSE TO EDIT
// ---------------------------------------------

if (isset($_GET["edit"])) {

    $editId = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($editId && $editId > 0) {

        $stmt = $conn->prepare(
            "SELECT * FROM expenses WHERE id = ?"
        );

        $stmt->bind_param("i", $editId);
        $stmt->execute();

        $editResult = $stmt->get_result();

        if ($editResult->num_rows > 0) {
            $editExpense = $editResult->fetch_assoc();
        }

        $stmt->close();
    }
}

// ---------------------------------------------
// 5. GET PROJECTS FOR DROPDOWN
// ---------------------------------------------

$projects = [];

$projectResult = $conn->query(
    "SELECT id, project_name FROM projects ORDER BY project_name ASC"
);

if ($projectResult) {
    while ($project = $projectResult->fetch_assoc()) {
        $projects[] = $project;
    }
}

// ---------------------------------------------
// 6. SEARCH EXPENSES
// ---------------------------------------------

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT expenses.*, projects.project_name
         FROM expenses
         LEFT JOIN projects
         ON expenses.project_id = projects.id
         WHERE expenses.expense_title LIKE ?
         OR expenses.category LIKE ?
         OR expenses.description LIKE ?
         OR projects.project_name LIKE ?
         ORDER BY expenses.expense_date DESC,
         expenses.id DESC"
    );

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();
    $expensesResult = $stmt->get_result();

} else {

    $expensesResult = $conn->query(
        "SELECT expenses.*, projects.project_name
         FROM expenses
         LEFT JOIN projects
         ON expenses.project_id = projects.id
         ORDER BY expenses.expense_date DESC,
         expenses.id DESC"
    );
}

// ---------------------------------------------
// 7. CALCULATE TOTAL EXPENSES
// ---------------------------------------------

$totalResult = $conn->query(
    "SELECT
        COUNT(*) AS total_records,
        COALESCE(SUM(amount), 0) AS total_amount
     FROM expenses"
);

$totalData = $totalResult->fetch_assoc();

$totalRecords = $totalData["total_records"];
$totalAmount = $totalData["total_amount"];

// ---------------------------------------------
// 8. HELPER FOR SAFE HTML OUTPUT
// ---------------------------------------------

function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ""),
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

    <title>Expenses Management | CHARITY HUB</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1250px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #173b57;
            margin: 0;
        }

        .back-link {
            text-decoration: none;
            background: #173b57;
            color: white;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(220px, 1fr)
            );
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .summary-card h3 {
            margin-top: 0;
            color: #666;
            font-size: 15px;
        }

        .summary-card p {
            margin-bottom: 0;
            font-size: 25px;
            font-weight: bold;
            color: #173b57;
        }

        .section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .section h2 {
            color: #173b57;
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(220px, 1fr)
            );
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 85px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 5px;
            padding: 11px 17px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-top: 15px;
        }

        .btn-primary {
            background: #173b57;
            color: white;
        }

        .btn-secondary {
            background: #777;
            color: white;
        }

        .btn-edit {
            background: #e6a23c;
            color: white;
            padding: 7px 10px;
        }

        .btn-delete {
            background: #c0392b;
            color: white;
            padding: 7px 10px;
        }

        .message {
            padding: 13px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success {
            background: #dff0d8;
            color: #27632a;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .search-form input {
            flex: 1;
            min-width: 200px;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #173b57;
            color: white;
        }

        tr:hover {
            background: #f7f9fb;
        }

        .actions {
            white-space: nowrap;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .section {
                padding: 16px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Expenses Management</h1>

        <a href="index.php" class="back-link">
            Back to Dashboard
        </a>

    </div>

    <?php if ($message !== ""): ?>

        <div class="message <?php echo e($messageType); ?>">
            <?php echo e($message); ?>
        </div>

    <?php endif; ?>

    <!-- SUMMARY -->

    <div class="summary">

        <div class="summary-card">

            <h3>Total Expenses Recorded</h3>

            <p><?php echo number_format($totalRecords); ?></p>

        </div>

        <div class="summary-card">

            <h3>Total Expenditure</h3>

            <p>
                KSh <?php echo number_format(
                    (float) $totalAmount,
                    2
                ); ?>
            </p>

        </div>

    </div>

    <!-- ADD OR EDIT FORM -->

    <div class="section">

        <h2>
            <?php echo $editExpense
                ? "Edit Expense"
                : "Record New Expense"; ?>
        </h2>

        <form method="POST" action="expenses.php">

            <input
                type="hidden"
                name="action"
                value="<?php echo $editExpense ? "update" : "add"; ?>"
            >

            <?php if ($editExpense): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo e($editExpense["id"]); ?>"
                >

            <?php endif; ?>

            <div class="form-grid">

                <div class="form-group">

                    <label>Expense Title *</label>

                    <input
                        type="text"
                        name="expense_title"
                        placeholder="Enter expense title"
                        maxlength="150"
                        required
                        value="<?php echo e(
                            $editExpense["expense_title"] ?? ""
                        ); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>Expense Category *</label>

                    <select name="category" required>

                        <option value="">Select Category</option>

                        <?php
                        $categories = [
                            "Food",
                            "Transport",
                            "Medical",
                            "Education",
                            "Office Supplies",
                            "Events",
                            "Administration",
                            "Other"
                        ];
                        ?>

                        <?php foreach ($categories as $categoryOption): ?>

                            <option
                                value="<?php echo e($categoryOption); ?>"
                                <?php
                                if (
                                    ($editExpense["category"] ?? "")
                                    === $categoryOption
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo e($categoryOption); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>Amount (KSh) *</label>

                    <input
                        type="number"
                        name="amount"
                        min="0.01"
                        step="0.01"
                        placeholder="Enter amount"
                        required
                        value="<?php echo e(
                            $editExpense["amount"] ?? ""
                        ); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>Expense Date *</label>

                    <input
                        type="date"
                        name="expense_date"
                        required
                        value="<?php echo e(
                            $editExpense["expense_date"] ?? ""
                        ); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>Related Project</label>

                    <select name="project_id">

                        <option value="">
                            No Project Selected
                        </option>

                        <?php foreach ($projects as $project): ?>

                            <option
                                value="<?php echo e($project["id"]); ?>"
                                <?php
                                if (
                                    (string) (
                                        $editExpense["project_id"] ?? ""
                                    ) === (string) $project["id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo e($project["project_name"]); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group full-width">

                    <label>Description</label>

                    <textarea
                        name="description"
                        placeholder="Enter additional expense details"
                    ><?php echo e(
                        $editExpense["description"] ?? ""
                    ); ?></textarea>

                </div>

            </div>

            <button type="submit" class="btn btn-primary">

                <?php echo $editExpense
                    ? "Update Expense"
                    : "Save Expense"; ?>

            </button>

            <?php if ($editExpense): ?>

                <a href="expenses.php" class="btn btn-secondary">
                    Cancel Editing
                </a>

            <?php endif; ?>

        </form>

    </div>

    <!-- EXPENSES TABLE -->

    <div class="section">

        <h2>Recorded Expenses</h2>

        <form method="GET" class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search by title, category or project..."
                value="<?php echo e($search); ?>"
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <a href="expenses.php" class="btn btn-secondary">
                Clear
            </a>

        </form>

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Expense Title</th>
                        <th>Category</th>
                        <th>Project</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (
                    $expensesResult &&
                    $expensesResult->num_rows > 0
                ): ?>

                    <?php while (
                        $expense = $expensesResult->fetch_assoc()
                    ): ?>

                        <tr>

                            <td><?php echo e($expense["id"]); ?></td>

                            <td>
                                <?php echo e($expense["expense_title"]); ?>
                            </td>

                            <td>
                                <?php echo e($expense["category"]); ?>
                            </td>

                            <td>
                                <?php echo e(
                                    $expense["project_name"]
                                    ?? "Not linked"
                                ); ?>
                            </td>

                            <td>
                                KSh <?php echo number_format(
                                    (float) $expense["amount"],
                                    2
                                ); ?>
                            </td>

                            <td>
                                <?php echo e($expense["expense_date"]); ?>
                            </td>

                            <td>
                                <?php echo e($expense["description"]); ?>
                            </td>

                            <td class="actions">

                                <a
                                    href="expenses.php?edit=<?php
                                        echo e($expense["id"]);
                                    ?>"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="expenses.php?delete=<?php
                                        echo e($expense["id"]);
                                    ?>"
                                    class="btn btn-delete"
                                    onclick="return confirm(
                                        'Are you sure you want to delete this expense?'
                                    );"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="8">
                            No expenses have been recorded yet.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>
