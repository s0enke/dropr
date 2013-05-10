CREATE TABLE IF NOT EXISTS `dropr_client` (
  `typ` enum('1','2') NOT NULL,
  `name` varchar(255) NOT NULL,
  `ts` bigint(20) unsigned NOT NULL,
  `payload` longblob NOT NULL,
  PRIMARY KEY (`name`,`typ`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;