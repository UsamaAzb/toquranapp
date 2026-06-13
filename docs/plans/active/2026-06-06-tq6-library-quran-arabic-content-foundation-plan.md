# TQ6 Library And Quran/Arabic Content Foundation Plan

Status: implementation-review; owner manual smoke reports Library is working locally, app code and guarded DB patches added, no production deployment, smoke cleanup, credential rotation, or public website work performed
Date: 2026-06-06
Sprint: TQ6 Library And Quran/Arabic Content Foundation
Branch: `codex/tq6-library-quran-arabic-content-foundation`

## Objective

Create a To Quran-owned app Library foundation for Quran Memorization and Arabic content, with no Week14 legacy Library surfaces in launch-facing workflows. The Library must be active and editable inside the app so admins/superadmins can add Quran Memorization materials such as new surahs, ayah-range/repetition subtitles, and YouTube links. Shared app Library materials are general app materials, visible to all subject teachers, not owned by or locked to one subject. Teachers can create LMS-style folders/resources inside the general Library so all teachers can benefit from them, while edit rights remain owner-aware.

## Owner Decisions Locked

- No Week14 legacy Library here for launch. Inherited Week14 SAT, Grammar, Listen & Read, Friends/TED/Court, English vocabulary, and similar surfaces should be hidden or denied, not preserved as normal launch UI.
- Quran videos live in the private app, not the public website.
- The current Quran/surah/video list is not active in the current app DB tables. It is preserved in `database/manual/backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql` and needs a deliberate TQ6 DB/content path before it becomes editable app content.
- Quran Memorization Library must be editable by admin/superadmin for:
  - New Surah;
  - subtitle / ayah-range repetition video label;
  - YouTube link;
  - ordering/status notes as needed.
- The app needs one general shared Library experience with owner-aware editing:
  - global app Quran content, visible to all subject teachers, not dedicated to one subject;
  - teacher-created folders/resources, visible to all subject teachers so everyone can benefit;
  - admin/superadmin can edit all general Library content, including other admins' and teachers' work;
  - a teacher can edit only the folders/resources they created.
- Quranic Arabic vocabulary games must be inspected before use with Quranic Uthmani/Othmani symbols; do not expose Week14 English vocabulary tooling as launch content.

## Sprint / Roadmap Relationship

- TQ4 already established teacher classes, sessions, normal tasks, student task review, protected attachment behavior, and launch smoke for basic learning flows.
- TQ5 completed the My Deen Journey/rewards/points launch layer.
- TQ6 is the Library/content cleanup and foundation step required before TQ7/TQ7.5 automation catalog work can safely reuse content sources.
- TQ9 deployment readiness remains separate. TQ6 must not perform production deployment, final smoke cleanup, temporary credential rotation, or public website deployment work.

## Required Reading Completed

- `AGENTS.md`
- `docs/WORKFLOW.md`
- `docs/TOQURAN-SPRINTS.md`
- `docs/TOQURAN-LOGIC.md`
- `docs/shared/TERMINOLOGY-AND-SERVICES.md`
- `docs/DB-SAFETY-POLICY.md`
- `database/manual/README.md`
- `docs/shared/*` shared handoff/decision docs
- Week14 Library/task integration docs and code under `D:\xampp\htdocs\week14-app-lms`, including:
  - `specs/2027-library-task-integration/plan.md`
  - `specs/2027-library-task-integration/data-model.md`
  - `specs/2027-library-task-integration/legacy-attachment-allowlist.md`
  - `docs/plans/archive/2026-05-14-library-attachment-journey-stabilization-plan.md`

## Current To Quran Audit Findings

### What Is Useful From Week14

- Modern Library tables/models already exist:
  - `library_sections`
  - `library_resources`
  - `App\Models\LibrarySection`
  - `App\Models\LibraryResource`
