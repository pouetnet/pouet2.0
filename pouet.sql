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
  `board` int(10) NOT NULL DEFAULT '0',
  `group` int(10) NOT NULL DEFAULT '0',
  `type` enum('WHQ','member','EHQ','USHQ','HQ','dist') DEFAULT NULL,
  KEY `board` (`board`),
  KEY `group` (`group`),
  CONSTRAINT `affiliatedboards_ibfk_2` FOREIGN KEY (`group`) REFERENCES `groups` (`id`),
  CONSTRAINT `affiliatedboards_ibfk_1` FOREIGN KEY (`board`) REFERENCES `boards` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `affiliatedprods`
--

DROP TABLE IF EXISTS `affiliatedprods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `affiliatedprods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original` int(10) DEFAULT '0',
  `derivative` int(10) DEFAULT '0',
  `type` enum('port','final','remix','pack','related') DEFAULT 'port',
  PRIMARY KEY (`id`),
  KEY `original` (`original`),
  KEY `derivative` (`derivative`),
  CONSTRAINT `affiliatedprods_ibfk_2` FOREIGN KEY (`derivative`) REFERENCES `prods` (`id`),
  CONSTRAINT `affiliatedprods_ibfk_1` FOREIGN KEY (`original`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2007`
--

DROP TABLE IF EXISTS `awardscand_2007`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2007` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT '0',
  `cat2` int(10) unsigned NOT NULL DEFAULT '0',
  `cat3` int(10) unsigned NOT NULL DEFAULT '0',
  `cat4` int(10) unsigned NOT NULL DEFAULT '0',
  `cat5` int(10) unsigned NOT NULL DEFAULT '0',
  `cat6` int(10) unsigned NOT NULL DEFAULT '0',
  `cat7` int(10) unsigned NOT NULL DEFAULT '0',
  `cat8` int(10) unsigned NOT NULL DEFAULT '0',
  `cat9` int(10) unsigned NOT NULL DEFAULT '0',
  `cat10` int(10) unsigned NOT NULL DEFAULT '0',
  `cat11` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2008`
--

DROP TABLE IF EXISTS `awardscand_2008`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2008` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT '0',
  `cat2` int(10) unsigned NOT NULL DEFAULT '0',
  `cat3` int(10) unsigned NOT NULL DEFAULT '0',
  `cat4` int(10) unsigned NOT NULL DEFAULT '0',
  `cat5` int(10) unsigned NOT NULL DEFAULT '0',
  `cat6` int(10) unsigned NOT NULL DEFAULT '0',
  `cat7` int(10) unsigned NOT NULL DEFAULT '0',
  `cat8` int(10) unsigned NOT NULL DEFAULT '0',
  `cat9` int(10) unsigned NOT NULL DEFAULT '0',
  `cat10` int(10) unsigned NOT NULL DEFAULT '0',
  `cat11` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2010`
--

DROP TABLE IF EXISTS `awardscand_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2010` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT '0',
  `cat2` int(10) unsigned NOT NULL DEFAULT '0',
  `cat3` int(10) unsigned NOT NULL DEFAULT '0',
  `cat4` int(10) unsigned NOT NULL DEFAULT '0',
  `cat5` int(10) unsigned NOT NULL DEFAULT '0',
  `cat6` int(10) unsigned NOT NULL DEFAULT '0',
  `cat7` int(10) unsigned NOT NULL DEFAULT '0',
  `cat8` int(10) unsigned NOT NULL DEFAULT '0',
  `cat9` int(10) unsigned NOT NULL DEFAULT '0',
  `cat10` int(10) unsigned NOT NULL DEFAULT '0',
  `cat11` int(10) unsigned NOT NULL DEFAULT '0',
  `cat12` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `awardscand_2011`
--

