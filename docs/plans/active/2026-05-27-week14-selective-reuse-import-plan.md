# Week14 Selective Reuse Import Plan

Status: approved for Phase 1; pre-implementation correction applied
Date: 2026-05-27; updated 2026-05-28

## Objective

Plan how to reuse `D:\xampp\htdocs\week14-app-lms` for the To Quran private app at `app.toquran.org` without importing blindly or destroying existing To Quran data.

This plan stops before implementation.

## Non-Goals

- Do not copy app code yet.
- Do not import or mutate DB schema/data yet.
- Do not clean old To Quran tables yet.
- Do not update the public website write path yet.
- Do not include Arabic vocabulary games in first import.

## Evidence Used

- `toquranapp` was empty/non-git before audit docs were created.
- Public `toquran` is a Laravel 10 public website with current To Quran service copy and booking form.
- Current To Quran DB evidence is the SQL export `u504065335_to_quran.sql`, backed up into this repo.
- Owner clarified on 2026-05-28 that the old `u504065335_to_quran` export has no client data requiring preservation; the only intentional legacy preservation target is the Quran YouTube/video list.
- Week14 current live schema was exported read-only into this repo.
- Week14 schema freshness check passed on 2026-05-28: `docs/audits/2026-05-28-week14-schema-freshness-check.md`.
- Week14 current app/docs/tests were inspected as the source implementation.

## Reuse Categories

### Copy Mostly As-Is

These should be copied from Week14 with minimal structural change, then reconfigured/rebranded:

| Week14 area | Why | To Quran changes |
| --- | --- | --- |
| Laravel 12 app foundation, bootstrap/config structure | Mature app base; `toquranapp` is empty | app name, URL, mail, branding, env examples |
| Jetstream/Fortify auth foundation | Needed for parent/student/teacher/admin accounts | To Quran copy and account emails |
| Spatie Permission setup | Week14 role model fits app roles | confirm To Quran roles: super_admin/admin/customer_support/teacher/parent/student |
| Manual DB workflow docs and `database/manual` pattern | Matches safety requirements | already scaffolded; continue To Quran-specific |
| Workflow docs, plans/archive style, sprint roadmap style | User asked to reuse workflow system | keep To Quran business names and DB ownership |
| Family Workspace/account lifecycle services | Strong fit for parent/student transfer and activation | replace Week14 copy; do not depend on old To Quran client rows |
| Protected file/attachment delivery pattern | Needed for Library and tasks | update app branding and content taxonomy |
| Test harness patterns and business-flow tests | Prevents regressions during adaptation | rename factories/fixtures and adjust service data |

### Adapt

These are high-value but need To Quran business adaptation:

| Week14 area | Adaptation needed |
| --- | --- |
| Booking/intake/admin review | Map public service interests; remove Week14/IB assumptions; app owns review-first and transfer rules |
| Parent/student/teacher/admin dashboards | Preserve role boundaries; rebrand and reduce Week14 academic wording |
| Sessions and normal tasks | Use for Quran Memorization and Quranic Arabic tutoring; update subject/service defaults |
| Task approval workflow | Keep parent/teacher approval mechanics; adapt labels and My Deen Journey flows |
| Journey | Convert into My Deen Journey service experience, not a rename-only change |
| Rewards and behavior/discipline points | Convert to rewards, behavior/accountability points, and consequence agreements |
| Versioned Routines | Useful for recurring Quran/Arabic/My Deen routines; label and scope carefully |
| Differentiated Tasks | Useful for learner-specific work where tutoring levels differ |
| Series Tasks | Useful for sequenced Quran revision, Arabic lessons, memorization pathways, or My Deen routines |
| Library resource foundation | Reuse protected Library/resource model; replace old English/SAT sources with Quran/Arabic taxonomy over time |
| Academic structure | Preserve hidden class/subject/year foundations where useful; avoid exposing IB school wording by default |

### Rename/Rebrand

