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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_children`
--

LOCK TABLES `booking_children` WRITE;
/*!40000 ALTER TABLE `booking_children` DISABLE KEYS */;
INSERT INTO `booking_children` VALUES (1,1,'Smoke Student Omar',10,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',2,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:01:55','2026-05-29 07:01:55',20),(2,1,'Smoke Student Layla',12,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',3,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:50','2026-05-29 07:11:50',20),(3,2,'Smoke Student Hana',9,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',4,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:51','2026-05-29 07:11:51',20),(4,3,'Smoke Student Yusuf',11,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',5,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:52','2026-05-29 07:11:52',20),(5,3,'Smoke Student Mariam',8,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',6,'[SMOKE] Delete before deployment.','2026-05-29','18:00',2,'2026-05-29 07:11:53','2026-05-29 07:11:53',20),(6,4,'Smoke Student Nour',13,2,'Egyptian','[\"Quran Memorization\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','https://meet.example.test/toquran-smoke',NULL,'transferred',NULL,'To Quran Smoke School',7,'[SMOKE] Delete before deployment.','2026-05-29','18:00',1,'2026-05-29 07:11:54','2026-05-29 07:11:54',20),(7,10,'Smoke TQ9 Clean Amina',10,2,'Egyptian','[\"Quran Memorization\",\"Arabic Language\"]','confirmed','','no_meeting_required',NULL,'fit','fit','undecided',NULL,NULL,'transferred',NULL,'Not applicable',8,' SMOKE-TQ9-TRANSFER-PREP-20260602204210',NULL,NULL,0,'2026-06-02 14:42:15','2026-06-04 20:22:33',NULL),(8,10,'Smoke TQ9 Clean Yusuf',12,2,'Other','[\"My Deen Journey\"]','confirmed','confirmed','completed',NULL,'fit','fit','online','http://127.0.0.1:8014/admin/bookings/children/8?return=http%3A%2F%2F127.0.0.1%3A8014%2Fadmin%2Fbookings',NULL,'transferred',NULL,'Not applicable',9,NULL,'2026-06-05','12:00',1,'2026-06-02 14:42:15','2026-06-05 10:44:25',20),(9,11,'Smoke TQ9 Review Duplicate',11,2,'Other','[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-02 14:42:54','2026-06-05 10:09:52',NULL),(10,12,'Smoke TQ9 Clean Amina',10,2,'Other','[\"Quranic Arabic\",\"Sanad Ijazah\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,1,'2026-06-04 18:43:18','2026-06-05 10:09:52',20),(11,6,'Ahmed',5,2,'Other','[\"Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-05 10:09:52',NULL),(12,7,'Ahmed',20,2,'Other','[\"Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-05 10:09:52',NULL),(13,8,'Mona',50,2,'Other','[\"Quran Memorization\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-05 10:09:52',NULL),(14,9,'Ahmed',50,2,'Other','[\"Quran Memorization\",\"Quranic Arabic\",\"Arabic Language\",\"My Deen Journey\"]','pending','pending',NULL,NULL,NULL,'undecided','undecided',NULL,NULL,'not_transferred',NULL,'Not applicable',NULL,NULL,NULL,NULL,0,'2026-06-04 20:18:17','2026-06-05 10:09:52',NULL);
/*!40000 ALTER TABLE `booking_children` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_child_audit_log`
--

DROP TABLE IF EXISTS `booking_child_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_child_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_child_id` bigint(20) unsigned NOT NULL,
  `field_name` varchar(100) NOT NULL COMMENT 'e.g. workflow_status, evaluation_outcome',
  `from_value` text DEFAULT NULL,
  `to_value` text DEFAULT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL COMMENT 'users.id of admin who made the change',
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bcal_child` (`booking_child_id`),
  KEY `idx_bcal_changed_at` (`changed_at`),
  KEY `idx_bcal_changed_by` (`changed_by`),
  CONSTRAINT `fk_bcal_booking_child` FOREIGN KEY (`booking_child_id`) REFERENCES `booking_children` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_child_audit_log`
