# Real DB Transition And Starter Data Plan

Status: active
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

1. Create or confirm a fresh backup/export for `u504065335_to_quran`.
2. Verify the active Laravel `.env`, Laravel config, and MySQL connection are all pointing to the intended DB target.
3. Extract or document the preservation path for the Quran YouTube/video list before destructive cleanup.
4. Write a guarded manual SQL patch under `database/manual/patches/` that:
   - selects or creates `u504065335_to_quran`;
   - checks `DATABASE() = 'u504065335_to_quran'`;
   - states that the real target is intentional for accelerated deployment;
   - includes backup evidence comments;
   - avoids importing Week14 rows blindly.
5. Write an execution note after running the patch.

## Starter/Reference Data Scope

Create intentionally for To Quran:

- roles: `super_admin`, `admin`, `customer_support`, `teacher`, `parent`, `student`
- required permission rows for imported middleware and dashboards
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
- align service interest values with the app service catalog;
- decide whether public intake writes directly to app-owned tables or calls an app-owned endpoint;
- preserve or replace public Quran video display using the later Library migration path.

## Verification

The transition is ready to close when:

- fresh real-target backup/export exists;
- real-target baseline patch and execution note exist;
- `u504065335_to_quran` has the expected app schema table count;
- no Week14 rows were blindly imported;
- starter/reference data patch is documented and scoped;
- `/login` still renders as `To Quran`;
- focused auth/PWA/credential tests still pass.
