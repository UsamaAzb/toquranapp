# To Quran / Week14 Reuse Audit

Date: 2026-05-27

## Scope

Audit:

- `D:\xampp\htdocs\toquranapp`
- `D:\xampp\htdocs\toquran`
- current To Quran DB/schema evidence
- `D:\xampp\htdocs\week14-app-lms`

This audit does not implement the import.

## Executive Findings

1. `toquranapp` started as an empty, non-git app directory. There was no app code, docs, `.env`, or existing Laravel structure to preserve inside this repo.
2. The public `toquran` repo is a Laravel 10 public website with booking/contact pages, To Quran service copy, and an SQL export named `u504065335_to_quran.sql`.
3. The configured public DB name `u504065335_to_quran` is not present in the local XAMPP MySQL data directory. Current To Quran schema evidence comes from the SQL export, not a live local schema.
4. Week14 is a mature Laravel 12 LMS with Jetstream, Livewire 3, Spatie Permission, Vuexy, manual DB workflow, docs, specs, tests, family lifecycle, booking/intake, sessions/tasks, rewards, Library, automation, and vocabulary work.
5. The To Quran export is much older/smaller than Week14's current LMS schema: 44 tables versus 352 tables. A direct DB overwrite or cleanup would be unsafe.

## Backup And Baseline Evidence

Created during audit:

- `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
- `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`

No DB mutation was performed.

## Public Website Relationship

Public repo findings:

- Laravel 10, PHP 8.1 target.
- Public routes include `/`, `/about`, `/pricing`, `/my-deen-journey`, `/book-trial`, `/contact`, and disabled public auth registration.
- Header links to `https://app.toquran.org/login`.
- Booking form captures parent name/email/phone/country, child name/age, service interest, preferred date/time, main concerns, and terms.
- Booking controller still generates references with `W14-`.
- Booking controller writes `type => Quran` and hardcodes a Zoom meeting link.
- Public website has no real `docs/shared/` decision system yet.

Public service values currently visible:

- My Deen Journey (Parenting System)
- Paid Parental Consultation
- Quran Memorization
- Quranic Arabic
- Sanad Ijazah Program

## To Quran Export Schema Findings

The SQL export contains 44 tables.

Data counts from export inserts include:

- `bookings`: 3 rows
- `contact_us`: 19 rows
- `students`: 18 rows
- `users`: 18 rows
- `employees`: 31 rows
- `employees_course`: 67 rows
- `student_quizzes`: 191 rows
- `employee_quizzes_old`: 2251 rows
- Quran/content tables such as `quran_courses`, `quran_course_translations`, `surahs`, `surahs_old`, `surh_videos`, levels, units, lessons

Important shape differences:

- `users` has old fields such as `pivot_id`, `model_name`, and `decr_password`.
- `students` has old fields like `parent_first_name`, `stu_first_name`, `school_name`, `course_id`, `quran_course_id`, `class_id`.
- `bookings` still has booking-level `type`, `student_id`, `transfer`, and single-child fields.
- No `parents` table in export.
- No modern `booking_children`, intake review, family lifecycle, account history, rewards ledger, Library resources, automation, or Spatie model role tables.

## Week14 Source Findings

Week14 has:

- Laravel 12
- Jetstream
- Livewire 3
- Spatie Permission
- Vuexy Bootstrap admin foundation
- manual DB baseline/patch workflow
- 152 model files
- 46+ service files, including Library and Vocabulary sub-services
- 49+ Livewire components across Teacher/Admin/Student/Parent
- broad feature/unit test coverage for booking, lifecycle, tasks, rewards, Library, automation, and vocabulary

Week14's workflow system includes:

- `AGENTS.md`
- `docs/WORKFLOW.md`
- `docs/DB-SAFETY-POLICY.md`
- `database/manual/README.md`
- `docs/SPRINTS.md` in Week14; To Quran uses `docs/TOQURAN-SPRINTS.md`
- `docs/WEEK14-LOGIC.md`
- `docs/MODELS.md`
- `docs/ROUTES-AND-AUTH.md`
- active/archive plan structure
- shared website handoff docs in `week14-website/docs/shared/`

## Week14 Modules Most Relevant To Reuse

High-value source modules:

- Laravel 12 app foundation, auth, Jetstream, Spatie Permission.
- Admin booking/intake review and family transfer foundation.
- Family Workspace and account lifecycle.
- Parent/student/teacher surfaces.
- Sessions, tasks, protected attachments, task approval workflow.
- Rewards, points ledger, behavior/discipline points, consequence agreements.
- Journey/task learner experience.
- Versioned Routines, Differentiated Tasks, Series Tasks.
- Library resource foundation and protected task attachments.
- Manual SQL and DB governance workflow.
- Tests and support traits for business-critical flows.

## DB Comparison Highlights

To Quran export vs Week14 live schema:

- To Quran table count: 44
- Week14 table count: 352
- Common table count: 23
- To Quran-only table count: 21
- Week14-only table count: 329

Missing from To Quran export but present in Week14:

- `parents`
- `booking_children`
- `booking_intake_review`
- `booking_intake_review_children`
- `booking_intake_submission_locks`
- `booking_parent_blocks`
- `booking_parent_identity_resolutions`
- `account_histories`
- `email_delivery_claims`
- `model_has_roles`, `role_has_permissions`, `model_has_permissions`
- `academic_years`, `classes`, `class_subjects`, `teacher_subject_classes`, `students_subjects`
- `class_sessions`, `session_tasks`, `session_task_student`, `attachment_files`
- `main_daily_session_*`
- `differentiated_*`
- `series_*`
- `reward_points_ledger`, `reward_totals`, `reward_discipline_points`
- `punishment_agreements`
- `library_sections`, `library_resources`
- `vocabulary_sets`, `vocabulary_source_access`, `vocabulary_game_assignments`

Likely obsolete or cleanup-risk To Quran-only tables:

- `employees`, `employees_old`, `employees_course`
- `employee_quizzes_old`
- old Laratrust-style `role_user`, `permission_user`, `permission_role`
- `courses_old`, `course_translations_old`, `lessons_old`
- old `students` shape

Preserve-review tables:

- `bookings`
- `contact_us`
- `users`
- `students`
- Quran content tables
- public website content/service values

## Import Strategy Implication

The To Quran app should not be rebuilt manually from scratch, and it should not use the old 44-table export as the target LMS schema.

Recommended direction:

1. Approve the selective reuse plan.
2. Import/adapt Week14 app foundation into `toquranapp`.
3. Prepare a To Quran app schema baseline based on Week14 current schema plus To Quran-specific service terminology.
4. Preserve/map To Quran export data intentionally.
5. Update public website handoff only after app-side schema and intake target are approved.

## Audit Limitations

- Live local `u504065335_to_quran` schema was not available, so the audit used the SQL export.
- No app runtime exists yet in `toquranapp`.
- No import implementation was performed.
