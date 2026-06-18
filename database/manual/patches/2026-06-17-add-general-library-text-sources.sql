-- TQ7.5 General Library text sources
-- Purpose: allow reviewed text-only sources, used first by the My Deen Journey Dua Bank.
-- Safety:
--   1. Requires explicit target confirmation through @toquran_confirm_real_db_target.
--   2. Requires the TQ6 General Library tables to exist.
--   3. Adds only one nullable text column and widens the resource_type enum to include 'text'.
--   4. Does not insert, update, or delete Library content.

SET @toquran_expected_db := 'u504065335_to_quran';

DROP PROCEDURE IF EXISTS tq75_guard_general_library_text_sources;

DELIMITER //
CREATE PROCEDURE tq75_guard_general_library_text_sources()
BEGIN
    IF COALESCE(@toquran_confirm_real_db_target, '') <> @toquran_expected_db
        OR DATABASE() <> @toquran_expected_db THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Refusing to alter General Library text sources without confirmed To Quran app DB target.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'general_library_resources'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'general_library_resources table is missing; run TQ6 General Library SQL first.';
    END IF;
END//
DELIMITER ;

CALL tq75_guard_general_library_text_sources();
DROP PROCEDURE IF EXISTS tq75_guard_general_library_text_sources;

SET @toquran_text_content_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'general_library_resources'
      AND COLUMN_NAME = 'text_content'
);

SET @toquran_add_text_content_sql := IF(
    @toquran_text_content_exists = 0,
    'ALTER TABLE general_library_resources ADD COLUMN text_content MEDIUMTEXT NULL AFTER external_url',
    'SELECT ''general_library_resources.text_content already exists'' AS note'
);

PREPARE tquran_stmt FROM @toquran_add_text_content_sql;
EXECUTE tquran_stmt;
DEALLOCATE PREPARE tquran_stmt;

SET @toquran_resource_type_column := (
    SELECT COLUMN_TYPE
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'general_library_resources'
      AND COLUMN_NAME = 'resource_type'
);

SET @toquran_add_text_enum_sql := IF(
    @toquran_resource_type_column LIKE '%''text''%',
    'SELECT ''general_library_resources.resource_type already supports text'' AS note',
    'ALTER TABLE general_library_resources MODIFY resource_type ENUM(''file'', ''link'', ''youtube'', ''text'') NOT NULL'
);

PREPARE tquran_stmt FROM @toquran_add_text_enum_sql;
EXECUTE tquran_stmt;
DEALLOCATE PREPARE tquran_stmt;

SELECT
    DATABASE() AS database_name,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'general_library_resources'
  AND COLUMN_NAME IN ('resource_type', 'text_content')
ORDER BY ORDINAL_POSITION;
