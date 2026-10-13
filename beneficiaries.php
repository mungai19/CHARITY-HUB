
<?php
// ==================================================
// CHARITY HUB - BENEFICIARIES MANAGEMENT MODULE
// ==================================================

require_once __DIR__ . "/db.php";

$message = "";
$messageType = "";
$editBeneficiary = null;

// ==================================================
// 1. REGISTER BENEFICIARY
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["register_beneficiary"])
) {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $assistance_type = trim($_POST["assistance_type"] ?? "");

    if ($full_name === "" || $phone === "") {

        header("Location: beneficiaries.php?status=empty");
        exit;

    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        header("Location: beneficiaries.php?status=invalid_email");
        exit;

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO beneficiaries
            (full_name, email, phone, address, gender, assistance_type)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssss",
            $full_name,
            $email,
            $phone,
            $address,
            $gender,
            $assistance_type
        );

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: beneficiaries.php?status=success");
            exit;

        } else {

            die("Unable to register beneficiary: " . $stmt->error);

        }
    }
}

// ==================================================
// 2. UPDATE BENEFICIARY
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_beneficiary"])
) {

    $id = filter_var(
        $_POST["beneficiary_id"] ?? "",
        FILTER_VALIDATE_INT
    );

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $assistance_type = trim($_POST["assistance_type"] ?? "");

    if (!$id || $id <= 0 || $full_name === "" || $phone === "") {

        header("Location: beneficiaries.php?status=empty");
        exit;

    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        header("Location: beneficiaries.php?status=invalid_email");
        exit;

    } else {

        $stmt = $conn->prepare(
            "UPDATE beneficiaries
             SET full_name = ?,
                 email = ?,
                 phone = ?,
                 address = ?,
                 gender = ?,
                 assistance_type = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssi",
            $full_name,
            $email,
            $phone,
            $address,
            $gender,
            $assistance_type,
            $id
        );

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: beneficiaries.php?status=updated");
            exit;

        } else {

            die("Unable to update beneficiary: " . $stmt->error);

        }
    }
}

// ==================================================
// 3. DELETE BENEFICIARY
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_beneficiary"])
) {

    $id = filter_var(
        $_POST["beneficiary_id"] ?? "",
        FILTER_VALIDATE_INT
    );

    if ($id !== false && $id !== null && $id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM beneficiaries WHERE id = ?"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: beneficiaries.php?status=deleted");
            exit;

        } else {

            die("Unable to delete beneficiary: " . $stmt->error);

        }
    }
}

// ==================================================
// 4. DISPLAY NOTIFICATION MESSAGES
// ==================================================

$status = $_GET["status"] ?? "";

if ($status === "success") {

    $message = "Beneficiary registered successfully!";
    $messageType = "success";

} elseif ($status === "updated") {

    $message = "Beneficiary information updated successfully!";
    $messageType = "success";

} elseif ($status === "deleted") {

    $message = "Beneficiary deleted successfully!";
    $messageType = "success";

} elseif ($status === "empty") {

    $message = "Please fill in all required fields.";
    $messageType = "error";

} elseif ($status === "invalid_email") {

    $message = "Please enter a valid email address.";
    $messageType = "error";

}

// ==================================================
// 5. FETCH BENEFICIARY FOR EDITING
// ==================================================

if (isset($_GET["edit"])) {

    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT
    );

    if ($editId !== false && $editId !== null && $editId > 0) {

        $stmt = $conn->prepare(
            "SELECT * FROM beneficiaries WHERE id = ?"
        );

        $stmt->bind_param("i", $editId);
        $stmt->execute();

        $editBeneficiary = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$editBeneficiary) {
            $message = "The selected beneficiary could not be found.";
            $messageType = "error";
        }

    }
}

// ==================================================
// 6. GET TOTAL BENEFICIARIES
// ==================================================

$totalResult = $conn->query(
    "SELECT COUNT(*) AS total FROM beneficiaries"
);

if (!$totalResult) {
    die("Unable to count beneficiaries: " . $conn->error);
}

$totalBeneficiaries = $totalResult->fetch_assoc()["total"];

// ==================================================
// 7. FETCH ALL BENEFICIARIES
// ==================================================

$result = $conn->query(
    "SELECT * FROM beneficiaries
     ORDER BY registration_date DESC"
);

