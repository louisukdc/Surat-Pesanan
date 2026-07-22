<?php
require_once dirname(__FILE__) . '/includes/auth.php';
$r = mysqli_query($GLOBALS['db_conn'], 'SHOW TABLES LIKE "%sisuper%"');
while ($row = mysqli_fetch_array($r)) {
    echo $row[0] . "\n";
}
$r2 = mysqli_query($GLOBALS['db_conn'], 'SHOW TABLES LIKE "%pengadaan%"');
while ($row = mysqli_fetch_array($r2)) {
    echo $row[0] . "\n";
}
?>
