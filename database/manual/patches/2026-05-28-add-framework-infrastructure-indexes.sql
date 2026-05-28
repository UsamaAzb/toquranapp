-- To Quran real app DB framework infrastructure index correction
-- Date: 2026-05-28
-- Target DB: u504065335_to_quran
-- Purpose: restore Laravel / Sanctum / Spatie infrastructure keys and indexes
-- after the real-name baseline import.
--
-- Required execution guard:
--   SET @toquran_confirm_real_db_target = 'u504065335_to_quran';
--
-- This patch is idempotent for the added keys/indexes/constraints. It does
-- not create or drop databases, and it does not insert application data.

USE `u504065335_to_quran`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_framework_index_preflight`$$
CREATE PROCEDURE `_toquran_framework_index_preflight`()
BEGIN
    DECLARE v_table_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = ''u504065335_to_quran'' before running this patch.';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran framework index correction.';
    END IF;

    SELECT COUNT(*)
      INTO v_table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF v_table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: expected the To Quran app baseline schema before adding framework indexes.';
    END IF;
END$$

DROP PROCEDURE IF EXISTS `_toquran_assert_no_duplicates`$$
CREATE PROCEDURE `_toquran_assert_no_duplicates`(
    IN p_label VARCHAR(128),
    IN p_duplicate_query TEXT
)
BEGIN
    SET @toquran_duplicate_count = 0;
    SET @toquran_duplicate_sql = CONCAT(
        'SELECT COUNT(*) INTO @toquran_duplicate_count FROM (',
        p_duplicate_query,
        ') AS duplicate_groups'
    );

    PREPARE toquran_duplicate_stmt FROM @toquran_duplicate_sql;
    EXECUTE toquran_duplicate_stmt;
    DEALLOCATE PREPARE toquran_duplicate_stmt;

    IF @toquran_duplicate_count > 0 THEN
        SET @toquran_duplicate_message = CONCAT(
            'ABORTED: duplicate values found for ',
            p_label,
            '; clean data before adding the framework key.'
        );
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @toquran_duplicate_message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `_toquran_add_index_if_missing`$$
CREATE PROCEDURE `_toquran_add_index_if_missing`(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_alter_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = p_table_name
           AND index_name = p_index_name
    ) THEN
        SET @toquran_alter_sql = p_alter_sql;
        PREPARE toquran_alter_stmt FROM @toquran_alter_sql;
        EXECUTE toquran_alter_stmt;
        DEALLOCATE PREPARE toquran_alter_stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `_toquran_add_constraint_if_missing`$$
CREATE PROCEDURE `_toquran_add_constraint_if_missing`(
    IN p_table_name VARCHAR(64),
    IN p_constraint_name VARCHAR(64),
    IN p_alter_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM information_schema.table_constraints
         WHERE constraint_schema = DATABASE()
           AND table_name = p_table_name
           AND constraint_name = p_constraint_name
    ) THEN
        SET @toquran_constraint_sql = p_alter_sql;
        PREPARE toquran_constraint_stmt FROM @toquran_constraint_sql;
        EXECUTE toquran_constraint_stmt;
        DEALLOCATE PREPARE toquran_constraint_stmt;
    END IF;
END$$

CALL `_toquran_framework_index_preflight`()$$

CALL `_toquran_assert_no_duplicates`('cache.key', 'SELECT `key` FROM `cache` GROUP BY `key` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('cache_locks.key', 'SELECT `key` FROM `cache_locks` GROUP BY `key` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('failed_jobs.id', 'SELECT `id` FROM `failed_jobs` GROUP BY `id` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('failed_jobs.uuid', 'SELECT `uuid` FROM `failed_jobs` GROUP BY `uuid` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('job_batches.id', 'SELECT `id` FROM `job_batches` GROUP BY `id` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('migrations.id', 'SELECT `id` FROM `migrations` GROUP BY `id` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('model_has_permissions primary tuple', 'SELECT `permission_id`, `model_id`, `model_type` FROM `model_has_permissions` GROUP BY `permission_id`, `model_id`, `model_type` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('model_has_roles primary tuple', 'SELECT `role_id`, `model_id`, `model_type` FROM `model_has_roles` GROUP BY `role_id`, `model_id`, `model_type` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('password_reset_tokens.email', 'SELECT `email` FROM `password_reset_tokens` GROUP BY `email` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('personal_access_tokens.id', 'SELECT `id` FROM `personal_access_tokens` GROUP BY `id` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('personal_access_tokens.token', 'SELECT `token` FROM `personal_access_tokens` GROUP BY `token` HAVING COUNT(*) > 1')$$
CALL `_toquran_assert_no_duplicates`('sessions.id', 'SELECT `id` FROM `sessions` GROUP BY `id` HAVING COUNT(*) > 1')$$

CALL `_toquran_add_index_if_missing`('cache', 'PRIMARY', 'ALTER TABLE `cache` ADD PRIMARY KEY (`key`)')$$
CALL `_toquran_add_index_if_missing`('cache_locks', 'PRIMARY', 'ALTER TABLE `cache_locks` ADD PRIMARY KEY (`key`)')$$

CALL `_toquran_add_index_if_missing`('failed_jobs', 'PRIMARY', 'ALTER TABLE `failed_jobs` MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)')$$
CALL `_toquran_add_index_if_missing`('failed_jobs', 'failed_jobs_uuid_unique', 'ALTER TABLE `failed_jobs` ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)')$$

CALL `_toquran_add_index_if_missing`('job_batches', 'PRIMARY', 'ALTER TABLE `job_batches` ADD PRIMARY KEY (`id`)')$$

CALL `_toquran_add_index_if_missing`('migrations', 'PRIMARY', 'ALTER TABLE `migrations` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)')$$

CALL `_toquran_add_index_if_missing`('model_has_permissions', 'PRIMARY', 'ALTER TABLE `model_has_permissions` ADD PRIMARY KEY (`permission_id`, `model_id`, `model_type`)')$$
CALL `_toquran_add_index_if_missing`('model_has_permissions', 'model_has_permissions_model_id_model_type_index', 'ALTER TABLE `model_has_permissions` ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`)')$$

