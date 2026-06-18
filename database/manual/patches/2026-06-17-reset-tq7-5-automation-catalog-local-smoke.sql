-- TQ7.5 automation starter catalog local smoke reset
-- Target: u504065335_to_quran
--
-- Purpose:
--   Remove the first local TQ7.5 starter catalog smoke copy for one selected
--   teacher so the revised catalog can be installed cleanly.
--
-- Safety:
--   - local reset only;
--   - guarded by database name, explicit operator variables, teacher id/email,
--     and existing registry rows;
--   - candidate rows are scoped through toquran_automation_catalog_entries;
--   - aborts if catalog rows have current assignments, assignment history,
--     generation states, generated class sessions, or generated session tasks;
--   - wraps catalog deletes in one transaction after all guards pass;
--   - deletes child rows before parent rows;
--   - deletes matching registry rows after catalog rows;
--   - does not delete non-catalog automation rows such as teacher 36's Salah Pro.
--
-- Before execution:
--   1. Create/confirm a focused backup/export for the intended To Quran app DB.
--   2. Confirm the selected database is intentionally u504065335_to_quran.
--   3. Set all three guard variables exactly for the local teacher target.
--
-- SET @toquran_confirm_real_db_target := 'u504065335_to_quran';
-- SET @toquran_reset_catalog_teacher_id := 36;
-- SET @toquran_reset_catalog_teacher_email := 'drosamaqandil@gmail.com';

DELIMITER //
DROP PROCEDURE IF EXISTS tq75_catalog_reset_guard_or_fail//
CREATE PROCEDURE tq75_catalog_reset_guard_or_fail()
BEGIN
    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: set @toquran_confirm_real_db_target first.';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: selected DB is not u504065335_to_quran.';
    END IF;

    IF COALESCE(@toquran_reset_catalog_teacher_id, 0) <> 36 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: unexpected teacher id.';
    END IF;

    IF COALESCE(@toquran_reset_catalog_teacher_email, '') COLLATE utf8mb4_unicode_ci <> 'drosamaqandil@gmail.com' COLLATE utf8mb4_unicode_ci THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: unexpected teacher email.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM users
        WHERE id = @toquran_reset_catalog_teacher_id
          AND email COLLATE utf8mb4_unicode_ci = COALESCE(@toquran_reset_catalog_teacher_email, '') COLLATE utf8mb4_unicode_ci
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: teacher id/email do not match.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'toquran_automation_catalog_entries'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: registry table is missing.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM toquran_automation_catalog_entries
        WHERE teacher_user_id = @toquran_reset_catalog_teacher_id
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: no registry rows found for teacher.';
    END IF;
END//
DELIMITER ;

CALL tq75_catalog_reset_guard_or_fail();
DROP PROCEDURE tq75_catalog_reset_guard_or_fail;

DROP TEMPORARY TABLE IF EXISTS tq75_reset_template_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_version_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_main_task_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_version_task_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_series_task_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_series_version_ids;
DROP TEMPORARY TABLE IF EXISTS tq75_reset_series_item_ids;

CREATE TEMPORARY TABLE tq75_reset_template_ids AS
SELECT DISTINCT target_id AS id
FROM toquran_automation_catalog_entries
WHERE teacher_user_id = @toquran_reset_catalog_teacher_id
  AND automation_type = 'versioned_routine'
  AND target_table = 'main_daily_session_templates';

CREATE TEMPORARY TABLE tq75_reset_version_ids AS
SELECT DISTINCT id
FROM main_daily_session_versions
WHERE main_daily_session_template_id IN (SELECT id FROM tq75_reset_template_ids);

CREATE TEMPORARY TABLE tq75_reset_main_task_ids AS
SELECT DISTINCT id
FROM main_daily_session_main_tasks
WHERE main_daily_session_template_id IN (SELECT id FROM tq75_reset_template_ids);

CREATE TEMPORARY TABLE tq75_reset_version_task_ids AS
SELECT DISTINCT id
FROM main_daily_session_version_tasks
WHERE version_id IN (SELECT id FROM tq75_reset_version_ids)
   OR main_task_id IN (SELECT id FROM tq75_reset_main_task_ids);

CREATE TEMPORARY TABLE tq75_reset_series_task_ids AS
SELECT DISTINCT target_id AS id
FROM toquran_automation_catalog_entries
WHERE teacher_user_id = @toquran_reset_catalog_teacher_id
  AND automation_type = 'series_task'
  AND target_table = 'series_tasks';

CREATE TEMPORARY TABLE tq75_reset_series_version_ids AS
SELECT DISTINCT id
FROM series_task_versions
WHERE series_task_id IN (SELECT id FROM tq75_reset_series_task_ids);

CREATE TEMPORARY TABLE tq75_reset_series_item_ids AS
SELECT DISTINCT id
FROM series_task_version_items
WHERE version_id IN (SELECT id FROM tq75_reset_series_version_ids);

