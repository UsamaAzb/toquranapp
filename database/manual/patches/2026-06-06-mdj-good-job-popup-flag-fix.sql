-- TQ5 My Deen Journey Good Job popup category flag fix
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-good-job-popup-flag-fix.sql
--
-- Purpose:
--   Restore the Week14 quick-action contract for the first Positive card:
--   `Good Job` is a category card that opens the behavior popup. It must not
--   behave like an instant +1 point action.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - updates only launch behavior template flags in reward tables;
--   - no history rows, drops, truncates, or deployment actions.

SET @tq5_mdj_good_job_popup_confirm := 'TQ5_MDJ_GOOD_JOB_POPUP_FLAG_FIX_2026_06_06';

SET @tq5_mdj_good_job_popup_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_good_job_popup_confirm = 'TQ5_MDJ_GOOD_JOB_POPUP_FLAG_FIX_2026_06_06'
    AND EXISTS (
        SELECT 1
        FROM subjects
        WHERE id = 15
          AND title = 'My Deen Journey'
          AND active = 1
          AND row_status = 'current'
    )
    AND EXISTS (
        SELECT 1
        FROM subjects
        WHERE id = 16
          AND title = 'Well Being'
          AND active = 1
          AND row_status = 'current'
    )
);

SELECT
    CASE
        WHEN @tq5_mdj_good_job_popup_guard_ok = 1
            THEN 'TQ5 MDJ Good Job popup category flag guard passed.'
        ELSE 'REFUSING TQ5 MDJ Good Job popup category flag fix: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_good_job_popup_guard;

UPDATE reward_discipline_transfer
SET teacher_desc = 1,
    updated_at = NOW()
WHERE @tq5_mdj_good_job_popup_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Good Job'
  AND teacher_desc <> 1;

UPDATE reward_discipline_points
SET teacher_desc = 1,
    updated_at = NOW()
WHERE @tq5_mdj_good_job_popup_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Good Job'
  AND teacher_desc <> 1;

SELECT
    'reward_discipline_transfer' AS table_name,
    title,
    type,
    teacher_desc,
    COUNT(*) AS row_count
FROM reward_discipline_transfer
WHERE title = 'Good Job'
GROUP BY title, type, teacher_desc
UNION ALL
SELECT
    'reward_discipline_points' AS table_name,
    title,
    type,
    teacher_desc,
    COUNT(*) AS row_count
FROM reward_discipline_points
WHERE title = 'Good Job'
GROUP BY title, type, teacher_desc;
