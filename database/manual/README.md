# To Quran Manual Database Artifacts

This directory stores DB evidence and owner-reviewed manual SQL for the To Quran app/LMS.

## Directory Layout

```text
database/manual/
|-- baseline/   # structure-only schema snapshots
|-- backups/    # full exports or backup copies used for audit safety
`-- patches/    # owner-reviewed manual SQL or SQL notes
```

## Current Audit Artifacts

- `backups/2026-05-27-235118-u504065335_to_quran-export.sql`
  - backup copy of the public To Quran SQL export found at `D:\xampp\htdocs\toquran\u504065335_to_quran.sql`
- `baseline/2026-05-27-235118-week14-live-schema.sql`
  - read-only structure snapshot of local Week14 live schema `u504065335_vuexy_week14`
- `baseline/2026-05-28-001530-week14-fresh-schema.sql`
  - fresh read-only Week14 schema export used by `docs/audits/2026-05-28-week14-schema-freshness-check.md`

## Current Local App Target

- Phase 2 local/app DB target: `toquranapp_local`
- Public/live website DB name to avoid for app baseline work: `u504065335_to_quran`

## Rules

- Do not edit baseline snapshots to create schema changes.
- Codex may execute To Quran local/app DB setup and schema work when `docs/DB-SAFETY-POLICY.md` target checks pass.
- Do not execute patches against public/live website DB targets.
- Add cleanup SQL only after cleanup intent and target data have been documented.
- Prefer MariaDB-compatible SQL because the local XAMPP Week14 baseline is MariaDB-family and Week14 manual patches target MariaDB compatibility.
