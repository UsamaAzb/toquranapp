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
| 2026-05-28 | Real-target app schema baseline was created locally in `u504065335_to_quran` with 352 tables and no imported rows; `/login` and focused tests passed | Manual baseline patch | `toquranapp` | Complete; committed |
| 2026-05-28 | Starter/reference rows were created intentionally in `u504065335_to_quran`: roles, service catalog values, operating year, program, learner levels, To Quran subjects, and grade-level subject mappings | Manual starter patch | `toquranapp` | Complete; committed |
| 2026-05-28 | Framework infrastructure keys/indexes were corrected in `u504065335_to_quran` for Laravel cache/session/job/password reset tables, Sanctum tokens, and Spatie role/permission pivots | Manual correction patch | `toquranapp` | Complete; committed |
| 2026-05-28 | Malformed Library column ` general_library_dp_unit_id` was corrected to `general_library_dp_unit_id` in `u504065335_to_quran`; To Quran replay artifacts were corrected so fresh targets do not recreate the typo | Manual correction patch | `toquranapp` | Complete; committed |
| 2026-05-28 | Remaining imported Library identifier drift was corrected in `u504065335_to_quran`: `teacher and_student_questions` to `teacher_and_student_questions`, and the MYP local/global challenges table now uses `general_library_myp_unit_id` | Manual correction patch | `toquranapp` | Complete; committed |
| 2026-05-29 | Arabic Language was added as a distinct app/public service reference value in `u504065335_to_quran` so the public website can send it separately from Quranic Arabic | Manual reference-data patch | `toquranapp` | Complete; committed in `529f7bc` |
| 2026-05-29 | Launch task-type rows were added to `task_types` for Assignment, Lesson, Project, and Quiz so teacher session tasks can be created during TQ4 smoke | Manual reference-data patch | `toquranapp` | Complete; committed in `529f7bc` |
| 2026-05-29 | Same-day task-type reference rows were corrected after review: attachment-kind rows were removed and id 7 was restored to Assignment/default | Manual correction patch | `toquranapp` | Complete; committed in `529f7bc` |
| 2026-06-02 | Week14 website/LMS handoff was inspected: Week14 website writes directly into LMS-owned `bookings`, `booking_children`, intake review, submission lock, and `contacts` tables. To Quran should follow this shared-table pattern instead of building a delayed JSON import bridge when both repos share the app DB. | Cross-repo audit | `toquranapp` + `toquran` | Approved direction for TQ9 website handoff |
| 2026-06-02 | Public website shared-DB handoff was implemented in `toquran` commit `6dfb71f`: booking writes target app-owned booking/review tables, Contact Us writes target `contacts`, references use `TQ-` / `CNT-`, and the public contact phone is `+201091051913` | Website implementation | `toquran` consuming app contract | Implemented; local shared-DB smoke passed |
| 2026-06-02 | Contact Us shared-DB contract was clarified and executed locally: generic public Contact Us submissions write to `contacts` without requiring or faking `child_age`; app-owned patch `database/manual/patches/2026-06-02-make-contacts-child-age-nullable.sql` made `contacts.child_age` nullable after focused structure backup evidence | Website handoff review + manual patch | `toquranapp` | Executed locally; production import/deploy must preserve this shape |
| 2026-06-02 | TQ9 production-equivalent local smoke passed from public website booking/contact into app review/transfer/login. A transfer provisioning bug was found and fixed so selected optional service interests activate their matching app subjects, e.g. Arabic Language subject id 3. | Local HTTP/DB smoke | `toquranapp` | Complete locally; see audit evidence |
| 2026-06-04 | Intake Review correction can resolve a flagged row into `clean_new_customer`; app-owned patch `database/manual/patches/2026-06-04-add-clean-new-customer-intake-review-enum.sql` added that enum value after focused structure backup evidence | Manual schema patch | `toquranapp` | Executed locally; production import/deploy must preserve this shape |
| 2026-06-04 | Family workspace lifecycle buttons require app-owned `families.*` Spatie permissions; app-owned patch `database/manual/patches/2026-06-04-add-family-workspace-permissions.sql` added the launch permission rows and assigned them to the `admin` role after focused backup evidence | Manual reference-data patch | `toquranapp` | Executed locally; production import/deploy must preserve this shape |
| 2026-06-04 | Country became first-class app user data via `users.country`; existing local smoke users were backfilled to Egypt because there are no real users in the local target | Manual schema/data patch | `toquranapp` | Executed locally; production import/deploy must preserve this shape |
| 2026-06-04 | Legacy booking-level child rows were normalized into `booking_children`, and blank inherited school metadata is silently defaulted for launch | Manual data cleanup patches | `toquranapp` | Executed locally through guarded PHP; replayable guarded SQL patches now exist for production import/deploy |

## Current Backup/Baseline Evidence

