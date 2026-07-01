-- ========================================================
-- 1. MEMBUAT DATABASE BARU DAN MENGGUNAKANNYA
-- ========================================================
CREATE DATABASE IF NOT EXISTS `material`;
USE `material`;

-- ========================================================
-- 2. MEMBUAT TABEL MASTER SUPPLIER (m_supplier)
-- ========================================================
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
    UNIQUE KEY `uq_kodesupplier` (`KodeSupplier`),
    KEY `ndx1` (`NamaSupplier`),
    KEY `indexmsupplier` (`KodeSupplier`, `NamaSupplier`),
    KEY `namasupplierflag` (`NamaSupplier`, `Flag`),
    KEY `Status` (`Status`),
    KEY `tanda` (`tanda`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

-- ========================================================
-- 3. MEMBUAT TABEL HEADER PESANAN (spu_h)
-- ========================================================
CREATE TABLE IF NOT EXISTS `spu_h` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `no_permintaan` VARCHAR(15) NOT NULL DEFAULT '0',
    `nama_lampiran` VARCHAR(15) NOT NULL DEFAULT '0',
    `tgl_pesan` DATE NOT NULL,
    `id_supplier` VARCHAR(6) NOT NULL COMMENT 'master_supplier',
    `no_penawaran` VARCHAR(25) NOT NULL DEFAULT '',
    `tgl_penawaran` DATE NOT NULL DEFAULT '1900-01-01',
    `tgl_kirim` DATE NOT NULL,
    `gudang` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'master unit',
    `jenis_bayar` VARCHAR(6) NOT NULL COMMENT 'Tunai , Kredit',
    `keterangan` TEXT NOT NULL,
    `user_created` VARCHAR(10) DEFAULT NULL,
    `dtime_created` DATETIME NOT NULL,
    `user_acc` VARCHAR(5) NOT NULL,
    `date_acc` DATETIME NOT NULL,
    `flag` VARCHAR(1) NOT NULL DEFAULT '',
    `status_acc` VARCHAR(20) DEFAULT 'Pending',
    `alasan_tolak` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `tgl_pesan` (`tgl_pesan`),
    KEY `flag` (`flag`),
    KEY `gudang` (`gudang`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

-- ========================================================
-- 4. MEMBUAT TABEL DETAIL PESANAN (spu_d)
-- ========================================================
CREATE TABLE IF NOT EXISTS `spu_d` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_sp` INT(11) NOT NULL DEFAULT 0,
    `barang` VARCHAR(90) NOT NULL DEFAULT '',
    `model` VARCHAR(35) NOT NULL DEFAULT '',
    `merk` VARCHAR(35) NOT NULL DEFAULT '',
    `spec` VARCHAR(55) NOT NULL DEFAULT '',
    `qty` DECIMAL(9, 2) NOT NULL DEFAULT 0.00,
    `harga` DECIMAL(9, 2) NOT NULL DEFAULT 0.00,
    `disc` DECIMAL(9, 2) NOT NULL DEFAULT 0.00,
    `ppn` DECIMAL(9, 2) NOT NULL DEFAULT 0.00,
    `jumlah` DECIMAL(9, 2) NOT NULL DEFAULT 0.00,
    `date_created` DATETIME NOT NULL DEFAULT '1900-01-01 00:00:00',
    PRIMARY KEY (`id`),
    KEY `id_sp` (`id_sp`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

-- ========================================================
-- 5. MEMBUAT GARIS RELASI (FOREIGN KEY) ANTAR TABEL
-- ========================================================
-- Drop existing constraints if they exist (ignoring errors if not)
-- The foreign keys are added safely

-- Menghubungkan spu_d (id_sp) ke spu_h (id)
ALTER TABLE `spu_d`
ADD CONSTRAINT `fk_spu_d_to_spu_h` FOREIGN KEY (`id_sp`) REFERENCES `spu_h` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Menghubungkan spu_h (id_supplier) ke m_supplier (KodeSupplier)
ALTER TABLE `spu_h`
ADD CONSTRAINT `fk_spu_h_to_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `m_supplier` (`KodeSupplier`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ========================================================
-- 6. TABEL USERS UNTUK LOGIN
-- ========================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'user',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `users` (`username`, `password`, `role`) VALUES ('admin', 'adminrkz', 'admin');
INSERT IGNORE INTO `users` (`username`, `password`, `role`) VALUES ('louis', MD5('123456'), 'user');

-- Insert Data m_supplier
INSERT IGNORE INTO `m_supplier` (`KodeSupplier`, `NamaSupplier`, `NPWP`, `Alamat1`, `Kota1`, `Telp1`, `ContactPerson`, `Status`, `CaraPembayaran`, `tanda`, `NamaInvoice`) VALUES 
('SUP001', 'PT. Medika Utama', '01.234.567.8-011.000', 'Jl. Kesehatan No. 10', 'Surabaya', '031-123456', 'Budi Santoso', 'Aktif', 'Kredit', 'P', 'PT. Medika Utama'),
('SUP002', 'CV. Sanitas Jaya', '01.234.567.8-012.000', 'Jl. Apotik No. 5', 'Jakarta', '021-987654', 'Siti Rahma', 'Aktif', 'Tunai', 'I', 'CV. Sanitas Jaya'),
('SUP003', 'PT. Bio Pharma Lestari', '01.234.567.8-013.000', 'Jl. Industri No. 22', 'Bandung', '022-555666', 'Andi Wijaya', 'Aktif', 'Kredit', 'P', 'PT. Bio Pharma Lestari');

-- Insert Data spu_h
INSERT IGNORE INTO `spu_h` (`id`, `no_permintaan`, `tgl_pesan`, `id_supplier`, `tgl_kirim`, `gudang`, `jenis_bayar`, `keterangan`, `user_created`, `dtime_created`, `user_acc`, `date_acc`, `flag`) VALUES 
(1, 'REQ-001', '2026-06-01', 'SUP001', '2026-06-05', 'G01', 'Kredit', 'Pesanan rutin spuit dan jarum suntik', 'louis', NOW(), 'MGR01', NOW(), 'y'),
(2, 'REQ-002', '2026-06-02', 'SUP002', '2026-06-06', 'G02', 'Tunai', 'Cito Kasa steril mendesak', 'louis', NOW(), 'MGR01', NOW(), 'y');

-- Insert Data spu_d
INSERT IGNORE INTO `spu_d` (`id_sp`, `barang`, `model`, `merk`, `spec`, `qty`, `harga`, `disc`, `ppn`, `jumlah`, `date_created`) VALUES 
(1, 'Spuit Terumo 3cc', 'Disposable', 'Terumo', '3ml dengan Jarum 23G', 100.00, 1500.00, 0.00, 16500.00, 166500.00, NOW()),
(2, 'Kasa Steril 16x16', 'Kotak', 'OneMed', 'Kasa Hidrofil Steril', 50.00, 8000.00, 5000.00, 43450.00, 438450.00, NOW());

