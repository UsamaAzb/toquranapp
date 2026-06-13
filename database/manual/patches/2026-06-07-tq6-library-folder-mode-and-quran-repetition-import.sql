-- TQ6 Library folder mode and Quran Repetition import
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-07-121006-u504065335_to_quran-before-tq6-library-folder-quran-import.sql
--
-- Purpose:
--   Add sources-only final folder mode and import the preserved Quran
--   repetition YouTube list into the general shared Library as folders/resources.
--
-- Safety:
--   - guarded to the real To Quran app DB name with explicit operator variable;
--   - additive/idempotent;
--   - no deletion or cleanup;
--   - uses preserved Quran SQL as data source, not as schema.

SET @tq6_operator_confirmed_db := 'u504065335_to_quran';
SET @tq6_library_quran_guard_ok := (DATABASE() = @tq6_operator_confirmed_db);

SELECT CASE WHEN @tq6_library_quran_guard_ok = 1 THEN 'TQ6 Library Quran import guard passed.' ELSE 'REFUSING TQ6 Library Quran import: wrong target DB.' END AS tq6_library_quran_guard;

DELIMITER //
CREATE PROCEDURE tq6_library_quran_guard_or_fail()
BEGIN
    IF @tq6_library_quran_guard_ok <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'REFUSING TQ6 Library Quran import: wrong target DB.';
    END IF;
END//
DELIMITER ;
CALL tq6_library_quran_guard_or_fail();
DROP PROCEDURE tq6_library_quran_guard_or_fail;

SET @tq6_library_admin_user_id := (
    SELECT u.id
    FROM users u
    JOIN model_has_roles mhr ON mhr.model_id = u.id AND mhr.model_type = 'App\\Models\\User'
    JOIN roles r ON r.id = mhr.role_id
    WHERE r.name IN ('super_admin', 'admin')
    ORDER BY CASE WHEN r.name = 'super_admin' THEN 0 ELSE 1 END, u.id
    LIMIT 1
);

DELIMITER //
CREATE PROCEDURE tq6_library_admin_or_fail()
BEGIN
    IF @tq6_library_admin_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'REFUSING TQ6 Library Quran import: no admin/superadmin user found.';
    END IF;
END//
DELIMITER ;
CALL tq6_library_admin_or_fail();
DROP PROCEDURE tq6_library_admin_or_fail;

ALTER TABLE general_library_folders
    ADD COLUMN IF NOT EXISTS content_mode ENUM('mixed', 'sources_only') NOT NULL DEFAULT 'mixed' AFTER source_label;

