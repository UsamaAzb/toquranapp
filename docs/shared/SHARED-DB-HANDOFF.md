# Shared DB Handoff

## Purpose

Track database and runtime items that affect both To Quran repos.

`toquranapp` owns schema decisions. `toquran` may submit intake data and display public content, but it should not independently evolve the LMS schema.

## Open Items

| Date | Item | Source | Owner | Status |
| --- | --- | --- | --- | --- |
| 2026-05-27 | `toquranapp` started empty; no app `.env` or app schema configured | Local audit | `toquranapp` | Historical |
| 2026-05-27 | Local `u504065335_to_quran` schema is absent; only SQL export evidence exists | Public repo export | `toquranapp` | Historical |
| 2026-05-27 | To Quran export has 44 tables; Week14 live schema snapshot has 352 tables; direct overwrite is unsafe | Schema comparison | `toquranapp` | Open |
| 2026-05-28 | Owner clarified the old `u504065335_to_quran` export has no client data needing preservation; only the Quran YouTube/video list is intentionally preserved | Owner clarification | `toquranapp` | Preserve video list later |
| 2026-05-27 | Week14 app schema includes missing LMS foundation tables for family lifecycle, intake review, automation, rewards, Library, vocabulary, permissions, and tests | Week14 snapshot | `toquranapp` | Candidate import source |
| 2026-05-28 | Fresh Week14 schema export matches the 2026-05-27 Week14 snapshot structurally; data-only patches remain To Quran planning caveats | Freshness check | `toquranapp` | Complete |
| 2026-05-28 | Phase 1 app skeleton import is complete in commit `270e832`; Phase 2 local app DB target is `toquranapp_local` | TQ1/TQ1.5 plan | `toquranapp` | Complete |
| 2026-05-28 | Local app schema baseline was created in `toquranapp_local` with 352 tables and no imported rows | Manual baseline patch | `toquranapp` | Complete |
| 2026-05-28 | Owner directed accelerated deployment work to target real app DB name `u504065335_to_quran`; `toquranapp_local` remains the dry-run baseline | Owner clarification | `toquranapp` | Active |
| 2026-05-28 | Real-target app schema baseline was created locally in `u504065335_to_quran` with 352 tables and no imported rows; `/login` and focused tests passed | Manual baseline patch | `toquranapp` | Complete locally; pending review before commit |
| 2026-05-28 | Starter/reference rows were created intentionally in `u504065335_to_quran`: roles, service catalog values, operating year, program, learner levels, To Quran subjects, and grade-level subject mappings | Manual starter patch | `toquranapp` | Complete locally; pending review before commit |
| 2026-05-28 | Framework infrastructure keys/indexes were corrected in `u504065335_to_quran` for Laravel cache/session/job/password reset tables, Sanctum tokens, and Spatie role/permission pivots | Manual correction patch | `toquranapp` | Complete locally; pending review before commit |
| 2026-05-28 | Malformed Library column ` general_library_dp_unit_id` was corrected to `general_library_dp_unit_id` in `u504065335_to_quran`; To Quran replay artifacts were corrected so fresh targets do not recreate the typo | Manual correction patch | `toquranapp` | Complete locally; pending review before commit |

## Current Backup/Baseline Evidence

- Public To Quran export source: `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`
- Backup copy in this repo: `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
- Week14 live schema snapshot: `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- Week14 fresh schema export: `database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql`
- Week14 freshness report: `docs/audits/2026-05-28-week14-schema-freshness-check.md`
- To Quran local app schema snapshot: `database/manual/baseline/2026-05-28-toquranapp-local-schema.sql`
- To Quran baseline patch: `database/manual/patches/2026-05-28-create-toquranapp-local-baseline.sql`
- To Quran baseline execution note: `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`
- Real target transition plan: `docs/plans/active/2026-05-28-real-db-transition-and-starter-data-plan.md`
- Quran video preservation extract: `database/manual/backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql`
- Real target baseline patch: `database/manual/patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql`
- Real target baseline execution note: `database/manual/patches/2026-05-28-u504065335_to_quran-baseline-execution-note.sql`
- Real target schema snapshot: `database/manual/baseline/2026-05-28-u504065335_to_quran-app-schema.sql`
- Starter/reference data patch: `database/manual/patches/2026-05-28-toquran-starter-reference-data.sql`
- Starter/reference data execution note: `database/manual/patches/2026-05-28-toquran-starter-reference-data-execution-note.sql`
- Framework infrastructure index correction patch: `database/manual/patches/2026-05-28-add-framework-infrastructure-indexes.sql`
- Framework infrastructure index execution note: `database/manual/patches/2026-05-28-framework-infrastructure-indexes-execution-note.sql`
- Library column correction patch: `database/manual/patches/2026-05-28-fix-library-dp-global-context-column.sql`
- Library column correction execution note: `database/manual/patches/2026-05-28-library-column-correction-execution-note.sql`
- Real target schema snapshot after DB corrections: `database/manual/baseline/2026-05-28-u504065335_to_quran-app-schema-after-db-corrections.sql`

## Schema Comparison Summary

- To Quran export table count: 44
- Week14 live schema table count: 352
- Common table count: 23
- To Quran Phase 2 local app target: `toquranapp_local`
- To Quran local app schema table count: 352
- Real accelerated app DB target: `u504065335_to_quran`
- Real app DB schema table count: 352
- To Quran-only tables include `employees`, `employees_course`, `employee_quizzes_old`, `quran_courses`, `quran_course_translations`, `surahs`, `surahs_old`, `surh_videos`, old Laratrust-style `role_user`/`permission_user` tables, and old course/lesson tables.
- Week14-only tables include modern `parents`, `booking_children`, `booking_intake_review`, `booking_intake_submission_locks`, `account_histories`, Spatie permission tables, automation tables, reward ledger tables, Library tables, and vocabulary wrapper tables.

## Data Worth Preserving Before Cleanup

From the To Quran SQL export:

- Quran YouTube/video list data, including `surh_videos`, should be preserved as a later Library/content migration item.
- Old bookings, contact messages, users, students, employee/course/quiz rows, and public-service labels are export evidence only unless a later plan deliberately promotes them.
- No client data currently needs preservation from `u504065335_to_quran`.

## Cleanup Risks

Document before destructive cleanup:

- old password mirror fields such as `decr_password`
- legacy student/user rows
- old employee/course/quiz tables
- Quran content tables that may belong to public content rather than private LMS workflows
- public website booking rows and contact messages

## Recommended DB Direction

1. Treat Phase 1 app skeleton import as complete in `toquranapp`.
2. Treat `toquranapp_local` as the completed dry-run app DB baseline.
3. Use the matched Week14 schema snapshot as structural source evidence.
4. Target the real app DB name `u504065335_to_quran` for accelerated deployment with backup/export evidence and guarded manual SQL.
5. Treat the executed starter/reference data patch as the current app reference baseline.
6. Treat the framework infrastructure index correction and Library column-name correction as part of the current real-target baseline for deployment planning.
7. Preserve the Quran YouTube/video list later through a Library/content migration.
8. Keep destructive cleanup documented before execution.
