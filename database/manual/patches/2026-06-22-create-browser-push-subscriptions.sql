-- To Quran browser push subscriptions table
-- Date: 2026-06-22
-- Target DB: u504065335_to_quran
-- Backup before execution: required before production execution; record evidence in a separate execution note.
-- Purpose:
--   - store browser/PWA push subscriptions for parent/student devices;
--   - support external browser push notifications without adding an in-app notification center.
-- Scope:
--   - creates push_subscriptions if missing;
--   - no user, student, parent, teacher, task, gift, or booking data changes.
-- Non-goals:
--   - no VAPID key generation;
--   - no push sends;
--   - no service worker registration.

USE `u504065335_to_quran`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `_toquran_create_browser_push_subscriptions`$$

CREATE PROCEDURE `_toquran_create_browser_push_subscriptions`()
BEGIN
    DECLARE table_count INT DEFAULT 0;

    IF COALESCE(@toquran_confirm_real_db_target, '') <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: set @toquran_confirm_real_db_target = u504065335_to_quran before running this patch';
    END IF;

    IF DATABASE() <> 'u504065335_to_quran' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: wrong database selected for To Quran browser push subscriptions';
    END IF;

    SELECT COUNT(*)
      INTO table_count
      FROM information_schema.tables
     WHERE table_schema = DATABASE();

    IF table_count < 300 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: app schema baseline is not present';
    END IF;
END$$

CALL `_toquran_create_browser_push_subscriptions`()$$

DROP PROCEDURE IF EXISTS `_toquran_create_browser_push_subscriptions`$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `endpoint` TEXT NOT NULL,
    `endpoint_hash` CHAR(64) NOT NULL,
    `public_key` VARCHAR(512) NOT NULL,
    `auth_token` VARCHAR(512) NOT NULL,
    `content_encoding` VARCHAR(32) NOT NULL DEFAULT 'aes128gcm',
    `user_agent` VARCHAR(512) NULL,
    `last_seen_at` TIMESTAMP NULL DEFAULT NULL,
    `revoked_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `push_subscriptions_endpoint_hash_unique` (`endpoint_hash`),
    KEY `push_subscriptions_user_revoked_index` (`user_id`, `revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT
    DATABASE() AS selected_database,
    COUNT(*) AS push_subscription_tables
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name = 'push_subscriptions';
