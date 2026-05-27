# To Quran Workflow

This is the default workflow for the To Quran app/LMS repo.

Use `docs/TOQURAN-SPRINTS.md` as the active To Quran roadmap. Do not assume a generic `docs/SPRINTS.md` file exists in this repo.

## Entry Points

Use these modes deliberately:

- **Audit**: inspect current repo, DB evidence, public website handoff, and Week14 source. Output findings and recommendations only.
- **Plan**: create an implementation plan in `docs/plans/active/`. Planning does not authorize implementation.
- **Implement**: copy/adapt Week14 or write To Quran-specific code only after explicit owner approval.
- **Review**: findings first, with file/line references where code exists.
- **Close**: verify code, docs, DB artifacts, public-site handoff, and tests before marking done.

## Cross-Repo Rule

`toquranapp` is the source of truth for the LMS schema, business logic, and shared operational decisions. The public website `toquran` must consume those decisions instead of inventing its own LMS schema or transfer rules.

Any change that affects both repos must update the relevant shared docs:

- `docs/shared/TOQURAN-DECISION-LOG.md`
- `docs/shared/SHARED-DB-HANDOFF.md`
- `docs/shared/INTAKE-TO-APP-HANDOFF.md`
- `docs/shared/TERMINOLOGY-AND-SERVICES.md`
- `docs/shared/DEPLOYMENT-AND-WORKFLOW-HANDOFF.md`

## Reuse Workflow

Before writing new app code:

1. Inspect the Week14 implementation for the same domain.
2. Classify the module as copy mostly as-is, adapt, rename/rebrand, skip/defer, or new To Quran code.
3. Check DB dependencies and manual SQL trail.
4. Check public website impact.
5. Write or update a plan before implementation.

## Plan Requirements

Each plan must include:

- objective
- sprint/roadmap relationship from `docs/TOQURAN-SPRINTS.md`
- current evidence
- Week14 reuse source files/modules
- To Quran-specific changes
- DB impact and backup/baseline evidence
- public website handoff
- test/verification scope
- explicit non-goals

## Closure Requirements

Before declaring an implementation done:

- Verify all planned files exist and match the approved scope.
- Run focused tests where code exists.
- Confirm manual SQL artifacts exist for DB changes.
- Confirm public website docs/implementation impact is recorded.
- Update shared docs if any service, intake, terminology, DB, deployment, or workflow decision changed.
- Do not close by intent or by partial evidence.
