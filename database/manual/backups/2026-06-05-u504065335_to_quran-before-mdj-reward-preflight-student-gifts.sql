-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: u504065335_to_quran
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `student_gifts`
--

DROP TABLE IF EXISTS `student_gifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_gifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(10) NOT NULL,
  `student_id` int(11) NOT NULL,
  `gift_name` varchar(255) DEFAULT NULL,
  `gift_image` varchar(255) DEFAULT NULL,
  `gift_id` int(11) DEFAULT NULL,
  `points_required` int(11) DEFAULT NULL,
  `status` enum('pending','reached','waiting','redeemed') DEFAULT NULL,
  `approved_by_id` int(10) DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reached_at` timestamp NULL DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `gift_order` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sg_student_year_points` (`student_id`,`academic_year_id`,`points_required`),
  KEY `idx_sg_student_id` (`student_id`),
  KEY `idx_sg_gift_id` (`gift_id`),
  KEY `idx_sg_approved_by_id` (`approved_by_id`),
  KEY `idx_sg_academic_year_id` (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_gifts`
--

LOCK TABLES `student_gifts` WRITE;
/*!40000 ALTER TABLE `student_gifts` DISABLE KEYS */;
INSERT INTO `student_gifts` VALUES (1,1,8,'Reward1',NULL,NULL,100,'pending',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(2,1,8,'Group Image','gifts/5fshNgvuWGk9u5DnqcNLdedoBzaP3kxkCbOtdXB8.png',NULL,200,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(3,1,8,'Reward3',NULL,NULL,300,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(4,1,8,'Reward4',NULL,NULL,400,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(5,1,8,'Reward5',NULL,NULL,500,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(6,1,8,'Reward6',NULL,NULL,600,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(7,1,8,'Reward7',NULL,NULL,700,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(8,1,8,'Reward8',NULL,NULL,800,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(9,1,8,'Reward9',NULL,NULL,900,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(10,1,8,'Reward10',NULL,NULL,1000,'waiting',NULL,NULL,'2026-06-02 20:45:59',NULL,NULL,NULL),(11,1,2,'Reward1',NULL,NULL,100,'pending',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(12,1,2,'Reward2',NULL,NULL,200,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(13,1,2,'Reward3',NULL,NULL,300,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(14,1,2,'Reward4',NULL,NULL,400,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(15,1,2,'Reward5',NULL,NULL,500,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(16,1,2,'Reward6',NULL,NULL,600,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(17,1,2,'Reward7',NULL,NULL,700,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(18,1,2,'Reward8',NULL,NULL,800,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(19,1,2,'Reward9',NULL,NULL,900,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL),(20,1,2,'Reward10',NULL,NULL,1000,'waiting',NULL,NULL,'2026-06-04 21:30:21',NULL,NULL,NULL);
/*!40000 ALTER TABLE `student_gifts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05  0:23:08
