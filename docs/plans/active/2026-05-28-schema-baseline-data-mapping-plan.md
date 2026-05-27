# To Quran Schema Baseline And Data Mapping Plan

Status: implemented locally; real DB transition requested
Date: 2026-05-28
Sprint: TQ1.5

## Objective

Create the Phase 2 DB strategy for the private To Quran LMS after the Week14 app foundation import. This plan establishes the local app DB target, schema source, starter-data strategy, preservation boundaries, and execution gates before any To Quran app DB setup is run.

## Current State

- Phase 1 app import is committed at `270e832 Import Week14 LMS foundation for To Quran`.
- The imported app boots locally and `/login` renders as `To Quran`.
- Current local `.env` is ignored and points to `toquranapp_local` for smoke testing.
- Owner direction on 2026-05-28 changed the deployment posture: target the real To Quran app DB name `u504065335_to_quran` for the accelerated deployment path.
- The old public To Quran export is backed up at `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`.
- The matched Week14 schema evidence is:
  - `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
  - `database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql`
  - `docs/audits/2026-05-28-week14-schema-freshness-check.md`

## Target DB Decision

Use this completed local dry-run DB target for Phase 2 proof:

```text
DB_DATABASE=toquranapp_local
```

Rationale:

- It is clearly local/app-specific.
- It is visually distinct from the public website DB name `u504065335_to_quran`.
- It avoids treating the old public export as the LMS target schema.

Accelerated real app DB target for deployment:

```text
u504065335_to_quran
```

That name was also the public website DB/export source, so it must not receive app baseline imports by accident. Real-target work is allowed only when the patch explicitly states the owner-approved deployment intent, confirms backup/export evidence, and documents preservation of the Quran YouTube/video list.

## Schema Source Strategy

Use the matched Week14 schema snapshot as structural source evidence. The To Quran app baseline should be derived from Week14's current LMS schema because the imported code expects those tables, indexes, and relationships.

Do not import Week14 rows blindly. Phase 2 schema work should separate:

- structure baseline;
- To Quran starter/reference data;
- optional legacy To Quran content migration;
- cleanup of old/export-only data.

## Data Classification

### Create Intentionally For To Quran

- app roles: `super_admin`, `admin`, `customer_support`, `teacher`, `parent`, `student`
- permission rows required by imported middleware and dashboards
- To Quran service catalog/reference values:
  - Quran Memorization
  - Quranic Arabic
  - My Deen Journey
  - Paid Parental Consultation
  - Sanad Ijazah
- minimal subject/service defaults needed for first admin/teacher setup
- app settings required by imported code paths

### Preserve For Later Mapping

- old Quran YouTube/video list from the public To Quran export

Target later destination:

- Library/content system, after TQ6 Library taxonomy is defined

### Do Not Preserve By Default

Owner clarified the old export has no client data that needs preservation. These old/export-only areas do not block app baseline setup:

- old users/students/parents/bookings/contact rows
- old course/quiz tables not needed for the private LMS baseline
- Laratrust legacy role tables
- public website implementation tables

### Skip Or Defer

- Week14 English vocabulary/Cambridge/phonics content rows
- Week14 QA/test accounts
- Week14 uploaded files, recordings, generated storage, and public content payloads
- Arabic vocabulary games and data model adaptation until TQ8/post-deployment planning

## Manual DB Artifact Plan

Before executing real-target DB setup, create durable artifacts under `database/manual/`:

1. `database/manual/patches/YYYY-MM-DD-create-toquranapp-local-baseline.sql`
   - target checks;
   - create/select `toquranapp_local`;
   - structural baseline derived from Week14 schema.
2. `database/manual/patches/YYYY-MM-DD-transition-u504065335_to_quran-to-app-baseline.sql`
   - fresh backup/export evidence;
   - target check for `u504065335_to_quran`;
   - explicit note that the target is intentional for accelerated app deployment;
   - preservation boundary for Quran YouTube/video list;
   - structural baseline derived from the verified Week14 schema.
3. `database/manual/patches/YYYY-MM-DD-toquran-starter-reference-data.sql`
   - roles/permissions;
   - service catalog/reference rows;
   - minimal app defaults.
4. `database/manual/patches/YYYY-MM-DD-toquran-legacy-video-library-mapping-notes.sql`
   - notes only at first, unless TQ6 Library migration is explicitly started.
5. Optional cleanup plan doc before any destructive cleanup of old/export-only data.

## Execution Gate

DB setup may run without separate owner approval only after all checks pass:

- backup/export evidence exists;
- target DB is verified as `toquranapp_local` for local proof work or `u504065335_to_quran` for the owner-approved real deployment target;
- connection is verified from `.env`, Laravel config, MySQL output, and SQL preflight guards;
- manual SQL or execution notes exist under `database/manual/`;
- command does not drop/truncate/overwrite the wrong DB;
- real-target work on `u504065335_to_quran` preserves or extracts the Quran YouTube/video list before destructive cleanup;
- destructive cleanup, if any, has a separate cleanup plan.

## Verification For Phase 2

Phase 2 is ready to close when:

- `toquranapp_local` target is documented and used consistently;
- baseline SQL/execution notes exist;
- app `.env.example` documents the intended local DB target without exposing secrets;
- imported app can connect to the baseline DB;
- `php artisan about`, `/login`, and focused tests still pass;
- `docs/shared/SHARED-DB-HANDOFF.md` records the target DB and schema-source decision.

Status: complete on 2026-05-28.

Result:

- baseline patch: `database/manual/patches/2026-05-28-create-toquranapp-local-baseline.sql`
- execution note: `database/manual/patches/2026-05-28-toquranapp-local-baseline-execution-note.sql`
- local app schema snapshot: `database/manual/baseline/2026-05-28-toquranapp-local-schema.sql`
- local app DB: `toquranapp_local`
- table count: 352
- data imported: none
- smoke check: `/login` returned 200 with title `To Quran | Login`

Next real-target direction:

- real app DB target: `u504065335_to_quran`
- reason: owner requested accelerated deployment within two days
- required before mutation: fresh backup/export confirmation, real-target guarded patch, and explicit Quran YouTube/video-list preservation note

## Open Caveats

- Composer security advisories inherited from Week14 must be resolved before deployment.
- Production/real DB naming is now owner-directed as `u504065335_to_quran`; `toquranapp_local` remains the local proof baseline.
- Public website intake handoff waits until app-side DB setup and TQ2 service/intake rules are ready.
