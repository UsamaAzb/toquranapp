# TQ7/TQ7.5 Automation Tracks And Starter Catalog Plan

Status: TQ7 implemented and review-ready; TQ7.5 pending; no DB execution performed
Date: 2026-06-14
Branch: `codex/tq7-automation-tracks-routines-series`
Sprint: TQ7 Automation Tracks For Routines, Differentiated Tasks, And Series Tasks; TQ7.5 Prebuilt Routine And Series Task Launch Catalog

## Objective

Audit the current To Quran automation surfaces against Week14, then define the concrete implementation plan for launch-safe Versioned Routines, Differentiated Tasks, Series Tasks, and a reproducible starter catalog.

The implementation goal is not to rebuild Week14 automation. The Week14 engines are already present and should stay mostly intact. TQ7 should adapt the content-source boundary, terminology, launch visibility, tests, and starter-data path so the automation tracks are safe for Quran Memorization, Quranic Arabic, Arabic Language, Sanad Program, My Deen Journey, and Well Being launch use.

Implementation invariant: preserve the existing Week14 automation rules and generation logic unless a specific regression is found. TQ7 must keep inactive/student-visibility blocking, the 7-day catch-up window, Series `stop_at_end` and `loop` behavior, and Series `continuous` versus `wait_for_completion` release policy behavior. The sprint changes the launch content sources and To Quran wording, not those scheduling/progression rules.

## Required Reading Completed

- `AGENTS.md`
- `docs/WORKFLOW.md`
- `docs/TOQURAN-SPRINTS.md`
- `docs/TOQURAN-LOGIC.md`
- `docs/shared/TERMINOLOGY-AND-SERVICES.md`
- `docs/DB-SAFETY-POLICY.md`
- `database/manual/README.md`
- `docs/plans/active/2026-06-06-tq6-library-quran-arabic-content-foundation-plan.md`
- Week14 automation docs:
  - `D:\xampp\htdocs\week14-app-lms\automated_tasks_logic_notes.md`
  - `D:\xampp\htdocs\week14-app-lms\docs\AUTOMATION-DIFFERENTIATED-TASKS-NOTES.md`
  - `D:\xampp\htdocs\week14-app-lms\docs\plans\archive\2026-05-03-series-tasks.md`

## TQ7 Implementation Checkpoint

Date: 2026-06-15

TQ7 is implemented and review-ready as a code-only unit. No production deployment, database migration, seeder, manual SQL execution, or catalog data installation was run.

Implemented:

- Centralized General Library attachment snapshotting in `app/Services/Library/GeneralLibraryAttachmentSnapshotter.php`.
- Routed normal task, Versioned Routine, Differentiated Task, and Series Task General Library attachments through the shared snapshotter.
- Removed launch-facing `series__...` legacy resolution from the shared Versioned Routine / Differentiated Task attachment trait.
- Added `general_library_folder` and `general_library_resource` Series source handling backed by TQ6 General Library folders/resources.
- Changed Series authoring, validation, and generation to use launch-only General Library source selection.
- Kept retained legacy resolver methods as hidden compatibility scaffolding, while blocking legacy source selection/publish/generation for launch.
- Added fail-closed generation behavior for pre-existing legacy Series rows and for General Library folders/resources that become invalid after publish.
- Preserved Week14 automation scheduling and progression behavior: inactive/student-visibility blocking, 7-day catch-up behavior, `stop_at_end`, `loop`, `continuous`, and `wait_for_completion`.
- Confirmed the operational behavior that if the acting automation user / creator can no longer use a General Library source, generation pauses fail-safe instead of posting work.

Review status:

- Internal review found no remaining blocking issues after the archived-folder generation gate fix.
- Claude review verdict: launch-safe and well-tested, with no blocking issues.
- Claude non-blocking hardening items were addressed:
  - Differentiated Task tests now prove a task-level attachment pool row alone does not deliver until a version-selection row exists.
  - Series resolver tests now pin `allCollections()` to launch-facing `general_library_folder` collections only.

Verification:

- `git diff --check`: clean.
- `D:\php\php-8.4\php.exe artisan test tests\Feature\CoreLms tests\Unit\LibraryResourceAttachmentWriterTest.php`
- Result: 283 passed, 1482 assertions.

TQ7.5 remains separate and pending. Do not start catalog registry SQL, installer commands, or starter data execution until backup/export evidence, target confirmation, and guarded manual notes are prepared.

## Roadmap Relationship

TQ7 depends on TQ4 and TQ6. TQ4 verified normal sessions/tasks and learner delivery. TQ6 created the launch-facing shared General Library with Quran Repetition content and explicitly notes that differentiated/daily-session/series automation attachment surfaces should reuse the same General Library adapter pattern.