if (!$result) {
    die("Unable to retrieve beneficiaries: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Beneficiaries | CHARITY HUB</title>

<style>

/* GENERAL SETTINGS */

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
    transition: 0.3s;
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

.subtitle {
    color: #777;
}

/* STATISTICS */

.stat {
    font-size: 32px;
    font-weight: bold;
    color: #26745f;
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

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #26745f;
}

/* BUTTONS */

button,
.btn {
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

.primary:hover {
    background: #1b5948;
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

/* TABLE */

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

tr:hover {
    background: #f5f8f7;
}

.table-wrap {
    overflow-x: auto;
}

/* ACTION BUTTONS */

.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-buttons form {
    margin: 0;
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

/* RESPONSIVENESS */

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

}

</style>

</head>

<body>

<!-- ======================================
     SIDEBAR NAVIGATION
====================================== -->

<div class="sidebar">

    <h2>CHARITY HUB</h2>

    <a href="index.php">Dashboard</a>

    <a href="donors.php">Donors</a>

    <a href="donations.php">Donations</a>

    <a href="volunteers.php">Volunteers</a>

    <a href="beneficiaries.php" class="active">
        Beneficiaries
    </a>

</div>


<!-- ======================================
     MAIN CONTENT
====================================== -->

<div class="main">

    <div class="card">

        <h1>Beneficiaries Management</h1>

        <p class="subtitle">
            Register, update and manage people receiving charity assistance.
        </p>

    </div>


    <!-- NOTIFICATIONS -->

    <?php if ($message !== ""): ?>

        <div class="alert <?php echo htmlspecialchars($messageType); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ======================================
         TOTAL BENEFICIARIES
    ====================================== -->

    <div class="card">

        <h2>Total Beneficiaries</h2>

        <div class="stat">

            <?php echo (int)$totalBeneficiaries; ?>

        </div>

    </div>


    <!-- ======================================
         REGISTER / EDIT FORM
    ====================================== -->

    <div class="card">

        <h2>

            <?php if ($editBeneficiary): ?>

                Edit Beneficiary

            <?php else: ?>

                Register New Beneficiary

            <?php endif; ?>

        </h2>


        <form method="POST" action="beneficiaries.php">

            <?php if ($editBeneficiary): ?>

                <input type="hidden"
                       name="beneficiary_id"
                       value="<?php echo (int)$editBeneficiary['id']; ?>">

            <?php endif; ?>


            <div class="form-grid">

                <!-- FULL NAME -->

                <div>

                    <label>Full Name *</label>

                    <input
                        type="text"
                        name="full_name"
                        maxlength="100"
                        placeholder="Enter full name"
                        value="<?php echo htmlspecialchars($editBeneficiary['full_name'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div>

                    <label>Email Address</label>

                    <input
                        type="email"
                        name="email"
                        maxlength="100"
                        placeholder="Enter email address"
                        value="<?php echo htmlspecialchars($editBeneficiary['email'] ?? ''); ?>"
                    >

                </div>


                <!-- PHONE -->

                <div>

                    <label>Phone Number *</label>

                    <input
                        type="tel"
                        name="phone"
                        maxlength="20"
                        placeholder="Enter phone number"
                        value="<?php echo htmlspecialchars($editBeneficiary['phone'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- GENDER -->

                <div>

                    <label>Gender</label>

                    <select name="gender">

                        <option value="">Select gender</option>

                        <option value="Female"
                            <?php echo (($editBeneficiary['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>
                            Female
                        </option>

                        <option value="Male"
                            <?php echo (($editBeneficiary['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>
                            Male
                        </option>

                        <option value="Other"
                            <?php echo (($editBeneficiary['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>
                            Other
                        </option>

                        <option value="Prefer not to say"
                            <?php echo (($editBeneficiary['gender'] ?? '') === 'Prefer not to say') ? 'selected' : ''; ?>>
                            Prefer not to say
                        </option>

                    </select>

                </div>


                <!-- ASSISTANCE TYPE -->

                <div>

                    <label>Assistance Type</label>

                    <select name="assistance_type">

                        <option value="">Select assistance</option>

                        <?php
                        $assistanceOptions = [
                            "Food",
                            "Education",
                            "Healthcare",
                            "Shelter",
                            "Financial Support",
                            "Other"
                        ];
                        ?>

                        <?php foreach ($assistanceOptions as $option): ?>

                            <option value="<?php echo htmlspecialchars($option); ?>"
                                <?php echo (($editBeneficiary['assistance_type'] ?? '') === $option) ? 'selected' : ''; ?>>

                                <?php echo htmlspecialchars($option); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ADDRESS -->

                <div>

                    <label>Address</label>

                    <textarea
                        name="address"
                        rows="2"
                        placeholder="Enter beneficiary address"
                    ><?php echo htmlspecialchars($editBeneficiary['address'] ?? ''); ?></textarea>

                </div>

            </div>


            <!-- FORM BUTTONS -->

            <?php if ($editBeneficiary): ?>

                <button
                    type="submit"
                    name="update_beneficiary"
                    class="primary"
                >

                    Update Beneficiary

                </button>

                <a href="beneficiaries.php" class="btn cancel">

                    Cancel Editing

                </a>

            <?php else: ?>

                <button
                    type="submit"
                    name="register_beneficiary"
                    class="primary"
                >

                    Register Beneficiary

                </button>

            <?php endif; ?>

        </form>

    </div>


    <!-- ======================================
         BENEFICIARY RECORDS
    ====================================== -->

    <div class="card">

        <h2>Registered Beneficiaries</h2>

        <input
            type="text"
            id="search"
            class="search-box"
            placeholder="Search beneficiaries by name, email or phone..."
        >


        <div class="table-wrap">

            <table id="beneficiariesTable">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Full Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Gender</th>

                        <th>Assistance</th>

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
                                    <?php echo htmlspecialchars($row["email"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["phone"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["gender"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["assistance_type"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["registration_date"]); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <!-- EDIT BUTTON -->

                                        <a
                                            href="beneficiaries.php?edit=<?php echo (int)$row['id']; ?>"
                                            class="btn edit"
                                        >

                                            Edit

                                        </a>


                                        <!-- DELETE BUTTON -->

                                        <form
                                            method="POST"
                                            action="beneficiaries.php"
                                            onsubmit="return confirm('Are you sure you want to delete this beneficiary?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="beneficiary_id"
                                                value="<?php echo (int)$row["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_beneficiary"
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

                            <td colspan="8" style="text-align:center;">

                                No beneficiaries registered yet.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ======================================
     SEARCH FUNCTIONALITY
====================================== -->

<script>

document.getElementById("search").addEventListener("input", function () {

    const searchValue = this.value.toLowerCase();

    const rows = document.querySelectorAll(
        "#beneficiariesTable tbody tr"
    );

    rows.forEach(function (row) {

        const rowText = row.textContent.toLowerCase();

        if (rowText.includes(searchValue)) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });

});

</script>

</body>

</html>