- `App\Livewire\Teacher\LibraryManager` already supports folders/resources and link uploads.
- Uploaded files are retained on the public disk but delivered through protected app routes, not raw storage URLs.
- `LibraryResourceAccessService` already enforces teacher ownership/subject access and learner task-assignment access.
- `LibraryResourceAttachmentWriter` and task attachment presenter logic already follow the Week14 snapshot model: assigned work uses `attachment_files`, so later Library edits do not mutate old assigned task attachments.
- `ProtectedLibraryResourceAccessTest` already checks normal session and Journey attachment access for assigned students and linked parents.

### Why Current Week14 Library Shape Is Not Enough For Global Content

- Current Library schema is subject/owner scoped:
  - `library_sections.owner_user_id` is `NOT NULL`.
  - `library_sections.subject_id` is `NOT NULL`.
  - `library_resources.owner_user_id` is `NOT NULL`.
  - `library_resources.subject_id` is `NOT NULL`.
- The existing `LibraryManager` filters access by teacher subjects. That is still useful for teacher-created folders/resources, but it does not satisfy the global Quran Library requirement where all subject teachers should normally see the same app-owned materials.
- A true To Quran launch Library needs a new app-level shared Library contract for global folders/resources. Structured Quran tables can remain preserved technical scaffolding, but the launch-facing Quran Repetition workflow should be folder/resource based.
- The existing model can still inform the teacher folder/resource creation UX and protected attachment/resource delivery, but its subject ownership contract must not drive the general Library experience.

### Launch-Facing Problems

- `routes/web.php` still registers inherited Week14 Library routes:
  - SAT
  - Grammar
  - Listen & Read
  - Level Up
  - Notice & Note
  - Peer Coach
  - TV/video/audio style routes
- `EnsureLegacyLibraryAccess` still allows all `student` and `parent` users through legacy Library routes. That preserved Week14 compatibility, but it is wrong for To Quran launch because these routes are Language and Literature leftovers, not Quran/Arabic content.
- `LibraryController@get_library()` still hardcodes legacy Week14 cards such as `Listen & Read`, `SAT`, `Grammar`, `TED`, `Friends`, and `Background`.
- `teacherToolCards()` can expose `Vocabulary` when a subject title contains `language` or `literature`. In To Quran, `Arabic Language` can accidentally trigger a Week14 English-vocabulary management surface.
- `LibraryPicker` can show `Vocabulary` and `Legacy Library Sources` folders when the Week14 catalog says they are available. These should not be launch-visible.
- Legacy access tests now use the To Quran `toquran.legacy_library_owner_user_ids` config key; inherited Week14 route/code remains accepted technical debt while launch-facing Library UI is routed to the new shared Library.

### Preserved Quran Content Evidence

- The old Quran/surah/video list is preserved separately at:
  - `database/manual/backups/2026-05-28-u504065335_to_quran-quran-video-preservation.sql`
- That artifact preserves `surahs`, `surahs_old`, and `surh_videos`.
- It includes 106 YouTube embed rows, with partial coverage for surah 1 and surahs 96-114.
- Read-only DB check on 2026-06-06 found `surahs`, `surahs_old`, and `surh_videos` are not active tables in the current app DB. `library_sections` and `library_resources` exist but currently have 0 rows.
- The preservation note already warns that future Library migration must validate YouTube links, use `utf8mb4`, add proper indexes/ownership rules, and avoid replaying old schema shapes blindly.

## Quranic Arabic Vocabulary Feasibility

Verdict: current Week14 vocabulary games are not safe to use for Quranic Uthmani/Othmani text as-is.

Reasons:

- `WrongOptionGenerator` is English/Latin specific:
  - missing-letter options use `abcdefghijklmnopqrstuvwxyz`;
  - spelling options use English vowels, consonants, spelling rules, `ctype_alpha`, `strlen`, `substr`, and `levenshtein`;
  - distractor rules such as magic-e, silent letters, `th`, `tion`, and vowel teams are English-only.
