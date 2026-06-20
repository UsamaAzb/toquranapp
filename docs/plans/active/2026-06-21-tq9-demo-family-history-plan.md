# TQ9 Demo Family History Plan

Status: draft for owner review; no data written yet
Date: 2026-06-21
Branch: `codex/tq9-launch-readiness`
Scope: one intentional production demo family after core app deployment smoke

## Objective

Create one realistic demo family that can be used to show the To Quran app after launch:

- one parent account;
- three active child/student accounts with different ages;
- real subject enrollment and teacher assignment visibility;
- Quran Memorization, Quranic Arabic, Arabic Language, My Deen Journey, and Well Being history;
- reusable BEGINNERS BOOK Library folder with the local PDFs/videos copied into app storage;
- session/task history that looks like normal To Quran work;
- daily automated-style Salah, Azkar, Deen Journey, and Well Being records;
- points lab behavior history;
- age-appropriate gift queues with at least 20 gifts per student, 10 reached and claimed.

This plan does not authorize production data writes by itself. The implementation must be a guarded command or reviewed manual data artifact, then run only after backup/evidence and exact owner approval.

## Safety Rules

- Do not use the old `toquran:bootstrap-smoke-data` command. It creates multiple smoke users and is not suitable for production demo data.
- Do not create admin/support/smoke accounts.
- Keep existing production launch accounts:
  - owner superadmin;
  - default teacher `drosamaqandil@gmail.com`.
- Create only one demo parent plus three demo students.
- Mark demo rows with stable, searchable names/references such as `TQDEMO-001`, `demo_family`, or `Demo` in notes where the table supports it.
- Use `--confirm-db=u504065335_to_quran` and refuse to run on any other DB.
- Refuse to run if production table count is below the expected launch baseline or the two launch users are missing.
- Make the command idempotent: running it again updates/verifies the same demo family, not duplicates it.
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
   - Deen Journey: all Salahs at home; five Morning Azkar duas and five Evening Azkar duas.
   - Well Being: brushing teeth, shower, school bag/materials ready, tidy learning place.

3. `Omar Demo`, age 9
   - Subjects: Quran Memorization, Arabic Language, My Deen Journey, Well Being.
   - Quran pace: 3 ayahs per session.
   - Arabic Language: age-appropriate reading/writing tasks rather than the beginner Tajweed book.
   - Deen Journey: all Salahs in mosque where suitable; Morning and Evening Azkar.
   - Well Being: independence routines, device limits, respectful sibling help, sleep/readiness.

## BEGINNERS BOOK Library Plan

Source folder to read/copy from local machine:

`D:\confedintial\Q Teaching\My Classes\My Material\2- Tajweed\1- Tajweed Syllabus\Book 1 BEGINNERS BOOK  Pages`

Observed source inventory on 2026-06-21:

- 84 files total;
- 44 PDF files;
- 40 MP4 files;
- 182,688,675 bytes total.

Owner update on 2026-06-21:

- Owner logged in and uploaded the BEGINNERS BOOK files manually before implementation.
- Implementation must inspect the uploaded Hostinger location, verify the uploaded inventory against the expected `84` files / `182,688,675` bytes as closely as the server permits, then import/register those files from the uploaded server location.
- Do not assume the production command can read the local `D:\...` path.
- Owner observed only 20 uploaded resources after selecting the full local folder. This likely matches Hostinger/PHP `max_file_uploads=20`, not a normal complete upload. Before import, verify the real uploaded resource count; if it is 20, upload the remaining files in batches of 20 or temporarily raise `maxFileUploads` in Hostinger PHP options to at least `100`.
- Owner also needs large book/video uploads. The app Library upload cap is intended to be `500 MB` per file; Hostinger PHP `uploadMaxFilesize` and `postMaxSize` must remain at least `500 MB`, and preferably above it for multi-file batches.
- App code update on 2026-06-21 raised `LibraryResourceValidator::MAX_UPLOAD_KB` to `512000` (`500 MB`) and was deployed to Hostinger with Laravel caches refreshed. Production verification reported `512000`.

Create a General Library root/subfolder:

- root or shared folder title: `BEGINNERS BOOK`
- description: `Quranic Arabic beginner book pages and page videos for launch demo lessons.`
- `content_mode=mixed`
- `source_label=Demo`

File grouping plan:

- Store each PDF and MP4 as an active `general_library_resources` file resource.
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

- Copy files into app-managed storage, not public webroot.
- Use the existing General Library file resource schema and existing Library snapshot/attachment logic.
- For session tasks, attach the selected General Library resource snapshot so the student can open the task material through normal protected routes.