CALL `_toquran_add_index_if_missing`('model_has_roles', 'PRIMARY', 'ALTER TABLE `model_has_roles` ADD PRIMARY KEY (`role_id`, `model_id`, `model_type`)')$$
CALL `_toquran_add_index_if_missing`('model_has_roles', 'model_has_roles_model_id_model_type_index', 'ALTER TABLE `model_has_roles` ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`)')$$

CALL `_toquran_add_index_if_missing`('password_reset_tokens', 'PRIMARY', 'ALTER TABLE `password_reset_tokens` ADD PRIMARY KEY (`email`)')$$

CALL `_toquran_add_index_if_missing`('personal_access_tokens', 'PRIMARY', 'ALTER TABLE `personal_access_tokens` MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)')$$
CALL `_toquran_add_index_if_missing`('personal_access_tokens', 'personal_access_tokens_token_unique', 'ALTER TABLE `personal_access_tokens` ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`)')$$
CALL `_toquran_add_index_if_missing`('personal_access_tokens', 'personal_access_tokens_tokenable_type_tokenable_id_index', 'ALTER TABLE `personal_access_tokens` ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)')$$

CALL `_toquran_add_index_if_missing`('sessions', 'PRIMARY', 'ALTER TABLE `sessions` ADD PRIMARY KEY (`id`)')$$
CALL `_toquran_add_index_if_missing`('sessions', 'sessions_user_id_index', 'ALTER TABLE `sessions` ADD KEY `sessions_user_id_index` (`user_id`)')$$
CALL `_toquran_add_index_if_missing`('sessions', 'sessions_last_activity_index', 'ALTER TABLE `sessions` ADD KEY `sessions_last_activity_index` (`last_activity`)')$$

CALL `_toquran_add_constraint_if_missing`('model_has_permissions', 'model_has_permissions_permission_id_foreign', 'ALTER TABLE `model_has_permissions` ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE')$$
CALL `_toquran_add_constraint_if_missing`('model_has_roles', 'model_has_roles_role_id_foreign', 'ALTER TABLE `model_has_roles` ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE')$$
CALL `_toquran_add_constraint_if_missing`('role_has_permissions', 'role_has_permissions_permission_id_foreign', 'ALTER TABLE `role_has_permissions` ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE')$$
CALL `_toquran_add_constraint_if_missing`('role_has_permissions', 'role_has_permissions_role_id_foreign', 'ALTER TABLE `role_has_permissions` ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE')$$

DROP PROCEDURE IF EXISTS `_toquran_add_constraint_if_missing`$$
DROP PROCEDURE IF EXISTS `_toquran_add_index_if_missing`$$
DROP PROCEDURE IF EXISTS `_toquran_assert_no_duplicates`$$
DROP PROCEDURE IF EXISTS `_toquran_framework_index_preflight`$$

DELIMITER ;
