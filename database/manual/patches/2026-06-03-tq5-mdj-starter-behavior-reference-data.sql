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
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM discipline_icons di WHERE di.path = launch_icons.icon_path
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Task Completed', 'active', 5,
       'Positive point for finishing an agreed Quran, Salah, class, or home task with care.',
       'Positive', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Task Completed' AND type = 'Positive'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Good Job' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Good Adab', 'active', 4,
       'Positive point for manners, respect, or calm speech.',
       'Positive', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Good Adab' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Helpful', 'active', 3,
       'Positive point for responsibility and helpfulness at home.',
       'Positive', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Helpful' AND type = 'Positive'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Helping Others' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Truthful', 'active', 3,
       'Positive point for honesty, self-control, and calm repair.',
       'Positive', NULL, NULL, 40, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Truthful' AND type = 'Positive'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Honesty' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Device Boundary', 'active', 3,
       'Positive point for respecting an agreed device or screen-time boundary.',
       'Positive', NULL, NULL, 50, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Device Boundary' AND type = 'Positive'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Self-Control' AND type = 'Positive'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Routine Missed', 'active', 2,
       'Minor slip for missing an agreed family, worship, study, or home routine after a reminder.',
       'Slip', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Routine Missed' AND type = 'Slip'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Oops!' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Task Not Done', 'active', 2,
       'Minor slip for delaying an agreed task when the child could reasonably do it.',
       'Slip', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Task Not Done' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Disrespect', 'active', 3,
       'Minor slip for disrespectful words, tone, or avoidable arguing.',
       'Slip', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Disrespect' AND type = 'Slip'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Adab Slip' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Untidy Space', 'active', 1,
       'Minor slip for leaving an agreed personal/home responsibility incomplete.',
       'Slip', NULL, NULL, 40, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Untidy Space' AND type = 'Slip'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Rule Reminder' AND type = 'Slip'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Refusal', 'active', 5,
       'Red-flag accountability point for refusing a clear, age-appropriate family instruction.',
       'No Way', NULL, NULL, 10, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Refusal' AND type = 'No Way'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Serious Matter' AND type = 'No Way'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Hurtful Words', 'active', 5,
       'Red-flag accountability point for hurtful speech, repeated disrespect, or harmful behavior.',
       'No Way', NULL, NULL, 20, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Hurtful Words' AND type = 'No Way'
);

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT 'Safety Boundary', 'active', 6,
       'Red-flag accountability point for breaking an agreed device, safety, or family trust boundary.',
       'No Way', NULL, NULL, 30, 0, 0
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Safety Boundary' AND type = 'No Way'
  )
  AND NOT EXISTS (
    SELECT 1 FROM reward_discipline_transfer
    WHERE title = 'Device Misuse' AND type = 'No Way'
);

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'The missed task is completed before screen time, games, or play.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'The missed task is completed before screen time, games, or play.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Entertainment phone, tablet, or game use is paused until the task is completed.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Entertainment phone, tablet, or game use is paused until the task is completed.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'A favorite activity or training may be paused for one time if the same slip repeats.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Minor Slip'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'A favorite activity or training may be paused for one time if the same slip repeats.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'Entertainment phone, games, or tablet use is paused for 24 hours.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'Entertainment phone, games, or tablet use is paused for 24 hours.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'A favorite activity, outing, or training is paused until the learner completes the repair step.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'A favorite activity, outing, or training is paused until the learner completes the repair step.'
  );

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, 'The learner makes things right through an apology, correction, or helping the affected person.'
FROM punishment_types pt
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND pt.title = 'Serious Action'
  AND NOT EXISTS (
      SELECT 1 FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = 'The learner makes things right through an apology, correction, or helping the affected person.'
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
WHERE @tq5_mdj_starter_data_guard_ok = 1;

-- Final TQ5 launch vocabulary reconciliation.
-- Keeps this starter patch aligned with the current PHP launch defaults instead
-- of relying on later local refresh patches to discover popup category rows.
CREATE TEMPORARY TABLE IF NOT EXISTS tq5_final_behavior_defaults (
    title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    points INT NOT NULL,
    description TEXT NULL,
    icon_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    sort_order INT NOT NULL,
    teacher_desc TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (title, type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

DELETE FROM tq5_final_behavior_defaults;

INSERT INTO tq5_final_behavior_defaults
    (title, type, points, description, icon_path, sort_order, teacher_desc)
VALUES
('Good Job', 'Positive', 1, 'Custom positive point for a good moment worth noticing.', 'images/discipline/34-ud0vRyQq.png', 10, 1),
('Good Effort', 'Positive', 1, 'The learner tried seriously and did not give up quickly.', 'images/discipline/74-Dizvjp7n.png', 20, 0),
('Focused', 'Positive', 1, 'The learner stayed attentive during learning time.', 'images/discipline/20-BPaZ4Ete.png', 30, 0),
('Good Adab', 'Positive', 1, 'The learner showed polite manners with teacher, parent, or others.', 'images/discipline/respect.png', 40, 0),
('Honesty', 'Positive', 1, 'The learner told the truth or corrected a mistake honestly.', 'images/discipline/35-CxgcOsNl.png', 50, 0),
('Responsibility', 'Positive', 1, 'The learner took care of an agreed duty or routine.', 'images/discipline/61-DWTOj_T6.png', 60, 0),
('Self-Control', 'Positive', 1, 'The learner controlled emotions, reactions, or distractions.', 'images/discipline/71-Ey5tyt2G.png', 70, 0),
('Helping Others', 'Positive', 1, 'The learner helped someone kindly.', 'images/discipline/shakehands.png', 80, 0),
('Good Deed', 'Positive', 1, 'The learner did something beneficial for family, class, or community.', 'images/discipline/67-DlneWycG.png', 90, 0),
('Good Question', 'Positive', 1, 'The learner asked a thoughtful question to understand better.', 'images/discipline/59-DctAzBtq.png', 100, 0),
('On Time', 'Positive', 1, 'The learner joined, started, or completed something at the agreed time.', 'images/discipline/clock.png', 110, 0),
('Oops!', 'Slip', 1, 'Custom slip point for a small mistake that needs a quick correction.', 'images/discipline/42-CcVNxBRq.png', 10, 1),
('Not Ready', 'Slip', 1, 'The learner started without needed preparation or attitude.', 'images/discipline/26-coCa5JE0.png', 20, 0),
('Distracted', 'Slip', 1, 'The learner lost focus during learning time.', 'images/discipline/51-CZkeNwpv.png', 30, 0),
('Time Wasted', 'Slip', 1, 'The learner used learning time poorly.', 'images/discipline/clock.png', 40, 0),
('Task Not Done', 'Slip', 1, 'The learner did not complete an agreed task or practice.', 'images/discipline/61-DWTOj_T6.png', 50, 0),
('Low Practice', 'Slip', 1, 'The learner did less daily practice than agreed.', 'images/discipline/leafpng.png', 60, 0),
('Adab Slip', 'Slip', 1, 'The learner forgot polite manners but can correct it quickly.', 'images/discipline/73-tSz4ujTS.png', 70, 0),
('Device Slip', 'Slip', 1, 'The learner used phone, game, tablet, or screen in a distracting way.', 'images/discipline/63-C0dY3Flz.png', 80, 0),
('Small Excuse', 'Slip', 1, 'The learner avoided responsibility with a weak or unclear excuse.', 'images/discipline/59-DctAzBtq.png', 90, 0),
('No Response', 'Slip', 1, 'The learner did not respond or participate when expected.', 'images/discipline/micopng.png', 100, 0),
('Rule Reminder', 'Slip', 1, 'The learner needed a reminder about an agreed rule.', 'images/discipline/40-CVyPO1Sf.png', 110, 0),
('Serious Matter', 'No Way', 5, 'Custom red-flag point for a serious matter that needs parent follow-up.', 'images/discipline/41-D3kTTAuf.png', 10, 1),
('Hurtful Words', 'No Way', 5, 'The learner used words that insult, mock, hurt, or disrespect others.', 'images/discipline/43-D4EMnrNR.png', 20, 0),
('Dishonesty', 'No Way', 5, 'The learner knowingly hid the truth or gave false information.', 'images/discipline/35-CxgcOsNl.png', 30, 0),
('Cheating', 'No Way', 5, 'The learner used unfair help or claimed work that was not truly done.', 'images/discipline/21-DWkgiIWq.png', 40, 0),
('Bullying', 'No Way', 5, 'The learner repeatedly hurt, mocked, pressured, or excluded someone.', 'images/discipline/50-DbUUee_w.png', 50, 0),
('Aggression', 'No Way', 5, 'The learner used physical force or unsafe behavior.', 'images/discipline/55-DT6MykPZ.png', 60, 0),
('Major Disrespect', 'No Way', 5, 'The learner showed clear disrespect after reminders.', 'images/discipline/50-DbUUee_w.png', 70, 0),
('Device Misuse', 'No Way', 5, 'The learner broke a clear phone, game, internet, or home screen rule.', 'images/discipline/63-C0dY3Flz.png', 80, 0),
('Rule Broken', 'No Way', 5, 'The learner clearly broke an important agreed rule.', 'images/discipline/40-CVyPO1Sf.png', 90, 0);

INSERT INTO discipline_icons (path)
SELECT DISTINCT d.icon_path
FROM tq5_final_behavior_defaults d
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1 FROM discipline_icons i WHERE i.path = d.icon_path
  );

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_final_behavior_title_map (
    old_title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    new_title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (old_title, type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

DELETE FROM tq5_final_behavior_title_map;

INSERT INTO tq5_final_behavior_title_map (old_title, type, new_title) VALUES
('Task Completed', 'Positive', 'Good Job'),
('Helpful', 'Positive', 'Helping Others'),
('Truthful', 'Positive', 'Honesty'),
('Device Boundary', 'Positive', 'Self-Control'),
('Routine Missed', 'Slip', 'Oops!'),
('Disrespect', 'Slip', 'Adab Slip'),
('Untidy Space', 'Slip', 'Rule Reminder'),
('Refusal', 'No Way', 'Serious Matter'),
('Safety Boundary', 'No Way', 'Device Misuse');

UPDATE reward_discipline_transfer rdt
JOIN tq5_final_behavior_title_map m
  ON m.old_title = rdt.title
 AND m.type = rdt.type
JOIN tq5_final_behavior_defaults d
  ON d.title = m.new_title
 AND d.type = m.type
JOIN discipline_icons i
  ON i.path = d.icon_path
SET rdt.title = d.title,
    rdt.points = d.points,
    rdt.description = d.description,
    rdt.discipline_icon_id = i.id,
    rdt.discipline_icon_path = i.path,
    rdt.sort = d.sort_order,
    rdt.teacher_desc = d.teacher_desc,
    rdt.status = 'active',
    rdt.updated_at = NOW()
WHERE @tq5_mdj_starter_data_guard_ok = 1;

UPDATE reward_discipline_transfer rdt
JOIN tq5_final_behavior_defaults d
  ON d.title = rdt.title
 AND d.type = rdt.type
JOIN discipline_icons i
  ON i.path = d.icon_path
SET rdt.points = d.points,
    rdt.description = d.description,
    rdt.discipline_icon_id = i.id,
    rdt.discipline_icon_path = i.path,
    rdt.sort = d.sort_order,
    rdt.teacher_desc = d.teacher_desc,
    rdt.status = 'active',
    rdt.updated_at = NOW()
WHERE @tq5_mdj_starter_data_guard_ok = 1;

INSERT INTO reward_discipline_transfer
    (title, status, points, description, type, discipline_icon_id, discipline_icon_path, sort, teacher_desc, selected)
SELECT d.title, 'active', d.points, d.description, d.type, i.id, i.path, d.sort_order, d.teacher_desc, 0
FROM tq5_final_behavior_defaults d
JOIN discipline_icons i
  ON i.path = d.icon_path
WHERE @tq5_mdj_starter_data_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM reward_discipline_transfer rdt
      WHERE rdt.title = d.title
        AND rdt.type = d.type
  );

SELECT 'punishment_types' AS table_name, COUNT(*) AS rows_count FROM punishment_types
UNION ALL SELECT 'punishments_suggestions', COUNT(*) FROM punishments_suggestions
UNION ALL SELECT 'reward_discipline_transfer', COUNT(*) FROM reward_discipline_transfer
UNION ALL SELECT 'discipline_icons', COUNT(*) FROM discipline_icons;
