CREATE TABLE `purchase_orders` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nomor_po` varchar(255) UNIQUE COMMENT 'Nomor unik Purchase Order',
  `tanggal_kirim_vendor` datetime COMMENT 'Kapan PO dikirim ke vendor',
  `status` varchar(255) COMMENT 'Contoh: Dikirim, Diterima, Selesai'
);

CREATE TABLE `surat_jalan` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `po_id` int,
  `nomor_surat_jalan` varchar(255) UNIQUE,
  `tanggal_terima` datetime,
  `teknisi_penerima_id` int COMMENT 'ID Teknisi yang mengecek',
  `file_scan_url` varchar(255) COMMENT 'Path/URL upload dokumen surat jalan',
  `kategori` varchar(255) COMMENT 'Barang / Jasa',
  `status_pengecekan` varchar(255) COMMENT 'Contoh: Sesuai, Ada Kerusakan',
  `created_at` datetime
);

CREATE TABLE `berita_acara` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int UNIQUE,
  `nomor_ba` varchar(255) UNIQUE,
  `tanggal_generate` datetime,
  `keterangan` text,
  `status_dokumen` varchar(255) COMMENT 'Status untuk Keuangan'
);

CREATE TABLE `laporan_kerja` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int UNIQUE,
  `nomor_lk` varchar(255) UNIQUE,
  `tanggal_generate` datetime,
  `rincian_pekerjaan` text,
  `status_dokumen` varchar(255) COMMENT 'Status untuk Keuangan'
);

CREATE TABLE `pembayaran` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int COMMENT 'Referensi dokumen lengkap',
  `keuangan_validator_id` int COMMENT 'ID Staf keuangan yang memvalidasi',
  `nomor_bukti_bayar` varchar(255) UNIQUE,
  `jumlah_bayar` decimal,
  `tanggal_validasi` datetime,
  `tanggal_bayar` datetime,
  `status_bayar` varchar(255) COMMENT 'Lunas / Tertunda'
);

ALTER TABLE `surat_jalan` ADD FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`);

ALTER TABLE `berita_acara` ADD FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);

ALTER TABLE `laporan_kerja` ADD FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);

ALTER TABLE `pembayaran` ADD FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);

ALTER TABLE `berita_acara` ADD FOREIGN KEY (`surat_jalan_id`) REFERENCES `pembayaran` (`surat_jalan_id`);

ALTER TABLE `laporan_kerja` ADD FOREIGN KEY (`surat_jalan_id`) REFERENCES `pembayaran` (`surat_jalan_id`);
