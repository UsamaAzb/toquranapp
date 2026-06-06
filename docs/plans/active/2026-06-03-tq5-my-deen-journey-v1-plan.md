# TQ5 My Deen Journey V1 Launch Plan

Status: complete for TQ5 app launch scope with post-review fixes in the current working tree; base TQ5 launch was committed in `813fde8`; no production deployment, merge, smoke cleanup, credential rotation, or public website work performed
Date: 2026-06-04
Sprint: TQ5 My Deen Journey V1
Branch: `codex/tq5-my-deen-journey-launch`

## Objective

Adapt the inherited Week14 Journey, rewards, behavior/accountability points, consequence agreements, parent quick actions, and progress follow-up into a coherent To Quran My Deen Journey launch experience.

TQ5 should make the existing app surfaces feel like one launch-ready MDJ workflow for parents, students, and teachers without creating a new finance, scheduling, class-management, or deployment project.

## Sprint / Roadmap Relationship

- TQ3/TQ3.5/TQ4 already established first launch access, family workspace, teacher assignment, task review, and core session/task smoke.
- TQ5 is the completed learner/parent accountability experience layer for the app launch scope.
- TQ9 deployment readiness remains separate. Do not do production deployment, smoke cleanup, temporary credential rotation, or merge-to-main work from this plan.
- Public website copy already presents My Deen Journey as daily Islamic tasks, rewards, agreed consequences, parent involvement, and progress follow-up. No public website work is part of TQ5; this app sprint should document app reality if a later handoff needs it.

## Current Audit Findings

### What Already Exists Locally

- Student Journey routes exist under `routes/web.php`:
  - `/student/journey`
  - `/student/journey/board/{student_id?}/{teachersubjectid?}`
  - `/student/consequence-agreement/{student_id}/{teachersubjectid?}`
  - `/student/reward-discpline/{student_id}/{teachersubjectid?}`
  - `/student/tasks/{sessionId}/journey/{student_id?}`
- Teacher Journey/reward/points routes exist under the teacher route group.
- Parent Points Lab route exists at `/parent/reward-discpline/{student_id}/`.
- Parent quick actions are already present on My Children:
  - Review
  - Add points
  - Points Lab
  - Rewards
  - Open workspace
- Parent trusted-child auto-approval settings already exist in the My Children topbar through `parent.trusted-child-approval-setting`.
- Parent behavior modal can write positive/slip/red-flag behavior events, choose point values, attach consequence agreements for slip/red-flag events, and dispatch reward-point refresh events.
- `RewardProgressionService`, `GiftBoard`, `PointsProgress`, `Teacher\RewardDisciplinePoints`, `Parent\BehaviorModal`, and the parent child-card shell are imported from Week14 with little or no local drift.
- `StudentTaskApprovalService` and `Student\Journey` are adapted locally so student PIN completion can pass fixed/default task effort points without letting students edit final task points in the Journey view.
- Admin reward privacy already exists on the student reward page: teachers are hidden from gift names/images unless they have `view student reward gift details`.
- To Quran subject provisioning already knows:
  - Quran Memorization
  - Quranic Arabic
  - Arabic Language
  - Sanad Program
  - My Deen Journey
  - Well Being
- `ParentBehaviorSubjectResolver` already enforces the key boundary: parent-written behavior points resolve to the Well Being subject, not MDJ.
- Transfer flow already creates the default 10-gift runway and attempts to seed behavior/consequence rows for each transferred student.

### What Was Week14 / Fragmented Before TQ5

- Visible app labels previously exposed inherited Week14 framing:
  - `Reward System`
  - `Discipline Points`
  - `Behavior Points`
  - route slug typo `reward-discpline`
- Launch-visible reward/behavior labels have since been aligned to short To Quran labels such as `Rewards`, `Points Lab`, and `Task Completion PIN`; the route slug typo remains accepted URL debt.
- Parent, student, and teacher entry points now have light My Deen Journey launch framing while keeping the short action labels selected for launch.
- Teacher session quick cards still use simple titles such as `Rewards` and `Points Lab`, with an added My Deen Journey follow-up frame.
- Admin student account tabs and PIN wording were updated after launch testing showed those inherited labels were confusing.
- Parent consequence agreement visibility is indirect. TQ5 will not add a parent agreement link or recording workflow because agreements are handled through meetings.
- Student consequence agreement is visible when a `teacherSubjectId` is available, but the card still uses inherited wording.
- Trusted-child auto-approval is launch-visible to parents. Admin may receive read-only support visibility only; admin/staff must not manage the setting in TQ5.
- Stale Week14 test assertions in `tests/Feature/CoreLms/ParentTeacherTaskApprovalWorkflowTest.php` were remediated to assert To Quran baseline evidence and current route-helper usage.
- Behavior/consequence starter data was empty for launch templates before the guarded TQ5 starter-data patch. See DB evidence below.
- Current consequence-agreement navigation depends on a `teacherSubjectId`. TQ5 must define a safe fallback for students who have MDJ/Well Being available but no usable teacher-subject context.
- Current launch teacher workflow still inherits the TQ4 one-active-student-per-class assumption for per-student teacher actions such as rewards, points, and task review.

