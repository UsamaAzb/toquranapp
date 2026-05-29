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
- Follow-up: Proceed to TQ3/TQ3.5 launch verification, then public website handoff under TQ9.
- Status: Approved through TQ2 app-side service/intake adaptation merged to `main` in `0b99741`.

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
- Follow-up: Continue TQ3/TQ3.5 launch checks, then public form labels/reference prefix under TQ9.
- Status: Approved and reflected in TQ2 app-side implementation merged to `main`.

### 2026-05-29 - Superadmin Staff User Management Is Required For Launch
- Decision: Before public intake is connected for launch, superadmin needs a clear app surface to create and manage internal staff users, especially admins and teachers.
- Why: The app cannot rely on manual database edits for the people who will operate intake, teaching, and support after launch.
- App/LMS impact: Add or confirm a staff-user management workflow using existing `users`, Spatie roles, and teacher/admin profile patterns where possible. Scope is account/role management only. The first launch superadmin account must be created and documented as part of this gate.
- Website impact: Public form and Contact Us handoff should wait until app-side staff users can be managed by superadmin.
- Owner: `toquranapp`
- Follow-up: Treat this as the next launch branch after TQ2 service/intake merge.
- Status: Approved for launch checklist.

### 2026-05-29 - Website Contact Phone For Launch
- Decision: Public To Quran website contact surfaces should use `+201091051913` for launch instead of the current number.
- Why: Owner explicitly identified this as a public website sprint item to avoid missing it during the app-first launch work.
- App/LMS impact: None directly, except shared deployment docs should carry the reminder until public website handoff.
- Website impact: Update visible Contact Us phone links/text and any `tel:` links during the `toquran` repo handoff.
- Owner: `toquran`
- Follow-up: Track in the public website sprint doc and verify during TQ9 end-to-end handoff.
- Status: Approved for public website handoff.
