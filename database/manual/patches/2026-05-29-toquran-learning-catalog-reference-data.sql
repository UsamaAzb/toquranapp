-- To Quran learning catalog reference data
-- Date: 2026-05-29
-- Target: real To Quran app DB, `u504065335_to_quran`
-- Backup before execution: database/manual/backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql
-- Purpose:
--   - add the remaining launch LMS class-subject catalog rows;
--   - map the new subjects to existing learner levels;
--   - keep MDJ and Well Being as separate app concepts.
-- Scope:
--   - subjects: Arabic Language, Sanad Program, Well Being;
--   - grade_level_subjects for all existing launch learner levels.
-- Non-goals:
--   - no public service/catalog change;
--   - no user/student/family/class data change;
--   - no teacher assignment rows;
--   - no Week14 school-subject activation.

USE `u504065335_to_quran`;

DELIMITER $$
DROP PROCEDURE IF EXISTS `_toquran_learning_catalog_preflight`$$
CREATE PROCEDURE `_toquran_learning_catalog_preflight`()
BEGIN
    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Refusing learning catalog patch: set @toquran_confirm_real_db_target = ''u504065335_to_quran'' before running this patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Refusing learning catalog patch: selected DB is not u504065335_to_quran';
    END IF;

    IF (
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = 'u504065335_to_quran'
    ) < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Refusing learning catalog patch: app schema baseline is not present';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM `subjects`
        WHERE (`id` = 1 AND (`title` <> 'Quran Memorization' OR `code` <> 'QURAN_MEM'))
           OR (`id` = 2 AND (`title` <> 'Quranic Arabic' OR `code` <> 'QURAN_AR'))
           OR (`id` = 3 AND (`title` <> 'Arabic Language' OR `code` <> 'ARABIC_LANG'))
           OR (`id` = 4 AND (`title` <> 'Sanad Program' OR `code` <> 'SANAD'))
           OR (`id` = 15 AND (`title` <> 'My Deen Journey' OR `code` <> 'MDJ'))
           OR (`id` = 16 AND (`title` <> 'Well Being' OR `code` <> 'WELL_BEING'))
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Learning catalog drift detected: canonical subject IDs already map to different values';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM `subjects`
        WHERE (`title` = 'Arabic Language' AND `id` <> 3)
           OR (`title` = 'Sanad Program' AND `id` <> 4)
           OR (`title` = 'Well Being' AND `id` <> 16)
           OR (`code` = 'ARABIC_LANG' AND `id` <> 3)
           OR (`code` = 'SANAD' AND `id` <> 4)
           OR (`code` = 'WELL_BEING' AND `id` <> 16)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Learning catalog drift detected: canonical subject title/code exists under a different ID';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM `grade_level_subjects`
        WHERE `subject_id` IN (3, 4, 16)
          AND (`type` <> 'standard' OR `status` <> 'active' OR `academic_year_id` <> 1)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Learning catalog drift detected: existing grade-level subject mapping has unexpected type/status/year';
    END IF;
END$$
DELIMITER ;

CALL `_toquran_learning_catalog_preflight`();
DROP PROCEDURE IF EXISTS `_toquran_learning_catalog_preflight`;

START TRANSACTION;

INSERT INTO `subjects` (`id`, `title`, `type`, `program_id`, `code`, `icon`, `active`, `row_status`, `created_at`, `updated_at`)
SELECT catalog_subjects.subject_id,
       catalog_subjects.subject_title,
       'standard',
       1,
       catalog_subjects.subject_code,
       catalog_subjects.subject_icon,
       1,
       'current',
       NOW(),
       NOW()
FROM (
    SELECT 3 AS subject_id, 'Arabic Language' AS subject_title, 'ARABIC_LANG' AS subject_code, 'ti tabler-language' AS subject_icon UNION ALL
    SELECT 4, 'Sanad Program', 'SANAD', 'ti tabler-certificate' UNION ALL
    SELECT 16, 'Well Being', 'WELL_BEING', 'ti tabler-heart-handshake'
) AS catalog_subjects
WHERE NOT EXISTS (
    SELECT 1
    FROM `subjects`
    WHERE `subjects`.`id` = catalog_subjects.subject_id
);

INSERT INTO `grade_level_subjects` (`grade_level_id`, `subject_id`, `academic_year_id`, `type`, `status`, `created_by_user_id`, `created_at`, `updated_at`)
SELECT grade_levels_to_map.grade_level_id,
       subjects_to_map.subject_id,
       1,
       'standard',
       'active',
       0,
       NOW(),
       NOW()
FROM (
    SELECT 1 AS grade_level_id UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4
) AS grade_levels_to_map
CROSS JOIN (
    SELECT 3 AS subject_id UNION ALL
    SELECT 4 UNION ALL
    SELECT 16
) AS subjects_to_map
WHERE EXISTS (
    SELECT 1
    FROM `grade_levels`
    WHERE `grade_levels`.`id` = grade_levels_to_map.grade_level_id
)
AND EXISTS (
    SELECT 1
    FROM `subjects`
    WHERE `subjects`.`id` = subjects_to_map.subject_id
)
AND NOT EXISTS (
    SELECT 1
    FROM `grade_level_subjects`
    WHERE `grade_level_subjects`.`grade_level_id` = grade_levels_to_map.grade_level_id
      AND `grade_level_subjects`.`subject_id` = subjects_to_map.subject_id
);

COMMIT;
