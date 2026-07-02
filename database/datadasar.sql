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

-- Dumping structure for table hrd.datadasar
CREATE TABLE IF NOT EXISTS `datadasar` (
  `NIP` varchar(5) NOT NULL,
  `Nik_atas` varchar(5) NOT NULL,
  `NoRM` varchar(9) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `NamaBaptis` varchar(100) NOT NULL,
  `NamaPanggilan` varchar(20) NOT NULL,
  `NamaKTP` varchar(100) NOT NULL,
  `NamaIjasah` varchar(100) NOT NULL,
  `NamaAkte` varchar(100) NOT NULL,
  `AlamatKTP` varchar(100) NOT NULL,
  `NoKTP` varchar(20) NOT NULL,
  `NamaHubungan` varchar(35) NOT NULL,
  `AlamatHubungan` varchar(100) NOT NULL,
  `TelpHubungan` varchar(20) NOT NULL,
  `Hubungan` varchar(25) NOT NULL,
  `AlamatAsal` varchar(100) NOT NULL,
  `KotaKTP` varchar(30) NOT NULL,
  `PropinsiKTP` varchar(25) NOT NULL,
  `TelpAreaAsal` varchar(4) NOT NULL,
  `TelpAsal` varchar(15) NOT NULL,
  `AlamatSekarang` varchar(100) NOT NULL,
  `KotaSekarang` varchar(30) NOT NULL,
  `PropinsiSekarang` varchar(30) NOT NULL,
  `TelpAreaSekarang` varchar(4) NOT NULL,
  `TelpSekarang` varchar(15) NOT NULL,
  `TelpAreaLain` varchar(4) NOT NULL,
  `TelpLain` varchar(15) NOT NULL,
  `Hp` varchar(15) NOT NULL,
  `JenisKelamin` varchar(1) NOT NULL,
  `Agama` varchar(15) NOT NULL,
  `Permandian` date NOT NULL,
  `KotaLahir` varchar(30) NOT NULL,
  `TglLahir` date NOT NULL,
  `KotaLahirIjasah` varchar(30) NOT NULL,
  `TglLahirIjasah` date NOT NULL,
  `KotaLahirKTP` varchar(30) NOT NULL,
  `TglLahirKTP` date NOT NULL,
  `SukuBangsa` varchar(50) NOT NULL,
  `TglWNI` datetime NOT NULL,
  `Status` varchar(11) NOT NULL,
  `MasukRSTgl` date NOT NULL,
  `Asrama` varchar(25) NOT NULL,
  `Bagian` varchar(50) NOT NULL,
  `Foto` enum('True','False') NOT NULL,
  `Email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `Hidup` varchar(1) NOT NULL,
  `UserNIK` varchar(5) NOT NULL,
  `TimeDate` datetime NOT NULL,
  `Flag` varchar(1) NOT NULL,
  `KepalaKeluarga` varchar(1) NOT NULL,
  `Penilaian` varchar(1) NOT NULL,
  `Keaktifan` varchar(15) NOT NULL,
  `UserNIKEdit` varchar(5) NOT NULL,
  `TimeDateEdit` datetime NOT NULL,
  `TglKeluar` date NOT NULL,
  `AktifYa` varchar(25) NOT NULL,
  `AktifTidak` varchar(25) NOT NULL,
  `rmvirtual` varchar(10) NOT NULL,
  `tgldiangkat` date NOT NULL,
  `kredensial` varchar(2) NOT NULL,
  `level` int(11) NOT NULL,
  `golgaji` varchar(10) NOT NULL,
  `kodegaji` varchar(10) NOT NULL,
  `encrypt_pass` text NOT NULL,
  `nokpj` varchar(20) NOT NULL,
  `tglkpj` date NOT NULL,
  `nobpjs` varchar(20) NOT NULL,
  `ppkkarah` varchar(1) NOT NULL,
  `idketenagaan` int(11) NOT NULL,
  `jeniskyw` varchar(15) NOT NULL COMMENT 'CAKAR, SUSTER,DOKTER,KYW',
  `idfinger` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`NIP`),
  KEY `namanipalamat` (`Nama`,`NIP`,`AlamatKTP`),
  KEY `NIPpassword` (`NIP`,`password`),
  KEY `namapanggilan` (`NamaPanggilan`),
  KEY `namaktp` (`NamaKTP`),
  KEY `agama` (`Agama`),
  KEY `NoRM` (`NoRM`),
  KEY `AktifYa` (`AktifYa`),
  KEY `AktifTidak` (`AktifTidak`),
  KEY `rmvirtual` (`rmvirtual`),
  KEY `TglKeluar` (`TglKeluar`),
  KEY `NoKTP` (`NoKTP`),
  KEY `MasukRSTgl` (`MasukRSTgl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
