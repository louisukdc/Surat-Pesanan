<?php
require_once dirname(__FILE__) . '/includes/auth.php';
$r = mysqli_query($GLOBALS['db_conn'], 'DESCRIBE spu_h');
while ($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}
?>