### Public Website Claim Check

The paired public repo `D:\xampp\htdocs\toquran` currently describes My Deen Journey as:

- daily Islamic tasks for Salah, Adhkar, manners, hygiene, Quran progress, and home habits;
- custom life tasks;
- points-based rewards agreed in advance;
- fair and consistent consequences;
- parent consultation/design support;
- visible progress and rewards.

TQ5 implementation should make the app launch experience support these claims at a modest V1 level. No public website code or copy changes are included.

## Owner Decisions / Product Contracts

These decisions are part of the TQ5 product contract.

1. Trusted-child admin contract:
   - Decision: trusted-child auto-approval remains parent-owned.
   - Admin may have optional read-only visibility for support.
   - Admin/staff must not change the trusted-child setting in TQ5.
   - `updated_by_user_id` remains the parent user who changed the setting.

2. Launch-visible labels:
   - Decision: keep short parent/student-facing labels as they are for launch unless a specific label is clearly confusing.
   - Examples to keep: `Rewards`, `Points Lab`, `Consequence Agreement`.
   - Admin/internal code titles may be more explicit where needed, but parent/student labels should stay simple and short.
   - Do not introduce broad relabeling such as `Add accountability` or `Well Being Points` in TQ5 without a separate owner request.

3. Public website claim alignment:
   - This means checking whether public copy promises something the app V1 does not actually provide.
   - Possible mismatch examples: automated habit scheduling, fully pre-built daily habit calendars, or app-managed agreement creation.
   - Decision: TQ5 records app reality and any mismatch in shared docs only. Public website code changes are not part of TQ5 unless explicitly requested later.

4. Parent consequence agreement surface:
   - Decision: no direct parent agreement review link in TQ5.
   - Decision: no new agreement-recording workflow in TQ5.
   - Agreements are decided through meetings. The app may preserve existing inherited surfaces/data, but TQ5 must not build new parent-facing agreement creation/review links.
   - If no valid `teacherSubjectId` is available, do not surface a broken agreement action.

5. Route typo debt:
   - Existing URLs use `reward-discpline`.
   - Decision: typo cleanup is allowed only if it is low-cost and does not create broad churn.
   - If changing it touches many routes/views/tests or risks breaking existing links, keep it as accepted debt.

## Week14 Reuse Source Files / Modules

Use Week14 first and adapt language carefully. Known Week14 references:

- `D:\xampp\htdocs\week14-app-lms\docs\plans\archive\2026-05-06-p5-rewards-journey-maturity.md`
- `D:\xampp\htdocs\week14-app-lms\docs\plans\archive\2026-05-07-p5-points-lab-subject-parent-access.md`
- `D:\xampp\htdocs\week14-app-lms\docs\plans\archive\2026-05-05-parent-teacher-quick-actions.md`
- `D:\xampp\htdocs\week14-app-lms\docs\plans\archive\2026-05-14-library-attachment-journey-stabilization-plan.md`
- `D:\xampp\htdocs\week14-app-lms\docs\LMS-UI-DESIGN-DIRECTION.md`
- `D:\xampp\htdocs\week14-app-lms\docs\WEEK14-LOGIC.md`
- `D:\xampp\htdocs\week14-app-lms\docs\ROUTES-AND-AUTH.md`

Primary local modules to reuse, not rebuild:

- `app/Services/RewardProgressionService.php`
- `app/Services/StudentTaskApprovalService.php`
- `app/Livewire/Student/Journey.php`
- `app/Livewire/Ui/GiftBoard.php`
- `app/Livewire/Ui/PointsProgress.php`
- `app/Livewire/Teacher/RewardDisciplinePoints.php`
- `app/Livewire/Parent/BehaviorModal.php`
- `app/Support/ParentBehaviorSubjectResolver.php`
- `app/Support/BookingSubjectProvisioning.php`
- `app/Services/BookingTransferService.php`
- `resources/views/livewire/student/journey.blade.php`
- `resources/views/livewire/ui/gift-board.blade.php`
- `resources/views/livewire/teacher/reward-discipline-points.blade.php`
- `resources/views/livewire/parent/behavior-modal.blade.php`
- `resources/views/parent/students/my-children.blade.php`
- `resources/views/student/workplace.blade.php`
- `resources/views/layouts/sections/menu/verticalMenu.blade.php`

