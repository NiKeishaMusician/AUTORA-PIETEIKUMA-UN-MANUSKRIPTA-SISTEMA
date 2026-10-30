-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for izdevnieciba
CREATE DATABASE IF NOT EXISTS `izdevnieciba` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `izdevnieciba`;

-- Dumping structure for table izdevnieciba.lietotaji
CREATE TABLE IF NOT EXISTS `lietotaji` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lietotajvards` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paroles_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `izveidots` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lietotajvards` (`lietotajvards`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table izdevnieciba.pieteikumi
CREATE TABLE IF NOT EXISTS `pieteikumi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pieteikuma_nr` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vards` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uzvards` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `epasts` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `talrunis` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nosaukums` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zanrs` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apraksts` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lappusu_skaits` int NOT NULL,
  `tiraza` int NOT NULL,
  `izdosanas_laiks` date NOT NULL,
  `faila_nosaukums` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faila_cels` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sanemts_datums` datetime DEFAULT CURRENT_TIMESTAMP,
  `statuss` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Saņemts',
  `statuss_mainits` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pieteikuma_nr` (`pieteikuma_nr`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table izdevnieciba.sazina
CREATE TABLE IF NOT EXISTS `sazina` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pieteikuma_id` int NOT NULL,
  `datums` datetime NOT NULL,
  `veids` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kopsavilkums` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nakama_darbiba` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nakama_darbiba_datums` date DEFAULT NULL,
  `izveidots` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pieteikuma_id` (`pieteikuma_id`),
  CONSTRAINT `sazina_ibfk_1` FOREIGN KEY (`pieteikuma_id`) REFERENCES `pieteikumi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
