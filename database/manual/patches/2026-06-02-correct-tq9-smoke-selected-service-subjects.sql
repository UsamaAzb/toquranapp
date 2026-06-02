-- To Quran TQ9 smoke selected-service subject correction
-- Date: 2026-06-02
-- Target DB: u504065335_to_quran
-- Status: executed locally by Codex on 2026-06-02.
--
-- Purpose:
-- Correct transferred TQ9 smoke rows created before BookingTransferService passed
-- child service_interests into BookingSubjectProvisioning. This updates only
-- transferred @toquran-smoke.test children whose own service_interests selected
-- Arabic Language or Sanad Ijazah.
--
-- Before-backup/evidence:
-- database/manual/backups/2026-06-02-205300-u504065335_to_quran-before-tq9-smoke-selected-subject-correction.sql

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_correct_tq9_smoke_selected_service_subjects`$$

CREATE PROCEDURE `_toquran_correct_tq9_smoke_selected_service_subjects`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = ''u504065335_to_quran'' before running this patch.';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for TQ9 smoke selected-service subject correction.';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: expected populated To Quran app schema before correcting TQ9 smoke subject rows.';
    END IF;

    START TRANSACTION;

    UPDATE `students_subjects` ss
    JOIN `booking_children` bc ON bc.`student_id` = ss.`student_id`
    JOIN `bookings` b ON b.`id` = bc.`booking_id`
    JOIN `grade_level_subjects` gls ON gls.`id` = ss.`grade_level_subject_id`
       SET ss.`status` = 'active',
           ss.`enrolled_at` = COALESCE(ss.`enrolled_at`, CURDATE())
     WHERE b.`parent_email` LIKE '%@toquran-smoke.test'
       AND bc.`transfer_status` = 'transferred'
       AND gls.`subject_id` = 3
       AND bc.`service_interests` LIKE '%"Arabic Language"%';

    UPDATE `students_subjects` ss
    JOIN `booking_children` bc ON bc.`student_id` = ss.`student_id`
    JOIN `bookings` b ON b.`id` = bc.`booking_id`
    JOIN `grade_level_subjects` gls ON gls.`id` = ss.`grade_level_subject_id`
       SET ss.`status` = 'active',
           ss.`enrolled_at` = COALESCE(ss.`enrolled_at`, CURDATE())
     WHERE b.`parent_email` LIKE '%@toquran-smoke.test'
       AND bc.`transfer_status` = 'transferred'
       AND gls.`subject_id` = 4
       AND (
            bc.`service_interests` LIKE '%"Sanad Ijazah"%'
         OR bc.`service_interests` LIKE '%"Sanad Ijazah Program"%'
       );

    UPDATE `teacher_subject_classes` tsc
    JOIN `students_subjects` ss ON ss.`class_subject_id` = tsc.`class_subject_id`
    JOIN `booking_children` bc ON bc.`student_id` = ss.`student_id`
    JOIN `bookings` b ON b.`id` = bc.`booking_id`
    JOIN `grade_level_subjects` gls ON gls.`id` = ss.`grade_level_subject_id`
       SET tsc.`status` = 'active',
           tsc.`assigned_at` = COALESCE(tsc.`assigned_at`, CURDATE()),
           tsc.`removed_at` = NULL,
           tsc.`updated_at` = NOW()
     WHERE b.`parent_email` LIKE '%@toquran-smoke.test'
       AND bc.`transfer_status` = 'transferred'
       AND ss.`status` = 'active'
       AND (
            (gls.`subject_id` = 3 AND bc.`service_interests` LIKE '%"Arabic Language"%')
         OR (gls.`subject_id` = 4 AND (
                bc.`service_interests` LIKE '%"Sanad Ijazah"%'
             OR bc.`service_interests` LIKE '%"Sanad Ijazah Program"%'
            ))
       );

    COMMIT;
END$$

CALL `_toquran_correct_tq9_smoke_selected_service_subjects`()$$

DROP PROCEDURE IF EXISTS `_toquran_correct_tq9_smoke_selected_service_subjects`$$

DELIMITER ;