Week14 rules to preserve:

- normal student completion submits to review;
- PIN completion finalizes separately;
- `StudentTaskApprovalService` owns final task approval;
- `RewardProgressionService` owns signed point deltas, ledger, totals, gift progression, and redemption;
- gifts are milestones, not spendable currency;
- gift redemption does not deduct points;
- reached/redeemed gifts do not roll back;
- next gift unlocks immediately;
- default 10-gift runway uses 100-point intervals;
- parent-written behavior points are separate from task history and resolve through the configured Well Being subject.

## To Quran-Specific Implementation Plan

### 1. Implementation Preflight

- Verify current branch and dirty worktree before touching files.
- Keep existing user edits in `docs/TOQURAN-SPRINTS.md`, `docs/WORKFLOW.md`, and `docs/shared/DEPLOYMENT-AND-WORKFLOW-HANDOFF.md` intact unless the owner explicitly asks for those files to be updated.
- Completed read-only DB target check against the local real app DB:
  - database name must be `u504065335_to_quran`;
  - no public website connection should be used;
  - assert `subjects.id = 15` is active/current `My Deen Journey`;
  - assert `subjects.id = 16` is active/current `Well Being`;
  - assert `config('toquran.parent_behavior_subject_id')` resolves to the Well Being subject id;
  - abort implementation on any subject identity mismatch;
  - count `reward_discipline_transfer`, `punishments_suggestions`, `reward_discipline_points`, `punishment_agreements`, `student_gifts`, and `reward_points_ledger`;
  - document the result before any data patch.
- If the read-only check is slow again, inspect Laravel DB connection/bootstrap before attempting any write.
- Treat the stale Week14 test-string remediation in section 10 as a precondition before focused test results are used as a TQ5 pass/fail gate.
- Carry forward the TQ4 launch assumption of one current/active student per class for teacher per-student MDJ actions. Do not expand multi-student class UX in TQ5.

### 2. MDJ Navigation And Entry Framing

Create a clear My Deen Journey V1 entry frame while preserving existing routes for compatibility.

Candidate UI/copy changes:

- Parent My Children card:
  - make the child card communicate "My Deen Journey" as the section frame;
  - keep Review, Add points, Points Lab, Rewards, and Open workspace actions;
  - keep the MDJ visual frame separate from data writes: Add points / Points Lab must still resolve to Well Being through `ParentBehaviorSubjectResolver`;
  - keep parent/student-facing action labels short and simple for launch;
  - do not introduce broad relabeling such as `Add accountability`, `Well Being Points`, or `MDJ Rewards` unless separately requested;
  - do not add a parent agreement review/creation action.
- Student workplace:
  - frame reward progress and behavior/points as part of My Deen Journey;
  - avoid suggesting parent behavior points are MDJ subject writes.
- Sidebar/menu:
  - keep simple labels such as Rewards, Points Lab, and Consequence Agreement unless a specific label is clearly confusing;
  - keep existing URLs unless typo cleanup is confirmed low-cost.
- Teacher session cards:
  - make rewards and points feel like MDJ follow-up/accountability tools;
  - keep teacher quick actions simple and session-contextual.
  - keep the one-active-student-per-class launch assumption explicit; do not design this pass for multi-student class management.
- Breadcrumbs and tab cards:
  - keep launch-visible parent/student labels short; admin/internal labels may be more explicit where helpful.

Do not redesign the entire Vuexy shell. This is a targeted launch-framing pass.

### 3. Rewards And Gift Progression

Keep the Week14 reward engine intact unless a launch blocker appears.

Implementation tasks:

- Preserve `RewardProgressionService` mechanics and idempotency.
- Preserve gift privacy masking and permission behavior:
  - parents, students, admins, and superadmins can reveal gift titles/images;
  - teachers see generic reward cards by default;
  - teachers can see reward titles/images only when admin grants `view student reward gift details`;
  - the admin reward privacy UI must remain understandable after MDJ relabeling.
- Preserve gift detail and redeem/PIN flow.
- Preserve the To Quran reward win-sound enhancement currently in `resources/views/livewire/ui/gift-board.blade.php`.
- Keep parent/student-facing reward labels short and simple for launch.
- Verify transfer-created students have a 10-gift runway.
- Add or update focused tests only where labels/copy are intentionally changed.

### 4. Trusted Child Auto-Approval

Treat trusted-child auto-approval as part of MDJ progress follow-up, not as a reward or behavior-point feature.

Implementation tasks:

