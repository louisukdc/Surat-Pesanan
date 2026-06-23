-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.27-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.17.0.7270
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table dbold.spu_d
CREATE TABLE IF NOT EXISTS `spu_d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sp` int(11) NOT NULL DEFAULT 0,
  `barang` varchar(90) NOT NULL DEFAULT '',
  `model` varchar(35) NOT NULL DEFAULT '',
  `merk` varchar(35) NOT NULL DEFAULT '',
  `spec` varchar(55) NOT NULL DEFAULT '',
  `qty` decimal(9,2) NOT NULL DEFAULT 0.00,
  `harga` decimal(9,2) NOT NULL DEFAULT 0.00,
  `disc` decimal(9,2) NOT NULL DEFAULT 0.00,
  `ppn` decimal(9,2) NOT NULL DEFAULT 0.00,
  `jumlah` decimal(9,2) NOT NULL DEFAULT 0.00,
  `date_created` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `id_sp` (`id_sp`)
) ENGINE=MyISAM AUTO_INCREMENT=325194 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbold.spu_h
CREATE TABLE IF NOT EXISTS `spu_h` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_permintaan` varchar(15) NOT NULL DEFAULT '0',
  `nama_lampiran` varchar(15) NOT NULL DEFAULT '0',
  `tgl_pesan` date NOT NULL,
  `id_supplier` varchar(6) NOT NULL COMMENT 'master_supplier',
  `no_penawaran` varchar(25) NOT NULL DEFAULT '',
  `tgl_penawaran` date NOT NULL DEFAULT '1900-01-01',
  `tgl_kirim` date NOT NULL,
  `gudang` varchar(3) NOT NULL DEFAULT '' COMMENT 'master unit',
  `jenis_bayar` varchar(6) NOT NULL COMMENT 'Tunai , Kredit',
  `keterangan` text NOT NULL,
  `user_created` varchar(10) DEFAULT NULL,
  `dtime_created` datetime NOT NULL,
  `user_acc` varchar(5) NOT NULL,
  `date_acc` datetime NOT NULL,
  `flag` varchar(1) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `tgl_pesan` (`tgl_pesan`),
  KEY `flag` (`flag`),
  KEY `gudang` (`gudang`)
) ENGINE=MyISAM AUTO_INCREMENT=56627 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
