<?php
require_once dirname(__FILE__) . '/includes/auth.php';
$r = mysqli_query($GLOBALS['db_conn'], 'DESCRIBE m_tarif_permohonan');
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        print_r($row);
    }
} else {
    echo "Table not found.";
}

echo "\n\nData:\n";
$r2 = mysqli_query($GLOBALS['db_conn'], 'SELECT * FROM m_tarif_permohonan LIMIT 5');
if ($r2) {
    while ($row = mysqli_fetch_assoc($r2)) {
        print_r($row);
    }
}
?>
