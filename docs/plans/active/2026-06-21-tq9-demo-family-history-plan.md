# TQ9 Demo Family History Plan

Status: Phases 1-2 complete locally; Phase 3 blocked on missing local `Tajweed Beginner's Book`; no production demo data written yet
Date: 2026-06-21
Branch: `codex/tq9-launch-readiness`
Scope: one intentional production demo family after core app deployment smoke

## Objective

Create one realistic demo family that can be used to show the To Quran app after launch:

- one parent account;
- three active child/student accounts with different ages;
- real subject enrollment and teacher assignment visibility;
- Quran Memorization, Quranic Arabic, Arabic Language, My Deen Journey, and Well Being history;
- reusable `Tajweed Beginner's Book` Library folder with the beginner book PDFs/videos available in app storage;
- session/task history that looks like normal To Quran work;
- daily automated-style Salah, Azkar, Deen Journey, and Well Being records;
- points lab behavior history;
- age-appropriate gift queues with at least 20 gifts per student, 10 reached and claimed.

This plan does not authorize production data writes by itself. The implementation must be a guarded command or reviewed manual data artifact, then run only after backup/evidence and exact owner approval.

## Implementation Evidence

Local implementation evidence as of 2026-06-21:

- command added: `toquran:bootstrap-demo-family`
- command file: `app/Console/Commands/BootstrapDemoFamily.php`
- focused tests added: `tests/Feature/CoreLms/BootstrapDemoFamilyCommandTest.php`
- command guards:
  - `--confirm-db` must match the active DB for writes;
  - non-test DB must be `u504065335_to_quran`;
  - expected launch table floor is at least `357`;
  - default teacher and active superadmin must exist;
  - `Tajweed Beginner's Book` must have `84` active resources and readable stored files;
  - `Quran Repetition` must have the required active surah folders and YouTube resources for Al-Faatiha, An-Naas, Al-Falaq, and Al-Ikhlaas;
  - Quran Memorization "Watch the repetition video" tasks attach the matching Quran Repetition resource through the normal General Library snapshot writer;
  - demo data is keyed by `TQDEMO-001` and reruns are idempotent.
- implementation uses the exact owner-approved parent email/phone: `osama.salem0217@gmail.com` and `+201091051913`.
- family/demo creation is direct and guarded instead of being driven through the live website form, because that is safer and faster for a large curated history. Website intake/email behavior remains a separate smoke test through the normal app lifecycle; no demo-only email behavior is planned.
- local focused verification passed on 2026-06-21:
  - `php -l app\Console\Commands\BootstrapDemoFamily.php`
  - `php artisan test tests\Feature\CoreLms\BootstrapDemoFamilyCommandTest.php tests\Feature\BookingTransferGatingTest.php tests\Feature\StudentWorkplaceLoadTest.php tests\Unit\LibraryResourceAttachmentWriterTest.php`
  - result: `42 passed`, `261 assertions`.
- local dry-run against the developer DB currently stops because the local DB does not have the owner-uploaded `Tajweed Beginner's Book` folder; this is expected until testing is performed against the deployed/production app DB where the owner uploaded the files.

## Safety Rules

- Do not use the old `toquran:bootstrap-smoke-data` command. It creates multiple smoke users and is not suitable for production demo data.
- Do not create admin/support/smoke accounts.
- Keep existing production launch accounts:
  - owner superadmin;
  - intended default teacher account, currently `drosamaqandil@gmail.com`.
- Create only one demo parent plus three demo students.
- Mark demo rows with stable, searchable names/references such as `TQDEMO-001`, `demo_family`, or `Demo` in notes where the table supports it.
- Use `--confirm-db=u504065335_to_quran` and refuse to run on any other DB.
- Refuse to run if production table count is below the expected launch baseline or the two launch users are missing.
- Make the command idempotent: running it again updates/verifies the same demo family, not duplicates it.
- Idempotency must detect the existing `TQDEMO-001` intake/family and verify or skip transfer-created rows. Do not call transfer again for a child that is already transferred.
- Do not print or commit passwords.
- Do not store private source file paths in DB descriptions visible to users.

## Existing Reference Data To Use

Confirmed local reference IDs:

- `subjects`
  - `1` Quran Memorization
  - `2` Quranic Arabic
  - `3` Arabic Language
  - `15` My Deen Journey
  - `16` Well Being
