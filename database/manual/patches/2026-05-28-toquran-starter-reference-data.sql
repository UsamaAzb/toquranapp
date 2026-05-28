-- To Quran starter/reference data
-- Date: 2026-05-28
-- Target: real To Quran app DB, `u504065335_to_quran`
-- Depends on: database/manual/patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql
-- Purpose: create the minimum reference rows needed before TQ2 intake/family adaptation.
-- Scope:
--   - roles used by imported route middleware;
--   - To Quran service catalog values used by intake transfer checks;
--   - one current operating year;
--   - To Quran tutoring program, learner levels, and starter subjects;
--   - grade-level subject rows needed by BookingTransferReadiness.
-- Non-goals:
--   - no user/admin/teacher/student/parent accounts;
--   - no Week14 QA rows;
--   - no Week14 English content;
--   - no Quran video Library migration.

USE `u504065335_to_quran`;

DELIMITER $$
DROP PROCEDURE IF EXISTS `_toquran_starter_reference_preflight`$$
CREATE PROCEDURE `_toquran_starter_reference_preflight`()
BEGIN
    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Refusing starter data: selected DB is not u504065335_to_quran';
    END IF;

    IF (
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = 'u504065335_to_quran'
    ) < 300 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Refusing starter data: app schema baseline is not present';
    END IF;
END$$
DELIMITER ;

CALL `_toquran_starter_reference_preflight`();
DROP PROCEDURE `_toquran_starter_reference_preflight`;

SET @now := NOW();

START TRANSACTION;

-- Spatie roles. `owner` is retained because imported Week14 routes still reference it.
INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT role_name, 'web', @now, @now
FROM (
    SELECT 'owner' AS role_name UNION ALL
    SELECT 'super_admin' UNION ALL
    SELECT 'admin' UNION ALL
    SELECT 'customer_support' UNION ALL
    SELECT 'teacher' UNION ALL
    SELECT 'parent' UNION ALL
    SELECT 'student'
) AS starter_roles
WHERE NOT EXISTS (
    SELECT 1
    FROM `roles`
    WHERE `roles`.`name` = starter_roles.role_name
      AND `roles`.`guard_name` = 'web'
);

-- Public/app service interests. Values must match BookingServiceInterest display/normalization.
INSERT INTO `services_types` (`title`, `value_old`, `value`, `info`, `active`)
SELECT service_title, service_value, service_value, service_info, 1
FROM (
    SELECT 'Quran Memorization' AS service_title, 'Quran Memorization' AS service_value, 'Private Quran memorization and revision tutoring.' AS service_info UNION ALL
    SELECT 'Quranic Arabic', 'Quranic Arabic', 'Arabic reading and Quranic Arabic tutoring.' UNION ALL
    SELECT 'My Deen Journey', 'My Deen Journey', 'Learner task journey, rewards, accountability, and parent follow-up.' UNION ALL
    SELECT 'Paid Parental Consultation', 'Paid Parental Consultation', 'Paid parent consultation before or alongside enrollment.' UNION ALL
    SELECT 'Sanad Ijazah', 'Sanad Ijazah', 'Advanced recitation and ijazah-oriented pathway.'
) AS starter_services
WHERE NOT EXISTS (
    SELECT 1
    FROM `services_types`
    WHERE `services_types`.`value` = starter_services.service_value
);

INSERT INTO `services` (`id`, `name`)
SELECT service_id, service_name
FROM (
    SELECT 1 AS service_id, 'Quran Memorization' AS service_name UNION ALL
    SELECT 2, 'Quranic Arabic' UNION ALL
    SELECT 3, 'My Deen Journey' UNION ALL
    SELECT 4, 'Paid Parental Consultation' UNION ALL
    SELECT 5, 'Sanad Ijazah'
) AS starter_service_names
WHERE NOT EXISTS (
    SELECT 1
    FROM `services`
    WHERE `services`.`id` = starter_service_names.service_id
);

