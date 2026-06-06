-- TQ5 My Deen Journey behavior/consequence wording refresh
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-wording-refresh.sql
--
-- Purpose:
--   Refresh current launch smoke/reference behavior titles and consequence
--   agreements so student-visible accountability text is short, practical,
--   and To Quran-specific. This updates existing starter rows and copied
--   per-student rows; it does not add finance, scheduling, or a new agreement
--   workflow. Family agreements remain meeting-defined.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - update-only / idempotent by old-title and old-suggestion checks;
--   - no deletes, drops, truncates, or cleanup.

SET @tq5_mdj_wording_refresh_confirm := 'TQ5_MDJ_WORDING_REFRESH_2026_06_05';

SET @tq5_mdj_wording_refresh_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_wording_refresh_confirm = 'TQ5_MDJ_WORDING_REFRESH_2026_06_05'
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
        WHEN @tq5_mdj_wording_refresh_guard_ok = 1
            THEN 'TQ5 MDJ wording refresh guard passed.'
        ELSE 'REFUSING TQ5 MDJ wording refresh: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_wording_refresh_guard;

UPDATE reward_discipline_transfer
SET title = 'Task Completed',
    description = 'Positive point for finishing an agreed Quran, Salah, class, or home task with care.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title IN ('Completed Quran or Salah task with care', 'Completed Quran task with care');

UPDATE reward_discipline_transfer
SET title = 'Good Adab',
    description = 'Positive point for manners, respect, or calm speech.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Showed good adab';

UPDATE reward_discipline_transfer
SET title = 'Helpful',
    description = 'Positive point for responsibility and helpfulness at home.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Helped at home without being asked';

UPDATE reward_discipline_transfer
SET title = 'Truthful',
    description = 'Positive point for honesty, self-control, and calm repair.',
    sort = 40
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Spoke truthfully and calmly';

UPDATE reward_discipline_transfer
SET title = 'Device Boundary',
    description = 'Positive point for respecting an agreed device or screen-time boundary.',
    sort = 50
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Kept an agreed device boundary';

UPDATE reward_discipline_transfer
SET title = 'Routine Missed',
    description = 'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title IN ('Missed an agreed routine after reminder', 'Missed an agreed routine');

UPDATE reward_discipline_transfer
SET title = 'Task Not Done',
    description = 'Minor slip for delaying or missing an agreed task when the learner could reasonably do it.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Delayed a task without a clear reason';

UPDATE reward_discipline_transfer
SET title = 'Disrespect',
    description = 'Minor slip for disrespectful words, tone, or avoidable arguing.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Spoke disrespectfully';

UPDATE reward_discipline_transfer
SET title = 'Untidy Space',
    description = 'Minor slip for leaving an agreed personal/home responsibility incomplete.',
    sort = 40
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Left a personal area untidy';

UPDATE reward_discipline_transfer
SET title = 'Refusal',
    description = 'Red-flag accountability point for refusing a clear, age-appropriate family instruction.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Refused an agreed family instruction';

UPDATE reward_discipline_transfer
SET title = 'Hurtful Words',
    description = 'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Used hurtful speech or behavior';

UPDATE reward_discipline_transfer
SET title = 'Safety Boundary',
    description = 'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Broke a device or safety boundary';

UPDATE reward_discipline_points
SET title = 'Task Completed',
    description = 'Positive point for finishing an agreed Quran, Salah, class, or home task with care.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title IN ('Completed Quran or Salah task with care', 'Completed Quran task with care');

UPDATE reward_discipline_points
SET title = 'Good Adab',
    description = 'Positive point for manners, respect, or calm speech.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Showed good adab';

UPDATE reward_discipline_points
SET title = 'Helpful',
    description = 'Positive point for responsibility and helpfulness at home.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Helped at home without being asked';

UPDATE reward_discipline_points
SET title = 'Truthful',
    description = 'Positive point for honesty, self-control, and calm repair.',
    sort = 40
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Spoke truthfully and calmly';

UPDATE reward_discipline_points
SET title = 'Device Boundary',
    description = 'Positive point for respecting an agreed device or screen-time boundary.',
    sort = 50
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Positive'
  AND title = 'Kept an agreed device boundary';

