<?php
require_once '../config.php';

$sql = "CREATE TABLE IF NOT EXISTS `surat_jalan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `id_spu_h` INT,
  `nomor_surat_jalan` VARCHAR(255) UNIQUE,
  `tanggal_terima` DATETIME,
  `teknisi_penerima_id` INT,
  `file_scan_url` VARCHAR(255),
  `kategori` VARCHAR(255),
  `status_pengecekan` VARCHAR(255),
  `catatan` TEXT,
  `created_at` DATETIME,
  KEY `idx_id_spu_h` (`id_spu_h`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;";

if($conn->query($sql)) {
    echo "surat_jalan created.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$sql2 = "CREATE TABLE IF NOT EXISTS `berita_acara` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` INT UNIQUE,
  `nomor_ba` VARCHAR(255) UNIQUE,
  `tanggal_generate` DATETIME,
  `keterangan` TEXT,
  `status_dokumen` VARCHAR(255)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;";
$conn->query($sql2);

$sql3 = "CREATE TABLE IF NOT EXISTS `laporan_kerja` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` INT UNIQUE,
  `nomor_lk` VARCHAR(255) UNIQUE,
  `tanggal_generate` DATETIME,
  `rincian_pekerjaan` TEXT,
  `status_dokumen` VARCHAR(255)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;";
$conn->query($sql3);

$sql4 = "CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` INT,
  `keuangan_validator_id` INT,
  `nomor_bukti_bayar` VARCHAR(255) UNIQUE,
  `jumlah_bayar` DECIMAL(15, 2),
  `tanggal_validasi` DATETIME,
  `tanggal_bayar` DATETIME,
  `status_bayar` VARCHAR(255),
  KEY `idx_surat_jalan_id` (`surat_jalan_id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;";
$conn->query($sql4);

echo "All tables created.";
?>
