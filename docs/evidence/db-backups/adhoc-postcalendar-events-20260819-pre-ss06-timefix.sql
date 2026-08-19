/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.8-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: openemr
-- ------------------------------------------------------
-- Server version	11.8.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `openemr_postcalendar_events`
--

DROP TABLE IF EXISTS `openemr_postcalendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `openemr_postcalendar_events` (
  `pc_eid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pc_catid` int(11) NOT NULL DEFAULT 0,
  `pc_multiple` int(10) unsigned NOT NULL,
  `pc_aid` varchar(30) DEFAULT NULL,
  `pc_pid` varchar(11) DEFAULT NULL,
  `pc_gid` int(11) DEFAULT 0,
  `pc_title` varchar(150) DEFAULT NULL,
  `pc_time` datetime DEFAULT NULL,
  `pc_hometext` text DEFAULT NULL,
  `pc_comments` int(11) DEFAULT 0,
  `pc_counter` mediumint(8) unsigned DEFAULT 0,
  `pc_topic` int(3) NOT NULL DEFAULT 1,
  `pc_informant` varchar(20) DEFAULT NULL,
  `pc_eventDate` date NOT NULL,
  `pc_endDate` date DEFAULT NULL,
  `pc_duration` bigint(20) NOT NULL DEFAULT 0,
  `pc_recurrtype` int(1) NOT NULL DEFAULT 0,
  `pc_recurrspec` text DEFAULT NULL,
  `pc_recurrfreq` int(3) NOT NULL DEFAULT 0,
  `pc_startTime` time DEFAULT NULL,
  `pc_endTime` time DEFAULT NULL,
  `pc_alldayevent` int(1) NOT NULL DEFAULT 0,
  `pc_location` text DEFAULT NULL,
  `pc_conttel` varchar(50) DEFAULT NULL,
  `pc_contname` varchar(50) DEFAULT NULL,
  `pc_contemail` varchar(255) DEFAULT NULL,
  `pc_website` varchar(255) DEFAULT NULL,
  `pc_fee` varchar(50) DEFAULT NULL,
  `pc_eventstatus` int(11) NOT NULL DEFAULT 0,
  `pc_sharing` int(11) NOT NULL DEFAULT 0,
  `pc_language` varchar(30) DEFAULT NULL,
  `pc_apptstatus` varchar(15) NOT NULL DEFAULT '-',
  `pc_prefcatid` int(11) NOT NULL DEFAULT 0,
  `pc_facility` int(11) NOT NULL DEFAULT 0 COMMENT 'facility id for this event',
  `pc_sendalertsms` varchar(3) NOT NULL DEFAULT 'NO',
  `pc_sendalertemail` varchar(3) NOT NULL DEFAULT 'NO',
  `pc_billing_location` smallint(6) NOT NULL DEFAULT 0,
  `pc_room` varchar(20) NOT NULL DEFAULT '',
  `uuid` binary(16) DEFAULT NULL,
  PRIMARY KEY (`pc_eid`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `basic_event` (`pc_catid`,`pc_aid`,`pc_eventDate`,`pc_endDate`,`pc_eventstatus`,`pc_sharing`,`pc_topic`),
  KEY `pc_eventDate` (`pc_eventDate`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `openemr_postcalendar_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `openemr_postcalendar_events` WRITE;