- Preserve the existing parent-owned trusted-child setting in `resources/views/parent/students/my-children.blade.php`.
- Preserve `StudentTaskApprovalSetting`, `StudentTaskApprovalService`, and `tasks:auto-approve-trusted-children` behavior.
- Admin contract: parent-owned setting, optional admin read-only visibility only, no admin write action.
- Keep `updated_by_user_id` as the parent who changed the setting.
- Keep trusted-child wording clear enough for To Quran parents; avoid hiding it behind Week14-only language.
- Add focused tests for the chosen admin/parent contract.

### 5. Behavior / Accountability Points

Keep MDJ and Well Being distinct.

Implementation tasks:

- Keep parent-written behavior points flowing through `ParentBehaviorSubjectResolver`.
- Keep `config/toquran.php` default `parent_behavior_subject_id` as `16`.
- Do not redirect parent behavior writes into subject 15 / My Deen Journey.
- Add an automated test proving parent behavior writes or `ParentBehaviorSubjectResolver` resolve to Well Being only.
- Treat database types `Positive`, `Slip`, and `No Way` as durable internal values.
- Use display aliases where needed:
  - `Positive` can remain visible;
  - `Slip` can remain or become "Slip";
  - `No Way` should continue displaying as "Red Flag" where existing UI already does this;
  - avoid harsher public-facing "discipline" language when a softer "accountability" label fits.
- Preserve parent quick-add speed and mobile friendliness.
- If touching any dropdown, date range, or form controls, preserve the documented mobile/tablet contained-control rules from `docs/WORKFLOW.md`.

### 6. Consequence Agreements

Use the existing consequence-agreement model where it already exists, but do not expand it into a parent-facing agreement product.

Implementation tasks:

- Do not add direct parent agreement review links.
- Do not add a new agreement-recording workflow.
- Treat agreements as meeting-defined family decisions, not an app-first form flow in TQ5.
- Preserve existing inherited agreement data/surfaces only where removing them would create unnecessary churn.
- Keep `Customized` agreement fallback unless product wording is explicitly changed.
- Update visible wording from `Punishment` where possible to `Consequence` or `Agreement`.
- Define fallback behavior when no valid `teacherSubjectId` is available:
  - do not surface a broken agreement action;
  - do not build a new fallback agreement link;
  - if an existing inherited surface requires context, show a neutral unavailable state and record the setup blocker.
- Do not build a broad family-contract product.

### 7. Progress Follow-Up

Make existing review, task, reward, and points surfaces tell one story.

Implementation tasks:

- Keep task review as the parent/teacher follow-up path.
- Include trusted-child auto-approval in the follow-up story if it remains available at launch.
- Keep reward-point progress visible through `PointsProgress` and Journey board.
- Keep behavior and task history readable from the points/accountability surface.
- Avoid building automated schedules, habit calendars, finance, or a new class-management workflow.

### 8. Starter / Reference Data

Read-only counts showed missing behavior/consequence starter rows, so TQ5 created and executed a guarded manual SQL patch.

Patch requirements:

- location: `database/manual/patches/`;
- include target checks for `u504065335_to_quran`;
- include backup/export evidence in the file header or companion execution note;
- no destructive cleanup;
- no public website DB target;
- insert or upsert To Quran starter behavior/accountability templates and consequence suggestions only where missing;
- use To Quran-appropriate wording for Quran learning, Salah, adab, cleanliness, parent respect, honesty, screen/device boundaries, and home responsibility;
- preserve the owner decision that agreements are meeting-defined and TQ5 does not create new parent agreement-review or agreement-recording workflow.

Do not remove smoke data during TQ5.

### 9. Test And Manual Verification Scope

Expected focused tests after implementation:

```powershell
php artisan test tests/Feature/CoreLms/StudentTaskApprovalSurfaceTest.php tests/Feature/CoreLms/ParentTeacherTaskApprovalWorkflowTest.php tests/Feature/CoreLms/JourneyAcademicYearTest.php tests/Feature/StudentWorkplaceLoadTest.php tests/Feature/BookingTransferLifecycleInitTest.php tests/Feature/BookingTransferGatingTest.php
```

Before treating the command above as a gate, complete section 10's stale Week14 test-string remediation.

Additional checks:

```powershell
php -l app/Support/ParentBehaviorSubjectResolver.php
php -l app/Services/BookingTransferService.php
php artisan view:clear
php artisan route:list --path=journey
git diff --check
```

Manual smoke, without Playwright:

- parent can open My Children and understand the MDJ action set;
- parent can add a positive behavior point;
- parent can add a slip/red-flag point and attach a consequence agreement;
- parent behavior write or resolver test proves the write resolves to Well Being only;
- student can open Journey board and see reward progress;
- student PIN completion keeps fixed task points readonly;
- parent can see and update trusted-child auto-approval;
- admin can only see trusted-child status if a read-only support surface is added;
- teacher can open session-context rewards/accountability actions for the assigned student;
- teacher without reward-detail permission sees generic reward titles/images;
- teacher with reward-detail permission can reveal reward titles/images;
- admin can grant/revoke teacher reward-detail visibility from the student reward page;
- reward point totals and gift status refresh after task approval or behavior write;
- Well Being is the subject used for parent behavior writes.

Do not use Playwright unless explicitly asked.

### 10. Test Debt / Existing Week14 Assertions

Before relying on the focused Core LMS tests, update or replace tests that still assert Week14-specific manual patch filenames or `u504065335_vuexy_week14` strings. This is a hard precondition before section 9 test output can be treated as a TQ5 pass/fail gate.

TQ5 should either:

- add To Quran manual evidence files for already-baselined reward/consequence indexes; or
- update tests to assert the To Quran baseline/manual evidence that actually exists.

The relevant schema guarantees already appear in the To Quran baseline:

- `punishment_agreements` unique `uq_pa_student_type_title`;
- `reward_points_ledger` unique `uq_rpl_student_year_source`;
- `student_gifts` unique `uq_sg_student_year_points`.

Do not create no-op SQL just to satisfy stale Week14 test strings unless that is the cleanest documented evidence path.

## DB Impact And Backup / Baseline Evidence

Expected schema impact: none.

Data impact: guarded starter/reference data only. No destructive cleanup and no schema change.

TQ5 local execution evidence:

- verified target database: `u504065335_to_quran`;
- verified subject 15: active/current `My Deen Journey`;
- verified subject 16: active/current `Well Being`;
- pre-patch counts: `reward_discipline_transfer=0`, `punishments_suggestions=0`, `reward_discipline_points=0`, `punishment_agreements=0`, `student_gifts=10`, `reward_points_ledger=0`, `punishment_types=0`;
- backup/export: full dump `database/manual/backups/2026-06-03-140853-u504065335_to_quran-before-tq5-mdj-starter-data.sql` was created before execution, but is intentionally not committed because it contained user/contact/booking rows and credential hashes;
- guarded patch: `database/manual/patches/2026-06-03-tq5-mdj-starter-behavior-reference-data.sql`;
- execution note: `database/manual/patches/2026-06-03-tq5-mdj-starter-behavior-reference-data-execution-note.sql`;
- post-patch counts: `punishment_types=2`, `punishments_suggestions=6`, `reward_discipline_transfer=12`.
- post-review safety correction: the reusable patch now uses a read-only guard variable before any insert, with every insert gated by the guard; rerunning the corrected patch on `u504065335_to_quran` was idempotent and returned the same counts.
- guard-failure path verified on 2026-06-04 by running the same patch through a temporary Laravel connection pointed at non-target local DB `toquranapp_local`; the guard emitted the refusal message and counts stayed unchanged: `punishment_types=0`, `punishments_suggestions=0`, `reward_discipline_transfer=0`.
- behavior/consequence wording refresh executed on 2026-06-05 after focused backup `database/manual/backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-wording-refresh.sql`;
- wording refresh patch: `database/manual/patches/2026-06-05-mdj-behavior-wording-refresh.sql`;
- wording refresh execution note: `database/manual/patches/2026-06-05-mdj-behavior-wording-refresh-execution-note.sql`;
- first-pass refreshed behavior titles verified as a compact 12-card launch set, but owner review rejected that as too narrow after the full ChatGPT behavior list was restored;
- first-pass consequence suggestions used meeting-editable sentences, but owner review rejected them as too robotic for student-visible agreement buttons;
- wording refresh guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.
- behavior icon mapping refresh executed on 2026-06-05 after focused backup `database/manual/backups/2026-06-05-u504065335_to_quran-before-mdj-behavior-icon-mapping-refresh.sql`;
- icon mapping patch: `database/manual/patches/2026-06-05-mdj-behavior-icon-mapping-refresh.sql`;
- icon mapping execution note: `database/manual/patches/2026-06-05-mdj-behavior-icon-mapping-refresh-execution-note.sql`;
- icon mapping verified `discipline_icons=12`, `reward_discipline_transfer_with_icons=12`, and `reward_discipline_points_with_icons=132`, replacing the single fallback heart icon with distinct existing discipline icons for launch behaviors;
- icon mapping guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.
- LMS-style behavior/consequence refresh executed on 2026-06-06 after focused backup `database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-lms-consequence-behavior-refresh.sql`;
- LMS-style refresh patch: `database/manual/patches/2026-06-06-mdj-lms-consequence-behavior-refresh.sql`;
- LMS-style refresh execution note: `database/manual/patches/2026-06-06-mdj-lms-consequence-behavior-refresh-execution-note.sql`;
- refreshed starter behavior counts now verify as `Positive=11`, `Slip=11`, and `No Way=9`; copied student rows verify as `Positive=121`, `Slip=121`, and `No Way=99`;
- refreshed behavior titles now verify as `Good Job`, `Good Effort`, `Focused`, `Good Adab`, `Honesty`, `Responsibility`, `Self-Control`, `Helping Others`, `Good Deed`, `Good Question`, `On Time`, `Oops!`, `Not Ready`, `Distracted`, `Time Wasted`, `Task Not Done`, `Low Practice`, `Adab Slip`, `Device Slip`, `Small Excuse`, `No Response`, `Rule Reminder`, `Serious Matter`, `Hurtful Words`, `Dishonesty`, `Cheating`, `Bullying`, `Aggression`, `Major Disrespect`, `Device Misuse`, and `Rule Broken`;
- consequence suggestions now verify as `10` Minor Slip and `10` Serious Action options, starting from Week14 LMS practical agreement language such as `No phone during study time today`, `Lose PlayStation time for one day`, `No phone or PlayStation for 3-5 days`, and `No outings / training / sleepovers until a reflection plan is approved`;
- LMS-style refresh guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.
- behavior icon remap executed on 2026-06-06 after focused backup `database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-behavior-icon-remap.sql`;
- behavior icon remap patch: `database/manual/patches/2026-06-06-mdj-behavior-icon-remap.sql`;
- behavior icon remap execution note: `database/manual/patches/2026-06-06-mdj-behavior-icon-remap-execution-note.sql`;
- remap fixed visually weak launch choices including thumbs-up `Oops!`, thumbs-up `Device Slip`, and crown `Rule Reminder` by reusing better-fitting Week14 icon files already copied to To Quran;
- icon remap guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.
- popup category flag fix executed on 2026-06-06 after focused backup `database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-popup-category-flag-fix.sql`;
- popup category flag patch: `database/manual/patches/2026-06-06-mdj-popup-category-flag-fix.sql`;
- popup category flag execution note: `database/manual/patches/2026-06-06-mdj-popup-category-flag-fix-execution-note.sql`;
- `Oops!` and `Serious Matter` now verify as `teacher_desc=1` popup category cards in both starter templates and copied student rows, so parent/teacher quick actions open the behavior/consequence modal instead of adding or deducting points directly;
- popup category flag guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.
- Good Job popup category flag fix executed on 2026-06-06 after focused backup `database/manual/backups/2026-06-06-u504065335_to_quran-before-mdj-good-job-popup-flag-fix.sql`;
- Good Job popup category flag patch: `database/manual/patches/2026-06-06-mdj-good-job-popup-flag-fix.sql`;
- Good Job popup category flag execution note: `database/manual/patches/2026-06-06-mdj-good-job-popup-flag-fix-execution-note.sql`;
- `Good Job` now verifies as `teacher_desc=1` in both starter templates and all 11 copied student rows, so the Positive first card opens the parent/teacher behavior modal instead of adding points directly;
- Good Job popup category flag guard-failure path was verified on `toquranapp_local`; the patch refused the wrong target and gated updates off.

Known baseline/backup evidence:

- `database/manual/README.md`
- `database/manual/backups/2026-05-27-235118-u504065335_to_quran-export.sql`
- `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data.sql`
- `database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data-execution-note.sql`
- `database/manual/backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql`
- `database/manual/patches/2026-05-29-launch-smoke-data-execution-note.sql`
- `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`

If implementation discovers any missing schema or reference-data prerequisite, stop and write guarded manual SQL or an execution note before applying DB changes.

## Implementation Evidence

Completed in this pass:

- added admin family workspace read-only trusted-child status badges for support visibility, with no admin write control;
- kept trusted-child ownership with parents;
- added light My Deen Journey framing to parent My Children, student workplace, and teacher session quick-action surfaces while preserving launch labels such as `Rewards`, `Points Lab`, and `Add points`;
- kept student-facing behavior/points framing simple as `Points follow-up` while the resolver/test contract preserves Well Being as the parent behavior subject;
- added automated coverage proving admin sees trusted/standard status read-only;
- added automated coverage proving parent behavior resolution stays on Well Being even when an MDJ teacher-subject context is passed;
- added automated Livewire write-path coverage proving a parent behavior modal save opened from an MDJ teacher-subject context persists through the Well Being teacher-subject and ledger subject;
- replaced MySQL-only subject priority ordering in `ParentBehaviorSubjectResolver` with portable SQL ordering;
- moved the reward-detail visibility switch out of the shared Points Lab progress bar; the toggle remains only on the rewards/gift board surface;
- refreshed starter behavior titles first to a compact launch set, then expanded them to the broader ChatGPT-reviewed set after owner review;
- refreshed default consequence suggestions first to practical sentences, then replaced them with the Week14 LMS practical agreement list plus a small To Quran layer after owner review;
- refreshed starter/copied behavior icon mappings so the admin/student behavior cards no longer all use the fallback heart icon;
- remapped visually weak behavior icons after manual review so launch cards use better-fitting Week14 icon files;
- restored the first Positive, Slip, and No Way behavior cards as popup category actions so they open the behavior/consequence modal instead of saving instant point changes;
- ordered `Customized` next to `None` in parent/teacher consequence agreement popups so it no longer appears in the middle of the practical agreement list;
- hardened the launch-default helper so existing copied behavior rows with stale fallback-heart icons are repaired on future default checks, not only by the manual DB patch;
- renamed the admin student behavior section from inherited `Reward Discipline Points` to the launch-facing `Points Lab`;
- renamed visible inherited reward/PIN labels to `Rewards`, `Points Lab`, and `Task Completion PIN` after manual testing exposed launch confusion;
- aligned activation email PIN wording to `Task Completion PIN`;
- updated stale Week14/manual-SQL contract tests to assert To Quran baseline evidence instead of old Week14 database names;
- updated student task approval surface tests to reflect the current extracted task-action component, 10-second polling, and shared attachment study viewer.

Focused verification completed:

```powershell
D:\php\php-8.4\php.exe artisan test tests/Feature/CoreLms/StudentTaskApprovalSurfaceTest.php tests/Feature/CoreLms/ParentTeacherTaskApprovalWorkflowTest.php tests/Feature/CoreLms/JourneyAcademicYearTest.php tests/Feature/StudentWorkplaceLoadTest.php tests/Feature/BookingTransferLifecycleInitTest.php tests/Feature/BookingTransferGatingTest.php tests/Feature/CoreLms/ParentBehaviorSubjectResolverTest.php tests/Feature/FamilyWorkspaceLifecycleTest.php
```

Final closure-review result after adding the parent modal write-path test: 77 passed, 758 assertions.

Post-manual-testing label/icon cleanup verification:

```powershell
D:\php\php-8.4\php.exe artisan test tests\Feature\MyDeenJourneyLaunchDefaultsTest.php tests\Feature\CoreLms\ParentBehaviorSubjectResolverTest.php tests\Unit\FamilyLifecycleServiceTest.php
```

Result before the June 6 LMS-style refresh: 34 passed, 125 assertions. This includes coverage that old copied behavior rows using the fallback heart icon are healed to the mapped launch icon.

Post-LMS-style behavior/consequence refresh verification:

```powershell
D:\php\php-8.4\php.exe artisan test tests\Feature\MyDeenJourneyLaunchDefaultsTest.php tests\Feature\CoreLms\ParentBehaviorSubjectResolverTest.php tests\Unit\FamilyLifecycleServiceTest.php
D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LifecycleGateTest.php tests\Feature\CoreLms\TeacherAttachmentStateTest.php tests\Feature\CoreLms\StudentTaskApprovalSurfaceTest.php tests\Feature\StudentWorkplaceLoadTest.php
git diff --check
```

Result: 34 passed, 125 assertions; 85 passed, 391 assertions; diff whitespace check clean.

Post-popup-category flag fix verification:

```powershell
D:\php\php-8.4\php.exe artisan test tests\Feature\MyDeenJourneyLaunchDefaultsTest.php tests\Feature\CoreLms\ParentBehaviorSubjectResolverTest.php
D:\php\php-8.4\php.exe -l app\Support\MyDeenJourneyLaunchDefaults.php
D:\php\php-8.4\php.exe -l app\Livewire\Teacher\RewardDisciplinePoints.php
D:\php\php-8.4\php.exe -l app\Livewire\Parent\BehaviorModal.php
D:\php\php-8.4\php.exe -l app\Livewire\Teacher\BehaviorModal.php
```

Result: 6 passed, 25 assertions. `Good Job`, `Oops!`, and `Serious Matter` verified in `reward_discipline_points` as `teacher_desc=1` for all 11 existing local student copies.

Teacher reward privacy subset:

```powershell
D:\php\php-8.4\php.exe artisan test tests/Feature/CoreLms/LifecycleGateTest.php --filter "teacher_.*reward|points_progress_masks|admin_reward_privacy"
```

