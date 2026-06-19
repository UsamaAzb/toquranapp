# TQ9 Launch Readiness Deployment Phases Plan

Status: planned; audit-only baseline complete, no production deployment authorized
Date: 2026-06-18
Branch: `codex/tq9-launch-readiness`
Sprint: TQ9 Deployment Readiness And Public Website Handoff

## Objective

Prepare To Quran app deployment safely, in explicit phases, from local readiness through Hostinger verification, DB backup/replay, code deployment, smoke testing, cleanup, credential rotation, and final evidence capture.

This plan does not authorize production deployment by itself. Each production-changing action still requires explicit owner approval of the exact command or action, especially DB import/restore/manual SQL, file edits on Hostinger, cleanup deletes, credential rotation, and final public launch cutover.

## Roadmap Relationship

TQ9 is active and depends on TQ2, TQ3/TQ3.5, TQ4, TQ6, TQ7, and TQ7.5 launch readiness.

Current TQ9 guard from `docs/TOQURAN-SPRINTS.md`:

- final deployment is not ready until remaining class/session/task/automation/MDJ/rewards/Library smoke passes or is explicitly moved out of launch scope;
- inherited Language and Literature Library source cleanup must remain verified;
- TQ7.5 starter routine/series catalog must exist and be assignable by teachers;
- final smoke cleanup, credential rotation, and JavaScript audit strategy must be resolved;
- public form to app intake/review/transfer/login smoke must pass on the deployment target or a production-equivalent environment.

## Current Evidence

Local audit on 2026-06-18 found:

- branch is `codex/tq9-launch-readiness`;
- `HEAD`, `main`, `origin/main`, and `origin/codex/tq9-launch-readiness` all point to `2ee1e51 Expand TQ7.5 automation catalog with Dua bank`;
- working tree was clean before this plan file and the related TQ9 roadmap note were edited;
- public website shared-DB handoff was introduced in `toquran` commit `6dfb71f`;
- local production-equivalent TQ9 smoke evidence exists in `docs/audits/2026-06-02-tq9-shared-db-smoke-and-hardening.md`;
- build marker exists at `docs/BUILD-DEPLOY-MARKER.md`, with matching `public/build/` and root `build/` fingerprints;
- local `.env` contains the required launch-relevant keys without exposing secrets in this plan;
- local `public/storage` exists as a junction/link;
- current PHP/Composer local tooling is available;
- JavaScript lockfile/tooling decision was confirmed on 2026-06-19: the repo has `yarn.lock` only, no `package-lock.json`/`pnpm-lock.yaml`, no direct local `yarn` command, and `corepack yarn --version` reports Yarn `1.22.22`; use `corepack yarn` for audit/install/build unless an owner-approved lockfile migration is opened separately;
- app JavaScript production dependency audit was hardened on 2026-06-19: removed unused direct/template packages `@iconify/tools`, `bloodhound-js`, `jkanban`, and `quill`; excluded unused Kanban/Quill demo entries from Vite; updated `swiper` to `12.1.2`, `sweetalert2` to `11.22.4`, `vite` to `6.4.3`, `axios` to `1.18.0`, and `glob` to `10.5.0`; changed `bootstrap-select` from a GitHub SSH dependency to npm `1.14.0-beta3`; added targeted transitive pins for `preact`, `protocol-buffers-schema`, `vite-plugin-full-reload/picomatch`, `rollup`, and `shell-quote`;
- app JavaScript verification on 2026-06-19: `corepack yarn install --frozen-lockfile` passes; `corepack yarn build` passed and updated `docs/BUILD-DEPLOY-MARKER.md` with matching `public/build/` and root `build/` fingerprints; `corepack yarn audit --groups dependencies --level moderate --summary` reports `0 vulnerabilities` across 305 production packages; full `corepack yarn audit --level moderate --summary` still reports 70 dev/tooling-only vulnerabilities across 1015 packages (`5` low, `30` moderate, `35` high, `0` critical), mostly BrowserSync, ESLint/Stylelint, Babel, Sass, and related local tooling paths;
- app Composer advisory was patched on 2026-06-19 by updating `laravel/framework` from `v12.61.0` to `v12.62.0`;
- Composer platform is pinned to PHP `8.2.12` to keep the lock compatible with the current local/runtime baseline and avoid PHP 8.4-only dependency drift;
- app verification on 2026-06-19: `composer check-platform-reqs` passed, `composer audit` passed, `composer audit --no-dev` passed, and `php artisan test tests/Unit tests/Feature` passed with 785 tests and 10 owner-managed/postponed skips;
- read-only inspection of `D:\xampp\htdocs\toquran` confirms the public website already has real launch forms: `/book-trial/store` writes app-owned booking/review rows with generated `TQ-` references, and `/contact/store` writes app-owned `contacts` rows with generated `CNT-` references;
- public website local smoke/build evidence reported on 2026-06-18 is healthy for behavior: website handoff tests passed, routes exist, build passes with Sass deprecation warnings, and contract values align with the app handoff;
- public website dependency audit evidence reported on 2026-06-18 is not launch-clean: `composer audit` reports 35 advisories across 15 packages, `composer audit --no-dev` reports 31 advisories across 13 packages, and `npm audit --audit-level=moderate` reports 10 vulnerabilities including critical/high items;
- owner clarified on 2026-06-18 that the Hostinger DB is empty and will be removed/recreated for a clean start before deployment. Codex still must not drop/remove/import/restore without exact approval and backup/empty-target evidence;
- owner clarified on 2026-06-18 that deployment covers both the public website and the app subdomain. On Hostinger, subdomains are inside the main domain folder, and `app.toquran.org` should use the custom `appdashboard` folder because the main domain already has an `app` folder belonging to the public website. The `appdashboard` folder is connected to GitHub and can pull the repo from Hostinger server settings; Remote SSH is also available for inspection and command execution;
- local public website repo is `https://github.com/UsamaAzb/toquran.git` on `main`; as of second-pass verification, `HEAD` and `origin/main` are `24d76d1 Polish launch email branding` and the worktree is clean;
- `toquran` commit `24d76d1` is two commits after the original shared-DB handoff commit `6dfb71f` and touches `BookingController`, `ContactController`, and launch email templates. Treat it as the current website deployment candidate only after reviewing the booking/contact controller changes against the app-owned `TQ-` / `CNT-` intake contract;
- owner clarified on 2026-06-18 that the TQ7.5 religious Adhkar/Dua starter content has been reviewed and is correct for launch use;
- owner clarified on 2026-06-18 that the current public website has no active users and can be recovered by downloading/backing up and reuploading files if a website deployment problem occurs. The deployment plan can use a simple website rollback path, but still requires backup/disposable confirmation before replacing server files;
- owner clarified on 2026-06-18 that the mailbox accounts are working. Email passwords must not be pasted into chat, committed, or recorded in deployment notes; only secret presence and successful mail behavior should be documented.

