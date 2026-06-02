# TQ9 Shared DB Smoke And Hardening

Date: 2026-06-02

Branch: `codex/tq9-deployment-smoke-hardening`

## Scope

Production-equivalent local smoke using the real app DB name `u504065335_to_quran`, the public website repo at `D:\xampp\htdocs\toquran`, and the app repo at `D:\xampp\htdocs\toquranapp`.

No Playwright/browser automation was used. Verification used HTTP form requests, SQL checks, Laravel services, and PHPUnit.

## Environment

- App server: `http://127.0.0.1:8014`
- Website server: `http://127.0.0.1:8016`
- DB target: `u504065335_to_quran`
- Website implementation: `toquran` commit `6dfb71f`
- App branch: `codex/tq9-deployment-smoke-hardening`

During smoke, port `8014` was found serving a Week14 process. It was stopped and restarted from `D:\xampp\htdocs\toquranapp`, matching the documented repo port rule.

## DB Patches Executed Locally

- `database/manual/patches/2026-06-02-make-contacts-child-age-nullable.sql`
  - Result: `contacts.child_age` is nullable for generic Contact Us rows.
  - Backup evidence: `database/manual/backups/2026-06-02-203533-u504065335_to_quran-before-contacts-child-age-nullable-structure.sql`

- `database/manual/patches/2026-06-02-correct-tq9-smoke-selected-service-subjects.sql`
  - Result: the first transferred TQ9 smoke child now has Arabic Language active because that child selected Arabic Language.
  - Backup evidence: `database/manual/backups/2026-06-02-205300-u504065335_to_quran-before-tq9-smoke-selected-subject-correction.sql`

## Website To App Smoke

- Clean booking submission created pending app booking `TQ-ZDVPE6FDMG`.
- The booking had two children and per-child `service_interests` arrays.
- Website generated no meeting link and kept review-first/manual scheduling behavior.
- Existing-family new-child submission created another normal pending booking, which matches the app identity model.
- Duplicate-child submission created `booking_intake_review` with `detection_reason = duplicate_child` instead of a normal booking.
- Contact Us submission created `contacts.reference = CNT-AZ44RW5VUJ` with `contacts.child_age = NULL` and no legacy `contact_us` row.

## Transfer And Login Smoke

- Prepared one clean child as fit-ready, transferred it through `BookingTransferService`, and activated the family/child through `FamilyLifecycleService`.
- Verified transferred parent, student, user links, and default teacher assignments.
- Fixed app transfer provisioning so selected optional public services activate the matching app subject:
  - `Arabic Language` -> subject id `3`
  - `Sanad Ijazah` / `Sanad Ijazah Program` -> subject id `4`
- Verified HTTP login redirects:
  - parent -> `/students`
  - student -> `/student/workplace`
  - teacher -> `/teacher/classes`
- Verified the teacher classes page contains the transferred smoke student and Arabic Language subject.

## Hardening Checks

- `composer update ... --with-all-dependencies` brought Laravel 12, Livewire 3, Symfony, CommonMark, PsySH, PHPUnit, and related packages to patched versions.
- `composer audit --no-dev`: no advisories.
- `composer audit`: no advisories.
- Focused tests passed: `40 tests / 259 assertions`.
- `php artisan optimize:clear` completed.
- `php artisan storage:link` created `public/storage` -> `storage/app/public`.
- Build manifests exist at `public/build/manifest.json` and `build/manifest.json`.

## Not Completed In This Pass

- JavaScript dependency audit: the repo has `yarn.lock`, but Yarn is not installed locally; `npm audit` cannot run without `package-lock.json`. Do not create a second lockfile casually during launch. Install/use Yarn or intentionally migrate the lockfile before treating JS audit as complete.
- Final production cleanup: smoke data and temporary shared local passwords remain intentionally for manual testing. Before production launch/export, run the scoped smoke cleanup plan and rotate the real default teacher/staff passwords.
- Production/server import: this smoke used the local real-name DB. Before deployment, confirm host backup/export and import the verified local DB shape/data according to the deployment plan.
