# ADR-BRAND-001: Five-plane, extension-points-only branding architecture

**Status:** Accepted and implemented (`feat/thiqa-branding-foundation`, commits `a1c22b6a1`…`c6c3f9e6e`).

## Context

Locked **Invariant 4** ("prefer configuration / assets / modules / upstream PRs over core edits") and
constraint **C4** (`docs/RebrandingPlan.md` §2.1) required a rebranding layer that attaches to OpenEMR
through published extension points wherever one exists, and treats any change to a tracked core file as a
last resort requiring its own numbered record (principle P3, §3.1). At the same time, constraint **C5**
required that the Control Plane (the eventual source of truth for tenant branding, `MVP-014`) never be
reachable from the request path (`Q76`), and constraint **C1** required that no free-text tenant input
could ever become CSS, JS, or a `sites/<site>/config.php` read (Invariant 9).

A single, undifferentiated "branding service" could not satisfy all three simultaneously — nothing would
stop a future change from letting a request-path class import a Control-Plane-facing HTTP client, or from
a materialisation routine drifting into being reachable from a controller.

## Decision

Split the branding layer into **five planes with one-directional dependencies** (`docs/RebrandingPlan.md`
§3.2, reproduced in `docs/branding/multi-tenant-white-label-readiness.md` §1.7 as "what is running today"):

1. **Plane 1 — Authority.** The Control Plane (PostgreSQL, `MVP-014`, not yet built — D-5). Validates a
   tenant's branding intent and assigns a revision. Out of this repository's scope.
2. **Plane 2 — Materialisation.** `BrandingMaterialiser` and the `thiqa-branding:materialise` /
   `thiqa-branding:apply-profile` CLI commands. Out-of-request, tenant-scoped, idempotent. The *only*
   plane permitted to write to `globals` or the token/asset files.
3. **Plane 3 — Runtime resolution.** `BrandingService`, reading only the already-loaded `globals` bag and
   the filesystem. Zero network calls, zero additional DB queries.
4. **Plane 4 (a–d) — Delivery.** Four independent attachment surfaces off Plane 3: HTML-head style
   injection (`StyleFilterEvent`), Twig template/namespace overrides, logo slot filtering
   (`LogoFilterEvent`), and machine contracts (SMART style JSON, FHIR metadata).
5. **Plane 5 — Shared immutable bundle.** The compiled Thiqa theme CSS and fonts, built once at deploy
   time from `brand/tokens/*.json`, identical for every tenant.

