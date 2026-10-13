
<?php
if (!isset($pageTitle)) {
    $pageTitle = "CHARITY HUB";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | CHARITY HUB</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="wrapper">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <span>Administrator</span>
    </div>