--

LOCK TABLES `booking_child_audit_log` WRITE;
/*!40000 ALTER TABLE `booking_child_audit_log` DISABLE KEYS */;
INSERT INTO `booking_child_audit_log` VALUES (1,8,'workflow_status','pending','confirmed',20,'2026-06-05 10:02:26'),(2,8,'consultation_type','undecided','online',20,'2026-06-05 10:02:26'),(3,8,'meeting_link',NULL,'http://127.0.0.1:8014/admin/bookings/children/8?return=http%3A%2F%2F127.0.0.1%3A8014%2Fadmin%2Fbookings',20,'2026-06-05 10:02:26'),(4,8,'scheduled_date',NULL,'2026-06-05 00:00:00',20,'2026-06-05 10:02:26'),(5,8,'scheduled_time',NULL,'12:00',20,'2026-06-05 10:02:26'),(6,8,'meeting_disposition',NULL,'completed',20,'2026-06-05 10:04:19'),(7,8,'evaluation_outcome','undecided','fit',20,'2026-06-05 10:04:19'),(8,8,'transfer_status','not_transferred','transferred',20,'2026-06-05 10:44:25'),(9,8,'student_id',NULL,'9',20,'2026-06-05 10:44:25');
/*!40000 ALTER TABLE `booking_child_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_child_emails`
--

DROP TABLE IF EXISTS `booking_child_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_child_emails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_child_id` bigint(20) unsigned NOT NULL,
  `email_type` enum('confirmation_parent','confirmation_admin','questionnaire_parent','transfer_welcome','transfer_admin') NOT NULL,
  `status` enum('not_sent','queued','sent','failed','resent') NOT NULL DEFAULT 'not_sent',
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `last_error_message` text DEFAULT NULL,
  `triggered_by` bigint(20) unsigned DEFAULT NULL COMMENT 'admin user who triggered manual send/resend',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bce_child_type` (`booking_child_id`,`email_type`),
  KEY `idx_bce_status` (`status`),
  CONSTRAINT `fk_bce_booking_child` FOREIGN KEY (`booking_child_id`) REFERENCES `booking_children` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_child_emails`
--

LOCK TABLES `booking_child_emails` WRITE;
/*!40000 ALTER TABLE `booking_child_emails` DISABLE KEYS */;
INSERT INTO `booking_child_emails` VALUES (1,8,'confirmation_parent','failed','2026-06-05 10:02:27',NULL,'Failed to authenticate on SMTP server with username \"support@toquran.org\" using the following authenticators: \"LOGIN\", \"PLAIN\". Authenticator \"LOGIN\" returned \"Expected response code \"235\" but got code \"535\", with message \"535 5.7.8 Error: authentication failed: UGFzc3dvcmQ6\".\". Authenticator \"PLAIN\" returned \"Expected response code \"235\" but got code \"535\", with message \"535 5.7.8 Error: authentication failed: UGFzc3dvcmQ6\".\".',20,'2026-06-05 10:02:27','2026-06-05 10:02:35'),(2,8,'confirmation_admin','failed','2026-06-05 10:02:35',NULL,'Failed to authenticate on SMTP server with username \"support@toquran.org\" using the following authenticators: \"LOGIN\", \"PLAIN\". Authenticator \"LOGIN\" returned \"Expected response code \"235\" but got code \"535\", with message \"535 5.7.8 Error: authentication failed: UGFzc3dvcmQ6\".\". Authenticator \"PLAIN\" returned \"Expected response code \"235\" but got code \"535\", with message \"535 5.7.8 Error: authentication failed: UGFzc3dvcmQ6\".\".',20,'2026-06-05 10:02:35','2026-06-05 10:02:45'),(3,8,'confirmation_parent','resent','2026-06-05 10:43:41','2026-06-05 10:43:41',NULL,20,'2026-06-05 10:43:41','2026-06-05 10:43:41'),(4,8,'confirmation_admin','resent','2026-06-05 10:43:43','2026-06-05 10:43:43',NULL,20,'2026-06-05 10:43:43','2026-06-05 10:43:43');
/*!40000 ALTER TABLE `booking_child_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_intake_review`
--

DROP TABLE IF EXISTS `booking_intake_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_intake_review` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(255) NOT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `parent_phone` varchar(100) DEFAULT NULL,
  `child_name` varchar(255) NOT NULL,
  `child_age` varchar(50) DEFAULT NULL,
  `child_grade` varchar(100) DEFAULT NULL,
  `school_system` varchar(100) DEFAULT NULL,
  `service_interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_interests`)),
  `children_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'full submitted child collection preserved for promotion' CHECK (json_valid(`children_payload`)),
  `child_count` int(10) unsigned NOT NULL DEFAULT 1,
  `open_submission_fingerprint` char(64) DEFAULT NULL COMMENT 'unique while unresolved; cleared once promoted/dismissed',
  `notes` text DEFAULT NULL,
  `detection_reason` enum('duplicate_child','repeat_submission','blocked_parent','existing_family_new_child','mixed_children','suspected_contact_mismatch','clean_new_customer') NOT NULL,
  `detection_detail` text DEFAULT NULL COMMENT 'human-readable summary explanation',
  `matched_booking_id` bigint(20) unsigned DEFAULT NULL COMMENT 'summary FK hint to matched booking if applicable',
  `matched_child_id` bigint(20) unsigned DEFAULT NULL COMMENT 'summary FK hint to matched booking_child if applicable',
  `status` enum('pending_review','promoted_to_queue','dismissed') NOT NULL DEFAULT 'pending_review',
  `resolved_by` bigint(20) unsigned DEFAULT NULL COMMENT 'users.id of admin who resolved',
  `resolution_note` text DEFAULT NULL COMMENT 'required on promote or dismiss',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resulting_booking_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_bir_open_submission_fingerprint` (`open_submission_fingerprint`),
  KEY `idx_bir_status` (`status`),
  KEY `idx_bir_parent_email` (`parent_email`),
  KEY `idx_bir_detection_reason` (`detection_reason`),
  KEY `idx_bir_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_intake_review`
--

LOCK TABLES `booking_intake_review` WRITE;
/*!40000 ALTER TABLE `booking_intake_review` DISABLE KEYS */;
INSERT INTO `booking_intake_review` VALUES (1,'Smoke TQ Nine Clean Parent','parent.tq9-clean@toquran-smoke.test','+2010902204222','Smoke TQ9 Clean Amina','10',NULL,NULL,'[\"Quranic Arabic\",\"Sanad Ijazah\"]','[{\"child_name\":\"Smoke TQ9 Clean Amina\",\"child_age\":10,\"child_grade\":null,\"school_system\":null,\"service_interests\":[\"Quranic Arabic\",\"Sanad Ijazah\"]}]',1,NULL,'[Website public intake]\nReference: TQ-XMSCQI3EKE\nRoute: review-first intake\nCountry: Egypt\nPreferred date: 2026-06-10\nPreferred time: Morning\nMain concerns: SMOKE-TQ9-REVIEW-DUPLICATE-20260602204337 duplicate child review shared DB smoke','clean_new_customer','Admin corrected this review row into a clean new customer.',NULL,NULL,'promoted_to_queue',20,'a','2026-06-04 18:43:18',12,'2026-06-02 14:43:41','2026-06-04 18:43:18');
/*!40000 ALTER TABLE `booking_intake_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_intake_review_children`
--

DROP TABLE IF EXISTS `booking_intake_review_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_intake_review_children` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_intake_review_id` bigint(20) unsigned NOT NULL,
  `child_index` int(10) unsigned NOT NULL COMMENT '0-based order from the submitted payload',
  `child_name` varchar(255) NOT NULL,
  `child_age` varchar(50) DEFAULT NULL,
  `child_grade` varchar(100) DEFAULT NULL,
  `school_system` varchar(100) DEFAULT NULL,
  `service_interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_interests`)),
  `review_reason` enum('duplicate_child','repeat_submission','blocked_parent','existing_family_new_child','clean_new_customer','suspected_contact_mismatch') NOT NULL,
  `review_detail` text DEFAULT NULL,
  `matched_booking_id` bigint(20) unsigned DEFAULT NULL,
  `matched_child_id` bigint(20) unsigned DEFAULT NULL,
  `resolution_status` enum('pending_decision','promote_child','dismiss_child') NOT NULL DEFAULT 'pending_decision',
  `resolution_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_birc_review_child_index` (`booking_intake_review_id`,`child_index`),
  KEY `idx_birc_review_id` (`booking_intake_review_id`),
  KEY `idx_birc_review_reason` (`review_reason`),
  KEY `idx_birc_resolution_status` (`resolution_status`),
  CONSTRAINT `fk_birc_review` FOREIGN KEY (`booking_intake_review_id`) REFERENCES `booking_intake_review` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_intake_review_children`
--

LOCK TABLES `booking_intake_review_children` WRITE;
/*!40000 ALTER TABLE `booking_intake_review_children` DISABLE KEYS */;
INSERT INTO `booking_intake_review_children` VALUES (1,1,0,'Smoke TQ9 Clean Amina','10',NULL,NULL,'[\"Quranic Arabic\",\"Sanad Ijazah\"]','clean_new_customer','Admin corrected this review row into a clean new customer.',NULL,NULL,'promote_child',NULL,'2026-06-02 14:43:41','2026-06-04 18:43:08');
/*!40000 ALTER TABLE `booking_intake_review_children` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_parent_identity_resolutions`
--

DROP TABLE IF EXISTS `booking_parent_identity_resolutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_parent_identity_resolutions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stage` enum('intake_review_promotion','booking_transfer') NOT NULL,
  `outcome` enum('clean_new_family','new_sibling_existing_family','verified_contact_update','dismissed','use_linked_parent','link_existing_parent_by_contact','create_new_parent','update_linked_parent_contact','blocked_conflict') NOT NULL,
  `booking_intake_review_id` bigint(20) unsigned DEFAULT NULL,
  `booking_intake_review_child_id` bigint(20) unsigned DEFAULT NULL,
  `booking_id` bigint(20) unsigned DEFAULT NULL,
  `booking_child_id` bigint(20) unsigned DEFAULT NULL,
  `matched_booking_id` bigint(20) unsigned DEFAULT NULL,
  `target_parent_id` int(10) DEFAULT NULL,
  `conflicting_parent_id` int(10) DEFAULT NULL,
  `submitted_parent_email` varchar(255) DEFAULT NULL,
  `submitted_parent_phone` varchar(255) DEFAULT NULL,
  `previous_parent_email` varchar(255) DEFAULT NULL,
  `previous_parent_phone` varchar(255) DEFAULT NULL,
  `resolved_parent_email` varchar(255) DEFAULT NULL,
  `resolved_parent_phone` varchar(255) DEFAULT NULL,
  `contact_action` enum('none','replace_email','replace_phone','add_phone','replace_email_and_phone','replace_email_add_phone','correct_submitted_contact') NOT NULL DEFAULT 'none',
  `child_identity_summary` text DEFAULT NULL,
  `conflict_summary` text DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL COMMENT 'users.id of admin who chose the resolution',
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bpir_stage_outcome` (`stage`,`outcome`),
  KEY `idx_bpir_review` (`booking_intake_review_id`),
  KEY `idx_bpir_review_child` (`booking_intake_review_child_id`),
  KEY `idx_bpir_booking` (`booking_id`),
  KEY `idx_bpir_booking_child` (`booking_child_id`),
  KEY `idx_bpir_target_parent` (`target_parent_id`),
  KEY `idx_bpir_conflicting_parent` (`conflicting_parent_id`),
  KEY `idx_bpir_resolved_at` (`resolved_at`),
  KEY `idx_bpir_resolved_by` (`resolved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_parent_identity_resolutions`
--

LOCK TABLES `booking_parent_identity_resolutions` WRITE;
/*!40000 ALTER TABLE `booking_parent_identity_resolutions` DISABLE KEYS */;
INSERT INTO `booking_parent_identity_resolutions` VALUES (1,'booking_transfer','create_new_parent',NULL,NULL,10,7,10,8,NULL,'parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210',NULL,NULL,'parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210','none','{\"booking_child_id\":7,\"child_name\":\"Smoke TQ9 Clean Amina\"}',NULL,NULL,NULL,'2026-06-02 17:45:57'),(2,'intake_review_promotion','clean_new_family',1,NULL,12,NULL,NULL,NULL,NULL,'parent.tq9-clean@toquran-smoke.test','+2010902204222',NULL,NULL,'parent.tq9-clean@toquran-smoke.test','+2010902204222','none',NULL,NULL,'a',20,'2026-06-04 18:43:18'),(3,'booking_transfer','use_linked_parent',NULL,NULL,10,8,10,8,NULL,'parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210','parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210','parent.tq9-clean-20260602204210@toquran-smoke.test','+2010902204210','none','{\"booking_child_id\":8,\"child_name\":\"Smoke TQ9 Clean Yusuf\"}',NULL,NULL,20,'2026-06-05 10:44:24');
/*!40000 ALTER TABLE `booking_parent_identity_resolutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_intake_submission_locks`
--

DROP TABLE IF EXISTS `booking_intake_submission_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_intake_submission_locks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_fingerprint` char(64) NOT NULL COMMENT 'sha256 of normalized parent email/phone plus sorted child names',
  `parent_email` varchar(255) DEFAULT NULL COMMENT 'normalized lowercase email snapshot, if present',
  `parent_phone` varchar(100) DEFAULT NULL COMMENT 'digits-only phone snapshot, if present',
  `child_names_hash` char(64) NOT NULL COMMENT 'sha256 of normalized sorted child-name list',
  `first_seen_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_bisl_submission_fingerprint` (`submission_fingerprint`),
  KEY `idx_bisl_parent_email` (`parent_email`),
  KEY `idx_bisl_parent_phone` (`parent_phone`),
  KEY `idx_bisl_last_seen_at` (`last_seen_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_intake_submission_locks`
--

LOCK TABLES `booking_intake_submission_locks` WRITE;
/*!40000 ALTER TABLE `booking_intake_submission_locks` DISABLE KEYS */;
INSERT INTO `booking_intake_submission_locks` VALUES (1,'b5b3f5ab2698a3017354b20a1182bb4ceb1cc14f381a08d787eb8e9dac86c006','parent.tq9-clean-20260602204210@toquran-smoke.test','2010902204210','28e3d89fc14afb0b38dd00f6e7e66cbbcd8a0adb22fe4343dd29d70ac3170c98','2026-06-02 14:42:15','2026-06-02 14:42:15','2026-06-02 14:42:15','2026-06-02 14:42:15'),(2,'b5e6ec0685d7d146d45122ec5606275243fe461c40646e453e9849873e45512e','parent.tq9-clean-20260602204210@toquran-smoke.test','2010902204210','1599e6553ab66ecc0af11437e618e0b4b37475d21001fa828c9b14bd7cf3343e','2026-06-02 14:42:54','2026-06-02 14:42:54','2026-06-02 14:42:54','2026-06-02 14:42:54'),(3,'52cf0c4a4e87956402d3dff810dee03c44cc6754b8d92fe2e1d41505d4e25975','parent.tq9-clean-20260602204210@toquran-smoke.test','2010902204210','6caa8610e4afb8542be99fed16d6bb2cbcbfe80d79a8761994bfa83e879d8d3f','2026-06-02 14:43:41','2026-06-02 14:43:41','2026-06-02 14:43:41','2026-06-02 14:43:41');
/*!40000 ALTER TABLE `booking_intake_submission_locks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 13:58:59
