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
- `backups/2026-06-02-203533-u504065335_to_quran-before-contacts-child-age-nullable-structure.sql`
  - focused structure-only real-target backup of `contacts` before making `contacts.child_age` nullable for generic public Contact Us submissions; no contact rows exported
- `backups/2026-06-02-205300-u504065335_to_quran-before-tq9-smoke-selected-subject-correction.sql`
  - redacted evidence note before correcting the TQ9 transferred smoke child's selected-service subject activation
- `backups/2026-06-04-u504065335_to_quran-before-intake-review-clean-new-customer-enum-structure.sql`
  - focused structure-only real-target backup of `booking_intake_review` before allowing `clean_new_customer` intake review summaries; no row data, auth/session/contact data, or credentials exported
- `backups/2026-06-04-u504065335_to_quran-before-family-workspace-permissions.sql`
  - focused restore backup of Spatie roles, permissions, and role-permission links before adding family workspace lifecycle permissions for launch admin operations
- `backups/2026-06-04-u504065335_to_quran-before-mdj-behavior-icon-heal.sql`
  - focused restore backup of MDJ behavior/reward icon tables before healing missing behavior icon metadata in local launch smoke data
- `backups/2026-06-04-u504065335_to_quran-before-users-country-structure.sql`
  - focused structure-only backup of `users` before adding first-class `country`; no user/auth/contact row data exported
- `backups/2026-06-04-u504065335_to_quran-before-legacy-booking-child-normalization.sql`
  - focused restore backup of `bookings` and `booking_children` before normalizing legacy booking-level child rows into editable child workflow rows
- `backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-wording-refresh.sql`
  - focused restore backup of MDJ behavior templates and consequence agreement tables before refreshing launch behavior/consequence wording
- `backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-icon-mapping-refresh.sql`
  - focused restore backup of MDJ behavior icon/template/history tables before replacing the single fallback behavior icon with distinct launch icon mappings
- `backups/2026-06-06-u504065335_to_quran-before-mdj-lms-consequence-behavior-refresh.sql`
  - focused restore backup of MDJ behavior/consequence reference and copied student rows before expanding launch behavior cards and replacing robotic consequence sentences with practical Week14 LMS-style agreements
- `backups/2026-06-06-u504065335_to_quran-before-mdj-behavior-icon-remap.sql`
  - focused restore backup of MDJ behavior icon/template rows before remapping visually weak launch icon choices to better-fitting Week14 icon files
- `backups/2026-06-06-u504065335_to_quran-before-mdj-popup-category-flag-fix.sql`
  - focused restore backup of MDJ behavior/agreement/history rows before restoring the first Slip and No Way cards as popup category actions
- `backups/2026-06-06-u504065335_to_quran-before-mdj-good-job-popup-flag-fix.sql`
  - focused restore backup of MDJ behavior template rows before restoring the first Positive card as a popup category action
- `backups/2026-06-06-231654-u504065335_to_quran-before-tq6-general-library-structure.sql`
  - focused structure-only backup of the real app DB target before creating the TQ6 general Library and Quran Library tables; no row data, auth/session/contact data, or credentials exported
- `backups/2026-06-07-121006-u504065335_to_quran-before-tq6-library-folder-quran-import.sql`
  - focused restore backup of the TQ6 Library tables before adding folder `content_mode` and importing the preserved Quran Repetition YouTube list into general Library folders/resources
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
- `patches/2026-06-02-make-contacts-child-age-nullable.sql`
  - guarded real-target schema patch that makes `contacts.child_age` nullable for generic public Contact Us submissions; executed locally against `u504065335_to_quran` on 2026-06-02
- `patches/2026-06-02-make-contacts-child-age-nullable-execution-note.sql`
  - execution note for the Contact Us `contacts.child_age` nullable patch
- `patches/2026-06-02-correct-tq9-smoke-selected-service-subjects.sql`
  - guarded real-target smoke-data correction patch that activates only transferred smoke child subjects selected in `booking_children.service_interests`
- `patches/2026-06-02-correct-tq9-smoke-selected-service-subjects-execution-note.sql`
  - execution note for the TQ9 smoke selected-service subject correction
- `patches/2026-06-04-add-clean-new-customer-intake-review-enum.sql`
  - guarded real-target schema patch that aligns `booking_intake_review.detection_reason` with the intake correction flow by adding `clean_new_customer`
- `patches/2026-06-04-add-clean-new-customer-intake-review-enum-execution-note.sql`
  - execution note for the intake review enum patch
- `patches/2026-06-04-add-family-workspace-permissions.sql`
  - guarded insert-only real-target data patch that adds family lifecycle/security permissions and assigns them to the `admin` role
