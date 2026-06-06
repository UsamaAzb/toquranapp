-- TQ5 My Deen Journey popup behavior category flag fix
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-popup-category-flag-fix.sql
--
-- Purpose:
--   Restore the Week14 quick-action contract for the first Slip / No Way cards:
--   `Oops!` and `Serious Matter` are category cards that open the behavior +
--   consequence agreement popup. They must not behave like instant point actions.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - updates only launch behavior template flags in reward tables;
--   - no history rows, drops, truncates, or deployment actions.

SET @tq5_mdj_popup_flag_confirm := 'TQ5_MDJ_POPUP_CATEGORY_FLAG_FIX_2026_06_06';

SET @tq5_mdj_popup_flag_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_popup_flag_confirm = 'TQ5_MDJ_POPUP_CATEGORY_FLAG_FIX_2026_06_06'
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
        WHEN @tq5_mdj_popup_flag_guard_ok = 1
            THEN 'TQ5 MDJ popup category flag guard passed.'
        ELSE 'REFUSING TQ5 MDJ popup category flag fix: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_popup_flag_guard;

UPDATE reward_discipline_transfer
SET teacher_desc = 1,
    updated_at = NOW()
WHERE @tq5_mdj_popup_flag_guard_ok = 1
  AND (
      (type = 'Slip' AND title = 'Oops!')
      OR (type = 'No Way' AND title = 'Serious Matter')
  )
  AND teacher_desc <> 1;

UPDATE reward_discipline_points
SET teacher_desc = 1,
    updated_at = NOW()
WHERE @tq5_mdj_popup_flag_guard_ok = 1
  AND (
      (type = 'Slip' AND title = 'Oops!')
      OR (type = 'No Way' AND title = 'Serious Matter')
  )
  AND teacher_desc <> 1;

SELECT
    'reward_discipline_transfer' AS table_name,
    title,
    type,
    teacher_desc,
    COUNT(*) AS row_count
FROM reward_discipline_transfer
WHERE title IN ('Oops!', 'Serious Matter')
GROUP BY title, type, teacher_desc
UNION ALL
SELECT
    'reward_discipline_points' AS table_name,
    title,
    type,
    teacher_desc,
    COUNT(*) AS row_count
FROM reward_discipline_points
WHERE title IN ('Oops!', 'Serious Matter')
GROUP BY title, type, teacher_desc;
