<?php
// Database connection settings
$db_host = 'localhost';
$db_name = 'water_sports_rental';
$db_user = 'root';
$db_password = '';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?> 