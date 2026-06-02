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

## Local Server Rule

Do not reuse the same local web port across the connected repos:

- To Quran app: `http://127.0.0.1:8014`
- Week14 LMS: `http://localhost:8000`
- Yonfiqoon: `http://127.0.0.1:8011`

For this repo, prefer:

```powershell
php artisan serve --host=127.0.0.1 --port=8014
```

If `8014` is already in use, identify what owns the port before choosing a temporary alternate and note the alternate in the task closure.

## Reuse Workflow

Before writing new app code:

1. Inspect the Week14 implementation for the same domain.
2. Classify the module as copy mostly as-is, adapt, rename/rebrand, skip/defer, or new To Quran code.
3. Check DB dependencies and manual SQL trail.
4. Check public website impact.
5. Write or update a plan before implementation.

## Frontend Form Control Rules

For any new or changed form UI, review mobile/tablet behavior for native `select` controls, `input[type="date"]`, and picker widgets such as Flatpickr before closing the task.

- Long `select` menus and date pickers can overflow narrow phone/tablet viewports. On mobile/tablet, use the shared responsive-control pattern or an equivalent contained custom UI instead of letting the native control spill outside the viewport.
- Do not change backend contracts while improving the UI. The real field must remain in the form with the same `id`, `name`, value, validation attributes, disabled/readonly state, and submitted payload.
- Custom controls must write to the real field and dispatch real DOM `input` and `change` events on that field so Livewire, Alpine, browser validation, and normal form submission all stay aligned.
- Custom menus/calendars must stay inside their parent width using `left: 0; right: 0`, mobile-safe max height, and internal scrolling.
- Dynamic rows, modal content, and Livewire-rendered fields must be enhanced after insertion and cleaned up if the field, row, modal, or form is removed.
- Server validation UI must stay visually attached to the visible control. If a native field is hidden/enhanced and has adjacent `.invalid-feedback`, move or mirror the invalid state so the visible custom trigger/input is red and the feedback appears below it.
- Flatpickr fields inside `wire:ignore` need special review. If the real input has an `@error(...) is-invalid @enderror` class but Flatpickr renders an `altInput`, mirror the invalid state to the visible `altInput`, and clear it when the user selects a valid value.
- Submit-time focus/scroll should target the visible custom trigger/input, not a hidden native field.
- Desktop should keep native behavior unless the task explicitly calls for a desktop redesign.

Minimum verification for these changes:

- Search changed views for native `<select>` and `input[type="date"]` controls.
- Test one normal form select, one select inside a modal or Livewire section, one date/picker field, and one dynamic row if the page has add/remove rows.
- Confirm real field values update, submitted names/values are unchanged, validation errors display below the visible control, invalid states clear after a valid selection, and date picker previous/next navigation works on touch/mobile.
- Rebuild assets when the implementation touches Vite-managed JS/CSS, and confirm manifests point to generated files that exist in both `public/build/` and `build/` while legacy root-build sync is still enabled.

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
- For frontend form-control work, complete the Frontend Form Control Rules verification above, especially mobile/tablet dropdown/date overflow and `wire:ignore` picker validation.
- Confirm manual SQL artifacts exist for DB changes.
- For DB baseline/replay work, complete the DB Review Checklist below before asking for external review.
- Confirm public website docs/implementation impact is recorded.
- Update shared docs if any service, intake, terminology, DB, deployment, or workflow decision changed.
- Do not close by intent or by partial evidence.

## DB Review Checklist

Before calling DB baseline, import, correction, or starter-data work review-ready:

- Compare framework-owned tables against expected Laravel/Sanctum/Spatie shape, including primary keys, unique keys, lookup indexes, foreign keys, and auto-increment columns.
- Scan schema snapshots and patches for suspicious identifiers, including leading/trailing spaces and accidental embedded spaces inside backticked column/table names.
- For fixed-ID starter/reference rows, add fail-fast drift checks before inserts so conflicting pre-existing IDs abort instead of silently preserving wrong mappings.
- For real-target patches, require an explicit operator confirmation variable or equivalent instance-level guard in addition to `DATABASE()` checks.
- For preservation-only SQL, make accidental execution inert by default, or document why that cannot be done.
- Re-run idempotent patches after execution where practical, and verify both the success path and the expected guard-failure path.
- Export or update a post-correction schema snapshot after live DB corrections, and make the replay order explicit in `database/manual/README.md`.
- Record the result in `docs/shared/SHARED-DB-HANDOFF.md`, with wording that distinguishes local completion from committed/finalized work.
