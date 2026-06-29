<?php
require_once '../config.php';

$where = "1=1 AND h.status_acc = 'Approved'";
$count_sql = "SELECT COUNT(h.id) as total FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier WHERE $where";
$total_res = $conn->query($count_sql);
if (!$total_res) {
    echo "COUNT ERROR: " . $conn->error . "\n";
} else {
    echo "Total: " . $total_res->fetch_assoc()['total'] . "\n";
}

$sql = "SELECT h.id, h.tgl_pesan, h.date_acc, s.NamaSupplier as namasup, g.NamaGudang as unit, h.jenis_bayar as pembayaran, h.flag, h.status_acc, h.nama_lampiran 
        FROM spu_h h LEFT JOIN m_supplier s ON h.id_supplier = s.KodeSupplier LEFT JOIN m_gudang g ON h.id_gudang = g.KodeGudang
        WHERE $where ORDER BY h.id DESC LIMIT 0, 50";
        
$res = $conn->query($sql);
if (!$res) {
    echo "SELECT ERROR: " . $conn->error . "\n";
} else {
    echo "Found " . $res->num_rows . " rows.\n";
}
?>
