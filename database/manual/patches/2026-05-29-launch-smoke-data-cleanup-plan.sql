-- To Quran launch smoke data cleanup plan
-- Date: 2026-05-29
-- Target DB: u504065335_to_quran
-- Status: plan only; do not execute until deployment cleanup.
--
-- Purpose:
-- Remove local smoke/demo users, family, class, and transferred intake records created by:
-- php artisan toquran:bootstrap-smoke-data --confirm-db=u504065335_to_quran
--
-- Scope:
-- - only rows identified by @toquran-smoke.test emails, [SMOKE] class/notes, or SMOKE-TQ-0001 reference
-- - does not remove starter/reference rows
-- - does not remove the real superadmin account
--
-- Execution guard:
-- SET @toquran_confirm_real_db_target = 'u504065335_to_quran';
-- SET @toquran_confirm_smoke_cleanup = 'DELETE_TOQURAN_SMOKE_DATA';

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_cleanup_smoke_data`$$
CREATE PROCEDURE `_toquran_cleanup_smoke_data`()
BEGIN
    IF @toquran_confirm_real_db_target <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target before smoke cleanup';
    END IF;

    IF @toquran_confirm_smoke_cleanup <> 'DELETE_TOQURAN_SMOKE_DATA' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_smoke_cleanup before smoke cleanup';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: smoke cleanup must run only against u504065335_to_quran';
    END IF;
END$$

CALL `_toquran_cleanup_smoke_data`()$$

DELIMITER ;

START TRANSACTION;

CREATE TEMPORARY TABLE `_toquran_smoke_users` AS
SELECT `id`
FROM `users`
WHERE `email` LIKE '%@toquran-smoke.test';

CREATE TEMPORARY TABLE `_toquran_smoke_parents` AS
SELECT `id`
FROM `parents`
WHERE `email` LIKE '%@toquran-smoke.test'
   OR `user_id` IN (SELECT `id` FROM `_toquran_smoke_users`);

CREATE TEMPORARY TABLE `_toquran_smoke_students` AS
SELECT `id`
FROM `students`
WHERE `student_email` LIKE '%@toquran-smoke.test'
   OR `user_id` IN (SELECT `id` FROM `_toquran_smoke_users`)
   OR `parent_id` IN (SELECT `id` FROM `_toquran_smoke_parents`);

CREATE TEMPORARY TABLE `_toquran_smoke_classes` AS
SELECT `id`
FROM `classes`
WHERE `title` LIKE '[SMOKE]%';

CREATE TEMPORARY TABLE `_toquran_smoke_bookings` AS
SELECT `id`
FROM `bookings`
WHERE `booking_reference` = 'SMOKE-TQ-0001'
   OR `parent_id` IN (SELECT `id` FROM `_toquran_smoke_parents`)
   OR `student_id` IN (SELECT `id` FROM `_toquran_smoke_students`)
   OR `notes` LIKE '%[SMOKE]%';

CREATE TEMPORARY TABLE `_toquran_smoke_booking_children` AS
SELECT `id`
FROM `booking_children`
WHERE `booking_id` IN (SELECT `id` FROM `_toquran_smoke_bookings`)
   OR `student_id` IN (SELECT `id` FROM `_toquran_smoke_students`)
   OR `notes` LIKE '%[SMOKE]%';

DELETE FROM `booking_child_audit_log`
WHERE `booking_child_id` IN (SELECT `id` FROM `_toquran_smoke_booking_children`);

DELETE FROM `booking_child_emails`
WHERE `booking_child_id` IN (SELECT `id` FROM `_toquran_smoke_booking_children`);

DELETE FROM `booking_children`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_booking_children`);

DELETE FROM `booking_intake_review`
WHERE `booking_id` IN (SELECT `id` FROM `_toquran_smoke_bookings`);

DELETE FROM `bookings`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_bookings`);

DELETE FROM `account_histories`
WHERE `parent_id` IN (SELECT `id` FROM `_toquran_smoke_parents`)
   OR `student_id` IN (SELECT `id` FROM `_toquran_smoke_students`);

DELETE FROM `students_subjects`
WHERE `student_id` IN (SELECT `id` FROM `_toquran_smoke_students`);

DELETE FROM `student_classes_history`
WHERE `student_id` IN (SELECT `id` FROM `_toquran_smoke_students`)
   OR `class_id` IN (SELECT `id` FROM `_toquran_smoke_classes`);

DELETE FROM `teacher_subject_classes`
WHERE `user_teacher_coteacher_id` IN (SELECT `id` FROM `_toquran_smoke_users`)
   OR `class_id` IN (SELECT `id` FROM `_toquran_smoke_classes`);

DELETE FROM `class_subjects`
WHERE `class_id` IN (SELECT `id` FROM `_toquran_smoke_classes`);

UPDATE `students`
SET `current_class_id` = NULL
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_students`);

DELETE FROM `classes`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_classes`);

DELETE FROM `students`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_students`);

DELETE FROM `parents`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_parents`);

DELETE FROM `sessions`
WHERE `user_id` IN (SELECT `id` FROM `_toquran_smoke_users`);

DELETE FROM `password_reset_tokens`
WHERE `email` LIKE '%@toquran-smoke.test';

DELETE FROM `personal_access_tokens`
WHERE `tokenable_type` = 'App\\Models\\User'
  AND `tokenable_id` IN (SELECT `id` FROM `_toquran_smoke_users`);

DELETE FROM `model_has_roles`
WHERE `model_type` = 'App\\Models\\User'
  AND `model_id` IN (SELECT `id` FROM `_toquran_smoke_users`);

DELETE FROM `users`
WHERE `id` IN (SELECT `id` FROM `_toquran_smoke_users`);

COMMIT;

DROP PROCEDURE IF EXISTS `_toquran_cleanup_smoke_data`;
