-- TQ5 My Deen Journey starter behavior/accountability reference data
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-03-140853-u504065335_to_quran-before-tq5-mdj-starter-data.sql
--
-- Purpose:
--   Seed launch behavior templates and consequence suggestion text used by the
--   inherited Week14 Points Lab / parent quick action flow. Agreements remain
--   meeting-defined family decisions; this patch does not create a new
--   parent-facing agreement workflow.
--
-- Safety:
--   - uses a read-only guard before any insert;
--   - guarded to the real To Quran app DB name;
--   - verifies subject 15/16 identity before inserting;
--   - insert-only / idempotent by title/type checks;
--   - no destructive cleanup.

SET @tq5_mdj_starter_data_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
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
        WHEN @tq5_mdj_starter_data_guard_ok = 1
            THEN 'TQ5 starter data guard passed.'
        ELSE 'REFUSING TQ5 starter data patch: wrong target DB or subject 15/16 identity mismatch. Inserts are gated off.'
    END AS tq5_mdj_starter_data_guard;

INSERT INTO punishment_types (title, decrease_point, active)
SELECT 'Minor Slip', 2, 1
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM punishment_types WHERE title = 'Minor Slip'
);

INSERT INTO punishment_types (title, decrease_point, active)
SELECT 'Serious Action', 5, 1
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM punishment_types WHERE title = 'Serious Action'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Completed Quran or Salah task with care', 'active', 5,
       'Positive family/accountability point for completing an agreed worship or Quran task with care.',
       'Positive', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Completed Quran or Salah task with care' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Showed good adab', 'active', 4,
       'Positive family/accountability point for manners, respect, or calm speech.',
       'Positive', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Showed good adab' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Helped at home without being asked', 'active', 3,
       'Positive family/accountability point for responsibility and helpfulness at home.',
       'Positive', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Helped at home without being asked' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Spoke truthfully and calmly', 'active', 3,
       'Positive family/accountability point for honesty, self-control, and calm repair.',
       'Positive', NULL, NULL, 40, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Spoke truthfully and calmly' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Kept an agreed device boundary', 'active', 3,
       'Positive family/accountability point for respecting an agreed device or screen-time boundary.',
       'Positive', NULL, NULL, 50, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Kept an agreed device boundary' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Missed an agreed routine after reminder', 'active', 2,
       'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.',
       'Slip', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Missed an agreed routine after reminder' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Delayed a task without a clear reason', 'active', 2,
       'Minor slip for delaying an agreed task when the child could reasonably do it.',
       'Slip', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Delayed a task without a clear reason' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Spoke disrespectfully', 'active', 3,
       'Minor slip for disrespectful words, tone, or avoidable arguing.',
       'Slip', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Spoke disrespectfully' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Left a personal area untidy', 'active', 1,
       'Minor slip for leaving an agreed personal/home responsibility incomplete.',
       'Slip', NULL, NULL, 40, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Left a personal area untidy' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Refused an agreed family instruction', 'active', 5,
       'Red-flag accountability point for refusing a clear, age-appropriate family instruction.',
       'No Way', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Refused an agreed family instruction' AND type = 'No Way'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Used hurtful speech or behavior', 'active', 5,
       'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.',
       'No Way', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Used hurtful speech or behavior' AND type = 'No Way'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Broke a device or safety boundary', 'active', 6,
       'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.',
       'No Way', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Broke a device or safety boundary' AND type = 'No Way'
);

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Use the meeting-defined consequence for this minor slip.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Use the meeting-defined consequence for this minor slip.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Short reflection and repair action agreed in the family meeting.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Short reflection and repair action agreed in the family meeting.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Repeat or repair the missed routine with parent support.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Repeat or repair the missed routine with parent support.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Use the meeting-defined consequence for this serious action.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Use the meeting-defined consequence for this serious action.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Pause an agreed privilege according to the family meeting decision.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Pause an agreed privilege according to the family meeting decision.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Repair the harm and discuss the next step with a parent.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Repair the harm and discuss the next step with a parent.'
  );

SELECT 'punishment_types' AS table_name, COUNT(*) AS rows_count FROM punishment_types
UNION ALL SELECT 'punishments_suggestions', COUNT(*) FROM punishments_suggestions
UNION ALL SELECT 'reward_discipline_transfer', COUNT(*) FROM reward_discipline_transfer;
