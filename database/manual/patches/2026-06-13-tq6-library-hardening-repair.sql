-- TQ6 Library hardening repair
-- Target: u504065335_to_quran
-- Backup before execution:
--   database/manual/backups/2026-06-13-155055-u504065335_to_quran-before-tq6-library-hardening.sql
--
-- Purpose:
--   Repair malformed YouTube embed URLs created from the preserved Quran
--   Repetition import source. This keeps the folder-based Library content
--   launch-facing and avoids blank/redirect-prone embedded videos.
--
-- Safety:
--   - guarded to the real To Quran app DB name with explicit operator variable;
--   - idempotent string repair only;
--   - no deletion or broad cleanup.

SET @tq6_operator_confirmed_db := 'u504065335_to_quran';
SET @tq6_library_hardening_guard_ok := (DATABASE() = @tq6_operator_confirmed_db);

SELECT CASE WHEN @tq6_library_hardening_guard_ok = 1 THEN 'TQ6 Library hardening repair guard passed.' ELSE 'REFUSING TQ6 Library hardening repair: wrong target DB.' END AS tq6_library_hardening_guard;

DELIMITER //
CREATE PROCEDURE tq6_library_hardening_guard_or_fail()
BEGIN
    IF @tq6_library_hardening_guard_ok <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'REFUSING TQ6 Library hardening repair: wrong target DB.';
    END IF;
END//
DELIMITER ;
CALL tq6_library_hardening_guard_or_fail();
DROP PROCEDURE tq6_library_hardening_guard_or_fail;

UPDATE general_library_resources
SET external_url = REPLACE(external_url, '/embed//', '/embed/')
WHERE resource_type = 'youtube'
  AND external_url LIKE '%/embed//%';
