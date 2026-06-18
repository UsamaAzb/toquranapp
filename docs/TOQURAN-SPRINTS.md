# To Quran Sprints / Pre-Planning Queue

This is a To Quran-specific roadmap scaffold. It adapts Week14's sprint style without copying Week14 priorities blindly.

## Status Values

- `pending`
- `active`
- `implementation-review`
- `postponed`
- `done`

## Do First

### TQ0. Audit And Selective Reuse Strategy

- Status: `done`
- Path: audit/plan
- Goal: Audit `toquranapp`, `toquran`, current DB evidence, and Week14 LMS. Produce the selective import strategy before importing code.
- Current artifact: `docs/audits/2026-05-27-toquran-week14-reuse-audit.md`
- Plan artifact: `docs/plans/active/2026-05-27-week14-selective-reuse-import-plan.md`
- Website action: decision/docs check later
- DB action: backup/export confirmed; no schema mutation

### TQ0.5. Week14 Schema Freshness Gate

- Status: `done`
- Depends on: TQ0 audit evidence
- Goal: Confirm the 2026-05-27 Week14 schema snapshot still matches current local Week14 before Phase 2 DB planning or app code import.
- Current artifact: `docs/audits/2026-05-28-week14-schema-freshness-check.md`
- Plan artifact: `docs/plans/active/2026-05-27-week14-selective-reuse-import-plan.md`
- Website action: none
- DB action: fresh read-only Week14 export only; no To Quran DB mutation

### TQ1. App Skeleton Import From Week14

- Status: `done`
- Depends on: TQ0 done and TQ0.5 freshness gate
- Goal: Bring in the Laravel 12/Jetstream/Spatie/Livewire/Vuexy app foundation from Week14 with To Quran branding/config and no data destructive operations.
- Current artifact: commit `270e832 Import Week14 LMS foundation for To Quran`
- Verification: app boots to `/login` as `To Quran`; focused auth/PWA/credential tests pass.
- Website action: none during app skeleton import except shared docs alignment.
- DB action: prepare To Quran app schema baseline plan; no import until the SQL plan exists and `docs/DB-SAFETY-POLICY.md` target checks pass.

### TQ1.5. Schema Baseline And Data Mapping Plan

- Status: `done`
- Depends on: TQ1
- Goal: Establish the To Quran local/app DB target, create a schema baseline plan from the matched Week14 structure, and document what starter data should be To Quran-created, skipped, or migrated later.
- Current artifact: `docs/plans/active/2026-05-28-schema-baseline-data-mapping-plan.md`
- DB artifact: `database/manual/patches/2026-05-28-create-toquranapp-local-baseline.sql`
- Execution artifact: `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`
- Real-target artifact: `database/manual/patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql`
- Starter artifact: `database/manual/patches/2026-05-28-toquran-starter-reference-data.sql`
- Verification: local `toquranapp_local` has 352 tables; real target `u504065335_to_quran` has 352 tables; `/login` renders as `To Quran`.
- Website action: none yet; public website handoff waits until app-side target/schema is approved.
- DB action: local/app schema baseline created; real target `u504065335_to_quran` baseline and starter/reference data created intentionally.

### TQ2. To Quran Service Catalog And Intake Foundation

- Status: `done`
- Depends on: TQ1.5
- Goal: Adapt Week14 family/intake model to To Quran service interests. App launch supports Quran Memorization, Quranic Arabic, Arabic Language, My Deen Journey, Paid Parental Consultation, and Sanad Ijazah; public child-facing intake should expose the owner-approved child services listed under TQ9.
- Current artifact: app-side service/intake adaptation committed in `124756b` and merged to `main` in `0b99741`; focused booking/family suite passed before merge.
- Launch scope: keep consultation scheduling, finance, class assignment detail, and teacher management manual for first deployment. Do not build those workflows before launch unless explicitly reopened.
- Website action: align public form values and reference prefix during TQ9 public website handoff.
- DB action: real-target transition and starter/reference data are complete; app-side service aliases now normalize old Week14 labels and To Quran website labels to the app service values. On 2026-05-29 Arabic Language was added as a distinct app/public service reference row so the website can send it separately from Quranic Arabic.

### TQ3. Family Workspace And Account Lifecycle Launch Verification

- Status: `done`
- Depends on: TQ1/TQ2
- Goal: Verify the reused Week14 Family Workspace, lifecycle gates, activation emails, account history, parent login, and student login are launch-ready for To Quran.
- Launch scope: this is a verification/adaptation gate, not a large rebuild. Fix only blockers that prevent intake review, transfer, activation, and parent/student account access from working.
- Launch gate: complete this verification before public website intake is connected.
- Current verification: automated family lifecycle, credential, transfer, booking milestone, staff/support, teacher assignment, and launch access suites pass locally; owner manual smoke confirmed launch access surfaces are working on 2026-05-29. Closed by launch access commit `529f7bc`.
- Website action: public copy must avoid immediate-login promises.
- DB action: no old To Quran user/student preservation dependency; keep the export as evidence and preserve only the Quran YouTube/video list later through Library planning.

