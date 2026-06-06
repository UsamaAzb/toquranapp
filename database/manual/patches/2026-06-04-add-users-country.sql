-- To Quran app DB patch: add first-class country to all app users.
-- Target: u504065335_to_quran (intentional app/LMS target for accelerated local launch path)
-- Backup evidence required before execution:
--   database/manual/backups/2026-06-04-u504065335_to_quran-before-users-country-structure.sql

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirm_tq_app_target := 'u504065335_to_quran';
SET @guard_ok := (
    SELECT DATABASE() = @expected_database
       AND @operator_confirm_tq_app_target = @expected_database
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'users'
       )
);

SELECT IF(
    @guard_ok,
    'Applying users.country patch to To Quran app DB.',
    CONCAT('REFUSING users.country patch. Connected database is ', COALESCE(DATABASE(), '<none>'), '.')
) AS guard_result;

SET @add_country_sql := IF(
    @guard_ok AND NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'country'
    ),
    "ALTER TABLE users ADD COLUMN country VARCHAR(100) NULL AFTER phone",
    "SELECT 'users.country already exists or guard failed.' AS skipped"
);

PREPARE add_users_country FROM @add_country_sql;
EXECUTE add_users_country;
DEALLOCATE PREPARE add_users_country;

UPDATE users
SET country = 'Egypt'
WHERE @guard_ok
  AND (country IS NULL OR TRIM(country) = '');

SELECT
    COUNT(*) AS total_users,
    SUM(CASE WHEN country IS NULL OR TRIM(country) = '' THEN 1 ELSE 0 END) AS users_missing_country
FROM users;
