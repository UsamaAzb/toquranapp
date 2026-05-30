# Superadmin Staff User Management

Status: done; implemented in launch access commit `529f7bc`
Date: 2026-05-29
Sprint: TQ3.5

## Objective

Provide the first launch-ready app-side staff account path for To Quran:

- bootstrap the first active `super_admin` account with explicit DB target confirmation;
- let superadmin create and manage admins, customer support users, and teachers;
- let admins/superadmins assign a transferred family to a customer support owner;
- keep scope limited to internal staff accounts and Spatie roles.

## Current Evidence

- TQ2 service/intake adaptation is merged to `main` through `0b99741`.
- The real app DB target is `u504065335_to_quran`.
- Starter/reference roles already include `super_admin`, `admin`, `customer_support`, and `teacher`.
- `users.status` exists in the real baseline and Fortify already blocks inactive/suspended login through `FortifyServiceProvider::userStatusAllowsLogin()`.

## Week14 Reuse Source

Relevant Week14 reference:

- `D:\xampp\htdocs\week14-app-lms\docs\plans\active\2026-05-27-customer-support-phase1-native-task-workflow.md`

Useful decision from that plan: customer-support workflows require a safe staff/user management prerequisite. The To Quran launch implementation follows that sequence instead of bundling support ticketing into this sprint.

Imported useful pieces already present in To Quran:

- Spatie roles/permissions
- `users.status`
- Fortify login gating
- Vuexy/Livewire admin layout patterns
- existing `UserStatusToggle` proof that staff status is a known operational concept

## Implemented Shape

- `app/Console/Commands/BootstrapSuperadmin.php`
  - command: `php artisan toquran:bootstrap-superadmin`
  - requires `--confirm-db=<current-db-name>`
  - creates or repairs an active `super_admin` user
  - assigns Spatie `super_admin`
  - stores the password through `CredentialService` so `recoverable_password_encrypted` stays consistent

- `app/Livewire/Admin/StaffUsers.php`
  - superadmin-only staff list/create/edit page
  - roles allowed at launch: `super_admin`, `admin`, `customer_support`, `teacher`
  - create staff user with password
  - edit name/email/phone/role/status
  - optional password reset during edit
  - activate/deactivate staff through `users.status`
  - prevents self-demotion/self-deactivation and prevents removing the last active superadmin

- route/menu:
  - route: `/admin/staff`
  - route name: `admin.staff.index`
  - visible in the admin sidebar for `super_admin`

- transferred-family support assignment:
  - uses the existing `parents.family_support_id` relationship to `users`
  - visible on the Transferred Families page as `Support: <name>`
  - assign/clear action is limited to `admin` and `super_admin`
  - assignment choices are active `customer_support` users only
  - customer support users can view transferred families but cannot reassign ownership

## DB Impact

No schema change is required.

Runtime writes are limited to existing tables:

- `users`
- `roles`
- `model_has_roles`
- `parents.family_support_id`

The bootstrap command is intentionally guarded by `--confirm-db` and should be run only after verifying `.env`, Laravel config, and the target DB name.

Example:

```powershell
php artisan toquran:bootstrap-superadmin --confirm-db=u504065335_to_quran --email="owner@example.com" --name="Owner Name"
```

If `--password` is omitted, the command prints a generated one-time password. Store it securely and change it after first login.

Launch smoke data was also created in `u504065335_to_quran` for ongoing manual verification:

- command: `php artisan toquran:bootstrap-smoke-data --confirm-db=u504065335_to_quran`
- marker: `@toquran-smoke.test`, `[SMOKE]`, and `SMOKE-TQ-0001`
- includes: admin, two customer support users, teacher, four transferred families, six students, one class, and one teacher-subject-class assignment
- family states: active mixed family, pending activation family, suspended family, and archived family; children include active, pending activation, suspended, and archived account states
- evidence: `database/manual/patches/2026-05-29-launch-smoke-data-execution-note.sql`
- cleanup plan: `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`

## Public Website Impact

The public `toquran` website should not connect live intake until a superadmin can manage internal staff users and at least one admin/support/teacher operating account exists.

## n8n / Automation Boundary

Support assignment is app-owned for launch. Future n8n, WhatsApp, notification, or survey automation may read `parents.family_support_id` to route reminders/messages, or may submit a reviewed app-side request when ownership should change. Automation must not directly overwrite `parents.family_support_id` or silently reassign support ownership during the launch phase.

## Verification

Focused tests:

```powershell
php artisan test tests/Feature/TransferredChildrenPageTest.php tests/Feature/StaffUserManagementTest.php tests/Feature/AuthenticationTest.php
```

Result:

- 26 passed
- 115 assertions

Real DB bootstrap:

- target: `u504065335_to_quran`
- execution note: `database/manual/patches/2026-05-29-first-superadmin-bootstrap-execution-note.sql`
- result: first active `super_admin` user created/repaired for Osama Qandil
- password is intentionally not recorded in docs
- smoke data: created and verified with scoped cleanup plan; remove before deployment
- local test credentials: current local users were reset to a shared 8-character test password for manual launch testing; execution note is `database/manual/patches/2026-05-29-test-password-reset-execution-note.sql`

Transferred-family support assignment is covered by admin assignment, customer-support denial, and non-support-user rejection tests.

Closeout:

- staff-user management, first superadmin bootstrap evidence, support assignment, launch smoke data, and teacher assignment prerequisites were completed in the launch access branch;
- merged closeout source commit: `529f7bc`;
- final deployment still requires smoke-data cleanup and temporary credential rotation.

## Non-Goals

- no finance/HR/payroll
- no teacher scheduling or class assignment management
- no customer-support ticket workflow
- no n8n ownership write-back
- no notification system
- no survey/onboarding questions
- no public website code change in this app sprint
