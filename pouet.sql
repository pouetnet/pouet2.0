-- MariaDB dump 10.19  Distrib 10.11.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- ------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `affiliatedboards`
--

DROP TABLE IF EXISTS `affiliatedboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `affiliatedboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board` int(10) NOT NULL DEFAULT 0,
  `group` int(10) NOT NULL DEFAULT 0,
  `type` enum('WHQ','member','EHQ','USHQ','HQ','dist') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `board` (`board`),
  KEY `group` (`group`),
  CONSTRAINT `affiliatedboards_ibfk_1` FOREIGN KEY (`board`) REFERENCES `boards` (`id`),
  CONSTRAINT `affiliatedboards_ibfk_2` FOREIGN KEY (`group`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `affiliatedprods`
--

DROP TABLE IF EXISTS `affiliatedprods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `affiliatedprods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original` int(10) DEFAULT 0,
  `derivative` int(10) DEFAULT 0,
  `type` enum('port','final','remix','pack','related','sequel') DEFAULT 'port',
  PRIMARY KEY (`id`),
  KEY `original` (`original`),
  KEY `derivative` (`derivative`),
  CONSTRAINT `affiliatedprods_ibfk_1` FOREIGN KEY (`original`) REFERENCES `prods` (`id`),
  CONSTRAINT `affiliatedprods_ibfk_2` FOREIGN KEY (`derivative`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awards`
--

DROP TABLE IF EXISTS `awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodID` int(10) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `awardType` enum('winner','nominee') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prodID_categoryID` (`prodID`,`categoryID`),
  KEY `prodID` (`prodID`),
  KEY `categoryID` (`categoryID`),
  CONSTRAINT `awards_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`),
  CONSTRAINT `awards_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `awards_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awards_categories`
--

DROP TABLE IF EXISTS `awards_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awards_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series` varchar(64) NOT NULL,
  `category` varchar(64) NOT NULL,
  `cssClass` varchar(64) NOT NULL,
  `year` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2007`
--

DROP TABLE IF EXISTS `awardscand_2007`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2007` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT 0,
  `cat2` int(10) unsigned NOT NULL DEFAULT 0,
  `cat3` int(10) unsigned NOT NULL DEFAULT 0,
  `cat4` int(10) unsigned NOT NULL DEFAULT 0,
  `cat5` int(10) unsigned NOT NULL DEFAULT 0,
  `cat6` int(10) unsigned NOT NULL DEFAULT 0,
  `cat7` int(10) unsigned NOT NULL DEFAULT 0,
  `cat8` int(10) unsigned NOT NULL DEFAULT 0,
  `cat9` int(10) unsigned NOT NULL DEFAULT 0,
  `cat10` int(10) unsigned NOT NULL DEFAULT 0,
  `cat11` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2008`
--

DROP TABLE IF EXISTS `awardscand_2008`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2008` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT 0,
  `cat2` int(10) unsigned NOT NULL DEFAULT 0,
  `cat3` int(10) unsigned NOT NULL DEFAULT 0,
  `cat4` int(10) unsigned NOT NULL DEFAULT 0,
  `cat5` int(10) unsigned NOT NULL DEFAULT 0,
  `cat6` int(10) unsigned NOT NULL DEFAULT 0,
  `cat7` int(10) unsigned NOT NULL DEFAULT 0,
  `cat8` int(10) unsigned NOT NULL DEFAULT 0,
  `cat9` int(10) unsigned NOT NULL DEFAULT 0,
  `cat10` int(10) unsigned NOT NULL DEFAULT 0,
  `cat11` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2010`
--

