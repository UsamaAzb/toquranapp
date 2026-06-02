# To Quran Shared Decision Log

## Purpose

Record decisions that affect both:

- `D:\xampp\htdocs\toquranapp` - private LMS/app
- `D:\xampp\htdocs\toquran` - public website

Use this for product names, service definitions, intake behavior, DB ownership, deployment conventions, and cross-repo workflow rules.

## Entry Format

### YYYY-MM-DD - Topic
- Decision:
- Why:
- App/LMS impact:
- Website impact:
- Owner:
- Follow-up:
- Status:

## Decisions

### 2026-05-27 - App/LMS Schema Ownership
- Decision: `toquranapp` owns the To Quran LMS schema, app business logic, transfer rules, and shared decision docs. `toquran` consumes those decisions for public content and intake.
- Why: The private app will carry parent/student/teacher/admin workflows, tasks, rewards, behavior/accountability points, consequence agreements, Library, and My Deen Journey logic. The public website should not become the schema authority by accident.
- App/LMS impact: Manual DB artifacts, future schema patches, and import strategy live in `toquranapp`.
- Website impact: Public intake and copy must align with shared docs and may need later updates after the app schema is approved.
- Owner: `toquranapp`
- Follow-up: Mirror or reference the shared docs from the website repo before public intake handoff work begins.
- Status: Approved; app-side baseline and TQ2 service/intake adaptation are merged to `main` through `0b99741`.

### 2026-05-27 - Reuse Week14 Selectively
- Decision: Week14 LMS is the source implementation, but Week14 product content and sprint history must not be copied blindly.
- Why: The app should reuse proven Laravel/Livewire/schema/workflow modules while adapting the business to Quran and Arabic tutoring.
- App/LMS impact: Copy/adapt Week14 code after owner approval according to the selective reuse plan.
- Website impact: Website handoff rules and service labels must follow To Quran docs, not Week14 naming.
- Owner: `toquranapp`
- Follow-up: Proceed with public website handoff under TQ9.
- Status: Approved through TQ3/TQ3.5/TQ4 launch access closeout in `529f7bc`.

### 2026-05-27 - My Deen Journey Is a To Quran Service
- Decision: My Deen Journey is a To Quran service, not a direct rename of Week14 Journey.
- Why: It includes learner task journey experience, rewards, behavior/accountability points, consequence agreements, parent involvement, and progress follow-up in an Islamic parenting/Quran learning context.
- App/LMS impact: Reuse Week14 Journey, points, rewards, consequence, approval, and task surfaces, but reframe service language and service boundaries.
- Website impact: Public pages may describe My Deen Journey, but app implementation and lifecycle rules belong to `toquranapp`.
- Owner: `toquranapp`
- Follow-up: Define service-to-app mapping in `TERMINOLOGY-AND-SERVICES.md`.
- Status: Approved for Phase 1 planning scope; detailed service behavior still lands in later implementation phases.

### 2026-05-27 - Arabic Vocabulary Games Deferred
- Decision: Vocabulary games should be planned as Arabic vocabulary games after deployment, not imported as English Week14 P7 in the first import.
- Why: Week14 P7 is large, English/Cambridge/phonics oriented, and still carries implementation-drift decisions. To Quran needs Arabic vocabulary content and a post-deployment plan.
- App/LMS impact: Do not include vocabulary games in the first app import. Preserve the Week14 P7 architecture as a future reference only.
- Website impact: Do not promise public Arabic vocabulary games until the app roadmap approves them.
- Owner: `toquranapp`
- Follow-up: Add a deferred sprint for Arabic vocabulary games.
- Status: Approved for Phase 1.

### 2026-05-28 - Accelerated Real DB Target
- Decision: Use `u504065335_to_quran` as the real To Quran app DB target for accelerated deployment. Keep `toquranapp_local` as the completed dry-run/proof baseline.
- Why: Owner wants deployment within two days and confirmed the old To Quran DB/export has no client data requiring preservation except the Quran YouTube/video list.
- App/LMS impact: Real-target schema and starter/reference data patches must intentionally target `u504065335_to_quran`, cite backup/export evidence, and use SQL preflight guards.
- Website impact: The public website must be updated or coordinated because it previously used the same DB name and may expect old tables.
- Owner: `toquranapp`
- Follow-up: Create public website handoff checklist and verify real server backup/deploy path before launch.
- Status: Merged to `main` through `0b99741`.