## Phase 0: Plan And Authorization Gate

Goal: confirm deployment boundaries before touching production.

Owner decision:

- Approved on 2026-06-19 to use this TQ9 deployment phase sequence as the working checklist.
- This approval does not authorize any production deployment, production DB write, Hostinger file edit, cleanup delete, credential rotation, or final launch cutover. Each production-changing step still requires exact owner approval before execution.

Actions:

1. Keep this plan in `docs/plans/active/`.
2. Confirm the owner agrees to use this phase sequence for TQ9.
3. Confirm no production deployment, production DB write, production file edit, or cleanup action is authorized until the owner approves the exact step.
4. Keep raw DB backups, `.env` values, SSH keys, tokens, passwords, PINs, session data, and production dumps out of Git.

Exit criteria:

- plan exists and has been reviewed;
- open blockers are listed;
- the next requested action is explicit.

## Phase 1: Local Branch, Dependency, And Build Hardening

Goal: make the local deployable artifact clean before Hostinger work.

Actions:

1. Reconfirm clean git status and correct remote.
2. Patch the current Composer advisory without broad unrelated dependency churn.
3. Run:
   - `composer audit`
   - `composer audit --no-dev`
   - focused PHPUnit suites covering launch access, intake/transfer, Library, automation, TQ7.5 catalog, MDJ/rewards, and tasks.
4. Resolve JavaScript audit strategy:
   - current decision: use `corepack yarn` with existing `yarn.lock` because the repo has no npm/pnpm lockfile;
   - alternative: owner-approved migration to a different lockfile tool;
   - do not create `package-lock.json` casually during launch.
5. Resolve app JavaScript audit blockers before launch or record explicit owner risk acceptance:
   - prefer a scoped hardening pass for direct/deploy-relevant packages first (`swiper`, `@iconify/tools`, `laravel-vite-plugin`/`vite`, `jkanban`, `bloodhound-js`, `quill`, and required transitive resolutions only where they are compatible);
   - rerun `corepack yarn audit --groups dependencies --level moderate --summary` and full `corepack yarn audit --level moderate --summary`;
   - if any advisories remain, record package, severity, exposure, reason it remains, and follow-up owner decision.