TQ7.5 is a deployment-readiness blocker in TQ9: final launch readiness is not complete until a prebuilt routine/series catalog exists, is reproducible, and can be assigned by teachers.

## Current To Quran Audit

### Present Automation Surfaces

The To Quran app already has the Week14 automation stack imported:

- Teacher routes are registered for all three tracks in `routes/web.php`:
  - `teacher/daily-sessions/...` for Versioned Routines
  - `teacher/differentiated-tasks/...`
  - `teacher/series-tasks/...`
- The teacher sidebar already exposes Automation with Versioned Routines, Differentiated Tasks, and Series Tasks at `resources/views/layouts/sections/menu/verticalMenu.blade.php:187`.
- The learner pages lazily generate due automation through the existing student subject/workplace flow, not through student Automation navigation.
- The schema baseline already includes Versioned Routine, Differentiated Task, and Series Task tables and learner snapshot traceability columns.
- Existing focused tests cover recurrence, assignment, authoring, route authorization, snapshot generation, and learner visibility.

### Week14 Code Reuse Status

File comparison against `D:\xampp\htdocs\week14-app-lms` shows:

- Same as Week14:
  - `app/Livewire/Teacher/SeriesTasksBoard.php`
  - `app/Services/Library/ResolvesLibraryAttachmentAttributes.php`
  - `app/Services/AutomatedTaskSnapshotWriter.php`
  - `app/Services/DifferentiatedTaskSnapshotWriter.php`
- To Quran-adapted:
  - `app/Livewire/Teacher/LibraryPicker.php`
  - `app/Services/Library/LibraryResourceAttachmentWriter.php`
  - `app/Services/SeriesLibrarySourceResolver.php`
- To Quran-only:
  - `app/Services/Library/GeneralLibraryAttachmentAdapter.php`

Conclusion: keep the proven Week14 automation engines, but adapt the Library/source bridge and launch catalog.

### TQ6 General Library Integration Status

Normal teacher session tasks already use the TQ6 adapter:

- `LibraryResourceAttachmentWriter` recognizes `general__{id}` selections.
- General Library file originals on the private/local disk are copied into independent public `attachment_files` snapshots for assigned normal tasks.
- General Library YouTube links are normalized to trusted embed URLs.

General Library authorization is not the old Week14 subject/private-owner model:

- TQ6 made `general_library_folders` / `general_library_resources` shared app Library content. Active teachers, admins, and superadmins can view/use active resources across subjects.
- `created_by_user_id` remains for edit ownership and audit. Teachers can edit/archive only their own shared Library rows, while admins/superadmins can edit/archive all shared Library rows.
- Automation code should pass the acting automation user, usually the task creator, when checking whether a General Library source can be used. That user id is an authorization context, not a subject-scoped Library ownership boundary.

Versioned Routines and Differentiated Tasks do not yet use the full TQ6 adapter:

- `LibraryPicker` can display General Library folders/resources and can emit `general__{id}` selections.
- `AutomatedTaskMainTaskModal` accepts selected Library IDs and calls `LibraryToVersionedRoutineAttachmentWriter` at `app/Livewire/Teacher/AutomatedTaskMainTaskModal.php:641`.
- `DifferentiatedTasksBoard` accepts selected Library IDs and calls `LibraryToDifferentiatedAttachmentWriter` at `app/Livewire/Teacher/DifferentiatedTasksBoard.php:143`.
- Both writers use `ResolvesLibraryAttachmentAttributes`, which currently accepts only numeric subject-scoped `library_resources` and `series__...` legacy catalog IDs. It returns `null` for `general__...` IDs at `app/Services/Library/ResolvesLibraryAttachmentAttributes.php:23`.

Launch risk: teachers can choose TQ6 Quran Repetition content from the shared Library picker for Versioned Routines or Differentiated Tasks, but current writer code can silently skip those selections.

### Series Task Source Status

Series Tasks currently still use `SeriesLibrarySourceResolver`.

Useful pieces:

- `series_tasks.library_collection_type` and `series_task_version_items.library_source_type` are `varchar(64)`, not enum-locked, in the current To Quran baseline. Adding a General Library source type should not require a schema change.
- Series generation already snapshots immutable learner rows through `class_sessions`, `session_tasks`, `session_task_student`, and `attachment_files`.
- Series source selection supports folder browsing and ordered items.

Launch problems:

- The Series board still has visible source labels for Week14 content families such as SAT, Literature, TV Series, Level Up, Audio Level, Peer Coach, Grammar, Notice & Note, and Vocabulary.
- `SeriesLibrarySourceResolver` can still return legacy Week14 source families.
- Week14's latest resolver had a Language and Literature gate, but the To Quran copy removed that guard. Separately, To Quran compatibility constants map `SUBJECT_LANGUAGE_AND_LITERATURE` to Quran Memorization, which can accidentally treat Quran Memorization as eligible for old Language and Literature sources.
- Series currently uses old subject-scoped `library_sections` / `library_resources` as its "new Library folder" source. TQ6 made launch-facing content live in `general_library_folders` / `general_library_resources`, so Series needs a first-class General Library source adapter.

### Test Status And Gaps

Existing tests are valuable, but many still prove Week14 behavior rather than To Quran launch behavior:

- `SeriesTaskGenerationTest` uses SAT sources and old `library_sections`.
- `AutomatedTaskAuthoringTest` and `DifferentiatedTaskSnapshotWriterTest` still include vocabulary-game attachment paths. Those paths should not become launch-visible English vocabulary assignments during TQ7, but the underlying Week14 vocabulary game code/design may remain as deferred TQ8 architecture.
- TQ6 tests cover normal-task General Library snapshots, but there are no equivalent tests for:
  - Versioned Routine `general__...` selection.
  - Differentiated Task `general__...` selection.
  - Series Task collection selection from `general_library_folders`.
  - Series Task generation from `general_library_resources`.
  - Legacy Week14 source families hidden/blocked from launch-facing automation.

## Week14 Reuse Source Files

Reuse mostly as-is:

- `app/Services/AutomatedTaskRecurrenceService.php`
- `app/Services/AutomatedTaskAssignmentService.php`
- `app/Services/AutomatedTaskSubscriptionService.php`
- `app/Services/AutomatedTaskPublishValidator.php`
- `app/Services/AutomatedTaskSnapshotWriter.php`
- `app/Services/DifferentiatedTaskRecurrenceService.php`
- `app/Services/DifferentiatedTaskAssignmentService.php`
- `app/Services/DifferentiatedTaskPublishValidator.php`
- `app/Services/DifferentiatedTaskPublisher.php`
- `app/Services/DifferentiatedTaskSnapshotWriter.php`
- `app/Services/SeriesTaskRecurrenceService.php`
- `app/Services/SeriesTaskAssignmentService.php`
- `app/Services/SeriesTaskPublishValidator.php`
- `app/Services/SeriesTaskPublisher.php`
- `app/Services/SeriesTaskSnapshotWriter.php`
- `app/Livewire/Teacher/AutomatedTasksBoard.php`
- `app/Livewire/Teacher/DifferentiatedTasksBoard.php`
- `app/Livewire/Teacher/SeriesTasksBoard.php`
- `app/Livewire/Teacher/*AssignmentModal.php`
- Existing automation tests as regression scaffolding.

Adapt:

- `app/Services/Library/ResolvesLibraryAttachmentAttributes.php`
- `app/Services/Library/LibraryToVersionedRoutineAttachmentWriter.php`
- `app/Services/Library/LibraryToDifferentiatedAttachmentWriter.php`
- `app/Services/SeriesLibrarySourceResolver.php`
- `app/Services/SeriesTaskSnapshotWriter.php`
- `app/Livewire/Teacher/SeriesTasksBoard.php`
- automation tests that currently depend on SAT, vocabulary, or old subject-scoped Library rows.

Skip or hide for launch:

- Week14 SAT, Listen & Read, TV/Friends/Avatar, Level Up, Grammar, Peer Coach, Notice & Note, Background, Audio, and English vocabulary content families.
- Week14 English vocabulary content and launch-facing vocabulary game assignments. Do not delete the Week14 game architecture/designs just because they are hidden from launch; TQ8 may reuse the game state machine, scoring, audio UI, option buttons, feedback animations, levels/categories, and attempt-saving patterns for Quranic Arabic games.

## To Quran-Specific Implementation Plan

### Phase TQ7-1: Close Legacy Automation Source Leaks

Goal: automation authoring should not expose or depend on Week14 Language and Literature content for launch.

Actions:

1. Hide legacy Series source groups from launch-facing Series Task source selection.
2. Prevent `SeriesLibrarySourceResolver::allCollections()` and `sourceIsSelectable()` from treating SAT, story, Level Up, TV Series, audio, peer coach, grammar, notice-note, background, or vocabulary as selectable launch sources by default.
3. Reject or feature-gate `series__...` legacy attachment IDs server-side in Versioned Routine and Differentiated Task authoring. Hiding legacy picker UI is not enough because `ResolvesLibraryAttachmentAttributes` can still resolve `series__...` IDs today.
4. Keep old source resolver methods only as technical compatibility if tests or hidden legacy paths still need them. Any retained compatibility must be unreachable from launch-facing teacher automation flows by default.
5. Handle any pre-existing automation rows that already reference legacy Week14 sources before launch:
   - do not silently generate SAT, vocabulary, TV/audio/story, or other legacy-source learner work from those rows;
   - publish validation and snapshot generation should fail closed with a clear unavailable-source message unless an explicit hidden compatibility flag is added later;
   - learner lazy-generation flows must handle these blocked rows gracefully: skip the invalid automation row, continue rendering other valid subject/session work, and avoid throwing an exception that breaks the student page;
   - record enough context for teacher/admin follow-up, such as automation id, source type/id, student id where applicable, and the unavailable-source reason, without exposing legacy content to the learner;
   - do not delete or rewrite existing legacy rows as part of TQ7 without a separate documented manual cleanup plan.
