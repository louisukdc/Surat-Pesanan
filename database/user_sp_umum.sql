-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.27-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.17.0.7270
-- --------------------------------------------------------
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET NAMES utf8 */
;
/*!50503 SET NAMES utf8mb4 */
;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */
;
/*!40103 SET TIME_ZONE='+00:00' */
;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;
-- Dumping structure for table dbold.m_menu
CREATE TABLE IF NOT EXISTS `m_menu` (
  `NoID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NamaMenu` varchar(996) NOT NULL,
  `Level0` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `Level1` int(10) unsigned NOT NULL DEFAULT 0,
  `Level2` int(10) unsigned NOT NULL DEFAULT 0,
  `url` text NOT NULL,
  `hint` text NOT NULL,
  `grup` int(10) NOT NULL,
  `padmenu` varchar(25) NOT NULL,
  `cetakpage` varchar(1) NOT NULL,
  `url2` text NOT NULL,
  `batal` varchar(1) NOT NULL,
  `direksi` varchar(1) NOT NULL,
  PRIMARY KEY (`NoID`),
  KEY `Level0` (`Level0`),
  KEY `Level1` (`Level1`),
  KEY `Level2` (`Level2`),
  KEY `batal` (`batal`),
  KEY `direksi` (`direksi`),
  KEY `grup` (`grup`) USING BTREE,
  KEY `NamaMenu` (`NamaMenu`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3443 DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'grup=10 and level0=2';
-- Data exporting was unselected.
-- Dumping structure for table dbold.m_user
CREATE TABLE IF NOT EXISTS `m_user` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `NIK` varchar(5) NOT NULL,
  `NoMenu` int(3) NOT NULL,
  `NamaUser` varchar(50) NOT NULL,
  `fidfile` varchar(2) NOT NULL,
  `fidcode` varchar(10) NOT NULL,
  `Tanda` varchar(1) NOT NULL,
  `Status` varchar(15) NOT NULL,
  `userdeleted` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `NIK` (`NIK`),
  KEY `NoMenu` (`NoMenu`) USING BTREE,
  KEY `userdeleted` (`userdeleted`)
) ENGINE = MyISAM AUTO_INCREMENT = 85081 DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;
-- Data exporting was unselected.
/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */
;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */
;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */
;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */
;