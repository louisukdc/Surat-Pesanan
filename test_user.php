<?php
require_once dirname(__FILE__) . '/includes/auth.php';
$res = mysqli_query($GLOBALS['db_conn'], "DESCRIBE sp_user");
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
