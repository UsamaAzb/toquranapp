# Deployment And Workflow Handoff

## Purpose

Track shared deployment, runtime, and AI workflow decisions between the To Quran app and public website.

## Current State

- `toquranapp` started as an empty/non-git directory.
- `toquran` is a Laravel 10 public site with vendor files present and no project docs/shared system.
- Week14 LMS is Laravel 12 with Jetstream, Livewire 3, Spatie Permission, Vuexy, manual DB artifacts, workflow docs, sprint docs, and tests.

## Workflow Direction

To Quran should reuse Week14's workflow structure:

- `AGENTS.md`
- `docs/WORKFLOW.md`
- `docs/DB-SAFETY-POLICY.md`
- `database/manual/`
- `docs/plans/active/`
- `docs/plans/archive/`
- shared decision docs
- sprint roadmap docs
- durable business-logic docs

Do not copy Week14 sprint order blindly. Create To Quran-specific sprints.

## Deployment Notes To Carry Forward Later

When app code is imported, verify:

- `APP_NAME`, `APP_URL`, mail sender, Vite app name, and route domains are To Quran-specific.
- no Week14 public URLs, emails, QA accounts, or service labels remain.
- queue worker requirements are documented if activation emails or queued mail are imported.
- storage/public file delivery rules are documented before uploading app assets.
- public website sign-in link remains `https://app.toquran.org/login`.

## Local Development Ports

Use separate local web origins so the connected repos do not collide:

| Repo | Local URL | Notes |
| --- | --- | --- |
| `D:\xampp\htdocs\toquranapp` | `http://127.0.0.1:8014` | To Quran private LMS/app |
| `D:\xampp\htdocs\week14-app-lms` | `http://localhost:8000` | Week14 LMS source/reference |
| `D:\xampp\htdocs\yonfiqoon` | `http://127.0.0.1:8011` | Yonfiqoon app/site |

For this repo, run:

```powershell
php artisan serve --host=127.0.0.1 --port=8014
```

Do not use the web port as DB-target evidence. DB target checks must come from `.env`, Laravel config, MySQL connection output, and manual SQL preflight guards.

## Accelerated DB Deployment Posture

Owner direction on 2026-05-28: target the real To Quran app DB name `u504065335_to_quran` instead of spending more time on disposable local-only targets. The completed `toquranapp_local` baseline remains useful as the safe proof run.

Current local branch result: `u504065335_to_quran` has been created locally with the app schema baseline and intentional starter/reference data. The Quran YouTube/video list is preserved separately for a later Library migration.

Before server deployment, confirm the destination host/database backup, run only reviewed manual SQL, and coordinate public website changes because the public site previously used the same DB name/export source.

## Open Follow-Up

Create a To Quran deployment checklist equivalent to Week14's server push checklist, including real server DB backup/restore, starter/reference data verification, queue/mail requirements, public website handoff, and composer security hardening.
