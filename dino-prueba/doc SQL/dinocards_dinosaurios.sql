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
-- Table structure for table `dinosaurios`
--

DROP TABLE IF EXISTS `dinosaurios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dinosaurios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(64) DEFAULT NULL,
  `especie` varchar(128) DEFAULT NULL,
  `periodo` varchar(128) DEFAULT NULL,
  `imagen_url` varchar(128) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `largo` decimal(4,2) DEFAULT NULL,
  `peso` decimal(6,3) DEFAULT NULL,
  `hp` int DEFAULT NULL,
  `vigor` int DEFAULT NULL,
  `ataque` int DEFAULT NULL,
  `defensa` int DEFAULT NULL,
  `agilidad` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dinosaurios`
--

LOCK TABLES `dinosaurios` WRITE;
/*!40000 ALTER TABLE `dinosaurios` DISABLE KEYS */;
INSERT INTO `dinosaurios` VALUES (1,'Davidde','Hynson','Hylaeosaurus',NULL,32.00,62.00,143.000,7625,609,958,577,780),(2,'Winfred','Tran','Nedcolbertia',NULL,22.00,61.00,34.000,8565,275,293,720,480),(3,'Orlando','Elger','Troodon',NULL,65.00,60.00,20.000,3416,478,820,556,24),(4,'Tabbie','Moncreif','Troodon',NULL,29.00,90.00,124.000,486,121,14,590,724),(5,'Dale','Cottrell','Gallimimus',NULL,98.00,95.00,15.000,262,227,866,532,848),(6,'Tawsha','Stanesby','Udanoceratops',NULL,87.00,74.00,198.000,180,265,156,615,46),(7,'Inness','Harvie','Pteranodon',NULL,63.00,35.00,150.000,7218,405,523,933,236),(8,'Dory','Nye','Dimetrodon',NULL,30.00,15.00,102.000,8344,999,935,976,605),(9,'Timmy','Orwell','Eoraptor',NULL,93.00,24.00,196.000,7395,715,651,153,849),(10,'Winthrop','Alywin','Sinornithosaurus',NULL,11.00,48.00,33.000,7877,621,17,531,635),(11,'Jourdain','Bea','Zalmoxes',NULL,44.00,89.00,165.000,9585,988,454,239,259),(12,'Karlie','Lavens','Thescelosaurus',NULL,70.00,56.00,90.000,7540,965,925,835,972),(13,'Beck','Snepp','Fukuiraptor',NULL,25.00,75.00,135.000,7529,148,465,609,35),(14,'Darcy','Gopsell','Gallimimus',NULL,28.00,7.00,199.000,9451,564,315,674,488),(15,'Haslett','Silversmid','Triceratops',NULL,39.00,36.00,145.000,495,373,811,830,972),(16,'Delphine','Bereford','Rugops',NULL,86.00,50.00,91.000,7631,530,891,341,610),(17,'Drake','Krates','Eoraptor',NULL,29.00,58.00,130.000,7082,995,372,839,537),(18,'Etheline','Ratcliffe','Kritosaurus',NULL,26.00,38.00,32.000,9116,964,661,969,263),(19,'Web','Hazeltine','Yangchuanosaurus',NULL,57.00,20.00,166.000,1748,443,117,881,447),(20,'Alan','Beggini','Nanotyrannus',NULL,93.00,83.00,176.000,5332,368,816,562,401),(21,'Drona','Linnett','Diplodocus',NULL,34.00,63.00,199.000,6663,135,653,878,867),(22,'Helyn','Olenchenko','Quaesitosaurus',NULL,56.00,19.00,146.000,4146,914,370,166,815),(23,'Ursulina','Kurton','Amargasaurus',NULL,96.00,69.00,49.000,8428,585,718,889,201),(24,'Lea','Whelpton','Sauropelta',NULL,44.00,91.00,175.000,8118,81,23,281,496),(25,'Fayina','Dominici','Thescelosaurus',NULL,9.00,42.00,77.000,1184,567,459,301,437),(26,'Margeaux','Cheeke','Corythosaurus',NULL,5.00,7.00,69.000,5255,286,157,481,930),(27,'Carla','Bickers','Ouranosaurus',NULL,60.00,89.00,146.000,9827,542,694,883,454),(28,'Thorvald','Gillease','Othnielia',NULL,94.00,94.00,137.000,378,973,621,35,752),(29,'Daffy','Hayford','Masiakasaurus',NULL,6.00,94.00,100.000,6553,405,251,559,194),(30,'Pauli','Horsell','Thescelosaurus',NULL,78.00,15.00,46.000,363,308,842,728,538),(31,'Krystalle','Haspineall','Megalosaurus',NULL,97.00,80.00,67.000,874,495,742,725,434),(32,'Zollie','Franses','Eustreptospondylus',NULL,45.00,77.00,197.000,6094,522,496,281,485),(33,'Orton','Titterton','Rajasaurus',NULL,40.00,47.00,106.000,874,803,82,235,969),(34,'Caye','Tizard','Pentaceratops',NULL,47.00,16.00,176.000,3025,636,282,604,689),(35,'Amaleta','Ruckhard','Tsintaosaurus',NULL,18.00,99.00,134.000,8515,623,26,766,853),(36,'Zea','Iggulden','Dromaeosaurus',NULL,95.00,60.00,149.000,4456,293,539,941,304),(37,'Danya','Jepp','Daspletosaurus',NULL,56.00,79.00,54.000,7784,607,71,268,658),(38,'Josepha','Yerborn','Nanotyrannus',NULL,20.00,64.00,79.000,526,635,932,308,839),(39,'Astrix','Pavett','Zuniceratops',NULL,42.00,36.00,136.000,79,459,937,277,611),(40,'Lillis','Macon','Iguanacolossus',NULL,82.00,24.00,17.000,6525,569,628,312,719),(41,'Gino','Alban','Ankylosaurus',NULL,22.00,60.00,193.000,2527,364,257,428,977),(42,'Aldo','MacCafferty','Sinornithosaurus',NULL,26.00,2.00,14.000,6076,399,379,765,655),(43,'Hortensia','Boddis','Othnielia',NULL,97.00,91.00,132.000,7522,300,30,597,868),(44,'Tod','Stobbes','Pentaceratops',NULL,7.00,92.00,44.000,9686,467,726,365,174),(45,'Hendrik','Walkington','Nedcolbertia',NULL,38.00,35.00,68.000,6386,607,474,854,452),(46,'Denys','Rodie','Compsognathus',NULL,18.00,1.00,18.000,5307,183,917,831,635),(47,'Alex','Nathan','Achillobator',NULL,40.00,49.00,19.000,909,710,771,224,251),(48,'Martha','Gately','Carnotaurus',NULL,16.00,75.00,142.000,288,664,932,733,8),(49,'Nollie','Guidi','Troodon',NULL,48.00,69.00,95.000,2166,467,689,598,71),(50,'Ermin','Francesc','Vagaceratops',NULL,51.00,14.00,136.000,3115,411,775,258,763),(51,'Robinson','MacSkeaghan','Rugops',NULL,66.00,17.00,53.000,9032,433,753,703,174),(52,'Andrew','Goddert.sf','Compsognathus',NULL,30.00,19.00,195.000,4963,406,934,972,539),(53,'Faunie','Jouhandeau','Parasaurolophus',NULL,43.00,15.00,176.000,1304,792,479,119,282),(54,'Lurline','Guite','Archaeopteryx',NULL,23.00,70.00,132.000,6329,251,476,58,581),(55,'Ellynn','Durnill','Herrerasaurus',NULL,99.00,63.00,103.000,8982,94,15,944,701),(56,'Sidney','Petr','Barosaurus',NULL,39.00,98.00,146.000,455,145,111,888,783),(57,'Jori','Wearn','Yangchuanosaurus',NULL,97.00,10.00,15.000,1334,518,764,224,631),(58,'Glad','Mawtus','Eoraptor',NULL,25.00,40.00,116.000,1578,115,252,78,239),(59,'Falito','Stivani','Pachyrhinosaurus',NULL,85.00,79.00,4.000,3022,902,314,181,877),(60,'Nealson','Creeghan','Amargasaurus',NULL,64.00,41.00,115.000,371,840,743,407,395),(61,'Ad','Klain','Futalognkosaurus',NULL,47.00,34.00,54.000,4970,255,706,932,868),(62,'Shayne','Lampart','Jeholosaurus',NULL,14.00,18.00,37.000,9362,514,470,101,935),(63,'Nicolas','Beausang','Pachycephalosaurus',NULL,12.00,21.00,50.000,1434,924,338,927,386),(64,'Berke','Giovannacc@i','Gallimimus',NULL,29.00,61.00,172.000,166,138,468,454,521),(65,'Susie','Agius','Valdosaurus',NULL,50.00,95.00,174.000,8669,647,422,604,187),(66,'Kirk','Kintzel','Xenotarsosaurus',NULL,34.00,13.00,41.000,6834,551,549,436,187),(67,'Brad','Ryton','Yandusaurus',NULL,26.00,47.00,39.000,1355,747,149,901,203),(68,'Selina','MacLeese','Microraptor',NULL,8.00,23.00,175.000,8361,629,159,969,768),(69,'Lezlie','Hinze','Ouranosaurus',NULL,90.00,47.00,78.000,8074,855,185,408,295),(70,'Tim','Balf','Camarasaurus',NULL,74.00,76.00,144.000,1569,592,515,138,549),(71,'Swen','Carruth','Plateosaurus',NULL,21.00,3.00,32.000,8806,101,306,390,258),(72,'Hazel','Jeffray','Tyrannosaurus',NULL,50.00,1.00,80.000,1040,230,409,687,596),(73,'Saraann','Dudek','Rugops',NULL,57.00,21.00,62.000,6398,503,550,832,772),(74,'Randi','Janeczek','Zuniceratops',NULL,25.00,72.00,95.000,7907,934,194,607,937),(75,'Catarina','Osmar','Ceratosaurus',NULL,40.00,45.00,118.000,8785,528,393,770,794),(76,'Rosalyn','Harrowing','Plateosaurus',NULL,62.00,67.00,157.000,908,466,895,671,460),(77,'Dev','Andrusov','Barosaurus',NULL,21.00,31.00,198.000,9756,950,196,39,588),(78,'Kathie','Handforth','Nedcolbertia',NULL,54.00,5.00,120.000,7921,563,185,373,266),(79,'Titos','Antham','Udanoceratops',NULL,89.00,4.00,35.000,9254,953,991,421,291),(80,'Hayley','Cookson','Cryolophosaurus',NULL,4.00,16.00,169.000,2765,957,788,85,292),(81,'Durante','Jolliss','Daspletosaurus',NULL,39.00,51.00,165.000,3234,722,903,686,620),(82,'Marianna','Sambiedge','Achelousaurus',NULL,12.00,25.00,52.000,1727,931,587,659,495),(83,'Gabriello','Vickors','Saurolophus',NULL,42.00,71.00,122.000,3568,604,889,493,942),(84,'Winslow','Beyer','Rugops',NULL,56.00,49.00,9.000,2047,41,636,142,322),(85,'Nappie','Patel','Brachiosaurus',NULL,87.00,38.00,62.000,3727,91,892,987,899),(86,'Harrie','Davidovich','Othnielia',NULL,2.00,82.00,11.000,1321,599,136,687,385),(87,'Meggi','Zorzutti','Pachyrhinosaurus',NULL,68.00,73.00,32.000,9217,597,967,974,656),(88,'Dela','Lyptrade','Udanoceratops',NULL,66.00,10.00,182.000,8987,825,958,431,480),(89,'Grantley','Sinnocke','Pentaceratops',NULL,65.00,72.00,97.000,6230,116,924,834,458),(90,'Darya','Norewood','Saltasaurus',NULL,3.00,77.00,171.000,5047,226,877,202,980),(91,'Clerissa','Yegorov','Valdosaurus',NULL,69.00,33.00,145.000,769,53,500,828,379),(92,'Rosana','Stitson','Eustreptospondylus',NULL,70.00,21.00,109.000,147,707,697,109,347),(93,'Joann','Zammett','Prenocephale',NULL,93.00,32.00,41.000,6556,32,456,988,290),(94,'Mitchael','Cawson','Ouranosaurus',NULL,6.00,52.00,9.000,4179,265,160,400,827),(95,'Alvin','Stockport','Tanycolagreus',NULL,92.00,93.00,62.000,2611,359,885,587,894),(96,'Jamaal','Mercer','Pachyrhinosaurus',NULL,39.00,60.00,92.000,6639,826,349,458,263),(97,'Ki','Mallock','Eoraptor',NULL,28.00,7.00,19.000,861,616,858,243,826),(98,'Annmaria','Hinemoor','Dimetrodon',NULL,93.00,13.00,45.000,7567,565,614,937,444),(99,'Charlotta','Perago','Bagaceratops',NULL,25.00,85.00,99.000,3578,138,321,811,303),(100,'Pebrook','Roscoe','Dacentrurus',NULL,13.00,96.00,151.000,4429,623,392,948,730);
/*!40000 ALTER TABLE `dinosaurios` ENABLE KEYS */;
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