- `grade_levels`
  - `2` Beginner
- Beginner `grade_level_subjects`
  - `4` Quran Memorization
  - `5` Quranic Arabic
  - `6` My Deen Journey
  - `16` Arabic Language
  - `18` Well Being
- Demo grade level: all three demo children use Beginner unless implementation review proves a better existing launch grade. Omar's Arabic Language override requires the Arabic Language Beginner grade-level-subject row to be active.
- `task_types`
  - use `7` Assignment for normal practice tasks.
- Current Quran Repetition Library resources exist for:
  - `001. Al-Faatiha`: Ayahs 1-3, Ayahs 4-6, Ayah 7, Full Surah;
  - `114. An-Naas`: Ayahs 1-3, Ayahs 4-6, Full Surah;
  - `113. Al-Falaq`: Ayahs 1-3, Ayahs 4-5, Full Surah;
  - `112. Al-Ikhlaas`: Full Surah.

## Demo Family Story

Use a family that feels real but clearly safe/demo:

- Parent: `Demo Parent Amina`
- Parent email: `osama.salem0217@gmail.com`
- Parent phone: `01091051913` locally, normalized to `+201091051913` where the app expects international format.
- Family reference: `TQDEMO-001`
- Teacher: current default teacher
- Enrollment status: active
- Schedule: two class sessions per week, Monday and Thursday, evening

Creation flow:

- Create a realistic app-owned intake scaffold: `bookings` + three `booking_children` rows with `TQDEMO-001` reference/notes.
- Make the rows fit-ready for transfer, then call the normal `BookingTransferService::transferChild()` for each child.
  - Code gate confirmed: each `booking_children` row must have `evaluation_outcome='fit'`, `meeting_disposition` in `completed`, `cancelled`, or `no_meeting_required`, at least one mapped service interest, and an effective grade level before transfer will run.
- The normal transfer flow seeds child accounts, 10 default gifts, behavior templates, and the student PIN. The demo command must reuse and adapt those rows instead of creating a parallel family structure.
- After transfer, explicitly apply the intended subject set per child. Omar is a deliberate demo override: deactivate Quranic Arabic for Omar and activate Arabic Language, while Yusuf/Maryam keep Quranic Arabic active.
- Activation emails are not sent by transfer. After the demo family is created and verified, activate the three children while the family is still pending, then activate the family. Code review confirms this sequence queues/sends 1 parent activation email and 3 child activation emails to the owner parent inbox.
- Before activation-email smoke, verify production queue behavior. If jobs are queued asynchronously, process the queue or use the existing Family Workspace resend action after activation; do not add custom demo-only email behavior.

Children:

1. `Yusuf Demo`, age 5
   - Subjects: Quran Memorization, Quranic Arabic, My Deen Journey, Well Being.
   - Quran pace: 1 ayah per session.
   - Quranic Arabic: beginner book page work, one page spread across 3-4 sessions.
   - Deen Journey: learning Salah gently; Maghrib once a day; one Morning Azkar dua and one Evening Azkar dua.
   - Well Being: shower routine, brushing teeth, sleep readiness, toy cleanup.

2. `Maryam Demo`, age 6
   - Subjects: Quran Memorization, Quranic Arabic, My Deen Journey, Well Being.
   - Quran pace: 1 ayah per session.
   - Quranic Arabic: beginner book page work, slightly ahead of Yusuf.
   - Deen Journey: learning/tracking the five Salahs with mixed completion; stronger Maghrib/Isha habit; rotating Morning and Evening Azkar.
   - Well Being: brushing teeth, shower, school bag/materials ready, tidy learning place.

3. `Omar Demo`, age 9
   - Subjects: Quran Memorization, Arabic Language, My Deen Journey, Well Being.
   - Quran pace: 3 ayahs per session.
   - Arabic Language: age-appropriate reading/writing tasks rather than the beginner Tajweed book.
   - Deen Journey: tracks the five Salahs; mosque target a few times weekly with parent; Morning and Evening Azkar.
   - Well Being: independence routines, device limits, respectful sibling help, sleep/readiness.

## BEGINNERS BOOK Library Plan

Original owner local source folder was used for inventory reference. Do not store or depend on the private local path in production data.

Observed source inventory on 2026-06-21:

