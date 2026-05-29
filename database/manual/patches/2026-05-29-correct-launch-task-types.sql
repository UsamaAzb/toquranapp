-- To Quran launch task-type correction
-- Date: 2026-05-29
-- Target DB: u504065335_to_quran
-- Backup: database/manual/backups/2026-05-29-173241-u504065335_to_quran-before-task-type-correction.sql
-- Purpose: correct the initial launch task-type rows to the teacher-facing Week14 task categories.

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_correct_launch_task_types`$$

CREATE PROCEDURE `_toquran_correct_launch_task_types`()
BEGIN
    DECLARE table_count INT DEFAULT 0;
    DECLARE stale_task_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running launch task type correction';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran launch task type correction';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: app schema baseline is not present';
    END IF;

    SELECT COUNT(*)
      INTO stale_task_count
      FROM `session_tasks`
     WHERE `task_type_id` IN (1, 8, 9);

    IF stale_task_count > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: existing session tasks use stale launch task type IDs 1, 8, or 9; map them before correcting task_types';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `task_types`
         WHERE (`id` = 2 AND `title` NOT IN ('Quiz'))
            OR (`id` = 3 AND `title` NOT IN ('Lesson'))
            OR (`id` = 4 AND `title` NOT IN ('Project'))
            OR (`id` = 7 AND `title` NOT IN ('Assignment', 'Assignments', 'File'))
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Task type drift detected: canonical launch task type IDs already contain unexpected meanings';
    END IF;

    DELETE FROM `task_types`
     WHERE (`id` = 1 AND `title` = 'Activity')
        OR (`id` = 8 AND `title` IN ('YouTube', 'Youtube'))
        OR (`id` = 9 AND `title` = 'Link');

    INSERT INTO `task_types` (`id`, `title`, `table_name`, `default_points`, `max_points`)
    VALUES
        (2, 'Quiz', 'teacher_classes_quizzes_and_exams', NULL, NULL),
        (3, 'Lesson', 'teacher_classes_lessons', NULL, NULL),
        (4, 'Project', 'teacher_classes_projects', NULL, NULL),
        (7, 'Assignment', 'teacher_classes_assignments', NULL, NULL)
    ON DUPLICATE KEY UPDATE
        `title` = VALUES(`title`),
        `table_name` = VALUES(`table_name`),
        `default_points` = VALUES(`default_points`),
        `max_points` = VALUES(`max_points`);
END$$

CALL `_toquran_correct_launch_task_types`()$$

DROP PROCEDURE IF EXISTS `_toquran_correct_launch_task_types`$$

DELIMITER ;
