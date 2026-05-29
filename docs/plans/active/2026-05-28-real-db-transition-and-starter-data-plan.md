# Real DB Transition And Starter Data Plan

Status: real DB baseline and starter/reference data executed locally
Date: 2026-05-28
Sprint: TQ1.5 exit gate before TQ2

## Objective

Move from the completed local proof baseline in `toquranapp_local` to the real To Quran app DB target `u504065335_to_quran`, then create intentional starter/reference data for the first deployable LMS.

## Owner Direction

On 2026-05-28, owner directed Codex to target the real DB because deployment is needed within two days.

This changes the execution target, not the safety requirements:

- `toquranapp_local` remains the completed dry-run/proof baseline.
- `u504065335_to_quran` is now the real app DB target.
- The Quran YouTube/video list is the only legacy data intentionally preserved from the old To Quran export.
- No client data preservation is required from the old To Quran DB/export.

## Required Before Real DB Mutation

1. Create or confirm backup/export evidence for `u504065335_to_quran`.
2. Verify the active Laravel `.env`, Laravel config, and MySQL connection are all pointing to the intended DB target.
3. Extract or document the preservation path for the Quran YouTube/video list before destructive cleanup.
4. Write a guarded manual SQL patch under `database/manual/patches/` that:
   - selects or creates `u504065335_to_quran`;
   - checks `DATABASE() = 'u504065335_to_quran'`;
   - for correction/data patches after the baseline, requires an explicit operator confirmation variable or equivalent instance-level guard;
   - states that the real target is intentional for accelerated deployment;
   - includes backup evidence comments;
   - avoids importing Week14 rows blindly.
5. Write an execution note after running the patch.

## Starter/Reference Data Scope

Create intentionally for To Quran:

- roles: `super_admin`, `admin`, `customer_support`, `teacher`, `parent`, `student`
- explicit permission rows only after a To Quran permission list is defined; the current imported access gates are role-based
- service catalog values:
  - Quran Memorization
  - Quranic Arabic
  - My Deen Journey
  - Paid Parental Consultation
  - Sanad Ijazah
- minimal app defaults needed for first admin/teacher setup

Do not include:

- Week14 QA/test accounts
- Week14 English vocabulary, Cambridge, SAT, or phonics content
- Arabic vocabulary games
- old To Quran users/bookings/contact rows by default
- Quran YouTube/video Library migration, except preservation notes before cleanup

## Public Website Risk

Because `u504065335_to_quran` is also the public website DB name/export source, replacing its structure may break old public website code that expects old tables. That risk is accepted only as part of the accelerated deployment path and must be paired with a public website handoff plan.

Minimum website follow-up before launch:

- update public sign-in links to `https://app.toquran.org/login`;
- align service interest values with the app service catalog, including Arabic Language as a distinct public/app service value when the website handoff starts;
- decide whether public intake writes directly to app-owned tables or calls an app-owned endpoint;
- preserve or replace public Quran video display using the later Library migration path.

## Verification

The transition is ready to close when:

- backup/export evidence exists;
- real-target baseline patch and execution note exist;
- `u504065335_to_quran` has the expected app schema table count;
- no Week14 rows were blindly imported;
- starter/reference data patch is documented and scoped;
- `/login` still renders as `To Quran`;
- focused auth/PWA/credential tests still pass.

## Execution Result

Completed locally on 2026-05-28:

- real-target baseline patch: `database/manual/patches/2026-05-28-transition-u504065335_to_quran-to-app-baseline.sql`
- execution note: `database/manual/patches/2026-05-28-u504065335_to_quran-baseline-execution-note.sql`
- preservation extract: `database/manual/backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql`
- real app DB target: `u504065335_to_quran`
- table count: 352
- imported rows: none
- verification: `/login` returned HTTP 200 as `To Quran | Login`; focused auth/PWA/credential tests passed
- starter/reference patch: `database/manual/patches/2026-05-28-toquran-starter-reference-data.sql`
- starter/reference execution note: `database/manual/patches/2026-05-28-toquran-starter-reference-data-execution-note.sql`
- starter/reference rows: roles, service catalog values, current operating year, program, learner levels, To Quran subjects, and grade-level subject mappings
- framework infrastructure correction patch: `database/manual/patches/2026-05-28-add-framework-infrastructure-indexes.sql`
- framework infrastructure correction note: `database/manual/patches/2026-05-28-framework-infrastructure-indexes-execution-note.sql`
- Library column correction patch: `database/manual/patches/2026-05-28-fix-library-dp-global-context-column.sql`
- Library column correction note: `database/manual/patches/2026-05-28-library-column-correction-execution-note.sql`
- Library identifier drift correction patch: `database/manual/patches/2026-05-28-fix-library-schema-identifier-drift.sql`
- Library identifier drift correction note: `database/manual/patches/2026-05-28-library-schema-identifier-drift-execution-note.sql`
- schema snapshot after DB corrections: `database/manual/baseline/2026-05-28-u504065335_to_quran-app-schema-after-db-corrections.sql`
- current blocker before TQ2 intake transfer: first admin/teacher account decision and To Quran adaptation of remaining Week14 intake/test fixtures

## Post-Review Corrections

CodeRabbit correctly identified that the initial real-target baseline omitted keys/indexes on several framework infrastructure tables. The follow-up correction restored the Laravel cache/session/job/password reset table keys, Sanctum token keys, and Spatie pivot keys/foreign keys. The credential-column comment was reviewed but not applied as a schema drop in this pass: `recoverable_password_encrypted` is an encrypted, active workflow field; `pin_unhash` is plaintext compatibility debt that requires app-code changes; `decryp_password` is legacy nullable plaintext and should remain unwritten or be removed in a focused hardening pass.

CodeRabbit also correctly identified a malformed imported Library column named ` general_library_dp_unit_id` with a leading space. The live real-target DB was corrected to `general_library_dp_unit_id`, and the To Quran-owned baseline replay files were corrected so fresh targets do not recreate the typo. Starter/reference data was also hardened with fail-fast drift checks for canonical fixed IDs.

CodeRabbit later identified more imported Library identifier drift: embedded spaces in `teacher and_student_questions` and an MYP table using a DP unit column name. Those were corrected in the real target and To Quran-owned replay artifacts. The framework index correction patch was also hardened with orphan checks before adding Spatie foreign keys.