- `patches/2026-06-04-add-family-workspace-permissions-execution-note.sql`
  - execution note for the family workspace permissions patch
- `patches/2026-06-04-mdj-behavior-icon-heal-execution-note.sql`
  - execution note for the local launch MDJ behavior icon heal
- `patches/2026-06-04-add-users-country.sql`
  - guarded real-target schema/data patch that adds `users.country` and backfills existing local smoke users
- `patches/2026-06-04-add-users-country-execution-note.sql`
  - execution note for the users.country patch
- `patches/2026-06-04-legacy-booking-child-normalization-execution-note.sql`
  - execution note for the local legacy booking child normalization
- `patches/2026-06-04-normalize-legacy-booking-children.sql`
  - guarded replayable patch for normalizing legacy booking-level child rows into booking_children
- `patches/2026-06-04-booking-child-school-default-heal-execution-note.sql`
  - execution note for the local booking child school-default heal
- `patches/2026-06-04-heal-booking-child-school-defaults.sql`
  - guarded replayable patch for silently defaulting blank booking child school metadata
- `patches/2026-06-05-mdj-reward-preflight-execution-note.sql`
  - execution note for local MDJ reward queue preflight before owner manual testing
- `patches/2026-06-05-booking-child-service-grade-cleanup-execution-note.sql`
  - execution note for local child service-prefix cleanup and Beginner grade default before owner manual testing
- `patches/2026-06-05-mdj-behavior-wording-refresh.sql`
  - guarded update-only wording refresh for MDJ behavior titles and default consequence agreement sentences
- `patches/2026-06-05-mdj-behavior-wording-refresh-execution-note.sql`
  - execution note for the local MDJ behavior/consequence wording refresh, including success and guard-failure evidence
- `patches/2026-06-05-mdj-behavior-icon-mapping-refresh.sql`
  - guarded icon metadata refresh that maps the TQ5 behavior labels to distinct existing discipline icons
- `patches/2026-06-05-mdj-behavior-icon-mapping-refresh-execution-note.sql`
  - execution note for the local MDJ behavior icon mapping refresh, including success and guard-failure evidence
- `patches/2026-06-06-mdj-lms-consequence-behavior-refresh.sql`
  - guarded refresh that expands the TQ5 behavior cards to the broader launch set and replaces the consequence defaults with practical Week14 LMS-style agreements plus a small To Quran layer
- `patches/2026-06-06-mdj-lms-consequence-behavior-refresh-execution-note.sql`
  - execution note for the local LMS-style behavior/consequence refresh, including backup evidence, success counts, collation correction note, and guard-failure evidence
- `patches/2026-06-06-mdj-behavior-icon-remap.sql`
  - guarded icon remap that replaces weak launch icon choices such as thumbs-up Oops/Device Slip and crown Rule Reminder with better-fitting Week14 icon files
- `patches/2026-06-06-mdj-behavior-icon-remap-execution-note.sql`
  - execution note for the behavior icon remap, including backup evidence and guard-failure evidence
- `patches/2026-06-06-mdj-popup-category-flag-fix.sql`
  - guarded flag fix that restores `Oops!` and `Serious Matter` as popup category cards instead of instant point actions
- `patches/2026-06-06-mdj-popup-category-flag-fix-execution-note.sql`
  - execution note for the popup category flag fix, including backup evidence, success counts, and guard-failure evidence
- `patches/2026-06-06-mdj-good-job-popup-flag-fix.sql`
  - guarded flag fix that restores `Good Job` as the Positive popup category card instead of an instant point action
- `patches/2026-06-06-mdj-good-job-popup-flag-fix-execution-note.sql`
  - execution note for the Good Job popup category flag fix, including backup evidence, success counts, and guard-failure evidence
- `patches/2026-06-06-create-tq6-general-library.sql`
  - guarded structure patch creating `general_library_folders`, `general_library_resources`, `quran_library_surahs`, and `quran_library_videos` for TQ6 shared app Library content
- `patches/2026-06-06-create-tq6-general-library-execution-note.sql`
  - execution note for the TQ6 general Library structure patch, including target verification, backup evidence, first-attempt FK-type correction, successful execution, and row-count verification
- `patches/2026-06-07-tq6-library-folder-mode-and-quran-repetition-import.sql`
  - guarded additive patch that adds final-destination `content_mode` to general Library folders and imports the preserved Quran YouTube list as `Quran Repetition` > per-surah source-only folders > YouTube sources