### 2026-05-28 - Starter Reference Data Baseline
- Decision: Create only minimal starter/reference rows for the deployable To Quran app baseline: roles, service values, current operating year, To Quran program, learner levels, To Quran subjects, and grade-level subject mappings. The `owner` role is included as a compatibility carry-forward because imported Week14 routes still reference it; it is not yet an approved To Quran product role.
- Why: TQ2 intake/family transfer needs known roles, service values, `AcademicYear::currentId()`, and grade-level subject mappings, but user/client accounts and content imports should remain intentional later steps.
- App/LMS impact: `u504065335_to_quran` now has starter/reference rows and no user/client rows. `BookingSubjectProvisioning` was adapted so subject ids 1, 2, and 15 map to Quran Memorization, Quranic Arabic, and My Deen Journey.
- Website impact: Public intake service values should align to the five app service values before launch.
- Owner: `toquranapp`
- Follow-up: Continue with first admin/teacher account creation path and public website service-value handoff.
- Status: Merged to `main` through `0b99741`; TQ2 app-side aliases landed in `124756b`.

### 2026-05-28 - Launch Scope Keeps Operations Manual
- Decision: First deployment reuses the Week14-style manual operations model for consultation scheduling, finance, detailed class management, and teacher assignment decisions. TQ2 should focus on To Quran service/intake semantics and transfer readiness, not new scheduling or finance systems.
- Why: Owner needs launch within two days and confirmed those operations are already handled manually in Week14.
- App/LMS impact: Keep imported intake, review, transfer, account lifecycle, LMS access, sessions, and task foundations. Do not block launch on management systems Week14 does not already have.
- Website impact: Public website should not promise automated scheduling, finance, or class-management features that the app does not yet own.
- Owner: `toquranapp`
- Follow-up: Continue with public form labels/reference prefix under TQ9.
- Status: Approved and reflected through launch access closeout in `529f7bc`.

### 2026-05-29 - Superadmin Staff User Management Is Required For Launch
- Decision: Before public intake is connected for launch, superadmin needs a clear app surface to create and manage internal staff users, especially admins and teachers.
- Why: The app cannot rely on manual database edits for the people who will operate intake, teaching, and support after launch.
- App/LMS impact: Add or confirm a staff-user management workflow using existing `users`, Spatie roles, and teacher/admin profile patterns where possible. Scope is account/role management only. The first launch superadmin account must be created and documented as part of this gate.
- Website impact: Public form and Contact Us handoff should wait until app-side staff users can be managed by superadmin.
- Owner: `toquranapp`
- Follow-up: Use the Staff Users page for ongoing admin/support/teacher management; rotate temporary credentials before production launch.
- Status: Implemented and committed in `529f7bc`; first real superadmin bootstrapped in `u504065335_to_quran`; owner manual smoke passed on 2026-05-29.

### 2026-05-29 - Family Support Assignment Is App-Owned
- Decision: The LMS owns transferred-family customer-support assignment through `parents.family_support_id`. Admins/superadmins may assign or clear an active `customer_support` owner from the Transferred Families page; customer support users may view assigned families but not reassign ownership.
- Why: Launch needs a simple way to say which support person is responsible for a family without building a full support-ticket or automation system.
- App/LMS impact: Add the support-owner display and assignment action to the transferred-family workflow using the existing parent/user relationship. Future support dashboards and notifications should route from this assignment.
- Website impact: None directly; public intake still hands families to the app, and app staff owns responsibility after transfer.
- Owner: `toquranapp`
- Follow-up: When n8n/WhatsApp/notification automation is added, let automation read the assignment or submit app-reviewed change requests. Do not allow n8n to directly overwrite `parents.family_support_id` during launch.
- Status: Implemented and committed in `529f7bc`.

### 2026-05-29 - Website Contact Phone For Launch
- Decision: Public To Quran website contact surfaces should use `+201091051913` for launch instead of the current number.
- Why: Owner explicitly identified this as a public website sprint item to avoid missing it during the app-first launch work.
- App/LMS impact: None directly, except shared deployment docs should carry the reminder until public website handoff.
- Website impact: Update visible Contact Us phone links/text and any `tel:` links during the `toquran` repo handoff.
- Owner: `toquran`
- Follow-up: Track in the public website sprint doc and verify during TQ9 end-to-end handoff.
- Status: Approved for public website handoff.

