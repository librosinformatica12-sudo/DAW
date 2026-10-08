-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: dinocards
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `ultima_obtencion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'triceraantonio75','antonio.suarez36@icloud.com','263767ec6d91aa58da1c2ad5fda96476','2025-10-21 02:47:43','2026-09-25 15:33:58'),(2,'eva_saurio93','eva.gallardo@gmail.com','0d4acc123a9b88e689ad66f653f8cf62','2025-12-05 19:49:05','2026-08-26 12:32:25'),(3,'estherdel55','esther.delgado@gmail.com','e1a3582bbd2f9c4a52def22f0d5d480a','2026-01-29 01:38:21','2026-08-28 11:23:03'),(4,'cretacicosalvador55','salvador.sanz@icloud.com','0e471cac6e3bbab3dd538fd9f328f186','2026-02-09 13:41:26','2026-09-27 19:22:13'),(5,'stegojulia30','julia.ferrer@icloud.com','38d1512f32820763c30ade6d691cae94','2026-02-18 08:36:53','2026-08-09 03:31:49'),(6,'mariaflo59','maria.flores@gmail.com','6d5a5cce9e2ab3b33cb7a92f2543e574','2026-03-28 08:46:57','2026-06-12 05:29:43'),(7,'araceli_raptor6','araceli.martinez@gmail.com','a2e4bcade15582a5457fcc0bf99366e9','2026-06-05 02:01:48','2026-09-21 17:50:06'),(8,'armando_tricera57','armando.ortiz@icloud.com','0fca78eb359b46b6fad87389360ce143','2026-06-23 16:21:35','2026-09-26 02:36:52'),(9,'rexleire49','leire.guerrero@comcast.net','9fbfb391dcd78c072784de93d78f89e4','2026-06-25 11:07:53','2026-09-27 09:41:51'),(10,'miguel_rex91','miguel.ortiz@icloud.com','b579717c9d3458ac8637bdcdf493b0cd','2026-09-08 00:33:12','2026-09-28 01:00:53'),(19,'oscar','oscar@gmail.com','81dc9bdb52d04dc20036dbd8313ed055','2026-10-08 10:33:55',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-08 13:02:54