### TQ3.5. Superadmin Staff User Management

- Status: `done`
- Depends on: TQ2/TQ3 access checks
- Goal: Provide a superadmin-owned screen/workflow to create, edit, activate/deactivate, and role-manage internal staff users: admins, customer support, and teachers.
- Current artifact: `docs/plans/active/2026-05-29-superadmin-staff-user-management.md`
- Related launch-access plan: `docs/plans/active/2026-05-29-launch-access-teacher-class-management-plan.md`
- Launch scope: this is required before deployment. It includes creating the first superadmin account who can manage everything after launch, using that account to manage staff users, assigning transferred families to customer support owners, and providing a launch-ready admin teacher/class assignment screen. It is staff account/support/class access administration only, not finance, HR, payroll, automated scheduling, or a full class-management product.
- Week14 reuse reference: inspect `D:\xampp\htdocs\week14-app-lms\docs\plans\active\2026-05-27-customer-support-phase1-native-task-workflow.md` before implementation. That plan treats staff/user management as a prerequisite for customer-support workflows and should inform To Quran launch sequencing.
- Support follow-up: keep launch support assignment (`parents.family_support_id`) active, but do a fresh Week14 LMS inspection before hardening support V1. Week14 has newer user/support improvements still in progress; To Quran should reuse those insights instead of inventing an isolated support workflow from the current imported skeleton.
- Website action: none directly, but public intake should not go live until app staff can manage the people who will process it.
- DB action: no schema change; first superadmin creation uses the guarded `toquran:bootstrap-superadmin` command with explicit `--confirm-db`. First real superadmin was created in `u504065335_to_quran`; execution evidence is in `database/manual/patches/2026-05-29-first-superadmin-bootstrap-execution-note.sql`. Support ownership uses existing `parents.family_support_id`; n8n/automation may read that assignment later but must not directly overwrite it during launch. Teacher/class assignment uses existing `teacher_subject_classes`, `class_subjects`, and `grade_level_subjects`. The LMS learning catalog was extended by guarded data patch `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data.sql` so the app has Quran Memorization, Arabic Language, Quranic Arabic, Sanad Program, MDJ, and Well Being. The launch default teacher account `drosamaqandil@gmail.com` was created in `u504065335_to_quran`; upcoming transfer-created class subjects resolve through `TOQURAN_DEFAULT_TEACHER_EMAIL` and remain editable per student subject. Local launch smoke data exists under `@toquran-smoke.test` / `[SMOKE]`, with active, pending, suspended, and archived family/child states, and must be removed before deployment using `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`.
- Closeout: implemented and verified in launch access commit `529f7bc`.

### TQ4. Core Tutoring Sessions And Tasks

- Status: `done`
- Depends on: TQ1/TQ2
- Goal: Reuse Week14 teacher/student/parent sessions, normal tasks, task approvals, protected attachments, and class/subject foundations for Quran/Arabic tutoring.
- Current launch-access artifact: `docs/plans/active/2026-05-29-launch-access-teacher-class-management-plan.md`
- Launch scope: smoke-test only before deployment: teacher login, student login, parent visibility, core session/task pages render, and basic assignment/visibility paths do not crash. Full tutoring workflow polish can continue after launch.
- Current verification: owner manual smoke confirmed teacher task creation, student visibility, parent visibility, and admin launch access flows are working on 2026-05-29. Automated focused task attachment tests, transfer tests, and broader launch-access tests pass locally. Closed by launch access commit `529f7bc`.
- Launch assumption: keep one current/active student per class for first deployment. Multi-student classes can be adapted later, but require a deliberate UX pass for per-student teacher actions such as Points Lab, rewards, behavior points, and task review.
- Website action: none unless public service claims change.
- DB action: launch task-type reference rows were added locally for Assignment, Lesson, Project, and Quiz so reused teacher session-task modals have selectable types during smoke testing.

### TQ5. My Deen Journey V1

