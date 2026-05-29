# To Quran Manual Database Artifacts

This directory stores DB evidence and owner-reviewed manual SQL for the To Quran app/LMS.

## Directory Layout

```text
database/manual/
|-- baseline/   # structure-only schema snapshots
|-- backups/    # redacted backup evidence notes or safe preservation extracts
`-- patches/    # owner-reviewed manual SQL or SQL notes
```

Do not commit full-fidelity DB dumps that contain users, passwords, recoverable
credentials, sessions, tokens, account histories, or real-looking contact data.
For those backups, keep the raw restore artifact in secured local/offline
storage and commit only a redacted evidence note with filename, purpose, size,
timestamp, and checksum.

## Current Audit Artifacts

- `backups/2026-05-27-235118-u504065335_to_quran-export.sql`
  - redacted evidence note for the public To Quran SQL export found at `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`; raw dump is excluded from Git because it contains legacy auth/contact data
- `baseline/2026-05-27-235118-week14-live-schema.sql`
  - read-only structure snapshot of local Week14 live schema `u504065335_vuexy_week14`
- `baseline/2026-05-28-001530-week14-fresh-schema.sql`
  - fresh read-only Week14 schema export used by `docs/audits/2026-05-28-week14-schema-freshness-check.md`
- `baseline/2026-05-28-toquranapp-local-schema.sql`
  - post-execution structure snapshot of local app DB `toquranapp_local`
- `baseline/2026-05-28-u504065335_to_quran-app-schema.sql`
  - post-execution structure snapshot of real app DB target `u504065335_to_quran`
- `baseline/2026-05-28-u504065335_to_quran-app-schema-after-db-corrections.sql`
  - post-correction structure snapshot of real app DB target after restoring Laravel/Sanctum/Spatie infrastructure keys/indexes and correcting the malformed Library column name
- `backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql`
  - preservation extract for legacy `surahs`, `surahs_old`, and `surh_videos` before real DB transition; SQL payload is block-commented so sourcing it creates zero tables unless a future Library migration deliberately unwraps/adapts it
- `backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql`
  - redacted evidence note for the real-target backup before adding the remaining To Quran LMS class-subject catalog rows
- `backups/2026-05-29-121601-u504065335_to_quran-before-smoke-one-student-correction.sql`
  - redacted evidence note for the real-target backup before correcting launch smoke data to one active student per class
- `backups/2026-05-29-160244-u504065335_to_quran-before-default-teacher.sql`
  - redacted evidence note for the real-target backup before creating/updating the launch default teacher account
- `backups/2026-05-29-165938-u504065335_to_quran-before-arabic-language-service.sql`
  - redacted evidence note for the real-target backup before adding Arabic Language as a distinct app/public service value
- `backups/2026-05-29-172119-u504065335_to_quran-before-task-types.sql`
  - redacted evidence note for the real-target backup before adding launch task-type reference rows
- `backups/2026-05-29-173241-u504065335_to_quran-before-task-type-correction.sql`
  - restore-only focused real-target backup before correcting the same-day task-type reference rows; contains task tables only and no auth/session/contact data
- `patches/2026-05-28-create-toquranapp-local-baseline.sql`
  - guarded structure-only baseline patch used to create `toquranapp_local`
- `patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql`
  - guarded structure-only real-target baseline patch used to create `u504065335_to_quran`
- `patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`
  - execution and verification notes for the local baseline setup
- `patches/2026-05-28-u504065335_to_quran-baseline-execution-note.sql`
  - execution and verification notes for the real-target baseline setup
- `patches/2026-05-28-toquran-starter-reference-data-notes.sql`
  - original starter/reference data notes; superseded by executable patch below
- `patches/2026-05-28-toquran-starter-reference-data.sql`
  - guarded starter/reference data patch for roles, service values, operating year, program, learner levels, and To Quran subjects
- `patches/2026-05-28-toquran-starter-reference-data-execution-note.sql`
  - execution and verification notes for the starter/reference data patch
- `patches/2026-05-28-add-framework-infrastructure-indexes.sql`
  - guarded real-target correction patch for Laravel cache/session/job/password reset, Sanctum token, and Spatie role/permission keys and indexes
- `patches/2026-05-28-add-framework-infrastructure-indexes-toquranapp-local.sql`
  - guarded local dry-run correction patch for the same framework infrastructure keys and indexes
- `patches/2026-05-28-framework-infrastructure-indexes-execution-note.sql`
  - execution and verification notes for the framework infrastructure index correction
- `patches/2026-05-28-fix-library-dp-global-context-column.sql`
  - guarded real-target correction patch for the malformed Library DP/global-context column name imported from Week14 source evidence
- `patches/2026-05-28-fix-library-dp-global-context-column-toquranapp-local.sql`
  - guarded local dry-run version of the Library DP/global-context column correction
- `patches/2026-05-28-library-column-correction-execution-note.sql`
  - execution and verification notes for the Library column-name correction
- `patches/2026-05-28-fix-library-schema-identifier-drift.sql`
  - guarded real-target correction patch for remaining malformed Library identifiers from the imported Week14 source schema
- `patches/2026-05-28-fix-library-schema-identifier-drift-toquranapp-local.sql`
  - guarded local dry-run version of the remaining Library identifier correction
- `patches/2026-05-28-library-schema-identifier-drift-execution-note.sql`
  - execution and verification notes for the remaining Library identifier correction
- `patches/2026-05-28-toquranapp-local-corrections-execution-note.sql`
  - execution and verification notes for applying the correction patches to the local dry-run target
- `patches/2026-05-29-launch-smoke-data-execution-note.sql`
  - execution and verification note for local launch smoke users, multiple family lifecycle states, class, teacher assignment, and transferred booking records in `u504065335_to_quran`
- `patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`
  - guarded cleanup plan for removing only `@toquran-smoke.test`, `[SMOKE]`, and `SMOKE-TQ-0001` launch smoke data before deployment
- `patches/2026-05-29-test-password-reset-execution-note.sql`
  - execution note for the temporary local test-password reset across current app users; do not deploy this credential state
- `patches/2026-05-29-toquran-learning-catalog-reference-data.sql`
  - guarded data patch that adds Arabic Language, Sanad Program, and Well Being LMS subject rows and maps all 6 To Quran class subjects to the current learner levels
- `patches/2026-05-29-toquran-learning-catalog-reference-data-execution-note.sql`
  - execution and verification note for the learning catalog reference-data patch
- `patches/2026-05-29-default-teacher-bootstrap-execution-note.sql`
  - execution note for the real launch default teacher account and transfer default-teacher resolver config
- `patches/2026-05-29-add-arabic-language-service-reference.sql`
  - guarded real-target data patch that adds Arabic Language to app service reference tables for the public multi-service intake handoff
- `patches/2026-05-29-add-arabic-language-service-reference-execution-note.sql`
  - execution note for the Arabic Language service reference patch
- `patches/2026-05-29-add-launch-task-types.sql`
  - guarded real-target data patch that adds launch task types for teacher session/task creation
- `patches/2026-05-29-add-launch-task-types-execution-note.sql`
  - execution note for the launch task-type reference patch
- `patches/2026-05-29-correct-launch-task-types.sql`
  - guarded real-target correction patch that removes the mistaken attachment-kind task rows and confirms Assignment/Lesson/Project/Quiz
- `patches/2026-05-29-correct-launch-task-types-execution-note.sql`
  - execution note for the launch task-type correction patch

## Current Local App Target

- Phase 2 local dry-run DB target: `toquranapp_local`
- Accelerated real app DB target: `u504065335_to_quran`
- Current local baseline table count: 352
- Current real-target baseline table count: 352
- Starter/reference rows have been created in the real app DB target; no user accounts or content rows were imported.
- Real-target framework infrastructure keys/indexes have been corrected after the baseline import.
- Launch smoke data exists locally for testing and must be removed before deployment using the documented cleanup plan.
- Current local users were reset to a shared test password for fast manual testing; real deployment credentials must be set individually before launch.
- To Quran LMS class subjects are now Quran Memorization, Arabic Language, Quranic Arabic, Sanad Program, My Deen Journey, and Well Being. MDJ and Well Being are separate; parent-written behavior points affect Well Being only.
- Arabic Language is now also a distinct app/public service value for intake; the public website can send it separately from Quranic Arabic during the multi-child/multi-service handoff.
- Default transfer teacher assignment is configured by `TOQURAN_DEFAULT_TEACHER_EMAIL`; the current launch default teacher is `drosamaqandil@gmail.com` in `u504065335_to_quran`.
- Launch task-type rows exist for Assignment, Lesson, Project, and Quiz so teacher session-task modals can create normal tasks during TQ4 smoke.

## Current Real-Target Replay Order

For a fresh accelerated To Quran app DB target, use the documented real-target artifacts in this order:

1. `patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql`
2. `patches/2026-05-28-add-framework-infrastructure-indexes.sql`
3. `patches/2026-05-28-fix-library-dp-global-context-column.sql`
4. `patches/2026-05-28-fix-library-schema-identifier-drift.sql`
5. `patches/2026-05-28-toquran-starter-reference-data.sql`
6. `patches/2026-05-29-toquran-learning-catalog-reference-data.sql`
7. `patches/2026-05-29-add-arabic-language-service-reference.sql`
8. `patches/2026-05-29-add-launch-task-types.sql`
9. `patches/2026-05-29-correct-launch-task-types.sql`

The framework infrastructure correction is part of the current real-target baseline shape even though it is stored as a follow-up patch, because the original structure dump omitted several Laravel/Sanctum/Spatie runtime keys and indexes. The Library column correction is also part of the current real-target baseline shape; the To Quran-owned baseline replay files have been corrected, and the follow-up patch remains idempotent for already-created targets.

## Current Local Dry-Run Replay Order

`toquranapp_local` is not the deployment target, but if the local dry-run path must be recreated, use:

1. `patches/2026-05-28-create-toquranapp-local-baseline.sql`
2. `patches/2026-05-28-add-framework-infrastructure-indexes-toquranapp-local.sql`
3. `patches/2026-05-28-fix-library-dp-global-context-column-toquranapp-local.sql`
4. `patches/2026-05-28-fix-library-schema-identifier-drift-toquranapp-local.sql`

This hard-wires the framework and Library correction patches for the exact local bootstrap path instead of relying on the historical baseline patch alone.

## Rules

- Do not edit baseline snapshots to create schema changes.
- Codex may execute To Quran app DB setup and schema work when `docs/DB-SAFETY-POLICY.md` target checks pass.
- Real-target patches for `u504065335_to_quran` must explicitly say the target is intentional for accelerated To Quran deployment and must cite backup/export evidence.
- Real-target correction/data patches for `u504065335_to_quran` must require an explicit operator confirmation variable or equivalent instance-level guard in addition to checking `DATABASE()`.
- Add cleanup SQL only after cleanup intent and target data have been documented.
- Prefer MariaDB-compatible SQL because the local XAMPP Week14 baseline is MariaDB-family and Week14 manual patches target MariaDB compatibility.

## DB Artifact Review Rules

Before a DB artifact is review-ready:

- Confirm Laravel/Sanctum/Spatie infrastructure tables have expected runtime keys and indexes.
- Search SQL artifacts for malformed quoted identifiers such as leading/trailing spaces or accidental embedded spaces inside backticks.
- Add fail-fast drift checks for canonical fixed-ID starter/reference rows.
- Keep preservation-only SQL inert by default when practical.
- Verify idempotent patches can be re-run safely, and verify real-target guards fail when the operator confirmation is missing.
- Create a post-correction snapshot when live DB shape changes after a baseline import.
