-- To Quran launch task-type reference data
-- Date: 2026-05-29
-- Target DB: u504065335_to_quran
-- Backup: database/manual/backups/2026-05-29-172119-u504065335_to_quran-before-task-types.sql
-- Purpose: restore the minimal teacher-facing task type rows needed by the reused Week14 teacher task modals for TQ4 launch smoke.

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_add_launch_task_types`$$

CREATE PROCEDURE `_toquran_add_launch_task_types`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running launch task types patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran launch task types patch';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: app schema baseline is not present';
    END IF;

    IF EXISTS (
        SELECT 1
          FROM `task_types`
         WHERE (`id` = 2 AND `title` <> 'Quiz')
            OR (`id` = 3 AND `title` <> 'Lesson')
            OR (`id` = 4 AND `title` <> 'Project')
            OR (`id` = 7 AND `title` <> 'Assignment')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Task type drift detected: launch task type IDs already contain different meanings';
    END IF;

    INSERT INTO `task_types` (`id`, `title`, `table_name`, `default_points`, `max_points`)
    SELECT task_type_id, task_type_title, task_table_name, task_default_points, task_max_points
      FROM (
        SELECT 2 AS task_type_id, 'Quiz' AS task_type_title, 'teacher_classes_quizzes_and_exams' AS task_table_name, NULL AS task_default_points, NULL AS task_max_points UNION ALL
        SELECT 3, 'Lesson', 'teacher_classes_lessons', NULL, NULL UNION ALL
        SELECT 4, 'Project', 'teacher_classes_projects', NULL, NULL UNION ALL
        SELECT 7, 'Assignment', 'teacher_classes_assignments', NULL, NULL
      ) AS launch_task_types
     WHERE NOT EXISTS (
        SELECT 1
          FROM `task_types`
         WHERE `task_types`.`id` = launch_task_types.task_type_id
     );
END$$

CALL `_toquran_add_launch_task_types`()$$

DROP PROCEDURE IF EXISTS `_toquran_add_launch_task_types`$$

DELIMITER ;