## Session History Plan

Create approximately 12 weeks of visible history plus current open work:

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

## Deen Journey And Well Being Daily Plan

Use the TQ7.5 automation catalog where possible; if direct historical generation is safer, create session/task snapshots that look like generated daily tasks and preserve source fields where required.

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

Create behavior history through existing `student_session_discipline`, `reward_points_ledger`, `reward_totals`, and `student_gift_points_history` flow. Prefer calling `RewardProgressionService::applyPointDelta()` from the guarded command so gift progression stays consistent.

Behavior personalities:

- Yusuf:
  - positives: tried again after mistake, said salam politely, listened first time, cleaned toys, practiced Maghrib.
  - slips: forgot task, rushed recitation, needed screen reminder.
  - no serious pattern; keep any negative small and age-appropriate.

- Maryam:
  - positives: completed all Salahs, helped sibling, careful Arabic page practice, honest correction, tidy learning place.
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
20. Big Surprise Toy

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
20. Special Day Out

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
20. Big Eid-Style Reward

Image plan:

- Do not block demo creation on gift images.
- Use the app default gift image for first pass.
- Owner can later provide images matching these gift names; update `gift_image` paths in a separate small pass.

## Proposed Implementation Phases

1. Create guarded command `toquran:bootstrap-demo-family`.
   - Options:
     - `--confirm-db=u504065335_to_quran`
     - `--dry-run`
     - optional `--parent-password=` and/or generated one-time password without printing by default.
   - Use stable family reference `TQDEMO-001`.
   - Use owner-provided real parent email/phone for the parent account.
   - Use safe demo-domain emails/usernames only for the child/student demo accounts.
   - Resolve subjects and grade-level subject mappings by stable code/title first, then verify the expected production IDs, instead of blindly trusting numeric IDs.
   - For parent email behavior, follow the normal app automation used for a real parent registration/activation/password flow. Do not add a special manual-only email flag unless implementation proves the real flow cannot be exercised safely.

2. Add tests for the command on the local DB/test DB.
   - refuses wrong DB;
   - dry-run does not write;
   - creates exactly one parent and three students;
   - assigns correct subjects;
   - creates beginner-book folder/resources from a fixture or mocked file list;
   - creates 20 gifts per student with 10 redeemed;
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
   - Inspect the owner-uploaded BEGINNERS BOOK server location.
   - Verify uploaded file inventory before importing/registering Library resources.
   - Copy/register BEGINNERS BOOK files into app-managed storage as needed.
   - Run command with exact approved command.
   - Rebuild/clear caches only if needed.

5. Production smoke after demo creation.
   - Superadmin can see family/students.
   - Teacher can see the three students in the correct classes.
   - Each student workplace opens.
   - Quran Memorization subject cards show To Do/In Review counts.
   - Quranic Arabic subject exists for the 5- and 6-year-old only.
   - Arabic Language subject exists for the 9-year-old only.
   - My Deen Journey and Well Being appear for all three.
   - Gift board shows 20 gifts, 10 claimed, one current target.
   - Points lab shows different positive/negative histories.
   - BEGINNERS BOOK resources open through protected attachment routes.

## Open Questions Before Build

- Demo parent email/phone/user name:
  - Owner decided to use `osama.salem0217@gmail.com` and `01091051913` / `+201091051913` so production email behavior can be tested end to end.
  - Use a clear demo username such as `demo_parent_amina` unless the app requires email-as-username.
- Student names: keep Yusuf/Maryam/Omar Demo, or choose different names?
- Parent/student passwords: should they share the temporary launch password, or should the command generate and store private one-time credentials for owner handoff?
  - Recommendation: generate separate demo credentials and record only that they were created, not the raw values.
- Parent email flow:
  - Owner expects the same automatic email behavior a real parent would receive after registration/activation/password handling.
  - The implementation should exercise and smoke-test that normal flow using the owner-provided parent email, not invent a separate artificial email button unless required by the code.
- Gift images: owner can provide images later; first pass should use default gift image.

## Stop Conditions

- Stop if DB target is not `u504065335_to_quran`.
- Stop if backup/export evidence is missing before production run.
- Stop if command would create any smoke/admin/support accounts.
- Stop if the uploaded BEGINNERS BOOK server location is missing, unreadable, or clearly does not match the expected file inventory.
- Stop if file copy cannot verify counts and checksums/sizes.
- Stop if teacher account or required subject/reference rows are missing.
- Stop if production route smoke fails after creation.