6. Update tests so To Quran launch behavior asserts old Week14 sources are absent or blocked across Series Tasks, Versioned Routines, and Differentiated Tasks, including pre-existing legacy-source rows.
7. Keep TQ8 as the Arabic vocabulary-games follow-up; do not expose English vocabulary game paths as automation starter material. Preserve reusable Week14 vocabulary game architecture unless it directly leaks English/Latin content into launch-facing automation.

### Phase TQ7-2: Extend The TQ6 General Library Adapter To Versioned And Differentiated Tasks

Goal: Versioned Routines and Differentiated Tasks can attach Quran/Arabic shared Library content exactly as normal tasks do.

Actions:

1. Extract the existing General Library snapshot logic from `LibraryResourceAttachmentWriter` into one shared service and route normal tasks, Versioned Routines, Differentiated Tasks, and Series Tasks through it. Do not create a third divergent implementation of file-copy, trusted YouTube normalization, link snapshotting, or authorization behavior.
2. Pass the acting automation user id into Versioned Routine and Differentiated Task General Library resolution. Usually this is the task creator / editing teacher. The resolver must apply the same TQ6 shared Library authorization rules used by normal tasks: active teachers, admins, and superadmins may use active shared resources, while inactive or unauthorized users cannot bind them.
3. For General Library files, copy the private source file into an independent automation/task attachment path before storing it in:
   - `main_daily_session_main_task_attachments`, for Versioned Routine source attachments.
   - `differentiated_task_attachments`, for Differentiated Task source attachments.
   The copied path must be durable and public/protected in the same way generated learner attachments expect; it must not point back to the editable private General Library original.
4. For Differentiated Tasks, creating a `differentiated_task_attachments` pool row is not enough. The runtime authoring path and any starter installer must also create the appropriate `differentiated_task_version_attachments` selection rows with stable ordering, because `DifferentiatedTaskSnapshotWriter` delivers only `$version->selectedAttachments`.
5. For General Library YouTube resources, snapshot the trusted embed URL as `type = youtube`.
6. For General Library link resources, snapshot the external URL as `type = link`.
7. Keep assigned/generated student snapshots immutable. Later Library edits/deletes must not mutate delivered work. For Versioned Routines, `AutomatedTaskSnapshotWriter` currently metadata-copies the source attachment path into generated `attachment_files`, so the source attachment itself must already be an independent durable copy before generation. For Differentiated Tasks, `DifferentiatedTaskSnapshotWriter` re-copies file attachments at generation time, but it reads the stored source attachment path; therefore that stored path must also be an independent durable copy rather than the editable private General Library original.
8. Extend the shared automation test schema/fixtures before adding tests so `general_library_folders` and `general_library_resources` exist consistently across Versioned Routine, Differentiated Task, and Series Task suites.
9. Add tests for Versioned Routine and Differentiated Task `general__...` attachment selection, Differentiated Task version-selection rows, ordering, acting-user authorization, unauthorized/inactive-user rejection, YouTube normalization, file-copy behavior, and generated student snapshot access.

### Phase TQ7-3: Add A General Library Source Adapter For Series Tasks

Goal: Series Tasks consume TQ6 shared Library source-only folders and resources, not old Week14 content families.

Actions:

1. Add a new resolver collection type, for example `general_library_folder`.
2. Treat active `general_library_folders` as browseable collections:
   - folders with active child folders are navigable;
   - source-only folders with active resources are selectable;
   - empty folders are blocked with a clear reason.
3. Treat active `general_library_resources` as ordered Series items:
   - source type `general_library_resource`;
   - title, description, item URL/path/type from `GeneralLibraryAttachmentAdapter`;
   - file resources must be copied into independent generated `attachment_files` snapshots when delivered.