- `patches/2026-06-07-tq6-library-folder-mode-and-quran-repetition-import-execution-note.sql`
  - execution note for the local TQ6 Quran Repetition import, including backup evidence, guard success, and verification counts for 20 surah folders and 106 sources

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
- Public website Contact Us handoff requires `contacts.child_age` to be nullable because generic contact rows do not always belong to a child. The app-owned manual patch was executed locally against `u504065335_to_quran` on 2026-06-02 after target verification and focused structure backup evidence.
- Intake review correction can resolve a flagged row into `clean_new_customer`; the app-owned manual enum patch was executed locally against `u504065335_to_quran` on 2026-06-04 after target verification and focused structure backup evidence.
- Family workspace lifecycle controls require the `families.*` Spatie permission rows. The app-owned insert-only permission patch was executed locally against `u504065335_to_quran` on 2026-06-04 after target verification and focused backup evidence.
- MDJ behavior template rows have fallback icon metadata for local launch testing. Existing local smoke rows were healed on 2026-06-04 after target verification and focused backup evidence.
- Users now have first-class `country` for launch operations. Existing local smoke users were backfilled to Egypt on 2026-06-04; future transferred parent/student users copy country from public intake notes when available.
- Legacy booking-level child rows were normalized locally into `booking_children` rows on 2026-06-04 so every visible child can open the child workflow editor; transfer may still be gated separately.
- Booking child school metadata is silently defaulted locally for launch. Existing local `booking_children` rows have zero blank `school_system` and `current_school` values after the 2026-06-04 guarded check.
- MDJ reward queues were preflighted locally on 2026-06-05 by running the launch-default service for every local student after a focused `student_gifts` backup; current-year reward gaps are now zero for manual testing.
- Booking child service/grade cleanup was run locally on 2026-06-05 after a focused `booking_children` backup; child-prefixed service labels, parent-only consultation service residue, and blank child grades are now cleared for manual testing.
- Local duplicate Osama intake rows were cleaned on 2026-06-05 after a focused booking-table backup; remaining pending Booking Admin rows are smoke/test rows only.
- TQ6 Library folder mode and Quran Repetition starter content were added locally on 2026-06-07 after a focused Library-table backup: `general_library_folders.content_mode` now supports `mixed` and `sources_only`; the preserved old Quran YouTube list is imported as `Quran Repetition` with 20 surah folders and 106 YouTube sources.
- MDJ behavior titles and consequence suggestions were refreshed locally on 2026-06-05 after a focused behavior/agreement backup; the first pass made labels short but was later superseded because the consequence sentences were too robotic for student-visible agreement buttons.
- MDJ behavior icons were refreshed locally on 2026-06-05 after a focused icon/template/history backup; starter templates and copied student behavior rows now use distinct existing discipline icons instead of the single fallback heart icon.
- MDJ behavior and consequence defaults were expanded locally on 2026-06-06 after a focused behavior/consequence backup; current starter cards are the broader ChatGPT-reviewed launch set, and consequence suggestions now start from the practical Week14 LMS agreement list before To Quran-specific additions.
- MDJ behavior icons were remapped locally on 2026-06-06 after manual review showed a few visually weak choices; Oops, Task Not Done, Device Slip, Rule Reminder, and several red-flag cards now use better-fitting Week14 icon files already present in the repo.
- MDJ popup category flags were restored locally on 2026-06-06 after manual testing showed `Oops!` and `Serious Matter` were deducting points directly; both now open the parent/teacher behavior and consequence agreement popup before saving.
- MDJ Positive popup category flag was restored locally on 2026-06-06 after manual testing showed `Good Job` was adding points directly; it now opens the parent/teacher behavior popup before saving.

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
10. `patches/2026-06-02-make-contacts-child-age-nullable.sql`
11. `patches/2026-06-04-add-clean-new-customer-intake-review-enum.sql`
12. `patches/2026-06-04-add-family-workspace-permissions.sql`
13. `patches/2026-06-04-add-users-country.sql`
14. `patches/2026-06-04-normalize-legacy-booking-children.sql`
15. `patches/2026-06-04-heal-booking-child-school-defaults.sql`
16. `patches/2026-06-05-mdj-behavior-wording-refresh.sql`
17. `patches/2026-06-05-mdj-behavior-icon-mapping-refresh.sql`
18. `patches/2026-06-06-mdj-lms-consequence-behavior-refresh.sql`
19. `patches/2026-06-06-mdj-behavior-icon-remap.sql`
20. `patches/2026-06-06-mdj-popup-category-flag-fix.sql`
21. `patches/2026-06-06-mdj-good-job-popup-flag-fix.sql`

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
