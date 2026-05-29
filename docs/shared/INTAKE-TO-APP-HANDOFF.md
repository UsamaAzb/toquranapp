# Intake To App Handoff

## Purpose

Define how public `toquran.org` consultation/intake data should hand off to private `app.toquran.org` workflows.

## Current Website Evidence

The public site currently:

- is a Laravel 10 app at `D:\xampp\htdocs\toquran`
- exposes `/book-trial`, posts to `/book-trial/store`, and redirects to `/book-trial/confirmation/{reference}`
- writes `Booking` rows with parent contact, country, child name/age, `service_interest`, preferred date/time, main concerns, status, terms, and meeting link
- uses booking references with `W14-` prefix in `BookingController`
- stores the hidden `type` value as `Quran`
- offers service interests:
  - `My Deen Journey (Parenting System)`
  - `Paid Parental Consultation`
  - `Quran Memorization`
  - `Quranic Arabic`
  - `Sanad Ijazah Program`

## Target Handoff Contract

The public website should remain a light consultation entry point. The app should own the operational workflow after intake.

### Website Owns

- public service copy and pricing copy
- basic form UX
- initial parent/child contact submission
- public confirmation/review-received messaging

### App Owns

- parent/family identity resolution
- duplicate/repeat/contact-mismatch review rules
- child-level consultation state
- scheduling confirmation and real meeting details
- transfer into parent/student accounts
- Family Workspace and activation
- tasks, sessions, My Deen Journey, rewards, behavior/accountability, consequence agreements, Library

## Service Interest Mapping

| Website value | App-owned meaning | First-import handling |
| --- | --- | --- |
| `My Deen Journey (Parenting System)` | My Deen Journey service interest, potentially parent + child accountability workflow | Adapt Week14 Journey/rewards/behavior/approval models |
| `Paid Parental Consultation` | parent/support consultation, not necessarily child LMS enrollment | New To Quran-specific service workflow may be needed |
| `Quran Memorization` | Quran tutoring subject/service | Adapt subject/service catalogs |
| `Quranic Arabic` | Arabic/Quranic Arabic tutoring subject/service | Adapt subject/service catalogs |
| `Sanad Ijazah Program` | advanced Quran recitation/certification path | New To Quran-specific service metadata likely needed |

## App-Side Alias Handling

The app now accepts both the current public To Quran values and the inherited Week14 service labels during intake normalization:

| Incoming value family | Canonical app value |
| --- | --- |
| `IB Private Classes`, `IB Private Tutoring`, `Quran`, `Hifz`, `Memorization` | `Quran Memorization` |
| `Help Me Read`, `SAT / ACT Preparation`, `Arabic`, `Arabic Language` | `Quranic Arabic` |
| `Help Me Study`, `My Deen Journey (Parenting System)` | `My Deen Journey` |
| `Paid Consultation`, `Parental Consultation`, `Paid Parental Consultation` | `Paid Parental Consultation` |
| `Sanad`, `Ijazah`, `Sanad Ijazah`, `Sanad Ijazah Program` | `Sanad Ijazah` |

## Launch Scope

For first deployment, To Quran follows the current Week14 operating model: intake, admin/customer-support review, family/student account transfer, and LMS access are app-supported; consultation scheduling, finance, detailed class management, and teacher assignment decisions remain manual until later sprints.

## Immediate Gaps

- Current public reference prefix `W14-` should become To Quran-specific later.
- Current public booking table is legacy single-child style and does not include Week14's modern `booking_children` and review-first workflow.
- Public code hardcodes a meeting link; app should own confirmed meeting details.
- Public `.env` points to a DB schema not present locally; current schema evidence comes from the SQL export.

## First Import Rule

Do not change the public website or import live intake logic yet. The first approved app import should provide the app-side schema/workflow target, then a later public-site handoff plan should update the website form and write path.