### 2026-05-29 - LMS Learning-Class Catalog And MDJ / Well Being Boundary
- Decision: The To Quran app-side LMS class-subject catalog is Quran Memorization, Arabic Language, Quranic Arabic, Sanad Program, My Deen Journey / `MDJ`, and Well Being. My Deen Journey and Well Being are fixed LMS surfaces available to all students and/or one or more class subjects. Inherited Week14 school-subject classes/subjects should be kept inactive where practical for future MDJ expansion.
- Why: Launch needs clear teacher/class assignment choices now, while preserving a future path for MDJ to support broader school-subject help without re-importing Week14 academic subjects later.
- App/LMS impact: Teacher-class assignment, subject provisioning, and future starter/reference data patches must use this catalog. Parent-written behavior points must continue to affect Well Being only through `ParentBehaviorSubjectResolver`, not MDJ.
- Website impact: Public copy may talk about My Deen Journey, Quran/Arabic, and Sanad services, but should not expose inactive Week14 school subjects or imply launch-time school-subject tutoring unless approved.
- Owner: `toquranapp`
- Follow-up: Use the 6-subject catalog in TQ4 launch smoke and keep inactive Week14 school subjects hidden by default unless future MDJ expansion is approved.
- Status: Approved by owner clarification; guarded reference-data patch executed locally in `u504065335_to_quran`; teacher assignment UI uses the catalog in `529f7bc`.

### 2026-05-29 - Launch Default Teacher For Transfers
- Decision: Upcoming transferred students should be assigned initially to the configured launch default teacher account, currently `drosamaqandil@gmail.com`, while remaining editable from Student Account > Subject Access.
- Why: Launch operations need a real teacher account receiving new transfer-created class subjects by default, without blocking on a full class-management workflow.
- App/LMS impact: Transfer provisioning and Student Account subject sync resolve the teacher through `TOQURAN_DEFAULT_TEACHER_EMAIL` via `DefaultTeacherResolver`; if no active teacher can be resolved, the transfer/sync fails loudly instead of silently falling back to a Week14 user id.
- Website impact: None directly; public intake still hands off to the app and the app owns teacher assignment after transfer.
- Owner: `toquranapp`
- Follow-up: Set `TOQURAN_DEFAULT_TEACHER_EMAIL` on production and verify first production-equivalent transfer assigns the expected teacher before public intake goes live.
- Status: Implemented and committed in `529f7bc`; real teacher account created in `u504065335_to_quran`.

### 2026-05-29 - Public Intake Child Service Selector
- Decision: The launch public booking form should support multiple children, and each child should be able to select one or more child-facing services: Quran Memorization, Quranic Arabic, Arabic Language, Sanad Ijazah Program, and My Deen Journey.
- Why: Owner wants the public `toquran` repo handoff to align with the app's review-first intake model and to distinguish Arabic Language from Quranic Arabic.
- App/LMS impact: `BookingServiceInterest` now treats Arabic Language as a distinct canonical service instead of normalizing it into Quranic Arabic. The real app DB target now includes `services_types.value = Arabic Language` and `services.id = 6 / name = Arabic Language`.
- Website impact: Public website commit `6dfb71f` implements the multi-child/per-child multi-service shared-DB handoff to app-owned booking/review tables.
- Owner: `toquranapp` owns the service contract; `toquran` owns the public form implementation.
- Follow-up: Run deployment-target smoke for clean booking, review booking, Contact Us, app review, transfer, and login.
- Status: App-side service contract implemented and committed in `529f7bc`; DB patch executed in `u504065335_to_quran`.

### 2026-06-02 - Public Website Uses Week14-Style Shared App DB Handoff
- Decision: To Quran should follow the Week14 website/LMS intake pattern for launch: the public website writes directly into app-owned LMS intake/contact tables in the shared app DB, instead of creating a delayed app import bridge from legacy JSON rows.
- Why: Week14 website already proves the safer pattern for these paired repos: public form UX lives on the website, while clean/review decisions, child rows, submission locks, and contact rows are written into LMS-owned tables immediately. Since To Quran is using the same accelerated shared DB deployment posture, an import bridge adds delay and split-brain risk without clear benefit.
- App/LMS impact: `toquranapp` remains schema and workflow authority. App-owned target tables for website booking are `bookings`, `booking_children`, `booking_intake_review`, `booking_intake_review_children`, and `booking_intake_submission_locks`; app-owned target table for Contact Us is `contacts`.
- Website impact: `toquran` commit `6dfb71f` adapts the Week14 website booking/contact implementation: To Quran service values and `TQ-` references, direct child rows, duplicate/repeat/blocked/contact-mismatch intake review routing, `CNT-` contact references, and Contact Us writes to `contacts` instead of `contact_us` / `massage`.
- Owner: `toquranapp` owns schema/rules; `toquran` owns public route/UI implementation.
- Follow-up: Preserve the same shared DB target and app schema shape during production deploy/import, then repeat the public booking/contact smoke on the server target after cleanup/rotation.
- Status: Implemented in public website commit `6dfb71f`; local production-equivalent shared DB smoke passed on 2026-06-02.

