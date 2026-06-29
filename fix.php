<?php
$lines = file('pages/list_pesanan.php');
$out = [];
foreach ($lines as $i => $l) {
    $lineNum = $i + 1;
    if ($lineNum < 122 || $lineNum > 194) {
        $out[] = $l;
    }
}
file_put_contents('pages/list_pesanan.php', implode("", $out));
echo "Fixed list_pesanan.php";
?>
