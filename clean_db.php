<?php
require 'config.php';
$keep_id = 24;

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DELETE FROM sp_surat_jalan WHERE id_spu_h != $keep_id");
$conn->query("DELETE FROM berita_acara");
$conn->query("DELETE FROM laporan_kerja");
$conn->query("DELETE FROM pembayaran");
$conn->query("DELETE FROM surat_jalan WHERE id_spu_h != $keep_id");
$conn->query("DELETE FROM spu_d WHERE id_sp != $keep_id");
$res = $conn->query("DELETE FROM spu_h WHERE id != $keep_id");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

if (!$res) {
    echo "Error: " . $conn->error . "\n";
} else {
    echo "Semua data berlebih telah dihapus.\n";
}
?>
