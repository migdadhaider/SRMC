<?php
// Database connection - update these values when DB is ready
$host = "localhost";
$user = "root";
$pass = "";    // set your DB password
$db   = "srms"; // change if different

// Create mysqli connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection (when DB exists)
if ($conn->connect_error) {
    // Note: when DB not created yet, this will show an error. That's expected.
    // Replace with silent handling if you prefer.
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
