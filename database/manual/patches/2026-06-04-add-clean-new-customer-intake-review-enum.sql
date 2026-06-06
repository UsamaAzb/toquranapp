-- To Quran app DB patch: allow intake review summaries to represent an admin-corrected clean new customer.
-- Target: u504065335_to_quran (intentional app/LMS target for accelerated local launch path)
-- Backup evidence required before execution:
--   database/manual/backups/2026-06-04-u504065335_to_quran-before-intake-review-clean-new-customer-enum-structure.sql

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirm_tq_app_target := 'u504065335_to_quran';
SET @guard_ok := (
    SELECT DATABASE() = @expected_database
       AND @operator_confirm_tq_app_target = @expected_database
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'booking_intake_review'
       )
);

SELECT IF(
    @guard_ok,
    'Applying intake review clean_new_customer enum patch to To Quran app DB.',
    CONCAT('REFUSING intake review enum patch. Connected database is ', COALESCE(DATABASE(), '<none>'), '.')
) AS guard_result;

SET @alter_sql := IF(
    @guard_ok,
    "ALTER TABLE booking_intake_review
        MODIFY detection_reason ENUM(
            'duplicate_child',
            'repeat_submission',
            'blocked_parent',
            'existing_family_new_child',
            'mixed_children',
            'suspected_contact_mismatch',
            'clean_new_customer'
        ) NOT NULL",
    "SELECT 'No schema change applied because guard failed.' AS skipped"
);

PREPARE intake_review_enum_patch FROM @alter_sql;
EXECUTE intake_review_enum_patch;
DEALLOCATE PREPARE intake_review_enum_patch;

SELECT COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'booking_intake_review'
  AND COLUMN_NAME = 'detection_reason';
