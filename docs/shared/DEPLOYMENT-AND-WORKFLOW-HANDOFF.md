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

## Open Follow-Up

After the import strategy is approved, create a deployment checklist equivalent to Week14's server push checklist but To Quran-specific.
