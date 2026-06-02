-- To Quran app contacts schema correction: allow generic Contact Us submissions
-- Date: 2026-06-02
-- Target DB: u504065335_to_quran
-- Backup: required before execution; export the confirmed target DB before running.
-- Purpose: public Contact Us writes app-compatible contacts rows without child-specific fields.
--          contacts.child_age must therefore be nullable. Do not remove the column because
--          app-side child-specific contact workflows may still use it.
-- Status: executed locally against u504065335_to_quran on 2026-06-02 after focused structure backup.

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_make_contacts_child_age_nullable`$$

CREATE PROCEDURE `_toquran_make_contacts_child_age_nullable`()
BEGIN
    DECLARE table_count INT DEFAULT 0;
    DECLARE column_count INT DEFAULT 0;
    DECLARE nullable_value VARCHAR(3) DEFAULT NULL;
    DECLARE column_type VARCHAR(64) DEFAULT NULL;
    DECLARE column_length BIGINT DEFAULT NULL;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running this patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran contacts child_age patch';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: target does not look like the To Quran app schema';
    END IF;

    SELECT COUNT(*), MAX(IS_NULLABLE), MAX(DATA_TYPE), MAX(CHARACTER_MAXIMUM_LENGTH)
      INTO column_count, nullable_value, column_type, column_length
      FROM information_schema.columns
     WHERE table_schema = DATABASE()
       AND table_name = 'contacts'
       AND column_name = 'child_age';

    IF column_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: contacts.child_age column was not found exactly once';
    END IF;

    IF column_type <> 'varchar' OR column_length <> 255 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: contacts.child_age is not the expected varchar(255) column';
    END IF;

    IF nullable_value = 'NO' THEN
        ALTER TABLE `contacts`
            MODIFY `child_age` VARCHAR(255) NULL DEFAULT NULL;
    END IF;
END$$

CALL `_toquran_make_contacts_child_age_nullable`()$$

DROP PROCEDURE IF EXISTS `_toquran_make_contacts_child_age_nullable`$$

DELIMITER ;
