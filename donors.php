
<?php
// CHARITY HUB - Donor Management

require_once "db.php";

$message = "";
$error = "";

// Register a donor
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($fullName === "") {
        $error = "Please enter the donor's full name.";
    } elseif (
        $email !== "" &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = "Please enter a valid email address.";
    } else {

        $emailValue = $email !== "" ? $email : null;
        $phoneValue = $phone !== "" ? $phone : null;
        $addressValue = $address !== "" ? $address : null;

        $stmt = $conn->prepare(
            "INSERT INTO donors (full_name, email, phone, address)
             VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            $error = "Unable to prepare donor registration.";
        } else {
            $stmt->bind_param(
                "ssss",
                $fullName,
                $emailValue,
                $phoneValue,
                $addressValue
            );

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: donors.php?saved=1");
                exit;
            }

            $error = "Unable to save donor. Please try again.";
            $stmt->close();
        }
    }
}

if (isset($_GET["saved"]) && $_GET["saved"] === "1") {
    $message = "Donor registered successfully!";
}

// Load registered donors
$result = $conn->query(
    "SELECT id, full_name, email, phone, address, created_at
     FROM donors
     ORDER BY id DESC"
);

if (!$result) {
    die("Unable to load donors. Check that the donors table exists.");
}

function donorEscape($value) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITY HUB - Donors</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .donors-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .donors-card {
            background: #fff;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,.06);
        }

        .donors-card h2 {
            margin-top: 0;
            color: #176b45;
        }

        .donors-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .donors-field label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .donors-field input {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font: inherit;
        }

        .donors-submit {
            margin-top: 20px;
            padding: 12px 22px;
            border: 0;
            border-radius: 5px;
            background: #176b45;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        .donors-message,
        .donors-error {
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .donors-message {
            background: #e0f5e7;
            color: #176b45;
        }

        .donors-error {
            background: #ffeded;
            color: #a12626;
        }

        .donors-table-wrap {
            overflow-x: auto;
        }

        .donors-table-wrap table {
            width: 100%;
            border-collapse: collapse;
        }

        .donors-table-wrap th,
        .donors-table-wrap td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donors-table-wrap th {
            background: #176b45;
            color: #fff;
        }

        .donors-back {
            color: #176b45;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 650px) {
            .donors-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="donors-page">

    <a class="donors-back" href="index.php">&larr; Back to Dashboard</a>

    <h1>CHARITY HUB - Donor Management</h1>
    <p>Register donors and view their contact details.</p>

    <?php if ($message !== ""): ?>
        <div class="donors-message">
            <?php echo donorEscape($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="donors-error">
            <?php echo donorEscape($error); ?>
        </div>
    <?php endif; ?>

    <section class="donors-card">
        <h2>Register New Donor</h2>

        <form action="donors.php" method="POST">
            <div class="donors-form-grid">

                <div class="donors-field">
                    <label for="full_name">Full Name *</label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        maxlength="150"
                        required
                        value="<?php echo donorEscape($_POST["full_name"] ?? ""); ?>"
                    >
                </div>

                <div class="donors-field">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        value="<?php echo donorEscape($_POST["email"] ?? ""); ?>"
                    >
                </div>

                <div class="donors-field">
                    <label for="phone">Phone Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        maxlength="30"
                        value="<?php echo donorEscape($_POST["phone"] ?? ""); ?>"
                    >
                </div>

                <div class="donors-field">
                    <label for="address">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        maxlength="255"
                        value="<?php echo donorEscape($_POST["address"] ?? ""); ?>"
                    >
                </div>

            </div>

            <button class="donors-submit" type="submit">
                Register Donor
            </button>
        </form>
    </section>

    <section class="donors-card">
        <h2>Registered Donors</h2>
        <p>Total registered donors: <?php echo $result->num_rows; ?></p>

        <div class="donors-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Date Registered</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($donor = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$donor["id"]; ?></td>
                                <td><?php echo donorEscape($donor["full_name"]); ?></td>
                                <td><?php echo donorEscape($donor["email"]); ?></td>
                                <td><?php echo donorEscape($donor["phone"]); ?></td>
                                <td><?php echo donorEscape($donor["address"]); ?></td>
                                <td><?php
                                    $timestamp = strtotime($donor["created_at"] ?? "");
                                    echo $timestamp ? date("d M Y", $timestamp) : "N/A";
                                ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No donors registered yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

</body>
</html>
