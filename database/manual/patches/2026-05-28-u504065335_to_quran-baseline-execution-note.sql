-- To Quran real app DB baseline execution note
-- Date: 2026-05-28
-- Target: real app DB name, `u504065335_to_quran`
-- Patch executed: database/manual/patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql
-- Status: executed locally against XAMPP MySQL on 2026-05-28 02:51 +03:00.

-- Preflight evidence:
-- - Local MySQL had `toquranapp_local` with 352 tables from the dry-run baseline.
-- - Local MySQL did not have `u504065335_to_quran` before this execution.
-- - Existing old/public export backup:
--   database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql
-- - Quran video preservation extract:
--   database/manual/backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql

-- Execution:
-- - The real-target baseline patch created/selects `u504065335_to_quran`.
-- - The patch preflight verifies DATABASE() = 'u504065335_to_quran'.
-- - The patch aborts if `u504065335_to_quran` already contains tables.
-- - The patch is structure-only; no Week14 rows or old To Quran rows were imported.

-- Verification:
-- - `u504065335_to_quran` table count: 352.
-- - `u504065335_to_quran.users` row count: 0.
-- - `u504065335_to_quran.sessions` row count: 0.
-- - `u504065335_to_quran.roles` row count: 0.
-- - `u504065335_to_quran.permissions` row count: 0.
-- - Post-execution schema snapshot:
--   database/manual/baseline/2026-05-28-u504065335_to_quran-app-schema.sql
-- - Snapshot encoding check: no NUL bytes in the first 256 bytes; mysqldump result-file output, not PowerShell UTF-16.

-- Laravel verification:
-- - Local ignored `.env` was updated to `DB_DATABASE=u504065335_to_quran`.
-- - `APP_URL` is `http://127.0.0.1:8014`.
-- - `php artisan config:clear` succeeded.
-- - `php artisan about --only=environment,cache,drivers` reported app name `To Quran`, Laravel 12.17.0, URL `127.0.0.1:8014`, and database driver `mysql`.
-- - `/login` returned HTTP 200 with title `To Quran | Login` on `http://127.0.0.1:8014/login`.
-- - Focused tests passed: `php artisan test tests\Feature\AuthenticationTest.php tests\Feature\PwaInstallabilityTest.php tests\Unit\CredentialServiceTest.php` (13 tests, 50 assertions).

-- Next:
-- - Create an intentional starter/reference data patch for roles, permissions, service catalog, and minimal app defaults.
-- - Do not migrate the preserved Quran video list into app tables until the Library taxonomy/migration plan is approved.