4. Update `SeriesTasksBoard` copy to prefer "Shared Library" and "Series Task" language, not "system sources" or Week14 source families.
5. Update `SeriesTaskSnapshotWriter` so General Library file items are copied out of private storage before learner delivery. Links and YouTube can be metadata snapshots.
6. Pass the task creator / acting automation user id through Series publish validation, publisher preflight, and snapshot generation when resolving General Library resources. This preserves TQ6's shared Library permission check for active teacher/admin/superadmin users without reintroducing Week14 subject-private ownership.
7. Update publish validation and item-belongs-to-collection logic for the new source type.
8. Reuse the shared General Library test schema/fixtures from TQ7-2 instead of creating one-off Series-only tables.
9. Add tests for source browsing, selectable source-only folders, nested folder navigation, ordered item snapshots, stop-at-end and loop behavior using General Library resources, acting-user authorization, and hidden legacy source families.

### Phase TQ7-4: Terminology And Launch UI Cleanup

Goal: keep the Week14 architecture while removing legacy wording/content from launch-facing pages.

Actions:

1. Keep the top-level teacher navigation label `Automation`.
2. Keep track labels unless owner later renames them:
   - `Versioned Routines`
   - `Differentiated Tasks`
   - `Series Tasks`
3. Remove or hide visible launch-facing labels like SAT, Literature, TV Series, Vocabulary, Vocab Games, Friends, Avatar, Grammar, Notice & Note, and Level Up from automation pickers, empty states, tests, and help copy.
4. Use Quran/Arabic/MDJ examples in tests and starter data.
5. Do not add student or parent Automation navigation. Learners continue to see generated work in normal subject/session/task surfaces.
6. If `SeriesTasksBoard` vocabulary policy fields remain in code, keep them dormant/unreachable from default launch source selection rather than deleting them. They are allowed as future TQ8 reuse scaffolding, not as visible English vocabulary functionality.

### Phase TQ7-5: Preserve TQ8 Vocabulary Game Reuse Path

Goal: avoid conflating launch hiding with removal of future Quranic Arabic game architecture.

Actions:

1. Treat `quranic_arabic_game_codex_brief.md` as the current TQ8 spike brief.
2. Do not import or expose Week14 English vocabulary content, Cambridge/phonics lists, or Latin-script game assumptions in TQ7/TQ7.5.
3. Do preserve reusable Week14 game implementation pieces where safe:
   - game page/layout patterns;
   - state machine and answer-check flow;
   - scoring and attempt-saving shape;
   - levels/categories concepts;
   - audio playback UI;
   - option-button UI and feedback animations.
4. Any Quranic Arabic game work remains out of TQ7 unless explicitly reopened. TQ8 must separately handle Qur'anic text accuracy, Unicode normalization, licensing/storage, Tanzil/Quran Foundation source policy, Arabic RTL rendering, teacher/admin review, and new Quran-specific tables/services.

### Phase TQ7.5-1: Define The Starter Catalog In Code

Goal: create reproducible launch-ready automation templates for teachers without one-off UI setup. These automation rows are created for a teacher/user so existing assignment ownership works; their Library sources remain shared TQ6 General Library content.

Implementation approach:

1. Add a code-defined catalog, for example `config/toquran_automation_catalog.php` or an app support class under `app/Support/ToQuranAutomationCatalog`.
2. The catalog manifest must be explicit enough that the installer makes no product decisions at runtime. Each entry must declare:
   - `catalog_key`, `subject_title`, automation type (`versioned_routine`, `differentiated_task`, or `series_task`), title, description, and install status.
   - task type lookup by title, defaulting to launch task type `Assignment`; the command must fail loudly if the configured task type is missing from `task_types`.
   - points policy, defaulting to `default_points = 5` and `max_points = 10` unless the entry declares otherwise.
   - recurrence rule using existing fields: `daily` interval `1` by default; optional explicit weekly weekdays or monthly day-of-month where a starter truly needs it.
   - ownership/install target: the teacher user selected by `--teacher-email` or approved `--all-active-teachers`; do not create staff/users.
   - teacher and subject eligibility: the installer must verify the target user is active, has the teacher role/permissions expected by automation ownership, and is eligible for the catalog entry's subject/class context before creating rows. If eligibility cannot be proven, the command must skip that entry or fail loudly in single-teacher mode with a clear reason instead of creating unusable automation rows.
   - platform-managed fields versus teacher-editable fields. At minimum, `catalog_key`, automation type, root and child catalog keys, target row ids, subject id, installed version, and source folder/resource bindings are platform-managed; title, description, points, recurrence, version text, and assignment choices are teacher-editable after install unless a later owner decision says otherwise.
3. Versioned Routine entries must declare versions and tasks:
   - stable version keys plus version display names and descriptions;
   - stable task keys plus main task title, prompt/description, sort order, task type, points, and optional `general__{resource}` attachments;
   - publish/install status, with text-only starters installed as `draft` and content-backed starters eligible for `active` only after publish validation passes.
