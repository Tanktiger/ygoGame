-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.1.66-0+squeeze1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL version:             7.0.0.4086
-- Date/time:                    2013-08-13 14:22:34
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping structure for table ygo.cards
CREATE TABLE IF NOT EXISTS `cards` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_en_alternate` varchar(255) DEFAULT NULL,
  `url` text,
  `pic_url` text,
  `type` varchar(255) DEFAULT NULL,
  `propertys` varchar(255) DEFAULT NULL,
  `attribute` varchar(255) DEFAULT NULL,
  `atk` smallint(6) DEFAULT NULL,
  `def` smallint(6) DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `effect_en` text,
  `effect_de` text,
  `code` int(11) DEFAULT NULL,
  `fusion_material` text,
  `material` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- Dumping data for table ygo.cards: ~0 rows (approximately)
DELETE FROM `cards`;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