DROP TABLE IF EXISTS `awardscand_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2010` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT 0,
  `cat2` int(10) unsigned NOT NULL DEFAULT 0,
  `cat3` int(10) unsigned NOT NULL DEFAULT 0,
  `cat4` int(10) unsigned NOT NULL DEFAULT 0,
  `cat5` int(10) unsigned NOT NULL DEFAULT 0,
  `cat6` int(10) unsigned NOT NULL DEFAULT 0,
  `cat7` int(10) unsigned NOT NULL DEFAULT 0,
  `cat8` int(10) unsigned NOT NULL DEFAULT 0,
  `cat9` int(10) unsigned NOT NULL DEFAULT 0,
  `cat10` int(10) unsigned NOT NULL DEFAULT 0,
  `cat11` int(10) unsigned NOT NULL DEFAULT 0,
  `cat12` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2011`
--

DROP TABLE IF EXISTS `awardscand_2011`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2011` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT 0,
  `cat2` int(10) unsigned NOT NULL DEFAULT 0,
  `cat3` int(10) unsigned NOT NULL DEFAULT 0,
  `cat4` int(10) unsigned NOT NULL DEFAULT 0,
  `cat5` int(10) unsigned NOT NULL DEFAULT 0,
  `cat6` int(10) unsigned NOT NULL DEFAULT 0,
  `cat7` int(10) unsigned NOT NULL DEFAULT 0,
  `cat8` int(10) unsigned NOT NULL DEFAULT 0,
  `cat9` int(10) unsigned NOT NULL DEFAULT 0,
  `cat10` int(10) unsigned NOT NULL DEFAULT 0,
  `cat11` int(10) unsigned NOT NULL DEFAULT 0,
  `cat12` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardssuggestions_categories`
--

DROP TABLE IF EXISTS `awardssuggestions_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardssuggestions_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventID` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `eventID` (`eventID`),
  CONSTRAINT `awardssuggestions_categories_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `awardssuggestions_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardssuggestions_events`
--

DROP TABLE IF EXISTS `awardssuggestions_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardssuggestions_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `votingStartDate` date NOT NULL,
  `votingEndDate` date NOT NULL,
  `eligibleYear` smallint(6) NOT NULL,
  `eligibleTypes` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardssuggestions_votes`
--

