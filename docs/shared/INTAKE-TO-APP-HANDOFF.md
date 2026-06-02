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
- is still single-child in the visible form
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

Before the public website handoff is implemented, the booking form should submit a children payload compatible with the app's review-first intake model:

- one parent/contact block per booking
- one or more child blocks
- each child has name, age, school/grade where available, and `service_interests`
- `service_interests` is an array with one or more of the launch child-facing values:
  - `Quran Memorization`
  - `Quranic Arabic`
  - `Arabic Language`
  - `Sanad Ijazah Program`
  - `My Deen Journey`

`Paid Parental Consultation` remains an app-supported service value, but it is not part of the owner-confirmed child-facing multi-service selector for this launch pass unless the owner reopens the public form scope.

## Website Notes JSON Contract

The public website W1 handoff stores the review-first payload in the legacy website `bookings.notes` column as JSON.

Contract name: `toquran_public_review_first_v1`

Shape:

```json
{
  "handoff_contract": "toquran_public_review_first_v1",
  "parent": {
    "name": "Parent Name",
    "email": "parent@example.com",
    "phone": "+2010 910 51 913",
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

Parser expectations:

- Treat `bookings.notes` as the source of truth for multi-child public requests when `handoff_contract` is `toquran_public_review_first_v1`.
- Keep the legacy `bookings.child_name`, `bookings.child_age`, and `bookings.service_interest` fields as summary/display fallback only.
- Normalize `children.*.service_interests` through the app-side alias/canonicalization rules below.
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

## Launch Scope

For first deployment, To Quran follows the current Week14 operating model: intake, admin/customer-support review, family/student account transfer, and LMS access are app-supported; consultation scheduling, finance, and detailed class management remain manual until later sprints.

Upcoming transferred students receive initial class-subject teacher assignments from the app-side default teacher configured by `TOQURAN_DEFAULT_TEACHER_EMAIL` (`drosamaqandil@gmail.com` for launch). Admin/superadmin can edit the teacher later from Student Account > Subject Access.

## Immediate Gaps

- Current public reference prefix `W14-` should become To Quran-specific later.
- Current public booking table is legacy single-child style and does not include Week14's modern `booking_children` and review-first workflow.
- Current public booking form is single-service; it must become per-child multi-service before app connection.
- Public code hardcodes a meeting link; app should own confirmed meeting details.
- Public `.env` points to a DB schema not present locally; current schema evidence comes from the SQL export.

## First Import Rule

Do not change the public website or import live intake logic yet. The first approved app import should provide the app-side schema/workflow target, then a later public-site handoff plan should update the website form and write path.