6. Rebuild assets with `corepack yarn build` after dependency changes.
7. Verify `public/build/manifest.json`, `build/manifest.json`, and `docs/BUILD-DEPLOY-MARKER.md`.
8. Prepare the public website repo for deployment before Hostinger work:
   - confirm the intended public website deployment commit, currently expected to be `24d76d1` unless superseded;
   - review `6dfb71f..24d76d1` changes, especially `BookingController` and `ContactController`, against the app-owned booking/contact intake contract;
   - run and resolve public website dependency audits:
     - `composer audit`;
     - `composer audit --no-dev`;
     - `npm audit --audit-level=moderate`;
   - rerun the public website handoff tests and build after dependency changes;
   - commit and push intended website changes to `UsamaAzb/toquran` `origin/main`;
   - verify the pushed website commit is the intended deployment source.

Exit criteria:

- Composer audits clean;
- JS audit decision documented, production dependency audit clean, and any remaining full-audit dev/tooling advisories recorded with package/severity/exposure/follow-up;
- assets rebuilt and build marker updated when needed;
- focused tests pass;
- public website booking/contact controller changes since `6dfb71f` are reviewed as contract-safe, or a fix/risk decision is recorded;
- public website Composer/npm audits are clean or each remaining advisory has an owner-approved launch risk decision with package, severity, exposure, and follow-up recorded;
- public website handoff tests and build pass after any dependency changes;
- public website repo is clean/pushed or a different deployment source is explicitly documented;
- git status is clean except intentional plan/evidence edits.

## Phase 2: Local Final App Smoke

Goal: prove the current code and local real-name app DB shape are launch-coherent.

Actions:

1. Use To Quran local app port `http://127.0.0.1:8014`; inspect any port conflict before changing ports.
2. Confirm `/login` returns `To Quran | Login`.
3. Smoke superadmin/admin/staff user management.
4. Smoke public website to app handoff locally if the website repo is available:
   - clean booking;
   - duplicate/review booking;
   - Contact Us to `contacts`;
   - app review/transfer;
   - parent/student login;
   - teacher class visibility.
5. Smoke class/session/normal task creation and task attachment visibility.
6. Smoke shared Library:
   - admin/superadmin can manage Quran Repetition folders/sources;
   - teacher can attach General Library content;
   - student and parent can view assigned attachment snapshots;
   - inherited Week14 Library/Vocabulary surfaces remain hidden or denied.
7. Smoke My Deen Journey, rewards/points, behavior/accountability, and consequence agreement surfaces.
8. Smoke TQ7 automation source adapters and TQ7.5 starter catalog assignment:
   - teacher can see copied Well Being / My Deen Journey starter drafts;
   - teacher assigns representative starter routine or Dua Bank Series to a smoke student;
   - student/parent generated output appears.

Exit criteria:

- smoke evidence captured in a TQ9 audit or execution note;
- any regression has a fix plan or is explicitly moved out of launch scope by owner decision.

## Phase 3: Hostinger Read-Only Inspection

Goal: understand server layout and runtime for both the public website and app subdomain without changing production files.

Actions:

1. If VS Code Remote SSH is connected, inspect only:
   - home directory structure;
   - public website code directory for `toquran.org`;
   - app subdomain code directory, expected to be the custom `appdashboard` folder for `app.toquran.org`;
   - public webroot for `app.toquran.org`;
   - public webroot for `toquran.org`;
   - current PHP version and extensions;
   - Composer availability;
   - Node/Yarn availability, if builds might run server-side;
   - cron/scheduler configuration options;
   - process manager or queue worker options;
   - file permissions for app path, `storage/`, and `bootstrap/cache/`.
2. Confirm whether `app.toquran.org` points to Laravel `public/` under `appdashboard` or relies on root compatibility files.
3. Confirm whether the public website deploy path is the main domain folder and whether it is Git-connected or requires SSH/manual sync.
4. Confirm `app.toquran.org` serving details in plain terms:
   - DNS for `app.toquran.org` resolves to the intended Hostinger account;
   - SSL is active for `https://app.toquran.org`;
   - the Hostinger subdomain document root points to `appdashboard/public`, or an owner-approved root-compatibility setup is documented;
   - if any DNS, SSL, or document-root change is needed, treat it as a separate deployment-window action requiring explicit owner approval before changing it.