- Status: `active`
- Depends on: TQ3/TQ4
- Goal: Adapt Week14 Journey, rewards, behavior/accountability points, consequence agreements, parent quick actions, and progress follow-up into My Deen Journey.
- Current branch: `codex/tq5-my-deen-journey-launch`
- Launch scope: focus on the app experience and launch smoke for MDJ/rewards/behavior/accountability. Do not turn this into production deployment, broad finance/scheduling work, or unrelated Library/automation implementation.
- Website action: align My Deen Journey public page claims with app reality.
- Launch cleanup: inherited school/current-school LMS fields should be silently defaulted for launch instead of blocking To Quran admins. Country is first-class user data because it affects time-zone and currency context. Booking child workflow edit access must remain available even when transfer is locked; transfer gating may lock transfer, but not the admin's ability to inspect/edit the child workflow.
- Future booking cleanup: legacy single-child bookings that still live only on the parent `bookings` row need a non-materializing CSV/export fallback and safer materialization. When reopened, `BookingList::displayableChildren(..., materializeLegacyChild: false)` should return a synthetic legacy child payload for export, and `legacyChildPayload()` should carry `consultation_date`, `consultation_time`, and `follow_up_date` into `scheduled_date`, `scheduled_time`, and `followup_date`. This is deferred because TQ5 launch/manual smoke uses current `booking_children` intake rows and CSV export is not part of the current launch path.
- DB action: `users.country` was added locally by guarded manual patch `database/manual/patches/2026-06-04-add-users-country.sql`; existing local smoke users were backfilled because there are no real users in the launch target. Guarded replay patches now also exist for legacy booking child normalization and silent school defaults: `database/manual/patches/2026-06-04-normalize-legacy-booking-children.sql` and `database/manual/patches/2026-06-04-heal-booking-child-school-defaults.sql`.

### TQ6. Library And Quran/Arabic Content Foundation

- Status: `implementation-review`
- Depends on: TQ4
- Goal: Create a To Quran-owned app Library foundation for Quran Memorization and Arabic content. Admins must be able to add/edit Quran Memorization materials such as new surahs, ayah-range/repetition subtitles, and YouTube links.
- Launch cleanup: no Week14 legacy Library here for launch. Remove or hide inherited Week14 Language and Literature Library sources/content from launch-facing Library surfaces before deployment.
- Scope decision: shared app Library materials are app-side and general for all subjects; all subject teachers can normally see them. Teachers can create LMS-style folders/resources inside the general Library so all teachers benefit; teachers edit only their own creations, while admins/superadmins can edit all.
- Website action: no public website Library/video work in TQ6; Quran videos live in the private app.
- Current artifact: `docs/plans/active/2026-06-06-tq6-library-quran-arabic-content-foundation-plan.md`
- Local implementation evidence: guarded structure patch created `general_library_folders` and `general_library_resources` for launch-facing shared Library content, with preserved `quran_library_surahs` / `quran_library_videos` left as non-launch technical scaffolding; launch-facing `teacher/library` now uses the To Quran shared Library; admin/superadmin have an admin Library tab at `admin/library`; teachers can create/edit-own shared folders/resources, admin/superadmin can edit all; folder `content_mode` now supports final source-only folders; preserved Quran Repetition videos were imported as `Quran Repetition` > per-surah folders > 106 YouTube sources; Quran repetition YouTube sources snapshot into normal task attachments. On 2026-06-13, malformed embed URLs were repaired, uploaded Library source originals were moved to private `local` storage, assigned uploaded files copy into independent task snapshots, stale structured Quran launch routes were removed, source/folder deletion and duplicate-title safeguards were tightened, owner manual smoke reported Library working, and final focused Library verification passed locally with 61 tests and 242 assertions.

### TQ7. Automation Tracks For Routines, Differentiated Tasks, And Series Tasks

- Status: `active`
- Depends on: TQ4/TQ6
- Goal: Reuse Week14 Versioned Routines, Differentiated Tasks, and Series Tasks after To Quran terminology and content source decisions.
- Current artifact: `docs/plans/active/2026-06-14-tq7-automation-routines-differentiated-series-plan.md`
- Current status note: TQ7 code implementation is review-ready; no DB execution was performed. TQ7.5 starter catalog work remains separate.
- Library note: when TQ7/TQ7.5 attaches Library content outside normal session tasks, reuse the TQ6 Quran/general Library adapter pattern for differentiated/daily-session/series attachment snapshots.
- Website action: none unless public pages mention automated routine features.

### TQ7.5. Prebuilt Routine And Series Task Launch Catalog