DROP TABLE IF EXISTS `awardssuggestions_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardssuggestions_votes` (
  `userID` int(10) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `prodID` int(10) NOT NULL,
  UNIQUE KEY `userID_categoryID_prodID` (`userID`,`categoryID`,`prodID`),
  KEY `categoryID` (`categoryID`),
  KEY `prodID` (`prodID`),
  CONSTRAINT `awardssuggestions_votes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  CONSTRAINT `awardssuggestions_votes_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `awardssuggestions_categories` (`id`),
  CONSTRAINT `awardssuggestions_votes_ibfk_3` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bbs_posts`
--

DROP TABLE IF EXISTS `bbs_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bbs_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` int(10) NOT NULL DEFAULT 0,
  `post` text NOT NULL,
  `author` int(10) NOT NULL DEFAULT 0,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `topic` (`topic`),
  KEY `idx_author` (`author`),
  KEY `idx_added` (`added`),
  FULLTEXT KEY `post` (`post`),
  CONSTRAINT `bbs_posts_ibfk_1` FOREIGN KEY (`topic`) REFERENCES `bbs_topics` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='the bbs posts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bbs_topics`
--

DROP TABLE IF EXISTS `bbs_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bbs_topics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) NOT NULL,
  `category` enum('general','gfx','code','music','parties','offtopic','residue') DEFAULT NULL,
  `lastpost` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userlastpost` int(10) NOT NULL DEFAULT 0,
  `count` int(10) unsigned NOT NULL DEFAULT 0,
  `firstpost` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userfirstpost` int(10) NOT NULL DEFAULT 0,
  `closed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_lastpost` (`lastpost`),
  KEY `userlastpost` (`userlastpost`),
  KEY `userfirstpost` (`userfirstpost`),
  CONSTRAINT `bbs_topics_ibfk_1` FOREIGN KEY (`userlastpost`) REFERENCES `users` (`id`),
  CONSTRAINT `bbs_topics_ibfk_2` FOREIGN KEY (`userfirstpost`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='the bbs topics';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boards`
--

DROP TABLE IF EXISTS `boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boards` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sysop` varchar(255) NOT NULL,
  `started` date DEFAULT NULL,
  `closed` date DEFAULT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `telnetip` varchar(255) NOT NULL,
  `addedUser` int(10) NOT NULL DEFAULT 0,
  `addedDate` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `addedUser` (`addedUser`),
  CONSTRAINT `boards_ibfk_1` FOREIGN KEY (`addedUser`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boards_ads`
--

DROP TABLE IF EXISTS `boards_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boards_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `board` int(10) NOT NULL DEFAULT 0,
  `added` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boards_platforms`
--

DROP TABLE IF EXISTS `boards_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boards_platforms` (
  `board` int(10) NOT NULL DEFAULT 0,
  `platform` int(10) NOT NULL DEFAULT 0,
  KEY `bbsb` (`board`),
  KEY `bbs` (`board`,`platform`),
  KEY `bbspl` (`platform`),
  CONSTRAINT `boards_platforms_ibfk_1` FOREIGN KEY (`platform`) REFERENCES `platforms` (`id`),
  CONSTRAINT `boards_platforms_ibfk_2` FOREIGN KEY (`board`) REFERENCES `boards` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boardsaka`
--

DROP TABLE IF EXISTS `boardsaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boardsaka` (
  `board1` int(10) unsigned NOT NULL DEFAULT 0,
  `board2` int(10) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `buttons`
--

DROP TABLE IF EXISTS `buttons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buttons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('affiliates','we like...','powered by...','link us !') NOT NULL DEFAULT 'affiliates',
  `img` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `dead` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='les boutons sur pou';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cdc`
--

DROP TABLE IF EXISTS `cdc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `which` int(10) NOT NULL DEFAULT 0,
  `addedDate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`which`),
  KEY `id_2` (`id`,`which`,`addedDate`),
  KEY `which` (`which`),
  CONSTRAINT `cdc_ibfk_1` FOREIGN KEY (`which`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='coups de coeur';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `which` int(10) NOT NULL DEFAULT 0,
  `who` int(10) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `rating` tinyint(2) NOT NULL DEFAULT 0,
  `addedDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `which` (`which`),
  KEY `who` (`who`),
  KEY `rating` (`rating`),
  KEY `quand` (`addedDate`),
  KEY `whichwho` (`who`,`which`),
  KEY `which_quand` (`which`,`addedDate`),
  FULLTEXT KEY `comment` (`comment`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`which`) REFERENCES `prods` (`id`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`who`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compotypes`
--

DROP TABLE IF EXISTS `compotypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compotypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componame` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credits`
--

DROP TABLE IF EXISTS `credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `role` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prodID_userID` (`prodID`,`userID`),
  KEY `userID` (`userID`),
  CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`),
  CONSTRAINT `credits_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `downloadlinks`
--

DROP TABLE IF EXISTS `downloadlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downloadlinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT 0,
  `type` varchar(64) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_type` (`prod`,`type`),
  UNIQUE KEY `prod_link` (`prod`,`link`),
  KEY `dl_prod` (`prod`),
  KEY `dl_prodtype` (`prod`),
  CONSTRAINT `downloadlinks_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faq`
--

DROP TABLE IF EXISTS `faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` enum('welcome','demos','general','pouet 2.0','syndication','BB Code') NOT NULL DEFAULT 'general',
  `deprecated` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='the pou';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gloperator_log`
--

DROP TABLE IF EXISTS `gloperator_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloperator_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gloperatorid` int(11) NOT NULL,
  `action` text NOT NULL,
  `itemid` int(11) NOT NULL,
  `itemType` enum('prod','group','party','topic','board') NOT NULL,
  `additionalData` text NOT NULL,
  `date` datetime /* mariadb-5.3 */ NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gloperatorid` (`gloperatorid`),
  CONSTRAINT `gloperator_log_ibfk_1` FOREIGN KEY (`gloperatorid`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(8) NOT NULL,
  `disambiguation` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL,
  `addedUser` int(10) NOT NULL DEFAULT 1,
  `addedDate` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views` int(10) unsigned DEFAULT NULL,
  `csdb` int(10) unsigned NOT NULL DEFAULT 0,
  `zxdemo` int(10) unsigned NOT NULL DEFAULT 0,
  `demozoo` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`addedUser`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`addedUser`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupsaka`
--

DROP TABLE IF EXISTS `groupsaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupsaka` (
  `group1` int(10) unsigned NOT NULL DEFAULT 0,
  `group2` int(10) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `quand` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_items`
--

DROP TABLE IF EXISTS `list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_items` (
  `list` int(10) unsigned NOT NULL DEFAULT 0,
  `itemid` int(10) unsigned NOT NULL DEFAULT 0,
  `type` enum('user','prod','group','party') NOT NULL DEFAULT 'prod',
  UNIQUE KEY `list_itemid_type` (`list`,`itemid`,`type`),
  CONSTRAINT `list_items_ibfk_1` FOREIGN KEY (`list`) REFERENCES `lists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_maintainers`
--

DROP TABLE IF EXISTS `list_maintainers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_maintainers` (
  `listID` int(10) unsigned NOT NULL,
  `userID` int(10) NOT NULL,
  KEY `listID` (`listID`),
  KEY `userID` (`userID`),
  CONSTRAINT `list_maintainers_ibfk_1` FOREIGN KEY (`listID`) REFERENCES `lists` (`id`),
  CONSTRAINT `list_maintainers_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lists`
--

DROP TABLE IF EXISTS `lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `addedUser` int(10) NOT NULL DEFAULT 0,
  `addedDate` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `owner` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`),
  KEY `addedUser` (`addedUser`),
  CONSTRAINT `lists_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`id`),
  CONSTRAINT `lists_ibfk_2` FOREIGN KEY (`addedUser`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logos`
--

DROP TABLE IF EXISTS `logos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `author1` int(10) NOT NULL DEFAULT 0,
  `author2` int(10) DEFAULT 0,
  `vote_count` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `author1` (`author1`),
  KEY `author2` (`author2`),
  CONSTRAINT `logos_ibfk_1` FOREIGN KEY (`author1`) REFERENCES `users` (`id`),
  CONSTRAINT `logos_ibfk_2` FOREIGN KEY (`author2`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logos_votes`
--

DROP TABLE IF EXISTS `logos_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logos_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logo` int(10) NOT NULL DEFAULT 0,
  `user` int(10) NOT NULL DEFAULT 0,
  `vote` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`vote`),
  KEY `user_2` (`user`,`vote`,`logo`),
  KEY `logo` (`logo`),
  CONSTRAINT `logos_votes_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  CONSTRAINT `logos_votes_ibfk_2` FOREIGN KEY (`logo`) REFERENCES `logos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='logos ratings given by users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modification_requests`
--

DROP TABLE IF EXISTS `modification_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modification_requests` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `requestType` varchar(32) NOT NULL,
  `itemID` int(10) NOT NULL,
  `itemType` enum('prod','group','party') NOT NULL,
  `requestBlob` text NOT NULL,
  `requestDate` datetime /* mariadb-5.3 */ NOT NULL,
  `userID` int(10) NOT NULL,
  `gloperatorID` int(10) DEFAULT NULL,
  `approved` tinyint(4) DEFAULT NULL,
  `comment` text NOT NULL DEFAULT '',
  `approveDate` datetime /* mariadb-5.3 */ DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prodid` (`itemID`),
  KEY `approved` (`approved`),
  KEY `userID` (`userID`),
  KEY `gloperatorID` (`gloperatorID`),
  CONSTRAINT `modification_requests_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  CONSTRAINT `modification_requests_ibfk_2` FOREIGN KEY (`gloperatorID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `quand` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `who` int(10) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newstickers`
--

DROP TABLE IF EXISTS `newstickers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newstickers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickerCode` varchar(256) NOT NULL,
  `html` text NOT NULL,
  `class` varchar(64) NOT NULL,
  `expires` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nfos`
--

DROP TABLE IF EXISTS `nfos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nfos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT 0,
  `user` int(10) NOT NULL DEFAULT 0,
  `added` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `prod` (`prod`),
  KEY `user` (`user`),
  CONSTRAINT `nfos_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  CONSTRAINT `nfos_ibfk_2` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ojnews`
--

DROP TABLE IF EXISTS `ojnews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ojnews` (
  `id` int(10) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `quand` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `authorid` int(11) NOT NULL DEFAULT 0,
  `authornick` varchar(255) NOT NULL,
  `authorgroup` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oldnicks`
--

DROP TABLE IF EXISTS `oldnicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oldnicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(10) NOT NULL,
  `nick` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `oldnicks_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oneliner`
--

DROP TABLE IF EXISTS `oneliner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oneliner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(303) NOT NULL,
  `who` int(10) NOT NULL DEFAULT 0,
  `addedDate` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `who` (`who`),
  CONSTRAINT `oneliner_ibfk_1` FOREIGN KEY (`who`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `othernfos`
--

DROP TABLE IF EXISTS `othernfos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `othernfos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `refid` int(10) unsigned NOT NULL DEFAULT 0,
  `type` enum('group','bbs') DEFAULT NULL,
  `added` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parties`
--

DROP TABLE IF EXISTS `parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL,
  `addedUser` int(10) NOT NULL DEFAULT 0,
  `addedDate` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `added` (`addedUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partiesaka`
--

DROP TABLE IF EXISTS `partiesaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partiesaka` (
  `party1` int(10) unsigned NOT NULL DEFAULT 0,
  `party2` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`party1`,`party2`),
  KEY `party1` (`party1`),
  KEY `party2` (`party2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partylinks`
--

DROP TABLE IF EXISTS `partylinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partylinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `party` int(10) NOT NULL DEFAULT 0,
  `year` int(10) unsigned NOT NULL DEFAULT 0,
  `download` varchar(255) NOT NULL,
  `csdb` int(10) unsigned NOT NULL DEFAULT 0,
  `zxdemo` int(10) unsigned NOT NULL DEFAULT 0,
  `demozoo` int(10) unsigned DEFAULT NULL,
  `slengpung` int(10) unsigned NOT NULL DEFAULT 0,
  `artcity` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `party` (`party`),
  CONSTRAINT `partylinks_ibfk_1` FOREIGN KEY (`party`) REFERENCES `parties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `platforms`
--

DROP TABLE IF EXISTS `platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platforms` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prodotherparty`
--

DROP TABLE IF EXISTS `prodotherparty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodotherparty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT 0,
  `party` int(10) NOT NULL DEFAULT 0,
  `party_year` int(10) unsigned NOT NULL DEFAULT 0,
  `party_place` int(10) unsigned NOT NULL DEFAULT 0,
  `party_compo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prod` (`prod`),
  KEY `partyyear` (`party`,`party_year`),
  KEY `party_compo` (`party_compo`),
  CONSTRAINT `prodotherparty_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`),
  CONSTRAINT `prodotherparty_ibfk_2` FOREIGN KEY (`party`) REFERENCES `parties` (`id`),
  CONSTRAINT `prodotherparty_ibfk_3` FOREIGN KEY (`party_compo`) REFERENCES `compotypes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods`
--

DROP TABLE IF EXISTS `prods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `download` varchar(255) NOT NULL,
  `releaseDate` date DEFAULT NULL COMMENT 'release date',
  `views` int(10) unsigned DEFAULT 0,
  `addedUser` int(10) unsigned NOT NULL DEFAULT 1,
  `addedDate` datetime DEFAULT NULL COMMENT 'addition date',
  `rank` int(11) unsigned NOT NULL DEFAULT 0,
  `type` set('32b','64b','128b','256b','512b','1k','4k','8k','16k','32k','40k','64k','80k','96k','100k','128k','256k','artpack','bbstro','cracktro','demo','demopack','demotool','dentro','diskmag','fastdemo','game','intro','invitation','liveact','musicdisk','procedural graphics','report','slideshow','votedisk','wild') DEFAULT NULL,
  `party` int(10) DEFAULT NULL,
  `party_year` int(2) unsigned DEFAULT NULL,
  `party_compo` int(11) DEFAULT NULL,
  `party_place` tinyint(3) unsigned DEFAULT NULL,
  `latestip` varchar(255) NOT NULL DEFAULT '',
  `group1` int(10) DEFAULT NULL,
  `group2` int(10) DEFAULT NULL,
  `group3` int(10) DEFAULT NULL,
  `csdb` int(10) unsigned NOT NULL DEFAULT 0,
  `zxdemo` int(10) unsigned NOT NULL DEFAULT 0,
  `demozoo` int(10) unsigned DEFAULT NULL,
  `sceneorg` int(10) unsigned NOT NULL DEFAULT 0,
  `voteup` int(10) unsigned NOT NULL DEFAULT 0,
  `votepig` int(10) unsigned NOT NULL DEFAULT 0,
  `votedown` int(10) unsigned NOT NULL DEFAULT 0,
  `voteavg` decimal(6,4) NOT NULL DEFAULT 0.0000,
  `invitation` int(10) DEFAULT NULL,
  `invitationyear` int(10) unsigned DEFAULT 0,
  `boardID` int(11) DEFAULT NULL,
  `DEPRECATED_downloads` int(10) unsigned NOT NULL DEFAULT 0,
  `DEPRECATED_downloads_ip` varchar(255) NOT NULL DEFAULT '',
  `DEPRECATED_video` varchar(255) NOT NULL DEFAULT '',
  `DEPRECATED_source` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `group1` (`group1`),
  KEY `group2` (`group2`),
  KEY `group3` (`group3`),
  KEY `id` (`id`,`group1`,`group2`,`group3`),
  KEY `party` (`party`),
  KEY `date` (`releaseDate`),
  KEY `quand` (`addedDate`),
  KEY `datequand` (`releaseDate`,`addedDate`),
  KEY `partyyear` (`party`,`party_year`),
  KEY `added` (`addedUser`),
  KEY `allgroups` (`group1`,`group2`,`group3`),
  KEY `boardID` (`boardID`),
  KEY `invitation` (`invitation`),
  KEY `party_compo` (`party_compo`),
  CONSTRAINT `prods_ibfk_1` FOREIGN KEY (`group1`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_2` FOREIGN KEY (`group2`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_3` FOREIGN KEY (`group3`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_4` FOREIGN KEY (`party`) REFERENCES `parties` (`id`),
  CONSTRAINT `prods_ibfk_5` FOREIGN KEY (`boardID`) REFERENCES `boards` (`id`),
  CONSTRAINT `prods_ibfk_6` FOREIGN KEY (`invitation`) REFERENCES `parties` (`id`),
  CONSTRAINT `prods_ibfk_7` FOREIGN KEY (`party_compo`) REFERENCES `compotypes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods_linkcheck`
--

DROP TABLE IF EXISTS `prods_linkcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods_linkcheck` (
  `prodID` int(10) NOT NULL,
  `protocol` varchar(5) NOT NULL,
  `returnCode` smallint(6) NOT NULL,
  `returnContentType` varchar(255) NOT NULL,
  `testDate` datetime /* mariadb-5.3 */ NOT NULL,
  PRIMARY KEY (`prodID`),
  CONSTRAINT `prods_linkcheck_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods_platforms`
--

DROP TABLE IF EXISTS `prods_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods_platforms` (
  `prod` int(10) NOT NULL DEFAULT 0,
  `platform` int(10) NOT NULL DEFAULT 0,
  KEY `plt` (`prod`,`platform`),
  KEY `pltpr` (`prod`),
  KEY `pltpl` (`platform`),
  CONSTRAINT `prods_platforms_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`),
  CONSTRAINT `prods_platforms_ibfk_2` FOREIGN KEY (`platform`) REFERENCES `platforms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods_refs`
--

DROP TABLE IF EXISTS `prods_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods_refs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) unsigned NOT NULL DEFAULT 0,
  `referrer` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pri` (`prod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sceneorgrecommended`
--

DROP TABLE IF EXISTS `sceneorgrecommended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sceneorgrecommended` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `prodid` int(10) NOT NULL DEFAULT 0,
  `type` enum('awardwinner','awardnominee','viewingtip','meteorikwinner','meteoriknominee') DEFAULT NULL,
  `category` enum('best demo','best intro','best 64k intro','best 4k intro','best effects','best graphics','best soundtrack','best direction','most original concept','breakthrough performance','public choice','viewing tip','best demo on an oldschool platform','best animation','best technical achievement','High End Demo','High End Intro','High End Graphics','High End Soundtrack','Low End Demo','Low End Intro','Low End Graphics','Low End Soundtrack','New Talent','Interactive','Standalone Graphics','Tiny Intro','Alternative Platforms','Best Art Direction','Best Pixel Graphics in a Low-End Demo or Intro','Best Storytelling / Storyline / Plot','Best High-End Intro','Best High-End Demo','Best Low-End Demo','Best Low-End intro','That''s not Possible on this Platform!','Best High-End 4k Intro','Best Freestyle Graphics','Best Low-End Production','Best Small High-End Intro','Best High-End 64k Intro','Best Visuals','Outstanding Technical Achievement','Outstanding Concept') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prodid_category` (`prodid`,`category`),
  KEY `prodid` (`prodid`),
  KEY `type` (`type`),
  KEY `category` (`category`),
  CONSTRAINT `sceneorgrecommended_ibfk_1` FOREIGN KEY (`prodid`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `screenshots`
--

DROP TABLE IF EXISTS `screenshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `screenshots` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT 0,
  `user` int(10) NOT NULL DEFAULT 0,
  `added` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_2` (`prod`),
  KEY `user` (`user`),
  CONSTRAINT `screenshots_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  CONSTRAINT `screenshots_ibfk_2` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='paternite des screenshots';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ud`
--

DROP TABLE IF EXISTS `ud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ud` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `joined` date NOT NULL DEFAULT '0000-00-00',
  `results` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `cputime` varchar(14) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='United Devices';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL DEFAULT 0,
  `nickname` varchar(16) NOT NULL,
  `level` enum('administrator','moderator','gloperator','user','pr0nstahr','fakeuser','banned') DEFAULT 'user',
  `permissionSubmitItems` tinyint(4) NOT NULL DEFAULT 1,
  `permissionPostBBS` tinyint(4) NOT NULL DEFAULT 1,
  `permissionPostOneliner` tinyint(4) NOT NULL DEFAULT 1,
  `avatar` varchar(255) NOT NULL,
  `registerDate` datetime DEFAULT NULL,
  `udlogin` varchar(255) NOT NULL DEFAULT '',
  `glops` int(10) unsigned NOT NULL DEFAULT 0,
  `ojuice` int(10) unsigned DEFAULT 0,
  `slengpung` int(10) unsigned DEFAULT 0,
  `csdb` int(10) unsigned NOT NULL DEFAULT 0,
  `zxdemo` int(10) unsigned NOT NULL DEFAULT 0,
  `demozoo` int(10) unsigned NOT NULL DEFAULT 0,
  `lastip` text DEFAULT NULL,
  `lasthost` text DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `sceneIDData` text DEFAULT NULL,
  `sceneIDLastRefresh` datetime DEFAULT NULL,
  `DEPRECATED_im_type` enum('','AIM','ICQ','Jabber','MSN','Skype','Xfire','Yahoo') DEFAULT NULL,
  `DEPRECATED_im_id` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_cdcs`
--

DROP TABLE IF EXISTS `users_cdcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_cdcs` (
  `user` int(10) NOT NULL DEFAULT 0,
  `cdc` int(10) NOT NULL DEFAULT 0,
  `timelock` date DEFAULT NULL,
  UNIQUE KEY `pcdc` (`user`,`cdc`),
  KEY `pcdcu` (`user`),
  KEY `pcdcc` (`cdc`),
  CONSTRAINT `users_cdcs_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  CONSTRAINT `users_cdcs_ibfk_2` FOREIGN KEY (`cdc`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_im`
--

DROP TABLE IF EXISTS `users_im`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_im` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userID` int(10) NOT NULL,
  `im_type` enum('','AIM','Bluesky','Discord','Email','Facebook','ICQ','Instagram','Jabber','Mastodon','MSN','Skype','Telegram','Twitch','Twitter','Xfire','Yahoo') DEFAULT NULL,
  `im_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  CONSTRAINT `users_im_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usersettings`
--

DROP TABLE IF EXISTS `usersettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersettings` (
  `id` int(10) NOT NULL DEFAULT 0,
  `topicposts` int(10) unsigned NOT NULL DEFAULT 25,
  `bbsbbstopics` int(10) unsigned NOT NULL DEFAULT 25,
  `prodlistprods` int(10) unsigned NOT NULL DEFAULT 25,
  `searchprods` int(10) unsigned NOT NULL DEFAULT 25,
  `userlogos` int(10) unsigned NOT NULL DEFAULT 10,
  `userprods` int(10) unsigned NOT NULL DEFAULT 10,
  `usergroups` int(10) unsigned NOT NULL DEFAULT 10,
  `userparties` int(10) unsigned NOT NULL DEFAULT 10,
  `userscreenshots` int(10) unsigned NOT NULL DEFAULT 10,
  `usernfos` int(10) unsigned NOT NULL DEFAULT 10,
  `usercomments` int(10) unsigned NOT NULL DEFAULT 10,
  `userrulez` int(10) unsigned NOT NULL DEFAULT 10,
  `usersucks` int(10) unsigned NOT NULL DEFAULT 10,
  `commentshours` int(10) unsigned NOT NULL DEFAULT 24,
  `logos` int(1) unsigned NOT NULL DEFAULT 1,
  `topbar` int(1) unsigned NOT NULL DEFAULT 1,
  `bottombar` int(1) unsigned NOT NULL DEFAULT 1,
  `userlistusers` int(10) unsigned NOT NULL DEFAULT 25,
  `topichidefakeuser` int(1) unsigned NOT NULL DEFAULT 0,
  `prodhidefakeuser` int(1) unsigned NOT NULL DEFAULT 0,
  `indextype` int(1) unsigned NOT NULL DEFAULT 1,
  `indexplatform` int(1) unsigned NOT NULL DEFAULT 1,
  `indexwatchlist` int(1) unsigned NOT NULL DEFAULT 5,
  `indexwhoaddedprods` int(1) unsigned NOT NULL DEFAULT 0,
  `indexwhocommentedprods` int(1) unsigned NOT NULL DEFAULT 0,
  `displayimages` int(1) NOT NULL DEFAULT 1,
  `prodcomments` int(11) NOT NULL DEFAULT -1,
  `customizerJSON` text NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`),
  CONSTRAINT `usersettings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watchlist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userID` int(10) NOT NULL,
  `prodID` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `prodID` (`prodID`),
  CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
