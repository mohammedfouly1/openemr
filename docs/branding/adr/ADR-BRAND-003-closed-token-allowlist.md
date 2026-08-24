# ADR-BRAND-003: Closed 11-key tenant-overridable token allowlist

**Status:** Accepted and implemented.

## Context

Locked **Invariant 9** requires that a tenant must never be able to supply arbitrary CSS or JavaScript.
Constraint **C1** (`docs/RebrandingPlan.md` §2.1) restates this as "no free-text tenant input... must be
enforceable as more than documentation." Separately, the design token set includes colours with a clinical
meaning — success, warning, critical, and info states — where the *meaning* a clinician has learned to
associate with a colour must hold regardless of which tenant they are viewing.

An allowlist expressed only as documentation ("tenants may only change these colours") is a policy that a
future change can violate by accident — adding a new token key and forgetting to also exclude it from
tenant overlays is exactly the kind of omission code review reliably misses over time.

## Decision

`TokenKey` (`interface/modules/custom_modules/oe-module-thiqa-branding/src/Token/TokenKey.php`) is a
**backed PHP enum** with 43 total cases, drawn one-for-one from `brand/tokens/thiqa-tokens.json`. There is
no code path that constructs a `TokenKey` from an arbitrary string beyond `TokenKey::tryFrom()`, which
returns `null` for anything outside the 43 declared cases — turning "the allowlist" from a claim into a
structural fact: a string a tenant supplies either resolves to one of 43 known enum cases or resolves to
nothing at all, with no third option.

Of those 43, exactly **11** return `true` from `TokenKey::isTenantOverridable()`
(`TokenKey.php:161-209`): the five `interactive.primary.*` keys, three `interactive.secondary.*` keys,
`interactive.focusRing`, and `link.default`/`link.hover`. Every other key — brand identity colours (7),
structural surfaces (6), borders/dividers (3), text colours (4), and all four semantic/clinical-safety
colour groups times three roles each (12) — returns `false` and cannot be moved by a tenant overlay under
any payload.

Exactly **10 of the 11** tenant-overridable keys carry WCAG 2.2 contrast rules.
`interactive.primary.disabled` deliberately remains overridable but has no SC 1.4.3/1.4.11 gate because
inactive controls are exempt. It is governed by a separate product rule in `TokenValidator`: the disabled
fill must retain at least **1.5:1 luminance separation** from both the enabled primary fill and the page
background. The distinction is intentional — this floor prevents a disabled control collapsing into an
enabled control or the canvas, but it is not represented as a WCAG requirement. The component rule retains
the fixed Bootstrap disabled opacity independently of the tenant colour.

The enum's own docblock states the reasoning for the semantic exclusion explicitly, in terms a future
maintainer changing this file needs to see before touching it (`TokenKey.php:154-159`):

> "semantic.{success,warning,critical,info}.* are NON-OVERRIDABLE FOR CLINICAL SAFETY. Status colour is a
> clinical signal, not decoration... A tenant that could recolour 'critical' toward its brand palette — or
> swap the success and warning hues — would be changing the meaning of an alert, not its styling. This one
> is a patient-safety boundary and is not negotiable per tenant."

Even within the 11 overridable keys, a value only reaches CSS after passing `TokenValidator` (format:
`#RRGGBB` only), then either its applicable WCAG contrast pair or the disabled-state product rule — both
re-run **tenant-side**, inside `BrandingMaterialiser`, regardless of what the Control Plane already
validated, because "the tenant does not trust the Control Plane blindly" (`docs/RebrandingPlan.md` §3.9).

## Consequences

- **A "different theme entirely" for one tenant is not representable in this model**, by construction —
  a tenant can move 11 named colours within their validated bounds and nothing else
  (`docs/branding/multi-tenant-white-label-readiness.md` §3.1).
- **Per-tenant custom fonts are explicitly out of scope** and the plan itself flags this as needing a *new*
  ADR before attempting (`docs/RebrandingPlan.md` §3.10, "Per-tenant custom fonts | No — deliberately |
  Would breach the shared-immutable-bundle rule (C2); requires a new ADR"). This ADR does not attempt that
  work; it only documents why the 11-key colour allowlist stops where it does.
- **Verified, not just designed:** the isolated `Token` PHPUnit suite (393 tests, 1,067 assertions) and the
  isolated `Accessibility`/WCAG suite (110 tests, 264 assertions) both pass
  (`docs/branding/remaining-dependencies.md` A3, V-05). A4 (guard against any tenant-uploaded CSS/JS
  executing) is independently confirmed by the PHPStan guardrail-rule test suite (54/54 pass) rather than
  by the token allowlist alone — the two mechanisms are complementary, not redundant.
- **Growing the allowlist is a deliberate, reviewable act**, not a default: adding a 12th overridable key
  requires touching `isTenantOverridable()`'s `match` expression directly (PHPStan level 10 requires
  exhaustive `match` arms with no `default`, per this repo's own coding standard — CLAUDE.md, "Exhaustive
  Matching"), so PHPStan itself would flag an incomplete match if a new `TokenKey` case were ever added
  without an explicit overridability decision for it.

## References

- `interface/modules/custom_modules/oe-module-thiqa-branding/src/Token/TokenKey.php:17-32` (allowlist rationale), `:146-209` (`isTenantOverridable()`)
- `docs/RebrandingPlan.md` §2.1 (C1), §2.4 (two-tier token model), §3.9 (threat model, tenant-side re-validation)
- `docs/branding/multi-tenant-white-label-readiness.md` §1.6, §3.1
- `docs/branding/remaining-dependencies.md` §2, rows A3/A4, V-05
