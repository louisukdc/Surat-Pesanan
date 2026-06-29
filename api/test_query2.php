<?php
require_once '../config.php';
$res = $conn->query('SELECT * FROM spu_h LIMIT 1'); 
print_r($res->fetch_assoc());
?>
