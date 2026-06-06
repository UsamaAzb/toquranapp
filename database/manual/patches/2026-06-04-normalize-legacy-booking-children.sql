-- To Quran app DB patch: normalize legacy booking-level child rows into booking_children.
-- Target: u504065335_to_quran (intentional app/LMS target for accelerated local launch path)
-- Backup evidence required before execution:
--   Focused restore backup of bookings and booking_children.
-- Notes:
--   This is a replayable equivalent of the local guarded normalization executed on 2026-06-04.
--   It only inserts a child row when a booking has child_name, child_age, and no booking_children rows.

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirm_tq_app_target := 'u504065335_to_quran';
SET @guard_ok := (
    SELECT DATABASE() = @expected_database
       AND @operator_confirm_tq_app_target = @expected_database
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'bookings'
       )
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'booking_children'
       )
);

SELECT IF(
    @guard_ok,
    'Applying legacy booking child normalization to To Quran app DB.',
    CONCAT('REFUSING legacy booking child normalization. Connected database is ', COALESCE(DATABASE(), '<none>'), '.')
) AS guard_result;

INSERT INTO booking_children (
    booking_id,
    child_name,
    child_age,
    child_grade,
    school_system,
    service_interests,
    consultation_status,
    workflow_status,
    meeting_disposition,
    meeting_disposition_reason,
    evaluation_status,
    evaluation_outcome,
    consultation_type,
    meeting_link,
    meeting_address,
    transfer_status,
    followup_date,
    current_school,
    student_id,
    notes,
    scheduled_date,
    scheduled_time,
    sort_order,
    updated_by,
    created_at,
    updated_at
)
SELECT
    b.id,
    TRIM(b.child_name),
    b.child_age,
    b.child_grade,
    COALESCE(NULLIF(TRIM(b.school_system), ''), 'Other'),
    CASE
        WHEN b.service_interest IS NULL OR TRIM(b.service_interest) = '' THEN JSON_ARRAY()
        ELSE JSON_ARRAY(TRIM(b.service_interest))
    END,
    CASE
        WHEN b.status IN ('pending', 'confirmed', 'followup', 'cancelled') THEN b.status
        ELSE NULL
    END,
    CASE
        WHEN b.status = 'confirmed' THEN 'confirmed'
        WHEN b.status = 'followup' THEN 'followup_required'
        WHEN b.status = 'cancelled' THEN 'cancelled'
        ELSE 'pending'
    END,
    NULL,
    NULL,
    CASE
        WHEN b.status = 'fit' THEN 'fit'
        WHEN b.status = 'unfit' THEN 'unfit'
        ELSE NULL
    END,
    CASE
        WHEN b.status = 'fit' THEN 'fit'
        WHEN b.status = 'unfit' THEN 'unfit'
        ELSE 'undecided'
    END,
    CASE
        WHEN b.consultation_type IN ('online', 'in-person') THEN b.consultation_type
        ELSE 'undecided'
    END,
    b.meeting_link,
    b.meeting_address,
    'not_transferred',
    b.follow_up_date,
    COALESCE(NULLIF(TRIM(b.current_school), ''), 'Not applicable'),
    b.student_id,
    NULL,
    b.consultation_date,
    b.consultation_time,
    1,
    NULL,
    NOW(),
    NOW()
FROM bookings b
WHERE @guard_ok
  AND b.child_name IS NOT NULL
  AND TRIM(b.child_name) <> ''
  AND b.child_age IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM booking_children bc
      WHERE bc.booking_id = b.id
  );

SELECT
    ROW_COUNT() AS inserted_booking_children,
    (
        SELECT COUNT(*)
        FROM bookings b
        WHERE b.child_name IS NOT NULL
          AND TRIM(b.child_name) <> ''
          AND b.child_age IS NOT NULL
          AND NOT EXISTS (
              SELECT 1
              FROM booking_children bc
              WHERE bc.booking_id = b.id
          )
    ) AS remaining_legacy_bookings_without_children;
