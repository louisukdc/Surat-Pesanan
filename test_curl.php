<?php
$data = http_build_query([
    'no_pesanan' => 'SP/2026/07/0689',
    'tgl_pesanan' => '2026-07-03',
    'nama_vendor' => 'ABADI BERKAT PERKASA PT',
    'action_status' => 'diajukan',
    'harga_vendor' => 693000,
    'nama_barang' => ['sadasd'],
    'merk' => ['MedisOne'],
    'model' => ['sdasd'],
    'spec' => ['dasd'],
    'satuan' => ['pcs'],
    'jumlah' => [1],
    'harga_satuan' => [698000],
    'disc_item' => [5000]
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $data,
    ],
];

$context  = stream_context_create($options);
// Read from localhost to simulate browser exactly
$result = file_get_contents('http://localhost:8001/sp_umum/home.php?page=buat_pesanan', false, $context);
if ($result === FALSE) { 
    echo "ERROR FETCHING\n";
} else {
    // extract just the initItemsRaw line to see what was injected
    preg_match('/let initItemsRaw = (.*?);/', $result, $matches);
    if(isset($matches[1])) {
        echo "INIT_ITEMS: " . $matches[1] . "\n";
    } else {
        echo "NO initItemsRaw found in HTML!\n";
    }
}
