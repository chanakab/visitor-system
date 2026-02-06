<?php
require 'db.php';

echo "<h1>System Update & Migration</h1>";

// 0. Import Schema (Create Tables)
$schemaFile = 'schema.sql';
if (file_exists($schemaFile)) {
    $sql = file_get_contents($schemaFile);
    if ($conn->multi_query($sql)) {
        do { if ($res = $conn->store_result()) $res->free(); } while ($conn->more_results() && $conn->next_result());
        echo "<p>✅ Database Schema Updated (Reset).</p>";
    } else {
        echo "<p style='color:red;'>❌ Schema Error: " . $conn->error . "</p>";
        exit;
    }
}

// 1. Create Default Data
$conn->query("INSERT IGNORE INTO institutes (id, name, code, address) VALUES (1, 'Head Office', 'HO-001', 'Colombo 10')");
$pass = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password_hash, role, institute_id, full_name, status) VALUES ('admin', '$pass', 'super_admin', 1, 'System Administrator', 'active')");
$pass_off = password_hash('1234', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password_hash, role, institute_id, full_name, counter_number, status) VALUES ('officer1', '$pass_off', 'officer', 1, 'Officer John', '01', 'active')");

echo "<hr><h3 style='color:green;'>Update Complete!</h3>";
echo "<a href='login.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none;'>Go to Login</a>";
?>
