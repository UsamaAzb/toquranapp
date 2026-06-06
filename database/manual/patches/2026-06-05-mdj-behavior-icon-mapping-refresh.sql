-- TQ5 My Deen Journey behavior icon mapping refresh
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-icon-mapping-refresh.sql
--
-- Purpose:
--   Replace the single fallback heart icon used by local MDJ launch behavior
--   rows with distinct existing Week14-style discipline icons already present
--   under public/images/discipline. This updates starter templates, copied
--   per-student behavior templates, and matching behavior history rows.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - update/insert-only for icon metadata;
--   - no deletes, drops, truncates, or cleanup.

SET @tq5_mdj_icon_mapping_confirm := 'TQ5_MDJ_ICON_MAPPING_REFRESH_2026_06_05';

SET @tq5_mdj_icon_mapping_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_icon_mapping_confirm = 'TQ5_MDJ_ICON_MAPPING_REFRESH_2026_06_05'
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
        WHEN @tq5_mdj_icon_mapping_guard_ok = 1
            THEN 'TQ5 MDJ icon mapping refresh guard passed.'
        ELSE 'REFUSING TQ5 MDJ icon mapping refresh: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_icon_mapping_guard;

INSERT INTO discipline_icons (path)
SELECT icon_path
FROM (
    SELECT 'images/discipline/correct-check.png' AS icon_path
    UNION ALL SELECT 'images/discipline/respect.png'
    UNION ALL SELECT 'images/discipline/shakehands.png'
    UNION ALL SELECT 'images/discipline/lamp.png'
    UNION ALL SELECT 'images/discipline/moving-policy.png'
    UNION ALL SELECT 'images/discipline/clock.png'
    UNION ALL SELECT 'images/discipline/keep-trying.png'
    UNION ALL SELECT 'images/discipline/tag.png'
    UNION ALL SELECT 'images/discipline/leafpng.png'
    UNION ALL SELECT 'images/discipline/persistence_.png'
    UNION ALL SELECT 'images/discipline/chatspng.png'
    UNION ALL SELECT 'images/discipline/earth.png'
) launch_icons
WHERE @tq5_mdj_icon_mapping_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1 FROM discipline_icons di WHERE di.path = launch_icons.icon_path
  );

UPDATE reward_discipline_transfer rdt
JOIN (
    SELECT 'Task Completed' AS title, 'Positive' AS type, 'images/discipline/correct-check.png' AS icon_path
    UNION ALL SELECT 'Good Adab', 'Positive', 'images/discipline/respect.png'
    UNION ALL SELECT 'Helpful', 'Positive', 'images/discipline/shakehands.png'
    UNION ALL SELECT 'Truthful', 'Positive', 'images/discipline/lamp.png'
    UNION ALL SELECT 'Device Boundary', 'Positive', 'images/discipline/moving-policy.png'
    UNION ALL SELECT 'Routine Missed', 'Slip', 'images/discipline/clock.png'
    UNION ALL SELECT 'Task Not Done', 'Slip', 'images/discipline/keep-trying.png'
    UNION ALL SELECT 'Disrespect', 'Slip', 'images/discipline/tag.png'
    UNION ALL SELECT 'Untidy Space', 'Slip', 'images/discipline/leafpng.png'
    UNION ALL SELECT 'Refusal', 'No Way', 'images/discipline/persistence_.png'
    UNION ALL SELECT 'Hurtful Words', 'No Way', 'images/discipline/chatspng.png'
    UNION ALL SELECT 'Safety Boundary', 'No Way', 'images/discipline/earth.png'
) icon_map
  ON icon_map.title = rdt.title
 AND icon_map.type = rdt.type
JOIN discipline_icons di
  ON di.path = icon_map.icon_path
SET rdt.discipline_icon_id = di.id,
    rdt.discipline_icon_path = icon_map.icon_path
WHERE @tq5_mdj_icon_mapping_guard_ok = 1;

