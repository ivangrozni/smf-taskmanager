-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: taskmanager
-- ------------------------------------------------------
-- Server version	5.5.37-0ubuntu0.12.04.1

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
-- Table structure for table `projekti`
--

DROP TABLE IF EXISTS `projekti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projekti` (
  `id_proj` int(11) NOT NULL DEFAULT '0',
  `ime` char(50) DEFAULT NULL,
  `opis` char(250) DEFAULT NULL,
  `dat_zac` date DEFAULT NULL,
  `dat_konc` date DEFAULT NULL,
  `id_koordinator` int(11) DEFAULT NULL,
  `komentar` char(250) DEFAULT NULL,
  PRIMARY KEY (`id_proj`),
  KEY `fk_member` (`id_koordinator`),
  CONSTRAINT `fk_member` FOREIGN KEY (`id_koordinator`) REFERENCES `uporabniki` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projekti`
--

LOCK TABLES `projekti` WRITE;
/*!40000 ALTER TABLE `projekti` DISABLE KEYS */;
INSERT INTO `projekti` VALUES (1,'glasilo','agitacija, smekancija, erekcija','0000-00-00','0000-00-00',5,'se v teku'),(2,'protesti','organiziranje sirsih ljudskih mnozic','0000-00-00','0000-00-00',3,'2000 udelezencev protesta - sprejemljiva uspesnost'),(3,'interno izobrazovanje','nujna teoretska podlaga za analizo in razumevanja druzbe in njenih pojavov','0000-00-00','0000-00-00',4,'zakljuceno - stevilo udelezencev se je s casom zmanjsevalo in se ustalilo na 20');
/*!40000 ALTER TABLE `projekti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `id_thread` int(11) NOT NULL DEFAULT '0',
  `besedilo` char(250) DEFAULT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_thread`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread`
--

LOCK TABLES `thread` WRITE;
/*!40000 ALTER TABLE `thread` DISABLE KEYS */;
INSERT INTO `thread` VALUES (1,'gre kdo na pir','2014-07-17 09:33:58'),(2,'moram na wc','2014-07-17 09:34:28'),(3,'Miro Cerar je car, ker je nadideoloski','2014-07-17 09:35:44');
/*!40000 ALTER TABLE `thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uporabniki`
--

DROP TABLE IF EXISTS `uporabniki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uporabniki` (
  `id_member` int(11) NOT NULL DEFAULT '0',
  `ime` char(50) DEFAULT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uporabniki`
--

LOCK TABLES `uporabniki` WRITE;
/*!40000 ALTER TABLE `uporabniki` DISABLE KEYS */;
INSERT INTO `uporabniki` VALUES (1,'molotov'),(2,'stalin'),(3,'lenin'),(4,'marx'),(5,'rosa');
/*!40000 ALTER TABLE `uporabniki` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zadolzitve`
--

DROP TABLE IF EXISTS `zadolzitve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zadolzitve` (
  `id_zad_prim` int(11) NOT NULL DEFAULT '0',
  `id_zad_sec` int(11) DEFAULT NULL,
  `id_proj` int(11) DEFAULT NULL,
  `id_autor` int(11) DEFAULT NULL,
  `id_uporabnik` int(11) DEFAULT NULL,
  `id_thread` int(11) DEFAULT NULL,
  `ime_zad` char(50) DEFAULT NULL,
  `opis_zad` char(250) DEFAULT NULL,
  `komentar_zad` char(250) DEFAULT NULL,
  `dat_vnos` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dat_zac` date DEFAULT NULL,
  `dat_konc` date DEFAULT NULL,
  `rok` datetime DEFAULT NULL,
  `porabl_cas` decimal(6,0) DEFAULT NULL,
  `predv_cas` decimal(6,0) DEFAULT NULL,
  `vidnost` int(11) DEFAULT NULL,
  `stanje` int(11) DEFAULT NULL,
  `pomembnost` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_zad_prim`),
  KEY `fk_member_a` (`id_autor`),
  KEY `fk_user` (`id_uporabnik`),
  KEY `fk_proj` (`id_proj`),
  KEY `fk_thread` (`id_thread`),
  CONSTRAINT `fk_member_a` FOREIGN KEY (`id_autor`) REFERENCES `uporabniki` (`id_member`),
  CONSTRAINT `fk_user` FOREIGN KEY (`id_uporabnik`) REFERENCES `uporabniki` (`id_member`),
  CONSTRAINT `fk_proj` FOREIGN KEY (`id_proj`) REFERENCES `projekti` (`id_proj`),
  CONSTRAINT `fk_thread` FOREIGN KEY (`id_thread`) REFERENCES `thread` (`id_thread`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zadolzitve`
--

LOCK TABLES `zadolzitve` WRITE;
/*!40000 ALTER TABLE `zadolzitve` DISABLE KEYS */;
INSERT INTO `zadolzitve` VALUES (1,1,2,3,1,1,'agitacijaFFa','sestavi urnik, govori v predavalnicah','v teku','2014-07-17 10:15:17','2014-03-30','2014-04-04','2014-04-04 00:00:01',17,20,1,1,2),(2,2,2,3,1,1,'Plakatiranje','prevzem plakatov, kuhanje lepila, sprehod po mestu','v teku','2014-07-17 10:15:22','2014-04-01','2014-04-04','2014-04-04 08:00:00',9,8,1,1,2),(3,2,2,3,2,1,'Plakatiranje','prevzem plakatov, kuhanje lepila, sprehod po mestu','v teku','2014-03-29 21:01:00','2014-04-01','2014-04-04','2014-04-04 08:00:00',9,8,1,1,2);
/*!40000 ALTER TABLE `zadolzitve` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-07-17 12:34:08
