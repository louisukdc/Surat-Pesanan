<?php
require 'config.php';
$conn->query("UPDATE users SET password = MD5('adminrkz') WHERE username = 'admin'");
echo "Admin password fixed!";
?>