Result: 5 passed, 21 assertions.

Additional checks completed:

```powershell
D:\php\php-8.4\php.exe -l app/Livewire/Admin/Families/FamilyWorkspace.php
D:\php\php-8.4\php.exe -l app/Support/ParentBehaviorSubjectResolver.php
D:\php\php-8.4\php.exe -l tests/Feature/CoreLms/StudentTaskApprovalSurfaceTest.php
D:\php\php-8.4\php.exe -l tests/Feature/CoreLms/ParentBehaviorSubjectResolverTest.php
D:\php\php-8.4\php.exe -l tests/Feature/CoreLms/ParentTeacherTaskApprovalWorkflowTest.php
D:\php\php-8.4\php.exe -l tests/Feature/FamilyWorkspaceLifecycleTest.php
D:\php\php-8.4\php.exe artisan view:clear
D:\php\php-8.4\php.exe artisan route:list --path=journey
git diff --check
```

Result: passed. Closure-review cleanup normalized the teacher quick-action header line endings without changing its content, and the final `git diff --check` result is clean.

Environment note: this workspace's default `php` on PATH may be older than the installed vendor platform requirements. Use `D:\php\php-8.4\php.exe` for the TQ5 verification commands unless PATH is updated.

## Public Website Handoff

No public website implementation is part of this app plan unless explicitly requested.

No public website work is needed for TQ5. If app behavior differs from current public-site claims, keep any note internal to the app/shared handoff trail for a later sprint:

- whether V1 supports daily Islamic habit tasks as a manual task/Journey workflow rather than an automated habit scheduler;
- whether rewards are milestone gifts/experiences, not spendable currency;
- whether consequence agreements are meeting-defined family decisions rather than a new parent-facing app workflow;
- whether progress follow-up is task review, reward progress, and behavior/accountability history;
- whether My Deen Journey and Well Being remain distinct inside the app even if public copy presents the family experience under MDJ.

If shared decisions change, update `docs/shared/` and state implementation ownership. Do not edit `D:\xampp\htdocs\toquran` in TQ5.

## Non-Goals

- No production deployment.
- No merge to `main` until TQ5 is genuinely complete and explicitly requested.
- No finance implementation.
- No automated scheduling implementation.
- No broad class-management implementation.
- No public website deployment.
- No smoke-data cleanup.
- No temporary credential rotation.
- No Playwright verification unless explicitly asked.
- No English vocabulary game imports.
- No Arabic vocabulary game planning unless reopened.
- No collapsing My Deen Journey and Well Being into one subject.
- No destructive DB operations.

## Accepted Known Debt

- Keep the `reward-discpline` route slug typo if fixing it would create broad route/view/test churn. Fixing the typo is allowed only if it is cheap and low-risk.
- Keep the inherited `session-agreement-reword-header` filename/class typo as accepted route/include debt for TQ5 because the user-facing text is correct and changing it would create avoidable churn.
- Keep the one-current/active-student-per-class launch assumption for teacher per-student MDJ actions. Multi-student classes require a future deliberate UX pass.
- Keep parent/student-facing labels simple and short for launch.
- Visible admin reward/PIN labels were changed to `Rewards`, `Points Lab`, and `Task Completion PIN` after launch confusion was reported during manual testing. Internal class names, filenames, and existing route slugs can remain inherited debt when changing them would create avoidable churn.
- Keep `reward_discipline_transfer` idempotency limited to `NOT EXISTS (title, type)` for this one-shot manual starter-data patch. A future admin-editable seed catalog would need a durable key or separate migration strategy.

## Completion Definition

TQ5 is complete when:

- parent, student, and teacher launch surfaces present Journey, rewards, accountability points, agreements, and review/follow-up as a coherent MDJ V1 experience;
- trusted-child auto-approval remains parent-owned, with optional admin read-only visibility only;
- teacher reward title/image visibility is preserved, verified, and understandable in the MDJ-labeled admin UI;
- the local DB target verifies subject 15 as My Deen Journey, subject 16 as Well Being, and `toquran.parent_behavior_subject_id` as the Well Being id;
- parent behavior writes are proven by automated test to affect Well Being only;
- reward/gift progression is still governed by Week14's accepted reward services;
- transferred students have required reward/accountability starter data or a documented manual-data blocker;
- no new parent agreement review/recording workflow is added, and any inherited agreement surface fails neutrally when no `teacherSubjectId` is available;
- focused tests pass or any remaining failures are documented as unrelated existing debt;
- public website handoff notes are updated if app reality and public claims differ;
- no production deployment, merge, cleanup, or credential rotation was performed.