- `missing-letter-state.js` masks only `/[A-Za-z]/`, strips non-Latin characters, and fills choices with English vowels.
- `hangman-state.js` uses `ABCDEFGHIJKLMNOPQRSTUVWXYZ` and removes anything outside `A-Z`.
- `spelling-choice-state.js` is the most adaptable, but still title-cases Latin text, generates English fallback distractors, and uses simple lower-case comparison.
- The views/copy/icons are English vocabulary oriented.

Future recommendation:

- Do not expose Week14 vocabulary games in TQ6 launch.
- Add a future Quranic Arabic game spike, probably under TQ8, starting with a curated choice/matching mode rather than missing-letter or hangman.
- Required future work:
  - RTL layout and Quran-capable font;
  - Uthmani-safe normalization rules;
  - grapheme-aware segmentation (`Intl.Segmenter` in JS or PHP intl/grapheme helpers where available);
  - no English fallback distractor generation;
  - curated Arabic/Uthmani distractors;
  - tests using real Quranic marks/symbols.
- Hangman and missing-letter should be redesigned before use with Uthmani text because Arabic marks, joining forms, and Quranic symbols make naive character masking unsafe.

## Implementation Plan

### Owner Manual Review Notes Added 2026-06-07

- Admin/superadmin Library management must be reachable from an admin-side Library tab, not only by knowing the teacher Library URL.
- Library folders need a final-destination / sources-only mode: once a folder contains uploaded/link/YouTube sources for assignment sequencing, it should not accept subfolders. This supports later Series Tasks where a folder can be assigned and the series runs source after source.
- The preserved Quran YouTube list from the old `u504065335_to_quran` export must be turned into editable app Library content instead of remaining only as an inert backup artifact. Local execution evidence now exists in `database/manual/patches/2026-06-07-tq6-library-folder-mode-and-quran-repetition-import-execution-note.sql`.
- Quran repetition content should live as folders: a top folder such as `Quran Repetition`, then one folder per Surah, then video sources inside each Surah folder. Admin/superadmin can still add or edit Surahs/videos as Library folders/sources, but the operational dashboard should feel folder-based, not like a floating `Add Surah` icon above unrelated content.
- The source upload/add popup should be upgraded to the stronger Week14 LMS Library upload experience instead of the current quick/cheap form.
- Opening Library sources should use an in-app viewer/modal style like the LMS where practical, instead of pushing users to a separate plain page.
- Breadcrumbs must be visible in the Library page so admins/teachers always know where they are in the folder tree.
- Owner review after the first folder-based UI pass added these launch polish requirements:
  - Library breadcrumbs belong in the top navbar, not as a second breadcrumb row inside the page.
  - YouTube playback must stop when the viewer closes.
  - Folder/resource cards must stay compact when a page has only one or two items.
  - Source deletion must not break already-assigned student work. Unused sources may be deleted; assigned sources should be archived so existing task snapshots remain available.
  - Edit source/folder modals should look intentional and closer to the LMS Library standard.
  - Source order must be editable through a drag/reorder mode, and that order becomes the order students and future Series Tasks consume.
  - The add-source modal should support saving many files/links/YouTube links in one pass, similar to the LMS popup.
  - The in-app viewer should move toward the LMS full-screen viewer with loading state, previous/next navigation, and basic zoom controls for file/image/PDF viewing.

### Local Implementation Evidence Added 2026-06-07

- Local server recovered after device restart:
  - Laravel app is running at `http://127.0.0.1:8014`;
  - MySQL was restarted locally after rebuilding missing Aria runtime logs;
  - `/login` returned `200`.
- Guarded Library DB patch executed locally against confirmed target `u504065335_to_quran`:
  - backup: `database/manual/backups/2026-06-07-121006-u504065335_to_quran-before-tq6-library-folder-quran-import.sql`;
  - patch: `database/manual/patches/2026-06-07-tq6-library-folder-mode-and-quran-repetition-import.sql`;
  - execution note: `database/manual/patches/2026-06-07-tq6-library-folder-mode-and-quran-repetition-import-execution-note.sql`.
