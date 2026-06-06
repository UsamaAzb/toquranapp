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
-- Table structure for table `booking_children`
--

DROP TABLE IF EXISTS `booking_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_children` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `child_name` varchar(255) NOT NULL,
  `child_age` tinyint(3) unsigned NOT NULL,
  `child_grade` int(10) unsigned DEFAULT NULL,
  `school_system` varchar(50) DEFAULT NULL,
  `service_interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`service_interests`)),
  `consultation_status` varchar(50) DEFAULT NULL,
  `workflow_status` enum('pending','confirmed','cancelled','questionnaire_sent','questionnaire_answer_received','followup_required') NOT NULL DEFAULT 'pending',
  `meeting_disposition` enum('completed','cancelled','no_meeting_required') DEFAULT NULL,
  `meeting_disposition_reason` varchar(500) DEFAULT NULL,
  `evaluation_status` varchar(50) DEFAULT NULL,
  `evaluation_outcome` enum('undecided','fit','unfit','PL') NOT NULL DEFAULT 'undecided',
  `consultation_type` enum('online','in-person','undecided') NOT NULL DEFAULT 'undecided',
  `meeting_link` varchar(500) DEFAULT NULL,
  `meeting_address` text DEFAULT NULL,
  `transfer_status` varchar(50) NOT NULL DEFAULT 'pending',
  `followup_date` datetime DEFAULT NULL,
  `current_school` varchar(255) DEFAULT NULL,
  `student_id` int(10) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_children_booking_id_index` (`booking_id`),
  KEY `booking_children_consultation_status_index` (`consultation_status`),
  KEY `booking_children_evaluation_status_index` (`evaluation_status`),
  KEY `booking_children_transfer_status_index` (`transfer_status`),
  KEY `booking_children_followup_date_index` (`followup_date`),
  KEY `booking_children_student_id_index` (`student_id`),
  KEY `booking_children_scheduled_date_index` (`scheduled_date`),
  KEY `idx_booking_children_workflow_status` (`workflow_status`),
  KEY `idx_booking_children_evaluation_outcome` (`evaluation_outcome`),
  KEY `idx_booking_children_updated_by` (`updated_by`),
  CONSTRAINT `booking_children_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_children_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_children`
--

LOCK TABLES `booking_children` WRITE;
/*!40000 ALTER TABLE `booking_children` DISABLE KEYS */;
INSERT INTO `booking_children` VALUES (1,1,'Smoke Student Omar',10,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',2,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:01:55','2026-05-29 07:01:55',20),(2,1,'Smoke Student Layla',12,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',3,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:50','2026-05-29 07:11:50',20),(3,2,'Smoke Student Hana',9,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',4,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:51','2026-05-29 07:11:51',20),(4,3,'Smoke Student Yusuf',11,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',5,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:52','2026-05-29 07:11:52',20),(5,3,'Smoke Student Mariam',8,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',6,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:53','2026-05-29 07:11:53',20),(6,4,'Smoke Student Nour',13,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',7,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:54','2026-05-29 07:11:54',20),(7,10,'Smoke TQ9 Clean Amina',10,2,'Egyptian','[\"Quran Memorization\",\"Arabic Language\"]','confirmed','','no_meeting_required',NULL,'fit','fit','undecided',NULL,NULL,'transferred',NULL,'Not applicable',8,' SMOKE-TQ9-TRANSFER-PREP-20260602204210',NULL,NULL,0,'2026-06-02 14:42:15','2026-06-04 20:22:33',NULL),(8,10,'Smoke TQ9 Clean Yusuf',12,NULL,'Other','[\"My Deen Journey\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','http://127.0.0.1:8014/admin/bookings/children/8?return=http%3A%2F%2F127.0.0.1%3A8014%2Fadmin%2Fbookings',NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,'2026-06-05','12:00',1,'2026-06-02 14:42:15','2026-06-05 10:04:19',20),(9,11,'Smoke TQ9 Review Duplicate',11,NULL,'Other','[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-02 14:42:54','2026-06-04 20:22:33',NULL),(10,12,'Smoke TQ9 Clean Amina',10,NULL,'Other','[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,1,'2026-06-04 18:43:18','2026-06-04 20:22:33',20),(11,6,'Ahmed',5,NULL,'Other','[\"Ahmed: Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-04 20:18:17',NULL),(12,7,'Ahmed',20,NULL,'Other','[\"Ahmed: Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-04 20:18:17',NULL),(13,8,'Mona',50,NULL,'Other','[\"Mona: Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-04 20:18:17',NULL),(14,9,'Ahmed',50,NULL,'Other','[\"Ahmed: Quran Memorization\",\"Quranic Arabic\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-04 20:18:17',NULL);
/*!40000 ALTER TABLE `booking_children` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 13:09:22
