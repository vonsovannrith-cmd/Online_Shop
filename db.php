<?php
$host = "localhost";
$user = "root";
$pass = "RITH2008@khmer";
$dbname = "onlineshopdb";

// Connect to database
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 for Khmer support
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("❌ Error setting charset: " . mysqli_error($conn));
}

// Optional: enable mysqli error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>