- 84 files total;
- 44 PDF files;
- 40 MP4 files;
- 182,688,675 bytes total.

Owner update on 2026-06-21:

- Owner logged in and uploaded all BEGINNERS BOOK files manually into the existing Library folder `Tajweed Beginner's Book`.
- Implementation must inspect the existing `Tajweed Beginner's Book` Library folder and verify the registered resource inventory against the expected `84` files / `182,688,675` bytes as closely as the server permits.
- Verification gate: query active `general_library_resources` under the `Tajweed Beginner's Book` folder. Pass only if file count is `84`; if `file_size` is populated, summed `file_size` should match `182,688,675` bytes or be explained by a documented upload/storage difference.
- Do not assume the production command can read the local `D:\...` path.
- Earlier owner smoke observed only 20 uploaded resources after selecting the full local folder. Owner later completed the upload. Before demo creation, verify the final real resource count and stop if the folder is still incomplete.
- Owner also needs large book/video uploads. The app Library upload cap is intended to be `500 MB` per file; Hostinger PHP `uploadMaxFilesize` and `postMaxSize` must remain at least `500 MB`, and preferably above it for multi-file batches.
- App code update on 2026-06-21 raised `LibraryResourceValidator::MAX_UPLOAD_KB` to `512000` (`500 MB`) and was deployed to Hostinger with Laravel caches refreshed. Production verification reported `512000`.

Use the existing General Library folder/resources:

- use the existing shared folder title: `Tajweed Beginner's Book`
- description: `Quranic Arabic beginner book pages and page videos for launch demo lessons.`
- `content_mode=mixed`
- `source_label=Demo`

Because the owner already created this folder and uploaded the files, treat this metadata as a verification target, not a reason to overwrite user-managed Library data. The command may normalize clearly missing demo metadata only after reporting the current values in dry-run output.

Attachment safety note:

- For demo session tasks, attach only file/link/YouTube Library resources unless production `attachment_files.type` explicitly supports `text`.
- General Library text sources are valid Library resources, but the production task attachment table is currently verified for `file`, `link`, and `youtube` snapshots. Dua/text-only content should stay as task descriptions or normal Library text resources unless a separate reviewed schema update adds text task attachments.
- Verify every attached beginner-book resource has `storage_disk` in `local` or `public`; `GeneralLibraryAttachmentSnapshotter` skips other disks.
- Attach key PDFs/videos intentionally rather than copying every repeated source into every historical task. The snapshotter creates public-disk attachment copies, so over-attaching the same 180 MB beginner-book set across many sessions can multiply storage use.

File grouping plan:

- Use each uploaded PDF and MP4 as an active `general_library_resources` file resource.
- Use the file number/lesson number for ordering.
- Pair videos with PDFs by lesson number and page number where obvious.
- Keep titles clean for teacher/student view, for example:
  - `1. Arabic Alphabet - PDF`
  - `1. Arabic Alphabet - Page 1 Video`
  - `3. Letter Positions - Page 3 Video`
  - `3. Letter Position 1 - PDF`
- Some PDFs have no matching video; that is okay.
- Some lessons have multiple videos/pages; create separate resources and attach the correct one to the appropriate session task.

Implementation preference:

- Reuse the existing uploaded resources in app-managed storage.
- Use the existing General Library file resource schema and existing Library snapshot/attachment logic.
- For session tasks, attach the selected General Library resource snapshot with `general__{general_library_resources.id}` selections through `LibraryResourceAttachmentWriter`.
- Code review confirmed file resources are copied into `attachment_files` snapshots through the normal task attachment flow; do not attach raw private storage paths directly.

## Session History Plan

Create approximately 12 weeks of detailed visible session/task history plus current open work:

- Date range: a realistic sequence ending near current launch date.
- Two sessions per week for each normal subject/class.
- Sessions should be normal teacher class sessions, not fake daily automated sessions.
- Each session for Quran Memorization should include:
  - Task 1: `Memorize and practice Ayah {n} from Surah {Name}`
  - Task 2: related Quran Repetition video attachment from the Library.
- Each Quranic Arabic session should include:
  - Task 1: beginner-book lesson/page task.
  - Task 2: attached PDF.
  - Task 3: attached page video when that page has one.
- Arabic Language for Omar should include:
  - short reading task;
  - short writing/copying task;
  - occasional review task.

Progression:

- Yusuf age 5:
  - Al-Faatiha ayahs 1-7 over 7 sessions.
  - An-Naas ayahs 1-6 over 6 sessions.
  - Current/open: Al-Falaq ayah 1.
  - Quranic Arabic: Arabic Alphabet, Letter Recognition, Letter Positions, Connecting Letters, Fathah.
- Maryam age 6:
  - Al-Faatiha ayahs 1-7 over 7 sessions.
  - An-Naas ayahs 1-6 over 6 sessions.
  - Al-Falaq ayahs 1-3 started.
  - Quranic Arabic: Arabic Alphabet through Words with Fathah/Kasrah.
- Omar age 9:
  - Al-Faatiha in grouped 3-ayah chunks.
  - An-Naas in two grouped chunks.
  - Al-Falaq in two grouped chunks.
  - Current/open: Al-Ikhlaas full-surah practice.
  - Arabic Language: reading fluency, dictation, handwriting, simple sentence building.

Task statuses:

- Most older tasks: `completed`, with points.
- A few recent tasks: `in_review`.
- Current tasks: `assigned`.
- Keep subject-card counts useful:
  - each child should have at least one current To Do task;
  - some pending-review tasks should exist;
  - completed tasks should show history.

Older history:

- The detailed session/task view does not need to show a full year of every row.
- To justify 10 reached/claimed gifts naturally, create older summarized reward/behavior/task history across roughly one year of demo activity.
- The one-year story can include monthly/periodic reward ledger entries, older completed-task point entries, and older gift milestone dates, all marked as demo history where the schema supports notes.
- 1000+ total points per child is acceptable and realistic over a year. Do not treat 1000 points as a problem; the implementation only needs the ledger, totals, gift history, and gift statuses to tell the same story.

## Demo Personality And Realism Layer

The demo history should feel like three real children, not three perfect generated profiles.

Use only existing app statuses and schemas. If a nuance such as `completed with help`, `parent reminder`, `pronunciation check`, or `teacher correction` is useful, store it in task titles, notes, feedback, or descriptions where the current app supports that text. Do not invent new status values or new parent-confirmation workflows for the demo.

Child personalities:

- Yusuf: sweet, young, needs reminders, improves through praise and short practice.
- Maryam: careful and helpful, sometimes delays a task or gets emotional after correction, then recovers.
- Omar: capable and more independent, sometimes distracted by devices or speed, learns responsibility and honest correction.

Realism rules:

- Include review/retry days instead of making every session advance perfectly.
- Avoid perfect daily worship streaks; mix completed, assigned, in-review, and occasional missed/late-style notes where supported.
- Keep consequences warm, practical, and age-appropriate.
- Keep Yusuf's penalties very small; reserve stronger recovery stories for Omar.
- Use existing `Tajweed Beginner's Book` and Quran Repetition Library resources for attachments, not placeholder content.
- Make gifts a clear parent-child agreement, not surprises. A gift can be small or large depending on the family agreement: money, candy, a phone, a dirt bike, a toy, a trip, parent-time, or another concrete reward.
- Keep bigger or expensive gifts as waiting/pending rather than already claimed unless the story explicitly needs one already-claimed milestone.

## Deen Journey And Well Being Daily Plan

For history, create controlled demo rows that look like normal student work and preserve source fields where required. Use the real TQ7.5 automation catalog only where it is safe for current/future assigned routines; do not depend on replaying the scheduler to manufacture old history.

Code boundary:

- Real TQ7.5 Automated Task generation requires valid `main_daily_session_templates`, versions, version tasks, student subscriptions, student assignments, active subject links, and `DailySessionPublisher` / `AutomatedTaskSnapshotWriter`.
- For current/future daily routines, prefer real automation assignment/subscription rows and let the publisher create generated snapshots.
- For backdated demo history, do not label rows as scheduler-generated unless they were produced by the real publisher/snapshot writer with valid source rows. If direct curated rows are safer, create normal class/session/task history for My Deen Journey and Well Being and mark it as demo history in supported notes/descriptions.
- Never create orphaned `generated_for_date` / `main_daily_session_template_id` rows without matching template/version/assignment provenance.

Daily automated-style tasks:

- Yusuf age 5:
  - Maghrib Prayer
  - Morning Dua: one short dua
  - Evening Dua: one short dua
  - Brush teeth
  - Shower day, twice weekly
  - Sleep early / bedtime readiness
  - Put learning materials away

