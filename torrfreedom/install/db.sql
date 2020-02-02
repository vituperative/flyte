-- MySQL dump 10.17  Distrib 10.3.17-MariaDB, for debian-linux-gnueabihf (armv7l)
--
-- Host: localhost    Database: torrfreedom
-- ------------------------------------------------------
-- Server version	10.3.17-MariaDB-0+deb10u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avps`
--

DROP TABLE IF EXISTS `avps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avps` (
  `arg` varchar(20) NOT NULL DEFAULT '',
  `value_i` int(11) NOT NULL DEFAULT 0,
  `value_u` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`arg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avps`
--

LOCK TABLES `avps` WRITE;
/*!40000 ALTER TABLE `avps` DISABLE KEYS */;
INSERT INTO `avps` VALUES ('lastcleantime',0,1580335145);
/*!40000 ALTER TABLE `avps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `sort_index` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Movies',10),(2,'Music',20),(3,'TV',30),(4,'Games',40),(5,'Apps',50),(6,'Misc.',60),(8,'Pictures',80),(9,'Anime',90),(10,'Comics',100),(11,'Books',110),(13,'Music Vid.',120);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT 0,
  `torrent` int(10) unsigned NOT NULL DEFAULT 0,
  `added` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT 0,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB AUTO_INCREMENT=242979 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `peers`
--

DROP TABLE IF EXISTS `peers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peers` (
  `torrent` int(10) unsigned NOT NULL DEFAULT 0,
  `peer_id` binary(20) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ip` text NOT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT 0,
  `uploaded` bigint(40) unsigned NOT NULL DEFAULT 0,
  `downloaded` bigint(40) unsigned NOT NULL DEFAULT 0,
  `to_go` bigint(40) unsigned NOT NULL DEFAULT 0,
  `seeder` enum('yes','no') NOT NULL DEFAULT 'no',
  `started` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_action` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `connectable` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`torrent`,`peer_id`),
  KEY `torrent` (`torrent`),
  KEY `connectable` (`connectable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `torrents`
--

DROP TABLE IF EXISTS `torrents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` binary(20) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `save_as` varchar(255) NOT NULL DEFAULT '',
  `search_text` text NOT NULL,
  `descr` text NOT NULL,
  `ori_descr` text NOT NULL,
  `category` int(10) unsigned NOT NULL DEFAULT 0,
  `size` bigint(40) unsigned NOT NULL DEFAULT 0,
  `added` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `type` enum('single','multi') NOT NULL DEFAULT 'single',
  `numfiles` int(10) unsigned NOT NULL DEFAULT 0,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `hits` int(10) unsigned NOT NULL DEFAULT 0,
  `times_completed` int(10) unsigned NOT NULL DEFAULT 0,
  `last_action` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `visible` enum('yes','no') NOT NULL DEFAULT 'yes',
  `owner` int(10) unsigned NOT NULL DEFAULT 0,
  `seeders` int(10) unsigned NOT NULL DEFAULT 0,
  `leechers` int(10) unsigned NOT NULL DEFAULT 0,
  `comments` int(10) unsigned NOT NULL DEFAULT 0,
  `banned` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `owner` (`owner`),
  KEY `visible` (`visible`),
  KEY `category_visible` (`category`,`visible`),
  KEY `filename` (`filename`),
  KEY `name` (`name`),
  KEY `added` (`added`),
  KEY `last_action` (`last_action`),
  FULLTEXT KEY `ft_search` (`search_text`,`ori_descr`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `secret` varchar(20) NOT NULL DEFAULT '',
  `status` enum('pending','confirmed') NOT NULL DEFAULT 'pending',
  `added` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `admin` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status_added` (`status`,`added`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
