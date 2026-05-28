# To Quran DB Safety Policy

This policy applies to `app.toquran.org` and the connected public website.

## Ownership

`D:\xampp\htdocs\toquranapp` owns the To Quran app/LMS schema and durable DB decisions.

`D:\xampp\htdocs\toquran` is the public website and intake consumer. It may submit intake data and display public content, but it must not become the schema authority for LMS/family/task/reward/library behavior.

## Current Audit Evidence

As of 2026-05-27:

- `toquranapp` had no app code or `.env`.
- The public site `.env` points to database `u504065335_to_quran`.
- That schema is not present in the local XAMPP MySQL data directory.
- A SQL export exists at `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`.
- A backup copy was created at `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`.
- Week14 live schema was exported read-only to `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`.
- Owner clarified on 2026-05-28 that the old `u504065335_to_quran` export has no client data requiring preservation. The only intentionally preserved legacy data is the Quran YouTube/video list, planned for a later Library migration.
- A Week14 schema freshness check confirmed the 2026-05-27 Week14 snapshot matches a fresh read-only export from local `u504065335_vuexy_week14`: `docs/audits/2026-05-28-week14-schema-freshness-check.md`.

As of 2026-05-28:

- Phase 1 app skeleton import is committed at `270e832`.
- The To Quran local/app DB target used for the Phase 2 proof run is `toquranapp_local`.
- The local `toquranapp_local` structure-only baseline was created with 352 tables and no imported rows. Execution evidence is recorded in `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`.
- The owner then changed the deployment posture for speed: the real To Quran app DB target is `u504065335_to_quran`.
- Because `u504065335_to_quran` was also the public website DB name/export source, every real-target patch must verify that it is intentionally targeting the app deployment DB and not an accidental wrong connection.
- On branch `tq-real-db-transition`, the real-name local target `u504065335_to_quran` was created from a guarded structure-only baseline, then starter/reference data was inserted intentionally. The target has 352 tables, 7 roles, 5 service values, 3 To Quran subjects, 12 grade-level subject mappings, and 0 users.
- A follow-up framework infrastructure correction restored Laravel/Sanctum/Spatie keys and indexes in `u504065335_to_quran`. Evidence is recorded in `database/manual/patches/2026-05-28-framework-infrastructure-indexes-execution-note.sql`.
- A follow-up Library column correction renamed the malformed imported column ` general_library_dp_unit_id` to `general_library_dp_unit_id` in `u504065335_to_quran`. Evidence is recorded in `database/manual/patches/2026-05-28-library-column-correction-execution-note.sql`.

## Allowed Without Separate Owner Approval

Codex may perform To Quran app DB setup and schema work without separate owner approval when all of these are true:

1. A backup/export exists for any source DB or export being used.
2. The target DB name and connection are verified as the intended To Quran app DB target.
3. If the target is `u504065335_to_quran`, the patch states that this is intentional for accelerated deployment.
4. If the target is `u504065335_to_quran`, correction/data patches require an explicit operator-set confirmation variable or equivalent instance-level guard, not only `DATABASE()` name checks.
5. Durable schema/data work is documented with manual SQL, migration notes, or a clear execution note under `database/manual/`.
6. Destructive cleanup of old/export-only data is documented before execution, including the Quran YouTube/video-list preservation boundary.

This permission covers dry-run schema creation, real app baseline setup, schema patch execution, and controlled data mapping for the To Quran app DB. It does not make `toquran` the app schema authority.

## Still Forbidden Without A Cleanup Plan

- Dropping tables or columns.
- Truncating data.
- Importing Week14 over To Quran data without a To Quran baseline/mapping plan.
- Running Laravel migrations or seeders against a target before confirming the intended To Quran app DB name and backup evidence.
- Running `migrate:fresh`, `migrate:refresh`, `db:wipe`, or restore/import commands against the wrong target or without backup evidence.
- Cleaning old Week14/To Quran data just because it looks obsolete.

## Required DB Change Flow

1. Confirm backup/export evidence.
2. Verify target DB name, connection, and repo ownership before executing anything.
3. Compare against Week14 source schema.
4. Identify data worth preserving, obsolete tables, missing tables, and cleanup risk.
5. Review framework infrastructure tables against expected Laravel/Sanctum/Spatie keys, indexes, foreign keys, and auto-increment columns.
6. Scan baseline/replay SQL for malformed identifiers, including leading/trailing spaces inside backticks.
7. Add fixed-ID drift checks before starter/reference inserts.
8. Make preservation-only SQL inert by default when practical.
9. Write a manual SQL patch or migration note under `database/manual/`.
10. Update `docs/shared/SHARED-DB-HANDOFF.md`.
11. Execute To Quran app DB work when the allowed-work checks above pass; otherwise stop and ask.

## Baseline/Patch Convention

- Structure snapshots live in `database/manual/baseline/`.
- Data exports/backups live in `database/manual/backups/`.
- Manual SQL patches live in `database/manual/patches/`.
- Cleanup plans belong in docs first. They become SQL only after cleanup intent, target data, backup evidence, and target DB checks are documented.

## Immediate DB Direction

Do not treat the old To Quran SQL export as the target LMS schema. It is evidence and a preservation source.

The recommended strategy is:

- treat `toquranapp_local` as the completed dry-run baseline;
- use `u504065335_to_quran` as the real app DB target for the accelerated deployment path;
- build the app schema from the current Week14 LMS schema after To Quran adaptation decisions are approved;
- keep the executed starter/reference data patch as the baseline for TQ2 intake and family adaptation;
- map/preserve the old Quran YouTube/video list later into the Library/content system;
- keep Arabic vocabulary games and legacy Quran video migration out of Phase 1;
- keep destructive cleanup of old tables documented separately before execution.