- Maryam age 6:
  - Fajr, Dhuhr, Asr, Maghrib, Isha
  - Morning Azkar: 5 items
  - Evening Azkar: 5 items
  - Brush teeth morning/evening
  - Shower routine
  - Prepare learning place
  - Help parent with small chore

- Omar age 9:
  - Fajr, Dhuhr, Asr, Maghrib, Isha in mosque where practical
  - Morning Azkar set
  - Evening Azkar set
  - Quran review habit
  - Device limit respected
  - Help younger sibling
  - Sleep readiness
  - Personal hygiene independence

History:

- Mix completed, in-review, and assigned daily tasks.
- Keep enough completed days to make dashboards feel alive without creating thousands of rows.
- Prefer the last 30-45 days for detailed daily history; older history can be summarized through points/gifts.

## Points Lab Behavior Plan

Create behavior history through existing `student_session_discipline`, `reward_points_ledger`, `reward_totals`, and `student_gift_points_history` flow.

Use only behavior titles that exist in the seeded `reward_discipline_points` catalog for each student, then put child-specific detail in notes/descriptions where the current schema supports it. Example catalog choices may include positive/adab/responsibility/focus items, minor slips, and rare No Way items; do not invent new behavior catalog rows for the demo.

`RewardProgressionService::applyPointDelta()` may be used only for current-day behavior/point events where a `now()` timestamp is acceptable. It must not be used to create the backdated gift milestone story, because the reward service stamps gift `reached_at` and `redeemed_at` with the current time.

For demo-family backfill, prefer direct guarded writes for all demo point and gift history instead of `applyPointDelta()`. `applyPointDelta()` advances the gift queue based on the running total, so calling it after curated gift timestamps are set can accidentally mark later gifts as reached today. If it is used at all for demo students, it must run before the final gift reconciliation and the final verification must prove no extra gifts were advanced.

Idempotence rule:

- Every direct `reward_points_ledger` row must use a deterministic, demo-owned, per-student unique source identity.
- For behavior rows, prefer creating/reusing real `student_session_discipline` rows and using their IDs as `source_id` with `source_type='discipline'`.
- For summarized/backfill point rows, use `source_type='adjustment'` with a reserved deterministic source ID range or stable lookup key that cannot collide with existing rows for the same student/current operating-year bucket.
- Before insert/update, verify the unique ledger key `(student_id, academic_year_id, source_type, source_id)` so rerunning the command updates/verifies the same demo story instead of duplicating or failing.

Subject attribution rule:

- Every `reward_points_ledger` and `reward_totals` row must have a concrete `subject_id`; both production columns are non-null.
- Attribute points to the child subject that generated the work/behavior whenever possible.
- For summarized one-year point history, distribute points across the child's active launch subjects, with heavier weight on Quran Memorization and My Deen Journey / Well Being. Do not create subject totals for inactive subjects such as Quranic Arabic for Omar.
- Reconciliation must prove that the signed sum of all demo ledger rows across subjects equals the single student aggregate in `student_gift_points_history`, and that per-subject `reward_totals` equal the ledger sums for those subjects.
- Treat `student_gift_points_history` as the current signed aggregate row for a student/current operating-year bucket, not as a historical log. The visible story/history belongs in `reward_points_ledger`, tasks, and behavior rows.
- Direct `student_session_discipline` rows must populate the required teacher-subject-class, behavior/icon, and icon-path fields from the behavior templates seeded by transfer before they are used as ledger sources.

Behavior personalities:

- Yusuf:
  - positives: tried again after mistake, said salam politely, listened first time, cleaned toys, practiced Maghrib.
  - slips: forgot task, rushed recitation, needed screen reminder.
  - no serious pattern; keep any negative small and age-appropriate.

- Maryam:
  - positives: followed Salah tracker honestly, helped sibling, careful Arabic page practice, honest correction, tidy learning place.
  - slips: delayed homework, interrupted teacher once, forgot toothbrush.
  - one consequence example if needed: parent reminder agreement.

- Omar:
  - positives: prayed in mosque, helped younger sibling, strong memorization review, showed patience, respected device limit.
  - slips: late to class, device distraction, rushed writing.
  - one or two stronger negative events for realism, balanced by recovery.

Point scale:

- Positive behavior: +1 to +5 depending on importance.
- Slip: -1 to -2.
- Serious/No Way: -5 only rarely.
- Completed academic tasks: use existing task points, mostly +5 to +10.

## Gift Plan

Create 20 gifts per student. At least 10 should be `redeemed`/claimed. Use `reached_at` and `redeemed_at` dates that show realistic delay:

- Some close: reached then claimed within 2-7 days.
- Some far: reached then claimed after 2-4 weeks.
- Keep one current `pending` gift and the rest `waiting`.
- Treat gifts as agreed rewards between parent and child, not vague surprises.
- Prefer the first 10 claimed gifts to be smaller or medium agreed rewards, and keep higher-tier rewards waiting/pending unless a specific milestone needs one claimed.
- The normal transfer flow pre-seeds 10 `Reward{N}` gifts. The demo command must rename/reuse those seeded rows and extend them to 20 total named gifts, keyed by student/current operating year (`academic_year_id`) and `points_required`, instead of duplicating a second gift queue.
- Use direct guarded writes for demo gift statuses and timestamps so the first 10 gifts can have realistic historical `reached_at` and `redeemed_at` delays. After direct writes, reconcile `reward_totals`, `student_gift_points_history`, and `reward_points_ledger` so the gift board, points lab, and ledger agree.
- Gift mapping: gifts #1-10 are reached/redeemed; gifts #11-20 stay open (`pending`/`waiting`).
- Preferred threshold strategy: keep gifts #1-10 on the normal 100-point rhythm (`100...1000`) and create enough one-year summarized demo point history for each student to exceed the 10th gift threshold.
- Concrete first-pass threshold strategy: keep gifts #1-10 on `100...1000`, set gift #11 to at least `1250`, and keep each student's final signed total around `1040...1090`. This makes 1000+ points realistic while keeping gift #11 unreached.
- To prevent automatic future advancement, set gifts #11-20 thresholds above the final demo total, or keep the final running total strictly below the 11th threshold. Record the chosen threshold/final-total strategy in implementation notes.
- Keep thresholds internally consistent with the final demo total. The final reward totals/history must match the 10 reached/claimed gifts and must not make gift #11 reachable unless the plan is deliberately changed.
- After the final points/gift pass, verify there are exactly 20 named gifts per demo student: 10 redeemed/claimed and 10 open (`pending`/`waiting`) with no extra `Reward{N}` runway rows appended and no gift #11+ marked reached.

Suggested gifts:

Yusuf age 5:

1. Sticker Pack
2. Coloring Book
3. Small Toy Car
4. Bubble Wand
5. Favorite Snack Box
6. Play-Dough Set
7. Story Time Choice
8. Dinosaur Figure
9. Mini Puzzle
10. Park Trip
11. Water Bottle Sticker
12. Extra Bedtime Story
13. Building Blocks Mini Set
14. Animal Flashcards
15. Ice Cream Treat
16. Toy Train
17. Art Markers
18. Small Plush
19. Family Game Night
20. Chosen Toy From Reward List

Maryam age 6:

1. Glitter Sticker Set
2. Coloring Pencils
3. Hair Clips
4. Craft Paper Pack
5. Mini Notebook
6. Bracelet Kit
7. Favorite Dessert
8. Story Book
9. Puzzle Box
10. Park Picnic
11. Paint Set
12. Cute Water Bottle
13. Doll Accessory
14. Family Baking Time
15. Stationery Pouch
16. Clay Craft Kit
17. Islamic Story Book
18. Board Game Choice
19. Outfit Accessory
20. Bookshop Visit

Omar age 9:

1. Football Cards
2. New Notebook
3. Puzzle Challenge
4. Sports Water Bottle
5. Extra Football Time
6. Science Experiment Kit
7. Book Choice
8. Drawing Pens
9. Board Game
10. Pizza Night
11. Football Jersey Item
12. LEGO Mini Set
13. Headphones Time
14. Museum/Activity Trip
15. Strategy Game
16. Model Kit
17. Desk Organizer
18. Larger LEGO Set
19. Sports Day Out
20. New Football

Image plan:

- Do not block demo creation on gift images.
- Use the app default gift image for first pass.
- Owner can later provide images matching these gift names; update `gift_image` paths in a separate small pass.

## Proposed Implementation Phases

