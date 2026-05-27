-- To Quran local baseline execution note
-- Date: 2026-05-28
-- Target: local/app DB only, `toquranapp_local`
-- Patch executed: database/manual/patches/2026-05-28-create-toquranapp-local-baseline.sql

-- Preflight evidence:
-- - Source backup/export exists:
--   database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql
-- - Week14 source schema evidence exists:
--   database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql
-- - Before execution, local MySQL had `u504065335_vuexy_week14`.
-- - Before execution, local MySQL did not have `toquranapp_local`.
-- - Before execution, local MySQL did not have public/export DB `u504065335_to_quran`.

-- Execution summary:
-- - A first PowerShell-piped mysql import hit the tool timeout and left a partial
--   freshly-created `toquranapp_local` schema with 327 tables.
-- - Because `toquranapp_local` did not exist before this run and contained only
--   the incomplete baseline just created by this task, it was dropped and retried.
-- - The retry used the same guarded baseline patch with a longer timeout.
-- - No public/live website DB was present or mutated.

-- Verification after retry:
-- - `toquranapp_local` exists.
-- - `toquranapp_local` table count: 352.
-- - `u504065335_to_quran` local table count: 0 / DB absent.
-- - Required app tables verified present:
--   users, sessions, cache, jobs, bookings, booking_children,
--   library_resources, series_tasks, vocabulary_sets.
-- - Snapshot exported:
--   database/manual/baseline/2026-05-28-toquranapp-local-schema.sql
-- - Local ignored `.env` was pointed to `DB_DATABASE=toquranapp_local`.
-- - Laravel sees `toquranapp_local`; `users` table row count is 0.
-- - `/login` HTTP smoke returned 200 with title `To Quran | Login`.

-- Data state:
-- - Structure-only baseline.
-- - No Week14 rows imported.
-- - No old To Quran export rows imported.
-- - Starter/reference data remains a later explicit patch.
