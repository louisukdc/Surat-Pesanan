-- ========================================================
-- DATABASE SKEMA E-PROCUREMENT RKZ (INTEGRATED)
-- ========================================================
-- 1. MASTER SUPPLIER
CREATE TABLE IF NOT EXISTS `m_supplier` (
  `IdSupplier` INT(11) NOT NULL AUTO_INCREMENT,
  `KodeSupplier` VARCHAR(6) NOT NULL,
  `NamaSupplier` VARCHAR(30) DEFAULT NULL,
  `NPWP` VARCHAR(30) DEFAULT NULL,
  `Alamat1` VARCHAR(100) DEFAULT NULL,
  `Alamat2` VARCHAR(100) DEFAULT NULL,
  `Kota2` VARCHAR(30) DEFAULT NULL,
  `Telp1` VARCHAR(20) DEFAULT NULL,
  `Telp2` VARCHAR(20) DEFAULT NULL,
  `Telp3` VARCHAR(20) DEFAULT NULL,
  `Kota1` VARCHAR(30) DEFAULT NULL,
  `ContactPerson` VARCHAR(30) DEFAULT NULL,
  `Mobile1` VARCHAR(30) DEFAULT NULL,
  `Mobile2` VARCHAR(30) DEFAULT NULL,
  `Fax1` VARCHAR(30) DEFAULT NULL,
  `Fax2` VARCHAR(30) DEFAULT NULL,
  `Email1` VARCHAR(50) DEFAULT NULL,
  `Email2` VARCHAR(50) DEFAULT NULL,
  `Status` VARCHAR(20) DEFAULT NULL,
  `KontrakAwal` DATE DEFAULT NULL,
  `KontrakAkhir` DATE DEFAULT NULL,
  `CaraPembayaran` VARCHAR(30) DEFAULT NULL,
  `NamaBank` VARCHAR(30) DEFAULT NULL,
  `NoRekening` VARCHAR(50) DEFAULT NULL,
  `TempoPembayaran` VARCHAR(20) DEFAULT NULL,
  `JamPengiriman` VARCHAR(30) DEFAULT NULL,
  `KodePerkiraan` CHAR(4) DEFAULT NULL,
  `Keterangan1` VARCHAR(50) DEFAULT NULL,
  `ffarmasi` CHAR(1) DEFAULT NULL,
  `UserCreated` CHAR(5) DEFAULT NULL,
  `DateTimeCreated` DATETIME DEFAULT NULL,
  `Flag` CHAR(1) DEFAULT NULL,
  `KodePerkiraanBaru` CHAR(5) DEFAULT NULL,
  `tanda` VARCHAR(1) NOT NULL COMMENT 'P=proyek I=implan',
  `NamaInvoice` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`IdSupplier`, `KodeSupplier`),
  UNIQUE KEY `uq_kodesupplier` (`KodeSupplier`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
-- 2. HEADER PESANAN (MENGGANTIKAN purchase_orders)
CREATE TABLE IF NOT EXISTS `spu_h` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `no_permintaan` VARCHAR(15) NOT NULL DEFAULT '0',
  `nama_lampiran` VARCHAR(15) NOT NULL DEFAULT '0',
  `tgl_pesan` DATE NOT NULL,
  `id_supplier` VARCHAR(6) NOT NULL COMMENT 'Relasi ke m_supplier',
  `no_penawaran` VARCHAR(25) NOT NULL DEFAULT '',
  `tgl_penawaran` DATE NOT NULL DEFAULT '1900-01-01',
  `tgl_kirim` DATE NOT NULL,
  `gudang` VARCHAR(3) NOT NULL DEFAULT '',
  `jenis_bayar` VARCHAR(6) NOT NULL,
  `keterangan` TEXT NOT NULL,
  `user_created` VARCHAR(10) DEFAULT NULL,
  `dtime_created` DATETIME NOT NULL,
  `user_acc` VARCHAR(5) NOT NULL,
  `date_acc` DATETIME NOT NULL,
  `flag` VARCHAR(1) NOT NULL DEFAULT '',
  `status_acc` VARCHAR(20) DEFAULT 'Draft',
  `alasan_tolak` TEXT,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
-- 3. DETAIL PESANAN
CREATE TABLE IF NOT EXISTS `spu_d` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_sp` INT(11) NOT NULL DEFAULT 0,
  `barang` VARCHAR(90) NOT NULL DEFAULT '',
  `model` VARCHAR(35) NOT NULL DEFAULT '',
  `merk` VARCHAR(35) NOT NULL DEFAULT '',
  `spec` VARCHAR(55) NOT NULL DEFAULT '',
  `qty` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `harga` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `disc` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `ppn` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `jumlah` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `date_created` DATETIME NOT NULL DEFAULT '1900-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `id_sp` (`id_sp`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
-- 4. ALUR E-PROCUREMENT (PENERIMAAN BARANG & BAST)
CREATE TABLE IF NOT EXISTS `surat_jalan` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `id_spu_h` int COMMENT 'Relasi ke spu_h (Header Pesanan)',
  `nomor_surat_jalan` varchar(255) UNIQUE,
  `tanggal_terima` datetime,
  `teknisi_penerima_id` int COMMENT 'ID Teknisi yang mengecek',
  `file_scan_url` varchar(255) COMMENT 'Path/URL upload dokumen surat jalan',
  `kategori` varchar(255) COMMENT 'Barang / Jasa',
  `status_pengecekan` varchar(255) COMMENT 'Contoh: Sesuai, Ada Kerusakan',
  `created_at` datetime
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
CREATE TABLE IF NOT EXISTS `berita_acara` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int UNIQUE,
  `nomor_ba` varchar(255) UNIQUE,
  `tanggal_generate` datetime,
  `keterangan` text,
  `status_dokumen` varchar(255) COMMENT 'Status untuk Keuangan'
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
CREATE TABLE IF NOT EXISTS `laporan_kerja` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int UNIQUE,
  `nomor_lk` varchar(255) UNIQUE,
  `tanggal_generate` datetime,
  `rincian_pekerjaan` text,
  `status_dokumen` varchar(255) COMMENT 'Status untuk Keuangan'
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `surat_jalan_id` int COMMENT 'Referensi dokumen lengkap',
  `keuangan_validator_id` int COMMENT 'ID Staf keuangan yang memvalidasi',
  `nomor_bukti_bayar` varchar(255) UNIQUE,
  `jumlah_bayar` decimal,
  `tanggal_validasi` datetime,
  `tanggal_bayar` datetime,
  `status_bayar` varchar(255) COMMENT 'Lunas / Tertunda'
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
-- 5. RELASI (FOREIGN KEYS)
ALTER TABLE `spu_d`
ADD CONSTRAINT `fk_spu_d_spu_h` FOREIGN KEY (`id_sp`) REFERENCES `spu_h` (`id`) ON DELETE CASCADE;
ALTER TABLE `spu_h`
ADD CONSTRAINT `fk_spu_h_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `m_supplier` (`KodeSupplier`) ON UPDATE CASCADE;
ALTER TABLE `surat_jalan`
ADD CONSTRAINT `fk_sj_spu_h` FOREIGN KEY (`id_spu_h`) REFERENCES `spu_h` (`id`);
ALTER TABLE `berita_acara`
ADD CONSTRAINT `fk_ba_sj` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);
ALTER TABLE `laporan_kerja`
ADD CONSTRAINT `fk_lk_sj` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);
ALTER TABLE `pembayaran`
ADD CONSTRAINT `fk_bayar_sj` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`);
-- Default Users
INSERT IGNORE INTO `users` (`username`, `password`, `role`)
VALUES ('admin', MD5('adminrkz'), 'admin');
INSERT IGNORE INTO `users` (`username`, `password`, `role`)
VALUES ('louis', MD5('123456'), 'user');