<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'water_sports_rental');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create connection function
function getDbConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset(DB_CHARSET);
    
    return $conn;
}
?> 