- Guard-failure path was verified by running the same patch against non-target `toquranapp_local`; it refused with `REFUSING TQ6 Library Quran import: wrong target DB.` and left the non-target `content_mode` column count unchanged at `0`.
- DB result:
  - `general_library_folders.content_mode` supports `mixed` and `sources_only`;
  - existing folders that already contained sources were marked `sources_only`;
  - `Quran Repetition` root folder was created as `Original`;
  - 20 surah folders were created under it;
  - 106 preserved Quran YouTube rows were imported as editable `general_library_resources` YouTube sources.
- App result:
  - admin/superadmin Library tab continues to open `admin/library`;
  - operational Quran content is folder-based, not a separate floating Surah dashboard;
  - folders with sources are final source folders and cannot accept subfolders;
  - folders with child folders cannot accept direct sources;
  - breadcrumbs are now passed to the shared top navbar rather than duplicated inside the page body;
  - source creation modal was upgraded to batch files/links/YouTube links in one save;
  - Library sources open in full-screen in-app viewer modals where practical, with loading state, previous/next controls, file zoom controls, and YouTube iframe reset on close;
  - source cards stay compact on sparse pages instead of stretching across the whole row;
  - source reorder mode persists the shared order used by picker/assignment consumers;
  - source deletion deletes only unused sources; already-assigned sources are archived so existing student task snapshots remain available.
- 2026-06-07 owner review follow-up:
  - folder cards must reorder too, not only source cards;
  - the viewer must follow the Week14 full-screen attachment viewer pattern more closely: one stable viewer shell, no footer/download controls, in-place previous/next, spinner, and file zoom controls inside the viewer;
  - Library cards need separate responsive behavior for sparse pages, many-item desktop pages, iPad/tablet, and small phones so cards do not become huge, fail to fill the row, or smush icons/actions.
- Focused verification:
  - `D:\php\php-8.4\php.exe -l app\Http\Controllers\Front\Teacher\GeneralLibraryController.php` passed;
  - `D:\php\php-8.4\php.exe -l app\Models\GeneralLibraryFolder.php` passed;
  - `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LibraryAuthTest.php` passed: 11 tests, 55 assertions;
  - `D:\php\php-8.4\php.exe artisan test tests\Unit\LibraryResourceAttachmentWriterTest.php` passed: 10 tests, 24 assertions;
  - `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LibraryManagerTest.php` passed: 18 tests, 73 assertions.
  - Final focused Library suite passed with `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LibraryAuthTest.php tests\Feature\CoreLms\LibraryManagerTest.php tests\Feature\CoreLms\LegacyLibraryAccessTest.php tests\Unit\LibraryResourceAttachmentWriterTest.php`: 47 tests, 163 assertions.
- 2026-06-07 owner review follow-up implementation:
  - `teacher.general-library.items.reorder` now persists mixed folder/source page order, with regression coverage for folder ordering;
  - Library cards use responsive sparse/many/mobile grid rules and fixed icon/action layout;
  - Library sources now open in one stable full-screen viewer shell with in-place previous/next navigation, iframe reset, spinner, and file zoom controls; the footer/download button was removed from the viewer.
  - Final focused Library suite passed with `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LibraryAuthTest.php tests\Feature\CoreLms\LibraryManagerTest.php tests\Feature\CoreLms\LegacyLibraryAccessTest.php tests\Unit\LibraryResourceAttachmentWriterTest.php`: 48 tests, 165 assertions.

### Phase 1: Remove Week14 Legacy Library From Launch Surfaces

Goal: no launch-facing Week14 Library clutter.

Actions:

- Hide hardcoded legacy cards from normal To Quran teachers/admins.
- Deny or remove student/parent access to inherited legacy Library routes by changing the current `EnsureLegacyLibraryAccess` student/parent pass-through behavior.
- Do not keep owner-only legacy route browsing as part of launch UX.
- Update existing legacy access tests that currently expect student/parent access, including `LegacyLibraryAccessTest` / `LibraryAuthTest` style coverage.
- Update tests so they assert To Quran `toquran.*` config keys if any compatibility guard remains, not stale `week14.*` keys.
- Keep route files/code only as accepted technical debt if removing them causes too much churn; the UI and access contract must be closed.

### Phase 2: Build The General To Quran App Library Model

Goal: support a general shared Library visible to all subject teachers, with admin/superadmin edit-all permissions and teacher edit-own permissions.

Actions:

- Create new app-level Library tables for general folders/resources created by admins/superadmins or teachers.
- Do not adapt `library_sections` / `library_resources` into fake global content for launch.
- Avoid a fake subject workaround because it would make shared app materials look like they belong to one subject.
- Add a new general Library authorization path instead of bending the existing subject-coupled `LibraryResourceAccessService`:
  - active teachers can view/use active general Library content;
  - teachers can edit only content they created;
  - admin/superadmin can edit all general Library content;
  - no subject access requirement.
- Add protected serving for uploaded files stored in the new general Library tables before assignment. Assigned task snapshots still use existing `attachment_files` protected behavior.
- Keep protected attachment delivery and task snapshot behavior from Week14 where it already fits assigned work.
- Reuse the existing LMS-style teacher folder/resource creation experience where practical, but point it at the general shared Library contract instead of a subject-locked private Library.
- Enforce edit rules:
  - admin/superadmin can edit all general Library content;
  - teacher can edit only content they created;
  - all subject teachers can view/use active general Library content.
- Keep DB changes under `database/manual/` with target guards and backup evidence.

Recommended launch direction:

- Use folder-based app content for Quran Memorization:
  - `Quran Repetition` as an `Original` starter folder;
  - one source-only folder per Surah;
  - YouTube sources for full-surah or ayah-range repetition videos;
  - source title/description, sort order, active/archived status, and `Original` marker where useful.
- Do not show teacher-facing `created by` badges for folders/resources; creator/edit history can be admin-only if audit display is needed.
- Surface this content through a general Library area visible to all subject teachers.
- Surface teacher-created folders/resources in the same general Library so all teachers can benefit from active shared sources.
- When a teacher attaches a Quran Repetition YouTube source to a task, snapshot it into `attachment_files` as a normal task attachment:
  - `type = youtube`;
  - `path = trusted YouTube embed URL`;
  - `title` / `description` copied from the Library item at assignment time.
- Use the general Library attachment adapter for the launch path. Preserved structured Quran rows are not launch-facing attachment selections.
- Uploaded files from teacher-created general Library resources continue to use protected app routes. YouTube videos are external embeds and are assignment-scoped in the app, not protected-file downloads.

### Phase 3: Build Editable Quran Memorization Library

Goal: admin/superadmin can maintain Quran repetition videos in the app through editable Library folders/sources.

Actions:

- Add an admin/superadmin-editable Quran Memorization Library surface using the shared Library:
  - add/edit Surah folders under `Quran Repetition`;
  - add/edit full-surah or ayah-range YouTube sources inside each Surah folder;
  - reorder folders and sources;
  - archive/delete according to assignment safety rules.
- Validate YouTube URLs and normalize watch/share/embed forms for reliable viewing.
- Keep videos app-side; no public website implementation.
- Use the preserved `surahs` / `surh_videos` SQL only through guarded import/replay notes. The owner-approved local launch import creates `Quran Repetition` folders/resources from the preserved 106-row extract so the content is editable in the shared Library.

### Phase 4: Teacher General Library Access

Goal: all subject teachers can see app Library materials and can create general Library folders/resources for everyone to benefit from.

Actions:

- Create teacher-facing Library view for global materials.
- Do not require a material to belong to the teacher's current subject.
- Preserve teacher-created Library folders/resources like the LMS, but place them in the general shared Library.
- Let teachers edit only the folders/resources they created.
- Let admin/superadmin edit all Library folders/resources.
- Keep task authoring/picker access simple:
  - teacher can choose from global app Library materials;
  - teacher can choose from teacher-created general Library materials;
  - assigned attachments still snapshot into the task;
  - student/parent access remains assignment-scoped and protected.
- Keep attachment behavior explicit:
  - Quran Repetition YouTube Library sources render for assigned student/parent tasks using the saved embed URL snapshot;
  - uploaded files from teacher-created general Library resources are served through protected app routes.
- Smoke across Quran Memorization, Quranic Arabic, Arabic Language, Sanad Program, and My Deen Journey teachers.

### Phase 5: Disable Week14 Vocabulary From Launch

Goal: avoid exposing English vocabulary tooling through `Arabic Language`.

Actions:

- Hide `teacher.library.vocabulary` from the teacher Library page by default.
- Prevent `LibraryPicker` from showing Week14 `Vocabulary` folders by default.
- Keep underlying vocabulary code as deferred architecture only.
- Add tests that an Arabic Language teacher does not see the Week14 vocabulary tool in the Library by default.
- Record Quranic Arabic game spike as a future TQ8 item.

### Phase 6: Documentation And Smoke Contract

Actions:

- Update `docs/TOQURAN-SPRINTS.md` when TQ6 implementation starts/finishes.
- If app and website content ownership decisions affect both repos, record them in `docs/shared/`.
- Keep TQ9 launch gate aware that inherited Language and Literature Library sources must be hidden/removed from launch-facing surfaces.
- Smoke after implementation:
  - admin can add/edit a Quran Repetition folder/source item;
  - teacher from any subject can see global Library materials;
  - teacher can attach a global Quran video to a normal task;
  - student opens the assigned Quran video from the task/Journey viewer;
  - parent opens the assigned Quran video from the task/Journey viewer;
  - student opens an assigned uploaded teacher Library file through a protected route;
  - parent opens an assigned uploaded teacher Library file through a protected route;
  - student/parent direct old Library routes are denied or unavailable;
  - Arabic Language teacher does not see English Vocabulary launch tooling.

## Database Impact

TQ6 requires DB work for new general app Library tables. Current `library_sections` and `library_resources` remain inherited teacher-owned/subject-scoped tables and are not adapted into the global content model during launch.

Planned DB path:

- New general Library folder/resource tables or equivalent app-level structures that support shared visibility and owner-aware editing.
- Preserved structured Quran Library tables may exist as technical scaffolding, but launch-facing Quran Repetition content is stored as general Library folders/resources.
- Existing `library_sections` / `library_resources` remain inherited tables unless a later migration deliberately maps them into the general Library model.
- Quran Repetition YouTube resources attach to tasks by writing `attachment_files` snapshots with `type = youtube`.
- Uploaded general Library files need a protected pre-assignment serve route/controller for teacher preview/download. After assignment, uploaded files continue through `attachment_files` protected access.

Executed local DB path on 2026-06-06:

- Confirmed app DB target from `.env`: `u504065335_to_quran`.
- Created focused structure-only backup: `database/manual/backups/2026-06-06-231654-u504065335_to_quran-before-tq6-general-library-structure.sql`.
- Executed guarded structure patch: `database/manual/patches/2026-06-06-create-tq6-general-library.sql`.
- Execution evidence: `database/manual/patches/2026-06-06-create-tq6-general-library-execution-note.sql`.
- Created active shared Library tables: `general_library_folders` and `general_library_resources`. Empty preserved `quran_library_surahs` and `quran_library_videos` tables also exist as non-launch-facing technical scaffolding from the first structure pass.

## Implementation Evidence

Local implementation currently includes:

- `teacher/library` now uses the To Quran shared Library controller instead of the inherited Week14 card grid.
- Admin/superadmin can reach the same Library manager from an admin sidebar tab at `admin/library`.
- General Library folders/resources are visible to active teachers across subjects.
- Teachers can create general Library folders/resources and edit/archive only their own.
- Admin/superadmin can edit/archive all general Library folders/resources.
- Admin/superadmin can add/edit Quran Repetition Surah folders and YouTube source rows with trusted YouTube embed normalization.
- Teacher pre-assignment uploaded-file preview/download uses a protected app route for the new general Library resources.
- Normal task attachment writing supports new shared Library picker IDs as `general__{id}`.
- Quran Repetition YouTube sources snapshot into `attachment_files` as `type = youtube` with the trusted embed URL.
- Week14 Vocabulary and Legacy Library Sources are hidden from the launch-facing Library picker root by default.
- Student/parent direct inherited legacy Library access is denied by `EnsureLegacyLibraryAccess`.

Verification run on 2026-06-06 with `D:\php\php-8.4\php.exe`:

- `tests/Feature/CoreLms/LibraryManagerTest.php`
- `tests/Feature/CoreLms/LibraryAuthTest.php`
- `tests/Feature/CoreLms/LegacyLibraryAccessTest.php`
- `tests/Unit/LibraryResourceAttachmentWriterTest.php`
- `tests/Feature/CoreLms/TeacherAttachmentStateTest.php`
- Result: 73 passed, 243 assertions.

Viewer/upload hardening added on 2026-06-07 and tightened on 2026-06-11:

- General Library upload limit reduced from inherited 500 MB to 50 MB per file.
- General Library source uploads now expose only launch-previewable file types: PDF, image, audio, and video formats supported by the app viewer.
- Source upload modal now has a scrollable body, static backdrop, reset-on-close behavior, calmer panel styling, supported-file validation, and visible upload/save progress.
- File selection now stages files through a temporary protected upload endpoint before Save; unsupported or oversized batches are blocked with an inline message before any source is created.
- Full-screen Library source viewer now loads iframe content after the modal is visible, preventing intermittent blank YouTube/PDF opens.
- Viewer differentiates YouTube, PDF, image, media, and link sources; images render through a fit-to-screen image element, and image zoom controls are only shown for image sources, not PDFs.

Verification run on 2026-06-11 with `D:\php\php-8.4\php.exe`:

- `tests/Feature/CoreLms/LibraryAuthTest.php`
- `tests/Feature/CoreLms/LibraryManagerTest.php`
- `tests/Feature/CoreLms/LegacyLibraryAccessTest.php`
- `tests/Unit/LibraryResourceFoundationTest.php`
- `tests/Unit/LibraryResourceAttachmentWriterTest.php`
- Result: 57 passed, 192 assertions.

TQ6 hardening added on 2026-06-13 after review:

- The malformed double-slash YouTube embed values from the guarded Quran Repetition import were corrected in the replay patch and in the current local DB:
  - backup: `database/manual/backups/2026-06-13-155055-u504065335_to_quran-before-tq6-library-hardening.sql`;
  - repair patch: `database/manual/patches/2026-06-13-tq6-library-hardening-repair.sql`;
  - current DB verification found `0` `general_library_resources.external_url` values matching `%/embed//%`.
- General Library uploaded source originals now live on the private `local` disk. The protected Library file route can still serve local originals and legacy public originals.
- Normal task assignment copies an uploaded general Library file into a separate `attachment_files` snapshot path on the public task-attachment disk, so later Library edits/deletes do not remove already-assigned student work.
- Deleting a Library file source now archives it when any direct or copied attachment snapshot exists; unused file sources can be deleted and their private original is removed.
- The launch-facing structured Quran routes and picker selections were removed. Preserved `quran_library_surahs` / `quran_library_videos` models/tables are technical scaffolding only; operational Quran Repetition content is edited through shared Library folders/resources.
- Source creation is restricted to final/source-only folders. The shared Library root and folders with child folders cannot receive direct sources.
- The upload modal keeps staged uploads on the private `local` disk, deletes staged files when the modal is cancelled, blocks unsupported/oversized files before Save, and uses a scroll-contained mobile layout.
- The stale temp-upload cleanup command now scans the private `local` disk, matching the hardened staging location.