5. Confirm the safest deployment mechanism:
   - preferred for `appdashboard` if configured: Hostinger GitHub pull for `UsamaAzb/toquranapp`, followed by SSH/terminal commands for Composer/cache/storage;
   - preferred for the public website only after its local repo is clean and pushed: Hostinger GitHub connection to `UsamaAzb/toquran`;
   - SSH/manual sync only if GitHub pull cannot deploy the right commit/build artifacts safely.
6. If connecting the public website folder to Hostinger Git requires an empty folder, schedule that as a deployment-window action only after:
   - current server files are backed up or confirmed disposable;
   - target commit for `UsamaAzb/toquran` is known;
   - owner explicitly approves deleting/replacing the current website files.
7. Identify the simple public website rollback path:
   - backup/download current website files before replacement, or record owner confirmation that they are disposable;
   - know the previous website commit or file backup location;
   - if website smoke fails, restore the backed-up files or previous commit before continuing.
8. Identify where raw server DB backups can be stored outside Git.
9. Do not edit production files during this phase.

Exit criteria:

- server layout notes recorded under docs or an execution note;
- app subdomain path, public website path, and public paths are known;
- chosen deployment mechanism is recorded for both repos;
- website repo clean state and pushed commit are recorded before deployment;
- runtime gaps are listed before code upload.

## Phase 4: Production Environment Configuration Review

Goal: verify production `.env` assumptions without exposing secrets.

Actions:

1. Confirm required keys exist, recording only presence and safe non-secret summaries:
   - `APP_ENV=production`;
   - `APP_DEBUG=false`;
   - `APP_URL=https://app.toquran.org`;
   - `APP_KEY` presence;
   - DB connection host/database/user presence;
   - `MAIL_*` presence and sender identity;
   - any webhook, automation, or third-party secret key presence required by launch features;
   - `QUEUE_CONNECTION`;
   - `CACHE_STORE`;
   - `SESSION_DRIVER`;
   - `FILESYSTEM_DISK`;
   - `TOQURAN_DEFAULT_TEACHER_EMAIL`.
2. Confirm no local smoke/test values are present in production config.
3. Confirm the intended production `TOQURAN_DEFAULT_TEACHER_EMAIL` value is present or ready to set. The email will only be resolution-checked after the default teacher account is created in Phase 6.
4. Confirm mail behavior for activation/password/support emails. Mailbox passwords must be entered only through the approved private Hostinger/server configuration path, never through chat, Git, screenshots, or deployment notes.
5. Confirm queue behavior:
   - real worker if available;
   - otherwise an owner-approved cron fallback with known limitations.
6. Confirm scheduler cron:
   - `* * * * * php /path/to/artisan schedule:run`.
7. Repeat the DB-related `.env` review after any Hostinger DB removal/recreation, because a recreated Hostinger database may receive a new DB name, user, password, or host.
8. Decide whether the public website should use a restricted DB user scoped to booking/contact/intake tables instead of the app's full-access DB credential. If creating or changing a DB user/grant is needed, treat it as a production DB change requiring guarded manual SQL or an execution note, target/instance evidence, backup/export evidence, and exact owner approval. If deferred, record the launch risk decision and follow-up.

Exit criteria:

- production env readiness checklist is complete without secret disclosure;
- any missing keys or unsafe values are fixed only after explicit approval.

## Phase 5: Production DB Clean-Start, Backup Evidence, And Manual SQL Manifest

Goal: start from the intended empty Hostinger app DB safely, preserve evidence of the pre-deployment state, and apply only reviewed DB changes to the intended app target.

Actions:

1. Before the owner removes/recreates the Hostinger DB, capture safe evidence that the target is empty or disposable:
   - DB name;
   - table count;
   - Hostinger backup/export status, even if the export is empty;
   - owner confirmation that removal/recreation is intentional for To Quran clean-start deployment.
2. Codex must not drop, remove, truncate, import, restore, or overwrite the Hostinger DB unless the owner explicitly approves the exact action/command after the target and backup/empty evidence are recorded.
3. After the owner removes/recreates the DB, verify the active target DB identity and reconcile configuration:
   - expected DB name, or the new Hostinger-assigned DB name if it changed;
   - DB host;
   - DB user;
   - app `.env` DB values updated to match the recreated target;
   - website `.env` DB values updated to match the same shared target;
   - all `--confirm-db` command literals and manual SQL guard literals updated if the DB name changed.
