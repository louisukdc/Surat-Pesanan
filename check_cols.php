<?php
require 'config.php';
$res = $conn->query("DESCRIBE spu_h");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | ";
}
?>
