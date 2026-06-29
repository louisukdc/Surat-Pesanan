<?php
require 'config.php';
$keep_id = 24;

$res = $conn->query("DELETE FROM spu_h WHERE id != $keep_id");
if (!$res) {
    echo "Error deleting spu_h: " . $conn->error . "\n";
} else {
    echo "Deleted spu_h successfully\n";
}
?>
