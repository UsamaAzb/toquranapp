# Launch Access And Teacher-Class Management Plan

Status: done; implemented in commit `529f7bc`
Date: 2026-05-29
Sprint: TQ3.5 closeout / TQ4 launch smoke prerequisite

## Objective

Finish the launch access layer that sits between staff management and real tutoring workflow smoke tests:

- let superadmin/admin assign teachers to existing classes;
- keep teacher access scoped to assigned classes/students;
- keep customer support family ownership compatible with the later n8n/notification plan;
- verify launch roles can open the right workspaces without building a full permission-management product;
- complete a visible branding pass on launch paths.

This plan extends the existing staff-user plan at `docs/plans/active/2026-05-29-superadmin-staff-user-management.md`.

## Sprint / Roadmap Relationship

- TQ3: Family Workspace launch verification has role/access smoke coverage for the launch path.
- TQ3.5: Superadmin Staff User Management includes first superadmin, staff user management, support assignment, smoke data, launch role access, and branding fixes.
- TQ4: Core Tutoring Sessions And Tasks has a teacher-class assignment path and passed launch task smoke.
- TQ9: Deployment readiness and public website handoff are the next active gate.

## Current Evidence

- First real `super_admin` account has been bootstrapped in `u504065335_to_quran`.
- Staff user page exists at `/admin/staff`.
- Support ownership is stored on `parents.family_support_id`.
- Launch smoke users/families/classes exist under the scoped markers `@toquran-smoke.test`, `[SMOKE]`, and `SMOKE-TQ-*`.
- Current local users were reset to the shared local test password for manual testing.
- Family workspace access was corrected so `super_admin`, `admin`, and `customer_support` can open family pages without requiring a fully seeded permission matrix.
- Superadmin now has a global app authorization bypass through `Gate::before`.
- Focused family/staff/support/auth tests passed after the latest access fixes: 64 tests, 305 assertions.
- Visible Week14 branding was found on error/login/sidebar paths and has started being replaced with To Quran assets.
- Subject catalog reference data has been patched locally in `u504065335_to_quran`: subjects 1, 2, 3, 4, 15, and 16 now represent the 6 To Quran LMS class subjects, with 24 grade-level-subject mappings.
- Teacher assignment screen exists at `/admin/teacher-class-assignments` for `super_admin` and `admin`.
- Teacher assignment writes existing `teacher_subject_classes` rows through existing `class_subjects` and `grade_level_subjects`; no new schema was added.
- Student Account > Subject Access now also supports assigning a teacher per class subject, using searchable teacher dropdowns and the same `teacher_subject_classes` data path.
- Teacher class cards now treat both `current` and `active` teacher assignments as visible teacher access.
- Teacher class cards now use the active student's name as the primary card label and keep the class/cohort title as context, preserving the Week14 one-student-per-class operating convention.
- Teacher class visibility now requires both an active student-subject row and an active/legacy-null child lifecycle state, so archived/suspended/pending smoke children do not appear as live teacher classes.
- Real launch default teacher account exists at `drosamaqandil@gmail.com`; upcoming transferred students resolve initial teacher assignments through `TOQURAN_DEFAULT_TEACHER_EMAIL` and remain editable per Student Account subject row.
- Launch smoke data now follows the current Week14/To Quran operating assumption of one current/active student per class. Other smoke students remain in the DB for lifecycle/state testing, but their class/subject links are inactive.
- Focused teacher assignment tests passed after adding Student Account subject-row assignment: 8 tests, 38 assertions.
- Follow-up focused teacher and transfer tests passed after student-aware teacher card/lifecycle filtering work: 29 tests, 186 assertions.
- Broader launch-access regression passed after student-aware teacher card/lifecycle filtering work: 159 tests, 803 assertions.
- Local HTTP session smoke against `http://127.0.0.1:8014/admin/teacher-class-assignments` returned 200 and confirmed the title, assignment form, assigned-teachers table, and no visible Week14 text in the rendered page body.
- Follow-up local HTTP smoke confirmed the teacher session page shows `1 student`, includes Omar, and no longer shows Yusuf as active in the smoke Quran class.

## Week14 Reuse Source Files / Modules

Implementation starts with a short Week14/source inventory before writing code. The inventory should confirm the exact files reused/adapted and update this plan if the chosen file list changes.

Known source files/modules to inspect first:

- `D:\xampp\htdocs\week14-app-lms\app\Http\Controllers\Admin\ClassesController.php`
- `D:\xampp\htdocs\week14-app-lms\app\Http\Controllers\Front\Teacher\ClassController.php`
- `D:\xampp\htdocs\week14-app-lms\app\Http\Controllers\Front\Teacher\DailySessionsController.php`
- `D:\xampp\htdocs\week14-app-lms\app\Models\TeacherSubjectClass.php`
- `D:\xampp\htdocs\week14-app-lms\app\Models\ClassModel.php`
- `D:\xampp\htdocs\week14-app-lms\app\Models\ClassSubject.php`
- `D:\xampp\htdocs\week14-app-lms\app\Models\StudentsSubject.php`
- `D:\xampp\htdocs\week14-app-lms\resources\views\teacher\classes\subject_classes.blade.php`
- `D:\xampp\htdocs\week14-app-lms\resources\views\livewire\teacher\daily-sessions-board.blade.php`
- `D:\xampp\htdocs\week14-app-lms\routes\web.php`

