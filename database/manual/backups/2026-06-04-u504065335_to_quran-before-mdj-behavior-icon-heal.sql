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
-- Table structure for table `discipline_icons`
--

DROP TABLE IF EXISTS `discipline_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discipline_icons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discipline_icons`
--

LOCK TABLES `discipline_icons` WRITE;
/*!40000 ALTER TABLE `discipline_icons` DISABLE KEYS */;
/*!40000 ALTER TABLE `discipline_icons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_discipline_points`
--

DROP TABLE IF EXISTS `reward_discipline_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_discipline_points` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `student_id` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Positive','Slip','No Way') DEFAULT NULL,
  `discipline_icon_id` int(11) DEFAULT NULL,
  `discipline_icon_path` text DEFAULT NULL,
  `sort` int(10) DEFAULT NULL,
  `teacher_desc` tinyint(1) NOT NULL DEFAULT 0,
  `selected` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_rdp_student_id` (`student_id`),
  KEY `idx_rdp_discipline_icon_id` (`discipline_icon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_discipline_points`
--

LOCK TABLES `reward_discipline_points` WRITE;
/*!40000 ALTER TABLE `reward_discipline_points` DISABLE KEYS */;
INSERT INTO `reward_discipline_points` VALUES (1,'Completed Quran or Salah task with care','active',2,5,'Positive family/accountability point for completing an agreed worship or Quran task with care.','2026-06-04 18:30:22','2026-06-04 18:30:22','Positive',NULL,NULL,10,0,0),(2,'Missed an agreed routine after reminder','active',2,2,'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.','2026-06-04 18:30:22','2026-06-04 18:30:22','Slip',NULL,NULL,10,0,0),(3,'Refused an agreed family instruction','active',2,5,'Red-flag accountability point for refusing a clear, age-appropriate family instruction.','2026-06-04 18:30:22','2026-06-04 18:30:22','No Way',NULL,NULL,10,0,0),(4,'Showed good adab','active',2,4,'Positive family/accountability point for manners, respect, or calm speech.','2026-06-04 18:30:22','2026-06-04 18:30:22','Positive',NULL,NULL,20,0,0),(5,'Delayed a task without a clear reason','active',2,2,'Minor slip for delaying an agreed task when the child could reasonably do it.','2026-06-04 18:30:22','2026-06-04 18:30:22','Slip',NULL,NULL,20,0,0),(6,'Used hurtful speech or behavior','active',2,5,'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.','2026-06-04 18:30:22','2026-06-04 18:30:22','No Way',NULL,NULL,20,0,0),(7,'Helped at home without being asked','active',2,3,'Positive family/accountability point for responsibility and helpfulness at home.','2026-06-04 18:30:22','2026-06-04 18:30:22','Positive',NULL,NULL,30,0,0),(8,'Spoke disrespectfully','active',2,3,'Minor slip for disrespectful words, tone, or avoidable arguing.','2026-06-04 18:30:22','2026-06-04 18:30:22','Slip',NULL,NULL,30,0,0),(9,'Broke a device or safety boundary','active',2,6,'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.','2026-06-04 18:30:22','2026-06-04 18:30:22','No Way',NULL,NULL,30,0,0),(10,'Spoke truthfully and calmly','active',2,3,'Positive family/accountability point for honesty, self-control, and calm repair.','2026-06-04 18:30:22','2026-06-04 18:30:22','Positive',NULL,NULL,40,0,0),(11,'Left a personal area untidy','active',2,1,'Minor slip for leaving an agreed personal/home responsibility incomplete.','2026-06-04 18:30:22','2026-06-04 18:30:22','Slip',NULL,NULL,40,0,0),(12,'Kept an agreed device boundary','active',2,3,'Positive family/accountability point for respecting an agreed device or screen-time boundary.','2026-06-04 18:30:22','2026-06-04 18:30:22','Positive',NULL,NULL,50,0,0),(13,'Completed Quran or Salah task with care','active',8,5,'Positive family/accountability point for completing an agreed worship or Quran task with care.','2026-06-04 18:30:23','2026-06-04 18:30:23','Positive',NULL,NULL,10,0,0),(14,'Missed an agreed routine after reminder','active',8,2,'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.','2026-06-04 18:30:23','2026-06-04 18:30:23','Slip',NULL,NULL,10,0,0),(15,'Refused an agreed family instruction','active',8,5,'Red-flag accountability point for refusing a clear, age-appropriate family instruction.','2026-06-04 18:30:23','2026-06-04 18:30:23','No Way',NULL,NULL,10,0,0),(16,'Showed good adab','active',8,4,'Positive family/accountability point for manners, respect, or calm speech.','2026-06-04 18:30:23','2026-06-04 18:30:23','Positive',NULL,NULL,20,0,0),(17,'Delayed a task without a clear reason','active',8,2,'Minor slip for delaying an agreed task when the child could reasonably do it.','2026-06-04 18:30:23','2026-06-04 18:30:23','Slip',NULL,NULL,20,0,0),(18,'Used hurtful speech or behavior','active',8,5,'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.','2026-06-04 18:30:23','2026-06-04 18:30:23','No Way',NULL,NULL,20,0,0),(19,'Helped at home without being asked','active',8,3,'Positive family/accountability point for responsibility and helpfulness at home.','2026-06-04 18:30:23','2026-06-04 18:30:23','Positive',NULL,NULL,30,0,0),(20,'Spoke disrespectfully','active',8,3,'Minor slip for disrespectful words, tone, or avoidable arguing.','2026-06-04 18:30:23','2026-06-04 18:30:23','Slip',NULL,NULL,30,0,0),(21,'Broke a device or safety boundary','active',8,6,'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.','2026-06-04 18:30:23','2026-06-04 18:30:23','No Way',NULL,NULL,30,0,0),(22,'Spoke truthfully and calmly','active',8,3,'Positive family/accountability point for honesty, self-control, and calm repair.','2026-06-04 18:30:23','2026-06-04 18:30:23','Positive',NULL,NULL,40,0,0),(23,'Left a personal area untidy','active',8,1,'Minor slip for leaving an agreed personal/home responsibility incomplete.','2026-06-04 18:30:23','2026-06-04 18:30:23','Slip',NULL,NULL,40,0,0),(24,'Kept an agreed device boundary','active',8,3,'Positive family/accountability point for respecting an agreed device or screen-time boundary.','2026-06-04 18:30:23','2026-06-04 18:30:23','Positive',NULL,NULL,50,0,0),(25,'Completed Quran or Salah task with care','active',7,5,'Positive family/accountability point for completing an agreed worship or Quran task with care.','2026-06-04 18:47:10','2026-06-04 18:47:10','Positive',NULL,NULL,10,0,0),(26,'Missed an agreed routine after reminder','active',7,2,'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.','2026-06-04 18:47:10','2026-06-04 18:47:10','Slip',NULL,NULL,10,0,0),(27,'Refused an agreed family instruction','active',7,5,'Red-flag accountability point for refusing a clear, age-appropriate family instruction.','2026-06-04 18:47:10','2026-06-04 18:47:10','No Way',NULL,NULL,10,0,0),(28,'Showed good adab','active',7,4,'Positive family/accountability point for manners, respect, or calm speech.','2026-06-04 18:47:10','2026-06-04 18:47:10','Positive',NULL,NULL,20,0,0),(29,'Delayed a task without a clear reason','active',7,2,'Minor slip for delaying an agreed task when the child could reasonably do it.','2026-06-04 18:47:10','2026-06-04 18:47:10','Slip',NULL,NULL,20,0,0),(30,'Used hurtful speech or behavior','active',7,5,'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.','2026-06-04 18:47:10','2026-06-04 18:47:10','No Way',NULL,NULL,20,0,0),(31,'Helped at home without being asked','active',7,3,'Positive family/accountability point for responsibility and helpfulness at home.','2026-06-04 18:47:10','2026-06-04 18:47:10','Positive',NULL,NULL,30,0,0),(32,'Spoke disrespectfully','active',7,3,'Minor slip for disrespectful words, tone, or avoidable arguing.','2026-06-04 18:47:10','2026-06-04 18:47:10','Slip',NULL,NULL,30,0,0),(33,'Broke a device or safety boundary','active',7,6,'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.','2026-06-04 18:47:10','2026-06-04 18:47:10','No Way',NULL,NULL,30,0,0),(34,'Spoke truthfully and calmly','active',7,3,'Positive family/accountability point for honesty, self-control, and calm repair.','2026-06-04 18:47:10','2026-06-04 18:47:10','Positive',NULL,NULL,40,0,0),(35,'Left a personal area untidy','active',7,1,'Minor slip for leaving an agreed personal/home responsibility incomplete.','2026-06-04 18:47:10','2026-06-04 18:47:10','Slip',NULL,NULL,40,0,0),(36,'Kept an agreed device boundary','active',7,3,'Positive family/accountability point for respecting an agreed device or screen-time boundary.','2026-06-04 18:47:11','2026-06-04 18:47:11','Positive',NULL,NULL,50,0,0);
/*!40000 ALTER TABLE `reward_discipline_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_discipline_transfer`
--

DROP TABLE IF EXISTS `reward_discipline_transfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_discipline_transfer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `points` int(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Positive','Slip','No Way') DEFAULT NULL,
  `discipline_icon_id` int(11) DEFAULT NULL,
  `discipline_icon_path` text DEFAULT NULL,
  `sort` int(10) DEFAULT NULL,
  `teacher_desc` tinyint(1) NOT NULL DEFAULT 0,
  `selected` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_rdt_discipline_icon_id` (`discipline_icon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_discipline_transfer`
--

LOCK TABLES `reward_discipline_transfer` WRITE;
/*!40000 ALTER TABLE `reward_discipline_transfer` DISABLE KEYS */;
INSERT INTO `reward_discipline_transfer` VALUES (1,'Completed Quran or Salah task with care','active',5,'Positive family/accountability point for completing an agreed worship or Quran task with care.','2026-06-03 11:10:53','2026-06-03 11:10:53','Positive',NULL,NULL,10,0,0),(2,'Showed good adab','active',4,'Positive family/accountability point for manners, respect, or calm speech.','2026-06-03 11:10:53','2026-06-03 11:10:53','Positive',NULL,NULL,20,0,0),(3,'Helped at home without being asked','active',3,'Positive family/accountability point for responsibility and helpfulness at home.','2026-06-03 11:10:54','2026-06-03 11:10:54','Positive',NULL,NULL,30,0,0),(4,'Spoke truthfully and calmly','active',3,'Positive family/accountability point for honesty, self-control, and calm repair.','2026-06-03 11:10:54','2026-06-03 11:10:54','Positive',NULL,NULL,40,0,0),(5,'Kept an agreed device boundary','active',3,'Positive family/accountability point for respecting an agreed device or screen-time boundary.','2026-06-03 11:10:54','2026-06-03 11:10:54','Positive',NULL,NULL,50,0,0),(6,'Missed an agreed routine after reminder','active',2,'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.','2026-06-03 11:10:54','2026-06-03 11:10:54','Slip',NULL,NULL,10,0,0),(7,'Delayed a task without a clear reason','active',2,'Minor slip for delaying an agreed task when the child could reasonably do it.','2026-06-03 11:10:54','2026-06-03 11:10:54','Slip',NULL,NULL,20,0,0),(8,'Spoke disrespectfully','active',3,'Minor slip for disrespectful words, tone, or avoidable arguing.','2026-06-03 11:10:54','2026-06-03 11:10:54','Slip',NULL,NULL,30,0,0),(9,'Left a personal area untidy','active',1,'Minor slip for leaving an agreed personal/home responsibility incomplete.','2026-06-03 11:10:54','2026-06-03 11:10:54','Slip',NULL,NULL,40,0,0),(10,'Refused an agreed family instruction','active',5,'Red-flag accountability point for refusing a clear, age-appropriate family instruction.','2026-06-03 11:10:54','2026-06-03 11:10:54','No Way',NULL,NULL,10,0,0),(11,'Used hurtful speech or behavior','active',5,'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.','2026-06-03 11:10:54','2026-06-03 11:10:54','No Way',NULL,NULL,20,0,0),(12,'Broke a device or safety boundary','active',6,'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.','2026-06-03 11:10:54','2026-06-03 11:10:54','No Way',NULL,NULL,30,0,0);
/*!40000 ALTER TABLE `reward_discipline_transfer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_session_discipline`
--

DROP TABLE IF EXISTS `student_session_discipline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_session_discipline` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `discipline_icon_id` int(10) NOT NULL,
  `discipline_icon_path` longtext NOT NULL,
  `student_reward_discipline_id` int(10) DEFAULT NULL,
  `class_session_id` int(10) DEFAULT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('Positive','Slip','No Way') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ssd_student_id` (`student_id`),
  KEY `idx_ssd_discipline_icon_id` (`discipline_icon_id`),
  KEY `idx_ssd_class_session_id` (`class_session_id`),
  KEY `idx_ssd_teacher_subject_classes_id` (`teacher_subject_classes_id`),
  KEY `idx_ssd_student_reward_discipline_id` (`student_reward_discipline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_session_discipline`
--

LOCK TABLES `student_session_discipline` WRITE;
/*!40000 ALTER TABLE `student_session_discipline` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_session_discipline` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-04 22:05:31
