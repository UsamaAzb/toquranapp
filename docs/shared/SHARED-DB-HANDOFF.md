# Shared DB Handoff

## Purpose

Track database and runtime items that affect both To Quran repos.

`toquranapp` owns schema decisions. `toquran` may submit intake data and display public content, but it should not independently evolve the LMS schema.

## Open Items

| Date | Item | Source | Owner | Status |
| --- | --- | --- | --- | --- |
| 2026-05-27 | `toquranapp` started empty; no app `.env` or app schema configured | Local audit | `toquranapp` | Historical |
| 2026-05-27 | Local `u504065335_to_quran` schema is absent; only SQL export evidence exists | Public repo export | `toquranapp` | Open |
| 2026-05-27 | To Quran export has 44 tables; Week14 live schema snapshot has 352 tables; direct overwrite is unsafe | Schema comparison | `toquranapp` | Open |
| 2026-05-28 | Owner clarified the old `u504065335_to_quran` export has no client data needing preservation; only the Quran YouTube/video list is intentionally preserved | Owner clarification | `toquranapp` | Preserve video list later |
| 2026-05-27 | Week14 app schema includes missing LMS foundation tables for family lifecycle, intake review, automation, rewards, Library, vocabulary, permissions, and tests | Week14 snapshot | `toquranapp` | Candidate import source |
| 2026-05-28 | Fresh Week14 schema export matches the 2026-05-27 Week14 snapshot structurally; data-only patches remain To Quran planning caveats | Freshness check | `toquranapp` | Complete |
| 2026-05-28 | Phase 1 app skeleton import is complete in commit `270e832`; Phase 2 local app DB target is `toquranapp_local` | TQ1/TQ1.5 plan | `toquranapp` | Complete |
| 2026-05-28 | Local app schema baseline was created in `toquranapp_local` with 352 tables and no imported rows | Manual baseline patch | `toquranapp` | Complete |

## Current Backup/Baseline Evidence

- Public To Quran export source: `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`
- Backup copy in this repo: `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
- Week14 live schema snapshot: `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- Week14 fresh schema export: `database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql`
- Week14 freshness report: `docs/audits/2026-05-28-week14-schema-freshness-check.md`
- To Quran local app schema snapshot: `database/manual/baseline/2026-05-28-toquranapp-local-schema.sql`
- To Quran baseline patch: `database/manual/patches/2026-05-28-create-toquranapp-local-baseline.sql`
- To Quran baseline execution note: `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`

## Schema Comparison Summary

- To Quran export table count: 44
- Week14 live schema table count: 352
- Common table count: 23
- To Quran Phase 2 local app target: `toquranapp_local`
- To Quran local app schema table count: 352
- Public/live website DB name to avoid for app baseline work: `u504065335_to_quran`
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

1. Do not import Week14 over the To Quran export.
2. Treat Phase 1 app skeleton import as complete in `toquranapp`; use Phase 2 to establish the app DB baseline.
3. Use the matched Week14 schema snapshot as structural source evidence.
4. Treat `toquranapp_local` as the current local app schema baseline.
5. Create To Quran starter/reference data intentionally in a later patch.
6. Preserve the Quran YouTube/video list later through a Library/content migration.
7. Keep destructive cleanup documented before execution.