/*!40000 ALTER TABLE `openemr_postcalendar_events` DISABLE KEYS */;
INSERT INTO `openemr_postcalendar_events` VALUES
(7,5,0,'6','1',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-16',NULL,1800,0,NULL,0,'128:00:00','08:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'?',0,3,'NO','NO',3,'','¢\\“@î–ˆ˛Ì\nú'),
(8,5,0,'7','2',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-16',NULL,1800,0,NULL,0,'129:30:00','10:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'?',0,3,'NO','NO',3,'','¢\\“µEÜ˚Ô5¬'),
(9,5,0,'6','3',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'130:00:00','10:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'x',0,3,'NO','NO',3,'','¢\\“—L)©Vè_∑’Mr'),
(10,5,0,'7','4',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'131:30:00','12:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'x',0,3,'NO','NO',3,'','¢\\“⁄FÃ£‚$Ó\nãF'),
(11,5,0,'6','5',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'132:00:00','12:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'x',0,3,'NO','NO',3,'','¢\\“¯NoïË2®óu∆'),
(12,5,0,'7','6',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'133:30:00','14:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“0B!†òh≈¡èo]'),
(13,5,0,'6','7',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'134:00:00','14:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'<',0,3,'NO','NO',3,'','¢\\“>@âêA@¯∂—§€'),
(14,5,0,'7','8',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'135:30:00','16:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“yK3ªï≥Çå˛âÏ'),
(15,5,0,'6','9',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'136:00:00','16:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“ãHhõ&ÂX©*~π'),
(16,5,0,'7','10',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'128:30:00','09:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“\"H`æDæNº6Ìg'),
(17,5,0,'6','11',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'129:00:00','09:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'<',0,3,'NO','NO',3,'','¢\\“$^C_ªÉˇ¶=NÄ'),
(18,5,0,'7','12',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'130:30:00','11:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“&úC°Æzø¥€-â'),
(19,5,0,'6','13',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'131:00:00','11:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“(ˇGı≤˙í#W$ó'),
(20,5,0,'7','14',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'132:30:00','13:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“+(IôõÉhDíB‘ù'),
(21,5,0,'6','15',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'133:00:00','13:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'<',0,3,'NO','NO',3,'','¢\\“-yI\'º~6@å/7'),
(22,5,0,'7','16',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'134:30:00','15:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“/üI<å2UGa-1S'),
(23,5,0,'6','17',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'135:00:00','15:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“1ßI]â:•≈!J\"ç'),
(24,5,0,'7','18',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'136:30:00','17:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“3ÒHsÅ¥Xï˙i≤'),
(25,5,0,'6','19',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-18',NULL,1800,0,NULL,0,'128:00:00','08:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“5‰DI™•—‹á◊Ì'),
(26,5,0,'7','20',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'129:30:00','10:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“8HFà<îkÈs'),
(27,5,0,'6','21',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-15',NULL,1800,0,NULL,0,'130:00:00','10:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“:êIÚ∂kWö£\'¯8'),
(28,5,0,'7','22',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-16',NULL,1800,0,NULL,0,'131:30:00','12:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“<’JÁØ˝Ê>º;‹'),
(29,5,0,'6','23',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'132:00:00','12:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“?0Bí8um˜‹K'),
(30,5,0,'7','24',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-18',NULL,1800,0,NULL,0,'133:30:00','14:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“AEN;Ω]ŸıTÚ¸'),
(31,5,0,'6','25',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'134:00:00','14:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“C\\D…ª´»ßŒ(d'),
(32,5,0,'7','26',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-15',NULL,1800,0,NULL,0,'135:30:00','16:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“EFOÇ.y±±	+ó'),
(33,5,0,'6','27',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-16',NULL,1800,0,NULL,0,'136:00:00','16:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“HJJπE‹ª˛ùB2'),
(34,5,0,'7','28',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'128:30:00','09:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“J¨Lúñ÷»ãﬂe'),
(35,5,0,'6','29',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-18',NULL,1800,0,NULL,0,'129:00:00','09:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“M@têxy«^w5'),
(36,5,0,'7','30',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'130:30:00','11:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“OòN‰∑MF˝¨Ÿ.'),
(37,5,0,'6','1',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-15',NULL,1800,0,NULL,0,'131:00:00','11:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“Q‹KW∞a¸“hî4'),
(38,5,0,'7','2',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-16',NULL,1800,0,NULL,0,'132:30:00','13:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“T=@ôm¨Kıs'),
(39,5,0,'6','3',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-17',NULL,1800,0,NULL,0,'133:00:00','13:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“VbCr•˚¥yB”H7'),
(40,5,0,'7','4',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-18',NULL,1800,0,NULL,0,'134:30:00','15:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“X’H/≥ÒGv˜°Ç'),
(41,5,0,'6','5',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-19',NULL,1800,0,NULL,0,'135:00:00','15:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'@',0,3,'NO','NO',3,'','¢\\“[\0JvÑﬁë%F∞'),
(42,5,0,'7','6',0,'Ophthalmology appointment (SYNTHETIC DEMO)','2026-08-14 06:43:38','SYNTHETIC DEMO appointment',0,0,1,'1','2026-08-15',NULL,1800,0,NULL,0,'136:30:00','17:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'>',0,3,'NO','NO',3,'','¢\\“]ùI≤ùhπ°ƒp'),
(43,5,0,'6','1',0,'Weekly post-operative review (SYNTHETIC DEMO)','2026-08-14 06:43:38','Recurring series, SYNTHETIC DEMO',0,0,1,'1','2026-08-10','2026-10-05',1800,1,'a:6:{s:17:\"event_repeat_freq\";s:1:\"1\";s:22:\"event_repeat_freq_type\";s:1:\"1\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";s:6:\"exdate\";s:0:\"\";}',0,'09:00:00','09:30:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,'-',0,3,'NO','NO',3,'','¢\\“`L\r•€‘’Yı');
/*!40000 ALTER TABLE `openemr_postcalendar_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-19 18:09:53
