-- TQ5 My Deen Journey behavior icon remap
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-behavior-icon-remap.sql
--
-- Purpose:
--   Replace weak launch icon choices after manual review. Week14 discipline
--   image assets were already present in this repo; this patch maps To Quran's
--   broader MDJ behavior titles to more fitting Week14 icon files.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - insert/update only, idempotent by icon path and behavior title/type;
--   - no drops, truncates, or production deployment.

SET @tq5_mdj_icon_remap_confirm := 'TQ5_MDJ_ICON_REMAP_2026_06_06';

SET @tq5_mdj_icon_remap_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_icon_remap_confirm = 'TQ5_MDJ_ICON_REMAP_2026_06_06'
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
        WHEN @tq5_mdj_icon_remap_guard_ok = 1
            THEN 'TQ5 MDJ behavior icon remap guard passed.'
        ELSE 'REFUSING TQ5 MDJ behavior icon remap: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_icon_remap_guard;

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_behavior_icon_map (
    title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    icon_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (title, type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

DELETE FROM tq5_behavior_icon_map;

INSERT INTO tq5_behavior_icon_map (title, type, icon_path) VALUES
('Good Job', 'Positive', 'images/discipline/34-ud0vRyQq.png'),
('Good Effort', 'Positive', 'images/discipline/74-Dizvjp7n.png'),
('Focused', 'Positive', 'images/discipline/20-BPaZ4Ete.png'),
('Good Adab', 'Positive', 'images/discipline/respect.png'),
('Honesty', 'Positive', 'images/discipline/35-CxgcOsNl.png'),
('Responsibility', 'Positive', 'images/discipline/61-DWTOj_T6.png'),
('Self-Control', 'Positive', 'images/discipline/71-Ey5tyt2G.png'),
('Helping Others', 'Positive', 'images/discipline/shakehands.png'),
('Good Deed', 'Positive', 'images/discipline/67-DlneWycG.png'),
('Good Question', 'Positive', 'images/discipline/59-DctAzBtq.png'),
('On Time', 'Positive', 'images/discipline/clock.png'),
('Oops!', 'Slip', 'images/discipline/42-CcVNxBRq.png'),
('Not Ready', 'Slip', 'images/discipline/26-coCa5JE0.png'),
('Distracted', 'Slip', 'images/discipline/51-CZkeNwpv.png'),
('Time Wasted', 'Slip', 'images/discipline/clock.png'),
('Task Not Done', 'Slip', 'images/discipline/61-DWTOj_T6.png'),
('Low Practice', 'Slip', 'images/discipline/leafpng.png'),
('Adab Slip', 'Slip', 'images/discipline/73-tSz4ujTS.png'),
('Device Slip', 'Slip', 'images/discipline/63-C0dY3Flz.png'),
('Small Excuse', 'Slip', 'images/discipline/59-DctAzBtq.png'),
('No Response', 'Slip', 'images/discipline/micopng.png'),
('Rule Reminder', 'Slip', 'images/discipline/40-CVyPO1Sf.png'),
('Serious Matter', 'No Way', 'images/discipline/41-D3kTTAuf.png'),
('Hurtful Words', 'No Way', 'images/discipline/43-D4EMnrNR.png'),
('Dishonesty', 'No Way', 'images/discipline/35-CxgcOsNl.png'),
('Cheating', 'No Way', 'images/discipline/21-DWkgiIWq.png'),
('Bullying', 'No Way', 'images/discipline/50-DbUUee_w.png'),
('Aggression', 'No Way', 'images/discipline/55-DT6MykPZ.png'),
('Major Disrespect', 'No Way', 'images/discipline/50-DbUUee_w.png'),
('Device Misuse', 'No Way', 'images/discipline/63-C0dY3Flz.png'),
('Rule Broken', 'No Way', 'images/discipline/40-CVyPO1Sf.png');

INSERT INTO discipline_icons (path)
SELECT m.icon_path
FROM tq5_behavior_icon_map m
WHERE @tq5_mdj_icon_remap_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM discipline_icons i
      WHERE i.path = m.icon_path
  );

UPDATE reward_discipline_transfer rdt
JOIN tq5_behavior_icon_map m
  ON m.title = rdt.title
 AND m.type = rdt.type
JOIN discipline_icons i
  ON i.path = m.icon_path
SET rdt.discipline_icon_id = i.id,
    rdt.discipline_icon_path = i.path,
    rdt.updated_at = NOW()
WHERE @tq5_mdj_icon_remap_guard_ok = 1;

UPDATE reward_discipline_points rdp
JOIN tq5_behavior_icon_map m
  ON m.title = rdp.title
 AND m.type = rdp.type
JOIN discipline_icons i
  ON i.path = m.icon_path
SET rdp.discipline_icon_id = i.id,
    rdp.discipline_icon_path = i.path,
    rdp.updated_at = NOW()
WHERE @tq5_mdj_icon_remap_guard_ok = 1;

SELECT rdt.type, rdt.title, rdt.discipline_icon_path
FROM reward_discipline_transfer rdt
WHERE rdt.type IN ('Positive', 'Slip', 'No Way')
ORDER BY FIELD(rdt.type, 'Positive', 'Slip', 'No Way'), rdt.sort, rdt.id;