Enforced by a PHPStan rule (`docs/RebrandingPlan.md` §3.2, "Dependency rule... enforced by a PHPStan rule,
§4.3 WP-2.7"): Plane 3 may not reference Plane 1 or Plane 2 classes, and Plane 2 must not be reachable from
any web entry point.

### The Plane 4a token-CSS delivery mechanism (superseding a stale line elsewhere in the plan)

`docs/RebrandingPlan.md` §3.2.2 initially favoured writing the per-tenant token CSS to a static file at
materialisation time ("option (b)"), but was **explicitly revised on 2026-08-09 (CR-19)** to recommend
"option (a)" instead: a module PHP endpoint (`.../oe-module-thiqa-branding/public/branding-tokens.php`)
that reads the already-loaded `globals` and emits `text/css` directly, with an immutable,
revision-keyed `Cache-Control` header. This requires **no writable directory** in the deployed image, which
matches the read-only-container assumption the plan's supply-chain model depends on. Option (b) (a static
file written under the module's `public/` directory) is retained only as a fallback for platforms that
require a genuinely static asset.

**Note on a stale cross-reference in the plan itself.** `docs/RebrandingPlan.md` §3.11 (deliverable
"D1.1... `ADR-BRAND-001`") still describes this ADR as recording "option (b)" — that line predates the
CR-19 revision two sections earlier (§3.2.2) and was never updated after the revision. This ADR records
the decision that was actually implemented and shipped: **option (a)**, confirmed directly from the shipped
code by `docs/branding/multi-tenant-white-label-readiness.md` §1.7 ("the response is immutable and
revision-keyed... nothing here writes to disk"). A future editor of `RebrandingPlan.md` §3.11 should
correct "option (b)" to "option (a)" to match this record and the shipped implementation.

> **Correction 2026-08-10 (`docs/RebrandingBugs.md` RB-04) — both routes are live, so "no writable
> directory" is not true of the shipped system.**
>
> The claim above is correct about the **serving** path: `StyleInjectionListener` emits a `<link>` to the
> PHP endpoint, and that endpoint writes nothing. But `TokenCssWriter` is also wired into
> `BrandingMaterialiser` and *simultaneously* implements option (b), writing
> `…/oe-module-thiqa-branding/public/branding/<site>/tokens-{light,dark}.css` on every materialisation.
> Observed after a real run: both files present, 1,553 B and 1,522 B, timestamped 2026-08-10 18:50.
>
> Three consequences, stated rather than smoothed over:
>
> 1. **Dependency D-8 (writable, execution-denied volume) is NOT eliminated.** The deployed image needs
>    that directory writable. `RebrandingPlan.md` §6.5 and `docs/branding/remaining-dependencies.md` both
>    record D-8 as "RESOLVED by design change"; that is accurate for the *recommended* route and inaccurate
>    for the *shipped* system.
> 2. **Nothing serves those files.** The only reader is `FilesystemStylesheetProbe`, i.e. the health check
>    — so they are write-only artefacts, and `thiqa-branding:verify` reporting "Light token stylesheet:
>    present / healthy" describes a file that is never on the serving path.
> 3. **They were written even with an empty Tier-2 overlay**, contradicting §3.2.2's explicit rule that
>    "when Tier 2 is empty (the default) no file is emitted and no `<link>` is added at all". With
>    `saas_branding_tokens_light`/`_dark` both `''`, the emitted files contain only the Tier-1 product
>    palette that the compiled bundle already carries.
>
> **Fixed so far:** the directory is now git-ignored (`.gitignore`), so tenant runtime state can no longer
> be committed into the source tree by a routine `git add -A`.
>
> **Still open — a design decision, not a defect to patch silently:** whether to keep (a) and make (b)
> opt-in, or keep both and re-open D-8. Until that is decided, treat D-8 as **OPEN** and do not cite "no
> writable directory" as a property of the shipped image.

### The new-entry-file theme strategy

Rather than editing the four surplus upstream theme SCSS files in place, the Thiqa theme ships as
**wholly new SCSS source files** (`interface/themes/thiqa/*.scss`,
`interface/themes/oe-styles/style_thiqa_{light,dark}.scss`) referenced by new `webpack.themes.js` entries
that repoint the existing `style_light`/`style_dark` output filenames at the new sources. The four surplus
upstream themes (`solar`, `manila`, `cobalt_blue`, `forest_green`) are removed from the entry map (not
deleted from the repository — see ADR-BRAND-004) rather than being overwritten. This keeps the diff against
upstream confined to `webpack.themes.js`'s entry map (a small, mechanically-mergeable change) instead of
touching upstream theme source files line-by-line.

## Consequences

- **127 of 136 BRAND IDs route through non-core mechanisms** (`docs/RebrandingPlan.md` §2.1, C4 row); the
  residual mandatory core-edit set is enumerated and justified individually — see
  `docs/branding/adr/patch-records.md`.
- Every Plane 2/3 boundary violation is a static-analysis failure, not a code-review judgment call —
  confirmed passing in this session's isolated PHPStan suite
  (`docs/branding/changes.md` row 120: `ForbiddenBrandingSiteConfigRule` test, 10/10 pass) and by direct
  grep finding zero HTTP-client or `sites/*/config.php` references in the module's render-path source
  (`docs/branding/remaining-dependencies.md` V-01, V-08).
- The plane split makes "is this code allowed to be slow / allowed to touch the network / allowed to write"
  a structural question answered by which namespace a class lives in, not a comment or a convention.
- **Trade-off accepted:** Plane 1 (the Control Plane) does not exist yet (D-5, open). Planes 2–5 were built
  and tested against a stand-in (`Console\JobPayload` hand-authored files) rather than a real CP
  integration. This is a known, documented gap — see
  `docs/branding/multi-tenant-white-label-readiness.md` §3.2 — not an oversight of this ADR.

## References

- `docs/RebrandingPlan.md` §3.1 (principles P1–P8), §3.2 (five planes), §3.2.1 (evidence), §3.2.2 (Plane 4a
  option revision, CR-19), §3.11 (D1.1–D1.8 deliverables)
- `docs/branding/multi-tenant-white-label-readiness.md` §1.7
- `docs/branding/changes.md` (PROHIBITED / BRAND-120 row; NO-ACTION rows 023/024)