Likely local tables/models to reuse:

- `teacher_subject_classes`
- `teacher_subjects`
- `classes`
- `subjects`
- `users`
- `roles`

Use the imported Week14 schema and code patterns instead of creating a parallel assignment system.

## To Quran Class-Subject Catalog

The app-side LMS class-subject catalog is:

- Quran Memorization
- Arabic Language
- Quranic Arabic
- Sanad Program
- My Deen Journey / `MDJ`
- Well Being

Rules:

- My Deen Journey and Well Being are fixed LMS surfaces that should be available to all students and/or attachable to one or more class subjects.
- My Deen Journey is the learner journey/service experience.
- Well Being is the behavior/accountability points subject surface.
- Parent-written behavior points affect Well Being only.
- Keep inherited Week14 school-subject classes/subjects available as inactive records where practical, so future MDJ expansion can support school subjects without re-importing them.
- Do not show inactive Week14 school subjects in launch assignment/dropdown surfaces unless an admin explicitly opts into future expansion work.

## To Quran-Specific Changes

### Teacher-Class Assignment

- Add a launch-ready superadmin/admin UI for assigning teachers to classes.
- Route target: `/admin/teacher-class-assignments`, name `admin.teacher-class-assignments.index`.
- Sidebar visibility: `super_admin` and `admin` only.
- Access rules:
  - `super_admin` and `admin` can assign/deactivate/reactivate teacher-class rows;
  - `customer_support`, `teacher`, `parent`, and `student` are denied from the assignment screen.
- Use the To Quran class-subject catalog above. Do not collapse Arabic Language into Quranic Arabic, and do not collapse Well Being into MDJ.
- Domain distinction:
  - My Deen Journey / `MDJ` is the To Quran service/subject for the learner journey, routines, rewards, accountability follow-up, and parent-facing service framing.
  - Well-Being is a separate behavior-points subject surface inherited from the LMS behavior workflow.
  - Parent-written behavior points must continue to resolve through `ParentBehaviorSubjectResolver` and affect Well-Being only; teacher-class assignment work must not redirect behavior writes into MDJ.
- Allow at minimum:
  - choose active teacher;
  - choose class;
  - choose subject when the existing schema requires it;
  - activate/deactivate an assignment safely.
- Assignment write decision for launch:
  - create or reactivate rows in `teacher_subject_classes`;
- active/current rows use `status = 'current'` or existing active-compatible status;
- deactivate by setting `status = 'inactive'` and `removed_at = now()`;
- do not delete `teacher_subject_classes` rows in the launch UI.
- Make the teacher view respect the assignment so a teacher only sees assigned classes/students.
- Upcoming transfer-created class subjects should be assigned to the configured launch default teacher from `TOQURAN_DEFAULT_TEACHER_EMAIL`, currently `drosamaqandil@gmail.com`.
- Admin/superadmin may later override that default from Student Account > Subject Access.

### Launch Role Rules

- `super_admin`: all app abilities through global authorization bypass; keep assignment tightly controlled and rely on action-level audit trails rather than logging every Gate check.
- `admin`: operational management, including teacher-class assignment and support-family assignment.
- `customer_support`: transferred families and family workspace access, but no admin-only student domain links or support reassignment.
- `teacher`: assigned classes/students only.
- Full permission-management UI is deferred unless a launch blocker appears.

### Support Ownership / n8n Boundary

- Keep `parents.family_support_id` app-owned for launch.
- n8n, WhatsApp, notifications, and survey automation may read `parents.family_support_id`.
- Automation must not directly overwrite `parents.family_support_id` during launch.
- If future automation wants to change support ownership, it should submit an app-side reviewed request or use an approved app-owned endpoint.

### Branding Pass

- Remove visible Week14 branding from launch paths:
  - login;
  - sidebar;
  - 403/404/500 pages;
  - staff users;
  - transferred families;
  - family workspace;
  - teacher launch views.
- Do not spend launch time cleaning old/deferred imported game/archive views unless they are linked from launch navigation.

## DB Impact And Backup / Baseline Evidence

Expected schema impact: none.

Reference-data impact: completed locally with guarded manual SQL.

- Patch: `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data.sql`
- Execution note: `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data-execution-note.sql`
- Backup evidence: `database/manual/backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql`
- Default teacher backup evidence: `database/manual/backups/2026-05-29-160244-u504065335_to_quran-before-default-teacher.sql`
- Default teacher execution note: `database/manual/patches/2026-05-29-default-teacher-bootstrap-execution-note.sql`

