<?php
$_GET['page'] = 'monitoring';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = 'page=monitoring';

// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin'; // Or direktur

require 'home.php';
?>
