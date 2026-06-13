-- TQ6 general app Library and Quran Memorization content schema
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-06-231654-u504065335_to_quran-before-tq6-general-library-structure.sql
--
-- Purpose:
--   Create To Quran-owned app Library tables for shared teacher resources and
--   editable Quran Memorization video content. These tables are intentionally
--   separate from inherited Week14 subject-owned library_sections/resources.
--
-- Safety:
--   - guarded to the real To Quran app DB name;
--   - create-only / additive;
--   - no destructive cleanup;
--   - foreign keys protect owner/folder/resource relationships.

SET @tq6_general_library_guard_ok := (DATABASE() = 'u504065335_to_quran');

SELECT
    CASE
        WHEN @tq6_general_library_guard_ok = 1
            THEN 'TQ6 general Library schema guard passed.'
        ELSE 'REFUSING TQ6 general Library schema patch: wrong target DB.'
    END AS tq6_general_library_guard;

DELIMITER //
CREATE PROCEDURE tq6_general_library_guard_or_fail()
BEGIN
    IF @tq6_general_library_guard_ok <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ6 general Library schema patch: wrong target DB.';
    END IF;
END//
DELIMITER ;

CALL tq6_general_library_guard_or_fail();
DROP PROCEDURE tq6_general_library_guard_or_fail;

SET @tq6_previous_foreign_key_checks := @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS general_library_folders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500) NULL,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    source_label VARCHAR(40) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by_user_id INT NOT NULL,
    updated_by_user_id INT NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY general_library_folders_parent_status_sort_idx (parent_id, status, sort_order),
    KEY general_library_folders_creator_status_idx (created_by_user_id, status),
    KEY general_library_folders_status_sort_idx (status, sort_order),
    CONSTRAINT general_library_folders_parent_fk
        FOREIGN KEY (parent_id) REFERENCES general_library_folders (id)
        ON DELETE RESTRICT,
    CONSTRAINT general_library_folders_created_by_fk
        FOREIGN KEY (created_by_user_id) REFERENCES users (id)
        ON DELETE RESTRICT,
    CONSTRAINT general_library_folders_updated_by_fk
        FOREIGN KEY (updated_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS general_library_resources (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    general_library_folder_id BIGINT UNSIGNED NULL,
    resource_type ENUM('file', 'link', 'youtube') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500) NULL,
    status ENUM('active', 'archived', 'unavailable') NOT NULL DEFAULT 'active',
    source_label VARCHAR(40) NULL,
    storage_disk VARCHAR(40) NULL,
    file_path VARCHAR(1024) NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(255) NULL,
    file_size BIGINT UNSIGNED NULL,
    external_url VARCHAR(2048) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by_user_id INT NOT NULL,
    updated_by_user_id INT NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY general_library_resources_folder_status_sort_idx (general_library_folder_id, status, sort_order),
    KEY general_library_resources_creator_status_idx (created_by_user_id, status),
    KEY general_library_resources_status_sort_idx (status, sort_order),
    KEY general_library_resources_file_path_idx (file_path(191)),
    CONSTRAINT general_library_resources_folder_fk
        FOREIGN KEY (general_library_folder_id) REFERENCES general_library_folders (id)
        ON DELETE RESTRICT,
    CONSTRAINT general_library_resources_created_by_fk
        FOREIGN KEY (created_by_user_id) REFERENCES users (id)
        ON DELETE RESTRICT,
    CONSTRAINT general_library_resources_updated_by_fk
        FOREIGN KEY (updated_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quran_library_surahs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    surah_number TINYINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255) NULL,
    ayah_count SMALLINT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    source_label VARCHAR(40) NULL DEFAULT 'Original',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by_user_id INT NULL,
    updated_by_user_id INT NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY quran_library_surahs_surah_number_unique (surah_number),
    KEY quran_library_surahs_status_sort_idx (status, sort_order),
    CONSTRAINT quran_library_surahs_created_by_fk
        FOREIGN KEY (created_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL,
    CONSTRAINT quran_library_surahs_updated_by_fk
        FOREIGN KEY (updated_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quran_library_videos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    quran_library_surah_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NULL,
    ayah_from SMALLINT UNSIGNED NULL,
    ayah_to SMALLINT UNSIGNED NULL,
    youtube_url VARCHAR(2048) NOT NULL,
    youtube_embed_url VARCHAR(2048) NOT NULL,
    description VARCHAR(500) NULL,
    status ENUM('active', 'archived', 'unavailable') NOT NULL DEFAULT 'active',
    source_label VARCHAR(40) NULL DEFAULT 'Original',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by_user_id INT NULL,
    updated_by_user_id INT NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY quran_library_videos_surah_status_sort_idx (quran_library_surah_id, status, sort_order),
    KEY quran_library_videos_status_sort_idx (status, sort_order),
    CONSTRAINT quran_library_videos_surah_fk
        FOREIGN KEY (quran_library_surah_id) REFERENCES quran_library_surahs (id)
        ON DELETE RESTRICT,
    CONSTRAINT quran_library_videos_created_by_fk
        FOREIGN KEY (created_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL,
    CONSTRAINT quran_library_videos_updated_by_fk
        FOREIGN KEY (updated_by_user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = @tq6_previous_foreign_key_checks;
