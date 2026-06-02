# Intake To App Handoff

## Purpose

Define how public `toquran.org` consultation/intake data should hand off to private `app.toquran.org` workflows.

## Current Website Evidence

As of the public website commit `6dfb71f` on `main`, the public site:

- is a Laravel 10 app at `D:\xampp\htdocs\toquran`
- exposes `/book-trial`, posts to `/book-trial/store`, and redirects to `/book-trial/confirmation/{reference}`
- renders a multi-child public booking form
- allows one or more service interests per child
- uses `TQ-` booking references
- writes clean booking submissions into app-owned `bookings` and `booking_children`
- writes duplicate/repeat/blocked/contact-mismatch submissions into `booking_intake_review` and `booking_intake_review_children` through `booking_intake_submission_locks`
- writes Contact Us submissions to app-compatible `contacts` with `CNT-` references
- sends support/admin email to `support@toquran.org`
- exposes service interests:
  - `Quran Memorization`
  - `Quranic Arabic`
  - `Arabic Language`
  - `Sanad Ijazah Program`
  - `My Deen Journey`

Website implementation is now aligned with the approved Week14-style shared-table handoff. Local production-equivalent TQ9 smoke passed on 2026-06-02 using the real app DB name `u504065335_to_quran`: clean public booking, duplicate/review booking, generic Contact Us, app transfer, parent/student/teacher login, and teacher class visibility. The remaining deployment work is server-target verification, production backup/export, smoke-data cleanup, and temporary credential rotation.

## Target Handoff Contract

The public website remains a light consultation entry point. The app owns the schema, operational workflow, and review-first decisions.

For launch, the approved direction is **direct shared-DB write**, matching the Week14 website/LMS pattern:

- `toquranapp` owns the tables, service definitions, duplicate/repeat/contact-mismatch rules, and transfer workflow.
- `toquran` may write public intake/contact records only into app-approved tables and columns.
- The website should not invent a separate LMS schema or rely on an app-side delayed import from legacy JSON.
- Website booking submissions should immediately create either normal `bookings` + `booking_children` rows or `booking_intake_review` + `booking_intake_review_children` rows through the app-owned review-first logic.
- Website contact submissions should write to the app-compatible `contacts` table, not the legacy `contact_us`/`massage` table.

### Website Owns

- public service copy and pricing copy
- basic form UX
- initial parent/child contact submission
- multi-child public intake UX
- child-level service-interest selection, allowing one or more services per child
- public confirmation/review-received messaging

### App Owns

- parent/family identity resolution
- duplicate/repeat/contact-mismatch review rules
- child-level consultation state
- scheduling confirmation and real meeting details
- transfer into parent/student accounts
- Family Workspace and activation
- tasks, sessions, My Deen Journey, rewards, behavior/accountability, consequence agreements, Library
- initial teacher assignment after transfer, using the app-configured default teacher until staff edit the Student Account subject rows

## Launch Public Form Contract

The booking form should submit or transform into a children payload compatible with the app's review-first intake model:

- one parent/contact block per booking
- one or more child blocks
- each child has name, age, and `service_interests`; school/grade is optional for the public website launch
- `service_interests` is an array with one or more of the launch child-facing values:
  - `Quran Memorization`
  - `Quranic Arabic`
  - `Arabic Language`
  - `Sanad Ijazah Program`
  - `My Deen Journey`

`Paid Parental Consultation` remains an app-supported service value, but it is not part of the owner-confirmed child-facing multi-service selector for this launch pass unless the owner reopens the public form scope.

## Direct Shared-DB Write Contract

The final launch handoff is the Week14-style shared app DB implementation, now present in the public website at commit `6dfb71f`.

Website booking submissions should:

- validate public-only fields and anti-spam controls on `toquran.org`
- normalize public service values through the same app-owned service mapping
- call/adapt the Week14-style `BookingIntakeDetectionService`
- serialize clean/review decisions through `booking_intake_submission_locks`
- create normal app records in `bookings` and `booking_children` for clean submissions
- create `booking_intake_review` and `booking_intake_review_children` for duplicate/repeat/blocked/contact-mismatch submissions
- generate and preserve `TQ-` booking references
- keep scheduling review-first: no confirmed meeting link/date/time is created by the website
- send public receipt/support emails after the DB write succeeds

Website contact submissions should:

- generate and preserve `CNT-` contact references
- write to `contacts`
- omit child-specific fields when the message is a generic Contact Us request
- rely on the app-owned schema contract where `contacts.child_age` is nullable; this was applied locally through `database/manual/patches/2026-06-02-make-contacts-child-age-nullable.sql`, and production deploy/import must preserve the same shape before public Contact Us writes to the shared app DB
- avoid writing new production records to legacy `contact_us`
- keep `support@toquran.org` as the launch support routing address unless owner changes the mailbox

## Historical Website Notes JSON Evidence

The public website W1 implementation at commit `c7addea` stored the review-first payload in the legacy website `bookings.notes` column as JSON.

This JSON remains useful only as compatibility evidence for old/local W1 rows. It is not the production handoff mechanism now that the website writes directly to the app-owned tables.