| Week14 term/source | To Quran direction |
| --- | --- |
| Week14 | To Quran |
| `week14-app-lms` | `toquranapp` / app.toquran.org |
| IB Private Tutoring | Not a To Quran label |
| Language and Literature default framing | Quranic Arabic / Arabic reading where applicable |
| Journey | My Deen Journey service experience |
| Discipline points | Behavior/accountability points where owner approves |
| Automated Tasks | Confirm label: Automated Tasks, Routines, or Journey Routines |
| W14 booking reference prefix | To Quran-specific prefix, likely `TQ-`, after approval |
| support/from names | To Quran Islamic Services / To Quran Academy as approved |

### Skip Or Defer

| Week14 area | Reason |
| --- | --- |
| English vocabulary games / Cambridge / phonics P7 import | Arabic vocabulary games are post-deployment deferred |
| SAT, Grammar, Notice & Note, Background English content | Not first-import Quran/Arabic scope |
| Week14 public website docs/sprints copied verbatim | To Quran needs separate product decisions |
| Old Week14 QA accounts and test data | Must not become To Quran data |
| Week14 sprint order after P7 | Roadmap must be To Quran-specific |
| Laratrust legacy tables from old To Quran export | Cleanup-risk; review only |
| Employee quiz/course old tables | Old/export-only evidence, not first app import |

### New To Quran-Specific Code/Docs Needed

| Area | Why |
| --- | --- |
| To Quran service catalog and mapping | Current website service interests do not equal Week14 service values |
| Public intake adapter/handoff | Website currently writes old booking rows and hardcodes W14/Zoom behavior |
| Quran/Arabic subject taxonomy | Week14 subject defaults are not To Quran-ready |
| My Deen Journey service boundary | Needs Quran/Salah/adab/home-habit framing and parent involvement rules |
| Old To Quran data classification | Owner clarified there is no client data to preserve; old rows remain export evidence unless a later plan says otherwise |
| Quran YouTube/video Library migration plan | Old export contains Quran video list data that should move later into Library/content |
| Arabic vocabulary game roadmap | Future architecture should reuse Week14 P7 patterns but with Arabic data/game rules |

## Recommended Implementation Phases

### Phase-To-Sprint Cross-Reference

Use this table when coordinating the import plan with `docs/TOQURAN-SPRINTS.md`.

| Import plan phase | Sprint roadmap item | Notes |
| --- | --- | --- |
| Phase 0 - Owner Review | TQ0 | Complete; audit and selective reuse strategy are approved for Phase 1. |
| Phase 0.5 - Week14 Schema Freshness Gate | TQ0.5 | Complete; schema snapshot freshness is verified. |
| Phase 1 - App Skeleton Import | TQ1 | Next implementation phase. |
| Phase 2 - Schema Baseline And Data Mapping Plan | TQ1 entry/exit gate before TQ2 | Establish the app DB target and schema plan before intake/family adaptation. |
| Phase 3 - Intake And Family Foundation | TQ2 and TQ3 | Split implementation work by service catalog/intake first, then family lifecycle. |
| Phase 4 - Core Tutoring LMS | TQ4 | Sessions, task flows, protected attachments, approval, rewards, and behavior/consequence foundations. |
| Phase 5 - My Deen Journey | TQ5 | Service-specific adaptation after core tutoring surfaces exist. |
| Phase 6 - Library And Automation | TQ6 and TQ7 | Split implementation work by Library/content foundation first, then routine/series automation. |
| Phase 7 - Deferred Arabic Vocabulary Games | TQ8 | Post-deployment planning item. |

### Phase 0 - Owner Review

Review this plan and decide:

- confirm reuse categories
- confirm first import excludes vocabulary games
- confirm app schema should be based on Week14 current LMS schema
- confirm public website is not changed until app target is approved

### Phase 0.5 - Week14 Schema Freshness Gate

Before using the 2026-05-27 Week14 schema snapshot as the To Quran baseline source:

- create a fresh read-only schema export from local `u504065335_vuexy_week14`
- compare it against `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- review Week14 manual patches after the last durable Week14 baseline
- classify patches as represented in fresh schema, data-only, verification/preflight only, or not applied/uncertain
- update this plan with caveats before app code is copied

Status: complete on 2026-05-28.

Result:

- fresh export: `database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql`
- report: `docs/audits/2026-05-28-week14-schema-freshness-check.md`
- comparison: 352 tables in both snapshots; no missing, added, or changed `CREATE TABLE` blocks after normalization
- caveat: the snapshot proves structure, not Week14 data-only patches; To Quran roles, permissions, service rows, starter data, and any content imports must be created intentionally

### Phase 1 - App Skeleton Import

Copy/adapt Week14 Laravel 12 app foundation into `toquranapp`:

- composer/package/vite/config/bootstrap
- app providers/auth/permissions/layout foundation
- workflow docs/templates/manual DB dirs
- no DB import
- no public website edits

Verification:

- app boots locally after To Quran `.env` setup
- no Week14 brand in visible first-run areas
- no DB destructive commands

### Phase 2 - Schema Baseline And Data Mapping Plan

Entry gate:

- establish and document the To Quran local/app target DB name and connection before executing schema setup
- confirm the target is not the public/live website DB
- confirm backup/export evidence is still present

Prepare manual SQL/migration notes:

- To Quran app schema baseline derived from Week14 current schema
- service catalog/value patch
- role/permission patch
- later preservation mapping for the old Quran YouTube/video list into Library/content
- no preservation dependency for old users/students/bookings/contact rows unless a later owner decision changes the scope

Verification:

- target DB and backup/export evidence are confirmed before execution
- cleanup plan remains separate

### Phase 3 - Intake And Family Foundation

Adapt Week14 booking/intake/family lifecycle:

- service-interest mapping
- review-first rules
- parent/student transfer
- Family Workspace
- activation and account history

Website follow-up:

- plan public form write-path changes after app target exists

### Phase 4 - Core Tutoring LMS

Adapt Week14:

- teacher/student/parent sessions
- normal task flows
- protected attachments
- task approval
- rewards/behavior/consequence agreements

### Phase 5 - My Deen Journey

Adapt:

- Journey UI/flow
- routines and task journey
- rewards/accountability/consequences
- parent progress follow-up

### Phase 6 - Library And Automation

Adapt:

- Library resource foundation
- Versioned Routines
- Differentiated Tasks
- Series Tasks
- Quran/Arabic content taxonomy

### Phase 7 - Deferred Arabic Vocabulary Games

Plan after deployment:

- Arabic vocabulary data model
- game types
- teacher/student access
- whether to reuse Week14 P7 vocabulary architecture

## DB Plan

Do not execute as part of this correction pass. For later Phase 2 DB work, Codex may execute To Quran local/app DB setup and schema work without separate owner approval when `docs/DB-SAFETY-POLICY.md` target checks pass.

1. Keep `u504065335_to_quran` export as preservation source.
2. Treat the matched Week14 live schema snapshot as source schema evidence after the completed Phase 0.5 freshness gate.
3. Create a To Quran app schema plan from Week14 after To Quran adaptation decisions are accepted.
4. Map old data:
   - Quran YouTube/video list -> later Library/content migration candidate
   - old bookings/contact/users/students -> no intentional preservation currently required
5. Create cleanup documentation before destructive cleanup of old/export-only data.

## Public Website Handoff Plan

After app schema target is approved:

- change `W14-` reference prefix
- remove public hardcoded confirmed meeting link behavior
- align service values with shared docs
- decide whether public writes directly to app-owned DB tables or calls an app-owned endpoint
- add/consume shared docs in the public repo

## Verification For This Planning Task

Done when:

- shared docs exist
- audit doc exists
- reuse/import plan exists
- backup/baseline evidence exists
- no app import was performed
- no DB mutation was performed
