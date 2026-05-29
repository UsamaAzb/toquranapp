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

## Intake Alias Rules

The app canonical service values are:

- Quran Memorization
- Quranic Arabic
- My Deen Journey
- Paid Parental Consultation
- Sanad Ijazah

Inherited Week14 labels may still arrive from old fixtures, old data, or transitional public forms. App intake normalization maps them to To Quran values instead of exposing them as product labels:

| Legacy/transitional label | To Quran value |
| --- | --- |
| IB Private Classes / IB Private Tutoring | Quran Memorization |
| Help Me Read / SAT or ACT preparation | Quranic Arabic |
| Help Me Study | My Deen Journey |
| My Deen Journey (Parenting System) | My Deen Journey |
| Sanad Ijazah Program | Sanad Ijazah |

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
- Whether "Quranic Arabic" and "Arabic" are separate services or one service with tracks.
