# Terminology And Services

## Product Name

- Public brand: To Quran
- Public website: `toquran.org`
- Private app/LMS: `app.toquran.org`

## Core Audience

- Parents
- Students/learners
- Teachers/tutors
- Admin/customer support

## Services

### Quran Memorization

Private tutoring around memorization, recitation practice, progress follow-up, parent visibility, sessions, tasks, and teacher feedback.

Week14 reuse:

- adapt sessions, teacher/student/parent task flows, Library attachments, and progress surfaces
- adapt service/subject catalog

### Quranic Arabic

Private tutoring around Arabic reading, Quranic vocabulary, comprehension, and language support.

Week14 reuse:

- adapt subject/service/class/session structures
- defer vocabulary games until Arabic vocabulary planning

### Arabic Language

Internal LMS class subject for broader Arabic language support. It is distinct from Quranic Arabic, which is tied more directly to Quran reading, Quranic vocabulary, comprehension, and recitation-adjacent language needs.

Week14 reuse:

- reuse class/subject/session/task assignment structures
- keep school-subject expansion paths available but inactive until approved

### Islamic Studies

Internal LMS class subject and child-facing service for age-appropriate Islamic studies lessons, adab, basic aqeedah/fiqh themes, stories, and practical Islamic knowledge. It is selected-only for intake transfer, and can also be manually activated/deactivated from a student's Subject Access page.

### Quran Literature

Internal LMS class subject and child-facing service for Quran stories, meanings, themes, reflection, and literature-style support around Quran learning. It is selected-only for intake transfer, and can also be manually activated/deactivated from a student's Subject Access page.

### My Deen Journey

To Quran service that combines:

- learner task journey experience
- rewards
- behavior/accountability points
- consequence agreements
- parent involvement
- progress follow-up
- Quran/Salah/adab/home habit framing where approved by owner

Week14 reuse:

- adapt Journey UI/workflows
- adapt rewards, behavior/discipline points, consequence agreements
- adapt parent/teacher task approval and quick-action flows

Do not treat this as just a rename of Week14 Journey.

### Well Being

Internal LMS learning/behavior subject used for behavior/accountability points and related parent/teacher behavior workflows.

Important boundary:

- My Deen Journey is the learner journey/service experience.
- Well Being is the behavior-points subject surface.
- Parent-written behavior points affect Well Being only through `ParentBehaviorSubjectResolver`.

### Paid Parental Consultation

Parent/support consultation that may or may not create child LMS enrollment.

Week14 reuse:

- adapt booking/intake/admin review foundation
- likely needs new To Quran-specific service workflow and reporting decisions

### Sanad Ijazah Program

Advanced Quran recitation/certification service.

Week14 reuse:

- likely reuse family/session/task/account structures
- new service metadata and teacher qualification workflow likely needed

### Internal LMS Class-Subject Catalog

The app-side LMS class-subject catalog is:

- Quran Memorization
- Arabic Language
- Quranic Arabic
- Sanad Program
- Islamic Studies
- Quran Literature
- My Deen Journey / `MDJ`
- Well Being

My Deen Journey and Well Being are fixed LMS surfaces active for all students. Quran Memorization, Quranic Arabic, Arabic Language, Sanad Program, Islamic Studies, and Quran Literature are selected-only or manually activated subjects. Keep inherited Week14 school-subject classes/subjects as inactive future-expansion options where practical, especially if My Deen Journey later expands into broader school-subject support.

## Intake Alias Rules

The app canonical service values are:

- Quran Memorization
- Quranic Arabic
- Arabic Language
- Islamic Studies
- Quran Literature
- My Deen Journey
- Sanad Ijazah
- Paid Parental Consultation (app-supported parent/support workflow; not part of the launch child-facing public selector)

The launch public child-facing booking selector should expose:

- Quran Memorization
- Quranic Arabic
- Arabic Language
- Islamic Studies
- Quran Literature
- Sanad Ijazah Program
- My Deen Journey

Each child can select one or more of those values. `Paid Parental Consultation` remains supported app-side, but it is not part of the owner-confirmed child-facing multi-service selector for the first public website handoff.

Inherited Week14 labels may still arrive from old fixtures, old data, or transitional public forms. App intake normalization maps them to To Quran values instead of exposing them as product labels:

| Legacy/transitional label | To Quran value |
| --- | --- |
| IB Private Classes / IB Private Tutoring | Quran Memorization |
| Help Me Read / SAT or ACT preparation | Quranic Arabic |
| Arabic / Arabic Language | Arabic Language |
| Islamic / Islamic Study / Islamic Studies | Islamic Studies |
| Quran Literature / Qur'an Literature / Quranic Literature / Quran Stories | Quran Literature |
| Help Me Study | My Deen Journey |
| My Deen Journey (Parenting System) | My Deen Journey |
| Sanad Ijazah Program | Sanad Ijazah |
| Sanad Program | Sanad Ijazah / Sanad Program class subject |

## Launch Scope

For first deployment, do not add new scheduling, finance, consultation-calendar, or full class-management systems. The app launch should reuse the current Week14-style manual operations around those areas while the LMS handles intake review, account transfer, family workspace, teacher/student/parent access, sessions, and tasks.

## Terms To Avoid As Ongoing To Quran Product Labels

- Week14
- IB Private Tutoring
- Language and Literature as the default visible service
- Hangman as user-facing vocabulary game label
- English vocabulary games as first-import scope

## Terms That Need Owner Confirmation During Import

- Whether "Automated Tasks" remains visible or becomes "Routines" / "Journey Routines" for My Deen Journey.
- Whether "behavior points" and "accountability points" are separate labels or one app concept with context labels.
- Whether "consequence agreements" is the final parent/student-facing phrase.
