<?php
// config.php
// Database configuration for native PHP 5

$db_host = '192.168.2.12';
$db_user = 'anugrah';

date_default_timezone_set('Asia/Jakarta');
$db_pass = 'anugrah'; 
$db_name = 'dbold'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$askes_conn = new mysqli($db_host, $db_user, $db_pass, 'askes');
$hrd_conn = new mysqli($db_host, $db_user, $db_pass, 'hrd');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($askes_conn->connect_error) {
    die("Askes Connection failed: " . $askes_conn->connect_error);
}

// Export as globals for compatibility with db_functions.php
$GLOBALS['db_conn'] = $conn;
$GLOBALS['askes_conn'] = $askes_conn;
$GLOBALS['hrd_conn'] = $hrd_conn;

// Ensure the character set is correct
$conn->set_charset("latin1");
$askes_conn->set_charset("latin1");
$hrd_conn->set_charset("latin1");
?>
