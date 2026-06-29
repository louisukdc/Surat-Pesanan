<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/sp_umum/api/orders.php';
$_GET['status_filter'] = 'Approved';
$_GET['page'] = 1;
$_GET['limit'] = 25;

session_start();
$_SESSION['user_id'] = 1;

require 'orders.php';
?>
