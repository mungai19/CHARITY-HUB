<?php
$donations = [
    [
        "donor" => "Mary Wanjiku",
        "amount" => "KES 5,000",
        "date" => "12 October 2026",
        "method" => "M-Pesa",
        "status" => "Completed"
    ],
    [
        "donor" => "John Kamau",
        "amount" => "KES 2,500",
        "date" => "10 October 2026",
        "method" => "Bank Transfer",
        "status" => "Completed"
    ],
    [
        "donor" => "Grace Achieng",
        "amount" => "KES 1,000",
        "date" => "08 October 2026",
        "method" => "Cash",
        "status" => "Pending"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations | CHARITY HUB</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        .donations-container {
            padding: 30px;
        }

        .donations-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .donations-header h1 {
            margin-bottom: 5px;
        }

        .add-donation-btn {
            background: #0d9488;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .donations-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .donations-table th,
        .donations-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donations-table th {
            background: #f1f5f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .completed {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<div class="donations-container">

    <div class="donations-header">
        <div>
            <h1>Donations Management</h1>
            <p>View and manage donations received by the charity.</p>
        </div>

        <a href="#" class="add-donation-btn"
           onclick="alert('The Add Donation form will be created in the next step.'); return false;">
            + Add New Donation
        </a>
    </div>

    <div class="table-wrapper">
        <table class="donations-table">
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?= htmlspecialchars($donation["donor"]) ?></td>
                        <td><?= htmlspecialchars($donation["amount"]) ?></td>
                        <td><?= htmlspecialchars($donation["date"]) ?></td>
                        <td><?= htmlspecialchars($donation["method"]) ?></td>
                        <td>
                            <span class="status <?= strtolower($donation["status"]) ?>">
                                <?= htmlspecialchars($donation["status"]) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>