1. Create guarded command `toquran:bootstrap-demo-family`.
   - Options:
     - `--confirm-db=u504065335_to_quran`
     - `--dry-run`
   - Use stable family reference `TQDEMO-001`.
   - Create fit-ready demo intake rows, then use the normal app transfer/account flow where practical, so account creation and password behavior follow the existing app code.
   - Use owner-provided real parent email/phone for the parent account.
   - Let the normal transfer/account code create child/student accounts the same way it would for a real family. Do not invent child emails unless the existing app code requires a unique placeholder.
   - Detect existing `TQDEMO-001` rows and verify/skip already transferred children instead of calling transfer again.
   - Implement command-level idempotency keys for tables that do not have useful production unique constraints:
     - booking: stable `TQDEMO-001` marker/reference/notes plus owner email/phone;
     - booking child: booking marker plus child name/age;
     - curated class session: student, teacher-subject-class, subject, date, and deterministic title;
     - session task: class session, deterministic title, sort order, and task type;
     - attachment: session task plus selected General Library resource id/title/type;
     - behavior row: student, teacher-subject-class, behavior title/icon, date, and deterministic demo note/reference.
   - Use lookup-or-update/upsert behavior for those keys; never blind-insert normal sessions, tasks, attachments, or booking scaffold rows on rerun.
   - Verify the default teacher resolver resolves to the intended teacher before transfer.
   - Build each booking child with fit-ready transfer fields matching `BookingChild::isFitReadyForTransfer()` and `BookingTransferReadiness::blockedReason()`.
   - Resolve subjects and grade-level subject mappings by stable code/title first, then verify the expected production IDs, instead of blindly trusting numeric IDs.
   - Verify required `services_types` rows exist and are active for mapped intake interests, especially Arabic Language for Omar.
   - Verify the Beginner grade-level-subject rows needed for the planned subject set are active, especially Arabic Language for Omar.
   - For Omar, either include Arabic Language in the fit-ready service interests before transfer or activate it immediately after transfer; in both cases, deactivate Quranic Arabic and resync teacher-subject-class status.
   - Do not add special demo email behavior. Use normal app account/password behavior only, then confirm with the owner which expected emails were received.
   - Backfill demo gifts/points with direct guarded writes where historical `reached_at`/`redeemed_at` timestamps are required, then run reconciliation checks.
   - Keep all demo reward/gift history in the current operating-year bucket. Implementation must still write the real `academic_year_id` column because the reward tables require it.
   - Reserve deterministic demo ledger source identities before inserting direct point rows, and verify no `(student_id, academic_year_id, source_type, source_id)` conflicts exist.
   - Assign every direct ledger and reward-total row to a real active subject for that child, then reconcile per-subject totals to the aggregate gift points row.
   - Use real automation rows only for current/future daily routines. For backdated Deen Journey / Well Being history, either call the real publisher/snapshot writer with valid source rows or use normal curated session/task rows with clear demo notes.

2. Add tests for the command on the local DB/test DB.
   - refuses wrong DB;
   - dry-run does not write;
   - creates exactly one parent and three students;
   - assigns correct subjects;
   - verifies required `services_types` and grade-level subject rows;
   - verifies/reuses existing beginner-book folder/resources from a fixture or mocked resource list;
   - verifies beginner-book resources use attachable storage disks;
   - creates 20 gifts per student with 10 redeemed;
   - reconciles reward totals, gift history, and ledger after direct backdated gift writes;
   - proves every direct ledger/reward-total row has a non-null active subject and that per-subject totals sum to the aggregate gift points row;
   - proves direct ledger source identities are deterministic and rerunnable without unique-key collisions;
   - proves booking/session/task/attachment idempotency by running the command twice and checking counts do not increase;
   - keeps gift #11+ open after any point writes/reconciliation;
   - skips/verifies an existing `TQDEMO-001` family without re-transferring children;
   - creates tasks and point history.

3. Run locally first.
   - Confirm pages load:
     - admin students/family views;
     - teacher classes;
     - teacher Journey board with student/teacher-subject id;
     - student workplace for each child.

