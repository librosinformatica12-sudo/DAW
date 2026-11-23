-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: dinocards
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `dinocards`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dinocards` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `dinocards`;

--
-- Table structure for table `colecciones`
--

DROP TABLE IF EXISTS `colecciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colecciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `dinosaurio_id` int DEFAULT NULL,
  `fecha_adquisicion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `dinosaurio_id` (`dinosaurio_id`),
  CONSTRAINT `colecciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `colecciones_ibfk_2` FOREIGN KEY (`dinosaurio_id`) REFERENCES `dinosaurios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=336 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colecciones`
--

LOCK TABLES `colecciones` WRITE;
/*!40000 ALTER TABLE `colecciones` DISABLE KEYS */;
INSERT INTO `colecciones` VALUES (1,1,74,'2025-10-21 21:54:39'),(2,1,91,'2025-10-22 14:33:01'),(3,1,84,'2025-11-06 02:00:05'),(4,1,81,'2025-11-11 12:34:21'),(5,2,65,'2025-12-07 06:51:21'),(6,2,47,'2025-12-08 13:07:24'),(7,2,41,'2025-12-11 17:38:00'),(8,1,22,'2025-12-18 01:21:30'),(9,1,90,'2026-01-11 07:17:42'),(10,1,42,'2026-01-14 08:29:12'),(11,1,57,'2026-01-15 03:35:52'),(12,1,62,'2026-01-21 10:52:55'),(13,3,85,'2026-01-30 13:31:29'),(14,1,85,'2026-02-04 23:35:11'),(15,1,91,'2026-02-05 02:12:27'),(16,4,71,'2026-02-09 20:20:54'),(17,4,90,'2026-02-10 15:50:45'),(18,4,28,'2026-02-11 08:44:57'),(19,4,29,'2026-02-12 10:39:47'),(20,4,100,'2026-02-17 11:21:31'),(21,5,56,'2026-02-18 17:13:54'),(22,4,21,'2026-02-19 08:35:25'),(23,4,96,'2026-02-19 14:10:52'),(24,4,71,'2026-02-20 22:16:11'),(25,1,58,'2026-02-22 20:53:20'),(26,1,83,'2026-02-28 02:47:52'),(27,4,79,'2026-02-28 03:52:49'),(28,4,21,'2026-03-07 12:38:38'),(29,4,31,'2026-03-07 17:31:15'),(30,5,79,'2026-03-09 12:22:56'),(31,4,65,'2026-03-09 21:30:27'),(32,4,31,'2026-03-10 04:52:37'),(33,4,53,'2026-03-10 16:47:14'),(34,4,19,'2026-03-11 12:24:01'),(35,4,96,'2026-03-13 08:32:52'),(36,4,34,'2026-03-14 12:28:44'),(37,4,59,'2026-03-14 12:32:47'),(38,4,80,'2026-03-15 03:30:51'),(39,1,53,'2026-03-15 09:28:46'),(40,4,13,'2026-03-19 06:22:15'),(41,1,33,'2026-03-19 14:46:23'),(42,4,7,'2026-03-19 18:49:04'),(43,1,47,'2026-03-22 13:23:44'),(44,4,36,'2026-03-23 17:59:51'),(45,4,77,'2026-03-23 23:39:47'),(46,4,38,'2026-03-25 12:10:54'),(47,4,95,'2026-03-26 05:24:05'),(48,4,81,'2026-03-27 13:59:00'),(49,4,100,'2026-03-30 03:18:48'),(50,6,13,'2026-03-30 04:24:24'),(51,4,41,'2026-04-01 21:01:53'),(52,4,51,'2026-04-04 07:00:18'),(53,4,15,'2026-04-10 12:06:31'),(54,5,76,'2026-04-15 19:43:34'),(55,5,85,'2026-04-15 21:41:48'),(56,4,64,'2026-04-16 22:42:30'),(57,4,65,'2026-04-17 00:26:00'),(58,4,2,'2026-04-18 17:15:30'),(59,4,7,'2026-04-20 07:46:09'),(60,4,95,'2026-04-21 05:50:20'),(61,4,62,'2026-04-25 13:12:25'),(62,4,64,'2026-04-29 04:50:35'),(63,4,53,'2026-05-01 02:53:51'),(64,4,82,'2026-05-01 19:54:44'),(65,4,27,'2026-05-04 00:28:14'),(66,5,14,'2026-05-04 15:41:13'),(67,4,36,'2026-05-04 17:21:02'),(68,4,21,'2026-05-04 23:26:33'),(69,4,89,'2026-05-05 06:03:04'),(70,4,58,'2026-05-06 11:43:58'),(71,1,31,'2026-05-06 17:03:00'),(72,4,89,'2026-05-07 16:05:32'),(73,4,65,'2026-05-08 20:31:42'),(74,4,11,'2026-05-09 17:43:31'),(75,4,14,'2026-05-10 23:32:02'),(76,4,64,'2026-05-11 15:33:23'),(77,4,34,'2026-05-12 11:00:07'),(78,4,25,'2026-05-12 19:47:04'),(79,4,99,'2026-05-13 06:35:00'),(80,5,17,'2026-05-14 17:04:03'),(81,4,61,'2026-05-15 04:10:19'),(82,4,41,'2026-05-19 14:37:12'),(83,4,95,'2026-05-21 06:07:55'),(84,4,19,'2026-05-23 12:52:18'),(85,4,91,'2026-05-24 04:58:40'),(86,4,24,'2026-05-24 12:50:27'),(87,1,50,'2026-05-25 03:22:00'),(88,4,4,'2026-05-25 20:16:18'),(89,4,60,'2026-05-26 02:47:07'),(90,4,47,'2026-05-26 16:26:09'),(91,4,33,'2026-05-28 19:56:33'),(92,4,24,'2026-06-05 04:34:28'),(93,7,50,'2026-06-05 04:39:57'),(94,4,83,'2026-06-08 03:39:55'),(95,7,99,'2026-06-08 05:31:07'),(96,4,96,'2026-06-08 06:26:13'),(97,6,16,'2026-06-12 05:29:43'),(98,1,48,'2026-06-14 10:32:28'),(99,7,8,'2026-06-14 15:44:00'),(100,1,100,'2026-06-14 20:22:25'),(101,4,18,'2026-06-15 20:53:10'),(102,2,46,'2026-06-16 14:05:00'),(103,4,76,'2026-06-18 16:02:20'),(104,7,71,'2026-06-19 15:52:19'),(105,7,93,'2026-06-20 02:01:16'),(106,7,52,'2026-06-20 05:58:07'),(107,4,2,'2026-06-22 03:51:19'),(108,7,55,'2026-06-22 04:57:26'),(109,7,35,'2026-06-23 07:42:23'),(110,7,38,'2026-06-23 21:46:50'),(111,4,84,'2026-06-24 02:46:19'),(112,4,97,'2026-06-24 14:38:01'),(113,8,39,'2026-06-25 07:48:31'),(114,8,83,'2026-06-25 08:16:00'),(115,7,48,'2026-06-25 17:53:31'),(116,8,12,'2026-06-25 19:11:31'),(117,7,50,'2026-06-26 07:06:12'),(118,9,53,'2026-06-26 09:44:05'),(119,9,8,'2026-06-26 15:10:11'),(120,5,90,'2026-06-26 21:48:48'),(121,8,94,'2026-06-27 09:00:52'),(122,4,95,'2026-06-27 10:17:46'),(123,8,73,'2026-06-27 20:25:41'),(124,8,66,'2026-06-27 20:58:22'),(125,7,56,'2026-06-28 11:30:23'),(126,8,74,'2026-06-28 16:26:40'),(127,8,85,'2026-06-30 12:21:08'),(128,9,90,'2026-07-01 04:17:44'),(129,8,51,'2026-07-02 02:11:25'),(130,9,38,'2026-07-02 02:58:06'),(131,8,74,'2026-07-02 07:57:13'),(132,7,9,'2026-07-04 19:51:42'),(133,8,90,'2026-07-05 02:12:39'),(134,8,100,'2026-07-05 05:57:57'),(135,8,84,'2026-07-08 14:01:06'),(136,7,37,'2026-07-09 20:00:36'),(137,9,52,'2026-07-11 05:04:41'),(138,8,23,'2026-07-11 05:39:40'),(139,1,34,'2026-07-11 16:31:12'),(140,9,53,'2026-07-12 23:54:33'),(141,4,13,'2026-07-13 00:46:32'),(142,8,96,'2026-07-13 03:33:47'),(143,9,7,'2026-07-13 04:03:22'),(144,1,6,'2026-07-14 01:13:13'),(145,8,26,'2026-07-15 14:35:51'),(146,9,80,'2026-07-15 15:21:59'),(147,7,17,'2026-07-15 19:06:41'),(148,8,47,'2026-07-15 22:23:53'),(149,8,71,'2026-07-15 23:37:35'),(150,9,94,'2026-07-16 02:20:12'),(151,8,25,'2026-07-16 10:49:29'),(152,9,15,'2026-07-16 12:07:12'),(153,7,98,'2026-07-16 15:02:53'),(154,9,3,'2026-07-16 17:37:39'),(155,7,80,'2026-07-17 11:29:06'),(156,8,87,'2026-07-17 21:24:16'),(157,7,84,'2026-07-18 01:41:19'),(158,8,28,'2026-07-18 13:48:01'),(159,8,65,'2026-07-20 03:57:28'),(160,4,18,'2026-07-21 01:08:22'),(161,8,74,'2026-07-21 01:31:02'),(162,8,87,'2026-07-21 15:37:45'),(163,8,90,'2026-07-22 08:46:32'),(164,8,1,'2026-07-22 08:49:42'),(165,8,12,'2026-07-22 15:48:44'),(166,3,35,'2026-07-22 22:48:24'),(167,4,42,'2026-07-23 11:49:43'),(168,3,100,'2026-07-23 18:21:29'),(169,1,80,'2026-07-24 09:26:39'),(170,8,89,'2026-07-24 11:49:57'),(171,4,53,'2026-07-25 07:26:04'),(172,4,56,'2026-07-25 09:30:11'),(173,8,58,'2026-07-26 18:36:28'),(174,8,42,'2026-07-26 20:21:33'),(175,4,86,'2026-07-27 05:25:10'),(176,4,51,'2026-07-27 18:57:24'),(177,5,94,'2026-07-28 02:42:30'),(178,4,52,'2026-07-29 08:31:17'),(179,9,26,'2026-07-29 15:01:38'),(180,4,26,'2026-07-31 19:19:35'),(181,8,70,'2026-08-01 10:30:13'),(182,7,42,'2026-08-01 17:07:00'),(183,8,37,'2026-08-02 07:23:43'),(184,9,49,'2026-08-02 21:42:57'),(185,8,66,'2026-08-03 14:51:07'),(186,8,67,'2026-08-04 00:57:17'),(187,8,68,'2026-08-04 04:39:57'),(188,9,80,'2026-08-04 10:44:21'),(189,4,81,'2026-08-05 05:19:52'),(190,9,60,'2026-08-05 11:14:50'),(191,8,34,'2026-08-05 20:38:24'),(192,7,50,'2026-08-06 09:30:18'),(193,9,13,'2026-08-07 03:14:55'),(194,5,26,'2026-08-07 14:28:38'),(195,9,36,'2026-08-08 00:19:04'),(196,8,65,'2026-08-08 12:38:05'),(197,5,81,'2026-08-09 03:31:49'),(198,8,98,'2026-08-09 18:17:29'),(199,4,47,'2026-08-10 02:28:59'),(200,4,24,'2026-08-11 00:07:26'),(201,8,37,'2026-08-11 01:06:42'),(202,9,59,'2026-08-11 08:58:23'),(203,4,28,'2026-08-12 02:46:45'),(204,4,36,'2026-08-12 14:05:15'),(205,4,41,'2026-08-12 20:11:43'),(206,9,82,'2026-08-13 19:47:18'),(207,1,67,'2026-08-14 05:57:19'),(208,8,8,'2026-08-14 21:29:54'),(209,9,99,'2026-08-15 05:37:43'),(210,8,81,'2026-08-15 14:00:57'),(211,7,65,'2026-08-15 23:26:26'),(212,8,91,'2026-08-16 07:10:22'),(213,8,65,'2026-08-16 08:38:45'),(214,8,96,'2026-08-16 09:47:57'),(215,8,58,'2026-08-16 20:56:36'),(216,4,85,'2026-08-17 06:18:27'),(217,9,90,'2026-08-17 16:48:51'),(218,7,70,'2026-08-17 21:32:23'),(219,1,72,'2026-08-18 06:01:19'),(220,3,89,'2026-08-18 18:33:06'),(221,8,21,'2026-08-18 23:40:06'),(222,4,93,'2026-08-19 07:55:58'),(223,8,55,'2026-08-19 13:58:03'),(224,8,73,'2026-08-19 23:58:50'),(225,8,46,'2026-08-20 04:34:05'),(226,8,66,'2026-08-21 14:18:36'),(227,8,44,'2026-08-21 22:42:44'),(228,7,50,'2026-08-22 11:41:04'),(229,4,42,'2026-08-24 04:31:12'),(230,8,3,'2026-08-25 08:40:53'),(231,7,53,'2026-08-25 09:52:55'),(232,8,65,'2026-08-25 12:49:45'),(233,8,54,'2026-08-25 18:28:24'),(234,8,35,'2026-08-25 19:10:46'),(235,7,16,'2026-08-26 10:09:21'),(236,2,45,'2026-08-26 12:32:25'),(237,8,66,'2026-08-26 21:31:58'),(238,9,13,'2026-08-27 01:48:58'),(239,8,28,'2026-08-28 01:56:40'),(240,7,13,'2026-08-28 02:03:55'),(241,3,24,'2026-08-28 11:23:03'),(242,4,82,'2026-08-28 21:51:03'),(243,8,24,'2026-08-29 12:45:20'),(244,9,36,'2026-08-29 15:03:58'),(245,8,49,'2026-08-30 10:18:49'),(246,9,23,'2026-08-31 18:47:26'),(247,7,59,'2026-08-31 18:51:27'),(248,1,42,'2026-09-01 11:02:20'),(249,8,72,'2026-09-01 13:42:25'),(250,1,17,'2026-09-02 11:21:42'),(251,8,22,'2026-09-03 02:46:50'),(252,7,43,'2026-09-03 15:14:55'),(253,9,65,'2026-09-05 04:39:26'),(254,8,34,'2026-09-05 10:14:29'),(255,7,51,'2026-09-06 10:21:47'),(256,8,47,'2026-09-08 10:34:03'),(257,10,95,'2026-09-08 19:53:51'),(258,7,59,'2026-09-09 05:06:44'),(259,9,47,'2026-09-09 09:38:53'),(260,9,33,'2026-09-10 15:13:55'),(261,9,77,'2026-09-10 21:50:53'),(262,8,65,'2026-09-10 23:40:42'),(263,1,53,'2026-09-11 00:26:54'),(264,7,57,'2026-09-11 12:20:12'),(265,8,83,'2026-09-11 12:31:41'),(266,8,96,'2026-09-11 13:24:24'),(267,7,55,'2026-09-11 15:07:59'),(268,9,73,'2026-09-11 16:22:53'),(269,10,40,'2026-09-11 16:34:24'),(270,7,65,'2026-09-11 22:45:34'),(271,4,50,'2026-09-12 19:37:26'),(272,9,74,'2026-09-12 20:04:27'),(273,8,57,'2026-09-12 22:43:44'),(274,7,34,'2026-09-13 02:58:33'),(275,9,48,'2026-09-14 01:43:07'),(276,4,27,'2026-09-14 04:43:47'),(277,10,68,'2026-09-15 02:45:20'),(278,10,75,'2026-09-15 16:59:47'),(279,7,89,'2026-09-15 17:17:40'),(280,9,75,'2026-09-15 19:51:10'),(281,9,13,'2026-09-16 04:23:30'),(282,8,98,'2026-09-17 23:20:36'),(283,4,38,'2026-09-18 03:58:11'),(284,4,91,'2026-09-18 18:00:23'),(285,4,22,'2026-09-19 02:22:00'),(286,8,100,'2026-09-19 16:40:42'),(287,10,56,'2026-09-19 19:10:57'),(288,10,36,'2026-09-20 12:53:49'),(289,7,19,'2026-09-20 18:16:21'),(290,7,24,'2026-09-21 14:34:30'),(291,7,73,'2026-09-21 17:50:06'),(292,8,21,'2026-09-22 02:10:24'),(293,10,81,'2026-09-22 09:20:56'),(294,4,18,'2026-09-22 09:45:53'),(295,8,59,'2026-09-23 08:31:36'),(296,8,65,'2026-09-23 17:20:38'),(297,9,29,'2026-09-24 08:00:26'),(298,9,65,'2026-09-25 03:08:00'),(299,4,97,'2026-09-25 03:15:51'),(300,1,96,'2026-09-25 15:33:58'),(301,10,7,'2026-09-25 19:18:37'),(302,8,46,'2026-09-26 02:36:52'),(303,9,98,'2026-09-26 21:33:13'),(304,9,74,'2026-09-27 09:41:51'),(305,4,24,'2026-09-27 19:22:13'),(306,10,42,'2026-09-28 01:00:53'),(307,5,42,'2026-10-12 17:59:09'),(308,19,29,'2026-10-12 18:33:54'),(311,19,3,'2026-10-12 20:36:13'),(312,20,1,'2026-10-12 21:21:30'),(313,20,13,'2026-10-12 21:22:26'),(316,20,1,'2026-10-12 21:53:23'),(317,20,1,'2026-10-12 21:53:24'),(318,20,13,'2026-10-14 09:57:37'),(324,19,53,'2026-10-14 12:52:09'),(332,19,36,'2026-10-14 13:37:49'),(333,19,59,'2026-10-14 13:37:49'),(334,19,66,'2026-10-14 13:37:49'),(335,19,86,'2026-10-14 13:37:49');
/*!40000 ALTER TABLE `colecciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dinosaurios`
--

DROP TABLE IF EXISTS `dinosaurios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dinosaurios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(64) DEFAULT NULL,
  `especie` varchar(1024) DEFAULT NULL,
  `periodo` varchar(1024) DEFAULT NULL,
  `imagen_url` varchar(1024) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `largo` decimal(4,2) DEFAULT NULL,
  `peso` decimal(6,3) DEFAULT NULL,
  `hp` int DEFAULT NULL,
  `vigor` int DEFAULT NULL,
  `ataque` int DEFAULT NULL,
  `defensa` int DEFAULT NULL,
  `agilidad` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dinosaurios`
--

LOCK TABLES `dinosaurios` WRITE;
/*!40000 ALTER TABLE `dinosaurios` DISABLE KEYS */;
INSERT INTO `dinosaurios` VALUES (1,'Tiranosaurio Rex','Tyrannosaurus rex','Cretácico Superior (68-66 Ma)','img/img-cartas/1.png',4.00,12.50,8.400,92,85,98,72,45),(2,'Triceratops','Triceratops horridus','Cretácico Superior (67-66 Ma)','img/img-cartas/2.png',2.80,9.00,6.500,88,80,78,92,38),(3,'Velociraptor','Velociraptor mongoliensis','Cretácico Superior (75-71 Ma)','img/img-cartas/3.png',0.50,1.80,0.015,32,48,82,42,96),(4,'Estegosaurio','Stegosaurus stenops','Jurásico Superior (155-150 Ma)','img/img-cartas/4.png',2.90,9.00,2.000,85,75,68,96,35),(5,'Braquiosaurio','Brachiosaurus altithorax','Jurásico Superior (154-153 Ma)','img/img-cartas/5.png',13.00,26.00,56.000,98,92,45,88,22),(6,'Espinosauro','Spinosaurus aegyptiacus','Cretácico Superior (112-97 Ma)','img/img-cartas/6.png',5.20,17.00,7.000,94,87,96,75,42),(7,'Parasaurolofus','Parasaurolophus walkeri','Cretácico Superior (75-72 Ma)','img/img-cartas/7.png',2.50,9.50,2.500,72,70,48,65,62),(8,'Anquilosaurio','Ankylosaurus magniventris','Cretácico Superior (68-66 Ma)','img/img-cartas/8.png',1.70,10.50,4.800,86,78,52,98,28),(9,'Pteranodon','Pteranodon longiceps','Cretácico Superior (88-86 Ma)','img/img-cartas/9.png',1.80,7.50,0.020,38,52,58,38,88),(10,'Allosaurio','Allosaurus fragilis','Jurásico Superior (155-145 Ma)','img/img-cartas/10.png',2.70,8.50,2.300,78,75,92,62,68),(11,'Diplodoco','Diplodocus longus','Jurásico Superior (154-152 Ma)','img/img-cartas/11.png',3.60,27.00,16.000,82,85,38,72,35),(12,'Trictops Bebé','Triceratops prorsus','Cretácico Superior (68-66 Ma)','img/img-cartas/12.png',1.20,2.50,0.150,42,48,45,72,58),(13,'Compsognato','Compsognathus longipes','Jurásico Superior (150-148 Ma)','img/img-cartas/13.png',0.25,0.90,0.003,18,35,68,22,98),(14,'Argentinosaurio','Argentinosaurus huinculensis','Cretácico Superior (95-100 Ma)','img/img-cartas/14.png',18.00,35.00,70.000,99,95,42,92,18),(15,'Ictiosaurio','Ichthyosaurus communis','Jurásico Inferior (194-189 Ma)','img/img-cartas/15.png',1.80,2.00,0.190,62,68,72,52,78),(16,'Plesiosaurio','Plesiosaurus dolichodeirus','Jurásico Inferior (200-175 Ma)','img/img-cartas/16.png',1.50,11.50,4.500,75,72,76,65,74),(17,'Mosasaurio','Mosasaurus missouriensis','Cretácico Superior (82-66 Ma)','img/img-cartas/17.png',3.00,17.50,15.000,90,82,94,68,58),(18,'Iguanodonte','Iguanodon bernissartensis','Cretácico Inferior (130-125 Ma)','img/img-cartas/18.png',4.00,10.00,3.500,74,72,62,68,52),(19,'Ceratosaurio','Carnotaurus sastrei','Cretácico Inferior (100-95 Ma)','img/img-cartas/19.png',2.50,7.50,1.950,76,73,88,58,72),(20,'Tarbosauro','Tarbosaurus bataar','Cretácico Superior (70-66 Ma)','img/img-cartas/20.png',3.80,12.00,5.000,86,80,94,68,52),(21,'Protosaurio','Protosauropod theodor','Triásico Superior (228-220 Ma)','img/img-cartas/21.png',1.20,2.50,0.080,35,42,42,38,68),(22,'Pterodáctilo','Pterodactyl kochi','Jurásico Superior (150-148 Ma)','img/img-cartas/22.png',0.80,3.50,0.008,28,42,52,32,92),(23,'Quetzalcóatl','Quetzalcoatlus northropi','Cretácico Superior (70-66 Ma)','img/img-cartas/23.png',3.50,11.00,0.150,52,62,68,45,85),(24,'Sinornitosaurio','Sinosauropteryx prima','Cretácico Inferior (125-122 Ma)','img/img-cartas/24.png',0.60,1.00,0.002,25,38,65,28,95),(25,'Apatosaurio','Apatosaurus louisae','Jurásico Superior (154-150 Ma)','img/img-cartas/25.png',8.50,21.00,18.000,88,88,48,82,28),(26,'Tricerátope de Larga Corna','Torosaurus latus','Cretácico Superior (68-66 Ma)','img/img-cartas/26.png',3.20,8.50,5.500,85,78,72,88,42),(27,'Ovirraptor','Oviraptor philceratops','Cretácico Superior (81-75 Ma)','img/img-cartas/27.png',1.50,2.50,0.025,38,52,78,48,88),(28,'Gallimimo','Gallimimus bullatus','Cretácico Superior (75-71 Ma)','img/img-cartas/28.png',2.00,6.00,0.350,48,58,54,42,92),(29,'Iguanodón Mayor','Iguanodon mantelli','Cretácico Inferior (135-125 Ma)','img/img-cartas/29.png',4.50,11.00,4.200,78,75,65,72,48),(30,'Deinoquiro','Deinochirus mirificus','Cretácico Superior (80-73 Ma)','img/img-cartas/30.png',3.50,11.00,0.600,62,68,72,52,78),(31,'Tiranosaurio Bebé','Tyrannosaurus rex (juvenil)','Cretácico Superior (68-66 Ma)','img/img-cartas/31.png',2.00,5.00,0.500,52,58,72,52,68),(32,'Placerias','Placerias hesternus','Triásico Superior (227-220 Ma)','img/img-cartas/32.png',1.10,3.50,0.200,48,55,48,62,35),(33,'Nothosauro','Nothosaurus mirabilis','Triásico Superior (235-228 Ma)','img/img-cartas/33.png',0.80,1.50,0.015,42,50,68,38,80),(34,'Ópistocélico','Opisthoteuthis granulosa','Jurásico Medio (175-160 Ma)','img/img-cartas/34.png',2.20,7.50,0.400,55,60,62,48,75),(35,'Saurópodo Pequeño','Camarasaurus supremus','Jurásico Superior (155-145 Ma)','img/img-cartas/35.png',6.00,18.00,20.000,84,86,45,78,32),(36,'Dimetrodonte','Dimetrodon grandis','Pérmico Superior (260-254 Ma)','img/img-cartas/36.png',0.40,1.20,0.008,32,45,58,35,72),(37,'Giganotosauro','Giganotosaurus carolinii','Cretácico Inferior (100-93 Ma)','img/img-cartas/37.png',4.20,13.20,8.800,93,86,97,70,48),(38,'Maniraptorá','Deinonychus antirrhopus','Cretácico Inferior (125-120 Ma)','img/img-cartas/38.png',1.10,3.40,0.073,42,55,85,45,92),(39,'Therizinosaurio','Therizinosaurus cheloniformis','Cretácico Superior (90-88 Ma)','img/img-cartas/39.png',3.00,5.50,5.000,78,75,68,85,35),(40,'Trodonte','Troodon formosus','Cretácico Superior (77-75 Ma)','img/img-cartas/40.png',0.60,2.00,0.050,35,48,72,38,94),(41,'Sauroposeidón','Sauroposeidon proteles','Cretácico Inferior (125-120 Ma)','img/img-cartas/41.png',15.50,34.00,60.000,98,93,48,89,20),(42,'Ictiteria','Ichthyomis dispar','Cretácico Superior (85-80 Ma)','img/img-cartas/42.png',0.30,0.40,0.001,20,38,58,25,88),(43,'Paquicefalosauro','Pachycephalosaurus wyomingensis','Cretácico Superior (69-66 Ma)','img/img-cartas/43.png',2.40,4.60,0.400,68,70,75,82,58),(44,'Ornitorrinco Prehistórico','Stegoceras validum','Cretácico Superior (75-72 Ma)','img/img-cartas/44.png',1.80,2.10,0.150,48,55,62,68,68),(45,'Saurópodo Acuático','Rebbachisaurus gaundi','Cretácico Inferior (125-113 Ma)','img/img-cartas/45.png',8.00,21.00,25.000,90,88,42,80,30),(46,'Raptor Mayor','Utahraptor ostrommaysorum','Cretácico Inferior (125-120 Ma)','img/img-cartas/46.png',1.80,7.00,0.500,58,65,88,55,85),(47,'Dinosaurio Volador','Archaeopteryx lithographica','Jurásico Superior (150-148 Ma)','img/img-cartas/47.png',0.40,0.50,0.001,28,42,48,32,90),(48,'Rinoceronte Marino','Leedsichthys problematics','Jurásico Medio (168-155 Ma)','img/img-cartas/48.png',4.00,16.50,45.000,92,84,76,72,42),(49,'Espinosaurido Acuático','Baryonyx walkeri','Cretácico Inferior (130-125 Ma)','img/img-cartas/49.png',2.50,8.50,1.700,74,72,84,58,66),(50,'Microrraptor','Microraptor zhaoianus','Cretácico Inferior (125-122 Ma)','img/img-cartas/50.png',0.35,1.20,0.001,24,40,76,35,97),(51,'Ankylosaurus Coludo','Edmontosaurus regalis','Cretácico Superior (76-74 Ma)','img/img-cartas/51.png',2.20,7.50,2.000,68,66,48,85,56),(52,'Titanosaurio Enano','Diamantinasaurus matildae','Cretácico Inferior (105-100 Ma)','img/img-cartas/52.png',5.00,16.00,12.000,82,84,40,76,26),(53,'Aviador Antiguo','Eudimorphodon ranzii','Triásico Superior (223-220 Ma)','img/img-cartas/53.png',0.30,0.75,0.001,22,38,50,28,89),(54,'Mosasaurio Gigante','Tylosaurus proriger','Cretácico Superior (85-80 Ma)','img/img-cartas/54.png',3.50,20.00,20.000,92,84,95,70,55),(55,'Ceratopsio Menor','Protoceratops andrewsi','Cretácico Superior (80-75 Ma)','img/img-cartas/55.png',0.90,1.80,0.180,38,48,52,65,72),(56,'Plesiosaurio Largo','Cryptocleidus oxoniensis','Jurásico Medio (165-155 Ma)','img/img-cartas/56.png',1.20,13.00,5.000,78,75,74,68,72),(57,'Pterosaurio Dentado','Anhanguera blittersdorfi','Cretácico Inferior (112-108 Ma)','img/img-cartas/57.png',1.50,4.20,0.020,36,50,62,40,87),(58,'Dromeosaurio Primitivo','Rahonavis ostromi','Cretácico Superior (70-66 Ma)','img/img-cartas/58.png',0.50,0.80,0.008,28,44,74,36,96),(59,'Saurópodo de Cuello Corto','Mamenchisaurus sinocanadorum','Jurásico Superior (160-155 Ma)','img/img-cartas/59.png',7.50,25.00,22.000,86,87,44,80,25),(60,'Espinosaurio Menor','Sigilmassasaurus brevicollis','Cretácico Inferior (120-116 Ma)','img/img-cartas/60.png',3.80,12.00,4.000,78,75,88,68,55),(61,'Ictiosaurio Gigante','Temnodontosaurus platyodon','Jurásico Inferior (195-190 Ma)','img/img-cartas/61.png',2.50,9.00,5.000,82,78,85,62,75),(62,'Ornitomímido','Ornithomimus edmontonensis','Cretácico Superior (75-71 Ma)','img/img-cartas/62.png',1.80,4.50,0.150,42,62,48,38,94),(63,'Ceratopsio Gigante','Pentaceratops sternbergii','Cretácico Superior (75-72 Ma)','img/img-cartas/63.png',3.50,8.20,8.000,86,82,75,90,42),(64,'Pteranodon Gigante','Pteranodon ingens','Cretácico Superior (89-85 Ma)','img/img-cartas/64.png',2.50,10.50,0.035,44,58,64,42,86),(65,'Eosimio Volador','Icaronycteris index','Cretácico Superior (50-48 Ma)','img/img-cartas/65.png',0.15,0.35,0.001,18,35,45,22,92),(66,'Paquicefalosaurio Pequeño','Homalocephale calathocercos','Cretácico Superior (75-72 Ma)','img/img-cartas/66.png',1.50,2.00,0.080,44,52,58,72,65),(67,'Nodosaurio Blindado','Polacanthus foxii','Cretácico Inferior (130-125 Ma)','img/img-cartas/67.png',1.60,8.00,3.500,82,76,48,96,32),(68,'Reptil Marino Corto','Nothosaurus longispinus','Triásico Medio (242-235 Ma)','img/img-cartas/68.png',1.20,2.20,0.028,48,54,72,42,78),(69,'Carnosaurio Emplumado','Guanlong wucaii','Jurásico Superior (155-150 Ma)','img/img-cartas/69.png',2.20,6.00,1.100,68,70,85,55,75),(70,'Estegosaurio Temprano','Huayangosaurus taibaii','Jurásico Medio (170-165 Ma)','img/img-cartas/70.jpg',1.80,4.00,0.400,58,62,52,82,52),(71,'Ictiosaurio Pequeño','Mixosaurus cornalianus','Triásico Medio (245-242 Ma)','img/img-cartas/71.png',0.80,1.00,0.012,35,46,62,38,82),(72,'Tiranosáurido Enano','Dilong paradoxus','Cretácico Inferior (130-125 Ma)','img/img-cartas/72.png',1.80,3.80,0.400,48,54,78,48,72),(73,'Arqueopterix','Archaeopteryx siemensii','Jurásico Superior (150-148 Ma)','img/img-cartas/73.png',0.25,0.60,0.001,26,40,52,35,92),(74,'Troodón','Troodon inequalis','Cretácico Superior (77-74 Ma)','img/img-cartas/74.png',0.50,2.40,0.050,36,50,75,40,95),(75,'Eoraptor','Eoraptor lunensis','Triásico Superior (228-225 Ma)','img/img-cartas/75.png',0.80,1.60,0.008,32,45,82,38,94),(76,'Tiranosáurido Plumado','Yutyrannus huali','Cretácico Inferior (125-122 Ma)','img/img-cartas/76.png',2.80,9.00,1.400,68,70,88,58,65),(77,'Paquicéfalo','Dracorex hogwartsia','Cretácico Superior (75-72 Ma)','img/img-cartas/77.png',1.60,2.20,0.100,46,54,65,75,68),(78,'Camposaurio Acuático','Suchomimus tenerensis','Cretácico Inferior (121-112 Ma)','img/img-cartas/78.png',3.50,11.00,3.200,76,72,86,60,64),(79,'Tiranosáurido Primitivo','Eotyrannus lengi','Cretácico Inferior (130-125 Ma)','img/img-cartas/79.png',2.40,4.60,0.500,54,62,85,52,68),(80,'Ceratopsio de Cuerno Largo','Kosmoceratops richardsoni','Cretácico Superior (76-74 Ma)','img/img-cartas/80.png',2.80,6.50,3.500,82,78,72,88,48),(81,'Pterosaurio Corto','Dimorphodon macronyx','Jurásico Inferior (199-196 Ma)','img/img-cartas/81.png',0.60,1.00,0.003,28,42,58,36,90),(82,'Carnosaurio Ágil','Megalosaurus bucklandii','Jurásico Medio (170-166 Ma)','img/img-cartas/82.png',2.50,7.50,1.100,72,74,90,54,68),(83,'Dinosaurio Pico de Pato Corto','Hypacrosaurus altispinus','Cretácico Superior (76-74 Ma)','img/img-cartas/83.png',2.70,9.50,2.200,74,72,48,78,56),(84,'Reptil Marino Primitivo','Placodus gigas','Triásico Medio (242-235 Ma)','img/img-cartas/84.png',1.40,2.80,0.045,52,58,68,48,72),(85,'Sauropodomorfo Primitivo','Panphagia protos','Triásico Superior (228-225 Ma)','img/img-cartas/85.png',0.90,1.20,0.010,34,48,42,56,78),(86,'Ictiosaurio Largo','Shonisaurus popularis','Triásico Superior (228-220 Ma)','img/img-cartas/86.png',3.20,15.50,18.000,90,84,82,68,48),(87,'Nodosaurio Gigante','Edmontonia longicauda','Cretácico Superior (75-72 Ma)','img/img-cartas/87.png',1.50,7.50,2.800,80,74,42,94,28),(88,'Pterosaurio Gigante','Azhdarchidae rex','Cretácico Superior (68-66 Ma)','img/img-cartas/88.png',4.20,10.80,0.180,58,64,72,52,74),(89,'Plesiosaurio Marino Gigante','Plesiosauru macrocephalus','Jurásico Inferior (195-190 Ma)','img/img-cartas/89.png',2.20,14.50,8.500,84,80,76,72,64),(90,'Dromeosaurio Volador','Archaeoraptor liaoningensis','Cretácico Inferior (122-120 Ma)','img/img-cartas/90.png',0.45,1.30,0.006,26,42,76,38,94),(91,'Carnosaurio Temprano','Herrerasaurus ischigualastensis','Triásico Superior (228-225 Ma)','img/img-cartas/91.png',1.60,4.20,0.210,46,52,84,44,82),(92,'Anquilosauro con Pica','Saichania chulsanensis','Cretácico Superior (80-75 Ma)','img/img-cartas/92.png',1.70,8.00,3.500,82,76,48,96,26),(93,'Terópodo de Cuello Largo','Erlikoaurus andrewsi','Cretácico Superior (98-92 Ma)','img/img-cartas/93.png',3.50,7.80,8.200,78,65,72,45,68),(94,'Ornitomímido Veloz','Struthiomimus altus','Cretácico Superior (99-66 Ma)','img/img-cartas/94.png',1.80,2.60,0.450,35,75,28,25,92),(95,'Iguanodon Primitivo','Hypsilophodon foxii','Cretácico Inferior (130-125 Ma)','img/img-cartas/95.png',1.20,1.80,0.180,38,68,32,35,85),(96,'Ceratopsio sin Cuernos','Leptoceratops gracilis','Cretácico Superior (100-66 Ma)','img/img-cartas/96.png',1.50,2.10,0.350,42,60,30,40,78),(97,'Plesiosaurio Depredador','Kronosaurus queenslandicus','Cretácico Inferior (130-100 Ma)','img/img-cartas/97.png',1.80,10.50,12.800,88,55,82,68,42),(98,'Saurópodo Gigante','Patagotitan mayorum','Cretácico Superior (100-95 Ma)','img/img-cartas/98.png',18.50,37.00,77.000,98,40,48,85,12),(99,'Ictiosaurio Depredador','Stenopterygius quadriscissus','Jurásico Medio (175-168 Ma)','img/img-cartas/99.png',1.20,4.20,1.800,48,70,65,40,80),(100,'Pterosaurio Cazador','Anhanguera piscator','Cretácico Superior (112-66 Ma)','img/img-cartas/100.png',1.50,3.80,0.850,42,72,58,38,88);
/*!40000 ALTER TABLE `dinosaurios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(64) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `password_hash` varchar(1024) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `ultima_obtencion` datetime DEFAULT NULL,
  `carta_prueba` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'triceraantonio75','antonio.suarez36@icloud.com','263767ec6d91aa58da1c2ad5fda96476','2025-10-21 02:47:43','2026-09-25 15:33:58',NULL),(2,'eva_saurio93','eva.gallardo@gmail.com','0d4acc123a9b88e689ad66f653f8cf62','2025-12-05 19:49:05','2026-08-26 12:32:25',NULL),(3,'estherdel55','esther.delgado@gmail.com','e1a3582bbd2f9c4a52def22f0d5d480a','2026-01-29 01:38:21','2026-08-28 11:23:03',NULL),(4,'cretacicosalvador55','salvador.sanz@icloud.com','0e471cac6e3bbab3dd538fd9f328f186','2026-02-09 13:41:26','2026-09-27 19:22:13',NULL),(5,'stegojulia30','julia.ferrer@icloud.com','38d1512f32820763c30ade6d691cae94','2026-02-18 08:36:53','2026-10-12 17:59:09',NULL),(6,'mariaflo59','maria.flores@gmail.com','6d5a5cce9e2ab3b33cb7a92f2543e574','2026-03-28 08:46:57','2026-06-12 05:29:43',NULL),(7,'araceli_raptor6','araceli.martinez@gmail.com','a2e4bcade15582a5457fcc0bf99366e9','2026-06-05 02:01:48','2026-09-21 17:50:06',NULL),(8,'armando_tricera57','armando.ortiz@icloud.com','0fca78eb359b46b6fad87389360ce143','2026-06-23 16:21:35','2026-09-26 02:36:52',NULL),(9,'rexleire49','leire.guerrero@comcast.net','9fbfb391dcd78c072784de93d78f89e4','2026-06-25 11:07:53','2026-09-27 09:41:51',NULL),(10,'miguel_rex91','miguel.ortiz@icloud.com','b579717c9d3458ac8637bdcdf493b0cd','2026-09-08 00:33:12','2026-09-28 01:00:53',NULL),(19,'oscar','oscar@gmail.com','81dc9bdb52d04dc20036dbd8313ed055','2026-10-12 18:32:46','2026-10-14 12:52:09','2026-10-14 13:37:49'),(20,'admin','admin@gmail.com','81dc9bdb52d04dc20036dbd8313ed055','2026-10-12 19:44:00','2026-10-14 09:57:37',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'dinocards'
--
/*!50003 DROP PROCEDURE IF EXISTS `AñadirNuevaCartaAColeccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `AñadirNuevaCartaAColeccion`(
  IN _usuario_id INT,
  IN _carta_id   INT,
  OUT _res       INT
)
BEGIN
  DECLARE u INT DEFAULT NULL;
  DECLARE d INT DEFAULT NULL;

  SELECT id INTO u FROM usuarios    WHERE id = _usuario_id;
  SELECT id INTO d FROM dinosaurios WHERE id = _carta_id;

  IF u IS NULL THEN
    SET _res = -1;                       -- usuario no existe
  ELSEIF d IS NULL THEN
    SET _res = -2;                       -- dinosaurio no existe
  ELSE
    INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
    VALUES (_usuario_id, _carta_id, SYSDATE());

    UPDATE usuarios SET ultima_obtencion = SYSDATE() WHERE id = _usuario_id;
    SET _res = 0;                        -- OK
  END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Login` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `Login`(
  IN _usuario  VARCHAR(256),
  IN _password VARCHAR(1024),
  OUT _res     INT
)
BEGIN
  DECLARE aux VARCHAR(64);
  SET aux = NULL;

  IF (_usuario IS NULL OR _usuario = '') THEN
    SET _res = -1;                       -- usuario/email vacio
  ELSEIF (_password IS NULL OR _password = '') THEN
    SET _res = -2;                       -- contrasena vacia
  ELSE
    SELECT username INTO aux FROM usuarios
      WHERE (email = _usuario OR username = _usuario)
        AND password_hash = _password
      LIMIT 1;

    IF (aux IS NULL) THEN
      SET _res = -3;                     -- credenciales incorrectas
    ELSE
      SET _res = 0;                      -- OK
      SELECT * FROM usuarios
        WHERE (email = _usuario OR username = _usuario)
          AND password_hash = _password;
    END IF;
  END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ObtenerColeccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `ObtenerColeccion`(
  IN _usuario_id INT,
  OUT _res       INT
)
BEGIN
  SET _res = 0;
  SELECT dinosaurio_id
  FROM colecciones
  WHERE usuario_id = _usuario_id
  ORDER BY dinosaurio_id ASC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Registro` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `Registro`(
  IN _username VARCHAR(64),
  IN _email    VARCHAR(256),
  IN _password VARCHAR(1024),
  OUT _res     INT
)
BEGIN
  DECLARE aux VARCHAR(16);
  SET aux = NULL;

  SELECT username FROM usuarios
    WHERE username LIKE _username OR email LIKE _email
    INTO aux;

  IF (_username LIKE '' OR _email LIKE '') THEN
    SET _res = -1;                       -- usuario/email vacio
  ELSEIF (aux IS NOT NULL) THEN
    SET _res = -2;                       -- ya existe
  ELSEIF (_password LIKE '') THEN
    SET _res = -3;                       -- contrasena vacia
  ELSE
    INSERT INTO usuarios (username, email, password_hash, fecha_registro)
    VALUES (_username, _email, _password, SYSDATE());
    SET _res = 0;                        -- OK
  END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 14:09:15
