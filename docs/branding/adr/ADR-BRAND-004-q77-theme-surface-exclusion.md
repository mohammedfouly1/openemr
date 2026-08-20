# ADR-BRAND-004: Restrict the build surface, don't delete or hide the surplus themes

**Status:** Accepted and implemented; guarded by a test.

## Context

Locked `Q77` requires exactly two selectable Saudi theme variants (light + dark, RTL-capable), with the
four surplus upstream themes (`solar`, `manila`, `cobalt_blue`, `forest_green`) **absent from the build
output**, not merely hidden in the UI. Constraint **C3** restates this (`docs/RebrandingPlan.md` §2.1: "no
core patch"). Two more constraints shape the option space: `Q1`/`Q2` (rebase compatibility — deleting
upstream source files widens every future rebase diff for no functional gain), and Invariant 4 (prefer a
non-core mechanism).

Three ways to get to "an administrator only sees two themes" were available:

1. Delete the four surplus themes' SCSS source files from the repository outright.
2. Keep the source files, but patch `interface/super/edit_globals.php`'s theme-selector dropdown to filter
   them out of the admin UI.
3. Keep the source files, but remove their entries from `webpack.themes.js`'s build entry map, so no
   compiled CSS output exists for them at all.

Option 1 maximises rebase conflict surface for a purely cosmetic goal (a future `git rebase` onto an
upstream commit that modifies `style_solar.scss` would conflict against a file this fork deleted, for a
theme this product never serves). Option 2 is a core-file edit that satisfies the UI-selector half of `Q77`
but not the "absent from build output" half — a stale `globals.theme` value pointing at a
`style_solar.css` the build never produced would need separate handling, and the surplus CSS would still
exist and be servable by URL even if the dropdown didn't offer it.

## Decision

**Option 3: exclude the four surplus themes from `webpack.themes.js`'s entry map (16 individual entries:
each of the 4 themes × 4 build variants — plain/compact/RTL/RTL-compact), and leave their SCSS source
files in the repository unmodified** (`docs/RebrandingPlan.md` §3.7.2).

This closes both halves of `Q77` without a single core-file edit:

- **"Absent from build output"** is satisfied directly — no webpack entry means no compiled
  `style_solar.css` (etc.) is ever written to `public/themes/`.
- **"Not selectable"** is satisfied as a side effect of pre-existing, unmodified core behaviour:
  `interface/globals.php:474-483` gates theme selection on `file_exists()` against `public/themes/`. A
  stale `globals`/`user_settings` value naming a removed theme therefore falls back to `style_light.css`
  automatically — the existing gate enforces the two-variant surface with zero new code
  (`docs/RebrandingPlan.md` §3.7.2: "the existing gate enforces the two-variant surface with no core
  patch, which is precisely why `Q77` chose this option").

The retained SCSS source (`style_solar.scss`, `style_manila.scss`,
`interface/themes/colors/style_cobalt_blue.scss`, `style_forest_green.scss`) stays in the tree, unused as a
build entry, purely for future-rebase compatibility — exactly `Q77`'s stated allowance
(`docs/RebrandingPlan.md` §3.7.2, "Upstream... remain in the repository for rebase compatibility, exactly
as `Q77` permits; only the build output is constrained").

**Guarded by CI, not by convention.** `BrandingGovernanceGuardTest` asserts the entry map contains exactly
the approved set — "8 required and 16 prohibited [webpack] entries"
(`docs/branding/coverage-matrix.md` row 10, citing `docs/AuditRebranding.md:1130`) — so a future upstream
theme, or a future merge that reintroduces one of the four prohibited entries, fails a test rather than
silently shipping.

## Consequences

- **Confirmed implemented and re-verified live in this session's predecessor pass**: `public/themes/` holds
  exactly the approved 17-file set (light/dark × plain/compact/RTL/RTL-compact + PDF/RTL-PDF + tabs
  variants + 3 non-theme shells), zero `solar`/`manila`/`cobalt_blue`/`forest_green` output files
  (`docs/branding/remaining-dependencies.md` row 10/43).
- **The admin theme-selector label wording (D-15) is CLOSED, and this section's original claim about it
  was wrong — corrected 2026-08-19.** This paragraph originally asserted the selector shows generic
  "Light"/"Dark" labels rather than "Saudi Light"/"Saudi Dark". Independent re-verification in
  `docs/branding/remaining-dependencies.md` (D-15 row) found the opposite directly in source:
  `ThemeVariant.php:46-47`'s `label()` method literally returns `'Saudi Light'`/`'Saudi Dark'`. That
  finding was surfaced to the Owner as a genuine open decision rather than trusting this document's
  claim, and the **Owner ruled 2026-08-19: keep "Saudi Light"/"Saudi Dark" as shipped, no code change.**
  D-15 is therefore CLOSED, not open, and no label-text change is pending. See
  `docs/branding/remaining-dependencies.md` §4, D-15 for the full trace.
- **A stale `globals` value is handled gracefully, not defensively re-validated.** This ADR relies on
  pre-existing `file_exists()` behaviour rather than adding a new guard against an invalid theme value —
  if that upstream gate's behaviour ever changes, this decision's "no core patch" property would need
  re-examination.

## References

- `docs/RebrandingPlan.md` §3.7.2 (`Q77` theme-surface enforcement), §2.1 (C3)
- `interface/globals.php:474-483` (unmodified `file_exists()` fallback gate)
- `webpack.themes.js` (entry map — 16 entries removed, 8 Thiqa entries added/repointed)
- `docs/branding/coverage-matrix.md` rows 10, 43
- `docs/branding/remaining-dependencies.md` row 10/43 (live re-verification)