- Public To Quran export source: `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`
- Redacted backup evidence note in this repo: `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
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
- Library schema identifier drift correction patch: `database/manual/patches/2026-05-28-fix-library-schema-identifier-drift.sql`
- Library schema identifier drift execution note: `database/manual/patches/2026-05-28-library-schema-identifier-drift-execution-note.sql`
- Local dry-run correction execution note: `database/manual/patches/2026-05-28-toquranapp-local-corrections-execution-note.sql`
- Real target schema snapshot after DB corrections: `database/manual/baseline/2026-05-28-u504065335_to_quran-app-schema-after-db-corrections.sql`
- Arabic Language service reference backup evidence: `database/manual/backups/2026-05-29-165938-u504065335_to_quran-before-arabic-language-service.sql`
- Arabic Language service reference patch: `database/manual/patches/2026-05-29-add-arabic-language-service-reference.sql`
- Arabic Language service reference execution note: `database/manual/patches/2026-05-29-add-arabic-language-service-reference-execution-note.sql`
- Launch task types backup evidence: `database/manual/backups/2026-05-29-172119-u504065335_to_quran-before-task-types.sql`
- Launch task types patch: `database/manual/patches/2026-05-29-add-launch-task-types.sql`
- Launch task types execution note: `database/manual/patches/2026-05-29-add-launch-task-types-execution-note.sql`
- Launch task type correction restore-only focused backup: `database/manual/backups/2026-05-29-173241-u504065335_to_quran-before-task-type-correction.sql`
- Launch task type correction patch: `database/manual/patches/2026-05-29-correct-launch-task-types.sql`
- Launch task type correction execution note: `database/manual/patches/2026-05-29-correct-launch-task-types-execution-note.sql`
- Contact Us `contacts.child_age` nullable patch: `database/manual/patches/2026-06-02-make-contacts-child-age-nullable.sql`
- Contact Us `contacts.child_age` nullable patch execution note: `database/manual/patches/2026-06-02-make-contacts-child-age-nullable-execution-note.sql`
- Intake Review `clean_new_customer` enum patch: `database/manual/patches/2026-06-04-add-clean-new-customer-intake-review-enum.sql`
- Intake Review `clean_new_customer` enum patch execution note: `database/manual/patches/2026-06-04-add-clean-new-customer-intake-review-enum-execution-note.sql`
- Family workspace permissions backup: `database/manual/backups/2026-06-04-u504065335_to_quran-before-family-workspace-permissions.sql`
- Family workspace permissions patch: `database/manual/patches/2026-06-04-add-family-workspace-permissions.sql`
- Family workspace permissions patch execution note: `database/manual/patches/2026-06-04-add-family-workspace-permissions-execution-note.sql`
- MDJ behavior icon heal backup: `database/manual/backups/2026-06-04-u504065335_to_quran-before-mdj-behavior-icon-heal.sql`
- MDJ behavior icon heal execution note: `database/manual/patches/2026-06-04-mdj-behavior-icon-heal-execution-note.sql`
- Users country backup: `database/manual/backups/2026-06-04-u504065335_to_quran-before-users-country-structure.sql`
- Users country patch: `database/manual/patches/2026-06-04-add-users-country.sql`
- Users country patch execution note: `database/manual/patches/2026-06-04-add-users-country-execution-note.sql`
- Legacy booking child normalization backup: `database/manual/backups/2026-06-04-u504065335_to_quran-before-legacy-booking-child-normalization.sql`
- Legacy booking child normalization replay patch: `database/manual/patches/2026-06-04-normalize-legacy-booking-children.sql`
- Legacy booking child normalization execution note: `database/manual/patches/2026-06-04-legacy-booking-child-normalization-execution-note.sql`
- Booking child school default backup: `database/manual/backups/2026-06-04-u504065335_to_quran-before-booking-child-school-default-heal.sql`
- Booking child school default replay patch: `database/manual/patches/2026-06-04-heal-booking-child-school-defaults.sql`
- Booking child school default execution note: `database/manual/patches/2026-06-04-booking-child-school-default-heal-execution-note.sql`
- TQ9 shared DB smoke/hardening audit: `docs/audits/2026-06-02-tq9-shared-db-smoke-and-hardening.md`
- TQ9 smoke selected-service subject correction patch: `database/manual/patches/2026-06-02-correct-tq9-smoke-selected-service-subjects.sql`
- TQ9 smoke selected-service subject correction execution note: `database/manual/patches/2026-06-02-correct-tq9-smoke-selected-service-subjects-execution-note.sql`

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
7. Treat Arabic Language as a distinct public/app service value for website intake; do not collapse it into Quranic Arabic during the public handoff.
8. Treat launch task types as required reference data for TQ4 teacher session/task smoke.
9. Treat public website booking/contact as a shared app-DB writer: website may write only to app-approved tables and columns, primarily `bookings`, `booking_children`, `booking_intake_review`, `booking_intake_review_children`, `booking_intake_submission_locks`, and `contacts`.
10. Do not make `contact_us` or legacy website-only booking JSON the long-term handoff target if the website can write to the shared app DB.
11. Preserve the locally executed `contacts.child_age NULL DEFAULT NULL` shape before public Contact Us writes directly to the production app DB; a generic contact message should not require a child age or fake placeholder value.
12. Preserve the locally executed `booking_intake_review.detection_reason` enum shape that includes `clean_new_customer`; the app correction flow can legitimately classify a reviewed row as a clean new customer.
13. Preserve the locally executed family workspace `families.*` permission rows for launch admin lifecycle operations.
14. Preserve `users.country` and ensure public intake/transfer keeps parent/student country populated.
15. Apply/preserve the legacy booking child normalization and silent school-default shape so admins do not see dead child workflow locks or inherited Week14 school blockers.
16. Preserve the Quran YouTube/video list later through a Library/content migration.
17. Keep destructive cleanup documented before execution.