Contract name: `toquran_public_review_first_v1`

Shape:

```json
{
  "handoff_contract": "toquran_public_review_first_v1",
  "parent": {
    "name": "Parent Name",
    "email": "parent@example.com",
    "phone": "+201091051913",
    "country": "United Kingdom"
  },
  "children": [
    {
      "name": "Learner Name",
      "age": 12,
      "service_interests": [
        "Quran Memorization",
        "Quranic Arabic"
      ]
    }
  ],
  "preferences": {
    "preferred_date": "2026-06-15",
    "preferred_time": "evening",
    "main_concerns": "Optional parent message"
  }
}
```

Fallback parser expectations, only if old W1 JSON rows must be inspected:

- Treat `bookings.notes` as a temporary/source-evidence fallback for multi-child public requests when `handoff_contract` is `toquran_public_review_first_v1`.
- Keep the legacy `bookings.child_name`, `bookings.child_age`, and `bookings.service_interest` fields as summary/display fallback only.
- Normalize `children.*.service_interests` through the app-side alias/canonicalization rules below.
- Phone should be stored in an international format when possible, such as `+201091051913`. The website should normalize obvious spacing/punctuation before write; app-side review code should still tolerate spaces, hyphens, parentheses, and local-format input because old website rows and manual entries may not be normalized.
- `preferences.preferred_time` is a public scheduling preference, not a confirmed session time. Launch values should be simple preference buckets such as `morning`, `afternoon`, or `evening`; app-side review should also tolerate free-form text from legacy rows.
- `preferences.main_concerns` is optional and may be omitted or stored as an empty string.
- Do not require school/grade for this website contract; the owner removed that public field for launch.
- Confirm scheduling, meeting details, identity resolution, transfer, and account creation inside the app review workflow, not from website-side assumptions.

## Service Interest Mapping

| Website value | App-owned meaning | First-import handling |
| --- | --- | --- |
| `My Deen Journey (Parenting System)` | My Deen Journey service interest, potentially parent + child accountability workflow | Adapt Week14 Journey/rewards/behavior/approval models |
| `Paid Parental Consultation` | parent/support consultation, not necessarily child LMS enrollment | New To Quran-specific service workflow may be needed |
| `Quran Memorization` | Quran tutoring subject/service | Adapt subject/service catalogs |
| `Quranic Arabic` | Arabic/Quranic Arabic tutoring subject/service | Adapt subject/service catalogs |
| `Arabic Language` | Broader Arabic language tutoring subject/service, distinct from Quranic Arabic | App service row added 2026-05-29; public website may send it as a distinct child service |
| `Sanad Ijazah Program` | advanced Quran recitation/certification path | New To Quran-specific service metadata likely needed |

## App-Side Alias Handling

The app now accepts both the current public To Quran values and the inherited Week14 service labels during intake normalization:

| Incoming value family | Canonical app value |
| --- | --- |
| `IB Private Classes`, `IB Private Tutoring`, `Quran`, `Hifz`, `Memorization` | `Quran Memorization` |
| `Help Me Read`, `SAT / ACT Preparation`, `Quranic Arabic` | `Quranic Arabic` |
| `Arabic`, `Arabic Language` | `Arabic Language` |
| `Help Me Study`, `My Deen Journey (Parenting System)` | `My Deen Journey` |
| `Paid Consultation`, `Parental Consultation`, `Paid Parental Consultation` | `Paid Parental Consultation` |
| `Sanad`, `Ijazah`, `Sanad Ijazah`, `Sanad Ijazah Program` | `Sanad Ijazah` |

During transfer, app provisioning uses each child's selected public `service_interests` to activate optional launch subjects in the student's class-subject plan. Quran Memorization, Arabic Language, and Sanad Program are activated only when selected; Quranic Arabic, My Deen Journey, and Well Being remain active-by-default for launch unless the owner changes that product rule.

## Launch Scope

For first deployment, To Quran follows the current Week14 operating model: intake, admin/customer-support review, family/student account transfer, and LMS access are app-supported; consultation scheduling, finance, and detailed class management remain manual until later sprints.

Upcoming transferred students receive initial class-subject teacher assignments from the app-side default teacher configured by `TOQURAN_DEFAULT_TEACHER_EMAIL` (`drosamaqandil@gmail.com` for launch). Admin/superadmin can edit the teacher later from Student Account > Subject Access.

## Immediate Gaps

- Public website shared-DB handoff is implemented in `toquran` commit `6dfb71f`; do not plan another delayed JSON import bridge unless deployment forces separate databases.
- App-owned `contacts.child_age` nullable patch was executed locally against `u504065335_to_quran` on 2026-06-02 with target verification and structure-backup evidence. Production deploy/import must preserve the nullable column shape before public Contact Us goes live.
- App-side public validation/website adapter must not require school/grade for launch public submissions.
- Deployment must prove that `toquran` and `toquranapp` are pointed at the intended shared app DB target before public form launch.

## First Import Rule

Do not build a delayed app-side import bridge unless deployment forces separate databases. The preferred launch path is to adapt the public website to the app-owned shared schema directly, following the Week14 website/LMS pattern.
