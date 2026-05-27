# toquranapp - AI Agent Guidelines

This repo is the private To Quran LMS for `app.toquran.org`.

It is paired with:

- `D:\xampp\htdocs\toquranapp` - private app/LMS and shared schema authority
- `D:\xampp\htdocs\toquran` - public website and intake entry point
- `D:\xampp\htdocs\week14-app-lms` - source implementation to reuse selectively

## Before Starting Work

- Read `docs/WORKFLOW.md`.
- Check the current To Quran roadmap in `docs/TOQURAN-SPRINTS.md`.
- For product, role, service, intake, or business-logic changes, read `docs/TOQURAN-LOGIC.md` and `docs/shared/TERMINOLOGY-AND-SERVICES.md`.
- For DB-adjacent work, read `docs/DB-SAFETY-POLICY.md` and `database/manual/README.md`.
- For public-website handoff or shared decisions, read every file in `docs/shared/`.
- Approval of an audit or plan does not authorize implementation. Wait for an explicit import/build/implement request.

## Reuse-First Rule

Week14 is the source implementation. Prefer copying/adapting its proven Laravel 12, Jetstream, Spatie, Livewire, Vuexy, services, tests, docs, and manual-SQL workflow over rebuilding from scratch.

Reuse does not mean blind copying:

- Keep To Quran service language, parent/student/teacher/admin flows, and My Deen Journey intent.
- Keep app/LMS schema authority in this repo.
- Convert Week14 academic/tutoring assumptions only where they fit Quran/Arabic tutoring.
- Defer English vocabulary game imports. Arabic vocabulary games are a post-deployment planning item.

## Database Rules

- Create or confirm an export before any DB investigation that may lead to change.
- Codex may perform To Quran local/app DB setup and schema work without separate owner approval when a backup/export exists, the target DB is verified as a To Quran local/app DB, and the action is not aimed at the public/live website DB by accident.
- Do not drop, truncate, clean, or overwrite old/export-only data without documenting the cleanup plan first.
- Do not run Laravel migrations, seeders, `migrate:fresh`, `db:wipe`, or import/restore commands against a public/live website DB target.
- Durable schema/data changes must be written as manual SQL or migration notes under `database/manual/`, with target checks and backup evidence.
- Treat public website migrations as consumer artifacts, not app schema authority.

## Ownership

- `toquranapp` owns app schema, app business logic, transfer rules, workflow conventions, and shared decisions.
- `toquran` consumes shared decisions for public content, pricing/intake copy, and the public consultation handoff.
- If a decision affects both repos, record it in `docs/shared/` and state implementation ownership.

## Current Audit State

As of 2026-05-27, this directory started as an empty/non-git app workspace. The first audit created planning/docs artifacts and captured:

- backup copy: `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
- Week14 live schema snapshot: `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- Week14 freshness check: `docs/audits/2026-05-28-week14-schema-freshness-check.md`

As of 2026-05-28, Phase 1 app skeleton import has been performed and committed:

- commit: `270e832 Import Week14 LMS foundation for To Quran`
- source: current Week14 working tree on branch `2028-vocabulary-intervention` at `c5d5af9`
- scope: Laravel/LMS foundation, app code, tests, config, views, routes, and static skeleton assets
- excluded/removed from the import: runtime uploads, copied public content payloads, logs, generated storage, vendor, node_modules, old SQL dumps, and To Quran planning/manual DB docs
- verification: `/login` returned 200 with title `To Quran | Login`; focused auth/PWA/credential tests passed

Current DB state: Phase 2 local schema baseline is complete in `toquranapp_local` with 352 tables and no imported rows. Starter/reference data is still pending and must be created intentionally in a later patch. Do not target the public/live website DB `u504065335_to_quran`.
