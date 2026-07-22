<?php
require_once dirname(__FILE__) . '/includes/auth.php';
require_once dirname(__FILE__) . '/config/db_functions.php';
$u = db_get_units();
print_r($u);
?>