### 2026-06-02 - Contact Us Does Not Require Child Age
- Decision: Generic public Contact Us submissions should write to the app-owned `contacts` table without requiring or faking `child_age`; the column remains present but becomes nullable.
- Why: Contact Us is not always tied to a learner. Requiring child age is an inherited Week14 schema constraint that breaks the To Quran public website handoff under strict MySQL/MariaDB inserts.
- App/LMS impact: App-owned manual patch `database/manual/patches/2026-06-02-make-contacts-child-age-nullable.sql` changes `contacts.child_age` from `NOT NULL` to `NULL DEFAULT NULL` after target and backup/export evidence are confirmed. No contact data is inserted, updated, or deleted by the patch.
- Website impact: `toquran` should write generic contact rows to `contacts` without adding fake child-age placeholders, and its shared-DB tests should treat `contacts.child_age` as nullable after the app patch.
- Owner: `toquranapp` owns the schema patch; `toquran` owns public Contact Us implementation and tests.
- Follow-up: Run website Contact Us to app DB smoke and ensure production import/deploy preserves the nullable `contacts.child_age` shape.
- Status: Executed locally against `u504065335_to_quran` on 2026-06-02 after target verification and focused structure backup evidence.

### 2026-06-02 - TQ9 Shared DB Smoke And Deployment Hardening
- Decision: Treat the Week14-style shared DB handoff as launch-ready locally after a production-equivalent smoke against the real app DB name, while keeping cleanup/rotation as the final deployment gate.
- Why: Owner wants the app and website to move quickly using the same shared-table pattern as Week14. The local smoke proved clean booking, duplicate/review routing, generic Contact Us, app transfer, account activation, parent/student/teacher login, and teacher class visibility without adding an import bridge.
- App/LMS impact: `contacts.child_age` is nullable locally; transfer provisioning now activates optional launch subjects from each child's selected `service_interests`; Composer advisories are clear after dependency hardening. Smoke data and temporary credentials remain local testing aids only.
- Website impact: Public website commit `6dfb71f` can continue writing to app-owned booking/review/contact tables as long as production deploy preserves the app schema shape and shared DB target.
- Owner: `toquranapp` owns schema, transfer rules, and deployment gates; `toquran` owns public form UX and public route implementation.
- Follow-up: Before production launch/export, verify server DB target and backup, clean `@toquran-smoke.test` / `[SMOKE]` / `SMOKE-TQ-*` rows, rotate temporary credentials, verify queue/mail/storage/build assets, and settle the JavaScript audit lockfile/tooling strategy.
- Status: Local smoke and Composer hardening complete on 2026-06-02; production cleanup/rotation still pending.

### 2026-05-29 - Launch Task Type Reference Data
- Decision: Seed minimal teacher-facing launch task-type reference rows: Assignment, Lesson, Project, and Quiz.
- Why: The reused Week14 teacher session-task modal requires `task_types` rows. Without these rows the TQ4 task creation dropdown is empty. Assignment remains id 7 because the reused modal defaults new normal tasks to id 7.
- App/LMS impact: Teachers can create normal session tasks during launch smoke. The reference-data patch is guarded and idempotent with fixed-ID drift checks.
- Website impact: None directly; public copy can say tutoring tasks exist only after TQ4 smoke passes.
- Owner: `toquranapp`
- Follow-up: Keep Assignment as default during future task taxonomy work.
- Status: Implemented and committed in `529f7bc`; DB patch executed in `u504065335_to_quran`; owner manual task/student/parent smoke passed on 2026-05-29.

### 2026-05-29 - Correct Mistaken Attachment-Kind Task Types
- Decision: Remove the same-day mistaken Activity/File/YouTube/Link task-type rows and correct the real DB to Assignment/Lesson/Project/Quiz.
- Why: File, YouTube, and Link are attachment kinds in the task modal, not task types. Keeping them in `task_types` would make the teacher UI misleading and would conflict with the expected Week14 launch workflow.
- App/LMS impact: Corrects the teacher task Type dropdown and keeps Assignment as the default normal task type.
- Website impact: None.
- Owner: `toquranapp`
- Follow-up: Keep Assignment as default during future task taxonomy work.
- Status: Implemented and committed in `529f7bc`; correction patch executed in `u504065335_to_quran`; owner manual task smoke passed on 2026-05-29.
