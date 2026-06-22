-- TQ9 production smoke booking/transfer cleanup
--
-- Purpose:
--   Remove only the launch/test smoke booking rows and smoke transferred
--   family created during TQ9 production verification. Preserve the intentional
--   demo family:
--     parent:  Demo Parent Amina / osama.salem0217@gmail.com / parent id 1
--     students: Yusuf, Maryam, Omar / student ids 1, 2, 3
--
-- Target:
--   u504065335_to_quran
--
-- Backup evidence:
--   database/manual/backups/2026-06-22-u504065335_to_quran-before-tq9-smoke-cleanup.sql
--
-- Production raw backup:
--   /home/u504065335/domains/toquran.org/public_html/appdashboard/_deploy_backups/20260622-144851-before-smoke-cleanup/u504065335_to_quran-full-before-smoke-cleanup.sql.gz
--
-- Owner request:
--   Remove smoke accounts from transfer and booking, leaving only the demo
--   family created for launch demonstration.
--
-- IMPORTANT:
--   This patch is intentionally guarded. It deletes nothing unless the selected
--   DB is exactly u504065335_to_quran, the demo family still exists, the smoke
--   rows match their expected names/references, and the operator confirmation
--   string below is set exactly.

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirmation := 'DELETE_TQ9_SMOKE_ONLY_2026_06_22';

SET @demo_parent_ok := (
  SELECT COUNT(*)
  FROM parents
  WHERE id = 1
    AND email = 'osama.salem0217@gmail.com'
);

SET @demo_students_ok := (
  SELECT COUNT(*)
  FROM students
  WHERE parent_id = 1
    AND id IN (1, 2, 3)
    AND first_name IN ('Yusuf', 'Maryam', 'Omar')
);

SET @smoke_parent_ok := (
  SELECT COUNT(*)
  FROM parents
  WHERE id = 2
    AND email = 'usama.mastery@gmail.com'
    AND first_name = 'Codex'
    AND last_name = 'Smoke Parent'
);

SET @smoke_students_ok := (
  SELECT COUNT(*)
  FROM students
  WHERE parent_id = 2
    AND id IN (4, 5)
    AND first_name IN ('Smoke Islamic Studies Child', 'Smoke Quran Literature Child')
);

SET @smoke_booking_ok := (
  SELECT COUNT(*)
  FROM bookings
  WHERE id IN (1, 3, 4, 5)
    AND (
      booking_reference IN ('TQ-CB21FTHXGU', 'TQ-GVX4TIAHXA', 'TQ-WNPCSXYRRX')
      OR parent_name = 'Codex Smoke Parent'
    )
);

SET @can_execute := (
  DATABASE() = @expected_database
  AND @operator_confirmation = 'DELETE_TQ9_SMOKE_ONLY_2026_06_22'
  AND @demo_parent_ok = 1
  AND @demo_students_ok = 3
  AND @smoke_parent_ok = 1
  AND @smoke_students_ok = 2
  AND @smoke_booking_ok = 4
);

SELECT
  DATABASE() AS selected_database,
  @expected_database AS expected_database,
  @demo_parent_ok AS demo_parent_ok,
  @demo_students_ok AS demo_students_ok,
  @smoke_parent_ok AS smoke_parent_ok,
  @smoke_students_ok AS smoke_students_ok,
  @smoke_booking_ok AS smoke_booking_ok,
  @can_execute AS can_execute;

START TRANSACTION;

DELETE FROM booking_child_audit_log
WHERE @can_execute
  AND booking_child_id IN (1, 5, 6, 7, 8, 9);

DELETE FROM booking_child_emails
WHERE @can_execute
  AND booking_child_id IN (1, 5, 6, 7, 8, 9);

DELETE FROM booking_parent_identity_resolutions
WHERE @can_execute
  AND (
    booking_id IN (1, 3, 4, 5)
    OR matched_booking_id IN (1, 3, 4, 5)
    OR booking_child_id IN (1, 5, 6, 7, 8, 9)
    OR booking_intake_review_id = 1
    OR booking_intake_review_child_id IN (1, 2)
  );

DELETE FROM booking_intake_review_children
WHERE @can_execute
  AND (id IN (1, 2) OR booking_intake_review_id = 1);

DELETE FROM booking_intake_review
WHERE @can_execute
  AND (id = 1 OR resulting_booking_id = 5);

DELETE FROM booking_children
WHERE @can_execute
  AND (id IN (1, 5, 6, 7, 8, 9) OR booking_id IN (1, 3, 4, 5));

