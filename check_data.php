<?php
require 'config.php';
$res1 = $conn->query("SELECT COUNT(*) FROM m_supplier");
$res2 = $conn->query("SELECT COUNT(*) FROM spu_h");
$res3 = $conn->query("SELECT COUNT(*) FROM surat_jalan");
echo "Suppliers: " . $res1->fetch_row()[0] . "\n";
echo "SPU_H: " . $res2->fetch_row()[0] . "\n";
echo "Surat Jalan: " . $res3->fetch_row()[0] . "\n";
