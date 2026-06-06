-- TQ5 My Deen Journey LMS-style consequence/behavior refresh
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-lms-consequence-behavior-refresh.sql
--
-- Purpose:
--   Replace the compact/robotic TQ5 launch consequence defaults with the
--   practical Week14 LMS agreement language, then add a small To Quran layer.
--   Expand starter behavior cards to the broader ChatGPT-reviewed launch set.
--
-- Safety:
--   - guarded to the intended To Quran app DB target;
--   - requires an explicit operator confirmation variable;
--   - verifies My Deen Journey / Well Being subject identities;
--   - update/insert only, idempotent by title/type/student checks;
--   - no drops, truncates, or production deployment.

SET @tq5_mdj_lms_refresh_confirm := 'TQ5_MDJ_LMS_REFRESH_2026_06_06';

SET @tq5_mdj_lms_refresh_guard_ok := (
    DATABASE() = 'u504065335_to_quran'
    AND @tq5_mdj_lms_refresh_confirm = 'TQ5_MDJ_LMS_REFRESH_2026_06_06'
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
        WHEN @tq5_mdj_lms_refresh_guard_ok = 1
            THEN 'TQ5 MDJ LMS consequence/behavior refresh guard passed.'
        ELSE 'REFUSING TQ5 MDJ LMS consequence/behavior refresh: wrong DB, missing confirmation, or subject 15/16 identity mismatch. Updates are gated off.'
    END AS tq5_mdj_lms_refresh_guard;

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_behavior_defaults (
    title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    points INT NOT NULL,
    description TEXT NULL,
    icon_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    sort_order INT NOT NULL,
    PRIMARY KEY (title, type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

DELETE FROM tq5_behavior_defaults;

INSERT INTO tq5_behavior_defaults (title, type, points, description, icon_path, sort_order) VALUES
('Good Job', 'Positive', 1, 'Custom positive point for a good moment worth noticing.', 'images/discipline/correct-check.png', 10),
('Good Effort', 'Positive', 1, 'The learner tried seriously and did not give up quickly.', 'images/discipline/work-hard.png', 20),
('Focused', 'Positive', 1, 'The learner stayed attentive during learning time.', 'images/discipline/lamp.png', 30),
('Good Adab', 'Positive', 1, 'The learner showed polite manners with teacher, parent, or others.', 'images/discipline/respect.png', 40),
('Honesty', 'Positive', 1, 'The learner told the truth or corrected a mistake honestly.', 'images/discipline/correct-check.png', 50),
('Responsibility', 'Positive', 1, 'The learner took care of an agreed duty or routine.', 'images/discipline/teamwork.png', 60),
('Self-Control', 'Positive', 1, 'The learner controlled emotions, reactions, or distractions.', 'images/discipline/moving-policy.png', 70),
('Helping Others', 'Positive', 1, 'The learner helped someone kindly.', 'images/discipline/shakehands.png', 80),
('Good Deed', 'Positive', 1, 'The learner did something beneficial for family, class, or community.', 'images/discipline/tree.png', 90),
('Good Question', 'Positive', 1, 'The learner asked a thoughtful question to understand better.', 'images/discipline/chatspng.png', 100),
('On Time', 'Positive', 1, 'The learner joined, started, or completed something at the agreed time.', 'images/discipline/clock.png', 110),
('Oops!', 'Slip', 1, 'Custom slip point for a small mistake that needs a quick correction.', 'images/discipline/keep-trying.png', 10),
('Not Ready', 'Slip', 1, 'The learner started without needed preparation or attitude.', 'images/discipline/graduatedpng.png', 20),
('Distracted', 'Slip', 1, 'The learner lost focus during learning time.', 'images/discipline/plane.png', 30),
('Time Wasted', 'Slip', 1, 'The learner used learning time poorly.', 'images/discipline/clock.png', 40),
('Task Not Done', 'Slip', 1, 'The learner did not complete an agreed task or practice.', 'images/discipline/keep-trying.png', 50),
('Low Practice', 'Slip', 1, 'The learner did less daily practice than agreed.', 'images/discipline/leafpng.png', 60),
('Adab Slip', 'Slip', 1, 'The learner forgot polite manners but can correct it quickly.', 'images/discipline/respect.png', 70),
('Device Slip', 'Slip', 1, 'The learner used phone, game, tablet, or screen in a distracting way.', 'images/discipline/moving-policy.png', 80),
('Small Excuse', 'Slip', 1, 'The learner avoided responsibility with a weak or unclear excuse.', 'images/discipline/chatspng.png', 90),
('No Response', 'Slip', 1, 'The learner did not respond or participate when expected.', 'images/discipline/micopng.png', 100),
('Rule Reminder', 'Slip', 1, 'The learner needed a reminder about an agreed rule.', 'images/discipline/tag.png', 110),
('Serious Matter', 'No Way', 5, 'Custom red-flag point for a serious matter that needs parent follow-up.', 'images/discipline/tag.png', 10),
('Hurtful Words', 'No Way', 5, 'The learner used words that insult, mock, hurt, or disrespect others.', 'images/discipline/chatspng.png', 20),
('Dishonesty', 'No Way', 5, 'The learner knowingly hid the truth or gave false information.', 'images/discipline/correct-check.png', 30),
('Cheating', 'No Way', 5, 'The learner used unfair help or claimed work that was not truly done.', 'images/discipline/graduatedpng.png', 40),
('Bullying', 'No Way', 5, 'The learner repeatedly hurt, mocked, pressured, or excluded someone.', 'images/discipline/earth.png', 50),
('Aggression', 'No Way', 5, 'The learner used physical force or unsafe behavior.', 'images/discipline/persistence_.png', 60),
('Major Disrespect', 'No Way', 5, 'The learner showed clear disrespect after reminders.', 'images/discipline/respect.png', 70),
('Device Misuse', 'No Way', 5, 'The learner broke a clear phone, game, internet, or home screen rule.', 'images/discipline/moving-policy.png', 80),
('Rule Broken', 'No Way', 5, 'The learner clearly broke an important agreed rule.', 'images/discipline/tag.png', 90);

INSERT INTO discipline_icons (path)
SELECT DISTINCT d.icon_path
FROM tq5_behavior_defaults d
WHERE @tq5_mdj_lms_refresh_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM discipline_icons i
      WHERE i.path = d.icon_path
  );

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_behavior_title_map (
    old_title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    new_title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (old_title, type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

DELETE FROM tq5_behavior_title_map;

INSERT INTO tq5_behavior_title_map (old_title, type, new_title) VALUES
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
JOIN tq5_behavior_title_map m
  ON m.old_title = rdt.title
 AND m.type = rdt.type
JOIN tq5_behavior_defaults d
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
    rdt.status = 'active',
    rdt.updated_at = NOW()
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

UPDATE reward_discipline_transfer rdt
JOIN tq5_behavior_defaults d
  ON d.title = rdt.title
 AND d.type = rdt.type
JOIN discipline_icons i
  ON i.path = d.icon_path
SET rdt.points = d.points,
    rdt.description = d.description,
    rdt.discipline_icon_id = i.id,
    rdt.discipline_icon_path = i.path,
    rdt.sort = d.sort_order,
    rdt.status = 'active',
    rdt.updated_at = NOW()
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

INSERT INTO reward_discipline_transfer (
    title,
    status,
    points,
    description,
    created_at,
    updated_at,
    type,
    discipline_icon_id,
    discipline_icon_path,
    sort,
    teacher_desc,
    selected
)
SELECT
    d.title,
    'active',
    d.points,
    d.description,
    NOW(),
    NOW(),
    d.type,
    i.id,
    i.path,
    d.sort_order,
    0,
    0
FROM tq5_behavior_defaults d
JOIN discipline_icons i
  ON i.path = d.icon_path
WHERE @tq5_mdj_lms_refresh_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM reward_discipline_transfer rdt
      WHERE rdt.title = d.title
        AND rdt.type = d.type
  );

UPDATE reward_discipline_points rdp
JOIN tq5_behavior_title_map m
  ON m.old_title = rdp.title
 AND m.type = rdp.type
JOIN tq5_behavior_defaults d
  ON d.title = m.new_title
 AND d.type = m.type
JOIN discipline_icons i
  ON i.path = d.icon_path
SET rdp.title = d.title,
    rdp.points = d.points,
    rdp.description = d.description,
    rdp.discipline_icon_id = i.id,
    rdp.discipline_icon_path = i.path,
    rdp.sort = d.sort_order,
    rdp.status = 'active',
    rdp.updated_at = NOW()
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

UPDATE reward_discipline_points rdp
JOIN tq5_behavior_defaults d
  ON d.title = rdp.title
 AND d.type = rdp.type
JOIN discipline_icons i
  ON i.path = d.icon_path
SET rdp.points = d.points,
    rdp.description = d.description,
    rdp.discipline_icon_id = i.id,
    rdp.discipline_icon_path = i.path,
    rdp.sort = d.sort_order,
    rdp.status = 'active',
    rdp.updated_at = NOW()
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

INSERT INTO reward_discipline_points (
    title,
    status,
    student_id,
    points,
    description,
    created_at,
    updated_at,
    type,
    discipline_icon_id,
    discipline_icon_path,
    sort,
    teacher_desc,
    selected
)
SELECT
    d.title,
    'active',
    students_with_behavior.student_id,
    d.points,
    d.description,
    NOW(),
    NOW(),
    d.type,
    i.id,
    i.path,
    d.sort_order,
    0,
    0
FROM (
    SELECT DISTINCT student_id
    FROM reward_discipline_points
    WHERE student_id IS NOT NULL
) students_with_behavior
CROSS JOIN tq5_behavior_defaults d
JOIN discipline_icons i
  ON i.path = d.icon_path
WHERE @tq5_mdj_lms_refresh_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM reward_discipline_points rdp
      WHERE rdp.student_id = students_with_behavior.student_id
        AND rdp.title = d.title
        AND rdp.type = d.type
  );

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_agreement_defaults (
    type_title VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    PRIMARY KEY (type_title, title)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DELETE FROM tq5_agreement_defaults;

INSERT INTO tq5_agreement_defaults (type_title, sort_order, title) VALUES
('Minor Slip', 10, 'Lose 2-3 reward points'),
('Minor Slip', 20, 'Skip one fun activity or break time'),
('Minor Slip', 30, 'Write a short reflection on "What I can do better next time"'),
('Minor Slip', 40, 'Spend 10-15 minutes extra reviewing what I missed'),
('Minor Slip', 50, 'Say sorry in action (help someone or tidy the study area)'),
('Minor Slip', 60, 'No phone during study time today'),
('Minor Slip', 70, 'Lose PlayStation time for one day'),
('Minor Slip', 80, 'Ask parent/teacher for one feedback note on improvement tomorrow'),
('Minor Slip', 90, 'Repeat the missed Quran, Arabic, or Salah practice calmly'),
('Minor Slip', 100, 'Complete the missed family routine before leisure time'),
('Serious Action', 10, 'Lose 20-30 points or 1 full gift level'),
('Serious Action', 20, 'No phone or PlayStation for 3-5 days'),
('Serious Action', 30, 'No outings / training / sleepovers until a reflection plan is approved'),
('Serious Action', 40, 'Write a make-it-right plan and discuss it with parent and teacher'),
('Serious Action', 50, 'Do a helpful project (organize study materials, tutor someone younger, fix something at home)'),
('Serious Action', 60, 'Present verbally how you plan to rebuild trust next week'),
('Serious Action', 70, 'Temporary pause from special privileges (e.g., joining group games, being team leader)'),
('Serious Action', 80, 'Write 3 things learned from the incident and share one in class or at home'),
('Serious Action', 90, 'Parent reflection talk with student & teacher together'),
('Serious Action', 100, 'Give a verbal or written apology and explain what you learned');

CREATE TEMPORARY TABLE IF NOT EXISTS tq5_agreement_title_map (
    old_title VARCHAR(255) NOT NULL,
    type_title VARCHAR(255) NOT NULL,
    new_title VARCHAR(255) NOT NULL,
    PRIMARY KEY (old_title, type_title)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DELETE FROM tq5_agreement_title_map;

INSERT INTO tq5_agreement_title_map (old_title, type_title, new_title) VALUES
('The missed task is completed before screen time, games, or play.', 'Minor Slip', 'Lose 2-3 reward points'),
('Entertainment phone, tablet, or game use is paused until the task is completed.', 'Minor Slip', 'Skip one fun activity or break time'),
('A favorite activity or training may be paused for one time if the same slip repeats.', 'Minor Slip', 'Write a short reflection on "What I can do better next time"'),
('Use the meeting-defined consequence for this minor slip.', 'Minor Slip', 'Lose 2-3 reward points'),
('Short reflection and repair action agreed in the family meeting.', 'Minor Slip', 'Skip one fun activity or break time'),
('Repeat or repair the missed routine with parent support.', 'Minor Slip', 'Write a short reflection on "What I can do better next time"'),
('Entertainment phone, games, or tablet use is paused for 24 hours.', 'Serious Action', 'Lose 20-30 points or 1 full gift level'),
('A favorite activity, outing, or training is paused until the learner completes the repair step.', 'Serious Action', 'No phone or PlayStation for 3-5 days'),
('The learner makes things right through an apology, correction, or helping the affected person.', 'Serious Action', 'No outings / training / sleepovers until a reflection plan is approved'),
('Use the meeting-defined consequence for this serious action.', 'Serious Action', 'Lose 20-30 points or 1 full gift level'),
('Pause an agreed privilege according to the family meeting decision.', 'Serious Action', 'No phone or PlayStation for 3-5 days'),
('Repair the harm and discuss the next step with a parent.', 'Serious Action', 'No outings / training / sleepovers until a reflection plan is approved');

UPDATE punishments_suggestions ps
JOIN punishment_types pt
  ON pt.id = ps.punishment_type_id
JOIN tq5_agreement_title_map m
  ON m.old_title = ps.suggestion_text
 AND m.type_title = pt.title
SET ps.suggestion_text = m.new_title
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

INSERT INTO punishments_suggestions (punishment_type_id, suggestion_text)
SELECT pt.id, d.title
FROM tq5_agreement_defaults d
JOIN punishment_types pt
  ON pt.title = d.type_title
WHERE @tq5_mdj_lms_refresh_guard_ok = 1
  AND pt.active = 1
  AND NOT EXISTS (
      SELECT 1
      FROM punishments_suggestions ps
      WHERE ps.punishment_type_id = pt.id
        AND ps.suggestion_text = d.title
  )
ORDER BY pt.id, d.sort_order;

UPDATE punishment_agreements pa
JOIN punishment_types pt
  ON pt.id = pa.punishment_type_id
JOIN tq5_agreement_title_map m
  ON m.old_title = pa.title
 AND m.type_title = pt.title
SET pa.title = m.new_title
WHERE @tq5_mdj_lms_refresh_guard_ok = 1;

INSERT INTO punishment_agreements (student_id, title, punishment_type_id, status)
SELECT students_with_agreements.student_id, d.title, pt.id, 'active'
FROM (
    SELECT DISTINCT student_id
    FROM punishment_agreements
    WHERE student_id IS NOT NULL
) students_with_agreements
JOIN punishment_types pt
  ON pt.active = 1
JOIN tq5_agreement_defaults d
  ON d.type_title = pt.title
WHERE @tq5_mdj_lms_refresh_guard_ok = 1
  AND NOT EXISTS (
      SELECT 1
      FROM punishment_agreements pa
      WHERE pa.student_id = students_with_agreements.student_id
        AND pa.punishment_type_id = pt.id
        AND pa.title = d.title
  )
ORDER BY students_with_agreements.student_id, pt.id, d.sort_order;

SELECT type, COUNT(*) AS starter_behavior_count
FROM reward_discipline_transfer
WHERE type IN ('Positive', 'Slip', 'No Way')
GROUP BY type
ORDER BY FIELD(type, 'Positive', 'Slip', 'No Way');

SELECT type, COUNT(*) AS copied_behavior_count
FROM reward_discipline_points
WHERE type IN ('Positive', 'Slip', 'No Way')
GROUP BY type
ORDER BY FIELD(type, 'Positive', 'Slip', 'No Way');

SELECT pt.title AS punishment_type, COUNT(*) AS suggestion_count
FROM punishment_types pt
JOIN punishments_suggestions ps ON ps.punishment_type_id = pt.id
WHERE pt.title IN ('Minor Slip', 'Serious Action')
GROUP BY pt.title
ORDER BY FIELD(pt.title, 'Minor Slip', 'Serious Action');

SELECT pt.title AS punishment_type, COUNT(*) AS copied_agreement_count
FROM punishment_types pt
JOIN punishment_agreements pa ON pa.punishment_type_id = pt.id
WHERE pt.title IN ('Minor Slip', 'Serious Action')
GROUP BY pt.title
ORDER BY FIELD(pt.title, 'Minor Slip', 'Serious Action');
