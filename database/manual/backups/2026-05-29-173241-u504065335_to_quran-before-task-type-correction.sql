-- RESTORE-ONLY FOCUSED BACKUP.
-- Do not execute this file as a forward patch. It contains only the task_types,
-- session_tasks, and daily_session_tasks tables captured before correcting the
-- launch task-type catalog. It intentionally excludes users, sessions,
-- credentials, account histories, and contact data.
-- Verify the target database and restore intent before sourcing this dump.
--
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: u504065335_to_quran
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
-- Table structure for table `task_types`
--

DROP TABLE IF EXISTS `task_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `table_name` varchar(255) DEFAULT NULL,
  `default_points` int(10) DEFAULT NULL,
  `max_points` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_types`
--

LOCK TABLES `task_types` WRITE;
/*!40000 ALTER TABLE `task_types` DISABLE KEYS */;
INSERT INTO `task_types` VALUES (1,'Activity','attachment_files',5,10),(7,'File','attachment_files',5,10),(8,'YouTube','attachment_files',5,10),(9,'Link','attachment_files',5,10);
/*!40000 ALTER TABLE `task_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_tasks`
--

DROP TABLE IF EXISTS `session_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_tasks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `class_session_id` int(10) NOT NULL,
  `taskable_id` int(10) DEFAULT NULL,
  `task_type_id` int(10) NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assign_to_all` enum('all','custom','attendees') NOT NULL DEFAULT 'all',
  `default_points` int(10) DEFAULT 5,
  `max_points` int(10) DEFAULT 10,
  `marks` int(10) DEFAULT NULL,
  `session_material_id` int(10) NOT NULL,
  `created_by_teacher_id` int(10) NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(10) DEFAULT NULL,
  `version_display_name_snapshot` varchar(120) DEFAULT NULL COMMENT 'Snapshot of version display_name at generation time',
  `source_version_task_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability back to main_daily_session_version_tasks; idempotency key',
  `source_differentiated_task_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to differentiated_tasks for generated DT snapshots',
  `source_differentiated_task_version_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to differentiated_task_versions for generated DT snapshots',
  `source_differentiated_task_assignment_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to differentiated_task_student_assignments for generated DT snapshots',
  `source_series_task_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to series_tasks for generated Series Task snapshots',
  `source_series_task_version_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to series_task_versions for generated Series Task snapshots',
  `source_series_task_version_item_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to series_task_version_items for generated Series Task snapshots',
  `source_series_task_assignment_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Traceability to series_task_student_assignments for generated Series Task snapshots',
  `source_series_library_type_snapshot` varchar(64) DEFAULT NULL COMMENT 'Library source type snapshot, such as sat or story_chapter',
  `source_series_library_id_snapshot` bigint(20) unsigned DEFAULT NULL COMMENT 'Library source id snapshot',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_tasks_automated` (`class_session_id`,`source_version_task_id_snapshot`),
  UNIQUE KEY `uq_session_tasks_dt` (`class_session_id`,`source_differentiated_task_id_snapshot`),
  UNIQUE KEY `uq_session_tasks_series_task` (`class_session_id`,`source_series_task_id_snapshot`),
  KEY `idx_st_class_session_id` (`class_session_id`),
  KEY `idx_st_session_material_id` (`session_material_id`),
  KEY `idx_st_created_by_teacher_id` (`created_by_teacher_id`),
  KEY `idx_st_task_type_id` (`task_type_id`),
  KEY `idx_st_snapshot_source` (`source_version_task_id_snapshot`),
  KEY `idx_session_tasks_dt_version_snapshot` (`source_differentiated_task_version_id_snapshot`),
  KEY `idx_session_tasks_dt_assignment_snapshot` (`source_differentiated_task_assignment_id_snapshot`),
  KEY `idx_session_tasks_series_version_snapshot` (`source_series_task_version_id_snapshot`),
  KEY `idx_session_tasks_series_item_snapshot` (`source_series_task_version_item_id_snapshot`),
  KEY `idx_session_tasks_series_assignment_snapshot` (`source_series_task_assignment_id_snapshot`),
  KEY `idx_session_tasks_series_library_snapshot` (`source_series_library_type_snapshot`,`source_series_library_id_snapshot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_tasks`
--

LOCK TABLES `session_tasks` WRITE;
/*!40000 ALTER TABLE `session_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `session_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_session_tasks`
--

DROP TABLE IF EXISTS `daily_session_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_session_tasks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `daily_session_id` int(11) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `default_points` int(10) NOT NULL,
  `max_points` int(10) NOT NULL,
  `sort` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dst_daily_session_id` (`daily_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_session_tasks`
--

LOCK TABLES `daily_session_tasks` WRITE;
/*!40000 ALTER TABLE `daily_session_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `daily_session_tasks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-29 17:32:41