INSERT INTO `academic_years` (`id`, `title`, `start_date`, `end_date`, `is_current`, `created_at`, `updated_at`)
SELECT 1, '2026-2027', '2026-05-28', '2027-05-27', 1, @now, @now
WHERE NOT EXISTS (
    SELECT 1 FROM `academic_years` WHERE `id` = 1
);

UPDATE `academic_years`
SET `is_current` = CASE WHEN `id` = 1 THEN 1 ELSE 0 END,
    `updated_at` = @now
WHERE `id` = 1 OR `is_current` = 1;

INSERT INTO `school_program` (`id`, `title`, `code`, `active`, `created_at`, `updated_at`)
SELECT 1, 'To Quran Private Tutoring', 'TQ', 1, @now, @now
WHERE NOT EXISTS (
    SELECT 1 FROM `school_program` WHERE `id` = 1
);

INSERT INTO `subjects` (`id`, `title`, `type`, `program_id`, `code`, `icon`, `active`, `row_status`, `created_at`, `updated_at`)
SELECT subject_id, subject_title, subject_type, 1, subject_code, subject_icon, 1, 'current', @now, @now
FROM (
    SELECT 1 AS subject_id, 'Quran Memorization' AS subject_title, 'standard' AS subject_type, 'QURAN_MEM' AS subject_code, 'ti tabler-book-2' AS subject_icon UNION ALL
    SELECT 2, 'Quranic Arabic', 'standard', 'QURAN_AR', 'ti tabler-language' UNION ALL
    SELECT 15, 'My Deen Journey', 'standard', 'MDJ', 'ti tabler-heart-handshake'
) AS starter_subjects
WHERE NOT EXISTS (
    SELECT 1
    FROM `subjects`
    WHERE `subjects`.`id` = starter_subjects.subject_id
);

INSERT INTO `grade_levels` (`id`, `title`, `active`, `level_order`, `program_id`, `code`, `created_at`, `updated_at`)
SELECT grade_level_id, grade_level_title, 1, grade_level_order, 1, grade_level_code, @now, @now
FROM (
    SELECT 1 AS grade_level_id, 'General Learner' AS grade_level_title, 1 AS grade_level_order, 'GENERAL' AS grade_level_code UNION ALL
    SELECT 2, 'Beginner', 2, 'BEGINNER' UNION ALL
    SELECT 3, 'Intermediate', 3, 'INTERMEDIATE' UNION ALL
    SELECT 4, 'Advanced', 4, 'ADVANCED'
) AS starter_grade_levels
WHERE NOT EXISTS (
    SELECT 1
    FROM `grade_levels`
    WHERE `grade_levels`.`id` = starter_grade_levels.grade_level_id
);

INSERT INTO `grade_level_subjects` (`grade_level_id`, `subject_id`, `academic_year_id`, `type`, `status`, `created_by_user_id`, `created_at`, `updated_at`)
SELECT starter_grade_subjects.grade_level_id,
       starter_grade_subjects.subject_id,
       1,
       'standard',
       'active',
       0,
       @now,
       @now
FROM (
    SELECT 1 AS grade_level_id, 1 AS subject_id UNION ALL
    SELECT 1, 2 UNION ALL
    SELECT 1, 15 UNION ALL
    SELECT 2, 1 UNION ALL
    SELECT 2, 2 UNION ALL
    SELECT 2, 15 UNION ALL
    SELECT 3, 1 UNION ALL
    SELECT 3, 2 UNION ALL
    SELECT 3, 15 UNION ALL
    SELECT 4, 1 UNION ALL
    SELECT 4, 2 UNION ALL
    SELECT 4, 15
) AS starter_grade_subjects
WHERE NOT EXISTS (
    SELECT 1
    FROM `grade_level_subjects`
    WHERE `grade_level_subjects`.`grade_level_id` = starter_grade_subjects.grade_level_id
      AND `grade_level_subjects`.`subject_id` = starter_grade_subjects.subject_id
      AND `grade_level_subjects`.`academic_year_id` = 1
);

COMMIT;
