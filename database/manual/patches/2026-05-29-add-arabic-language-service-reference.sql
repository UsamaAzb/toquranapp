-- To Quran real-target service catalog correction: Arabic Language public/app service
-- Date: 2026-05-29
-- Target DB: u504065335_to_quran
-- Backup: database/manual/backups/2026-05-29-165938-u504065335_to_quran-before-arabic-language-service.sql
-- Purpose: align app intake normalization and future public website multi-service intake with Arabic Language as a distinct selectable service.

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_add_arabic_language_service`$$

CREATE PROCEDURE `_toquran_add_arabic_language_service`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running this patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran Arabic Language service patch';
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
         WHERE `id` = 6
           AND `name` <> 'Arabic Language'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Service catalog drift detected: service id 6 is not Arabic Language';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `services_types`
         WHERE `value` = 'Arabic Language'
           AND `title` <> 'Arabic Language'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Service type drift detected: Arabic Language value has a different title';
    END IF;

    INSERT INTO `services_types` (`title`, `value_old`, `value`, `info`, `active`)
    SELECT 'Arabic Language', 'Arabic Language', 'Arabic Language', 'Broader Arabic language tutoring distinct from Quranic Arabic.', 1
    WHERE NOT EXISTS (
        SELECT 1
          FROM `services_types`
         WHERE `value` = 'Arabic Language'
    );

    INSERT INTO `services` (`id`, `name`)
    SELECT 6, 'Arabic Language'
    WHERE NOT EXISTS (
        SELECT 1
          FROM `services`
         WHERE `id` = 6
    );
END$$

CALL `_toquran_add_arabic_language_service`()$$

DROP PROCEDURE IF EXISTS `_toquran_add_arabic_language_service`$$

DELIMITER ;
