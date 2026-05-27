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
- Status: Approved for Phase 1 and Phase 2 local baseline.

### 2026-05-27 - Reuse Week14 Selectively
- Decision: Week14 LMS is the source implementation, but Week14 product content and sprint history must not be copied blindly.
- Why: The app should reuse proven Laravel/Livewire/schema/workflow modules while adapting the business to Quran and Arabic tutoring.
- App/LMS impact: Copy/adapt Week14 code after owner approval according to the selective reuse plan.
- Website impact: Website handoff rules and service labels must follow To Quran docs, not Week14 naming.
- Owner: `toquranapp`
- Follow-up: Create intentional To Quran starter/reference data, then proceed to TQ2 service catalog and intake adaptation.
- Status: Approved for Phase 1 and Phase 2 local baseline.

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
- Follow-up: Create the real-target transition patch, starter/reference data patch, and public website handoff checklist before launch.
- Status: Approved for accelerated deployment path.