4. Differentiated Task entries must declare:
   - task title/description, task type, points, recurrence, and status;
   - stable version keys plus display name, version prompt/description, selected attachments, and ordering;
   - both attachment pool rows and per-version selection rows; every selected `general__...` attachment must be present in `differentiated_task_attachments` and linked through `differentiated_task_version_attachments` or it will not be delivered to students;
   - text-only launch starters installed as `draft` unless explicit activation is requested and validation passes.
5. Series Task entries must declare:
   - `library_collection_type = general_library_folder`;
   - a stable General Library folder lookup path, for example `Quran Repetition/001. Al-Faatiha`, not a mutable numeric id in config;
   - strict source resolution behavior: installation must fail loudly on zero matching folders/resources, multiple matching folders/resources, archived/inactive folders/resources, a folder that is not source-only when the entry requires source-only, or a folder whose active resources do not match the declared inclusion policy;
   - sequence behavior (`stop_at_end` for Quran Repetition launch starters unless a specific entry says `loop`);
   - release policy (`continuous` unless a specific entry says `wait_for_completion`);
   - stable version keys, version display names, and ordered item inclusion policy (`all active resources in folder by sort_order/title` for Quran Repetition starters, unless an explicit resource list is provided);
   - stable generated item keys, derived from explicit catalog resource keys or deterministic folder path plus resource lookup identity, never from mutable numeric ids alone;
   - generated item status as active only when every referenced General Library resource resolves through the shared Library authorization check.
6. Add the durable catalog identity registry before writing the installer. Title-based upserts are not enough because teachers may edit starter titles. The TQ7.5 plan uses a small guarded manual-SQL registry table, `toquran_automation_catalog_entries`:
   - store root and child mappings in the registry, using fields such as `automation_type`, `catalog_key`, `entry_scope` (`root`, `version`, `task`, `item`, or `attachment`), `entry_key`, target table/id, teacher user id, subject id, installed version, manifest hash, and timestamps;
   - enforce uniqueness on `automation_type + catalog_key + teacher_user_id + subject_id + entry_scope + entry_key`; use `entry_scope = root` and `entry_key = root` for the root automation row so MySQL `NULL` uniqueness edge cases do not weaken idempotency;
   - never delete registry rows or teacher-edited automation rows automatically;
   - use the registry for idempotent root and child-row updates while preserving teacher edits unless a catalog field is explicitly platform-managed.
   If implementation avoids this table, the plan must be amended and re-reviewed with a concrete non-schema identity strategy, including child-row identity and its limitations, before code is written.
7. Define child-row sync behavior before installer implementation:
   - on first install, create root rows, versions, tasks/items, and attachment/source bindings from the manifest and record every platform-managed row in the registry;
   - on rerun, resolve every root and child row through registry identity, not title text or mutable numeric config ids;
   - before updating any registry-resolved row, verify the target row still belongs to the same teacher user, subject, automation type, parent catalog root, and expected parent table/id chain; if any check fails, skip the update, report the registry mismatch, and require manual review rather than mutating the row;
   - update only platform-managed fields such as source bindings, publish/install status, sort order, and manifest version/hash;
   - preserve teacher-editable text, points, recurrence, and assignment choices on already-installed rows unless a later owner decision marks a field platform-managed;
   - create newly added manifest children when their stable keys do not exist;
   - never delete missing/removed manifest children automatically; mark them skipped/orphaned in dry-run output and require a documented cleanup plan if removal is needed.
8. Add a guarded Artisan command, for example `toquran:install-automation-catalog`, that:
   - refuses to run unless `--confirm-db=u504065335_to_quran` matches the active DB;
   - supports `--teacher-email=` for the launch/default teacher;
   - supports `--all-active-teachers` only after explicit owner approval;
   - verifies teacher activity, role/permission eligibility, and subject/class eligibility for every catalog entry before writing rows;
   - upserts records by the durable catalog identity strategy above, not by mutable titles alone;
   - never deletes teacher-edited rows;
   - supports `--dry-run` and reports the exact rows it would create/update/skip;
   - reports created/updated/skipped counts.
9. Add a database/manual execution note template before any run. If the command is executed locally, first create or confirm focused backup/export evidence and then record target, command, counts, and verification.

Initial starter catalog proposal:

- Quran Memorization (`content-backed now` for the listed Quran Repetition Series Tasks; `text-only launch starter` for routine task prompts unless owner supplies extra source files):
  - Versioned Routine: `Daily Hifz And Review`
    - Beginner: listen/repeat, recite short range, parent check.
    - Steady: listen/repeat, recite range, correction note, review older ayat.
    - Challenge: recite from memory, self-check, teacher correction prompt.
  - Series Tasks:
    - `Quran Repetition - Al-Faatiha`
    - `Quran Repetition - Al-Ikhlaas`
    - `Quran Repetition - Al-Falaq`
    - `Quran Repetition - An-Naas`
    - Source from existing TQ6 `Quran Repetition` General Library per-surah folders.
