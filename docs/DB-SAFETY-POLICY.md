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
- The To Quran local/app DB target for Phase 2 planning is `toquranapp_local`.
- The public/live website DB name remains `u504065335_to_quran`; it must not be used as the app baseline target by accident.
- The local `toquranapp_local` structure-only baseline was created with 352 tables and no imported rows. Execution evidence is recorded in `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`.

## Allowed Without Separate Owner Approval

Codex may perform To Quran local/app DB setup and schema work without separate owner approval when all of these are true:

1. A backup/export exists for any source DB or export being used.
2. The target DB name and connection are verified as a To Quran local/app DB target.
3. The action is not aimed at the public/live website DB by accident.
4. Durable schema/data work is documented with manual SQL, migration notes, or a clear execution note under `database/manual/`.
5. Destructive cleanup of old/export-only data is documented before execution.

This permission covers local/app schema creation, app baseline setup, schema patch execution, and controlled data mapping for the To Quran app DB. It does not make `toquran` the app schema authority.

## Still Forbidden Without A Cleanup Plan

- Dropping tables or columns.
- Truncating data.
- Importing Week14 over To Quran data without a To Quran baseline/mapping plan.
- Running Laravel migrations or seeders against a public/live website DB target.
- Running `migrate:fresh`, `migrate:refresh`, `db:wipe`, or restore/import commands against the wrong target or without backup evidence.
- Cleaning old Week14/To Quran data just because it looks obsolete.

## Required DB Change Flow

1. Confirm backup/export evidence.
2. Verify target DB name, connection, and repo ownership before executing anything.
3. Compare against Week14 source schema.
4. Identify data worth preserving, obsolete tables, missing tables, and cleanup risk.
5. Write a manual SQL patch or migration note under `database/manual/`.
6. Update `docs/shared/SHARED-DB-HANDOFF.md`.
7. Execute local/app To Quran DB work when the allowed-work checks above pass; otherwise stop and ask.

## Baseline/Patch Convention

- Structure snapshots live in `database/manual/baseline/`.
- Data exports/backups live in `database/manual/backups/`.
- Manual SQL patches live in `database/manual/patches/`.
- Cleanup plans belong in docs first. They become SQL only after cleanup intent, target data, backup evidence, and target DB checks are documented.

## Immediate DB Direction

Do not treat the old To Quran SQL export as the target LMS schema. It is evidence and a preservation source.

The recommended strategy is:

- build the app schema from the current Week14 LMS schema after To Quran adaptation decisions are approved;
- use `toquranapp_local` as the local/app target DB name for Phase 2 schema setup unless a later decision changes it;
- create To Quran starter/reference data intentionally in a later patch;
- map/preserve the old Quran YouTube/video list later into the Library/content system;
- keep Arabic vocabulary games and legacy Quran video migration out of Phase 1;
- keep destructive cleanup of old tables documented separately before execution.