UPDATE reward_discipline_points rdp
JOIN (
    SELECT 'Task Completed' AS title, 'Positive' AS type, 'images/discipline/correct-check.png' AS icon_path
    UNION ALL SELECT 'Good Adab', 'Positive', 'images/discipline/respect.png'
    UNION ALL SELECT 'Helpful', 'Positive', 'images/discipline/shakehands.png'
    UNION ALL SELECT 'Truthful', 'Positive', 'images/discipline/lamp.png'
    UNION ALL SELECT 'Device Boundary', 'Positive', 'images/discipline/moving-policy.png'
    UNION ALL SELECT 'Routine Missed', 'Slip', 'images/discipline/clock.png'
    UNION ALL SELECT 'Task Not Done', 'Slip', 'images/discipline/keep-trying.png'
    UNION ALL SELECT 'Disrespect', 'Slip', 'images/discipline/tag.png'
    UNION ALL SELECT 'Untidy Space', 'Slip', 'images/discipline/leafpng.png'
    UNION ALL SELECT 'Refusal', 'No Way', 'images/discipline/persistence_.png'
    UNION ALL SELECT 'Hurtful Words', 'No Way', 'images/discipline/chatspng.png'
    UNION ALL SELECT 'Safety Boundary', 'No Way', 'images/discipline/earth.png'
) icon_map
  ON icon_map.title = rdp.title
 AND icon_map.type = rdp.type
JOIN discipline_icons di
  ON di.path = icon_map.icon_path
SET rdp.discipline_icon_id = di.id,
    rdp.discipline_icon_path = icon_map.icon_path,
    rdp.updated_at = NOW()
WHERE @tq5_mdj_icon_mapping_guard_ok = 1
  AND (
      rdp.discipline_icon_path IS NULL
      OR rdp.discipline_icon_path = ''
      OR rdp.discipline_icon_path = 'images/discipline/respect.png'
      OR rdp.discipline_icon_path <> icon_map.icon_path
  );

UPDATE student_session_discipline ssd
JOIN (
    SELECT 'Task Completed' AS title, 'Positive' AS type, 'images/discipline/correct-check.png' AS icon_path
    UNION ALL SELECT 'Good Adab', 'Positive', 'images/discipline/respect.png'
    UNION ALL SELECT 'Helpful', 'Positive', 'images/discipline/shakehands.png'
    UNION ALL SELECT 'Truthful', 'Positive', 'images/discipline/lamp.png'
    UNION ALL SELECT 'Device Boundary', 'Positive', 'images/discipline/moving-policy.png'
    UNION ALL SELECT 'Routine Missed', 'Slip', 'images/discipline/clock.png'
    UNION ALL SELECT 'Task Not Done', 'Slip', 'images/discipline/keep-trying.png'
    UNION ALL SELECT 'Disrespect', 'Slip', 'images/discipline/tag.png'
    UNION ALL SELECT 'Untidy Space', 'Slip', 'images/discipline/leafpng.png'
    UNION ALL SELECT 'Refusal', 'No Way', 'images/discipline/persistence_.png'
    UNION ALL SELECT 'Hurtful Words', 'No Way', 'images/discipline/chatspng.png'
    UNION ALL SELECT 'Safety Boundary', 'No Way', 'images/discipline/earth.png'
) icon_map
  ON icon_map.title = ssd.title
 AND icon_map.type = ssd.type
JOIN discipline_icons di
  ON di.path = icon_map.icon_path
SET ssd.discipline_icon_id = di.id,
    ssd.discipline_icon_path = icon_map.icon_path,
    ssd.updated_at = NOW()
WHERE @tq5_mdj_icon_mapping_guard_ok = 1
  AND (
      ssd.discipline_icon_path IS NULL
      OR ssd.discipline_icon_path = ''
      OR ssd.discipline_icon_path = 'images/discipline/respect.png'
      OR ssd.discipline_icon_path <> icon_map.icon_path
  );

SELECT 'discipline_icons' AS table_name, COUNT(*) AS rows_count FROM discipline_icons
UNION ALL SELECT 'reward_discipline_transfer_with_icons', COUNT(*) FROM reward_discipline_transfer WHERE discipline_icon_id IS NOT NULL AND discipline_icon_path IS NOT NULL
UNION ALL SELECT 'reward_discipline_points_with_icons', COUNT(*) FROM reward_discipline_points WHERE discipline_icon_id IS NOT NULL AND discipline_icon_path IS NOT NULL;

SELECT title, type, discipline_icon_path
FROM reward_discipline_transfer
WHERE title IN (
    'Task Completed',
    'Good Adab',
    'Helpful',
    'Truthful',
    'Device Boundary',
    'Routine Missed',
    'Task Not Done',
    'Disrespect',
    'Untidy Space',
    'Refusal',
    'Hurtful Words',
    'Safety Boundary'
)
ORDER BY FIELD(type, 'Positive', 'Slip', 'No Way'), sort, id;