- Quranic Arabic (`text-only launch starter`; `owner-content required` before presenting as content-backed):
  - Versioned Routine: `Quranic Arabic Reading Practice`
    - Letter/sound recognition, word reading, short ayah reading versions.
  - Differentiated Task: `Reading Support Prompt`
    - Listen-and-repeat, trace/read, independent reading versions.
- Arabic Language (`text-only launch starter`; `owner-content required` before presenting as content-backed):
  - Versioned Routine: `Arabic Practice Loop`
    - letter/word/sentence versions, text-only until Arabic resources are added.
- My Deen Journey (`text-only launch starter`; can be launch-ready as prompts/habit checks without Library files):
  - Versioned Routine: `My Deen Daily Check-In`
    - Salah check, adab/home habit, reflection task.
  - Differentiated Task: `Reflection Prompt`
    - simple, guided, independent versions.
- Sanad Program (`text-only launch starter`; `owner-content required` before presenting as content-backed):
  - Versioned Routine: `Sanad Recitation Preparation`
    - preparation, recording/self-check, teacher recitation note.

Catalog boundaries:

- Do not add English vocabulary games.
- Do not invent Quranic text/ayah ranges beyond existing owner-approved Library content unless owner supplies content.
- Do not present text-only or owner-content-required starters as content-backed Library material.
- Do not create production user accounts.
- Do not assign starter catalog items to students automatically. Teachers assign after smoke.
- The command may create draft or active-ready automation templates for a teacher, but assignment remains manual and Library sources remain shared General Library content.

### Phase TQ7.5-2: Starter Catalog Data Safety And Manual Evidence

Goal: make starter data reproducible and reviewable.

Actions:

1. Before any catalog execution, confirm backup/export evidence for `u504065335_to_quran`.
2. Confirm active target DB name and that it is intentionally the accelerated To Quran app DB.
3. Record the command or SQL execution under `database/manual/patches/`.
4. If durable starter data is created by command instead of raw SQL, add a SQL note file that documents:
   - backup evidence;
   - command and options;
   - target DB;
   - expected affected tables;
   - idempotency behavior;
   - created/updated/skipped counts;
   - guard-failure result.
5. Update `database/manual/README.md` replay/order notes if the catalog becomes part of the launch replay process.
6. Update `docs/shared/SHARED-DB-HANDOFF.md` only if the catalog execution affects shared deployment expectations.

## Implementation Sequencing

Land TQ7 and TQ7.5 as separate reviewable units, even if they stay on the same working branch during local development:

1. TQ7 implementation unit:
   - close legacy automation source leaks;
   - extract the shared General Library attachment service;
   - adapt Versioned Routine, Differentiated Task, and Series Task source handling;
   - update focused tests;
   - no DB writes, no registry SQL, no catalog command execution.
2. TQ7.5 implementation unit:
   - add the code-defined starter catalog manifest;
   - add the guarded `toquran_automation_catalog_entries` manual SQL;
   - add the guarded installer command and dry-run reporting;
   - add catalog idempotency and eligibility tests;
   - execute nothing against any DB until backup/export evidence, target confirmation, and manual execution notes are complete.

## Database Impact

Expected schema impact: none for TQ7 if the implementation uses existing flexible `varchar(64)` Series source-type columns.

TQ7.5 schema impact: one small guarded registry table, `toquran_automation_catalog_entries`. That table must be created only through reviewed manual SQL with target checks, backup evidence, and an execution note. If the registry table is not used, implementation must amend this plan before building the installer.

Expected data impact:

- TQ7 code changes only should not write production data.
- TQ7.5 starter catalog writes rows to automation tables only when the guarded command or reviewed manual data artifact is explicitly executed.
- No DB changes may run until backup/export evidence exists, target DB is confirmed, and the manual artifact/execution note exists.
- No destructive cleanup is part of this plan.

Potential affected tables for TQ7.5:

- `toquran_automation_catalog_entries`
- `main_daily_session_templates`
- `main_daily_session_versions`
- `main_daily_session_main_tasks`
- `main_daily_session_version_tasks`
- `main_daily_session_main_task_attachments`
- `differentiated_tasks`
- `differentiated_task_versions`
- `differentiated_task_attachments`
- `differentiated_task_version_attachments`
- `series_tasks`
- `series_task_versions`
- `series_task_version_items`
- possibly `attachment_files` only during generated smoke, not catalog creation.

## Public Website Handoff

No public website implementation is planned for TQ7/TQ7.5.