- Status: `active`
- Depends on: TQ4/TQ7 planning
- Goal: Before launch, create code-defined starter Versioned Routines and Series Tasks so teachers have ready-to-copy Well Being and My Deen Journey launch material instead of an empty automation catalog.
- Current artifact: `docs/plans/active/2026-06-14-tq7-automation-routines-differentiated-series-plan.md`
- Current status note: TQ7.5 code-defined Well Being and My Deen Journey starter catalog implementation is part of the current launch baseline; local guarded registry SQL, selected-teacher reset, and revised reinstall smoke were executed with backup/evidence. Current starter shape uses per-version task inclusion, the product-file Salah ladder order, concrete Well Being task checklists, expanded Wudu/Quran progression, Masjid/Prayer Adab, full parsed Morning Adhkar/Evening Adhkar/Dua Bank content from `to_quran_adhkar_dua_banks.md`, and installer-seeded General Library text sources for `My Deen Journey / Dua Bank`. Owner clarified on 2026-06-18 that the religious text has been reviewed and is correct for launch use. Owner decision: starter catalog is an admin-curated source recipe that copies teacher-owned draft automation rows into selected teacher workspaces, not true admin-owned shared automation rows.
- Launch scope: this is required before treating deployment as ready. Use code or guarded manual data artifacts so the catalog is reproducible; do not rely on one-off manual UI setup.
- App action: add a small admin/superadmin copy UI that calls the starter catalog installer service for selected active teachers with preview and result messages, then verify teachers can assign the copied starter routines/series.
- Website action: public website should not promise specific automated routine content, especially Quran/Arabic automation, until the relevant catalog items exist and are smoke-tested.
- DB action: use reviewed/manual data patches or code-level bootstrap commands with target checks and backup evidence; no destructive cleanup.

### TQ8. Arabic Vocabulary Games

- Status: `postponed`
- Depends on: post-deployment owner approval
- Goal: Plan Arabic vocabulary games using Week14 P7 architecture as reference, not first-import English content. TQ6 inspection found current Week14 vocabulary games are Latin/English-specific and not safe for Quranic Uthmani/Othmani symbols without a separate Quranic Arabic spike.
- Current spike brief: `quranic_arabic_game_codex_brief.md`
- Reuse note: preserve Week14 vocabulary game code/design patterns where safe for future Arabic adaptation, including game state, scoring, audio UI, option buttons, feedback animations, levels/categories, and attempt saving; do not expose English vocabulary content as launch material.
- Website action: do not promise until scoped.

### TQ9. Deployment Readiness And Public Website Handoff

- Status: `active`
- Depends on: TQ2, TQ3 launch verification, TQ3.5, and TQ4 launch smoke.
- Goal: Continue deployment readiness and public `toquran` website handoff without declaring production launch complete yet.
- Deployment guard: final deployment is not ready until the remaining class/session/task/automation/MDJ/rewards/Library smoke scope, inherited Language and Literature Library source cleanup, and TQ7.5 prebuilt routine/series catalog are explicitly closed or moved out of launch scope by owner decision.
- App action: confirm Composer audit remains clean, confirm production `.env`, build assets, storage link, queue/mail behavior, first superadmin/admin/teacher access, and final app smoke.
- Website action: public website shared-DB handoff has been implemented in `toquran` commit `6dfb71f`: booking writes use the Week14-style app-owned tables (`bookings`, `booking_children`, `booking_intake_review`, `booking_intake_review_children`, and `booking_intake_submission_locks`), Contact Us targets app-compatible `contacts`, booking references use `TQ-`, contact references use `CNT-`, and the launch public phone is `+201091051913`. Remaining website work is deployment smoke/verification against the intended shared app DB target.
- Current artifacts: local production-equivalent smoke and hardening evidence is in `docs/audits/2026-06-02-tq9-shared-db-smoke-and-hardening.md`; deployment phase plan is in `docs/plans/active/2026-06-18-tq9-launch-readiness-deployment-phases-plan.md`. HTTP parent/student/teacher login smoke passed after the shared website booking/contact flow, but the Composer audit must be rechecked because a new Laravel advisory appeared on 2026-06-17.
- Remaining app smoke: classes, sessions, normal tasks, automated tasks/versioned routines, My Deen Journey surfaces, rewards/points, Library/content access, inherited Language and Literature Library source cleanup, and teacher assignment from prebuilt routine/series catalogs.
- DB action: confirm real server backup/export/import path, verify starter/reference data, preserve the locally executed `contacts.child_age` nullable shape for public Contact Us, verify website/app share the intended app DB target, and preserve Quran YouTube/video extract for later Library migration. Transfer provisioning now activates selected optional public services such as Arabic Language and Sanad Program.
- Done when: public form to app intake/review/transfer/login smoke passes on the deployment target or a production-equivalent environment; class/session/task/automation/MDJ/rewards/Library smoke scope passes; inherited Language and Literature Library sources are removed/hidden from launch-facing Library surfaces; TQ7.5 prebuilt routine/series catalog exists and can be assigned by teachers; final smoke cleanup/credential rotation is complete; and JS dependency audit is resolved with the intended lockfile tool.