DELETE FROM bookings
WHERE @can_execute
  AND id IN (1, 3, 4, 5);

DELETE FROM account_histories
WHERE @can_execute
  AND parent_id = 2;

DELETE FROM punishment_agreements
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM reward_discipline_points
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM reward_points_ledger
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM reward_totals
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM student_gift_points_history
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM student_gifts
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM students_subjects
WHERE @can_execute
  AND student_id IN (4, 5);

DELETE FROM student_classes_history
WHERE @can_execute
  AND (student_id IN (4, 5) OR class_id IN (6, 7));

DELETE FROM task_pin_hashes
WHERE @can_execute
  AND user_id IN (8, 9, 10);

DELETE FROM reward_pin_hashes
WHERE @can_execute
  AND user_id IN (8, 9, 10);

DELETE FROM model_has_roles
WHERE @can_execute
  AND model_id IN (8, 9, 10)
  AND model_type LIKE '%User%';

DELETE FROM teacher_subject_classes
WHERE @can_execute
  AND class_id IN (6, 7);

DELETE FROM class_subjects
WHERE @can_execute
  AND class_id IN (6, 7);

DELETE FROM students
WHERE @can_execute
  AND id IN (4, 5)
  AND parent_id = 2;

DELETE FROM classes
WHERE @can_execute
  AND id IN (6, 7)
  AND title IN (
    'Smoke Islamic Studies Child - Beginner',
    'Smoke Quran Literature Child - Beginner'
  );

DELETE FROM parents
WHERE @can_execute
  AND id = 2
  AND email = 'usama.mastery@gmail.com';

DELETE FROM users
WHERE @can_execute
  AND id IN (8, 9, 10)
  AND email IN (
    'usama.mastery@gmail.com',
    'SM101@app.toquran.org',
    'SM102@app.toquran.org'
  );

COMMIT;

SELECT
  'remaining_smoke_bookings' AS check_name,
  COUNT(*) AS remaining_count
FROM bookings
WHERE id IN (1, 3, 4, 5)
   OR booking_reference IN ('TQ-CB21FTHXGU', 'TQ-GVX4TIAHXA', 'TQ-WNPCSXYRRX')
   OR parent_name LIKE '%Smoke%'
   OR parent_email LIKE '%smoke%'
   OR parent_email IN ('rrrrrr@gmail.com', 'usama.mastery@gmail.com', 'osama.mastery@gmail.com')

UNION ALL

SELECT
  'remaining_smoke_booking_children',
  COUNT(*)
FROM booking_children
WHERE id IN (1, 5, 6, 7, 8, 9)
   OR booking_id IN (1, 3, 4, 5)
   OR child_name LIKE '%Smoke%'
   OR child_name = 'Amal'

UNION ALL

SELECT
  'remaining_smoke_intake_reviews',
  COUNT(*)
FROM booking_intake_review
WHERE id = 1
   OR resulting_booking_id = 5
   OR parent_name LIKE '%Smoke%'
   OR parent_email LIKE '%smoke%'
   OR parent_email IN ('rrrrrr@gmail.com', 'usama.mastery@gmail.com', 'osama.mastery@gmail.com')
   OR notes LIKE '%TQ-KFBXWHDTH9%'
   OR notes LIKE '%TQ-WNPCSXYRRX%'
   OR notes LIKE '%TQ-CB21FTHXGU%'
   OR notes LIKE '%TQ-GVX4TIAHXA%'

UNION ALL

SELECT
  'remaining_smoke_parent',
  COUNT(*)
FROM parents
WHERE id = 2
   OR email IN ('usama.mastery@gmail.com', 'osama.mastery@gmail.com', 'rrrrrr@gmail.com')
   OR first_name LIKE '%Smoke%'
   OR last_name LIKE '%Smoke%'

UNION ALL

SELECT
  'remaining_smoke_students',
  COUNT(*)
FROM students
WHERE id IN (4, 5)
   OR parent_id = 2
   OR first_name LIKE '%Smoke%'
   OR first_name = 'Amal'

UNION ALL

SELECT
  'demo_parent',
  COUNT(*)
FROM parents
WHERE id = 1
  AND email = 'osama.salem0217@gmail.com'

UNION ALL

SELECT
  'demo_students',
  COUNT(*)
FROM students
WHERE parent_id = 1
  AND id IN (1, 2, 3)
  AND first_name IN ('Yusuf', 'Maryam', 'Omar');
