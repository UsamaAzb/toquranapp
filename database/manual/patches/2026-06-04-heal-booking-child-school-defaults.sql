-- To Quran app DB patch: silently default inherited Week14 school metadata on booking children.
-- Target: u504065335_to_quran (intentional app/LMS target for accelerated local launch path)
-- Backup evidence required before execution:
--   Focused restore backup of booking_children.

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirm_tq_app_target := 'u504065335_to_quran';
SET @guard_ok := (
    SELECT DATABASE() = @expected_database
       AND @operator_confirm_tq_app_target = @expected_database
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'booking_children'
       )
);

SELECT IF(
    @guard_ok,
    'Applying booking child school-default heal to To Quran app DB.',
    CONCAT('REFUSING booking child school-default heal. Connected database is ', COALESCE(DATABASE(), '<none>'), '.')
) AS guard_result;

UPDATE booking_children
SET school_system = 'Other',
    updated_at = NOW()
WHERE @guard_ok
  AND (school_system IS NULL OR TRIM(school_system) = '');

SET @school_system_rows := ROW_COUNT();

UPDATE booking_children
SET current_school = 'Not applicable',
    updated_at = NOW()
WHERE @guard_ok
  AND (current_school IS NULL OR TRIM(current_school) = '');

SET @current_school_rows := ROW_COUNT();

SELECT
    @school_system_rows AS school_system_rows_updated,
    @current_school_rows AS current_school_rows_updated,
    SUM(CASE WHEN school_system IS NULL OR TRIM(school_system) = '' THEN 1 ELSE 0 END) AS booking_children_missing_school_system,
    SUM(CASE WHEN current_school IS NULL OR TRIM(current_school) = '' THEN 1 ELSE 0 END) AS booking_children_missing_current_school
FROM booking_children;
