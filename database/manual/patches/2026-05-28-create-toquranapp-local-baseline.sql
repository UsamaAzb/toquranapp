-- To Quran Phase 2 local app schema baseline
-- Date: 2026-05-28
-- Target: local/app DB only, `toquranapp_local`
-- Source structure evidence: database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql
-- Safety:
--   - Do not execute against `u504065335_to_quran` or any public/live website DB.
--   - This baseline aborts if `toquranapp_local` already contains tables.
--   - No Week14 rows are imported; this is structure-only.

CREATE DATABASE IF NOT EXISTS `toquranapp_local` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `toquranapp_local`;

DELIMITER $$
DROP PROCEDURE IF EXISTS `_toquranapp_local_baseline_preflight`$$
CREATE PROCEDURE `_toquranapp_local_baseline_preflight`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    IF DATABASE() <> 'toquranapp_local' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Refusing To Quran baseline: selected DB is not toquranapp_local';
    END IF;

    SELECT COUNT(*) INTO table_count
    FROM information_schema.tables
    WHERE table_schema = 'toquranapp_local';

    IF table_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Refusing To Quran baseline: toquranapp_local already contains tables';
    END IF;
END$$
DELIMITER ;

CALL `_toquranapp_local_baseline_preflight`();
DROP PROCEDURE `_toquranapp_local_baseline_preflight`;

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
DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_years` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL COMMENT 'Family anchor; always set, even for child/user events',
  `event_type` varchar(80) NOT NULL COMMENT 'Event type code (see AccountHistoryEventType enum)',
  `reason_code` varchar(80) DEFAULT NULL COMMENT 'Controlled reason code for lifecycle transitions',
  `actor_user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'User who triggered the event',
  `actor_role` varchar(50) DEFAULT NULL COMMENT 'Role snapshot at event time',
  `subject_type` enum('family','parent','child','user') NOT NULL DEFAULT 'family' COMMENT 'Subject entity type',
  `subject_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Subject entity ID',
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Structured context: booking IDs, email IDs, skip reasons' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ah_parent_id_created` (`parent_id`,`created_at`),
  KEY `ah_subject` (`subject_type`,`subject_id`),
  KEY `ah_event_type` (`event_type`),
  KEY `ah_actor` (`actor_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `achievement_level_bands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_level_bands` (
  `id` int(10) NOT NULL,
  `label` varchar(255) NOT NULL,
  `min_score` int(10) NOT NULL,
  `max_score` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `achievement_level_descriptors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_level_descriptors` (
  `id` int(10) NOT NULL,
  `description` text NOT NULL,
  `assessment_criterion_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `achievement_level_band_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_criteria` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `academic_year_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_criteria_strand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_criteria_strand` (
  `id` int(10) NOT NULL,
  `assessment_criterion_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessments_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments_types` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assignment_answer_criteria_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_answer_criteria_report` (
  `id` int(10) NOT NULL,
  `student_classes_assignment_id` int(10) NOT NULL,
  `teacher_classes_assignment_id` int(10) NOT NULL,
  `teacher_comment` text DEFAULT NULL,
  `student_id` int(10) NOT NULL,
  `assignment_assessment_criteria_id` int(10) NOT NULL,
  `mark` int(10) NOT NULL,
  `achievement_level_band_id` int(10) NOT NULL,
  `achiev_level_descriptore` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assignment_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_assessment_criteria` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `teacher_classes_assignment_id` int(10) NOT NULL,
  `assessment_criteria_title` varchar(255) NOT NULL,
  `assessments_type_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assignment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_types` (
  `id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachment_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('file','link','youtube') NOT NULL,
  `path` text NOT NULL,
  `file_size` int(10) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `subject_id` int(10) DEFAULT NULL,
  `class_id` int(10) DEFAULT NULL,
  `teacher_subject_class_id` int(10) NOT NULL,
  `session_task_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_af_session_task_id` (`session_task_id`),
  KEY `idx_af_task_sort` (`session_task_id`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendances` (
  `id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `class_session_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `week` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audio_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audio_lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audio_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audio_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `level_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `background`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `background` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `pdf_link` longtext DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_posts` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_parent_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_parent_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `normalized_email` varchar(255) DEFAULT NULL,
  `normalized_phone` varchar(100) DEFAULT NULL,
  `block_reason` varchar(255) DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  `blocked_by` bigint(20) unsigned DEFAULT NULL COMMENT 'users.id or operator reference',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_bpb_email_unique` (`normalized_email`),
  UNIQUE KEY `idx_bpb_phone_unique` (`normalized_phone`),
  CONSTRAINT `chk_bpb_identifier_present` CHECK (`normalized_email` is not null or `normalized_phone` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `category` enum('busy','available','consultation','classes','holiday') NOT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT 0,
  `guests` int(11) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ce_created_by_user_id` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `camb_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camb_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(10) NOT NULL,
  `active` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `camb_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camb_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `camb_cat_id` int(10) NOT NULL,
  `camb_sound_id` int(11) DEFAULT NULL,
  `unit` int(11) NOT NULL,
  `lesson` int(11) NOT NULL,
  `active` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cambradge_words_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cambradge_words_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `us_sound` varchar(255) DEFAULT NULL,
  `uk_sound` varchar(255) DEFAULT NULL,
  `difficulty_levels` int(11) DEFAULT NULL,
  `difficulty_reason` varchar(500) DEFAULT NULL COMMENT 'Short human-readable reason generated by VocabularyDifficultyEstimator.',
  `difficulty_source` enum('legacy_unknown','generated','manual') NOT NULL DEFAULT 'legacy_unknown' COMMENT 'Tracks whether difficulty_levels is legacy unknown, generated by the estimator, or manually edited/accepted.',
  `wrong_spelling` mediumtext DEFAULT NULL,
  `wrong_spelling_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of generated wrong-option metadata, e.g. [{"text":"freind","rule":"ie_to_ei","label":"ie/ei swap"}]. NULL means no generated metadata is stored.' CHECK (json_valid(`wrong_spelling_rules`)),
  `wrong_spelling_source` enum('legacy_unknown','generated','manual') NOT NULL DEFAULT 'legacy_unknown' COMMENT 'Tracks whether wrong_spelling is legacy unknown, generated by the rule engine, or manually edited/accepted.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category_group_word`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_group_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(10) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `check_point_list` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `child_hangman_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `child_hangman_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `student_id` int(10) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `order` int(10) DEFAULT NULL,
  `active` int(10) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `child_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `child_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sound` varchar(255) DEFAULT NULL,
  `child_category_id` int(10) NOT NULL,
  `hangman_words_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_posts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `summited_at` timestamp NULL DEFAULT NULL,
  `content` text NOT NULL,
  `uploaded_file` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_sessions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `class_id` int(10) NOT NULL,
  `daily_session_id` int(10) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `date` date NOT NULL,
  `session_start_time` time DEFAULT NULL,
  `session_end_time` time DEFAULT NULL,
  `class_subject_id` int(10) DEFAULT NULL,
  `generated_for_date` date DEFAULT NULL,
  `student_id` int(10) unsigned DEFAULT NULL COMMENT 'Non-null for Automated Task per-student generated rows',
  `main_daily_session_template_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to main_daily_session_templates for generated rows',
  `differentiated_task_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to differentiated_tasks for Differentiated Task generated rows',
  `series_task_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK-like traceability to series_tasks for Series Task generated rows',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_sessions_automated` (`main_daily_session_template_id`,`student_id`,`generated_for_date`),
  UNIQUE KEY `uq_class_sessions_dt` (`differentiated_task_id`,`student_id`,`generated_for_date`),
  UNIQUE KEY `uq_class_sessions_series_task` (`series_task_id`,`student_id`,`generated_for_date`),
  KEY `idx_cs_unit_id` (`unit_id`),
  KEY `idx_cs_teacher_subject_classes_id` (`teacher_subject_classes_id`),
  KEY `idx_cs_teacher_id` (`teacher_id`),
  KEY `idx_cs_subject_id` (`subject_id`),
  KEY `idx_cs_grade_id` (`grade_id`),
  KEY `idx_cs_class_subject_student` (`class_subject_id`,`student_id`,`date`),
  KEY `idx_cs_tsc_student` (`teacher_subject_classes_id`,`student_id`,`id`),
  KEY `idx_class_sessions_dt_student_date` (`differentiated_task_id`,`student_id`,`generated_for_date`),
  KEY `idx_class_sessions_series_student_date` (`series_task_id`,`student_id`,`generated_for_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_subjects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `class_id` int(10) NOT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_csub_class_id` (`class_id`),
  KEY `idx_csub_grade_level_subject_id` (`grade_level_subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `grade_level_id` int(10) DEFAULT NULL,
  `grade_name` varchar(255) DEFAULT NULL,
  `class_img` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `type` enum('main','secondary') NOT NULL DEFAULT 'main',
  `academic_year_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_classes_grade_level_id` (`grade_level_id`),
  KEY `idx_classes_academic_year_id` (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classwork_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classwork_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_logs` (
  `id` int(10) NOT NULL,
  `customer_service_id` int(10) NOT NULL,
  `log_type` enum('call','email','chat') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `related_user_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_us`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `massage` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','responded','closed') NOT NULL DEFAULT 'new',
  `preferred_contact` varchar(255) DEFAULT NULL,
  `child_name` varchar(255) DEFAULT NULL,
  `child_age` varchar(255) NOT NULL,
  `current_grade` varchar(255) DEFAULT NULL,
  `curriculum` varchar(255) DEFAULT NULL,
  `school_system` varchar(255) DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `reference` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `court_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `court_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `video_link` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_attachment_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_attachment_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('file','link','youtube') NOT NULL,
  `path` text NOT NULL,
  `file_size` int(10) DEFAULT NULL,
  `subject_id` int(10) DEFAULT NULL,
  `daily_session_task_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_daf_daily_session_task_id` (`daily_session_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_session_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_session_students` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `daily_session_id` int(10) NOT NULL,
  `is_active` int(10) NOT NULL DEFAULT 1,
  `paused_at` timestamp NULL DEFAULT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `last_generated_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dss_student_daily_unique` (`student_id`,`daily_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `daily_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_sessions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `main_daily_session_id` int(10) NOT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ds_main_daily_session_id` (`main_daily_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diagnosis_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diagnosis_meetings` (
  `id` int(10) NOT NULL,
  `parent_id` int(10) NOT NULL,
  `family_support_id` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `notes` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `differentiated_task_id` bigint(20) unsigned NOT NULL,
  `type` enum('file','link','youtube') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `path` varchar(1000) DEFAULT NULL,
  `url` varchar(2000) DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dta_task_order` (`differentiated_task_id`,`sort_order`,`id`),
  CONSTRAINT `fk_dta_task` FOREIGN KEY (`differentiated_task_id`) REFERENCES `differentiated_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_student_assignment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_student_assignment_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `differentiated_task_id` bigint(20) unsigned NOT NULL,
  `event_type` enum('assign','move','unassign','version_deleted') NOT NULL,
  `from_version_id` bigint(20) unsigned DEFAULT NULL,
  `from_version_display_name` varchar(255) DEFAULT NULL,
  `to_version_id` bigint(20) unsigned DEFAULT NULL,
  `to_version_display_name` varchar(255) DEFAULT NULL,
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dtsah_student_task` (`student_id`,`differentiated_task_id`),
  KEY `idx_dtsah_task_created` (`differentiated_task_id`,`created_at`),
  KEY `idx_dtsah_actor` (`actor_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_student_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_student_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `differentiated_task_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL,
  `effective_from_date` date NOT NULL,
  `effective_to_date` date DEFAULT NULL,
  `assigned_by_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dtsa_student_task_from` (`student_id`,`differentiated_task_id`,`effective_from_date`),
  KEY `idx_dtsa_student_task_effective` (`student_id`,`differentiated_task_id`,`effective_from_date`,`effective_to_date`),
  KEY `idx_dtsa_task_effective` (`differentiated_task_id`,`effective_from_date`,`effective_to_date`,`student_id`),
  KEY `idx_dtsa_task_version_open` (`differentiated_task_id`,`version_id`,`effective_to_date`),
  KEY `idx_dtsa_assigned_by` (`assigned_by_user_id`),
  CONSTRAINT `chk_dtsa_effective_range` CHECK (`effective_to_date` is null or `effective_to_date` >= `effective_from_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_student_generation_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_student_generation_states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `differentiated_task_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Internal generation fence only; not a teacher-managed subscription',
  `start_date` date DEFAULT NULL COMMENT 'First date eligible for generation; normally the first assignment effective_from_date',
  `end_date` date DEFAULT NULL COMMENT 'Internal generation fence end date when the student becomes unassigned/inactive for this DT',
  `last_generated_date` date DEFAULT NULL,
  `paused_through_date` date DEFAULT NULL COMMENT 'Internal resume fence for skipped recurrence dates, if later needed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dtsgs_student_task` (`student_id`,`differentiated_task_id`),
  KEY `idx_dtsgs_task_active` (`differentiated_task_id`,`is_active`),
  KEY `idx_dtsgs_student_active` (`student_id`,`is_active`),
  KEY `idx_dtsgs_start_date` (`start_date`),
  KEY `idx_dtsgs_last_generated` (`last_generated_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_version_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_version_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version_id` bigint(20) unsigned NOT NULL,
  `attachment_id` bigint(20) unsigned NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dtva_version_attachment` (`version_id`,`attachment_id`),
  KEY `idx_dtva_attachment` (`attachment_id`),
  CONSTRAINT `fk_dtva_attachment` FOREIGN KEY (`attachment_id`) REFERENCES `differentiated_task_attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dtva_version` FOREIGN KEY (`version_id`) REFERENCES `differentiated_task_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_task_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_task_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `differentiated_task_id` bigint(20) unsigned NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dtv_task_display_name` (`differentiated_task_id`,`display_name`),
  KEY `idx_dtv_task_order` (`differentiated_task_id`,`sort_order`,`id`),
  CONSTRAINT `fk_dtv_task` FOREIGN KEY (`differentiated_task_id`) REFERENCES `differentiated_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `differentiated_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `differentiated_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned NOT NULL,
  `task_type_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `recurrence_kind` enum('daily','weekly','monthly') NOT NULL DEFAULT 'weekly',
  `recurrence_weekdays` varchar(20) DEFAULT NULL COMMENT 'CSV of 0-6 (Sun=0) for weekly recurrence',
  `recurrence_day_of_month` tinyint(3) unsigned DEFAULT NULL COMMENT '1-31 for monthly recurrence',
  `recurrence_interval` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `default_points` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `max_points` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dt_subject_status` (`subject_id`,`status`),
  KEY `idx_dt_creator_subject_status` (`created_by_user_id`,`subject_id`,`status`),
  KEY `idx_dt_creator` (`created_by_user_id`),
  KEY `idx_dt_task_type` (`task_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `difficulty_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `difficulty_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discipline_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discipline_icons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dp_atl_skills` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dp_global_contexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dp_global_contexts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dp_key_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dp_related_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dp_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dp_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_delivery_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_delivery_claims` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `claim_key` varchar(191) NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `subject_type` enum('parent','child') NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `event_type` varchar(80) NOT NULL,
  `status` enum('claimed','sent','failed','skipped') NOT NULL DEFAULT 'claimed',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_delivery_claims_claim_key_unique` (`claim_key`),
  KEY `email_delivery_claims_parent_subject_idx` (`parent_id`,`subject_type`,`subject_id`),
  KEY `email_delivery_claims_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `status` enum('sent','failed') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `follow_up_sheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follow_up_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `google_sheet` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `foundation_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foundation_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `foundation_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `foundations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foundations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `game_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `post_task_id` int(11) NOT NULL,
  `game_type` varchar(100) DEFAULT NULL,
  `state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`state`)),
  `score` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_assignments` (
  `id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `supervisor_user_id` int(10) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `file_summited` int(10) NOT NULL,
  `assignment_type_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_group_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_group_questions` (
  `id` int(10) NOT NULL,
  `quiz_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_lesson_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_lesson_elements` (
  `id` int(10) NOT NULL,
  `lesson_id` int(10) NOT NULL,
  `text` longtext DEFAULT NULL,
  `media` text DEFAULT NULL,
  `attachfile` text DEFAULT NULL,
  `show_type` varchar(255) DEFAULT NULL,
  `text_script` longtext DEFAULT NULL,
  `question_id` int(10) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_lesson_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_lesson_questions` (
  `id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `lesson_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 1,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_lessons` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short_desc` text DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `supervisor_user_id` int(10) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_projects` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `supervisor_user_id` int(10) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `file_summited` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_questions` (
  `id` int(10) NOT NULL,
  `library_quiz_id` int(10) DEFAULT NULL,
  `question_group_id` int(10) NOT NULL,
  `lesson_id` int(10) DEFAULT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `subject_brunch` int(10) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_questions_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_questions_options` (
  `id` int(10) NOT NULL,
  `library_question_id` int(10) DEFAULT NULL,
  `option_text` text NOT NULL,
  `correct` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_quizzes_and_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_quizzes_and_exams` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `supervisor_user_id` int(10) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reading` longtext DEFAULT NULL,
  `quiz_type` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `timer` time DEFAULT NULL,
  `is_assessment` int(10) NOT NULL DEFAULT 0,
  `assessments_type_id` int(10) DEFAULT NULL,
  `feedback_answers` enum('after each question','in the end of quiz','only teacher') NOT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `points` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_assessment_criteria` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `assessment_criteria_strand_id` int(10) DEFAULT NULL,
  `academic_year_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_atl_skills` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `dp_atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_global_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_global_context` (
  `id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `global_context_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_key_concepts` (
  `id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `key_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_dp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_dp_related_concepts` (
  `id` int(10) NOT NULL,
  `general_library_dp_unit_id` int(10) NOT NULL,
  `related_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_atl_skills` (
  `id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `myp_atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_global_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_global_context` (
  `id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `global_context_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_key_concepts` (
  `id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `key_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_myp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_myp_related_concepts` (
  `id` int(10) NOT NULL,
  `general_library_myp_unit_id` int(10) NOT NULL,
  `related_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_atl_skills` (
  `id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `pyp_atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_pyp_id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_other_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_other_concepts` (
  `id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `other_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_specified_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_specified_concepts` (
  `id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `specified_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_unit_pyp_transdisciplinary_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_unit_pyp_transdisciplinary_theme` (
  `id` int(10) NOT NULL,
  `general_library_pyp_unit_id` int(10) NOT NULL,
  `theme_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_units` (
  `id` int(10) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `academic_year_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `supervisor_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `unit_type_id` int(10) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_interdisciplinary` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_units_dp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_units_dp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 1,
  `unit_id` int(10) NOT NULL,
  `central_idea` text DEFAULT NULL,
  `lines_of_inquiry` text DEFAULT NULL,
  `action` text DEFAULT NULL,
  `connections_transdisciplinary_and_past` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `initial_reflections` text DEFAULT NULL,
  `prior_learning` text DEFAULT NULL,
  `connections_transdisciplinary_past` text DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `co_constructed_success_criteria` text DEFAULT NULL,
  `monitoring_documenting_measuring` text DEFAULT NULL,
  `summative_inquiry_relationship` text DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_units_myp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_units_myp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 2,
  `unit_id` int(10) NOT NULL,
  `statement_inquiry` text DEFAULT NULL,
  `objectives` text DEFAULT NULL,
  `summative_assessment` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `summative_inquiry_relationship` varchar(255) DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `additional_teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_units_pyp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_units_pyp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 1,
  `unit_id` int(10) NOT NULL,
  `central_idea` text DEFAULT NULL,
  `lines_of_inquiry` text DEFAULT NULL,
  `action` text DEFAULT NULL,
  `connections_transdisciplinary_and_past` text DEFAULT NULL,
  `services_as_action` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `initial_reflections` text DEFAULT NULL,
  `prior_learning` text DEFAULT NULL,
  `connections_transdisciplinary_past` text DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `co_constructed_success_criteria` text DEFAULT NULL,
  `monitoring_documenting_measuring` text DEFAULT NULL,
  `summative_inquiry_relationship` text DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `general_library_units_standrand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_library_units_standrand` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 4,
  `unit_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `default_points_required` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grade_level_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade_level_subjects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `grade_level_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `academic_year_id` int(10) DEFAULT NULL,
  `type` enum('standard','optional') NOT NULL,
  `status` enum('active','archived') NOT NULL,
  `created_by_user_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gls_grade_level_id` (`grade_level_id`),
  KEY `idx_gls_subject_id` (`subject_id`),
  KEY `idx_gls_academic_year_id` (`academic_year_id`),
  KEY `idx_gls_created_by_user_id` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grade_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade_levels` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  `level_order` int(10) unsigned DEFAULT NULL,
  `program_id` int(10) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gl_program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grammar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grammar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `video_link` longtext DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `category_id` int(10) NOT NULL,
  `camb_sound_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hangman_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hangman_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(10) DEFAULT NULL,
  `active` int(10) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hangman_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hangman_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `category_id` int(10) NOT NULL,
  `camb_sound_id` int(11) DEFAULT NULL,
  `difficulty_levels` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_resources_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_resources_types` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inquiry_teacher_student_question_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inquiry_teacher_student_question_types` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interdisciplinary_subject_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interdisciplinary_subject_assessment_criteria` (
  `id` int(10) NOT NULL,
  `interdisciplinary_subject_id` int(10) NOT NULL,
  `assessment_criterion_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interdisciplinary_subject_teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interdisciplinary_subject_teachers` (
  `id` int(10) NOT NULL,
  `interdisciplinary_subject_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interdisciplinary_subject_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interdisciplinary_subject_units` (
  `id` int(10) NOT NULL,
  `interdisciplinary_subject_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interdisciplinary_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interdisciplinary_subjects` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `program_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journey_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journey_themes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `background_image_path` varchar(255) DEFAULT NULL,
  `sound_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lesson_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_types` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `level_up`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `level_up` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `iframe_link` mediumtext NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `library_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_resources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `library_section_id` bigint(20) unsigned NOT NULL,
  `resource_type` enum('file','link') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','archived','unavailable') NOT NULL DEFAULT 'active',
  `storage_disk` varchar(32) DEFAULT NULL,
  `file_path` varchar(2048) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `external_url` varchar(2048) DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_by_user_id` bigint(20) unsigned NOT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lr_owner_subject_section_status` (`owner_user_id`,`subject_id`,`library_section_id`,`status`),
  KEY `idx_lr_owner_subject_type_status` (`owner_user_id`,`subject_id`,`resource_type`,`status`),
  KEY `idx_lr_section_order` (`library_section_id`,`sort_order`,`id`),
  KEY `idx_lr_file_path` (`file_path`(191)),
  KEY `idx_lr_external_url` (`external_url`(191)),
  KEY `idx_lr_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_lr_section` FOREIGN KEY (`library_section_id`) REFERENCES `library_sections` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `library_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_by_user_id` bigint(20) unsigned NOT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ls_owner_subject_parent_status` (`owner_user_id`,`subject_id`,`parent_id`,`status`),
  KEY `idx_ls_owner_subject_status_order` (`owner_user_id`,`subject_id`,`status`,`sort_order`,`id`),
  KEY `idx_ls_parent` (`parent_id`),
  KEY `idx_ls_subject` (`subject_id`),
  KEY `idx_ls_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_ls_parent` FOREIGN KEY (`parent_id`) REFERENCES `library_sections` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `listening_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listening_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `listening_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listening_chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `listen_book_id` int(11) NOT NULL,
  `iframe_link` varchar(255) DEFAULT NULL,
  `aduio` varchar(255) DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `local_global_challenges_opportunities_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `local_global_challenges_opportunities_types` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_main_task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_main_task_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `main_task_id` bigint(20) unsigned NOT NULL,
  `type` enum('file','link','youtube') NOT NULL DEFAULT 'file',
  `title` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `path` varchar(500) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mdmta_main_task` (`main_task_id`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_main_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_main_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `main_daily_session_template_id` bigint(20) unsigned NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `task_type_id` int(10) unsigned NOT NULL,
  `default_points` int(11) DEFAULT NULL,
  `max_points` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mdmt_template` (`main_daily_session_template_id`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_student_assignment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_student_assignment_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `main_daily_session_template_id` bigint(20) unsigned NOT NULL,
  `event_type` enum('assign','reassign','unassign') NOT NULL,
  `from_version_id` bigint(20) unsigned DEFAULT NULL,
  `from_version_display_name` varchar(120) DEFAULT NULL,
  `to_version_id` bigint(20) unsigned DEFAULT NULL,
  `to_version_display_name` varchar(120) DEFAULT NULL,
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mdssah_student_template` (`student_id`,`main_daily_session_template_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_student_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_student_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `main_daily_session_template_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL,
  `effective_from_date` date NOT NULL,
  `effective_to_date` date DEFAULT NULL COMMENT 'NULL = open-ended (current interval)',
  `assigned_by_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mdssa_student_template_from` (`student_id`,`main_daily_session_template_id`,`effective_from_date`),
  KEY `idx_mdssa_effective_lookup` (`student_id`,`main_daily_session_template_id`,`effective_from_date`,`effective_to_date`),
  KEY `idx_mdssa_version` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_subscriptions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `main_daily_session_template_id` bigint(20) unsigned NOT NULL,
  `is_active` int(10) NOT NULL DEFAULT 1,
  `paused_at` timestamp NULL DEFAULT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `last_generated_date` date DEFAULT NULL,
  `paused_through_date` date DEFAULT NULL COMMENT 'Resume fence: paused recurrence dates on or before this date are skipped without advancing last_generated_date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mdss_student_template` (`student_id`,`main_daily_session_template_id`),
  KEY `idx_mdss_student` (`student_id`),
  KEY `idx_mdss_template` (`main_daily_session_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned NOT NULL,
  `recurrence_kind` enum('daily','weekly','monthly') NOT NULL DEFAULT 'weekly',
  `recurrence_weekdays` varchar(20) DEFAULT NULL COMMENT 'CSV of 0-6 (Sun=0) for weekly recurrence',
  `recurrence_day_of_month` tinyint(3) unsigned DEFAULT NULL COMMENT '1-31 for monthly recurrence',
  `recurrence_interval` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mdst_subject` (`subject_id`),
  KEY `idx_mdst_status` (`status`),
  KEY `idx_mdst_created_by` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_version_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_version_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version_id` bigint(20) unsigned NOT NULL,
  `main_task_id` bigint(20) unsigned NOT NULL,
  `description_override` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mdsvt_version_task` (`version_id`,`main_task_id`),
  KEY `idx_mdsvt_version_sort` (`version_id`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `main_daily_session_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_daily_session_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `main_daily_session_template_id` bigint(20) unsigned NOT NULL,
  `display_name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mdsv_template_name` (`main_daily_session_template_id`,`display_name`),
  KEY `idx_mdsv_template_sort` (`main_daily_session_template_id`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `meeting_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting_tickets` (
  `id` int(10) NOT NULL,
  `customer_service_id` int(10) NOT NULL,
  `related_user_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `myp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `myp_atl_skills` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `myp_global_contexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `myp_global_contexts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `myp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `myp_key_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `myp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `myp_related_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `myp_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `myp_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `new_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `us_sound` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notice_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `video_link` longtext DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_answer_reflection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_answer_reflection` (
  `id` int(11) NOT NULL,
  `reflection_unit_id` int(11) NOT NULL,
  `parent_reflection_question_id` int(11) NOT NULL,
  `parent_reflection_question_unit_id` int(10) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `summited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_course_enrollments` (
  `id` int(11) NOT NULL,
  `parent_id` int(10) NOT NULL,
  `parent_course_id` int(10) NOT NULL,
  `enrollment_date` timestamp NULL DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_courses` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_reflection_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_reflection_questions` (
  `id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `text_question` text NOT NULL,
  `active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_reflection_questions_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_reflection_questions_unit` (
  `id` int(10) NOT NULL,
  `parent_reflection_question_id` int(10) NOT NULL,
  `unit_reflection_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parents` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `family_support_id` int(10) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `lifecycle_status` enum('pending_activation','active','suspended','archived') DEFAULT NULL COMMENT 'Family account lifecycle status. NULL = unclassified/blocked during rollout.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parents_user_id` (`user_id`),
  KEY `idx_parents_family_support_id` (`family_support_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_schedules` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','overdue') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `peer_coach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peer_coach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `video_link` longtext DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_role_laratrust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role_laratrust` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_user_laratrust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_user_laratrust` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions_laratrust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions_laratrust` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phonics_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phonics_levels` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `order` int(10) DEFAULT NULL,
  `active` int(10) NOT NULL DEFAULT 1,
  `hangman_url` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phonics_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phonics_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `category_id` int(10) NOT NULL,
  `camb_sound_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_answer_criteria_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_answer_criteria_report` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `project_assessment_criteria_id` int(10) NOT NULL,
  `student_classes_project_id` int(10) NOT NULL,
  `teacher_comment` text NOT NULL,
  `teacher_classes_project_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `mark` int(10) NOT NULL,
  `achievement_level_band_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_assessment_criteria` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `teacher_classes_project_id` int(10) NOT NULL,
  `assessment_criteria_title` varchar(255) NOT NULL,
  `assessments_type_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `punishment_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punishment_agreements` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `punishment_type_id` int(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pa_student_type_title` (`student_id`,`punishment_type_id`,`title`),
  KEY `idx_pa_student_id` (`student_id`),
  KEY `idx_pa_punishment_type_id` (`punishment_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `punishment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punishment_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `decrease_point` int(10) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `punishments_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `punishments_suggestions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `punishment_type_id` int(10) NOT NULL,
  `suggestion_text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pyp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pyp_atl_skills` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pyp_other_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pyp_other_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pyp_specified_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pyp_specified_concepts` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pyp_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pyp_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pyp_transdisciplinary_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pyp_transdisciplinary_theme` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_types` (
  `id` int(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questionnaire_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaire_questions` (
  `id` int(10) NOT NULL,
  `questionnaire_id` int(10) NOT NULL,
  `question_type` int(10) NOT NULL,
  `question_text` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questionnaire_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaire_response` (
  `id` int(10) NOT NULL,
  `questionnaire_id` int(10) NOT NULL,
  `questionnaire_question_id` int(10) NOT NULL,
  `filled_by_user_id` int(10) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `student_user_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `parent_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questionnaire_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaire_sections` (
  `id` int(10) NOT NULL,
  `questionnaire_id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `order` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questionnaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaires` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `questionnair_type` enum('diagnosis','feedback') DEFAULT NULL,
  `order` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_exam_answer_criteria_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_exam_answer_criteria_report` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `student_classes_quiz_exam_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `teacher_classes_quiz_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `quiz_assessment_criteria_id` int(10) NOT NULL,
  `mark` int(10) NOT NULL,
  `achievement_level_band_id` int(10) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `teacher_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_exam_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_exam_assessment_criteria` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `teacher_classes_quiz_id` int(10) NOT NULL,
  `assessment_criteria_title` varchar(255) NOT NULL,
  `assessments_type_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_types` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reflection_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reflection_unit` (
  `id` int(11) NOT NULL,
  `teacher_subject_classes_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_events` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `event_type` enum('Task','Completed','Discipline','Attendance','Adjustment') NOT NULL,
  `event_id` int(10) NOT NULL,
  `delta_points` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `created_by_user_id` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_pin_hashes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_pin_hashes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `pin_unhash` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rph_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_points_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_points_ledger` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `source_type` enum('task','discipline','attendance','completed','adjustment') NOT NULL,
  `source_id` bigint(20) DEFAULT NULL,
  `points_delta` int(10) NOT NULL,
  `sign` enum('plus','minus') DEFAULT NULL,
  `granted_by` int(10) NOT NULL,
  `granted_at` timestamp NULL DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rpl_student_year_source` (`student_id`,`academic_year_id`,`source_type`,`source_id`),
  KEY `idx_rpl_student_id` (`student_id`),
  KEY `idx_rpl_granted_by` (`granted_by`),
  KEY `idx_rpl_academic_year_id` (`academic_year_id`),
  KEY `idx_rpl_subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_totals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_totals` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `total_points` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rt_student_id` (`student_id`),
  KEY `idx_rt_academic_year_id` (`academic_year_id`),
  KEY `idx_rt_subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_user_laratrust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user_laratrust` (
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles_laratrust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_laratrust` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `video_link` longtext DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_program` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_types` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL,
  `arabic_name` varchar(255) NOT NULL,
  `english_name` varchar(255) NOT NULL,
  `school_type` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `location_map` text NOT NULL,
  `city_id` int(11) NOT NULL,
  `governorate_id` int(11) NOT NULL,
  `journey` int(10) NOT NULL DEFAULT 0,
  `points` int(10) NOT NULL DEFAULT 0,
  `vocab_bank` int(10) NOT NULL DEFAULT 0,
  `criteria` int(10) NOT NULL,
  `parent_support` int(10) NOT NULL DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `sec_desc` text DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `semester_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_definitions` (
  `id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `series_season_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `link` mediumtext DEFAULT NULL,
  `subtitles` mediumtext DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_task_student_assignment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_task_student_assignment_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `series_task_id` bigint(20) unsigned NOT NULL,
  `event_type` enum('assign','move','unassign','version_deleted','position_changed') NOT NULL,
  `from_version_id` bigint(20) unsigned DEFAULT NULL,
  `from_version_display_name` varchar(255) DEFAULT NULL,
  `to_version_id` bigint(20) unsigned DEFAULT NULL,
  `to_version_display_name` varchar(255) DEFAULT NULL,
  `from_sequence_position` smallint(5) unsigned DEFAULT NULL,
  `to_sequence_position` smallint(5) unsigned DEFAULT NULL,
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stsah_student_task` (`student_id`,`series_task_id`),
  KEY `idx_stsah_task_created` (`series_task_id`,`created_at`),
  KEY `idx_stsah_actor` (`actor_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_task_student_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_task_student_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `series_task_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL,
  `start_sequence_position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `effective_from_date` date NOT NULL,
  `effective_to_date` date DEFAULT NULL,
  `assigned_by_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stsa_student_task_from` (`student_id`,`series_task_id`,`effective_from_date`),
  KEY `idx_stsa_student_task_effective` (`student_id`,`series_task_id`,`effective_from_date`,`effective_to_date`),
  KEY `idx_stsa_task_effective` (`series_task_id`,`effective_from_date`,`effective_to_date`,`student_id`),
  KEY `idx_stsa_task_version_open` (`series_task_id`,`version_id`,`effective_to_date`),
  KEY `idx_stsa_assigned_by` (`assigned_by_user_id`),
  CONSTRAINT `fk_stsa_task_version` FOREIGN KEY (`series_task_id`, `version_id`) REFERENCES `series_task_versions` (`series_task_id`, `id`),
  CONSTRAINT `chk_stsa_effective_range` CHECK (`effective_to_date` is null or `effective_to_date` >= `effective_from_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_task_student_generation_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_task_student_generation_states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `series_task_id` bigint(20) unsigned NOT NULL,
  `current_version_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Internal generation fence only; not a teacher-managed subscription',
  `start_date` date DEFAULT NULL COMMENT 'First date eligible for generation; normally the current assignment effective_from_date',
  `end_date` date DEFAULT NULL COMMENT 'Internal generation fence end date when the student becomes unassigned/inactive for this Series Task',
  `next_sequence_position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `last_delivered_sequence_position` smallint(5) unsigned DEFAULT NULL,
  `last_generated_date` date DEFAULT NULL,
  `paused_through_date` date DEFAULT NULL COMMENT 'Internal resume fence for skipped recurrence dates, if later needed',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'Set when stop_at_end reaches the final selected item',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stsgs_student_task` (`student_id`,`series_task_id`),
  KEY `idx_stsgs_task_active` (`series_task_id`,`is_active`),
  KEY `idx_stsgs_student_active` (`student_id`,`is_active`),
  KEY `idx_stsgs_current_version` (`current_version_id`),
  KEY `idx_stsgs_start_date` (`start_date`),
  KEY `idx_stsgs_last_generated` (`last_generated_date`),
  KEY `fk_stsgs_task_current_version` (`series_task_id`,`current_version_id`),
  CONSTRAINT `fk_stsgs_task_current_version` FOREIGN KEY (`series_task_id`, `current_version_id`) REFERENCES `series_task_versions` (`series_task_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_task_version_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_task_version_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version_id` bigint(20) unsigned NOT NULL,
  `library_source_type` varchar(64) NOT NULL,
  `library_source_id` bigint(20) unsigned NOT NULL,
  `library_title_snapshot` varchar(255) NOT NULL,
  `library_url_snapshot` varchar(2000) DEFAULT NULL,
  `library_summary_snapshot` text DEFAULT NULL,
  `sequence_position` smallint(5) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stvi_version_position` (`version_id`,`sequence_position`),
  UNIQUE KEY `uq_stvi_version_source` (`version_id`,`library_source_type`,`library_source_id`),
  KEY `idx_stvi_source` (`library_source_type`,`library_source_id`),
  KEY `idx_stvi_version_active` (`version_id`,`is_active`,`sequence_position`),
  CONSTRAINT `fk_stvi_version` FOREIGN KEY (`version_id`) REFERENCES `series_task_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_task_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_task_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `series_task_id` bigint(20) unsigned NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stv_task_id` (`series_task_id`,`id`),
  UNIQUE KEY `uq_stv_task_display_name` (`series_task_id`,`display_name`),
  KEY `idx_stv_task_order` (`series_task_id`,`sort_order`,`id`),
  CONSTRAINT `fk_stv_task` FOREIGN KEY (`series_task_id`) REFERENCES `series_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned NOT NULL,
  `task_type_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `library_collection_type` varchar(64) NOT NULL,
  `library_collection_id` bigint(20) unsigned DEFAULT NULL,
  `vocabulary_allowed_games` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vocabulary_allowed_games`)),
  `vocabulary_difficulty_policy` enum('student_choice','sprout','climber','champion') DEFAULT NULL,
  `recurrence_kind` enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `recurrence_weekdays` varchar(20) DEFAULT NULL COMMENT 'CSV of 0-6 (Sun=0) for weekly recurrence',
  `recurrence_day_of_month` tinyint(3) unsigned DEFAULT NULL COMMENT '1-31 for monthly recurrence',
  `recurrence_interval` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `sequence_behavior` enum('stop_at_end','loop') NOT NULL DEFAULT 'stop_at_end',
  `release_policy` varchar(32) NOT NULL DEFAULT 'continuous',
  `default_points` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `max_points` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_st_subject_status` (`subject_id`,`status`),
  KEY `idx_st_creator_subject_status` (`created_by_user_id`,`subject_id`,`status`),
  KEY `idx_st_creator` (`created_by_user_id`),
  KEY `idx_st_task_type` (`task_type_id`),
  KEY `idx_st_library_collection` (`library_collection_type`,`library_collection_id`),
  CONSTRAINT `chk_st_points_range` CHECK (`default_points` <= `max_points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_plans` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `currency` enum('EGP','USD') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_weeks` int(11) NOT NULL,
  `type` enum('Fixed','Flexible') NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `initial_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `value_old` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `info` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_classwork`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_classwork` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `classwork_type_id` int(10) NOT NULL,
  `table_name` varchar(255) DEFAULT NULL,
  `library_id` int(10) DEFAULT NULL,
  `class_session_id` int(10) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subject_id` int(10) NOT NULL,
  `class_id` int(10) NOT NULL,
  `teacher_subject_class_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `session_id_materials` int(10) NOT NULL,
  `assign_to_all` enum('all','custom','attendees') NOT NULL DEFAULT 'all',
  `created_by_teacher_id` int(10) NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_scw_class_session_id` (`class_session_id`),
  KEY `idx_scw_classwork_type_id` (`classwork_type_id`),
  KEY `idx_scw_subject_id` (`subject_id`),
  KEY `idx_scw_class_id` (`class_id`),
  KEY `idx_scw_unit_id` (`unit_id`),
  KEY `idx_scw_teacher_subject_class_id` (`teacher_subject_class_id`),
  KEY `idx_scw_created_by_teacher_id` (`created_by_teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_classwork_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_classwork_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `classwork_id` int(10) NOT NULL,
  `status` enum('all','attendees','custom') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_scs_student_id` (`student_id`),
  KEY `idx_scs_classwork_id` (`classwork_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_materials` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `session_id` int(11) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `assign_to_all` enum('all','custom','attendees') NOT NULL DEFAULT 'all',
  `task_desc` mediumtext DEFAULT NULL,
  `class_work_desc` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_materials_session` (`session_id`),
  KEY `idx_sm_session_id` (`session_id`),
  KEY `idx_sm_teacher_subject_classes_id` (`teacher_subject_classes_id`),
  KEY `idx_sm_teacher_id` (`teacher_id`),
  KEY `idx_sm_subject_id` (`subject_id`),
  KEY `idx_sm_grade_id` (`grade_id`),
  KEY `idx_sm_unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_task_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_task_student` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `session_task_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `student_points` int(10) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `review_submitted_at` timestamp NULL DEFAULT NULL COMMENT 'When the task was submitted for approval review',
  `review_submitted_by_id` int(10) DEFAULT NULL COMMENT 'User id that submitted the task for review',
  `review_submission_source` varchar(64) DEFAULT NULL COMMENT 'P3 source such as student_review',
  `approval_source` enum('parent_approval','parent_direct_completion','teacher_approval','student_pin','trusted_child_auto') DEFAULT NULL COMMENT 'Final completion source',
  `approved_by_id` int(10) DEFAULT NULL COMMENT 'Human final approver user id when one exists',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'When final approval/completion happened',
  `trusted_auto_approval_snapshot` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Trusted-child setting captured when task entered review',
  `trusted_auto_approval_due_at` timestamp NULL DEFAULT NULL COMMENT 'Due time for trusted auto-approval using configured review period',
  `trusted_auto_approval_granted_by_id` int(10) DEFAULT NULL COMMENT 'Captured parent/updater user id used for reward ledger accountability',
  `assign_to_all` enum('all','attendees','custom') NOT NULL DEFAULT 'all',
  `status` enum('assigned','completed','pending','in_review') DEFAULT NULL,
  `flag` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_task_student` (`session_task_id`,`student_id`),
  KEY `idx_sts_session_task_id` (`session_task_id`),
  KEY `idx_sts_student_id` (`student_id`),
  KEY `idx_sts_status_review_due` (`status`,`trusted_auto_approval_snapshot`,`trusted_auto_approval_due_at`),
  KEY `idx_sts_review_work` (`student_id`,`status`,`review_submitted_at`),
  KEY `idx_sts_approval_source` (`approval_source`,`approved_at`),
  KEY `idx_sts_review_submitted_by` (`review_submitted_by_id`),
  KEY `idx_sts_approved_by` (`approved_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `story_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `story_chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `iframe_link` varchar(255) DEFAULT NULL,
  `story_id` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `youtube` mediumtext DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `audio` mediumtext DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `story_chapters_youtube`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `story_chapters_youtube` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `youtube` mediumtext DEFAULT NULL,
  `iframe_link` varchar(255) DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `audio` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `story_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_answer_reflection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_answer_reflection` (
  `id` int(11) NOT NULL,
  `reflection_unit_id` int(11) NOT NULL,
  `student_reflection_question_id` int(11) NOT NULL,
  `student_reflection_question_unit_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `summited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_class_post_replay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_class_post_replay` (
  `id` int(10) NOT NULL,
  `class_post_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `replay_text` text NOT NULL,
  `replay_file` text DEFAULT NULL,
  `summited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_assignments` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `session_task_id` int(10) NOT NULL,
  `teacher_class_assignment_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `assignment_points` int(10) NOT NULL,
  `student_points` int(10) NOT NULL,
  `sudent_mark` int(10) DEFAULT NULL,
  `uploaded_file` mediumtext NOT NULL,
  `description_answers` mediumtext NOT NULL,
  `summited_at` datetime NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `class_id` int(10) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date DEFAULT NULL,
  `status` enum('current','past','inactive','archived') DEFAULT 'current',
  PRIMARY KEY (`id`),
  KEY `idx_sch_student_id` (`student_id`),
  KEY `idx_sch_class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_lesson_question_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_lesson_question_answers` (
  `id` int(10) NOT NULL,
  `lesson_id` int(10) NOT NULL,
  `lesson_question_id` int(10) NOT NULL,
  `question_type` int(10) NOT NULL,
  `correct_answer` text NOT NULL,
  `student_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_lessons` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `student_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `class_session_id` int(10) NOT NULL,
  `session_material_id` int(10) NOT NULL,
  `teacher_classes_lesson_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_projects` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_classes_project_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `project_points` int(10) NOT NULL,
  `student_points` int(10) NOT NULL,
  `project_marks` int(10) DEFAULT NULL,
  `student_marks` int(10) DEFAULT NULL,
  `uploaded_file` mediumtext NOT NULL,
  `description_answers` mediumtext NOT NULL,
  `summited_at` datetime NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_questions_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_questions_answers` (
  `id` bigint(20) unsigned NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `teacher_classes_quiz_id` int(10) DEFAULT NULL,
  `question_group_id` int(10) NOT NULL,
  `student_classes_quiz_exam_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `subject_branch` int(10) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `student_answer` text NOT NULL,
  `student_score` int(10) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `answer_time` varchar(255) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_classes_quiz_exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_classes_quiz_exam` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_classes_quiz_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `is_marked` tinyint(1) NOT NULL,
  `session_task_id` int(10) NOT NULL,
  `quiz_exam_points` int(10) NOT NULL,
  `student_points` int(10) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `summited_at` datetime NOT NULL,
  `left_time` varchar(255) NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_criterion_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_criterion_performance` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `semester_id` int(10) DEFAULT NULL,
  `academic_year_id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `average_level` decimal(3,2) NOT NULL,
  `highest_level` int(10) NOT NULL,
  `latest_level` int(10) NOT NULL,
  `task_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_gift_points_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_gift_points_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `date` date DEFAULT current_timestamp(),
  `sign` enum('plus','minus') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sgph_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_logs` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `teacher_subject_class_id` int(10) NOT NULL,
  `session_id` int(10) NOT NULL,
  `date_time` datetime NOT NULL,
  `ip_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_performance_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_performance_cache` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criterion_id` int(10) NOT NULL,
  `average_level` decimal(3,2) NOT NULL,
  `last_level` int(10) NOT NULL,
  `best_level` int(10) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_punishments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_punishments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `punishment_agreement_id` int(10) NOT NULL,
  `student_session_discipline_id` int(10) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `teacher_subject_class` int(10) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `created_by_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_reflection_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_reflection_questions` (
  `id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `text_question` text NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_reflection_questions_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_reflection_questions_unit` (
  `id` int(10) NOT NULL,
  `student_reflection_question_id` int(10) NOT NULL,
  `unit_reflection_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_services` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `service_id` int(10) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `student_id` int(10) NOT NULL,
  `parent_id` int(10) NOT NULL,
  `subscription_type` enum('fixed','flexible') NOT NULL,
  `class_nums` int(10) NOT NULL,
  `price_month` varchar(255) NOT NULL,
  `started_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `student_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_settings` (
  `student_id` int(11) NOT NULL,
  `selected_theme_id` int(11) DEFAULT NULL,
  `other_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`other_prefs`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_subject_branch_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_subject_branch_performance` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `subject_branch_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `semester_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `average_score` decimal(5,2) NOT NULL,
  `best_score` decimal(5,2) NOT NULL,
  `last_score` decimal(5,2) NOT NULL,
  `attempts` int(10) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_task_approval_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_task_approval_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_task_student_id` int(10) NOT NULL,
  `session_task_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `event_type` enum('submitted_for_review','approved','completed_with_pin','completed_by_parent','trusted_auto_approved','skipped_stale') NOT NULL,
  `actor_user_id` int(10) DEFAULT NULL,
  `actor_role` varchar(32) DEFAULT NULL,
  `source` varchar(64) NOT NULL,
  `points` int(10) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (`metadata` is null or json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stae_pivot_created` (`session_task_student_id`,`created_at`),
  KEY `idx_stae_task_student_created` (`session_task_id`,`student_id`,`created_at`),
  KEY `idx_stae_student_created` (`student_id`,`created_at`),
  KEY `idx_stae_actor` (`actor_user_id`),
  KEY `idx_stae_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_task_approval_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_task_approval_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `trusted_auto_approval_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by_user_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stas_student` (`student_id`),
  KEY `idx_stas_trusted_enabled` (`trusted_auto_approval_enabled`),
  KEY `idx_stas_updated_by` (`updated_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_task_completion_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_task_completion_cache` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `week_start` date NOT NULL,
  `assigned_tasks` int(10) NOT NULL,
  `completed_tasks` int(10) NOT NULL,
  `completion_percent` decimal(5,2) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `service_type_id` int(10) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_phone` varchar(255) DEFAULT NULL,
  `age` int(10) DEFAULT NULL,
  `school_system` enum('IB','American','British','Egyptian','Other') DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_email` varchar(255) DEFAULT NULL,
  `father_phone` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `father_national_id` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_email` varchar(255) DEFAULT NULL,
  `mother_phone` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `mother_national_id` varchar(255) DEFAULT NULL,
  `student_image` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birthdate_password` varchar(255) DEFAULT NULL,
  `grade_level_id` int(10) DEFAULT NULL,
  `grade_name` varchar(255) DEFAULT NULL,
  `program_id` int(10) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `school_fees` tinyint(4) DEFAULT 0,
  `current_class_id` int(10) DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `health_condition` varchar(255) DEFAULT NULL,
  `relatives_school` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `status` enum('active','moved','graduated','inactive') DEFAULT NULL,
  `account_status` enum('pending_activation','active','suspended','archived') DEFAULT NULL COMMENT 'Login/account lifecycle status. NULL = unclassified/blocked during rollout. Separate from students.status (academic).',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_students_user_id` (`user_id`),
  KEY `idx_students_parent_id` (`parent_id`),
  KEY `idx_students_grade_level_id` (`grade_level_id`),
  KEY `idx_students_current_class_id` (`current_class_id`),
  KEY `idx_students_program_id` (`program_id`),
  KEY `idx_students_service_type_id` (`service_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `students_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students_subjects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `enrolled_at` date NOT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `class_subject_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ss_student_id` (`student_id`),
  KEY `idx_ss_grade_level_subject_id` (`grade_level_subject_id`),
  KEY `idx_ss_academic_year_id` (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_branch_grade_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_branch_grade_levels` (
  `id` int(10) NOT NULL,
  `subject_branch_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `created_by_user_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_branchs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_branchs` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_student_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_student_points` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_student_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_student_progress` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `subject_brunch_id` int(10) NOT NULL,
  `points` int(10) DEFAULT NULL,
  `marks` int(10) DEFAULT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('standard','optional','interdisciplinary') DEFAULT 'standard',
  `program_id` int(10) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `row_status` enum('current','past','inactive','archived') NOT NULL DEFAULT 'current',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_subjects_program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `billing_interval` varchar(255) NOT NULL,
  `trial_period_days` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `stripe_plan_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL,
  `parent_id` int(10) NOT NULL,
  `subscription_plan_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','inactive','cancelled') NOT NULL DEFAULT 'active',
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `payment_gateway` varchar(255) DEFAULT NULL,
  `gateway_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_status` varchar(255) DEFAULT NULL,
  `stripe_plan` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supervision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supervision` (
  `id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `supervisor_user_id` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supervision_teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supervision_teachers` (
  `id` int(10) NOT NULL,
  `supervision_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_pin_hashes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_pin_hashes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `pin_unhash` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_answer_unit_reflections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_answer_unit_reflections` (
  `id` int(11) NOT NULL,
  `teacher_reflection_type` int(10) NOT NULL,
  `answer` text NOT NULL,
  `reflection_unit_id` int(10) NOT NULL,
  `summited_at` timestamp NULL DEFAULT NULL,
  `teacher_questions_reflection_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_assignments` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL,
  `file_summited` int(10) NOT NULL,
  `created_by_user_id` int(10) NOT NULL,
  `teacher_library_assignment_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `class_session_id` int(10) NOT NULL,
  `session_material_id` int(10) NOT NULL,
  `points` int(11) DEFAULT NULL,
  `marks` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_assessment` int(11) NOT NULL,
  `assessments_type_id` int(10) NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_group_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_group_questions` (
  `id` int(10) NOT NULL,
  `teacher_library_quiz_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_lesson_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_lesson_elements` (
  `id` int(10) NOT NULL,
  `teacher_classes_lesson_id` int(10) NOT NULL,
  `text` longtext DEFAULT NULL,
  `media` text DEFAULT NULL,
  `attachfile` text DEFAULT NULL,
  `show_type` varchar(255) DEFAULT NULL,
  `lesson_type` enum('in_lesson','file_lesson') DEFAULT NULL,
  `text_script` longtext DEFAULT NULL,
  `teacher_classes_lesson_question_id` int(10) unsigned DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_lesson_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_lesson_questions` (
  `id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `teacher_classes_lesson_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 1,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_lessons` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short_desc` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `order` int(10) DEFAULT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `class_session_id` int(10) NOT NULL,
  `session_materials_id` int(10) NOT NULL,
  `teacher_library_lesson_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `assign_to_all` enum('all','custom','attendees') NOT NULL DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_projects` (
  `id` int(10) NOT NULL,
  `teacher_library_project_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `session_id` int(11) NOT NULL,
  `is_assessment` int(10) NOT NULL,
  `assessments_type_id` int(10) NOT NULL,
  `points` int(10) DEFAULT NULL,
  `marks` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `file_summited` int(10) NOT NULL,
  `order` int(10) NOT NULL,
  `session_material_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_questions` (
  `id` int(10) NOT NULL,
  `teacher_classes_quiz_id` int(10) NOT NULL,
  `question_group_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `subject_branch_id` int(10) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `feedback_massage` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_questions_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_questions_options` (
  `id` int(10) NOT NULL,
  `teacher_classes_question_id` int(10) NOT NULL,
  `option_text` text NOT NULL,
  `correct` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_quiz_exam_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_quiz_exam_sections` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `teacher_classes_quiz_exam_id` int(10) DEFAULT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `instructions` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_classes_quizzes_and_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_classes_quizzes_and_exams` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `class_session_id` int(10) NOT NULL,
  `session_material_id` int(10) NOT NULL,
  `quiz_exam_id` int(10) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `is_assessment` int(10) NOT NULL,
  `assessments_type_id` int(10) NOT NULL,
  `feedback_answers` enum('after each question','in the end of quiz','only teacher') DEFAULT NULL,
  `due_date` date NOT NULL,
  `order` int(10) DEFAULT NULL,
  `timer` time NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `points` int(10) DEFAULT NULL,
  `marks` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_assignments` (
  `id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_library_item` tinyint(1) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `file_summited` int(10) NOT NULL,
  `is_assessment` int(10) NOT NULL DEFAULT 0,
  `assignment_type_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_group_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_group_questions` (
  `id` int(10) NOT NULL,
  `teacher_library_quiz_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_lesson_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_lesson_elements` (
  `id` int(10) NOT NULL,
  `teacher_library_lesson_id` int(10) NOT NULL,
  `text` longtext DEFAULT NULL,
  `media` text DEFAULT NULL,
  `attachfile` text DEFAULT NULL,
  `show_type` varchar(255) DEFAULT NULL,
  `lesson_type` enum('in_lesson','file_lesson') DEFAULT NULL,
  `text_script` longtext DEFAULT NULL,
  `teacher_library_lesson_question_id` int(10) unsigned DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_lesson_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_lesson_questions` (
  `id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `teacher_library_lesson_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 1,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_lessons` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short_desc` text DEFAULT NULL,
  `lesson_type_id` int(10) NOT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `general_library_item` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_projects` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `teacher_id` int(10) NOT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `is_assessment` int(10) NOT NULL DEFAULT 0,
  `file_summited` int(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_questions` (
  `id` int(10) NOT NULL,
  `teacher_library_quiz_id` int(10) NOT NULL,
  `question_group_id` int(10) NOT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `subject_branch` int(10) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `feedback_massage` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `upload_file` mediumtext DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_questions_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_questions_options` (
  `id` int(10) NOT NULL,
  `teacher_library_question_id` int(10) NOT NULL,
  `option_text` text NOT NULL,
  `correct` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_quiz_exam_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_quiz_exam_sections` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `teacher_library_quiz_exam_id` int(10) DEFAULT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `instructions` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_library_quizzes_and_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_library_quizzes_and_exams` (
  `id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `teacher_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `grade_level_subject_id` int(10) NOT NULL,
  `type` enum('quiz','exam') NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quiz_type` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `timer` time DEFAULT NULL,
  `is_assessment` int(10) NOT NULL DEFAULT 0,
  `assessments_type_id` int(10) DEFAULT NULL,
  `feedback_answers` enum('after each question','in the end of quiz','only teacher') NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `points` int(10) NOT NULL,
  `is_library_item` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_questions_reflections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_questions_reflections` (
  `id` int(10) NOT NULL,
  `teacher_reflection_type` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `text_question` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_reflection_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_reflection_types` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_subject_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_subject_classes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_teacher_coteacher_id` int(10) DEFAULT NULL,
  `teacher_name` varchar(255) DEFAULT NULL,
  `class_subject_id` int(10) DEFAULT NULL,
  `grade_id` int(10) NOT NULL,
  `grade_name` varchar(255) DEFAULT NULL,
  `class_id` int(10) NOT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `class_img` varchar(255) DEFAULT NULL,
  `subject_id` int(10) NOT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `status` enum('active','current','past','inactive','archived') NOT NULL DEFAULT 'current',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `removed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tsc_user_teacher_coteacher_id` (`user_teacher_coteacher_id`),
  KEY `idx_tsc_class_id` (`class_id`),
  KEY `idx_tsc_grade_id` (`grade_id`),
  KEY `idx_tsc_subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_unit_learning_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_unit_learning_process` (
  `id` int(11) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `learning_process_type` int(10) NOT NULL,
  `content` text NOT NULL,
  `teacher_subject_class_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ted_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ted_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `video_link` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` bigint(20) unsigned NOT NULL,
  `parent_name` varchar(255) NOT NULL,
  `child_age` int(11) DEFAULT NULL,
  `service_used` varchar(255) NOT NULL,
  `testimonial_text` text NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `photo_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `timer_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timer_sounds` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tu` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `iframe_link` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tv_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tv_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_assessment_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_assessment_criteria` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `assessment_criteria_id` int(10) NOT NULL,
  `assessment_criteria_strand_id` int(10) DEFAULT NULL,
  `academic_year_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_atl_skills` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_global_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_global_context` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `global_context_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_key_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `key_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_dp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_dp_related_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `related_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_field_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_field_descriptions` (
  `id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_interdisciplinary_collaborators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_interdisciplinary_collaborators` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_atl_skills` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_global_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_global_context` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `global_context_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_key_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_key_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `key_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_myp_related_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_myp_related_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `related_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_atl_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_atl_skills` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `atl_skills` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_check_point_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_check_point_list` (
  `id` int(10) NOT NULL,
  `check_point_list_pyp_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_ib_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_ib_resources` (
  `id` int(10) NOT NULL,
  `ib_resource_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_inquiry_student_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_inquiry_student_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_inquiry_teacher_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_inquiry_teacher_questions` (
  `id` int(10) NOT NULL,
  `inquiry_questions_type` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_learner_profiles_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_learner_profiles_attributes` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `learner_profiles_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_local_global_challenges_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_local_global_challenges_opportunities` (
  `id` int(10) NOT NULL,
  `local_global_challenges_opportunities_type_id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_other_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_other_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `other_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_specified_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_specified_concepts` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `specified_concepts_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_pyp_transdisciplinary_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_pyp_transdisciplinary_theme` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `theme_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_semester_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_semester_map` (
  `id` int(10) NOT NULL,
  `unit_id` int(10) NOT NULL,
  `semester_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `program_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `teacher_subject_classes_id` int(10) NOT NULL,
  `academic_year_id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `class_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `grade_level_id` int(10) NOT NULL,
  `unit_type_id` int(10) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_interdisciplinary` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_units_teacher_subject_classes_id` (`teacher_subject_classes_id`),
  KEY `idx_units_academic_year_id` (`academic_year_id`),
  KEY `idx_units_subject_id` (`subject_id`),
  KEY `idx_units_class_id` (`class_id`),
  KEY `idx_units_teacher_id` (`teacher_id`),
  KEY `idx_units_grade_level_id` (`grade_level_id`),
  KEY `idx_units_unit_type_id` (`unit_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_dp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_dp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 1,
  `unit_id` int(10) NOT NULL,
  `central_idea` text DEFAULT NULL,
  `lines_of_inquiry` text DEFAULT NULL,
  `action` text DEFAULT NULL,
  `connections_transdisciplinary_and_past` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `initial_reflections` text DEFAULT NULL,
  `prior_learning` text DEFAULT NULL,
  `connections_transdisciplinary_past` text DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `co_constructed_success_criteria` text DEFAULT NULL,
  `monitoring_documenting_measuring` text DEFAULT NULL,
  `summative_inquiry_relationship` text DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_myp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_myp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 2,
  `unit_id` int(10) NOT NULL,
  `statement_inquiry` text DEFAULT NULL,
  `objectives` text DEFAULT NULL,
  `summative_assessment` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `summative_inquiry_relationship` varchar(255) DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `additional_teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_pyp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_pyp` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 1,
  `unit_id` int(10) NOT NULL,
  `central_idea` text DEFAULT NULL,
  `lines_of_inquiry` text DEFAULT NULL,
  `action` text DEFAULT NULL,
  `connections_transdisciplinary_and_past` text DEFAULT NULL,
  `services_as_action` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `initial_reflections` text DEFAULT NULL,
  `prior_learning` text DEFAULT NULL,
  `connections_transdisciplinary_past` text DEFAULT NULL,
  `learning_goals` text DEFAULT NULL,
  `co_constructed_success_criteria` text DEFAULT NULL,
  `monitoring_documenting_measuring` text DEFAULT NULL,
  `summative_inquiry_relationship` text DEFAULT NULL,
  `designing_engaging_learning_experiences` text DEFAULT NULL,
  `learning_environment_and_spaces` text DEFAULT NULL,
  `supporting_student_agency` text DEFAULT NULL,
  `teacher_and_student_questions` text DEFAULT NULL,
  `formative_assessment` text DEFAULT NULL,
  `differentiation` text DEFAULT NULL,
  `service_as_action` text DEFAULT NULL,
  `student_self_reflection` text DEFAULT NULL,
  `peer_feedback` text DEFAULT NULL,
  `unit_duration` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_standrand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_standrand` (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `program_id` int(10) NOT NULL DEFAULT 4,
  `unit_id` int(10) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_types` (
  `id` int(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` int(10) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `program_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_logins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` enum('female','male') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `decryp_password` text DEFAULT NULL COMMENT 'Legacy plaintext - no longer written by new code; nullable so new flows skip it',
  `recoverable_password_encrypted` text DEFAULT NULL COMMENT 'Encrypted recoverable credential via Laravel encrypted cast / APP_KEY. Updated atomically with users.password.',
  `phone` varchar(255) DEFAULT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` int(10) DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `activated_at` datetime DEFAULT NULL,
  `activated_by_admin_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vocabulary_game_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary_game_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vocabulary_set_id` bigint(20) unsigned NOT NULL,
  `assigned_by_user_id` bigint(20) unsigned NOT NULL,
  `audience_type` enum('student','class') NOT NULL,
  `audience_id` bigint(20) unsigned NOT NULL,
  `allowed_games` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`allowed_games`)),
  `difficulty_policy` enum('student_choice','sprout','climber','champion') NOT NULL DEFAULT 'student_choice',
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vocabulary_game_assignments_set` (`vocabulary_set_id`),
  KEY `idx_vocabulary_game_assignments_audience` (`audience_type`,`audience_id`,`status`),
  KEY `idx_vocabulary_game_assignments_assigned_by` (`assigned_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vocabulary_set_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary_set_words` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vocabulary_set_id` bigint(20) unsigned NOT NULL,
  `word_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `added_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vocabulary_set_words_set_word` (`vocabulary_set_id`,`word_id`),
  KEY `idx_vocabulary_set_words_word` (`word_id`),
  KEY `idx_vocabulary_set_words_position` (`vocabulary_set_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vocabulary_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary_sets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `node_type` enum('folder','playable') NOT NULL DEFAULT 'playable',
  `set_type` enum('system','teacher','legacy_import') NOT NULL DEFAULT 'teacher',
  `source_kind` enum('custom','legacy_cambridge','legacy_phonics','legacy_group','legacy_difficulty','legacy_hangman') NOT NULL DEFAULT 'custom',
  `source_key` varchar(120) DEFAULT NULL,
  `owner_user_id` bigint(20) unsigned DEFAULT NULL,
  `visibility` enum('private','shared','system','archived') NOT NULL DEFAULT 'private',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `updated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vocabulary_sets_source` (`source_kind`,`source_key`),
  KEY `idx_vocabulary_sets_parent` (`parent_id`),
  KEY `idx_vocabulary_sets_node_type` (`node_type`),
  KEY `idx_vocabulary_sets_owner_visibility` (`owner_user_id`,`visibility`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vocabulary_source_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary_source_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vocabulary_set_id` bigint(20) unsigned NOT NULL,
  `audience_type` enum('student','class') NOT NULL,
  `audience_id` bigint(20) unsigned NOT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `enabled_by_user_id` bigint(20) unsigned NOT NULL,
  `enabled_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vocabulary_source_access_current` (`vocabulary_set_id`,`audience_type`,`audience_id`),
  KEY `idx_vocabulary_source_access_set` (`vocabulary_set_id`),
  KEY `idx_vocabulary_source_access_audience` (`audience_type`,`audience_id`,`status`),
  KEY `idx_vocabulary_source_access_enabled_by` (`enabled_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