Runtime writes may use existing tables:

- `teacher_subject_classes`
- `teacher_subjects`
- `classes`
- `subjects`
- `users`
- `model_has_roles`
- `parents.family_support_id`

Current DB target:

- real app DB: `u504065335_to_quran`
- baseline/correction trail: `database/manual/README.md`
- smoke data note: `database/manual/patches/2026-05-29-launch-smoke-data-execution-note.sql`
- smoke cleanup plan: `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`

If implementation discovers a missing table/column, stop and write a guarded manual SQL patch or execution note before changing the DB.

If implementation discovers any additional missing class-subject catalog rows, write a guarded data patch rather than adding them by hand in phpMyAdmin or Tinker.

## Public Website Handoff

No public website code changes are part of this app plan.

Record for the public `toquran` repo sprint and keep mirrored in `docs/shared/DEPLOYMENT-AND-WORKFLOW-HANDOFF.md`:

- public intake should not claim automated class scheduling or finance management during launch;
- public form child-facing service values must match the updated app contract: Quran Memorization, Quranic Arabic, Arabic Language, Sanad Ijazah Program, and My Deen Journey, with one or more selectable per child;
- Contact Us phone should be changed to `+201091051913`;
- public intake handoff should not go live until staff/support/teacher launch access passes smoke testing.

## Test / Verification Scope

Focused automated tests:

```powershell
php artisan test tests/Feature/StaffUserManagementTest.php tests/Feature/TransferredChildrenPageTest.php tests/Feature/FamilyWorkspaceLifecycleTest.php
```

Latest broader verification:

```powershell
php artisan test tests/Feature/FamilyWorkspaceLifecycleTest.php tests/Feature/CredentialRevealTest.php tests/Feature/TransferredChildrenPageTest.php tests/Feature/StaffUserManagementTest.php tests/Feature/TeacherClassAssignmentTest.php tests/Feature/AuthenticationTest.php tests/Feature/BookingTransferLifecycleInitTest.php tests/Feature/BookingTransferGatingTest.php tests/Feature/BookingMilestoneTest.php tests/Unit/BookingServiceInterestTest.php
```

Teacher-class assignment tests cover:

- expected file: `tests/Feature/TeacherClassAssignmentTest.php`;
- admin or superadmin can assign an active teacher to a class;
- customer support cannot assign teachers;
- teacher cannot open the admin assignment page;
- inactive/non-teacher users cannot be assigned as teachers;
- teacher class visibility is limited to assigned classes;
- teacher class cards show the active student identity while retaining class/cohort context;
- archived/suspended/pending children are excluded from teacher-facing class visibility;
- deactivated/removed assignment hides the class from teacher scope.

Manual smoke:

- 2026-05-29 owner manual smoke passed for launch access and TQ4 task flow:
  - superadmin/staff access;
  - support assignment and family workspace access;
  - teacher assignment from Student Account > Subject Access;
  - teacher class visibility with one active/current student per class;
  - session/task Type dropdown using Assignment, Lesson, Project, and Quiz;
  - teacher task creation;
  - student visibility;
  - parent visibility;
  - To Quran branding in visible launch surfaces.
- Smoke cleanup plan remains scoped and unexecuted until deployment cleanup.

Post-CodeRabbit cleanup notes:

- Raw real-target backup dumps that contained users, sessions, credentials, account histories, or real-looking contact data were replaced with redacted evidence notes; full-fidelity restore dumps must stay in secured local/offline storage.
- Staff password display is now hidden by default with a per-row show/hide action, and create/edit password fields are masked with show/hide toggles.
- Task attachments are type-agnostic for launch: Assignment, Lesson, Project, and Quiz can all carry uploaded files, links, and YouTube links. File, Link, and YouTube are attachment kinds, not task types.
- Teacher assignment remains available to `admin` and `super_admin` intentionally because `admin` owns operational class/support assignment during launch.
- Well Being remains active-by-default intentionally because MDJ and Well Being are fixed launch LMS surfaces; parent-written behavior points still resolve to Well Being only.
- Customer-support ownership assignment now verifies the selected parent belongs to a transferred family before writing `parents.family_support_id`.
- The stale `logo.webp` asset was removed after all visible auth/layout references moved to `logo.png`.

Status: done. Launch access, teacher assignment, support assignment, task-type correction, branding cleanup, and backup redaction fixes are committed in `529f7bc`; TQ9 deployment/public website handoff is next.

## Non-Goals

- no finance workflow
- no automated consultation scheduling
- no full permission-management UI
- no customer-support ticketing/task workflow
- no n8n ownership write-back
- no notification implementation
- no survey/onboarding questions
- no public website code change
- no Arabic vocabulary game work
- no broad cleanup of old imported Week14 game/archive views
