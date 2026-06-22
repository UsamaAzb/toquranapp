-- TQ9 demo child login email domain normalization
--
-- Purpose:
--   Normalize the intentional production demo child login emails from the
--   internal app subdomain domain to the public To Quran domain:
--     MA101@app.toquran.org -> MA101@toquran.org
--     OM101@app.toquran.org -> OM101@toquran.org
--     YU101@app.toquran.org -> YU101@toquran.org
--
-- Scope:
--   Updates only the existing TQDEMO-001 demo children and their linked user
--   login rows. It does not touch the parent account, bookings, smoke cleanup
--   artifacts, or any future transferred students.
--
-- Target:
--   u504065335_to_quran
--
-- Required before execution:
--   1. Confirm a fresh production DB backup/export exists.
--   2. Select the production app DB.
--   3. Set the confirmation variable in the same MySQL session:
--        SET @toquran_confirm_real_db_target := 'u504065335_to_quran';
--
-- Safety:
--   The update is inert unless the selected database and confirmation variable
--   match exactly, the three expected demo child user names exist, there are no
--   conflicting @toquran.org user emails, and the three matching student rows
--   are linked to the intentional demo parent email.
--
-- Rollback, if required:
--   Reverse the two UPDATE statements below by swapping @new_domain and
--   @old_domain, after taking a fresh backup and rechecking the same guards.

SET @expected_database := 'u504065335_to_quran';
SET @old_domain := 'app.toquran.org';
SET @new_domain := 'toquran.org';

SET @selected_database_ok := DATABASE() = @expected_database;
SET @confirmation_ok := COALESCE(@toquran_confirm_real_db_target, '') = @expected_database;

SET @demo_parent_ok := (
  SELECT COUNT(*)
  FROM parents
  WHERE email = 'osama.salem0217@gmail.com'
    AND first_name = 'Demo'
    AND last_name = 'Parent Amina'
);

SET @demo_users_old := (
  SELECT COUNT(*)
  FROM users
  WHERE name IN ('MA101', 'OM101', 'YU101')
    AND email IN ('MA101@app.toquran.org', 'OM101@app.toquran.org', 'YU101@app.toquran.org')
);

SET @demo_users_new := (
  SELECT COUNT(*)
  FROM users
  WHERE name IN ('MA101', 'OM101', 'YU101')
    AND email IN ('MA101@toquran.org', 'OM101@toquran.org', 'YU101@toquran.org')
);

SET @new_email_conflicts := (
  SELECT COUNT(*)
  FROM users
  WHERE email IN ('MA101@toquran.org', 'OM101@toquran.org', 'YU101@toquran.org')
    AND name NOT IN ('MA101', 'OM101', 'YU101')
);

SET @demo_students_old := (
  SELECT COUNT(*)
  FROM students s
  INNER JOIN parents p ON p.id = s.parent_id
  WHERE p.email = 'osama.salem0217@gmail.com'
    AND s.user_name IN ('MA101', 'OM101', 'YU101')
    AND s.student_email IN ('MA101@app.toquran.org', 'OM101@app.toquran.org', 'YU101@app.toquran.org')
);

SET @demo_students_new := (
  SELECT COUNT(*)
  FROM students s
  INNER JOIN parents p ON p.id = s.parent_id
  WHERE p.email = 'osama.salem0217@gmail.com'
    AND s.user_name IN ('MA101', 'OM101', 'YU101')
    AND s.student_email IN ('MA101@toquran.org', 'OM101@toquran.org', 'YU101@toquran.org')
);

SET @can_execute := (
  @selected_database_ok
  AND @confirmation_ok
  AND @demo_parent_ok = 1
  AND @new_email_conflicts = 0
  AND (
    (@demo_users_old = 3 AND @demo_students_old = 3)
    OR (@demo_users_new = 3 AND @demo_students_new = 3)
  )
);

SELECT
  DATABASE() AS selected_database,
  @expected_database AS expected_database,
  @confirmation_ok AS confirmation_ok,
  @demo_parent_ok AS demo_parent_ok,
  @demo_users_old AS demo_users_old,
  @demo_users_new AS demo_users_new,
  @demo_students_old AS demo_students_old,
  @demo_students_new AS demo_students_new,
  @new_email_conflicts AS new_email_conflicts,
  @can_execute AS can_execute;

START TRANSACTION;

UPDATE users
SET email = CONCAT(name, '@', @new_domain),
    updated_at = NOW()
WHERE @can_execute
  AND name IN ('MA101', 'OM101', 'YU101')
  AND email = CONCAT(name, '@', @old_domain);

UPDATE students s
INNER JOIN parents p ON p.id = s.parent_id
SET s.student_email = CONCAT(s.user_name, '@', @new_domain),
    s.updated_at = NOW()
WHERE @can_execute
  AND p.email = 'osama.salem0217@gmail.com'
  AND s.user_name IN ('MA101', 'OM101', 'YU101')
  AND s.student_email = CONCAT(s.user_name, '@', @old_domain);

COMMIT;

SELECT
  'post_normalization_users' AS check_name,
  COUNT(*) AS normalized_count
FROM users
WHERE name IN ('MA101', 'OM101', 'YU101')
  AND email IN ('MA101@toquran.org', 'OM101@toquran.org', 'YU101@toquran.org')
UNION ALL
SELECT
  'post_normalization_students' AS check_name,
  COUNT(*) AS normalized_count
FROM students s
INNER JOIN parents p ON p.id = s.parent_id
WHERE p.email = 'osama.salem0217@gmail.com'
  AND s.user_name IN ('MA101', 'OM101', 'YU101')
  AND s.student_email IN ('MA101@toquran.org', 'OM101@toquran.org', 'YU101@toquran.org');
