<?php
// db.php - Database Connection

$host = 'localhost';
$user = 'root';      // Default WAMP/XAMPP user
$pass = 'chanaka';          // Default WAMP/XAMPP password
$dbname = 'visitor_sys';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure UTF-8 support
$conn->set_charset("utf8");

?>
