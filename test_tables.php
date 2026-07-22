<?php
require_once dirname(__FILE__) . '/includes/auth.php';
$r = mysqli_query($GLOBALS['db_conn'], 'SHOW TABLES');
while ($row = mysqli_fetch_array($r)) {
    echo $row[0] . "\n";
}
?>
