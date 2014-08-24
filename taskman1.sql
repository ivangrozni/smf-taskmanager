-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: taskman
-- ------------------------------------------------------
-- Server version	5.5.38-0+wheezy1

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
-- Current Database: `taskman`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `taskman` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `taskman`;

--
-- Table structure for table `Members`
--

DROP TABLE IF EXISTS `Members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Members` (
  `id_member` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(50) DEFAULT NULL,
  `joined` date DEFAULT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Members`
--

LOCK TABLES `Members` WRITE;
/*!40000 ALTER TABLE `Members` DISABLE KEYS */;
INSERT INTO `Members` VALUES (1,'Marx','0000-00-00'),(2,'Lenin','0000-00-00'),(3,'Rosa Luxembourg','0000-00-00'),(4,'Karl Kautsky','2014-01-03'),(5,'Fidel Castro','1959-01-01');
/*!40000 ALTER TABLE `Members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Projects`
--

DROP TABLE IF EXISTS `Projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Projects` (
  `id_proj` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` char(50) DEFAULT NULL,
  `p_desc` char(250) DEFAULT NULL,
  `id_coord` int(11) DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  PRIMARY KEY (`id_proj`),
  KEY `fk_coord` (`id_coord`),
  CONSTRAINT `fk_coord` FOREIGN KEY (`id_coord`) REFERENCES `Members` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Projects`
--

LOCK TABLES `Projects` WRITE;
/*!40000 ALTER TABLE `Projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `Projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tasks`
--

DROP TABLE IF EXISTS `Tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tasks` (
  `id_prim` int(11) NOT NULL AUTO_INCREMENT,
  `id_sec` int(11) NOT NULL,
  `id_proj` int(11) DEFAULT NULL,
  `id_author` int(11) NOT NULL,
  `t_name` char(50) NOT NULL,
  `t_desc` char(250) DEFAULT NULL,
  `in_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `priority` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `id_worker` int(11) DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `end_comment` char(250) DEFAULT NULL,
  PRIMARY KEY (`id_prim`),
  KEY `fk_avtor` (`id_author`),
  KEY `fk_worker` (`id_worker`),
  KEY `fk_proj` (`id_proj`),
  CONSTRAINT `fk_avtor` FOREIGN KEY (`id_author`) REFERENCES `Members` (`id_member`),
  CONSTRAINT `fk_worker` FOREIGN KEY (`id_worker`) REFERENCES `Members` (`id_member`),
  CONSTRAINT `fk_proj` FOREIGN KEY (`id_proj`) REFERENCES `Projects` (`id_proj`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tasks`
--

LOCK TABLES `Tasks` WRITE;
/*!40000 ALTER TABLE `Tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `Tasks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-08-24 17:59:51
