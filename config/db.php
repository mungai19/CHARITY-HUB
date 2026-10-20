<?php
// ---- Database settings (XAMPP defaults) ----
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";          // XAMPP default is empty. Change if you set a MySQL password.
$DB_NAME = "charity_hub";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    error_log("DB connection error: " . $conn->connect_error);
    http_response_code(500);
    exit("<h2 style='font-family:sans-serif'>Cannot connect to the database.</h2>
    <p style='font-family:sans-serif'>1) Start MySQL in XAMPP. 2) Import <b>schema.sql</b> in phpMyAdmin.
    3) Check the username/password in <b>config/db.php</b>.</p>");
}
$conn->set_charset("utf8mb4");