4. Because local and production both may use the same DB name, do not rely on `DATABASE()` or `--confirm-db` alone for production DB safety. Before every production DB write, import, command-driven catalog install, and cleanup, record a host/instance-level guard including:
   - execution location, such as Hostinger SSH path or Hostinger panel action;
   - `DB_HOST` safe summary showing Hostinger, not local `127.0.0.1` / `localhost`;
   - DB name;
   - DB user;
   - MySQL identity evidence such as safe output from `SELECT DATABASE(), USER(), @@hostname;`;
   - owner approval of the exact command/action after this identity evidence is captured.
5. Confirm the recreated target is empty before applying baseline/import work. If it is not empty, stop and write a separate reviewed delta/restore plan instead of replaying baseline SQL.
6. Confirm the website and app point to the intended shared app DB target before public form launch.
7. If using a restricted public website DB user, verify it can perform required public booking/contact writes but cannot read or write unrelated app tables such as users, credentials, sessions, family/student records, and staff/admin data.
8. Produce a concrete production DB manifest before any DB action. The manifest must list exact filenames/commands in execution order and classify each step as:
   - schema baseline;
   - schema correction;
   - reference/starter data;
   - content import;
   - command-driven catalog install;
   - cleanup;
   - verification only.
9. Update `database/manual/README.md` replay/order notes before production replay if the current order is stale.
10. Confirm which manual artifacts must be present on production, including:
   - real app baseline and framework/index/identifier corrections, only for the confirmed empty clean-start target;
   - starter/reference data;
   - learning catalog, Arabic Language service, task types;
   - `contacts.child_age` nullable;
   - `booking_intake_review.detection_reason = clean_new_customer`;
   - family workspace permissions;
   - `users.country`;
   - legacy booking child normalization and school defaults;
   - MDJ behavior/reward/consequence patches;
   - TQ6 General Library tables, Quran Repetition import, and hardening/private-storage shape;
   - TQ7.5 automation catalog registry;
   - TQ7.5 General Library text-source schema;
   - any approved TQ7.5 catalog install command for intended teacher(s).
11. For each DB-changing action, require:
   - backup/export evidence;
   - target DB confirmation;
   - host/instance-level production guard evidence, not only DB name;
   - guarded manual SQL or execution note under `database/manual/`;
   - owner approval of the exact command/action.
12. Do not run Laravel migrations, seeders, `migrate:fresh`, `db:wipe`, restore/import, or destructive SQL unless separately approved with the required DB safety evidence.
13. Validate backup/restore confidence before production launch:
   - record backup file size/checksum or Hostinger backup identifier;
   - verify the dump has the expected table list/count;
   - restore to an isolated scratch DB when practical, or document why Hostinger access prevents a scratch restore.

Exit criteria:

- production DB shape matches the reviewed launch shape;
- exact production DB manifest is recorded;
- backup/empty-target evidence and restore-confidence notes are recorded;
- execution evidence is recorded;
- no raw backups or secrets are committed.

## Phase 6: Code Deployment And Runtime Preparation

Goal: deploy both public website and app subdomain code/runtime assets safely after local and DB gates are satisfied.

Likely commands, subject to Hostinger layout and explicit approval:

- `composer install --no-dev --optimize-autoloader`
- package-manager install/build command matching the approved JS lockfile strategy, if building on server;
- upload or sync `public/build/` and `build/` if building locally;
- `php artisan optimize:clear`
- `php artisan storage:link`
- `php artisan config:cache` after final `.env` changes, including default teacher email;
- `php artisan route:cache` only if route caching is verified safe;
- `php artisan view:cache`

Actions:

1. Confirm deploy source commit and release path for `UsamaAzb/toquranapp` into Hostinger `appdashboard`.
2. Confirm deploy source commit and release path for public website repo `UsamaAzb/toquran` into the main `toquran.org` folder.
3. Prefer Hostinger GitHub pull for `appdashboard` if it can deploy the exact reviewed commit. Use SSH for inspection, Composer/cache/storage commands, and verification.
4. Before connecting or pulling the public website repo on Hostinger, ensure the local website repo is clean and pushed to `origin/main`, or explicitly document why a different commit/source is being deployed.
5. If Hostinger requires deleting current website files to connect GitHub, do that only during the approved deployment window after backup/disposable confirmation.
6. Ensure app `.env` and website `.env` are not overwritten by repo sync.
7. Ensure app `storage/`, `bootstrap/cache/`, and private Library upload paths are writable.
8. Ensure app public storage link is correct for Hostinger.
9. Ensure app generated build artifacts match `docs/BUILD-DEPLOY-MARKER.md`; upload `public/build/` and root `build/` if builds are produced locally.
10. Ensure public website build/static assets are deployed according to `D:\xampp\htdocs\toquran\docs\DEPLOYMENT-DAY-NOTES.md`.
11. Configure scheduler and queue after code is in place.
12. Execute the production account/bootstrap sequence after app code is present:
    - do not import local user rows wholesale just to preserve existing accounts;
    - run the guarded `php artisan toquran:bootstrap-superadmin --confirm-db=...` command only after the Phase 5 host/instance-level production DB guard is captured and the owner supplies the final email/name/phone through a private, non-logged method;
    - for the initial password, prefer omitting `--password` so the command generates a one-time password, then immediately rotate it after first login; do not copy the generated password into Git, chat, screenshots, execution notes, or shared logs;
    - do not pass a real production password through `--password` unless a separate code change first adds a hidden prompt or another owner-approved non-logged secret path;
    - default expected superadmin email is `osama.elazab22@gmail.com` unless owner chooses a different owner/root account before execution;
    - log in as the production superadmin;
    - create/verify the launch default teacher through Staff Users as the easiest safe path;
    - expected default teacher email is `drosamaqandil@gmail.com`;
    - set/verify `TOQURAN_DEFAULT_TEACHER_EMAIL=drosamaqandil@gmail.com`;
    - rebuild config cache after setting or changing `TOQURAN_DEFAULT_TEACHER_EMAIL`;
    - verify the default teacher resolves through the app before first production transfer smoke;
    - TQ7.5 religious starter content is owner-confirmed reviewed and correct for launch use, so catalog install may be in launch scope after the default teacher exists and resolves;
    - only after the default teacher exists and resolves, run any approved `toquran:install-automation-catalog --teacher-email=drosamaqandil@gmail.com --confirm-db=...` command with the Phase 5 host/instance-level production DB guard and record an execution note;
    - if Staff Users cannot create the teacher before catalog install, stop and create a separate guarded teacher-bootstrap artifact/command plan instead of ad hoc SQL;
    - never record production passwords in Git, execution notes, chat, screenshots, or terminal logs.
13. After code deployment and account/bootstrap steps complete, capture a known-good backup of the populated production DB before public website smoke begins. This is the preferred rollback point for launch-day smoke failures.
14. Confirm launch rollback checkpoints before mutating public smoke:
    - app deployed commit and previous known-good state are recorded;
    - public website deployed commit and backup/restore path are recorded;
    - populated production DB backup from Phase 6 is available;
    - if smoke fails, stop new mutating tests, restore the website/app files or commit if needed, restore DB only from the approved known-good backup path, and document the failure before retrying.

Exit criteria:

- deployed app and website commits are known;
- app and public website boot with production config;
- production superadmin/default teacher/catalog-bootstrap path has completed before post-deploy smoke, with credentials kept out of durable notes;
- app caches/build/storage are correct;
- public website assets/routes are correct;
- no production secrets were copied into Git or chat.

## Phase 7: Post-Deploy Smoke

Goal: prove the deployed target works before public launch is treated as complete.

Smoke tests:

1. `https://app.toquran.org/login` loads as To Quran.
2. Superadmin login works and has expected launch access.
3. Admin/staff/teacher management works.
4. Teacher login opens classes.
5. Parent and student login open expected dashboards.
6. Admin analytics dashboard Swiper carousel renders and responds correctly after the `swiper` 12 upgrade.
7. Public website booking creates app-owned booking rows with `TQ-` references.
8. Duplicate/review booking routes to intake review, not normal booking.
9. Contact Us creates `CNT-` row in `contacts` with nullable child age.
10. App review/transfer creates parent/student users and assigns the configured default teacher.
11. Teacher class/session/task creation works.
12. Library attachment snapshots render for teacher/student/parent.
13. My Deen Journey, rewards/points, behavior, and consequence agreement surfaces render and save.
14. TQ7/TQ7.5 automation assignment generates visible student/parent work.
15. Old Week14 Library/Vocabulary surfaces remain hidden or denied.
16. Scheduler and queue/mail behavior are verified with safe launch checks.

Public website deployment-day notes:

- Treat `D:\xampp\htdocs\toquran\docs\DEPLOYMENT-DAY-NOTES.md` as a required checklist for public website smoke.
- Stop if `contacts.child_age` is still `NOT NULL` on the target app DB.
- Stop if the website points at a DB that does not have the app-owned handoff tables.
- Stop if clean booking creates confirmed scheduling, meeting links, scheduled dates, or scheduled times.
- Stop if Contact Us writes to legacy `contact_us` or `massage` tables instead of app-owned `contacts`.
- Stop if there is no target confirmation or backup/export evidence for a requested DB change.

