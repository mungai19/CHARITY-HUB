
<?php
require_once "db.php";

// Retrieve all volunteers
$sql = "SELECT * FROM volunteers ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Unable to load volunteers: " . $conn->error);
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITY HUB | Volunteers</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263238;
        }

        header {
            background: #174c3c;
            color: white;
            padding: 22px 30px;
        }

        header h1 { margin: 0 0 8px; }

        nav { margin-top: 15px; }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            display: inline-block;
            background: #176b50;
            color: white;
            padding: 11px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .table-wrap {
            background: white;
            padding: 18px;
            border-radius: 9px;
            overflow-x: auto;
            margin-top: 22px;
            box-shadow: 0 2px 8px #0000000d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 10px;
            border-bottom: 1px solid #e6ebef;
            text-align: left;
        }

        th { background: #edf4f0; }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 12px;
            background: #e5f3e9;
            font-size: 12px;
        }

        .actions { white-space: nowrap; }

        .edit { color: #176b50; }
        .delete { color: #b42318; }

        .message {
            padding: 12px;
            background: #e5f3e9;
            border-radius: 5px;
        }

        @media (max-width: 600px) {
            header { padding: 20px; }
            main { padding: 0 12px; }
        }
    </style>
</head>
<body>

<header>
    <h1>CHARITY HUB</h1>
    <p>Volunteer Management Module</p>

    <nav>
        <a href="index.php">Dashboard</a>
        <a href="volunteers.php">Volunteers</a>
        <a href="donations.php">Donations</a>
    </nav>
</header>

<main>
    <div class="top">
        <div>
            <h2>Volunteers</h2>
            <p>Register and manage your charity volunteers.</p>
        </div>

        <a class="btn" href="add_volunteer.php">
            + Add Volunteer
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <p class="message">
            <?= e($_GET['success']) ?>
        </p>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Skills</th>
                    <th>Availability</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($volunteer = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= e($volunteer['id']) ?></td>
                        <td><?= e($volunteer['full_name']) ?></td>
                        <td><?= e($volunteer['email']) ?></td>
                        <td><?= e($volunteer['phone']) ?></td>
                        <td><?= e($volunteer['skills']) ?></td>
                        <td><?= e($volunteer['availability']) ?></td>
                        <td>
                            <span class="status">
                                <?= e($volunteer['status']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a class="edit"
                               href="edit_volunteer.php?id=<?= (int)$volunteer['id'] ?>">
                                Edit
                            </a>
                            |
                            <a class="delete"
                               href="delete_volunteer.php?id=<?= (int)$volunteer['id'] ?>"
                               onclick="return confirm('Delete this volunteer?')">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">
                        No volunteers registered yet.
                        Click Add Volunteer to begin.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