4. Production run only after approval.
   - Confirm backup exists.
   - Confirm DB target and table count.
   - Inspect the owner-uploaded `Tajweed Beginner's Book` Library folder.
   - Verify uploaded file inventory before using Library resources in demo tasks: active resource count must be `84`; summed `file_size` should match `182,688,675` bytes if file sizes are populated.
   - Verify attached beginner-book resources use `storage_disk` values accepted by the snapshotter.
   - Reuse the registered Library resources. If the folder is incomplete, stop and document the missing inventory; do not auto-correct production Library resources without a separate approved correction.
   - Run command with exact approved command.
   - Activate the three children first, then activate the family through the normal Family Workspace/lifecycle flow after command verification.
   - Confirm queue/email processing, then confirm expected activation emails with the owner.
   - Confirm the parent and each child can use the normal activation/password flow from the received emails or generated credentials without adding a custom demo password.
   - Rebuild/clear caches only if needed.

5. Production smoke after demo creation.
   - Superadmin can see family/students.
   - Parent login works through the normal activated account flow.
   - Teacher can see the three students in the correct classes.
   - Each child login/workplace opens through the normal activated account flow.
   - Student PIN-protected reward/task actions accept the transfer-seeded PIN.
   - Quran Memorization subject cards show To Do/In Review counts.
   - Active Quranic Arabic subject card shows for the 5- and 6-year-old only.
   - Active Arabic Language subject card shows for the 9-year-old only.
   - My Deen Journey and Well Being appear for all three.
   - Gift board shows 20 gifts, 10 claimed, one current target.
   - Points lab shows different positive/negative histories.
   - Reward totals, ledger sum, and gift points history reconcile for each student.
   - Gift #11+ remains open after final smoke; no open gift was auto-reached with the smoke timestamp.
   - `Tajweed Beginner's Book` resources open through the normal task attachment/resource surfaces.

## Decisions And Follow-Ups Before Build

- Demo parent email/phone/user name:
  - Owner decided to use `osama.salem0217@gmail.com` and `01091051913` / `+201091051913` so production email behavior can be tested end to end.
  - Parent/student usernames should follow the normal transfer/account flow; do not require a custom `demo_parent_amina` username.
- Student names are decided: `Yusuf Demo`, `Maryam Demo`, and `Omar Demo`.
- Parent/student passwords:
  - No separate demo password design is needed. Use the normal website/intake/transfer/account code behavior; the owner may change the password later if desired.
- Parent email flow:
  - No custom email feature is planned for demo data.
  - Transfer itself does not send activation emails. During smoke, activate the children first and the family second through the normal lifecycle flow, then confirm with the owner whether the expected 4 activation emails arrived at the owner-provided parent email.
- Gift images: owner can provide images later; first pass should use default gift image.

## Stop Conditions

- Stop if DB target is not `u504065335_to_quran`.
- Stop if backup/export evidence is missing before production run.
- Stop if command would create any smoke/admin/support accounts.
- Stop if the uploaded `Tajweed Beginner's Book` folder is missing, unreadable, or clearly does not match the expected file inventory.
- Stop if uploaded resource inventory cannot be verified, or if any required missing-resource correction cannot verify counts and sizes.
- Stop if teacher account or required subject/reference rows are missing.
- Stop if the default teacher resolver does not resolve to the intended teacher.
- Stop if required `services_types` rows for planned intake interests are missing or inactive.
- Stop if Arabic Language's Beginner grade-level-subject row is not active and Omar's override cannot attach.
- Stop if per-child active subjects do not match the planned subject set after transfer and subject override.
- Stop if any beginner-book resource selected for attachment uses an unsupported storage disk.
- Stop if the daily/automation snapshot source rows are missing but the implementation is about to create rows that pretend to be scheduler-generated.
- Stop if demo reward/gift rows cannot be kept within one current operating-year bucket backed by the required `academic_year_id`.
- Stop if any direct reward ledger or reward total row cannot be assigned to a real active subject for that child.
- Stop if deterministic demo ledger source identities cannot be reserved without colliding with existing reward ledger rows.
- Stop if command-level idempotency checks for bookings, sessions, tasks, attachments, or behavior rows cannot identify the existing demo rows on rerun.
- Stop if activation email jobs cannot be processed or resent through existing app behavior.
- Stop if the parent or child accounts cannot complete the normal activation/login flow.
- Stop if reward reconciliation fails after gift/point backfill.
- Stop if gift #11 or later becomes reached/redeemed unexpectedly after point/gift reconciliation or smoke.
- Stop if production route smoke fails after creation.
