<?php
$conn = new mysqli('localhost', 'root', '123456789');
$res = $conn->query("SHOW DATABASES");
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
?>
