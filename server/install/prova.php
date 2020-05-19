<?php
require "../config.php";
$connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$connection->exec(
<<<EOL
CREATE TABLE IF NOT EXISTS `certificati` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`codice` text NOT NULL,
`nome` text NOT NULL,
`interventi` text NOT NULL,
`url` text NOT NULL,
`file` text NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `esercitazioni` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`data` date NOT NULL,
`nome` varchar(999) NOT NULL,
`inizio` time NOT NULL,
`fine` time NOT NULL,
`personale` text NOT NULL,
`capo` text NOT NULL,
`luogo` text NOT NULL,
`note` text NOT NULL,
`dec` varchar(999) NOT NULL DEFAULT 'test',
`inseritoda` varchar(200) NOT NULL DEFAULT 'test',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `interventi` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`data` date NOT NULL,
`codice` text NOT NULL,
`uscita` time NOT NULL,
`rientro` time NOT NULL,
`capo` varchar(999) NOT NULL DEFAULT 'test',
`autisti` varchar(999) NOT NULL DEFAULT 'test',
`personale` varchar(999) NOT NULL DEFAULT 'test',
`luogo` varchar(999) NOT NULL DEFAULT 'test',
`note` varchar(999) NOT NULL DEFAULT 'test',
`tipo` text NOT NULL,
`incrementa` varchar(999) NOT NULL,
`inseritoda` varchar(200) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `intrusioni` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`pagina` varchar(999) COLLATE utf8mb4_unicode_ci NOT NULL,
`data` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`ora` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
`servervar` varchar(9999) COLLATE utf8mb4_unicode_ci NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`azione` varchar(100) NOT NULL,
`subisce` varchar(100) NOT NULL,
`agisce` varchar(100) NOT NULL,
`data` varchar(100) NOT NULL,
`ora` varchar(100) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `minuti` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`mese` enum('gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre') NOT NULL,
`anno` varchar(4) NOT NULL,
`list` mediumtext NOT NULL,
`a1` mediumtext NOT NULL,
`a2` mediumtext NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `tipo` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`nome` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `nometipologia` (`nome`(99))
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `vigili` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`nome` text CHARACTER SET utf8 NOT NULL,
`disponibile` tinyint(1) NOT NULL DEFAULT 0,
`caposquadra` tinyint(1) NOT NULL DEFAULT 0,
`autista` tinyint(1) NOT NULL DEFAULT 0,
`telefono` varchar(25) DEFAULT NULL,
`password` varchar(200) NOT NULL,
`password_hash` varchar(2000) NOT NULL,
`interventi` int(11) NOT NULL DEFAULT 0,
`esercitazioni` int(11) NOT NULL,
`online` tinyint(1) NOT NULL DEFAULT 0,
`online_time` int(11) NOT NULL,
`minuti_dispo` int(11) NOT NULL,
`immagine` varchar(1000) NOT NULL,
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `dbversion` (
`id` INT NOT NULL AUTO_INCREMENT,
`version` INT NOT NULL,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
PRIMARY KEY (`id`),
KEY `Id` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
EOL);