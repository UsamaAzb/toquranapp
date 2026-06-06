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
  `detection_reason` enum('duplicate_child','repeat_submission','blocked_parent','existing_family_new_child','mixed_children','suspected_contact_mismatch') NOT NULL,
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-04 21:26:04
