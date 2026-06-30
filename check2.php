<?php
require 'config.php';
$res = $conn->query('SELECT * FROM surat_jalan'); 
if (!$res) {
    echo "Error: " . $conn->error . "\n";
} else {
    while($r = $res->fetch_assoc()){ 
        print_r($r); 
    }
}
?>
