-- To Quran real app DB Library column-name correction
-- Date: 2026-05-28
-- Target DB: u504065335_to_quran
-- Purpose: rename the malformed legacy column
-- `general_library_unit_dp_global_context`.` general_library_dp_unit_id`
-- to `general_library_dp_unit_id`.
--
-- Required execution guard:
--   SET @toquran_confirm_real_db_target = 'u504065335_to_quran';

USE `u504065335_to_quran`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_fix_library_dp_context_column`$$
CREATE PROCEDURE `_toquran_fix_library_dp_context_column`()
BEGIN
    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = ''u504065335_to_quran'' before running this patch.';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for Library column correction.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
          FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = 'general_library_unit_dp_global_context'
           AND column_name = ' general_library_dp_unit_id'
    ) THEN
        IF NOT EXISTS (
            SELECT 1
              FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = 'general_library_unit_dp_global_context'
               AND column_name = 'general_library_dp_unit_id'
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'ABORTED: expected Library context column was not found.';
        END IF;
    ELSEIF EXISTS (
        SELECT 1
          FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = 'general_library_unit_dp_global_context'
           AND column_name = 'general_library_dp_unit_id'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: both malformed and corrected Library context columns exist.';
    ELSE
        ALTER TABLE `general_library_unit_dp_global_context`
            CHANGE COLUMN ` general_library_dp_unit_id` `general_library_dp_unit_id` int(10) NOT NULL;
    END IF;
END$$

CALL `_toquran_fix_library_dp_context_column`()$$

DROP PROCEDURE IF EXISTS `_toquran_fix_library_dp_context_column`$$

DELIMITER ;
