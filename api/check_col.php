<?php
require_once '../config.php';
$res = $conn->query("SHOW COLUMNS FROM surat_jalan");
if(!$res) {
    echo "ERROR: " . $conn->error;
} else {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
?>