UPDATE general_library_folders f
SET f.content_mode = 'sources_only'
WHERE EXISTS (
    SELECT 1 FROM general_library_resources r
    WHERE r.general_library_folder_id = f.id
      AND r.status <> 'archived'
)
AND f.content_mode <> 'sources_only';

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT NULL, 'Quran Repetition', 'Original Quran Memorization repetition videos preserved from the old To Quran Quran list.', 'active', 'Original', 'mixed', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id IS NULL AND title = 'Quran Repetition');
SET @quran_repetition_folder_id := (SELECT id FROM general_library_folders WHERE parent_id IS NULL AND title = 'Quran Repetition' ORDER BY id LIMIT 1);

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '001. Al-Faatiha', 'Quran repetition videos for Surah Al-Faatiha (7 ayahs).', 'active', 'Original', 'sources_only', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '001. Al-Faatiha');
SET @surah_folder_1 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '001. Al-Faatiha' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_1, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Faatiha, Ayahs 1-3.', 'active', 'Original', 'https://www.youtube.com/embed/ZRKWcTbYwT4', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_1 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/ZRKWcTbYwT4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_1, 'youtube', 'Ayahs 4-6', 'Original repetition video for Al-Faatiha, Ayahs 4-6.', 'active', 'Original', 'https://www.youtube.com/embed/xRPhXuDH6Ho', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_1 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/xRPhXuDH6Ho');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_1, 'youtube', 'Ayah 7', 'Original repetition video for Al-Faatiha, Ayah 7.', 'active', 'Original', 'https://www.youtube.com/embed/6e7LrJR5pGQ', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_1 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/6e7LrJR5pGQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_1, 'youtube', 'Full Surah', 'Original repetition video for Al-Faatiha, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/rxOvQIPiNLU', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_1 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/rxOvQIPiNLU');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '096. Al-Alaq', 'Quran repetition videos for Surah Al-Alaq (19 ayahs).', 'active', 'Original', 'sources_only', 960, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '096. Al-Alaq');
SET @surah_folder_96 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '096. Al-Alaq' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Alaq, Ayahs 1-2.', 'active', 'Original', 'https://youtube.com/embed/o6ZtqCc8I3M', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/o6ZtqCc8I3M');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 3-4', 'Original repetition video for Al-Alaq, Ayahs 3-4.', 'active', 'Original', 'https://youtube.com/embed/IBOT-utvy3w', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/IBOT-utvy3w');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 5-6', 'Original repetition video for Al-Alaq, Ayahs 5-6.', 'active', 'Original', 'https://youtube.com/embed/NaomuQtz9AY', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/NaomuQtz9AY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 7-8', 'Original repetition video for Al-Alaq, Ayahs 7-8.', 'active', 'Original', 'https://youtube.com/embed/fX5s0u875dE', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/fX5s0u875dE');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 9-10', 'Original repetition video for Al-Alaq, Ayahs 9-10.', 'active', 'Original', 'https://youtube.com/embed/zMQUXQXYkck', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/zMQUXQXYkck');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 1-6', 'Original repetition video for Al-Alaq, Ayahs 1-6.', 'active', 'Original', 'https://youtube.com/embed/b6f0d32YonQ', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/b6f0d32YonQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 11-12', 'Original repetition video for Al-Alaq, Ayahs 11-12.', 'active', 'Original', 'https://youtube.com/embed/R22KqtKfAKs', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/R22KqtKfAKs');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 1-12', 'Original repetition video for Al-Alaq, Ayahs 1-12.', 'active', 'Original', 'https://youtube.com/embed/RkJgJx7rQGo', 80, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/RkJgJx7rQGo');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 13-14', 'Original repetition video for Al-Alaq, Ayahs 13-14.', 'active', 'Original', 'https://youtube.com/embed/usIan7FdpuI', 90, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/usIan7FdpuI');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 15-16', 'Original repetition video for Al-Alaq, Ayahs 15-16.', 'active', 'Original', 'https://youtube.com/embed/jLOV_w3aSZU', 100, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/jLOV_w3aSZU');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 17-19', 'Original repetition video for Al-Alaq, Ayahs 17-19.', 'active', 'Original', 'https://youtube.com/embed/GcDCTL6HCs8', 110, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/GcDCTL6HCs8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Ayahs 13-19', 'Original repetition video for Al-Alaq, Ayahs 13-19.', 'active', 'Original', 'https://youtube.com/embed/k7J8HtL8Q5A', 120, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/k7J8HtL8Q5A');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_96, 'youtube', 'Full Surah', 'Original repetition video for Al-Alaq, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/tWR3jdZj4YM', 130, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_96 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/tWR3jdZj4YM');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '097. Al-Qadr', 'Quran repetition videos for Surah Al-Qadr (5 ayahs).', 'active', 'Original', 'sources_only', 970, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '097. Al-Qadr');
SET @surah_folder_97 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '097. Al-Qadr' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_97, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Qadr, Ayahs 1-2.', 'active', 'Original', 'https://youtube.com/embed/M0mkG0DWw4A', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_97 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/M0mkG0DWw4A');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_97, 'youtube', 'Ayah 3', 'Original repetition video for Al-Qadr, Ayah 3.', 'active', 'Original', 'https://youtube.com/embed/EdmoRHo4S_A', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_97 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/EdmoRHo4S_A');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_97, 'youtube', 'Ayah 4', 'Original repetition video for Al-Qadr, Ayah 4.', 'active', 'Original', 'https://youtube.com/embed/IccmY7WgGbY', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_97 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/IccmY7WgGbY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_97, 'youtube', 'Ayah 5', 'Original repetition video for Al-Qadr, Ayah 5.', 'active', 'Original', 'https://youtube.com/embed/NMh0NxVCAmY', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_97 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/NMh0NxVCAmY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_97, 'youtube', 'Full Surah', 'Original repetition video for Al-Qadr, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/s3dBSIGmCms', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_97 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/s3dBSIGmCms');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '098. Al-Bayyina', 'Quran repetition videos for Surah Al-Bayyina (8 ayahs).', 'active', 'Original', 'sources_only', 980, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '098. Al-Bayyina');
SET @surah_folder_98 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '098. Al-Bayyina' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 1', 'Original repetition video for Al-Bayyina, Ayah 1.', 'active', 'Original', 'https://youtube.com/embed/xr_wZsIYKus', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/xr_wZsIYKus');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 2-3', 'Original repetition video for Al-Bayyina, Ayahs 2-3.', 'active', 'Original', 'https://youtube.com/embed/MlEp6_ss9Y8', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/MlEp6_ss9Y8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Bayyina, Ayahs 1-3.', 'active', 'Original', 'https://youtube.com/embed/UTVbXp8x9f0', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/UTVbXp8x9f0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 4', 'Original repetition video for Al-Bayyina, Ayah 4.', 'active', 'Original', 'https://youtube.com/embed/BSavFZdaTTc', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/BSavFZdaTTc');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 1-4', 'Original repetition video for Al-Bayyina, Ayahs 1-4.', 'active', 'Original', 'https://youtube.com/embed/tm9WF3AScX0', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/tm9WF3AScX0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 5', 'Original repetition video for Al-Bayyina, Ayah 5.', 'active', 'Original', 'https://youtube.com/embed/Aux9AV8U3qA', 60, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/Aux9AV8U3qA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 1-5', 'Original repetition video for Al-Bayyina, Ayahs 1-5.', 'active', 'Original', 'https://youtube.com/embed/XJPDFlJ50pM', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/XJPDFlJ50pM');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 6', 'Original repetition video for Al-Bayyina, Ayah 6.', 'active', 'Original', 'https://youtube.com/embed/rlohTeyiot8', 80, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/rlohTeyiot8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 1-6', 'Original repetition video for Al-Bayyina, Ayahs 1-6.', 'active', 'Original', 'https://youtube.com/embed/0AMDkD0siLM', 90, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/0AMDkD0siLM');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 7', 'Original repetition video for Al-Bayyina, Ayah 7.', 'active', 'Original', 'https://youtube.com/embed/ArenY50u_Lk', 100, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/ArenY50u_Lk');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayahs 1-7', 'Original repetition video for Al-Bayyina, Ayahs 1-7.', 'active', 'Original', 'https://youtube.com/embed/CJrkLTRKtow', 110, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/CJrkLTRKtow');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Ayah 8', 'Original repetition video for Al-Bayyina, Ayah 8.', 'active', 'Original', 'https://youtube.com/embed/zRE-FdVoDyM', 120, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/zRE-FdVoDyM');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_98, 'youtube', 'Full Surah', 'Original repetition video for Al-Bayyina, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/fzCPZWCat30', 130, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_98 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/fzCPZWCat30');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '099. Az-Zalzala', 'Quran repetition videos for Surah Az-Zalzala (8 ayahs).', 'active', 'Original', 'sources_only', 990, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '099. Az-Zalzala');
SET @surah_folder_99 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '099. Az-Zalzala' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 1-2', 'Original repetition video for Az-Zalzala, Ayahs 1-2.', 'active', 'Original', 'https://youtube.com/embed/WcvounSov0Q', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/WcvounSov0Q');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 3-4', 'Original repetition video for Az-Zalzala, Ayahs 3-4.', 'active', 'Original', 'https://youtube.com/embed/NrWFtVaVaIU', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/NrWFtVaVaIU');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 1-4', 'Original repetition video for Az-Zalzala, Ayahs 1-4.', 'active', 'Original', 'https://youtube.com/embed/c8dl8yyhwtg', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/c8dl8yyhwtg');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 5-6', 'Original repetition video for Az-Zalzala, Ayahs 5-6.', 'active', 'Original', 'https://youtube.com/embed/2EGrGB8o3Jc', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/2EGrGB8o3Jc');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 1-6', 'Original repetition video for Az-Zalzala, Ayahs 1-6.', 'active', 'Original', 'https://youtube.com/embed/9F5erCQfRJ8', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/9F5erCQfRJ8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Ayahs 7-8', 'Original repetition video for Az-Zalzala, Ayahs 7-8.', 'active', 'Original', 'https://youtube.com/embed/LMZrsH-mRP4', 60, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/LMZrsH-mRP4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_99, 'youtube', 'Full Surah', 'Original repetition video for Az-Zalzala, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/_nCAsakSmz4', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_99 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/_nCAsakSmz4');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '100. Al-Aadiyaat', 'Quran repetition videos for Surah Al-Aadiyaat (11 ayahs).', 'active', 'Original', 'sources_only', 1000, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '100. Al-Aadiyaat');
SET @surah_folder_100 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '100. Al-Aadiyaat' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Aadiyaat, Ayahs 1-3.', 'active', 'Original', 'https://youtube.com/embed/WvOhnQwAK84', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/WvOhnQwAK84');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 4-5', 'Original repetition video for Al-Aadiyaat, Ayahs 4-5.', 'active', 'Original', 'https://youtube.com/embed/pWvnK9mKXbg', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/pWvnK9mKXbg');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 1-5', 'Original repetition video for Al-Aadiyaat, Ayahs 1-5.', 'active', 'Original', 'https://youtube.com/embed/2hwChOwRls4', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/2hwChOwRls4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 6-7', 'Original repetition video for Al-Aadiyaat, Ayahs 6-7.', 'active', 'Original', 'https://youtube.com/embed/uQkmgrUMqg0', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/uQkmgrUMqg0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 1-7', 'Original repetition video for Al-Aadiyaat, Ayahs 1-7.', 'active', 'Original', 'https://youtube.com/embed/QNFuLawgxCc', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/QNFuLawgxCc');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 8-9', 'Original repetition video for Al-Aadiyaat, Ayahs 8-9.', 'active', 'Original', 'https://youtube.com/embed/VUsVZHVe5S8', 60, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/VUsVZHVe5S8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 1-9', 'Original repetition video for Al-Aadiyaat, Ayahs 1-9.', 'active', 'Original', 'https://youtube.com/embed/4-lphiYTDEA', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/4-lphiYTDEA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Ayahs 10-11', 'Original repetition video for Al-Aadiyaat, Ayahs 10-11.', 'active', 'Original', 'https://youtube.com/embed/py5eVPqsZT4', 80, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/py5eVPqsZT4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_100, 'youtube', 'Full Surah', 'Original repetition video for Al-Aadiyaat, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/HTjcD2-qkwY', 90, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_100 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/HTjcD2-qkwY');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '101. Al-Qaari''a', 'Quran repetition videos for Surah Al-Qaari''a (11 ayahs).', 'active', 'Original', 'sources_only', 1010, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '101. Al-Qaari''a');
SET @surah_folder_101 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '101. Al-Qaari''a' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Qaari''a, Ayahs 1-3.', 'active', 'Original', 'https://youtube.com/embed/Qk6ISimZUAU', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/Qk6ISimZUAU');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 4-5', 'Original repetition video for Al-Qaari''a, Ayahs 4-5.', 'active', 'Original', 'https://youtube.com/embed/suGKK7UojSw', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/suGKK7UojSw');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 1-5', 'Original repetition video for Al-Qaari''a, Ayahs 1-5.', 'active', 'Original', 'https://youtube.com/embed/Nl9tmuyQQU8', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/Nl9tmuyQQU8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 6-7', 'Original repetition video for Al-Qaari''a, Ayahs 6-7.', 'active', 'Original', 'https://youtube.com/embed/5qUQjr21QqM', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/5qUQjr21QqM');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 1-7', 'Original repetition video for Al-Qaari''a, Ayahs 1-7.', 'active', 'Original', 'https://youtube.com/embed/H8z8Plbg0DI', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/H8z8Plbg0DI');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 8-9', 'Original repetition video for Al-Qaari''a, Ayahs 8-9.', 'active', 'Original', 'https://youtube.com/embed/7ujD11mVKsw', 60, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/7ujD11mVKsw');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 1-9', 'Original repetition video for Al-Qaari''a, Ayahs 1-9.', 'active', 'Original', 'https://youtube.com/embed/VzKb0cS7AtM', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/VzKb0cS7AtM');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Ayahs 10-11', 'Original repetition video for Al-Qaari''a, Ayahs 10-11.', 'active', 'Original', 'https://youtube.com/embed/Trv1ZBAixXQ', 80, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/Trv1ZBAixXQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_101, 'youtube', 'Full Surah', 'Original repetition video for Al-Qaari''a, Full Surah.', 'active', 'Original', 'https://youtube.com/embed/LbAqb_FEcNI', 90, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_101 AND resource_type = 'youtube' AND external_url = 'https://youtube.com/embed/LbAqb_FEcNI');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '102. At-Takaathur', 'Quran repetition videos for Surah At-Takaathur (8 ayahs).', 'active', 'Original', 'sources_only', 1020, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '102. At-Takaathur');
SET @surah_folder_102 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '102. At-Takaathur' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 1-2', 'Original repetition video for At-Takaathur, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/HudsDhMcUvo', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/HudsDhMcUvo');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 3-4', 'Original repetition video for At-Takaathur, Ayahs 3-4.', 'active', 'Original', 'https://www.youtube.com/embed/mvWyJYQOTUA', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/mvWyJYQOTUA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 1-4', 'Original repetition video for At-Takaathur, Ayahs 1-4.', 'active', 'Original', 'https://www.youtube.com/embed/SX1hD9MPfKQ', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/SX1hD9MPfKQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 5-6', 'Original repetition video for At-Takaathur, Ayahs 5-6.', 'active', 'Original', 'https://www.youtube.com/embed/XH0Vc6r_UpE', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/XH0Vc6r_UpE');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 1-6', 'Original repetition video for At-Takaathur, Ayahs 1-6.', 'active', 'Original', 'https://www.youtube.com/embed/3a8gL5vTix4', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/3a8gL5vTix4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Ayahs 7-8', 'Original repetition video for At-Takaathur, Ayahs 7-8.', 'active', 'Original', 'https://www.youtube.com/embed/WGqcELIa_PU', 60, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/WGqcELIa_PU');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_102, 'youtube', 'Full Surah', 'Original repetition video for At-Takaathur, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/l2qJMjIzfI8', 70, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_102 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/l2qJMjIzfI8');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '103. Al-Asr', 'Quran repetition videos for Surah Al-Asr (3 ayahs).', 'active', 'Original', 'sources_only', 1030, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '103. Al-Asr');
SET @surah_folder_103 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '103. Al-Asr' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_103, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Asr, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/FCD1tS-eGAY', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_103 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/FCD1tS-eGAY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_103, 'youtube', 'Ayah 3', 'Original repetition video for Al-Asr, Ayah 3.', 'active', 'Original', 'https://www.youtube.com/embed/4ASDJPw9LGA', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_103 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/4ASDJPw9LGA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_103, 'youtube', 'Full Surah', 'Original repetition video for Al-Asr, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/8-y3yuyBUI4', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_103 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/8-y3yuyBUI4');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '104. Al-Humaza', 'Quran repetition videos for Surah Al-Humaza (9 ayahs).', 'active', 'Original', 'sources_only', 1040, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '104. Al-Humaza');
SET @surah_folder_104 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '104. Al-Humaza' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_104, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Humaza, Ayahs 1-3.', 'active', 'Original', 'https://www.youtube.com/embed/YY1PA-JABZ0', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_104 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/YY1PA-JABZ0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_104, 'youtube', 'Ayahs 4-6', 'Original repetition video for Al-Humaza, Ayahs 4-6.', 'active', 'Original', 'https://www.youtube.com/embed/xEEFxoWt5vk', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_104 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/xEEFxoWt5vk');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_104, 'youtube', 'Ayahs 7-9', 'Original repetition video for Al-Humaza, Ayahs 7-9.', 'active', 'Original', 'https://www.youtube.com/embed/xDpCfTESofk', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_104 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/xDpCfTESofk');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_104, 'youtube', 'Ayahs 1-6', 'Original repetition video for Al-Humaza, Ayahs 1-6.', 'active', 'Original', 'https://www.youtube.com/embed/AI6bPbLmvQQ', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_104 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/AI6bPbLmvQQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_104, 'youtube', 'Full Surah', 'Original repetition video for Al-Humaza, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/Zcpo5SevPLM', 50, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_104 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/Zcpo5SevPLM');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '105. Al-Fil', 'Quran repetition videos for Surah Al-Fil (5 ayahs).', 'active', 'Original', 'sources_only', 1050, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '105. Al-Fil');
SET @surah_folder_105 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '105. Al-Fil' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_105, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Fil, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/WRoD_puhZOQ', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_105 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/WRoD_puhZOQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_105, 'youtube', 'Ayahs 3-4', 'Original repetition video for Al-Fil, Ayahs 3-4.', 'active', 'Original', 'https://www.youtube.com/embed/Iu_HeT6ItWo', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_105 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/Iu_HeT6ItWo');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_105, 'youtube', 'Ayah 5', 'Original repetition video for Al-Fil, Ayah 5.', 'active', 'Original', 'https://www.youtube.com/embed/zyb7G6ZHhoQ', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_105 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/zyb7G6ZHhoQ');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_105, 'youtube', 'Full Surah', 'Original repetition video for Al-Fil, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/g4r5sq3-ekY', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_105 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/g4r5sq3-ekY');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '106. Quraish', 'Quran repetition videos for Surah Quraish (4 ayahs).', 'active', 'Original', 'sources_only', 1060, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '106. Quraish');
SET @surah_folder_106 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '106. Quraish' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_106, 'youtube', 'Ayahs 1-2', 'Original repetition video for Quraish, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/k7UhcDi8leY', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_106 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/k7UhcDi8leY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_106, 'youtube', 'Ayahs 3-4', 'Original repetition video for Quraish, Ayahs 3-4.', 'active', 'Original', 'https://www.youtube.com/embed/0ToX7hxJ12E', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_106 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/0ToX7hxJ12E');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_106, 'youtube', 'Full Surah', 'Original repetition video for Quraish, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/AaUSiA6I5TA', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_106 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/AaUSiA6I5TA');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '107. Al-Maa''un', 'Quran repetition videos for Surah Al-Maa''un (7 ayahs).', 'active', 'Original', 'sources_only', 1070, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '107. Al-Maa''un');
SET @surah_folder_107 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '107. Al-Maa''un' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_107, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Maa''un, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/LW85dro-v6E', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_107 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/LW85dro-v6E');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_107, 'youtube', 'Ayahs 3-5', 'Original repetition video for Al-Maa''un, Ayahs 3-5.', 'active', 'Original', 'https://www.youtube.com/embed/yNtRbS7wdjY', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_107 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/yNtRbS7wdjY');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_107, 'youtube', 'Ayahs 6-7', 'Original repetition video for Al-Maa''un, Ayahs 6-7.', 'active', 'Original', 'https://www.youtube.com/embed/e90ici28rQk', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_107 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/e90ici28rQk');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_107, 'youtube', 'Full Surah', 'Original repetition video for Al-Maa''un, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/0WciWcHMiUc', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_107 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/0WciWcHMiUc');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_107, 'youtube', 'Ayahs 1-5', 'Original repetition video for Al-Maa''un, Ayahs 1-5.', 'active', 'Original', 'https://www.youtube.com/embed/e-5Xs-Tjji4', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_107 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/e-5Xs-Tjji4');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '108. Al-Kawthar', 'Quran repetition videos for Surah Al-Kawthar (3 ayahs).', 'active', 'Original', 'sources_only', 1080, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '108. Al-Kawthar');
SET @surah_folder_108 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '108. Al-Kawthar' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_108, 'youtube', 'Full Surah', 'Original repetition video for Al-Kawthar, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/dl8s0ugXJEM', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_108 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/dl8s0ugXJEM');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '109. Al-Kaafiroon', 'Quran repetition videos for Surah Al-Kaafiroon (6 ayahs).', 'active', 'Original', 'sources_only', 1090, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '109. Al-Kaafiroon');
SET @surah_folder_109 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '109. Al-Kaafiroon' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_109, 'youtube', 'Ayahs 1-2', 'Original repetition video for Al-Kaafiroon, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/XiO4-y2QouA', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_109 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/XiO4-y2QouA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_109, 'youtube', 'Ayahs 3-4', 'Original repetition video for Al-Kaafiroon, Ayahs 3-4.', 'active', 'Original', 'https://www.youtube.com/embed/lK_vKRFnQF4', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_109 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/lK_vKRFnQF4');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_109, 'youtube', 'Ayahs 5-6', 'Original repetition video for Al-Kaafiroon, Ayahs 5-6.', 'active', 'Original', 'https://www.youtube.com/embed/BvVIn6alAe0', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_109 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/BvVIn6alAe0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_109, 'youtube', 'Full Surah', 'Original repetition video for Al-Kaafiroon, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/N18x8L242Ss', 40, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_109 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/N18x8L242Ss');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_109, 'youtube', 'Ayahs 1-4', 'Original repetition video for Al-Kaafiroon, Ayahs 1-4.', 'active', 'Original', 'https://www.youtube.com/embed/REKbQWbnFCw', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_109 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/REKbQWbnFCw');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '110. An-Nasr', 'Quran repetition videos for Surah An-Nasr (3 ayahs).', 'active', 'Original', 'sources_only', 1100, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '110. An-Nasr');
SET @surah_folder_110 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '110. An-Nasr' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_110, 'youtube', 'Ayahs 1-2', 'Original repetition video for An-Nasr, Ayahs 1-2.', 'active', 'Original', 'https://www.youtube.com/embed/77IZISxxkwU', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_110 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/77IZISxxkwU');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_110, 'youtube', 'Ayah 3', 'Original repetition video for An-Nasr, Ayah 3.', 'active', 'Original', 'https://www.youtube.com/embed/twVeYJj6mKE', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_110 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/twVeYJj6mKE');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_110, 'youtube', 'Full Surah', 'Original repetition video for An-Nasr, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/hWxOzFI_cQE', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_110 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/hWxOzFI_cQE');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '111. Al-Masad', 'Quran repetition videos for Surah Al-Masad (5 ayahs).', 'active', 'Original', 'sources_only', 1110, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '111. Al-Masad');
SET @surah_folder_111 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '111. Al-Masad' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_111, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Masad, Ayahs 1-3.', 'active', 'Original', 'https://www.youtube.com/embed/DpeQzSsyWtA', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_111 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/DpeQzSsyWtA');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_111, 'youtube', 'Ayahs 4-5', 'Original repetition video for Al-Masad, Ayahs 4-5.', 'active', 'Original', 'https://www.youtube.com/embed/CatsXQjAFps', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_111 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/CatsXQjAFps');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_111, 'youtube', 'Full Surah', 'Original repetition video for Al-Masad, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/gLDCUHUBJnM', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_111 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/gLDCUHUBJnM');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '112. Al-Ikhlaas', 'Quran repetition videos for Surah Al-Ikhlaas (4 ayahs).', 'active', 'Original', 'sources_only', 1120, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '112. Al-Ikhlaas');
SET @surah_folder_112 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '112. Al-Ikhlaas' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_112, 'youtube', 'Full Surah', 'Original repetition video for Al-Ikhlaas, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/0V6wPdSuUuE', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_112 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/0V6wPdSuUuE');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '113. Al-Falaq', 'Quran repetition videos for Surah Al-Falaq (5 ayahs).', 'active', 'Original', 'sources_only', 1130, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '113. Al-Falaq');
SET @surah_folder_113 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '113. Al-Falaq' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_113, 'youtube', 'Ayahs 1-3', 'Original repetition video for Al-Falaq, Ayahs 1-3.', 'active', 'Original', 'https://www.youtube.com/embed/X9UHC9rcgz0', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_113 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/X9UHC9rcgz0');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_113, 'youtube', 'Ayahs 4-5', 'Original repetition video for Al-Falaq, Ayahs 4-5.', 'active', 'Original', 'https://www.youtube.com/embed/sO1mwTJIOl8', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_113 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/sO1mwTJIOl8');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_113, 'youtube', 'Full Surah', 'Original repetition video for Al-Falaq, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/MTPkkrC2FSg', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_113 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/MTPkkrC2FSg');