DELIMITER //
DROP PROCEDURE IF EXISTS tq75_catalog_reset_dependency_guard_or_fail//
CREATE PROCEDURE tq75_catalog_reset_dependency_guard_or_fail()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM main_daily_session_student_assignments
        WHERE main_daily_session_template_id IN (SELECT id FROM tq75_reset_template_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: versioned routine assignments exist.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM main_daily_session_student_assignment_history
        WHERE main_daily_session_template_id IN (SELECT id FROM tq75_reset_template_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: versioned routine assignment history exists.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM class_sessions
        WHERE main_daily_session_template_id IN (SELECT id FROM tq75_reset_template_ids)
           OR series_task_id IN (SELECT id FROM tq75_reset_series_task_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: generated class sessions exist.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM session_tasks
        WHERE source_version_task_id_snapshot IN (SELECT id FROM tq75_reset_version_task_ids)
           OR source_series_task_id_snapshot IN (SELECT id FROM tq75_reset_series_task_ids)
           OR source_series_task_version_id_snapshot IN (SELECT id FROM tq75_reset_series_version_ids)
           OR source_series_task_version_item_id_snapshot IN (SELECT id FROM tq75_reset_series_item_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: generated session tasks exist.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM series_task_student_assignments
        WHERE series_task_id IN (SELECT id FROM tq75_reset_series_task_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: series assignments exist.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM series_task_student_assignment_history
        WHERE series_task_id IN (SELECT id FROM tq75_reset_series_task_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: series assignment history exists.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM series_task_student_generation_states
        WHERE series_task_id IN (SELECT id FROM tq75_reset_series_task_ids)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog reset: series generation states exist.';
    END IF;
END//
DELIMITER ;

CALL tq75_catalog_reset_dependency_guard_or_fail();
DROP PROCEDURE tq75_catalog_reset_dependency_guard_or_fail;

SELECT 'before' AS phase, 'registry_rows' AS metric, COUNT(*) AS row_count
FROM toquran_automation_catalog_entries
WHERE teacher_user_id = @toquran_reset_catalog_teacher_id
UNION ALL
SELECT 'before', 'versioned_routine_roots', COUNT(*) FROM tq75_reset_template_ids
UNION ALL
SELECT 'before', 'versioned_routine_versions', COUNT(*) FROM tq75_reset_version_ids
UNION ALL
SELECT 'before', 'versioned_routine_tasks', COUNT(*) FROM tq75_reset_main_task_ids
UNION ALL
SELECT 'before', 'versioned_routine_version_tasks', COUNT(*) FROM tq75_reset_version_task_ids
UNION ALL
SELECT 'before', 'series_roots', COUNT(*) FROM tq75_reset_series_task_ids
UNION ALL
SELECT 'before', 'series_versions', COUNT(*) FROM tq75_reset_series_version_ids
UNION ALL
SELECT 'before', 'series_items', COUNT(*) FROM tq75_reset_series_item_ids;

START TRANSACTION;

DELETE FROM main_daily_session_main_task_attachments
WHERE main_task_id IN (SELECT id FROM tq75_reset_main_task_ids);

DELETE FROM main_daily_session_version_tasks
WHERE id IN (SELECT id FROM tq75_reset_version_task_ids);

DELETE FROM main_daily_session_main_tasks
WHERE id IN (SELECT id FROM tq75_reset_main_task_ids);

DELETE FROM main_daily_session_versions
WHERE id IN (SELECT id FROM tq75_reset_version_ids);

DELETE FROM main_daily_session_templates
WHERE id IN (SELECT id FROM tq75_reset_template_ids);

DELETE FROM series_task_version_items
WHERE id IN (SELECT id FROM tq75_reset_series_item_ids);

DELETE FROM series_task_versions
WHERE id IN (SELECT id FROM tq75_reset_series_version_ids);

DELETE FROM series_tasks
WHERE id IN (SELECT id FROM tq75_reset_series_task_ids);

DELETE FROM toquran_automation_catalog_entries
WHERE teacher_user_id = @toquran_reset_catalog_teacher_id;

COMMIT;

SELECT 'after' AS phase, 'registry_rows' AS metric, COUNT(*) AS row_count
FROM toquran_automation_catalog_entries
WHERE teacher_user_id = @toquran_reset_catalog_teacher_id
UNION ALL
SELECT 'after', 'catalog_templates_remaining', COUNT(*)
FROM main_daily_session_templates
WHERE id IN (SELECT id FROM tq75_reset_template_ids)
UNION ALL
SELECT 'after', 'non_catalog_salah_pro_remaining', COUNT(*)
FROM main_daily_session_templates
WHERE created_by_user_id = @toquran_reset_catalog_teacher_id
  AND title = 'Salah Pro';
