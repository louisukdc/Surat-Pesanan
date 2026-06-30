<?php
require 'config.php';
$res = $conn->query('SHOW CREATE TABLE sp_surat_jalan'); 
print_r($res->fetch_assoc());
?>