INSERT INTO general_library_folders (parent_id, title, description, status, source_label, content_mode, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @quran_repetition_folder_id, '114. An-Naas', 'Quran repetition videos for Surah An-Naas (6 ayahs).', 'active', 'Original', 'sources_only', 1140, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '114. An-Naas');
SET @surah_folder_114 := (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id AND title = '114. An-Naas' ORDER BY id LIMIT 1);
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_114, 'youtube', 'Ayahs 1-3', 'Original repetition video for An-Naas, Ayahs 1-3.', 'active', 'Original', 'https://www.youtube.com/embed/AQhYb-SZPpc', 10, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_114 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/AQhYb-SZPpc');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_114, 'youtube', 'Ayahs 4-6', 'Original repetition video for An-Naas, Ayahs 4-6.', 'active', 'Original', 'https://www.youtube.com/embed/MOvwzu9I66U', 20, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_114 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/MOvwzu9I66U');
INSERT INTO general_library_resources (general_library_folder_id, resource_type, title, description, status, source_label, external_url, sort_order, created_by_user_id, updated_by_user_id, created_at, updated_at)
SELECT @surah_folder_114, 'youtube', 'Full Surah', 'Original repetition video for An-Naas, Full Surah.', 'active', 'Original', 'https://www.youtube.com/embed/yPSUqmb42GU', 30, @tq6_library_admin_user_id, @tq6_library_admin_user_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM general_library_resources WHERE general_library_folder_id = @surah_folder_114 AND resource_type = 'youtube' AND external_url = 'https://www.youtube.com/embed/yPSUqmb42GU');

SELECT COUNT(*) AS quran_repetition_folders FROM general_library_folders WHERE source_label = 'Original' AND (title = 'Quran Repetition' OR parent_id = @quran_repetition_folder_id);
SELECT COUNT(*) AS quran_repetition_sources FROM general_library_resources WHERE source_label = 'Original' AND general_library_folder_id IN (SELECT id FROM general_library_folders WHERE parent_id = @quran_repetition_folder_id);
