-- SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";
-- --------------------------------------------------------

--
-- Table structure for table `migrate_sites`
--

CREATE TABLE IF NOT EXISTS `migrate_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step` tinyint NOT NULL DEFAULT '0',
  `url` varchar(75) NOT NULL,
  `new_url` varchar(75) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


--
-- Table structure for table `migrate_content`
--

CREATE TABLE IF NOT EXISTS `migrate_content` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(1000) NULL,
  `page` varchar(1000) NOT NULL,
  `html` longtext NOT NULL,
  `site` int(11) NOT NULL,
  `crawled` tinyint NOT NULL DEFAULT '1',
  `content` longtext DEFAULT NULL,
  `wash` longtext DEFAULT NULL,
  `tidy` longtext DEFAULT NULL,
  `clean` longtext DEFAULT NULL,
  `ready` longtext DEFAULT NULL,
  `page_slug` varchar(50) DEFAULT NULL,
  `page_parent` int(11) NOT NULL '0',
  `deleted` tinyint NOT NULL DEFAULT '0'
  PRIMARY KEY (`id`),
  INDEX (`page`),
  INDEX (`page_slug`),
  KEY `fk_sites` (`site`),
  CONSTRAINT `fk_sites` FOREIGN KEY (`site`) REFERENCES `migrate_sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


--
-- Table structure for table `migrate_users`
--

CREATE TABLE IF NOT EXISTS `migrate_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `mail` varchar(255) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX (`mail`),
  INDEX (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2;

--
-- Dumping data for table `migrate_users`
--

INSERT INTO `migrate_users` (`id`, `name`, `mail`, `username`, `password`, `lastlogin`, `level`) VALUES
(1, NULL, 'admin@example.com', 'admin', 'password', '2013-05-22 13:37:00', 2);