UPDATE reward_discipline_points
SET title = 'Routine Missed',
    description = 'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title IN ('Missed an agreed routine after reminder', 'Missed an agreed routine');

UPDATE reward_discipline_points
SET title = 'Task Not Done',
    description = 'Minor slip for delaying or missing an agreed task when the learner could reasonably do it.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Delayed a task without a clear reason';

UPDATE reward_discipline_points
SET title = 'Disrespect',
    description = 'Minor slip for disrespectful words, tone, or avoidable arguing.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Spoke disrespectfully';

UPDATE reward_discipline_points
SET title = 'Untidy Space',
    description = 'Minor slip for leaving an agreed personal/home responsibility incomplete.',
    sort = 40
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'Slip'
  AND title = 'Left a personal area untidy';

UPDATE reward_discipline_points
SET title = 'Refusal',
    description = 'Red-flag accountability point for refusing a clear, age-appropriate family instruction.',
    sort = 10
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Refused an agreed family instruction';

UPDATE reward_discipline_points
SET title = 'Hurtful Words',
    description = 'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.',
    sort = 20
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Used hurtful speech or behavior';

UPDATE reward_discipline_points
SET title = 'Safety Boundary',
    description = 'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.',
    sort = 30
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND type = 'No Way'
  AND title = 'Broke a device or safety boundary';

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'The missed task is completed before screen time, games, or play.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND ps.suggestion_text IN (
      'Use the meeting-defined consequence for this minor slip.',
      'Use the meeting-defined consequence.'
  );

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'Entertainment phone, tablet, or game use is paused until the task is completed.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND ps.suggestion_text = 'Short reflection and repair action agreed in the family meeting.';

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'A favorite activity or training may be paused for one time if the same slip repeats.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND ps.suggestion_text = 'Repeat or repair the missed routine with parent support.';

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'Entertainment phone, games, or tablet use is paused for 24 hours.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND ps.suggestion_text = 'Use the meeting-defined consequence for this serious action.';

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'A favorite activity, outing, or training is paused until the learner completes the repair step.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND ps.suggestion_text = 'Pause an agreed privilege according to the family meeting decision.';

UPDATE punishments_suggestions ps
JOIN punishment_types pt ON pt.id = ps.punishment_type_id
SET ps.suggestion_text = 'The learner makes things right through an apology, correction, or helping the affected person.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND ps.suggestion_text = 'Repair the harm and discuss the next step with a parent.';

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'The missed task is completed before screen time, games, or play.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND pa.title IN (
      'Use the meeting-defined consequence for this minor slip.',
      'Use the meeting-defined consequence.'
  );

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'Entertainment phone, tablet, or game use is paused until the task is completed.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND pa.title = 'Short reflection and repair action agreed in the family meeting.';

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'A favorite activity or training may be paused for one time if the same slip repeats.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND pa.title = 'Repeat or repair the missed routine with parent support.';

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'Entertainment phone, games, or tablet use is paused for 24 hours.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND pa.title = 'Use the meeting-defined consequence for this serious action.';

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'A favorite activity, outing, or training is paused until the learner completes the repair step.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND pa.title = 'Pause an agreed privilege according to the family meeting decision.';

UPDATE punishment_agreements pa
JOIN punishment_types pt ON pt.id = pa.punishment_type_id
SET pa.title = 'The learner makes things right through an apology, correction, or helping the affected person.'
WHERE @tq5_mdj_wording_refresh_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND pa.title = 'Repair the harm and discuss the next step with a parent.';

SELECT type, GROUP_CONCAT(title ORDER BY sort, id SEPARATOR ' | ') AS behavior_titles
FROM reward_discipline_transfer
WHERE type IN ('Positive', 'Slip', 'No Way')
GROUP BY type
ORDER BY FIELD(type, 'Positive', 'Slip', 'No Way');

SELECT pt.title AS punishment_type, GROUP_CONCAT(ps.suggestion_text ORDER BY ps.id SEPARATOR ' | ') AS suggestions
FROM punishment_types pt
JOIN punishments_suggestions ps ON ps.punishment_type_id = pt.id
WHERE pt.title IN ('Minor Slip', 'Serious Action')
GROUP BY pt.title
ORDER BY FIELD(pt.title, 'Minor Slip', 'Serious Action');
