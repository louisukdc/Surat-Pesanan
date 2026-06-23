-- --------------------------------------------------------
-- Host:                         db-svr.rkzsby.local
-- Server version:               5.5.59-log - Source distribution
-- Server OS:                    Linux
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

-- Dumping structure for table material.dap_sp_d
CREATE TABLE IF NOT EXISTS `dap_sp_d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sp` int(11) DEFAULT '0',
  `id_barang` int(11) DEFAULT '0',
  `id_kategori` int(9) DEFAULT '0',
  `id_satuan` int(9) DEFAULT '0',
  `id_merek` int(11) DEFAULT '0',
  `id_kelompok` mediumint(9) DEFAULT '0',
  `jumlah` decimal(9,2) DEFAULT '0.00',
  `terima` decimal(9,2) DEFAULT '0.00',
  `harga` decimal(9,2) DEFAULT '0.00',
  `disc` decimal(9,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id_sp` (`id_sp`),
  KEY `id_barang` (`id_barang`),
  KEY `id_kategori` (`id_kategori`),
  KEY `id_satuan` (`id_satuan`),
  KEY `id_merek` (`id_merek`),
  KEY `id_kelompok` (`id_kelompok`),
  KEY `terima` (`terima`)
) ENGINE=MyISAM AUTO_INCREMENT=325194 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table material.dap_sp_h
CREATE TABLE IF NOT EXISTS `dap_sp_h` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_pesan` date DEFAULT NULL,
  `tgl_kirim` date DEFAULT NULL,
  `gudang` varchar(3) DEFAULT NULL,
  `jenis_bayar` varchar(6) DEFAULT NULL,
  `supplier` varchar(6) DEFAULT NULL,
  `acc1` varchar(20) DEFAULT NULL,
  `acc2` varchar(20) DEFAULT NULL,
  `keterangan` text,
  `flag` varchar(1) DEFAULT 'n',
  `user` varchar(10) DEFAULT NULL,
  `dtime_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tgl_pesan` (`tgl_pesan`),
  KEY `supplier` (`supplier`),
  KEY `flag` (`flag`),
  KEY `gudang` (`gudang`)
) ENGINE=MyISAM AUTO_INCREMENT=56627 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