Verification run on 2026-06-13 with `D:\php\php-8.4\php.exe`:

- `tests/Feature/CoreLms/LibraryAuthTest.php`
- `tests/Feature/CoreLms/LibraryCompatibilityTest.php`
- `tests/Unit/LibraryResourceAttachmentWriterTest.php`
- `tests/Feature/CoreLms/LibraryManagerTest.php`
- `tests/Feature/CoreLms/LegacyLibraryAccessTest.php`
- `tests/Unit/LibraryResourceFoundationTest.php`
- Result: 58 passed, 229 assertions.

Final owner/manual and focused-review closure on 2026-06-13:

- Owner manual smoke reported the shared Library experience is working.
- Folder create double-submit was guarded with a submit-once form handler and server-side duplicate title protection.
- Folder edit now rejects duplicate sibling titles.
- Teacher-owned folder cards expose delete actions; non-empty or historical folders archive instead of hard-deleting, so archived Library history is not orphaned.
- Source and edit modal close buttons were normalized so the X sits inside the modal header in its resting state.
- Source upload modal no longer shows the supported-type chip strip; server/client validation still enforces supported previewable files.
- Focused verification passed with `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms\LibraryAuthTest.php tests\Feature\CoreLms\LibraryCompatibilityTest.php tests\Unit\LibraryResourceAttachmentWriterTest.php tests\Feature\CoreLms\LibraryManagerTest.php tests\Feature\CoreLms\LegacyLibraryAccessTest.php tests\Unit\LibraryResourceFoundationTest.php`: 61 passed, 242 assertions.
- `D:\php\php-8.4\php.exe -l app\Http\Controllers\Front\Teacher\GeneralLibraryController.php` passed.
- Local `/login` returned `200` with title `To Quran | Login`.

Future TQ7 note:

- Differentiated/daily-session/series task attachment surfaces should reuse the same Quran/general Library adapter pattern when TQ7/TQ7.5 automation work attaches Library content outside normal session tasks.

Any DB work must follow `docs/DB-SAFETY-POLICY.md` and `database/manual/README.md`:

- confirmed target DB;
- backup/export evidence;
- guarded SQL/manual notes;
- no accidental public website DB target;
- no destructive deletion without a documented cleanup plan.

## Non-Goals

- No public website implementation in TQ6 unless explicitly reopened.
- No production deployment.
- No final smoke cleanup or credential rotation.
- No English vocabulary content import.
- No launch use of Week14 vocabulary games.
- No broad class-management, finance, or automated scheduling work.
- No destructive legacy Library row deletion in this sprint.

## Completion Definition

TQ6 is complete when:

- launch-facing Library surfaces no longer show inherited Week14 Language and Literature cards by default;
- student/parent direct access to inherited Week14 legacy Library routes is closed or unavailable;
- Arabic Language does not expose Week14 English Vocabulary tooling by accident;
- Quran Memorization Library is active and admin/superadmin-editable inside the app;
- all subject teachers can see general Library materials without subject-specific ownership;
- teachers can create folders/resources in the general Library for all teachers to use;
- teacher-created content is edit-own for teachers and edit-all for admin/superadmin;
- teacher can attach Library materials to tasks;
- Quran Repetition YouTube source attachments render from assigned tasks/Journey;
- uploaded-file task attachments from teacher-created general Library resources remain protected for students/parents;
- Quranic Arabic vocabulary feasibility is documented as deferred/spike work;
- tests and smoke evidence cover the cleanup boundaries.
