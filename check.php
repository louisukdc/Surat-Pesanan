<?php
require 'config.php';
$r = $conn->query('SELECT COUNT(*) as c FROM spu_h');
echo "Count spu_h: " . $r->fetch_assoc()['c'] . "\n";
$r2 = $conn->query('SELECT COUNT(*) as c FROM spu_d');
echo "Count spu_d: " . $r2->fetch_assoc()['c'] . "\n";
$r3 = $conn->query('SELECT COUNT(*) as c FROM surat_jalan');
echo "Count surat_jalan: " . $r3->fetch_assoc()['c'] . "\n";
?>
