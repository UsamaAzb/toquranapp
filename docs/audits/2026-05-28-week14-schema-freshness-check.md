# Week14 Schema Freshness Check

Date: 2026-05-28
Status: complete for pre-implementation correction

## Purpose

Confirm whether the 2026-05-27 Week14 schema snapshot can be treated as the current source-schema evidence for To Quran planning before any app code import or To Quran DB work.

## Evidence

- Previous To Quran-held Week14 snapshot: `database/manual/baseline/2026-05-27-235118-week14-live-schema.sql`
- Fresh read-only export from local Week14 DB `u504065335_vuexy_week14`: `database/manual/baseline/2026-05-28-001530-week14-fresh-schema.sql`
- Week14 source repo reviewed: `D:\xampp\htdocs\week14-app-lms`
- Last durable Week14 baseline found in source repo: `D:\xampp\htdocs\week14-app-lms\database\manual\baseline\2026-04-18-sprint04-post-family-lifecycle.sql`

## Snapshot Comparison

The fresh read-only Week14 schema export was compared against the 2026-05-27 snapshot by parsing and normalizing `CREATE TABLE` blocks.

| Check | Result |
| --- | --- |
| 2026-05-27 snapshot table count | 352 |
| 2026-05-28 fresh export table count | 352 |
| Tables missing from fresh export | none |
| Tables added in fresh export | none |
| Changed `CREATE TABLE` blocks | none |

Conclusion: the 2026-05-27 Week14 schema snapshot matches the current local Week14 schema structure. It is acceptable as strong source-schema evidence for To Quran planning, with the data-only caveats below.

## Post-Baseline Patch Classification

These Week14 manual patches exist after the last durable Week14 baseline. Classification describes whether their result is represented in the fresh schema snapshot.

| Patch | Classification | To Quran caveat |
| --- | --- | --- |
| `2026-04-18-sprint04-db-health-check.sql` | verification/preflight only | Read-only health check; no To Quran schema impact. |
| `2026-04-18-sprint04-email-delivery-claims.sql` | represented in fresh schema | Table exists in the matched snapshot. |
| `2026-04-18-sprint04-family-lifecycle-schema.sql` | represented in fresh schema | Structure is represented; any data backfill assumptions need To Quran review. |
| `2026-04-18-sprint04-pre-existing-family-classification.sql` | data-only | Do not import Week14 account classification data. |
| `2026-04-18-sprint04-spatie-permissions.sql` | data-only | Role/permission seed data must be recreated for To Quran intentionally. |
| `2026-04-18-sprint04-spatie-permissions-repair.sql` | represented in fresh schema | Structural repair is represented; repair data should not be copied as Week14 data. |
| `2026-04-18-sprint04-spatie-permissions-repair-continuation.sql` | represented in fresh schema | Structural repair continuation is represented. |
| `2026-04-18-sprint04-spatie-unique-index-repair.sql` | represented in fresh schema | Unique-index state is represented; no Week14 rows should be imported blindly. |
| `2026-04-25-automated-tasks-preflight.sql` | verification/preflight only | Read-only/destructive-safety check; not a To Quran patch. |
| `2026-04-25-automated-tasks-schema.sql` | represented in fresh schema | Automated-task structure is represented. |
| `2026-04-25-automated-tasks-truncate.sql` | data-only | Week14 destructive rollout data action; do not replay for To Quran unless a To Quran cleanup plan requires it. |
| `2026-04-25-automated-tasks-verify.sql` | verification/preflight only | Read-only verification. |
| `2026-04-26-automated-tasks-pause-fence.sql` | represented in fresh schema | Pause-fence columns are represented. |
| `2026-04-30-differentiated-tasks-schema.sql` | represented in fresh schema | Differentiated-task structure is represented. |
| `2026-05-01-versioned-routine-multi-version-assignments.sql` | represented in fresh schema | Later uniqueness patch changes the final state; snapshot has the final state. |
| `2026-05-02-versioned-routine-restore-single-version-uniqueness.sql` | represented in fresh schema | Final uniqueness state is represented; preflight/delete portions are data cleanup caveats. |
| `2026-05-02-version-task-drop-attachment-choice.sql` | represented in fresh schema | Removed columns are absent in the final snapshot. |
| `2026-05-03-series-tasks-schema.sql` | represented in fresh schema | Series-task structure is represented. |
| `2026-05-04-parent-teacher-task-approval-workflow.sql` | represented in fresh schema | Approval-workflow structure is represented. |
| `2026-05-04-parent-teacher-task-approval-workflow-preflight.sql` | verification/preflight only | Read-only readiness check. |
| `2026-05-04-parent-teacher-task-approval-workflow-verify.sql` | verification/preflight only | Read-only verification. |
| `2026-05-05-parent-teacher-task-approval-workflow-data-cleanup.sql` | data-only | Cleanup rules may inform To Quran, but Week14 cleanup rows are not imported. |
| `2026-05-05-punishment-agreements-unique-student-type-title.sql` | represented in fresh schema | Unique index is represented; duplicate cleanup caveat is data-only. |
| `2026-05-07-student-gifts-academic-year-unique.sql` | represented in fresh schema | Unique index is represented; duplicate cleanup caveat is data-only. |
| `2026-05-08-reward-points-ledger-source-unique.sql` | represented in fresh schema | Unique index is represented. |
| `2026-05-09-p6-library-resource-foundation.sql` | represented in fresh schema | Library foundation tables/columns are represented; Week14 content rows are not To Quran content. |
| `2026-05-15-attachment-files-sort-order.sql` | represented in fresh schema | Attachment order column is represented; any row ordering backfill is data-only. |
| `2026-05-17-series-tasks-library-collection-type.sql` | represented in fresh schema | Final source-type flexibility is represented. |
| `2026-05-17-series-tasks-release-policy.sql` | represented in fresh schema | Release policy columns are represented. |
| `2026-05-18-series-task-version-items-source-type.sql` | represented in fresh schema | Final pathway source-type flexibility is represented. |
| `2026-05-18-whitespace-trim-audit-and-library-cleanup.sql` | data-only | Week14 cleanup data should not be copied; To Quran may need its own content cleanup later. |
| `2026-05-19-p7-vocabulary-intervention.sql` | represented in fresh schema | Vocabulary structure is represented, but Arabic vocabulary games are deferred and English content should not be imported in Phase 1. |
| `2026-05-23-p7-series-vocabulary-policy.sql` | represented in fresh schema | Series/vocabulary policy columns are represented; feature remains deferred for To Quran first import. |
| `2026-05-23-vocabulary-quality-quick-wins.sql` | represented in fresh schema | Quality columns are represented; Week14 English vocabulary data is not imported. |
| `2026-05-25-phonics-library-cleanup.sql` | data-only | Week14 phonics cleanup is not a To Quran Phase 1 import item. |
| `2026-05-25-phonics-library-cleanup-audit.sql` | verification/preflight only | Read-only audit. |
| `2026-05-26-ai-browser-qa-accounts.sql` | data-only | Week14 QA accounts must not be imported into To Quran. |

## Import Planning Caveats

- Use the matched Week14 snapshot as structural evidence only.
- Recreate To Quran roles, permissions, service catalog rows, and any starter data intentionally; do not copy Week14 production/test rows blindly.
- Keep English vocabulary, Cambridge, phonics, and Week14 QA data out of the first To Quran import.
- Preserve the old To Quran Quran YouTube/video list as a later Library migration item, not a Phase 1 schema/app import dependency.
- No app code import and no To Quran DB mutation were performed during this freshness check.
