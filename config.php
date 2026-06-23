<?php
// config.php
// Database configuration for native PHP 5

$db_host = 'localhost';
$db_user = 'root';

date_default_timezone_set('Asia/Jakarta');
$db_pass = '123456789'; // Changed as per user request
$db_name = 'material'; // Adjust to the actual database name

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the character set is correct
$conn->set_charset("latin1");
?>
