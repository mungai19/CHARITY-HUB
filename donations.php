<?php
// CHARITY HUB - Donations Management

require_once "db.php";

$message = "";
$error = "";

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $donor_name = trim($_POST["donor_name"] ?? "");
    $amount = $_POST["amount"] ?? "";
    $donation_date = $_POST["donation_date"] ?? "";
    $payment_method = $_POST["payment_method"] ?? "";
    $notes = trim($_POST["notes"] ?? "");

    // Validate the required fields
    if (
        $donor_name === "" ||
        !is_numeric($amount) ||
        (float)$amount <= 0 ||
        $donation_date === "" ||
        $payment_method === ""
    ) {
        $error = "Please fill in all required fields correctly.";
    } else {

        // Save the donation securely
        $stmt = $conn->prepare(
            "INSERT INTO donations
            (donor_name, amount, donation_date, payment_method, notes)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $amount_value = (float)$amount;

            $stmt->bind_param(
                "sdsss",
                $donor_name,
                $amount_value,
                $donation_date,
                $payment_method,
                $notes
            );

            if ($stmt->execute()) {
                header("Location: donations.php?saved=1");
                exit;
            } else {
                $error = "The donation could not be saved. Please try again.";
            }

            $stmt->close();
        } else {
            $error = "Unable to prepare the database request.";
        }
    }
}

// Success message after saving
if (isset($_GET["saved"]) && $_GET["saved"] === "1") {
    $message = "Donation saved successfully!";
}

// Retrieve saved donations
$donations = $conn->query(
    "SELECT id, donor_name, amount, donation_date,
            payment_method, status, notes
     FROM donations
     ORDER BY id DESC"
);

if (!$donations) {
    die("Unable to retrieve donations. Please check your database table.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITY HUB | Donations</title>
    <link rel="stylesheet" href="style.css">

    <style>
        /* Donations page styles only */
        .donations-content {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .donations-form,
        .donations-list {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .donations-form h2,
        .donations-list h2 {
            color: #176b45;
            margin-top: 0;
        }

        .donations-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .donations-form input,
        .donations-form select,
        .donations-form textarea {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font: inherit;
        }

        .donations-form button {
            margin-top: 20px;
            padding: 12px 22px;
            border: none;
            border-radius: 5px;
            background: #176b45;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        .donations-form button:hover {
            background: #105335;
        }

        .donation-message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 18px;
        }

        .donation-success {
            background: #e0f5e7;
            color: #176b45;
        }

        .donation-error {
            background: #ffeded;
            color: #a12626;
        }

        .donations-table-wrap {
            overflow-x: auto;
        }

        .donations-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .donations-list th,
        .donations-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donations-list th {
            background: #176b45;
            color: white;
        }

        .donations-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #176b45;
            text-decoration: none;
            font-weight: bold;
        }

        @media (min-width: 700px) {
            .donations-form .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
        }
    </style>
</head>

<body>

<div class="donations-content">

    <a class="donations-back" href="index.php">
        &larr; Back to Dashboard
    </a>

    <h1>CHARITY HUB - Donations</h1>
    <p>Record and manage charity donations.</p>

    <?php if ($message !== ""): ?>
        <div class="donation-message donation-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="donation-message donation-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="donations-form">
        <h2>Record a Donation</h2>

        <form method="POST" action="donations.php">

            <div class="form-row">
                <div>
                    <label for="donor_name">Donor Name *</label>
                    <input
                        type="text"
                        id="donor_name"
                        name="donor_name"
                        maxlength="100"
                        required
                    >
                </div>

                <div>
                    <label for="amount">Amount (KSh) *</label>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        min="0.01"
                        step="0.01"
                        required
                    >
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="donation_date">Donation Date *</label>
                    <input
                        type="date"
                        id="donation_date"
                        name="donation_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >
                </div>

                <div>
                    <label for="payment_method">Payment Method *</label>
                    <select
                        id="payment_method"
                        name="payment_method"
                        required
                    >
                        <option value="">Choose a method</option>
                        <option value="M-Pesa">M-Pesa</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <label for="notes">Notes (Optional)</label>
            <textarea
                id="notes"
                name="notes"
                rows="3"
                placeholder="Additional donation details"
            ></textarea>

            <button type="submit">Save Donation</button>

        </form>
    </section>

    <section class="donations-list">
        <h2>Recorded Donations</h2>

        <div class="donations-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($donations->num_rows > 0): ?>

                        <?php while ($row = $donations->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo (int)$row["id"]; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row["donor_name"]); ?>
                                </td>
                                <td>
                                    KSh <?php echo number_format((float)$row["amount"], 2); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row["donation_date"]); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row["payment_method"]); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row["status"]); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                No donations recorded yet. Use the form above to add one.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

</body>
</html>