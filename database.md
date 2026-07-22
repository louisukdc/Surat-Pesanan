-- --------------------------------------------------------
-- Host:                         192.168.2.12
-- Server version:               10.11.14-MariaDB-0ubuntu0.24.04.1 - Ubuntu 24.04
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             12.19.0.7314
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for material
CREATE DATABASE IF NOT EXISTS `material` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci */;
USE `material`;

-- Dumping structure for table material.smerah_surat_merah
CREATE TABLE IF NOT EXISTS `smerah_surat_merah` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `TGLTODAY` date DEFAULT NULL,
  `PERMOHONAN` text DEFAULT NULL,
  `BAGIANDITUJU` text DEFAULT NULL,
  `BELUMKETERANGAN` text DEFAULT NULL,
  `ACCKETERANGAN` text DEFAULT NULL,
  `PROSESKETERANGAN` text DEFAULT NULL,
  `SELESAIKETERANGAN` text DEFAULT NULL,
  `BELUMTGL` date NOT NULL DEFAULT '1900-01-01',
  `ACCTGL` date NOT NULL DEFAULT '1900-01-01',
  `ACCJAM` time NOT NULL,
  `PROSESTGL` date NOT NULL DEFAULT '1900-01-01',
  `SELESAITGL` date NOT NULL DEFAULT '1900-01-01',
  `APPROVAL` varchar(15) DEFAULT NULL,
  `NOSP` varchar(15) DEFAULT NULL,
  `IDBAGIAN` varchar(50) DEFAULT NULL,
  `IDUSER` varchar(5) DEFAULT NULL,
  `KEMBALITGL` date NOT NULL DEFAULT '1900-01-01',
  `IDPENDUKUNG` varchar(5) NOT NULL,
  `ACC1` varchar(5) NOT NULL,
  `ACC2` varchar(5) NOT NULL,
  `KD_BAG_PEMOHON` varchar(5) NOT NULL,
  `KD_BAG_TERMOHON` varchar(5) NOT NULL,
  `TGLACC1` date NOT NULL DEFAULT '1900-01-01',
  `MIRM` varchar(2) NOT NULL,
  `USERCREATED` varchar(5) NOT NULL,
  `FLAG` varchar(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDPENDUKUNG` (`IDPENDUKUNG`),
  KEY `ACC1` (`ACC1`),
  KEY `ACC2` (`ACC2`),
  KEY `TGLTODAY` (`TGLTODAY`),
  KEY `KD_BAG_TERMOHON` (`KD_BAG_TERMOHON`),
  KEY `APPROVAL` (`APPROVAL`),
  KEY `IDUSER` (`IDUSER`),
  KEY `MIRM` (`MIRM`),
  KEY `USERCREATED` (`USERCREATED`),
  KEY `FLAG` (`FLAG`),
  KEY `SELESAITGL` (`SELESAITGL`),
  KEY `ACCTGL` (`ACCTGL`),
  FULLTEXT KEY `BAGIANDITUJU` (`BAGIANDITUJU`)
) ENGINE=MyISAM AUTO_INCREMENT=72987 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table material.smerah_surat_merah: 17 rows
INSERT INTO `smerah_surat_merah` (`ID`, `TGLTODAY`, `PERMOHONAN`, `BAGIANDITUJU`, `BELUMKETERANGAN`, `ACCKETERANGAN`, `PROSESKETERANGAN`, `SELESAIKETERANGAN`, `BELUMTGL`, `ACCTGL`, `ACCJAM`, `PROSESTGL`, `SELESAITGL`, `APPROVAL`, `NOSP`, `IDBAGIAN`, `IDUSER`, `KEMBALITGL`, `IDPENDUKUNG`, `ACC1`, `ACC2`, `KD_BAG_PEMOHON`, `KD_BAG_TERMOHON`, `TGLACC1`, `MIRM`, `USERCREATED`, `FLAG`) VALUES
	(72970, '2026-07-21', 'PEKERJAAN BARU', 'PEMBELIAN', '<p>Mohon ijin perbaikan lampu sorot atap lt.9 GSY yg mati 2pcs &amp; penggantian lampu PJU solarcell set di taman tengah depan Monika2</p>\n<p>mohon hubungi p.Trisno pt.persembahan bersama utama</p>', '', '', '', '2026-07-21', '2026-07-21', '00:00:00', '2026-07-21', '1900-01-01', 'ACC', '', 'TEKNIK DAN PERTUKANGAN', '03075', '1900-01-01', '', '02435', '02435', '', '91', '1900-01-01', '', '03075', ''),
	(72971, '2026-07-21', 'PERBAIKAN', '', '<p>Mohon perbaikan mesin humfrey perimetri poli mata GSY lt 3 karena tanggal di mesin tidak sesuai sehingga program alat salah mendeteksi umur,&nbsp; di mana pengaturan umur berpengaruh pada settingan trial lens liquid di mesin mengakibatkan hasil yang di keluarkan untuk pasien di beresiko kurang akurat.</p>\n<p>Atas perhatiannya terima kasih</p>', '', '', '', '2026-07-21', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'INSTALASI RAWAT JALAN', '03451', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '03451', ''),
	(72972, '2026-07-21', 'PERBAIKAN', 'RUMAH TANGGA', '<p>Meja bundar kayu Km. 109 =&gt; pinggiran nya mengelupas</p>', '<p>minta pak Imam, cari solusi yang lebih awet</p>', '', '', '2026-07-21', '2026-07-21', '14:45:16', '2026-07-21', '1900-01-01', 'ACC', '', 'UNIT ANAK & NICU', '03032', '1900-01-01', '', '02212', '02212', '', '66', '1900-01-01', '', '03032', ''),
	(72973, '2026-07-21', 'PERBAIKAN', 'TEKNIK DAN PERTUKANGAN', '<p>Mohon perbaikan lampu kamar anak anak TPA mati 1 buah&nbsp;</p>\n<p>Note : sudah disampaikan mas vander teknisi untuk perbaikan&nbsp;</p>', '', '', '', '2026-07-21', '2026-07-21', '14:45:33', '2026-07-21', '1900-01-01', 'ACC', '', 'KERUMAHTANGGAAN', '03000', '1900-01-01', '', '02212', '02212', '', '160', '1900-01-01', '', '03000', ''),
	(72974, '2026-07-21', 'PERBAIKAN', '', '<p>printer Kasir LX310-795 hasil cetak patah-patah sudah di cek oleh bp Pandu TIID</p>\n<p>Terima kasih</p>', '', '', '', '2026-07-21', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'INSTALASI RAWAT JALAN', '02556', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '03745', ''),
	(72975, '2026-07-21', 'PERBAIKAN', 'TEKNIK DAN PERTUKANGAN', '<p>Mohon memperbaiki mesin giling bubur saring, karena tidak bisa menyala</p>\n<p>Keterangan : Sudah dicek petugas Teknisi 21/7/2026</p>', '', '', '', '2026-07-21', '2026-07-21', '14:46:35', '2026-07-21', '1900-01-01', 'ACC', '', 'INSTALASI GIZI', '03060', '1900-01-01', '', '02212', '02212', '', '160', '1900-01-01', '', '03060', ''),
	(72976, '2026-07-21', 'PERBAIKAN', 'TEKNIK DAN PERTUKANGAN', '<p>Mohon memperbaiki kran air di ruang loket, karena air masih menetes ketika kran ditutup.</p>\n<p>Keterangan : sudah diperbaiki petugas pertukangan 20/7/2026</p>', '', '', '', '2026-07-21', '2026-07-21', '14:46:59', '2026-07-21', '1900-01-01', 'ACC', '', 'INSTALASI GIZI', '03060', '1900-01-01', '', '02212', '02212', '', '160', '1900-01-01', '', '03060', ''),
	(72977, '2026-07-21', 'PEKERJAAN BARU', '', '<p>Tolong tambahkan akses saya ke komplain kepada karyawan. Saya sudah mengajukan dan di acc tapi belum ditindak lanjuti</p>', '', '', '', '2026-07-21', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'BIDANG SUMBER DAYA MANUSIA', '03904', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '03904', ''),
	(72978, '2026-07-21', 'PERBAIKAN', '', '<p>Flow meter oksigen no GM 01010, Ngowos dan bola tidak mau turun.</p>', '', '', '', '2026-07-21', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PERAWATAN INTENSIF', '02880', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '02880', ''),
	(72979, '2026-07-21', 'PERBAIKAN', '', '<p>Lampu Bohlam K.8 mati 1 --&gt; sdh di gabti pak Tius Teknisi</p>', '', '', '', '2026-07-21', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PAVILIUN MONIKA', '01803', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '01803', ''),
	(72980, '2026-07-22', 'PERBAIKAN', '', '<p>Pada tanggal 22 Juli 2026 penutup exhaust fan di ruang pantry SDM pecah dan lepas (kondisi penutup sudah rapuh), mohon bantuan untuk diperbaiki. terima kasih.</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'BIDANG SUMBER DAYA MANUSIA', '03830', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '03830', ''),
	(72981, '2026-07-22', 'PEKERJAAN BARU', '', '<p>Mohon master pemeriksaan baru untuk mikrobiologi</p>\n<p>Pemeriksaan Mikroskopis PCP ( <em>Pneumocystis jirovecii</em> pneumonia ) dengan harga Rp. 175.000, -</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'INSTALASI LABORATORIUM', '02744', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '02744', ''),
	(72982, '2026-07-22', '', '', '<p>Lampu tengah kamar 46, sesuai ketentuan dinkes mohon disesuaikan&nbsp; pencahayaan nya sesuai standard 250 lux, utk lampu tidur pasien selama ini hanya ada 1 lampu saja, mohon dibuatkan masing-masing pasien 1 lampu tidur dengan pencahayaan sesuai standar 50 lux.</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PAVILIUN YOAKIM 3', '01660', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '01660', ''),
	(72983, '2026-07-22', '', '', '<p>Pintu kamar mandi kamar 46 masih bukaan ke dalam, sesuai ketentuan dinkes mohon di rubah menjadi bukaan ke luar</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PAVILIUN YOAKIM 3', '01660', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '01660', ''),
	(72984, '2026-07-22', 'PERBAIKAN', '', '<p>Mohon perbaikan wastafel di kafetaria ( dpan toilet umum)&nbsp;</p>\n<p>Kondisi : Goyang, sudah disampaikan ke mas vander untuk perbaikan&nbsp;</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'KERUMAHTANGGAAN', '03000', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '03000', ''),
	(72985, '2026-07-22', 'PEMELIHARAAN', '', '<p>mohon lampu tidur di kamar 32 bed 1,2 dan 3 di ganti dengan lampu tidur standart 50 lux, dan untuk lampu dalam kamar 32 di ganti dengan lampu standart 250 lux (sesuai dengan arahan dari DINKES)&nbsp;</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PAVILIUN YOAKIM 1', '02476', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '02476', ''),
	(72986, '2026-07-22', 'PEKERJAAN BARU', '', '<p>mohon pemasangan pegangan tangan pasien (disamping closed) di kamar mandi&nbsp; km.32&nbsp;</p>', '', '', '', '2026-07-22', '1900-01-01', '00:00:00', '1900-01-01', '1900-01-01', '', '', 'PAVILIUN YOAKIM 1', '02476', '1900-01-01', '', '', '', '', '', '1900-01-01', '', '02476', '');

-- Dumping structure for table material.smohon_pengadaan_d
CREATE TABLE IF NOT EXISTS `smohon_pengadaan_d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_m` int(11) NOT NULL,
  `jenis` int(11) NOT NULL,
  `subjenis` int(11) NOT NULL,
  `barang` text NOT NULL,
  `jumlah` varchar(35) NOT NULL,
  `spesifikasi` text NOT NULL,
  `catatan` text NOT NULL,
  `usercreated` varchar(5) NOT NULL,
  `datecreated` datetime NOT NULL,
  `flag` char(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `jenis` (`jenis`) USING BTREE,
  KEY `usercreated` (`usercreated`) USING BTREE,
  KEY `flag` (`flag`) USING BTREE,
  KEY `notiket` (`id_m`) USING BTREE,
  KEY `subjenis` (`subjenis`)
) ENGINE=MyISAM AUTO_INCREMENT=6880 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table material.smohon_pengadaan_d: 13 rows
INSERT INTO `smohon_pengadaan_d` (`id`, `id_m`, `jenis`, `subjenis`, `barang`, `jumlah`, `spesifikasi`, `catatan`, `usercreated`, `datecreated`, `flag`) VALUES
	(6866, 5528, 26, 9, 'Laptop Mobile E-RM Kamar Bedah', '1', 'MSI Modern 14 F13MG', 'Untuk penambahan laptop mobile E-RM kamar bedah di ruang pulih sadar. Saat ini belum tersedia laptop mobile di RR. Untuk kereta mobile menggunakan eksisting. Referensi surat permohonan nomor 26/05/B/15.', '03847', '2026-07-20 07:33:32', ''),
	(6868, 5529, 24, 10, 'DISPENSER SABUN CUCI TANGAN UNTUK REHAB MEDIS BARU ( LT.3 : 2, LT.5 :1)', '3 BUAH', 'KAPASITAS 200 ML  MATERIAL ABS. MEREK AZKO KRIBOW', 'WASTAFEL TITIK BARU', '03000', '2026-07-21 07:29:16', ''),
	(6869, 5530, 25, 9, 'kabel', '1 roll', 'kabel nymhy 3x0.75', 'u/ stok', '03219', '2026-07-21 08:25:34', ''),
	(6870, 5530, 25, 9, 'kabel', '1 roll', 'kabel nymhy 3x1.5', 'u/ stok', '03219', '2026-07-21 08:25:34', ''),
	(6871, 5531, 23, 9, 'tespen', '2 bj', ' ', 'u/ logistik', '03219', '2026-07-21 10:55:03', ''),
	(6872, 5531, 25, 9, 'baterai', '10 pcs', 'baterai bulat LR44', 'u/ stok', '03219', '2026-07-21 10:55:03', ''),
	(6873, 5531, 25, 9, 'baterai', '5 pcs', 'baterai bulat 1.55v G3-A', 'U/ STOK', '03219', '2026-07-21 10:55:03', ''),
	(6874, 5531, 25, 9, 'BATERAI', '10pcs', 'baterai 27A/12v', 'u/ stok', '03219', '2026-07-21 10:55:03', ''),
	(6875, 5531, 25, 9, 'steker', '5 bj', 'steker kombinasi on-off merk broco/krisbow', 'u/ stok', '03219', '2026-07-21 10:55:03', ''),
	(6876, 5532, 26, 10, 'Televisi 32 inch', '1', 'TV 32 inch utk disambung dgn USG ECHOCARDIOGRAPHY sehingga keluarga bisa melihat dan bisa dijelaskan lewat layar TV.', 'Perlu dicoba dulu dgn stok TV yg ada', '02556', '2026-07-22 08:54:20', ''),
	(6877, 5533, 23, 7, 'logo disabilitas', '1', 'dalam bentuk stiker atau tulisan utk kamar mandi kamar 46\r\n', 'sesuai ketentuan Dinkes', '01660', '2026-07-22 09:32:27', ''),
	(6878, 5534, 23, 10, 'CASE DAN PELINDUNG LAYAR TABLET ', 'MASING MASING 1 BUAH', 'CASE DAN PELINDUNG LAYAR TABLET\r\nTYPE SAMSUNG S10-LITE', 'TABLET GST LT 1 KLINIK ORTHOPAEDI\r\nBELUM ADA CASE PELINDUNG UNTUK TABLET DAN LAYARNYA.\r\nTERIMA KASIH.', '02944', '2026-07-22 09:33:30', ''),
	(6879, 5535, 23, 10, 'stiker ', '1', 'stiker disabilitas', 'stiker tempel untuk kamar mandi ', '02476', '2026-07-22 09:51:10', '');

-- Dumping structure for table material.smohon_pengadaan_m
CREATE TABLE IF NOT EXISTS `smohon_pengadaan_m` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notiket` varchar(12) NOT NULL,
  `tanggal` date NOT NULL,
  `bagian` varchar(50) NOT NULL,
  `diminta` varchar(50) NOT NULL,
  `disetujui` varchar(20) NOT NULL,
  `tgl_setuju` date NOT NULL,
  `setuju` varchar(1) NOT NULL,
  `accsetuju` varchar(5) NOT NULL,
  `didukung` varchar(50) NOT NULL,
  `diproses` varchar(20) NOT NULL,
  `tgl_proses` date NOT NULL,
  `proses` varchar(1) NOT NULL,
  `accproses` varchar(5) NOT NULL,
  `batalacc` varchar(100) NOT NULL,
  `dateproses` datetime NOT NULL,
  `catatanproses` text NOT NULL,
  `level3` varchar(1) NOT NULL,
  `acc3` varchar(5) NOT NULL,
  `date3` datetime NOT NULL,
  `level2` varchar(1) NOT NULL,
  `acc2` varchar(5) NOT NULL,
  `date2` datetime NOT NULL,
  `level1` varchar(1) NOT NULL,
  `acc1` varchar(5) NOT NULL,
  `date1` datetime NOT NULL,
  `level0` varchar(1) NOT NULL,
  `acc0` varchar(5) NOT NULL,
  `date0` datetime NOT NULL,
  `proses_ips` varchar(1) NOT NULL DEFAULT '',
  `acc_ips` varchar(5) NOT NULL,
  `date_ips` datetime NOT NULL,
  `diarahkan` varchar(50) NOT NULL,
  `kddiarahkan` varchar(5) NOT NULL,
  `usercreated` varchar(5) NOT NULL,
  `datecreated` datetime NOT NULL,
  `tglselesai` datetime NOT NULL,
  `closing` varchar(5) NOT NULL,
  `dateclosing` datetime NOT NULL,
  `mirm` varchar(2) NOT NULL,
  `flag` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usercreated` (`usercreated`),
  KEY `flag` (`flag`),
  KEY `notiket` (`notiket`),
  KEY `kddiarahkan` (`kddiarahkan`),
  KEY `tglselesai` (`tglselesai`),
  KEY `closing` (`closing`),
  KEY `mirm` (`mirm`),
  KEY `bagian` (`bagian`)
) ENGINE=MyISAM AUTO_INCREMENT=5536 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table material.smohon_pengadaan_m: 8 rows
INSERT INTO `smohon_pengadaan_m` (`id`, `notiket`, `tanggal`, `bagian`, `diminta`, `disetujui`, `tgl_setuju`, `setuju`, `accsetuju`, `didukung`, `diproses`, `tgl_proses`, `proses`, `accproses`, `batalacc`, `dateproses`, `catatanproses`, `level3`, `acc3`, `date3`, `level2`, `acc2`, `date2`, `level1`, `acc1`, `date1`, `level0`, `acc0`, `date0`, `proses_ips`, `acc_ips`, `date_ips`, `diarahkan`, `kddiarahkan`, `usercreated`, `datecreated`, `tglselesai`, `closing`, `dateclosing`, `mirm`, `flag`) VALUES
	(5528, '26/07/B/63', '2026-07-20', 'INFRAKSTRUKTUR & PERANGKAT TEKNIK INFORMASI', 'ADIYATMA PANDU WIJAYANTO, S.Kom', '02240', '2026-07-20', '', '', '', 'DIR UAK', '2026-07-21', 'Y', '02212', '', '2026-07-21 15:08:36', '', '', '', '0000-00-00 00:00:00', 'Y', '03847', '2026-07-20 07:33:32', 'Y', '02240', '2026-07-20 13:41:52', 'Y', '02212', '2026-07-21 15:08:36', 'Y', '02212', '2026-07-21 15:08:36', 'PEMBELIAN', '91', '03847', '2026-07-20 07:33:32', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5529, '26/07/B/64', '2026-07-21', 'KERUMAHTANGGAAN', 'LOURY YHALESNITA TUNGGADEWI, S.T.', '', '0000-00-00', '', '', '', 'DIR UAK', '2026-07-21', 'Y', '02212', '', '2026-07-21 15:09:12', '', '', '', '0000-00-00 00:00:00', 'Y', '03000', '2026-07-21 07:29:16', 'Y', '03000', '2026-07-21 07:29:16', 'Y', '02212', '2026-07-21 15:09:12', 'Y', '02212', '2026-07-21 15:09:12', 'PEMBELIAN', '91', '03000', '2026-07-21 07:29:16', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5530, '26/07/B/65', '2026-07-21', 'MANAJEMEN LOGISTIK TERPADU', 'AMALIA DWI JAYANTI', '', '2026-07-21', '', '', '', 'DIR UAK', '2026-07-21', 'Y', '02212', '', '2026-07-21 15:09:26', '', '', '', '0000-00-00 00:00:00', 'Y', '02715', '2026-07-21 12:14:07', 'Y', '02715', '2026-07-21 12:14:07', 'Y', '02212', '2026-07-21 15:09:26', 'Y', '02212', '2026-07-21 15:09:26', 'PEMBELIAN', '91', '03219', '2026-07-21 08:25:34', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5531, '26/07/B/66', '2026-07-21', 'MANAJEMEN LOGISTIK TERPADU', 'AMALIA DWI JAYANTI', '', '2026-07-21', '', '', '', 'DIR UAK', '2026-07-21', 'Y', '02212', '', '2026-07-21 15:09:41', '', '', '', '0000-00-00 00:00:00', 'Y', '02715', '2026-07-21 12:14:13', 'Y', '02715', '2026-07-21 12:14:13', 'Y', '02212', '2026-07-21 15:09:41', 'Y', '02212', '2026-07-21 15:09:41', 'PEMBELIAN', '91', '03219', '2026-07-21 10:55:03', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5532, '26/07/B/67', '2026-07-22', 'INSTALASI RAWAT JALAN', 'dr. JULIANA SANDRAWATI, M.Kes', '', '0000-00-00', '', '', '', '', '0000-00-00', '', '', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00', 'Y', '02556', '2026-07-22 08:54:20', 'Y', '02556', '2026-07-22 08:54:20', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '02556', '2026-07-22 08:54:20', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5533, '26/07/B/68', '2026-07-22', 'PAVILIUN YOAKIM 3', 'ANDRIANI WYDIASTUTIE, S.Kep., Ners.', '', '0000-00-00', '', '', '', '', '0000-00-00', '', '', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00', 'Y', '01660', '2026-07-22 09:29:57', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '01660', '2026-07-22 09:29:57', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5534, '26/07/B/69', '2026-07-22', 'INSTALASI RAWAT JALAN', 'ANIK DWIJAYANTI', '', '0000-00-00', '', '', '', '', '0000-00-00', '', '', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '02944', '2026-07-22 09:33:30', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', ''),
	(5535, '26/07/B/70', '2026-07-22', 'PAVILIUN YOAKIM 1', 'MARIA DEWI NUGRAHA, S.Kep., NERS', '', '0000-00-00', '', '', '', '', '0000-00-00', '', '', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00', 'Y', '02476', '2026-07-22 09:47:08', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '', '', '02476', '2026-07-22 09:47:08', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