Public website smoke data rule:

- The public website forms are real launch forms, not a fake import bridge. Production smoke should use the existing public form routes from `D:\xampp\htdocs\toquran`: `/book-trial/store` and `/contact/store`.
- Default decision: production public-form smoke rows should be removed after verification. Do not retain smoke-created booking/contact/family/student rows as launch evidence unless the owner explicitly chooses retention before cleanup.
- Before production public-form smoke, choose an owner-approved smoke marker and mailbox strategy:
  - preferred: an owner-controlled mailbox with plus-addressing or a clearly identifiable smoke subject/name marker;
  - alternative: a non-deliverable `@toquran-smoke.test` style address only if mail behavior is disabled or bounce risk is accepted for that test.
- Record generated `TQ-` and `CNT-` references immediately after each smoke submission.
- Before production public-form smoke, draft and review a guarded public-form smoke cleanup artifact under `database/manual/patches/`. After smoke, fill in the captured `TQ-` / `CNT-` references and final row counts before executing cleanup. The existing `2026-05-29-launch-smoke-data-cleanup-plan.sql` is not sufficient for `TQ-` / `CNT-` public-form smoke rows by itself.
- Because production smoke includes public booking/contact writes and app review/transfer, the cleanup artifact must cover downstream rows created from the smoke booking, including `bookings`, `booking_children`, `booking_intake_review`, `booking_intake_review_children`, `booking_intake_submission_locks`, `contacts`, transferred parent/student users, parent/student profiles, class/subject rows, teacher assignments, account history, sessions/tokens, and any task/automation rows created during smoke. It must not delete the real superadmin, real default teacher, starter/reference rows, or non-smoke public submissions.
- The cleanup artifact must include the Phase 5 host/instance-level production DB guard. A DB-name-only cleanup guard is not sufficient because the local and production targets may share the same name.
- If owner decides to keep a production smoke row as launch evidence, document that retention decision and do not delete it accidentally during cleanup.
- If the TQ7.5 starter catalog install is deferred, Phase 7 cannot fully close the TQ7.5 launch criterion. In that case, smoke only the available TQ7 automation path and record an explicit owner decision to defer TQ7.5 catalog assignment from launch.

Exit criteria:

- smoke results are recorded with dates, target, commit, and DB evidence;
- generated public smoke references are either cleaned through a reviewed guarded artifact or intentionally retained with owner approval;
- any failures are fixed or owner-approved as non-blocking before launch.

## Phase 8: Final Cleanup And Credential Rotation

Goal: remove local/testing residue and rotate temporary credentials before launch.

Actions:

1. Create or confirm backup/export evidence immediately before cleanup.
2. Use only scoped cleanup artifacts:
   - `database/manual/patches/2026-05-29-launch-smoke-data-cleanup-plan.sql`
   - a new guarded production public-form smoke cleanup artifact for captured `TQ-` / `CNT-` references, unless those rows are intentionally retained as launch evidence.
3. Verify zero `@toquran-smoke.test`, `[SMOKE]`, and `SMOKE-TQ-*` launch-facing rows remain.
4. Verify captured public-form smoke rows are either removed or intentionally retained per the recorded owner decision.
5. Clean-start production should not contain shared local test passwords. Verify that no real staff/admin/teacher credentials were set to shared local test values; rotate immediately if any are found.
6. Confirm only approved owner/root accounts hold `super_admin`.
7. Clear sessions/tokens if needed after credential rotation.
8. Record cleanup and rotation evidence without exposing passwords or hashes.

Exit criteria:

- smoke data removed from launch target;
- public-form smoke rows cleaned or intentionally retained with evidence;
- temporary credentials rotated;
- no shared test password remains;
- evidence notes are committed without secrets.

## Phase 9: Public Website Launch Verification And Closure

Goal: close TQ9 with a complete cross-repo deployment record.

Actions:

1. Verify public `toquran.org` points to the intended shared DB and production app URL.
2. Verify public sign-in link goes to `https://app.toquran.org/login`.
3. Verify launch public phone remains `+201091051913`.
4. Do not submit new public booking/contact forms after the final cleanup gate unless the owner approves a second smoke run and a second cleanup/retention decision.
5. Perform non-mutating public checks after cleanup/rotation:
   - homepage loads;
   - `/book-trial` form renders;
   - `/contact` form renders;
   - app login link resolves;
   - app `/login` renders.
