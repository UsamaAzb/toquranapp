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
- Verification: local `toquranapp_local` has 352 tables; `/login` renders as `To Quran`.
- Website action: none yet; public website handoff waits until app-side target/schema is approved.
- DB action: local/app schema baseline created; starter/reference data remains a later explicit patch.

### TQ2. To Quran Service Catalog And Intake Foundation

- Status: `pending`
- Depends on: TQ1.5
- Goal: Adapt Week14 family/intake model to Quran Memorization, Quranic Arabic, My Deen Journey, Paid Parental Consultation, and Sanad Ijazah service interests.
- Website action: align public form values and reference prefix after app target is approved.
- DB action: manual SQL/map plan for service tables and intake rows.

### TQ3. Family Workspace And Account Lifecycle

- Status: `pending`
- Depends on: TQ1/TQ2
- Goal: Reuse Week14 Family Workspace, lifecycle gates, activation emails, and account history with To Quran language.
- Website action: public copy must avoid immediate-login promises.
- DB action: no old To Quran user/student preservation dependency; keep the export as evidence and preserve only the Quran YouTube/video list later through Library planning.

### TQ4. Core Tutoring Sessions And Tasks

- Status: `pending`
- Depends on: TQ1/TQ2
- Goal: Reuse Week14 teacher/student/parent sessions, normal tasks, task approvals, protected attachments, and class/subject foundations for Quran/Arabic tutoring.
- Website action: none unless public service claims change.

### TQ5. My Deen Journey V1

- Status: `pending`
- Depends on: TQ3/TQ4
- Goal: Adapt Week14 Journey, rewards, behavior/accountability points, consequence agreements, parent quick actions, and progress follow-up into My Deen Journey.
- Website action: align My Deen Journey public page claims with app reality.

### TQ6. Library And Quran/Arabic Content Foundation

- Status: `pending`
- Depends on: TQ4
- Goal: Reuse Week14 Library foundation/protected attachments, then define Quran/Arabic content taxonomy and migration path for useful To Quran content tables.
- Website action: decide public/content ownership for Quran course/surah content.

### TQ7. Automation Tracks For Routines, Differentiated Tasks, And Series Tasks

- Status: `pending`
- Depends on: TQ4/TQ6
- Goal: Reuse Week14 Versioned Routines, Differentiated Tasks, and Series Tasks after To Quran terminology and content source decisions.
- Website action: none unless public pages mention automated routine features.

### TQ8. Arabic Vocabulary Games

- Status: `postponed`
- Depends on: post-deployment owner approval
- Goal: Plan Arabic vocabulary games using Week14 P7 architecture as reference, not first-import English content.
- Website action: do not promise until scoped.
