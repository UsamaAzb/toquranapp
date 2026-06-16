-- TQ7.5 automation starter catalog registry
-- Target: u504065335_to_quran
--
-- Status: reviewed manual artifact; not executed by Codex during implementation.
--
-- Before execution:
--   1. Create/confirm a focused backup/export for the intended To Quran app DB.
--   2. Confirm the selected database is intentionally u504065335_to_quran.
--   3. Set @toquran_confirm_real_db_target exactly as shown below.
--
-- Purpose:
--   Create a durable identity registry for code-defined starter automation rows.
--   The installer uses this table to avoid title-based upserts because teachers
--   may edit template titles, version labels, task prompts, points, recurrence,
--   and assignment choices after installation.
--
-- Safety:
--   - create-only / additive;
--   - no starter automation rows are inserted by this SQL;
--   - no destructive cleanup;
--   - guarded by database name plus explicit operator confirmation variable.

-- SET @toquran_confirm_real_db_target := 'u504065335_to_quran';

DELIMITER //
DROP PROCEDURE IF EXISTS tq75_catalog_registry_guard_or_fail//
CREATE PROCEDURE tq75_catalog_registry_guard_or_fail()
BEGIN
    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog registry patch: set @toquran_confirm_real_db_target first.';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog registry patch: selected DB is not u504065335_to_quran.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'main_daily_session_templates'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'REFUSING TQ7.5 catalog registry patch: automation tables are missing.';
    END IF;
END//
DELIMITER ;

CALL tq75_catalog_registry_guard_or_fail();
DROP PROCEDURE tq75_catalog_registry_guard_or_fail;

CREATE TABLE IF NOT EXISTS toquran_automation_catalog_entries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    automation_type VARCHAR(64) NOT NULL,
    catalog_key VARCHAR(160) NOT NULL,
    entry_scope VARCHAR(64) NOT NULL,
    entry_key VARCHAR(191) NOT NULL,
    target_table VARCHAR(128) NOT NULL,
    target_id BIGINT UNSIGNED NOT NULL,
    teacher_user_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    installed_version VARCHAR(80) NOT NULL,
    manifest_hash CHAR(64) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY tq_auto_catalog_identity_uq (
        automation_type,
        catalog_key,
        teacher_user_id,
        subject_id,
        entry_scope,
        entry_key
    ),
    KEY tq_auto_catalog_target_idx (target_table, target_id),
    KEY tq_auto_catalog_teacher_subject_idx (teacher_user_id, subject_id),
    CONSTRAINT tq_auto_catalog_teacher_fk
        FOREIGN KEY (teacher_user_id) REFERENCES users (id)
        ON DELETE RESTRICT,
    CONSTRAINT tq_auto_catalog_subject_fk
        FOREIGN KEY (subject_id) REFERENCES subjects (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