DROP TABLE IF EXISTS `awardscand_2011`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `awardscand_2011` (
  `user` int(10) unsigned NOT NULL,
  `cat1` int(10) unsigned NOT NULL DEFAULT '0',
  `cat2` int(10) unsigned NOT NULL DEFAULT '0',
  `cat3` int(10) unsigned NOT NULL DEFAULT '0',
  `cat4` int(10) unsigned NOT NULL DEFAULT '0',
  `cat5` int(10) unsigned NOT NULL DEFAULT '0',
  `cat6` int(10) unsigned NOT NULL DEFAULT '0',
  `cat7` int(10) unsigned NOT NULL DEFAULT '0',
  `cat8` int(10) unsigned NOT NULL DEFAULT '0',
  `cat9` int(10) unsigned NOT NULL DEFAULT '0',
  `cat10` int(10) unsigned NOT NULL DEFAULT '0',
  `cat11` int(10) unsigned NOT NULL DEFAULT '0',
  `cat12` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bbs_posts`
--

DROP TABLE IF EXISTS `bbs_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bbs_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` int(10) NOT NULL DEFAULT '0',
  `post` text NOT NULL,
  `author` int(10) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `topic` (`topic`),
  KEY `idx_author` (`author`),
  KEY `idx_added` (`added`),
  CONSTRAINT `bbs_posts_ibfk_1` FOREIGN KEY (`topic`) REFERENCES `bbs_topics` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='the bbs posts';
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
  `lastpost` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userlastpost` int(10) NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `firstpost` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userfirstpost` int(10) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lastpost` (`lastpost`),
  KEY `userlastpost` (`userlastpost`),
  KEY `userfirstpost` (`userfirstpost`),
  CONSTRAINT `bbs_topics_ibfk_1` FOREIGN KEY (`userlastpost`) REFERENCES `users` (`id`),
  CONSTRAINT `bbs_topics_ibfk_2` FOREIGN KEY (`userfirstpost`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='the bbs topics';
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
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `adder` (`adder`),
  CONSTRAINT `boards_ibfk_1` FOREIGN KEY (`adder`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boards_ads`
--

DROP TABLE IF EXISTS `boards_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boards_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `board` int(10) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boards_platforms`
--

DROP TABLE IF EXISTS `boards_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boards_platforms` (
  `board` int(10) NOT NULL DEFAULT '0',
  `platform` int(10) NOT NULL DEFAULT '0',
  KEY `bbsb` (`board`),
  KEY `bbs` (`board`,`platform`),
  KEY `bbspl` (`platform`),
  CONSTRAINT `boards_platforms_ibfk_2` FOREIGN KEY (`board`) REFERENCES `boards` (`id`),
  CONSTRAINT `boards_platforms_ibfk_1` FOREIGN KEY (`platform`) REFERENCES `platforms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boardsaka`
--

DROP TABLE IF EXISTS `boardsaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boardsaka` (
  `board1` int(10) unsigned NOT NULL DEFAULT '0',
  `board2` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `dead` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='les boutons sur pou';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cdc`
--

DROP TABLE IF EXISTS `cdc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `which` int(10) NOT NULL DEFAULT '0',
  `quand` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`which`),
  KEY `id_2` (`id`,`which`,`quand`),
  KEY `which` (`which`),
  CONSTRAINT `cdc_ibfk_1` FOREIGN KEY (`which`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='coups de coeur';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `which` int(10) NOT NULL DEFAULT '0',
  `who` int(10) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `rating` tinyint(2) NOT NULL DEFAULT '0',
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `which` (`which`),
  KEY `who` (`who`),
  KEY `rating` (`rating`),
  KEY `quand` (`quand`),
  KEY `whichwho` (`who`,`which`),
  KEY `which_quand` (`which`,`quand`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`who`) REFERENCES `users` (`id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`which`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  KEY `prodID` (`prodID`),
  KEY `userID` (`userID`),
  CONSTRAINT `credits_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `downloadlinks`
--

DROP TABLE IF EXISTS `downloadlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downloadlinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dl_prod` (`prod`),
  KEY `dl_prodtype` (`prod`),
  CONSTRAINT `downloadlinks_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `category` enum('general','pouet 2.0','syndication','BB Code') NOT NULL DEFAULT 'general',
  `deprecated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='the pou';
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
  `itemType` enum('prod','group','party','topic') NOT NULL,
  `additionalData` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gloperatorid` (`gloperatorid`),
  CONSTRAINT `gloperator_log_ibfk_1` FOREIGN KEY (`gloperatorid`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `web` varchar(255) NOT NULL,
  `added` int(10) NOT NULL DEFAULT '1',
  `views` int(10) unsigned DEFAULT NULL,
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `csdb` int(10) unsigned NOT NULL DEFAULT '0',
  `zxdemo` int(10) unsigned NOT NULL DEFAULT '0',
  `demozoo` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`added`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`added`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupsaka`
--

DROP TABLE IF EXISTS `groupsaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupsaka` (
  `group1` int(10) unsigned NOT NULL DEFAULT '0',
  `group2` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `listitems`
--

DROP TABLE IF EXISTS `listitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listitems` (
  `list` int(10) unsigned NOT NULL DEFAULT '0',
  `itemid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('user','prod','group','party') NOT NULL DEFAULT 'prod',
  KEY `list` (`list`),
  CONSTRAINT `listitems_ibfk_1` FOREIGN KEY (`list`) REFERENCES `lists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) NOT NULL DEFAULT '0',
  `upkeeper` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `adder` (`adder`),
  KEY `upkeeper` (`upkeeper`),
  CONSTRAINT `lists_ibfk_2` FOREIGN KEY (`upkeeper`) REFERENCES `users` (`id`),
  CONSTRAINT `lists_ibfk_1` FOREIGN KEY (`adder`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `author1` int(10) NOT NULL DEFAULT '0',
  `author2` int(10) DEFAULT '0',
  `vote_count` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `author1` (`author1`),
  KEY `author2` (`author2`),
  CONSTRAINT `logos_ibfk_2` FOREIGN KEY (`author2`) REFERENCES `users` (`id`),
  CONSTRAINT `logos_ibfk_1` FOREIGN KEY (`author1`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logos_votes`
--

DROP TABLE IF EXISTS `logos_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logos_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logo` int(10) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0',
  `vote` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`vote`),
  KEY `user_2` (`user`,`vote`,`logo`),
  KEY `logo` (`logo`),
  CONSTRAINT `logos_votes_ibfk_2` FOREIGN KEY (`logo`) REFERENCES `logos` (`id`),
  CONSTRAINT `logos_votes_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='logos ratings given by users';
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
  `requestDate` datetime NOT NULL,
  `userID` int(10) NOT NULL,
  `gloperatorID` int(10) DEFAULT NULL,
  `approved` tinyint(4) DEFAULT NULL,
  `comment` text NOT NULL,
  `approveDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prodid` (`itemID`),
  KEY `approved` (`approved`),
  KEY `userID` (`userID`),
  KEY `gloperatorID` (`gloperatorID`),
  CONSTRAINT `modification_requests_ibfk_2` FOREIGN KEY (`gloperatorID`) REFERENCES `users` (`id`),
  CONSTRAINT `modification_requests_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `who` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nfos`
--

DROP TABLE IF EXISTS `nfos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nfos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `prod` (`prod`),
  KEY `user` (`user`),
  CONSTRAINT `nfos_ibfk_2` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`),
  CONSTRAINT `nfos_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ojnews`
--

DROP TABLE IF EXISTS `ojnews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ojnews` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `authorid` int(11) NOT NULL DEFAULT '0',
  `authornick` varchar(255) NOT NULL,
  `authorgroup` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `who` int(10) NOT NULL DEFAULT '0',
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `who` (`who`),
  CONSTRAINT `oneliner_ibfk_1` FOREIGN KEY (`who`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `othernfos`
--

DROP TABLE IF EXISTS `othernfos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `othernfos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `refid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('group','bbs') DEFAULT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adder` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `added` int(10) NOT NULL DEFAULT '0',
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partiesaka`
--

DROP TABLE IF EXISTS `partiesaka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partiesaka` (
  `party1` int(10) unsigned NOT NULL DEFAULT '0',
  `party2` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`party1`,`party2`),
  KEY `party1` (`party1`),
  KEY `party2` (`party2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partylinks`
--

DROP TABLE IF EXISTS `partylinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partylinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `party` int(10) NOT NULL DEFAULT '0',
  `year` int(10) unsigned NOT NULL DEFAULT '0',
  `download` varchar(255) NOT NULL,
  `csdb` int(10) unsigned NOT NULL DEFAULT '0',
  `zxdemo` int(10) unsigned NOT NULL DEFAULT '0',
  `demozoo` int(10) unsigned DEFAULT NULL,
  `slengpung` int(10) unsigned NOT NULL DEFAULT '0',
  `artcity` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `party` (`party`),
  CONSTRAINT `partylinks_ibfk_1` FOREIGN KEY (`party`) REFERENCES `parties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prodotherparty`
--

DROP TABLE IF EXISTS `prodotherparty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodotherparty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT '0',
  `party` int(10) NOT NULL DEFAULT '0',
  `party_year` int(10) unsigned NOT NULL DEFAULT '0',
  `party_place` int(10) unsigned NOT NULL DEFAULT '0',
  `partycompo` enum('invit','none','4k procedural gfx','8bit demo','8bit 1k','16 seconds demo','32bit low demo','32bit hi demo','32k game','96k game','acorn demo','acorn intro','acorn 4k','acorn 1k','alternative demo','amiga demo','amiga intro','amiga fastintro','amiga aga demo','amiga ecs demo','amiga 128k','amiga 64k','amiga 40k','amiga 10k','amiga 4k','amiga 2k','amiga 256b','animation','atari demo','atari intro','atari 8bit demo','atari xl demo','atari 8bit intro','atari 192k','atari 96k','atari 4k','atari 128b','BASIC demo','BK demo','BK 4k','black&white video compo','bootsector intro','browser demo','C16 16k','C16 1k','C16 128b','C16 64b','c64 demo','c64 intro','c64 256b','c64 1k','c64 4k','coding','crazy demo','combined demo','combined dentro','combined demo/intro','combined intro','combined 80k','combined 64k/4k','combined 64k','combined 4k','combined 256b','combined 128b','console demo','cpc demo','disqualified demos compo','dreamcast demo','dynamic demo','fake demo','falcon intro','falcon demo','fast demo','flash demo','gamedev','gameboy demo','handheld demo','java demo','java intro','lamer demo','lowend demo','lowend intro','mac demo','megademo','mobile demo','musicdisk','music video','oldskool demo','oldskool intro','OHP demo','pc demo','pc intro','pc fast intro','pc 256k','pc 128k','pc 100k','pc 80k','pc 64k','pc 16k','pc 8k','pc 5k','pc 4k','pc 1k','pc 512b','pc 256b','pc 128b','pc 64b','pc 32b','pc 3d acc demo','pc non 3d acc demo','pirated demo','playstation demo','php demo','processing demo','recycle.bin','scrooler demo','silent movie','shortfilm','short wild','textmode demo','useless utility','website','wild demo','windows demo','windows95 demo','windows98 demo','zx demo','zx intro','zx 4k','zx 1k','zx 512b','zx 256b','zx 128b','gravedigger') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prod` (`prod`),
  KEY `partyyear` (`party`,`party_year`),
  CONSTRAINT `prodotherparty_ibfk_2` FOREIGN KEY (`party`) REFERENCES `parties` (`id`),
  CONSTRAINT `prodotherparty_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
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
  `date` date DEFAULT NULL COMMENT 'release date',
  `views` int(10) unsigned DEFAULT '0',
  `added` int(10) unsigned NOT NULL DEFAULT '1',
  `rank` int(11) unsigned NOT NULL DEFAULT '0',
  `type` set('32b','64b','128b','256b','512b','1k','4k','8k','16k','32k','40k','64k','80k','96k','100k','128k','256k','artpack','bbstro','cracktro','demo','demopack','demotool','dentro','diskmag','fastdemo','game','intro','invitation','liveact','musicdisk','procedural graphics','report','slideshow','votedisk','wild') DEFAULT NULL,
  `party` int(10) DEFAULT NULL,
  `party_year` int(2) unsigned DEFAULT NULL,
  `partycompo` enum('invit','none','4k procedural gfx','8bit demo','8bit 1k','16 seconds demo','32bit low demo','32bit hi demo','32k game','96k game','acorn demo','acorn intro','acorn 4k','acorn 1k','alternative demo','amiga demo','amiga intro','amiga fastintro','amiga aga demo','amiga ecs demo','amiga 128k','amiga 64k','amiga 40k','amiga 10k','amiga 4k','amiga 2k','amiga 256b','animation','atari demo','atari intro','atari 8bit demo','atari xl demo','atari 8bit intro','atari 192k','atari 96k','atari 4k','atari 128b','BASIC demo','BK demo','BK 4k','beginner demo compo','black&white video compo','bootsector intro','browser demo','browser intro','C16 16k','C16 1k','C16 128b','C16 64b','c64 demo','c64 intro','c64 256b','c64 1k','c64 4k','coding','crazy demo','combined demo','combined dentro','combined demo/intro','combined intro','combined 80k','combined 64k/4k','combined 64k','combined 4k','combined 256b','combined 128b','console demo','cpc demo','disqualified demos compo','dreamcast demo','dynamic demo','fake demo','falcon intro','falcon demo','fast demo','flash demo','gamedev','gameboy demo','handheld demo','hugescreen wild','java demo','java intro','lamer demo','lowend demo','lowend intro','mac demo','megademo','mobile demo','musicdisk','music video','oldskool demo','oldskool intro','OHP demo','pc demo','pc intro','pc fast intro','pc 256k','pc 128k','pc 100k','pc 80k','pc 64k','pc 16k','pc 8k','pc 5k','pc 4k','pc 1k','pc 512b','pc 256b','pc 128b','pc 64b','pc 32b','pc 3d acc demo','pc non 3d acc demo','pirated demo','playstation demo','php demo','processing demo','recycle.bin','scrooler demo','silent movie','shortfilm','short wild','textmode demo','useless utility','website','wild demo','windows demo','windows95 demo','windows98 demo','zx demo','zx intro','zx 4k','zx 1k','zx 512b','zx 256b','zx 128b','javascript 1k','freestyle','media facade','shadertoy','gravedigger','interactive','remix') DEFAULT NULL,
  `party_place` tinyint(3) unsigned DEFAULT NULL,
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'addition date',
  `latestip` varchar(255) NOT NULL,
  `group1` int(10) DEFAULT NULL,
  `group2` int(10) DEFAULT NULL,
  `group3` int(10) DEFAULT NULL,
  `csdb` int(10) unsigned NOT NULL DEFAULT '0',
  `zxdemo` int(10) unsigned NOT NULL DEFAULT '0',
  `demozoo` int(10) unsigned DEFAULT NULL,
  `sceneorg` int(10) unsigned NOT NULL DEFAULT '0',
  `voteup` int(10) unsigned NOT NULL DEFAULT '0',
  `votepig` int(10) unsigned NOT NULL DEFAULT '0',
  `votedown` int(10) unsigned NOT NULL DEFAULT '0',
  `voteavg` decimal(4,2) NOT NULL DEFAULT '0.00',
  `invitation` int(10) DEFAULT NULL,
  `invitationyear` int(10) unsigned NOT NULL DEFAULT '0',
  `boardID` int(11) DEFAULT NULL,
  `DEPRECATED_downloads` int(10) unsigned NOT NULL DEFAULT '0',
  `DEPRECATED_downloads_ip` varchar(255) NOT NULL,
  `DEPRECATED_video` varchar(255) NOT NULL,
  `DEPRECATED_source` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group1` (`group1`),
  KEY `group2` (`group2`),
  KEY `group3` (`group3`),
  KEY `id` (`id`,`group1`,`group2`,`group3`),
  KEY `party` (`party`),
  KEY `date` (`date`),
  KEY `quand` (`quand`),
  KEY `datequand` (`date`,`quand`),
  KEY `partyyear` (`party`,`party_year`),
  KEY `added` (`added`),
  KEY `allgroups` (`group1`,`group2`,`group3`),
  KEY `boardID` (`boardID`),
  KEY `invitation` (`invitation`),
  CONSTRAINT `prods_ibfk_6` FOREIGN KEY (`invitation`) REFERENCES `parties` (`id`),
  CONSTRAINT `prods_ibfk_1` FOREIGN KEY (`group1`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_2` FOREIGN KEY (`group2`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_3` FOREIGN KEY (`group3`) REFERENCES `groups` (`id`),
  CONSTRAINT `prods_ibfk_4` FOREIGN KEY (`party`) REFERENCES `parties` (`id`),
  CONSTRAINT `prods_ibfk_5` FOREIGN KEY (`boardID`) REFERENCES `boards` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods_platforms`
--

DROP TABLE IF EXISTS `prods_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods_platforms` (
  `prod` int(10) NOT NULL DEFAULT '0',
  `platform` int(10) NOT NULL DEFAULT '0',
  KEY `plt` (`prod`,`platform`),
  KEY `pltpr` (`prod`),
  KEY `pltpl` (`platform`),
  CONSTRAINT `prods_platforms_ibfk_2` FOREIGN KEY (`platform`) REFERENCES `platforms` (`id`),
  CONSTRAINT `prods_platforms_ibfk_1` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prods_refs`
--

DROP TABLE IF EXISTS `prods_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prods_refs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod` int(10) unsigned NOT NULL DEFAULT '0',
  `referrer` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pri` (`prod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sceneorgrecommended`
--

DROP TABLE IF EXISTS `sceneorgrecommended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sceneorgrecommended` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `prodid` int(10) NOT NULL DEFAULT '0',
  `type` enum('awardwinner','awardnominee','viewingtip') DEFAULT NULL,
  `category` enum('best demo','best intro','best 64k intro','best 4k intro','best effects','best graphics','best soundtrack','best direction','most original concept','breakthrough performance','public choice','viewing tip','best demo on an oldschool platform','best animation','best technical achievement') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prodid` (`prodid`),
  KEY `type` (`type`),
  KEY `category` (`category`),
  CONSTRAINT `sceneorgrecommended_ibfk_1` FOREIGN KEY (`prodid`) REFERENCES `prods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `screenshots`
--

DROP TABLE IF EXISTS `screenshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `screenshots` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `prod` int(10) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_2` (`prod`),
  KEY `user` (`user`),
  CONSTRAINT `screenshots_ibfk_2` FOREIGN KEY (`prod`) REFERENCES `prods` (`id`),
  CONSTRAINT `screenshots_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='paternite des screenshots';
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
  `results` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `cputime` varchar(14) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='United Devices';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL DEFAULT '0',
  `nickname` varchar(16) NOT NULL,
  `im_id` varchar(255) NOT NULL,
  `im_type` enum('AIM','ICQ','Jabber','MSN','Skype','Xfire','Yahoo') DEFAULT NULL,
  `level` enum('administrator','moderator','gloperator','user','pr0nstahr','fakeuser','banned') DEFAULT 'user',
  `avatar` varchar(255) NOT NULL,
  `quand` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `udlogin` varchar(255) NOT NULL,
  `glops` int(10) unsigned NOT NULL DEFAULT '0',
  `ojuice` int(10) unsigned DEFAULT '0',
  `slengpung` int(10) unsigned DEFAULT '0',
  `csdb` int(10) unsigned NOT NULL DEFAULT '0',
  `zxdemo` int(10) unsigned NOT NULL DEFAULT '0',
  `demozoo` int(10) unsigned NOT NULL,
  `lastip` text NOT NULL,
  `lasthost` text NOT NULL,
  `lastlogin` datetime NOT NULL,
  `sceneIDData` text NOT NULL,
  `sceneIDLastRefresh` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_cdcs`
--

DROP TABLE IF EXISTS `users_cdcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_cdcs` (
  `user` int(10) NOT NULL DEFAULT '0',
  `cdc` int(10) NOT NULL DEFAULT '0',
  `timelock` date DEFAULT NULL,
  UNIQUE KEY `pcdc` (`user`,`cdc`),
  KEY `pcdcu` (`user`),
  KEY `pcdcc` (`cdc`),
  CONSTRAINT `users_cdcs_ibfk_2` FOREIGN KEY (`cdc`) REFERENCES `prods` (`id`),
  CONSTRAINT `users_cdcs_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usersettings`
--

DROP TABLE IF EXISTS `usersettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indextopglops` int(10) unsigned NOT NULL DEFAULT '10',
  `indextopprods` int(10) unsigned NOT NULL DEFAULT '10',
  `indexoneliner` int(10) unsigned NOT NULL DEFAULT '5',
  `indexlatestadded` int(10) unsigned NOT NULL DEFAULT '5',
  `indexlatestreleased` int(10) unsigned NOT NULL DEFAULT '5',
  `indexojnews` int(10) unsigned NOT NULL DEFAULT '5',
  `indexlatestcomments` int(10) unsigned NOT NULL DEFAULT '5',
  `indexbbstopics` int(10) unsigned NOT NULL DEFAULT '10',
  `topicposts` int(10) unsigned NOT NULL DEFAULT '25',
  `bbsbbstopics` int(10) unsigned NOT NULL DEFAULT '25',
  `prodlistprods` int(10) unsigned NOT NULL DEFAULT '25',
  `searchprods` int(10) unsigned NOT NULL DEFAULT '25',
  `userlogos` int(10) unsigned NOT NULL DEFAULT '10',
  `userprods` int(10) unsigned NOT NULL DEFAULT '10',
  `usergroups` int(10) unsigned NOT NULL DEFAULT '10',
  `userparties` int(10) unsigned NOT NULL DEFAULT '10',
  `userscreenshots` int(10) unsigned NOT NULL DEFAULT '10',
  `usernfos` int(10) unsigned NOT NULL DEFAULT '10',
  `usercomments` int(10) unsigned NOT NULL DEFAULT '10',
  `userrulez` int(10) unsigned NOT NULL DEFAULT '10',
  `usersucks` int(10) unsigned NOT NULL DEFAULT '10',
  `commentshours` int(10) unsigned NOT NULL DEFAULT '24',
  `indexcdc` int(1) unsigned NOT NULL DEFAULT '1',
  `indexsearch` int(1) unsigned NOT NULL DEFAULT '1',
  `indexlinks` int(1) unsigned NOT NULL DEFAULT '1',
  `indexstats` int(1) unsigned NOT NULL DEFAULT '1',
  `logos` int(1) unsigned NOT NULL DEFAULT '1',
  `topbar` int(1) unsigned NOT NULL DEFAULT '1',
  `bottombar` int(1) unsigned NOT NULL DEFAULT '1',
  `userlistusers` int(10) unsigned NOT NULL DEFAULT '25',
  `topichidefakeuser` int(1) unsigned NOT NULL DEFAULT '0',
  `prodhidefakeuser` int(1) unsigned NOT NULL DEFAULT '0',
  `indextype` int(1) unsigned NOT NULL DEFAULT '1',
  `indexplatform` int(1) unsigned NOT NULL DEFAULT '1',
  `indexwatchlist` int(1) unsigned NOT NULL DEFAULT '5',
  `indexwhoaddedprods` int(1) unsigned NOT NULL DEFAULT '0',
  `indexwhocommentedprods` int(1) unsigned NOT NULL DEFAULT '0',
  `indexlatestparties` int(10) unsigned NOT NULL DEFAULT '5',
  `indextopkeops` int(10) unsigned NOT NULL DEFAULT '10',
  `displayimages` int(1) NOT NULL DEFAULT '1',
  `indexbbsnoresidue` tinyint(4) NOT NULL DEFAULT '1',
  `prodcomments` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`prodID`) REFERENCES `prods` (`id`),
  CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
