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
- mobile/tablet form controls are checked before launch: long dropdowns, multi-service selectors, date/time/datetime fields, and Flatpickr-style pickers must be contained within the viewport, preserve real field names/values, dispatch real DOM `input`/`change` events, mirror programmatic value changes, avoid duplicate hidden-field tab stops/accessibility entries, clean up observers after dynamic removal, and keep validation feedback attached to the visible control.
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

## Launch Order

1. Keep local/manual testing easy while launch work is active: smoke users, smoke families, and the shared local test password may remain in the local real-name app DB until the final deployment cleanup gate.
2. App-side TQ3 launch verification is complete for the current launch path: intake review, transfer, Family Workspace access, lifecycle states, parent login, and student login have automated/manual smoke coverage.
3. App-side TQ3.5 staff/user access is complete for launch: first superadmin was created, Staff Users manages admins/customer support/teachers, `TOQURAN_DEFAULT_TEACHER_EMAIL` is documented, and transferred-family support ownership is app-owned.
4. App-side TQ4 launch smoke is complete: teacher login, student login, parent visibility, teacher class assignment, session task creation, task attachments, and core task visibility passed local/manual checks.
5. Start the public `toquran` repo handoff under TQ9: booking form values, multi-child/per-child multi-service intake payload, reference prefix, Contact Us behavior, contact phone `+201091051913`, sign-in link, and app handoff path. The approved handoff path is the Week14-style shared app DB pattern: website writes directly to app-owned booking/review/contact tables, not to a delayed JSON import queue.
6. Run end-to-end public form to app intake/review/transfer/login smoke tests.
7. Complete deployment hardening: backup/export, queue/mail/storage/build assets, and Composer security advisories.
8. Final deployment-only cleanup gate: remove launch smoke data marked by `@toquran-smoke.test`, `[SMOKE]`, and `SMOKE-TQ-*`; rotate any real staff/teacher credentials that were temporarily set to a shared local test password; verify no shared test passwords remain before any production launch or production DB export.

## Local Testing Convenience Vs Deployment Safety

- During active local testing, it is acceptable to keep smoke accounts/families/classes and the shared local test password because they make fast role-switching and end-to-end checks possible.
- Do not treat that convenience state as deployable. The cleanup/rotation gate is mandatory only when moving from local smoke testing to production launch/export.
- The real launch default teacher account `drosamaqandil@gmail.com` may keep the temporary shared password only during local testing; it must be rotated before go-live because it survives smoke-data cleanup.
- Smoke cleanup should use the documented scoped cleanup plan rather than ad hoc deletes: `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`.
- After cleanup, verify zero `@toquran-smoke.test` users and no `[SMOKE]` / `SMOKE-TQ-*` rows remain in launch-facing tables.

## Launch Authorization And Audit Posture

- `super_admin` intentionally has a global Laravel `Gate::before` authorization bypass for launch recovery and owner-level administration.
- Do not add audit writes inside `Gate::before`: it runs during authorization checks, not durable business actions, and would create noisy records without proving what changed.
- Sensitive actions should keep action-level audit trails instead: family lifecycle changes, credential reveal/reset, intake transfer/contact resolution, staff/user management, support assignment, teacher assignment, and destructive cleanup.
- Keep `super_admin` assignment tightly controlled: only the owner/root launch account should hold it unless a second break-glass account is explicitly approved.
- Before production launch, rotate any temporary `super_admin` or real teacher/admin passwords and confirm no smoke/test account has `super_admin`.

## Launch Access Handoff

- Teacher-class assignment is an app-owned launch prerequisite before TQ4 smoke tests. The app route is `/admin/teacher-class-assignments` for `super_admin` and `admin`.
- Public website booking/contact handoff is a shared-DB write, matching Week14 website/LMS. The website may write only to app-approved tables and columns. Booking targets are `bookings`, `booking_children`, `booking_intake_review`, `booking_intake_review_children`, and `booking_intake_submission_locks`. Contact target is `contacts`, not legacy `contact_us`.
- Before public Contact Us writes to `contacts`, execute the reviewed app-owned `contacts.child_age` nullable patch with confirmed target and backup/export evidence. Generic Contact Us rows should not need or fake child age.
- Public website booking should preserve `TQ-` booking references and remain review-first: clean submissions create pending app bookings/children; duplicate/repeat/blocked/contact-mismatch submissions create intake review records; confirmed scheduling and real meeting details remain app/manual operations.
- Teacher assignment should reuse `teacher_subject_classes` and deactivate assignments with `status = 'inactive'` plus `removed_at`, not hard-delete launch rows.
- Upcoming transfer-created teacher assignments resolve through `TOQURAN_DEFAULT_TEACHER_EMAIL`; launch local default is `drosamaqandil@gmail.com` and must be set in production before first transfer smoke. If the configured teacher cannot be resolved, the app should fail loudly instead of silently assigning a legacy Week14 teacher id.
- App-side class-subject catalog: Quran Memorization, Arabic Language, Quranic Arabic, Sanad Program, My Deen Journey / `MDJ`, and Well Being.
- Public child-facing intake service selector for launch: Quran Memorization, Quranic Arabic, Arabic Language, Sanad Ijazah Program, and My Deen Journey. Each child may choose one or more services.
- Because the public website selector will be multi-child and per-child multi-service, the website repo must apply the same mobile/tablet dropdown/date rules as the app: keep the real submitted fields as the source of truth, enhance dynamically added child rows, keep menus inside their parent width, and ensure validation appears below the visible custom control.
- Guarded learning-catalog reference-data patch has been executed locally: `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data.sql`.
- My Deen Journey / `MDJ` and Well Being must stay distinct: MDJ is the To Quran learner journey/service framing, while parent-written behavior points continue to resolve through the Well Being behavior subject only.
- Keep inherited Week14 school-subject classes/subjects inactive where practical for future MDJ expansion; do not expose them in launch public copy or launch assignment dropdowns by default.
- Current launch smoke and teacher-session checks assume one current/active student per class, matching the inherited Week14 operating model. Multi-student classes are allowed as a future deliberate adaptation, but they need a specific UX review for per-student actions such as Points Lab, rewards, behavior points, and task review.
- Public website copy should avoid promising automated class scheduling, finance management, or a full class-management product during launch.

## Open Follow-Up

Create a To Quran deployment checklist equivalent to Week14's server push checklist, including real server DB backup/restore, starter/reference data verification, queue/mail requirements, public website handoff, and Composer security hardening. Track that work under `TQ9. Deployment Readiness And Public Website Handoff`.

Keep n8n/WhatsApp/notification ownership boundaries explicit during deployment: automation can read the app's family support assignment for routing, but should not write directly to `parents.family_support_id` without a reviewed app-side endpoint or workflow.

Local smoke data was created for launch testing on 2026-05-29. Keep it while active manual testing is still happening; remove it only at the final deployment cleanup gate using `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`.