6. Update shared docs if any deployment, DB, intake, service, or workflow decision changed.
7. Add final deployment evidence note under `docs/audits/` or `database/manual/patches/` as appropriate.
8. Confirm final git status is clean and all intended docs/code changes are committed.

Exit criteria:

- TQ9 done criteria from `docs/TOQURAN-SPRINTS.md` are satisfied or explicitly adjusted by owner decision;
- final smoke evidence is recorded;
- no loose tails remain.

## DB Impact

This plan itself changes no DB data or schema.

Expected future DB-impacting deployment work may include applying or preserving already-reviewed manual SQL artifacts, running guarded catalog install commands, scoped smoke cleanup, and credential/session cleanup. Each such action must follow `docs/DB-SAFETY-POLICY.md` and `database/manual/README.md`.

## Public Website Handoff

The public website handoff was introduced in `toquran` commit `6dfb71f`; the current deployment candidate is `24d76d1` pending booking/contact controller review. Deployment target verification remains required:

- website and app must share the intended app-owned DB target;
- public website and `app.toquran.org` must both be deployed and verified;
- public booking writes to app-owned booking/review/lock tables;
- Contact Us writes to `contacts`;
- public values match the To Quran service contract;
- public website should preferably use a restricted DB credential for public booking/contact writes, or the full-access shared credential risk must be explicitly accepted before launch;
- mutating public booking/contact smoke runs during Phase 7 only, with captured references and cleanup/retention evidence; Phase 9 uses non-mutating public checks unless the owner approves a second smoke run and second cleanup/retention decision.

## Risks And Blockers

- App Composer audits are clean after updating `laravel/framework` to `v12.62.0`; keep rechecking before deployment.
- JS audit tool decision is resolved: use `corepack yarn 1.22.22` with existing `yarn.lock`. App production dependency audit is clean after the 2026-06-19 hardening pass. Full JS audit still has dev/tooling-only advisories; do not install dev dependencies or run server-side builds on Hostinger unless those dev-tooling advisories are separately fixed or risk-accepted.
- Public website dependency audits currently fail: Composer reports many advisories, including production advisories, and npm reports critical/high vulnerabilities. These must be fixed or explicitly risk-accepted before production-changing deployment.
- Hostinger layout, PHP extensions, cron, queue worker options, and public path are not yet verified.
- Hostinger app subdomain must deploy into `appdashboard`, not the public website's existing `app` folder.
- Public website deployment uses `UsamaAzb/toquran`; current candidate `24d76d1` is clean/pushed but includes Booking/Contact controller changes after `6dfb71f` that must be reviewed against the intake contract before relying on Hostinger GitHub pull.
- Public website DB credential scope is undecided. A full-access shared app DB credential increases blast radius from the public website; prefer a restricted website DB user or record explicit launch risk acceptance.
- If Hostinger GitHub connection for the public website requires an empty folder, deleting current website files must wait for the approved deployment window and a server-file backup/disposable confirmation.
- Hostinger DB is expected to be empty and removed/recreated for clean start, but Codex still needs target/empty/backup evidence and exact approval before any destructive or import action.
- Local and production DBs may share the same database name, so production DB writes and cleanup require host/instance-level guard evidence in addition to DB-name checks.
- `database/manual/README.md` replay order needs review for newer TQ6/TQ7.5 artifacts before production replay, and a concrete production DB manifest must be created before DB execution.
- Clean-start production needs deliberate account bootstrap: owner/root superadmin and active default teacher must exist before final transfer/catalog smoke.
- TQ7.5 religious Adhkar/Dua starter content is owner-confirmed reviewed and correct, but production catalog install still requires the normal DB safety gate and exact command approval.
- Production smoke cleanup and credential rotation are mandatory before public launch, including public-form `TQ-` / `CNT-` smoke rows and all transfer-created downstream smoke data unless owner approves retaining them as launch evidence.
- A known-good populated production DB backup and simple website rollback path must exist before mutating public smoke.
- Production file edits and DB writes remain blocked until explicit owner approval of exact actions.

## Explicit Non-Goals

- No production deployment from this plan alone.
- No app code changes in this planning step.
- No DB execution in this planning step.
- No Laravel migrations, seeders, `migrate:fresh`, `db:wipe`, restore/import, or destructive cleanup without the separate DB safety gate and exact approval.
- No raw DB backups, secrets, SSH keys, `.env` values, tokens, passwords, PINs, hashes, or sessions committed to Git.
- No new public website implementation unless TQ9 smoke reveals a deployment-specific gap.