Public pages should not promise specific automated routine or series content until:

- TQ7 General Library automation adapters are implemented and tested;
- TQ7.5 starter catalog exists in a reproducible form;
- teacher assignment smoke passes locally or on the intended launch target.

If public copy later mentions automated routines, record the decision in shared docs and implement public copy in `D:\xampp\htdocs\toquran`, not in this sprint.

## Verification Scope

Focused automated tests:

- Existing automation suites:
  - `tests/Feature/CoreLms/AutomatedTaskAuthoringTest.php`
  - `tests/Feature/CoreLms/AutomatedTaskAssignmentTest.php`
  - `tests/Feature/CoreLms/AutomatedTaskVisibilityTest.php`
  - `tests/Feature/CoreLms/DifferentiatedTaskAssignmentTest.php`
  - `tests/Feature/CoreLms/DifferentiatedTaskSnapshotWriterTest.php`
  - `tests/Feature/CoreLms/SeriesTaskGenerationTest.php`
  - relevant unit recurrence/publish validator tests.
- New or updated tests:
  - Shared automation test schema/fixtures include `general_library_folders` and `general_library_resources`.
  - Normal tasks, Versioned Routines, Differentiated Tasks, and Series Tasks all use one shared General Library attachment service for file-copy, link, YouTube, and authorization behavior.
  - Versioned Routine can attach General Library YouTube/link/file sources only through an authorized active acting user.
  - Differentiated Task can attach General Library YouTube/link/file sources only through an authorized active acting user, and selected resources create both pool and per-version selection rows.
  - Series source resolver exposes General Library source-only folders and hides Week14 legacy families.
  - Pre-existing automation rows with legacy Week14 source bindings fail closed, skip gracefully during learner lazy generation, and do not break the student subject/session page.
  - Series Task generation snapshots General Library YouTube/link/file resources safely.
  - Starter catalog installer is idempotent for root and child rows, fails loudly on ambiguous/missing/inactive General Library source lookups, verifies teacher/subject eligibility and registry-resolved target ownership/type/parentage before updating, and refuses wrong DB/missing confirmation.

Manual smoke after implementation:

- Teacher sees Automation tracks.
- Teacher creates or opens a Quran Memorization Versioned Routine and attaches Quran Repetition from Shared Library.
- Teacher creates or opens a Differentiated Task and selects Quran/Arabic General Library content.
- Teacher creates or opens a Series Task from a General Library source-only folder.
- Teacher assigns a starter routine/series to a smoke student.
- Student sees generated work in the normal subject/session/task surface.
- Parent can see generated work through existing parent visibility.
- Old Week14 source families are not visible in launch-facing automation pickers.

Frontend form-control verification:

- Automation pages include native `select` controls and modal search/picker fields. If implementation changes those controls, complete the `docs/WORKFLOW.md` frontend form-control verification, including mobile/tablet behavior.

## Explicit Non-Goals

- No production deployment.
- No DB execution during this planning task.
- No Laravel migrations, seeders, `migrate:fresh`, `db:wipe`, or destructive cleanup.
- No launch-facing English vocabulary games or Week14 vocabulary content.
- No Arabic vocabulary game implementation; keep it in TQ8, while preserving reusable Week14 game architecture/designs for that later spike.
- No finance, scheduling, consultation calendar, or broad class-management work.
- No student/parent Automation navigation.
- No public website implementation.
- No automatic assignment of starter catalog work to real students.

## Completion Definition

TQ7 is implementation-review ready when:

- General Library content can be attached and safely snapshotted by Versioned Routines, Differentiated Tasks, and Series Tasks.
- General Library file-copy, link, YouTube, and authorization behavior is centralized in one shared service rather than duplicated across task families.
- Differentiated Task General Library attachments are linked through both the task-level pool and version-selection rows.
- Week14 legacy source families are hidden or blocked from launch-facing automation, including pre-existing legacy-source rows, without breaking learner lazy-generation pages.
- Existing automation behavior remains covered by focused tests.
- Quran/Arabic/MDJ terminology appears in new tests and visible copy where applicable.

TQ7.5 is implementation-review ready when:

- A reproducible starter catalog exists in code or guarded manual data artifacts.
- The catalog can create/update launch-ready automation templates for eligible teachers without duplicate drift in root rows, versions, tasks, items, or attachments, and refuses ambiguous source lookups, ineligible teacher/subject targets, or stale registry target mismatches.
- Catalog idempotency uses the guarded `toquran_automation_catalog_entries` registry table unless this plan is amended and re-reviewed first.
- Execution is guarded by target DB checks and recorded under `database/manual/` if run.
- Teachers can assign starter routines/series to smoke students.
- Student and parent smoke confirms generated output appears through existing learner surfaces.
