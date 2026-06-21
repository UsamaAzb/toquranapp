-- To Quran service/subject catalog expansion: Islamic Studies and Quran Literature
-- Date: 2026-06-21
-- Target DB: u504065335_to_quran
-- Backup before execution: required before production execution; record evidence in a separate execution note.
-- Purpose:
--   - add Islamic Studies and Quran Literature as public/app child-facing service values;
--   - add matching LMS subject rows;
--   - map both subjects to current launch learner levels so they appear in Student Account > Subject Access.
-- Scope:
--   - services_types rows for Islamic Studies and Quran Literature;
--   - services rows ids 7 and 8;
--   - subjects rows ids 17 and 18;
--   - grade_level_subjects mappings for learner levels 1-4 where those levels exist.
-- Non-goals:
--   - no booking, student, parent, teacher, family, class, session, task, or gift rows;
--   - no automatic activation for existing students;
--   - no destructive cleanup.

USE `u504065335_to_quran`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_add_islamic_quran_lit_catalog`$$

CREATE PROCEDURE `_toquran_add_islamic_quran_lit_catalog`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running this patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran service/subject catalog expansion';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: app schema baseline is not present';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `services`
         WHERE (`id` = 7 AND `name` <> 'Islamic Studies')
            OR (`id` = 8 AND `name` <> 'Quran Literature')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Service catalog drift detected: ids 7/8 are already used for different services';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `services`
         WHERE (`name` = 'Islamic Studies' AND `id` <> 7)
            OR (`name` = 'Quran Literature' AND `id` <> 8)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Service catalog drift detected: new service name already exists under a different id';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `services_types`
         WHERE (`value` = 'Islamic Studies' AND `title` <> 'Islamic Studies')
            OR (`value` = 'Quran Literature' AND `title` <> 'Quran Literature')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Service type drift detected: new service value has a different title';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `subjects`
         WHERE (`id` = 17 AND (`title` <> 'Islamic Studies' OR `code` <> 'ISLAMIC_STUDIES'))
            OR (`id` = 18 AND (`title` <> 'Quran Literature' OR `code` <> 'QURAN_LIT'))
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Subject catalog drift detected: ids 17/18 are already used for different subjects';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `subjects`
         WHERE (`title` = 'Islamic Studies' AND `id` <> 17)
            OR (`title` = 'Quran Literature' AND `id` <> 18)
            OR (`code` = 'ISLAMIC_STUDIES' AND `id` <> 17)
            OR (`code` = 'QURAN_LIT' AND `id` <> 18)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Subject catalog drift detected: new subject title/code already exists under a different id';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `grade_level_subjects`
         WHERE `subject_id` IN (17, 18)
           AND (`type` <> 'standard' OR `status` <> 'active' OR `academic_year_id` <> 1)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Grade-level subject drift detected: new subject mapping has unexpected type/status/year';
    END IF;
END$$

CALL `_toquran_add_islamic_quran_lit_catalog`()$$

DROP PROCEDURE IF EXISTS `_toquran_add_islamic_quran_lit_catalog`$$

DELIMITER ;

START TRANSACTION;

INSERT INTO `services_types` (`title`, `value_old`, `value`, `info`, `active`)
SELECT service_rows.title,
       service_rows.value_old,
       service_rows.value,
       service_rows.info,
       1
FROM (
    SELECT 'Islamic Studies' AS title, 'Islamic Studies' AS value_old, 'Islamic Studies' AS value, 'Age-appropriate Islamic Studies tutoring and practice.' AS info UNION ALL
    SELECT 'Quran Literature', 'Quran Literature', 'Quran Literature', 'Quran stories, meanings, themes, and literature support.'
) AS service_rows
WHERE NOT EXISTS (
    SELECT 1
      FROM `services_types`
     WHERE `services_types`.`value` = service_rows.value
);

INSERT INTO `services` (`id`, `name`)
SELECT service_rows.id,
       service_rows.name
FROM (
    SELECT 7 AS id, 'Islamic Studies' AS name UNION ALL
    SELECT 8, 'Quran Literature'
) AS service_rows
WHERE NOT EXISTS (
    SELECT 1
      FROM `services`
     WHERE `services`.`id` = service_rows.id
);

INSERT INTO `subjects` (`id`, `title`, `type`, `program_id`, `code`, `icon`, `active`, `row_status`, `created_at`, `updated_at`)
SELECT subject_rows.id,
       subject_rows.title,
       'standard',
       1,
       subject_rows.code,
       subject_rows.icon,
       1,
       'current',
       NOW(),
       NOW()
FROM (
    SELECT 17 AS id, 'Islamic Studies' AS title, 'ISLAMIC_STUDIES' AS code, 'ti tabler-moon-stars' AS icon UNION ALL
    SELECT 18, 'Quran Literature', 'QURAN_LIT', 'ti tabler-book'
) AS subject_rows
WHERE NOT EXISTS (
    SELECT 1
      FROM `subjects`
     WHERE `subjects`.`id` = subject_rows.id
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
    SELECT 17 AS subject_id UNION ALL
    SELECT 18
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
