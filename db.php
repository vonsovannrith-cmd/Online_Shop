<?php
// Database connection
$conn = new mysqli("localhost", "root", "RITH2008@khmer", "onlineshopdb");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>