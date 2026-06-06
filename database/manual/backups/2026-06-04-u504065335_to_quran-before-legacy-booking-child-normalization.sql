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
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(255) NOT NULL,
  `parent_email` varchar(255) NOT NULL,
  `parent_phone` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `student_id` int(10) DEFAULT NULL,
  `child_name` varchar(255) NOT NULL,
  `child_age` int(11) NOT NULL,
  `child_grade` int(10) DEFAULT NULL,
  `current_school` varchar(255) DEFAULT NULL,
  `school_system` enum('IB','American','British','Egyptian','Other') DEFAULT NULL,
  `primary_challenges` text DEFAULT NULL,
  `service_interest` varchar(255) DEFAULT NULL,
  `contact_method` enum('email','phone','both') NOT NULL DEFAULT 'email',
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `consultation_time` varchar(255) DEFAULT NULL,
  `consultation_type` enum('online','in-person') DEFAULT NULL,
  `consultation_date` date DEFAULT NULL,
  `follow_up_date` datetime DEFAULT NULL,
  `main_concerns` text DEFAULT NULL,
  `how_heard` enum('google-search','social-media','friend-referral','school-recommendation') DEFAULT NULL,
  `status` enum('pending','confirmed','followup','cancelled','fit','unfit') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `booking_reference` text DEFAULT NULL,
  `terms` tinyint(4) NOT NULL DEFAULT 0,
  `teacher_notes` text DEFAULT NULL,
  `transfer` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_link` longtext DEFAULT NULL,
  `meeting_address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'Smoke Parent Aisha','parent.aisha@toquran-smoke.test','+201090000004',4,2,'Smoke child group',10,2,'To Quran Smoke School','Egyptian',NULL,'Quran Memorization','both','2026-05-29','evening','18:00','online','2026-05-29',NULL,'[SMOKE] Launch verification family.','social-media','confirmed','[SMOKE] Delete before deployment.','SMOKE-TQ-0001',1,'Created by smoke bootstrap admin user #20.',1,'https://meet.example.test/toquran-smoke',NULL,'2026-05-29 07:01:55','2026-05-29 09:16:22'),(2,'Smoke Parent Bilal','parent.bilal@toquran-smoke.test','+201090000008',5,4,'Smoke child group',10,2,'To Quran Smoke School','Egyptian',NULL,'Quran Memorization','both','2026-05-29','evening','18:00','online','2026-05-29',NULL,'[SMOKE] Launch verification family.','social-media','confirmed','[SMOKE] Delete before deployment.','SMOKE-TQ-0002',1,'Created by smoke bootstrap admin user #20.',1,'https://meet.example.test/toquran-smoke',NULL,'2026-05-29 07:11:50','2026-05-29 09:16:24'),(3,'Smoke Parent Dina','parent.dina@toquran-smoke.test','+201090000010',6,5,'Smoke child group',10,2,'To Quran Smoke School','Egyptian',NULL,'Quran Memorization','both','2026-05-29','evening','18:00','online','2026-05-29',NULL,'[SMOKE] Launch verification family.','social-media','confirmed','[SMOKE] Delete before deployment.','SMOKE-TQ-0003',1,'Created by smoke bootstrap admin user #20.',1,'https://meet.example.test/toquran-smoke',NULL,'2026-05-29 07:11:52','2026-05-29 09:16:25'),(4,'Smoke Parent Karim','parent.karim@toquran-smoke.test','+201090000013',7,7,'Smoke child group',10,2,'To Quran Smoke School','Egyptian',NULL,'Quran Memorization','both','2026-05-29','evening','18:00','online','2026-05-29',NULL,'[SMOKE] Launch verification family.','social-media','confirmed','[SMOKE] Delete before deployment.','SMOKE-TQ-0004',1,'Created by smoke bootstrap admin user #20.',1,'https://meet.example.test/toquran-smoke',NULL,'2026-05-29 07:11:54','2026-05-29 09:16:26'),(6,'Osama Elazab','osama.elazab22@gmail.com','+201146004550',NULL,NULL,'Ahmed',5,NULL,NULL,NULL,NULL,'Ahmed: Quran Memorization, Arabic Language, My Deen Journey','email','2026-06-03','afternoon',NULL,NULL,NULL,NULL,NULL,NULL,'pending','{\n    \"handoff_contract\": \"toquran_public_review_first_v1\",\n    \"parent\": {\n        \"name\": \"Osama Elazab\",\n        \"email\": \"osama.elazab22@gmail.com\",\n        \"phone\": \"+201146004550\",\n        \"country\": \"Egypt\"\n    },\n    \"children\": [\n        {\n            \"name\": \"Ahmed\",\n            \"age\": 5,\n            \"service_interests\": [\n                \"Quran Memorization\",\n                \"Arabic Language\",\n                \"My Deen Journey\"\n            ]\n        }\n    ],\n    \"preferences\": {\n        \"preferred_date\": \"2026-06-03\",\n        \"preferred_time\": \"afternoon\",\n        \"main_concerns\": null\n    }\n}','TQ-R46PJQ3AR8',1,NULL,0,NULL,NULL,'2026-05-30 08:56:36','2026-05-30 08:56:36'),(7,'Osama Elazab','osama.elazab22@gmail.com','+201146004550',NULL,NULL,'Ahmed',20,NULL,NULL,NULL,NULL,'Ahmed: Quran Memorization, Arabic Language, My Deen Journey','email','2026-06-04','afternoon',NULL,NULL,NULL,NULL,'sdfm lksg sdmlDSF:M gdf\r\nG, dsfg, dsf:mg,dsf\r\n\" g,sdfg, d;sfgm,\r\nSDFM gsd\r\nfg,d\r\nsf<g dsf:gmdsf;lm gsd\r\nf:gm \r\nsdfg, lsdf\r\n;g m,;l\r\nSDf gm;LSDFMg;ldsfmg\'lfds m',NULL,'pending','{\n    \"handoff_contract\": \"toquran_public_review_first_v1\",\n    \"parent\": {\n        \"name\": \"Osama Elazab\",\n        \"email\": \"osama.elazab22@gmail.com\",\n        \"phone\": \"+201146004550\",\n        \"country\": \"Egypt\"\n    },\n    \"children\": [\n        {\n            \"name\": \"Ahmed\",\n            \"age\": 20,\n            \"service_interests\": [\n                \"Quran Memorization\",\n                \"Arabic Language\",\n                \"My Deen Journey\"\n            ]\n        }\n    ],\n    \"preferences\": {\n        \"preferred_date\": \"2026-06-04\",\n        \"preferred_time\": \"afternoon\",\n        \"main_concerns\": \"sdfm lksg sdmlDSF:M gdf\\r\\nG, dsfg, dsf:mg,dsf\\r\\n\\\" g,sdfg, d;sfgm,\\r\\nSDFM gsd\\r\\nfg,d\\r\\nsf<g dsf:gmdsf;lm gsd\\r\\nf:gm \\r\\nsdfg, lsdf\\r\\n;g m,;l\\r\\nSDf gm;LSDFMg;ldsfmg\'lfds m\"\n    }\n}','TQ-SQUAWKNQV5',1,NULL,0,NULL,NULL,'2026-05-30 10:18:42','2026-05-30 10:18:42'),(8,'Osama Elazab','osama.elazab22@gmail.com','+201146004550',NULL,NULL,'Mona',50,NULL,NULL,NULL,NULL,'Mona: Quran Memorization, Arabic Language, My Deen Journey','email','2026-06-03','afternoon',NULL,NULL,NULL,NULL,'sadfsdaklhfsda\r\nfksadlkghsad\'lkgn\r\nasdkgmklsadhg;kojsad h;okasdj goiasidhgoiasdhf;okdsa ha\r\nsdgk;lj asoighsdaoi hga\'seg\r\naskdj ;oajs jas\'',NULL,'pending','{\n    \"handoff_contract\": \"toquran_public_review_first_v1\",\n    \"parent\": {\n        \"name\": \"Osama Elazab\",\n        \"email\": \"osama.elazab22@gmail.com\",\n        \"phone\": \"+201146004550\",\n        \"country\": \"Egypt\"\n    },\n    \"children\": [\n        {\n            \"name\": \"Mona\",\n            \"age\": 50,\n            \"service_interests\": [\n                \"Quran Memorization\",\n                \"Arabic Language\",\n                \"My Deen Journey\"\n            ]\n        }\n    ],\n    \"preferences\": {\n        \"preferred_date\": \"2026-06-03\",\n        \"preferred_time\": \"afternoon\",\n        \"main_concerns\": \"sadfsdaklhfsda\\r\\nfksadlkghsad\'lkgn\\r\\nasdkgmklsadhg;kojsad h;okasdj goiasidhgoiasdhf;okdsa ha\\r\\nsdgk;lj asoighsdaoi hga\'seg\\r\\naskdj ;oajs jas\'\"\n    }\n}','TQ-XZKKSMKBVD',1,NULL,0,NULL,NULL,'2026-05-30 10:31:47','2026-05-30 10:31:47'),(9,'Osama Elazab','osama.elazab22@gmail.com','+201146004550',NULL,NULL,'Ahmed',50,NULL,NULL,NULL,NULL,'Ahmed: Quran Memorization, Quranic Arabic, Arabic Language, My Deen Journey','email','2026-06-06','afternoon',NULL,NULL,NULL,NULL,NULL,NULL,'pending','{\n    \"handoff_contract\": \"toquran_public_review_first_v1\",\n    \"parent\": {\n        \"name\": \"Osama Elazab\",\n        \"email\": \"osama.elazab22@gmail.com\",\n        \"phone\": \"+201146004550\",\n        \"country\": \"Egypt\"\n    },\n    \"children\": [\n        {\n            \"name\": \"Ahmed\",\n            \"age\": 50,\n            \"service_interests\": [\n                \"Quran Memorization\",\n                \"Quranic Arabic\",\n                \"Arabic Language\",\n                \"My Deen Journey\"\n            ]\n        }\n    ],\n    \"preferences\": {\n        \"preferred_date\": \"2026-06-06\",\n        \"preferred_time\": \"afternoon\",\n        \"main_concerns\": null\n    }\n}','TQ-RU2X5Z5IDS',1,NULL,0,NULL,NULL,'2026-05-30 10:52:36','2026-05-30 10:52:36'),(10,'Smoke TQ Nine Clean Parent','parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210',8,8,'Smoke TQ9 Clean Amina',10,NULL,NULL,NULL,NULL,'Smoke TQ9 Clean Amina: Quran Memorization, Arabic Language | Smoke TQ9 Clean Yusuf: My Deen Journey','email','2026-06-09','evening',NULL,NULL,NULL,NULL,'SMOKE-TQ9-CLEAN-20260602204210 end to end shared DB booking smoke',NULL,'pending','[Website public intake]\nReference: TQ-ZDVPE6FDMG\nRoute: clean booking intake\nCountry: Egypt\nPreferred date: 2026-06-09\nPreferred time: Evening\nMain concerns: SMOKE-TQ9-CLEAN-20260602204210 end to end shared DB booking smoke','TQ-ZDVPE6FDMG',1,NULL,1,NULL,NULL,'2026-06-02 14:42:15','2026-06-02 17:45:59'),(11,'Smoke TQ Nine Clean Parent','parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210',NULL,NULL,'Smoke TQ9 Review Duplicate',11,NULL,NULL,NULL,NULL,'Smoke TQ9 Review Duplicate: Quranic Arabic, Sanad Ijazah','email','2026-06-10','morning',NULL,NULL,NULL,NULL,'SMOKE-TQ9-REVIEW-20260602204250 duplicate review shared DB smoke',NULL,'pending','[Website public intake]\nReference: TQ-I95AZCCSTI\nRoute: clean booking intake\nCountry: Egypt\nPreferred date: 2026-06-10\nPreferred time: Morning\nMain concerns: SMOKE-TQ9-REVIEW-20260602204250 duplicate review shared DB smoke','TQ-I95AZCCSTI',1,NULL,0,NULL,NULL,'2026-06-02 14:42:54','2026-06-02 14:42:54'),(12,'Smoke TQ Nine Clean Parent','parent.tq9-clean@toquran-smoke.test','+2010902204222',NULL,NULL,'Smoke TQ9 Clean Amina',10,NULL,NULL,NULL,NULL,'Quranic Arabic, Sanad Ijazah','email',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'pending','[Website public intake]\nReference: TQ-XMSCQI3EKE\nRoute: review-first intake\nCountry: Egypt\nPreferred date: 2026-06-10\nPreferred time: Morning\nMain concerns: SMOKE-TQ9-REVIEW-DUPLICATE-20260602204337 duplicate child review shared DB smoke',NULL,0,NULL,0,NULL,NULL,'2026-06-04 18:43:18','2026-06-04 18:43:18');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_children`
--

LOCK TABLES `booking_children` WRITE;
/*!40000 ALTER TABLE `booking_children` DISABLE KEYS */;
INSERT INTO `booking_children` VALUES (1,1,'Smoke Student Omar',10,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',2,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:01:55','2026-05-29 07:01:55',20),(2,1,'Smoke Student Layla',12,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',3,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:50','2026-05-29 07:11:50',20),(3,2,'Smoke Student Hana',9,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',4,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:51','2026-05-29 07:11:51',20),(4,3,'Smoke Student Yusuf',11,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',5,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:52','2026-05-29 07:11:52',20),(5,3,'Smoke Student Mariam',8,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',6,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:53','2026-05-29 07:11:53',20),(6,4,'Smoke Student Nour',13,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',7,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:54','2026-05-29 07:11:54',20),(7,10,'Smoke TQ9 Clean Amina',10,2,'Egyptian','[\"Quran Memorization\",\"Arabic Language\"]','confirmed','','no_meeting_required',NULL,'fit','fit','undecided',NULL,NULL,'transferred',NULL,NULL,8,' SMOKE-TQ9-TRANSFER-PREP-20260602204210',NULL,NULL,0,'2026-06-02 14:42:15','2026-06-02 17:45:59',NULL),(8,10,'Smoke TQ9 Clean Yusuf',12,NULL,NULL,'[\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-02 14:42:15','2026-06-02 14:42:15',NULL),(9,11,'Smoke TQ9 Review Duplicate',11,NULL,NULL,'[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-06-02 14:42:54','2026-06-02 14:42:54',NULL),(10,12,'Smoke TQ9 Clean Amina',10,NULL,NULL,'[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-04 18:43:18','2026-06-04 18:43:18',20);
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

-- Dump completed on 2026-06-04 23:17:53
