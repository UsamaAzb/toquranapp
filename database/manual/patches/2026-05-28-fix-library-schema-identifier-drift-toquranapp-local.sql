-- To Quran local dry-run DB Library schema identifier correction
-- Date: 2026-05-28
-- Target DB: toquranapp_local
-- Purpose:
--   - rename malformed `teacher and_student_questions` columns to
--     `teacher_and_student_questions`;
--   - rename the mismatched MYP local/global challenges column from
--     `general_library_dp_unit_id` to `general_library_myp_unit_id`.
--
-- Required execution guard:
--   SET @toquran_confirm_local_db_target = 'toquranapp_local';

USE `toquranapp_local`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_fix_library_identifier_drift`$$
CREATE PROCEDURE `_toquran_fix_library_identifier_drift`()
BEGIN
    IF COALESCE(@toquran_confirm_local_db_target, '') <> 'toquranapp_local' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_local_db_target = ''toquranapp_local'' before running this patch.';
    END IF;

    IF DATABASE() <> 'toquranapp_local' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for Library identifier correction.';
    END IF;
END$$

DROP PROCEDURE IF EXISTS `_toquran_rename_column_if_needed`$$
CREATE PROCEDURE `_toquran_rename_column_if_needed`(
    IN p_table_name VARCHAR(128),
    IN p_old_column_name VARCHAR(128),
    IN p_new_column_name VARCHAR(128),
    IN p_column_definition VARCHAR(255)
)
BEGIN
    IF EXISTS (
        SELECT 1
          FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = p_table_name
           AND column_name = p_old_column_name
    ) THEN
        IF EXISTS (
            SELECT 1
              FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = p_table_name
               AND column_name = p_new_column_name
        ) THEN
            SET @toquran_column_message = CONCAT(
                'ABORTED: both old and new Library columns exist on ',
                p_table_name,
                '.'
            );
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @toquran_column_message;
        END IF;

        SET @toquran_rename_sql = CONCAT(
            'ALTER TABLE `',
            p_table_name,
            '` CHANGE COLUMN `',
            p_old_column_name,
            '` `',
            p_new_column_name,
            '` ',
            p_column_definition
        );

        PREPARE toquran_rename_stmt FROM @toquran_rename_sql;
        EXECUTE toquran_rename_stmt;
        DEALLOCATE PREPARE toquran_rename_stmt;
    ELSEIF NOT EXISTS (
        SELECT 1
          FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = p_table_name
           AND column_name = p_new_column_name
    ) THEN
        SET @toquran_missing_column_message = CONCAT(
            'ABORTED: neither old nor new Library column exists on ',
            p_table_name,
            '.'
        );
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @toquran_missing_column_message;
    END IF;
END$$

CALL `_toquran_fix_library_identifier_drift`()$$

CALL `_toquran_rename_column_if_needed`(
    'general_library_units_dp',
    'teacher and_student_questions',
    'teacher_and_student_questions',
    'text DEFAULT NULL'
)$$

CALL `_toquran_rename_column_if_needed`(
    'general_library_units_pyp',
    'teacher and_student_questions',
    'teacher_and_student_questions',
    'text DEFAULT NULL'
)$$

CALL `_toquran_rename_column_if_needed`(
    'units_dp',
    'teacher and_student_questions',
    'teacher_and_student_questions',
    'text DEFAULT NULL'
)$$

CALL `_toquran_rename_column_if_needed`(
    'units_pyp',
    'teacher and_student_questions',
    'teacher_and_student_questions',
    'text DEFAULT NULL'
)$$

CALL `_toquran_rename_column_if_needed`(
    'general_library_unit_myp_local_global_challenges_opportunities',
    'general_library_dp_unit_id',
    'general_library_myp_unit_id',
    'int(10) NOT NULL'
)$$

DROP PROCEDURE IF EXISTS `_toquran_rename_column_if_needed`$$
DROP PROCEDURE IF EXISTS `_toquran_fix_library_identifier_drift`$$

DELIMITER ;
