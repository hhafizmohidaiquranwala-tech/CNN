<?php
// db.php - Database connection
$host = 'localhost';
$username = 'rsoa_rsoa378_41';
$password = '123456';
$database = 'rsoa_rsoa378_41';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

session_start();
?>
