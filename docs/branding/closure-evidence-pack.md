# MVP-010 / MVP-014 closure evidence pack

**Phase 5 deliverable F9** (`docs/RebrandingPlan.md` §7.1: "`MVP-010` / `MVP-014` closure evidence pack —
PR references, test evidence, security/tenant-isolation review, runbook links — the Global Definition of
Done in R2"). Written 2026-08-10 against branch `feat/thiqa-branding-foundation`.

**Purpose and honesty rule.** This document is a compilation, not new research — it indexes evidence
already produced in `docs/branding/changes.md`, `docs/branding/remaining-dependencies.md`,
`docs/branding/multi-tenant-white-label-readiness.md` and the Global Definition of Done in
`Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md`
(R2), and states plainly whether each closure criterion is met. Per Invariant 10 ("claims must describe
actual controls, not inferred capabilities") and this project's evidence discipline, criteria are marked
**MET** only where a citation supports it. Nothing here rounds up.

**Bottom line, stated first so it cannot be missed:** **MVP-010 and MVP-014 closure is NOT justified
today.** The branding *code* is substantially built and unit/isolated-tested, but the thing `Q76`
actually requires — a real, live token/logo materialisation executing against a tenant's database — has
never run, not even once, against the only tenant that exists on this system. Cross-tenant isolation
(A1/A2) has not been exercised at all, because no second tenant exists. Both are stated as open below,
not glossed over.

---

## 1. MVP-010 — "Implement safe tenant branding"

Acceptance criteria reproduced from R2 (`Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md:361-371`),
each cross-referenced to its actual evidence in `docs/branding/remaining-dependencies.md` (acceptance
tests A1–A8, checks V-01–V-10) and `docs/branding/multi-tenant-white-label-readiness.md`.

| # | Acceptance criterion (R2) | Status | Evidence |
|---|---|---|---|
| 1 | Tenant can change approved logo and tokenized palette only | **MET (source + isolated tests), NOT live-exercised** | `Token\TokenKey` is a closed 43-case backed enum; only 11 keys are tenant-overridable (`multi-tenant-white-label-readiness.md` §1.6). `TokenValidator`/`ContrastCalculator` gate every override. `Token` isolated suite: **393/393 tests, 1,067 assertions, OK** (`remaining-dependencies.md` A3). No live tenant overlay has ever been applied (see §4 below) |
| 2 | Invalid CSS/value payloads are rejected | **MET** | Same `Token` suite (A3, PASS): 393/393 tests, 1,067 assertions |
| 3 | No tenant-uploaded CSS/JS is executed | **MET** | A4 PASS — PHPStan guardrail suite `tests\Tests\Isolated\PHPStan\ThiqaBranding`: **54/54 tests, 80 assertions, OK**, covering `ForbiddenBrandingHttpClientRule`, `ForbiddenBrandingSiteConfigRule`, `ForbiddenBrandingTwigPathRule`, `ForbiddenBrandingPlaceholderDomainRule` |
| 4 | Cache keys/revisions prevent one tenant's branding from appearing in another tenant | **NOT MET — unexercised** | The mechanism (`BrandingRevision` in every branding URL) is built and code-reviewed, but A1/A2 (the acceptance tests that actually prove no cross-tenant bleed) are **BLOCKED**: `remaining-dependencies.md` §2 — "D-6 — `ls sites/` shows only `default`. Not attempted." See §4 below for the full tenant-isolation review |
| 5 | (Q76) No Control Plane network request during ordinary page rendering | **MET (source + static guardrail), no live network trace** | V-01 VERIFIED (source + static guardrail): `ForbiddenBrandingHttpClientRule` tests pass (same 54/54 as row 3); `sqlQuery`/`QueryUtils::` calls confined to the writer/CLI tier, not the render path. No instrumented page-load network capture was run (out of scope for a docs pass) |
| 6 | (Q76) Branding reaches the tenant only via tenant-scoped, idempotent materialisation; `globals` is a materialisation target, never authoritative | **PARTIAL — code proven, never run live** | V-02: `Materialisation` isolated suite 50/51 pass first run (1 timeout attributed to this host's Google-Drive-mount I/O contention, re-ran alone → 1/1 pass in 9.4s — see `remaining-dependencies.md` Surprise 7). **No live tenant has ever been materialised even once** (Surprise 8) — idempotence is proven only against test doubles, not a real `writeAll()` transaction |
| 7 | (Q76) CP unavailable → last-good branding retained; failed materialisation leaves the previous revision intact | **PARTIAL — code proven, live behaviour unexercised** | V-03: `MaterialiserKillRecoveryTest` passes in isolation. Live: `thiqa-branding:verify --site=default` reports `never materialised` / `revision 0` — there is no live revision n-1 to protect yet, so this criterion cannot be observed live until a first materialisation happens |
| 8 | (Q77) Deployed `public/themes/` holds only Saudi Light/Dark (+ required non-selectable artefacts); the 4 surplus themes absent/unselectable including via stale globals/user_settings | **MET** | V-04 partially, plus direct evidence: `webpack.themes.js` entry map restricted to the Q77 set, 17-18 compiled CSS files present, zero `solar`/`manila`/`cobalt_blue`/`forest_green` output files (`changes.md` BRAND-076, `remaining-dependencies.md` area #10/#43). The forced-stale-global fallback path itself (setting `css_header=style_solar.css` and confirming the fallback) was **not** live-exercised — avoided as a write against shared live state |
| 9 | (R-SMART-DARK) SMART style endpoint returns dark tokens for a dark theme | ~~**NOT MET**~~ → **MET in mechanism, not live-verified on this host** *(corrected 2026-08-10, RB-09)* | **This row's original conclusion was wrong.** It inferred "nothing returns dark tokens" from `smartStyleTokens()` having no caller — but that is not the delivery path. R-SMART-DARK is delivered by the **Twig template route**: `SMARTAuthorizationController::smartAppStyles()` (`:419-434`) composes `/api/smart/smart-<coreTheme>.json.twig` and dispatches `TemplatePageEvent` **unnamed**; `TwigOverrideListener::onTemplatePage()` (registered on `TemplatePageEvent::class`, `Bootstrap.php:125`) matches `oauth2/authorize/smart-style`, resolves the variant from `css_header`, and rewrites to `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig`. That file exists and carries genuinely dark values (`color_background: #0B1220`, `color_text: #F5F6F8`, `color_error: #F29088`). **Live verification is blocked on this host only** — `GET /oauth2/default/smart-style-url` returns 500 with `Unable to create/recreate oauth2 keys … OPEN_SSL: no such file`, the pre-existing `OPENSSL_CONF` environment quirk already recorded in `docs/rebranding.md` §11.2/§17.2, not a branding defect. What *is* genuinely true, and is a different and smaller finding: `smartStyleTokens()`/`SmartStyleContract` is a second, parallel implementation with no caller and no dedicated test — see RB-09 |

### 1.1 MVP-010 closure evidence checklist (R2 §Closure evidence)

| Item | Status | Detail |
|---|---|---|
| PR/commit or infrastructure change reference recorded | **Commits only, no PR** | See §3 below — 8 commits exist locally on this branch; none have been pushed to `origin`, and no PR has been opened. Do not represent this as "PR #NNN" anywhere else in this documentation set |
| Automated/manual test evidence attached | **Attached, see §2** | Real, cited pass counts — no estimates |
| Relevant security/tenant-isolation impact reviewed | **NOT MET** | See §4 — the cross-tenant guard is unit-tested against fakes only; no integration test with two real database connections exists |
| Documentation/runbook updated | **Partial** | `docs/branding/architecture.md`, `changes.md`, `remaining-dependencies.md`, `coverage-matrix.md`, `multi-tenant-white-label-readiness.md` all exist. `docs/branding/runbook.md` (F5) — see §5 below |

**MVP-010 bottom line:** 4 of 9 acceptance criteria fully MET, 1 explicitly NOT MET (criterion 9,
SMART dark tokens not actually returned by anything), 1 NOT MET (criterion 4, cross-tenant, blocked on
D-6), 3 PARTIAL (code/tests exist, live behaviour unexercised). **Not ready to close.**

---

## 2. MVP-014 — "Build Control Plane foundation"

Acceptance criteria reproduced from R2 (`...Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md:449-457`).
MVP-014's Control Plane itself is explicitly out of this branding work's scope — `docs/RebrandingPlan.md`
§2.3 states the branding plan "specifies the branding contract [the Control Plane] must expose and builds
the tenant-side receiver so the two can be integrated when `MVP-014` lands (dependency D-5)." This pack
therefore evaluates only the one criterion the branding layer can speak to.

| # | Acceptance criterion (R2) | Status | Evidence |
|---|---|---|---|
| 1 | Managed PostgreSQL 18 current minor is provisioned | **NOT MET / out of branding scope** | No Control Plane exists. `remaining-dependencies.md` D-5: "OPEN — directly confirmed... No CP exists to have built against" |
| 2 | No clinical PHI tables exist in the control-plane schema | **N/A — no schema exists yet** | Not applicable until MVP-014 itself is built |
| 3 | Tenant/domain/subscription/membership/deployment/feature/branding/provisioning models exist | **N/A — out of branding scope** | The branding *contract* those models must expose is specified (`docs/RebrandingPlan.md` §3.3.2: `saas_branding_profile`, `saas_branding_token`, `saas_branding_asset_ref`, `saas_branding_revision`, `saas_branding_materialisation_log`), but the tables themselves are Control-Plane-side and unbuilt |
| 4 | OpenEMR runtime has no direct control-plane DB credential | **MET by construction (tenant side)** | `QueryUtilsBrandingGlobalsWriter` and the whole materialisation tier read/write only the tenant's own `globals`; V-01's `ForbiddenBrandingHttpClientRule` guardrail (54/54 tests) forbids any HTTP client in the render path, which structurally rules out a CP credential reaching the render path from the tenant side. The CP side of this criterion cannot be evaluated — no CP exists |
| 5 | Secrets stored as references only | **N/A — no CP exists** | Cannot be evaluated |
| 6 | Tenant runtime continues essential local operation during a simulated CP outage | **PARTIAL — designed and unit-tested, not live-simulated** | Same evidence as MVP-010 criterion 7 (V-03): `MaterialiserKillRecoveryTest` passes in isolation; no live CP-outage simulation was run because no live CP exists to take down |
| 7 | (Q76) Branding model stores authoritative tokens/logo refs/revision, exposes tenant-scoped idempotent materialisation outside the request path | **PARTIAL — tenant-side receiver built, CP-side authority absent** | The tenant-side half (`BrandingMaterialiser`, `thiqa-branding:materialise` CLI, out-of-request by construction) exists and is tested (V-02). The CP-side authoritative store is `MVP-014`'s own deliverable and does not exist |

**MVP-014 bottom line:** the branding layer built exactly what `docs/RebrandingPlan.md` committed to
building on the tenant side of the Q76 boundary — nothing more, nothing less. The Control Plane itself,
which most of MVP-014's criteria are actually about, has not been started under this branch. **MVP-014 is
not closeable by this branch's work; only its branding-contract sub-scope is addressed, and even that is
untested end-to-end because there is no live counterpart to integrate against.**

---

## 3. PR / commit references

No pull request has been opened for this work. Checked directly in this session:

```
git log origin/feat/thiqa-branding-foundation..HEAD --oneline
c6c3f9e6e docs(branding): record the Phase 3 audit, remediation and plan corrections
b866c5358 fix(branding): rebrand the HTTP error page titles
d9757fc55 chore(branding): install the brand kit assets and wire the tooling
dabc855c3 test(branding): cover the branding layer and the core string changes
df3cc18f2 fix(branding): repoint the residual core product strings
32764921c feat(branding): add the Thiqa theme and repoint the webpack entries
c6a2f65b3 feat(branding): add the brand token generator and asset installer
a1c22b6a1 feat(branding): add the Thiqa branding layer module
```

All 8 commits above exist only on the local `feat/thiqa-branding-foundation` branch and have not been
pushed to `origin`. **There is no PR to cite.** Any future revision of this document that claims a PR
number must first confirm one has actually been opened (`gh pr view` or equivalent) — do not infer one
from branch existence.

---

## 4. Security / tenant-isolation review

**This is explicitly not complete. It is the single most important open item in this pack.**

The structural design is sound and source-verified: `QueryUtilsBrandingGlobalsWriter` is constructed
bound to exactly one `SiteId` and every read/write method calls `assertBoundTo()` first, throwing
`LogicException` on a mismatch, with a deliberately generic exception message so a cross-tenant attempt
cannot leak the target site's name (`docs/branding/multi-tenant-white-label-readiness.md` §1.2). `SiteId`
itself rejects path-traversal-shaped input by construction (§1.2, same document).

But per `docs/branding/multi-tenant-white-label-readiness.md` §2.4, quoting `docs/AuditRebranding.md:1174`
directly: **"AR-P2-006 DB-backed and two-tenant tests — OPEN — Focused tests use recording collaborators.
Add MariaDB failure injection and two independently bootstrapped tenant connections."** In plain terms:
the cross-tenant guard is unit-tested against fakes (recording collaborators standing in for a second
tenant), and no test in this repository has opened two real database connections and proven the guard
rejects a genuine cross-tenant write. Acceptance tests A1 and A2 — the tests that would actually
demonstrate no cross-tenant bleed end-to-end — are **BLOCKED** on D-6 (no second tenant provisioned on
this system; `ls sites/` returns only `default`).

**Conclusion of this review: the tenant-isolation guard is real and well-designed, but its actual
behaviour under a genuine two-tenant, two-connection scenario has never been observed.** This is not a
checked box. A security sign-off for MVP-010/MVP-014 should not be recorded as complete until A1/A2 run
against a second provisioned tenant.

---

## 5. Runbook links

`docs/branding/runbook.md` (Phase 5 deliverable F5: provision a tenant's branding, change a token, roll
back a revision, rebuild themes, regenerate tokens, verify a release, recover a failed materialisation)
is a companion Phase 5 deliverable. As of this document's writing it had not yet appeared in
`docs/branding/` (directory listing at time of writing: `changes.md`, `coverage-matrix.md`,
`multi-tenant-white-label-readiness.md`, `remaining-dependencies.md`). If it exists by the time this pack
is read, add it here as the authoritative operational reference; until then, treat runbook coverage as
**not yet delivered**, not as an oversight of this document.

The nearest operational reference available today is
`docs/branding/multi-tenant-white-label-readiness.md` §1.4 (the three `thiqa-branding:*` CLI commands —
`apply-profile`, `materialise`, `verify` — with their actual invocation syntax) and §2.2 (the literal,
not-yet-executed steps to provision and exercise a second tenant).

---

## 6. Overall closure recommendation

**Do not close MVP-010 or MVP-014 on the strength of this branch's work today.** What is genuinely done:
the branding layer's architecture, token model, guardrails and isolated/unit test suites are built and
passing at real, cited numbers (393/393, 54/54, 110/110, 50/51-then-1/1, 20/20, 23/23, 10/12 across the
suites cited in §1–§2 above). What is not done, and must not be represented as done:

1. ~~**Live materialisation has never executed, even once, against the one tenant that exists.**~~
   **SUPERSEDED 2026-08-10 (RB-11).** A materialisation *has* since run. `thiqa-branding:verify
   --site=default` now reports `Status: healthy`, `Revision: 1`, `Materialised at:
   2026-08-10T18:50:40+00:00`, and `globals.saas_branding_revision = 1`. **The important caveat, which
   stops this being a clean win:** the run materialised with an **empty Tier-2 overlay**
   (`saas_branding_tokens_light` and `_dark` are both `''`), so what was exercised is the transaction and
   revision-bump path, **not** a real token overlay reaching a rendered page. AC-6 is not closed by this;
   a materialisation with a non-empty overlay, and the resulting `<link>` captured on a rendered page, is
   what would close it.
2. **Cross-tenant isolation (A1/A2) has not been demonstrated at all** — blocked on D-6, no second
   tenant provisioned. *(Unchanged and still the largest gap.)*
3. ~~**The SMART dark-token acceptance criterion (R-SMART-DARK) is not actually met**~~ **CORRECTED
   2026-08-10 (RB-09).** The mechanism *is* built and correct — it is the Twig template route, not
   `smartStyleTokens()`. See the corrected row 9 in §1. Live verification is blocked by this host's
   `OPENSSL_CONF` quirk, not by anything in the branding layer. The genuine residual finding is narrower:
   `SmartStyleContract` is an orphaned parallel implementation, and because the SMART templates carry
   baked hex literals, a Tier-2 tenant overlay does **not** reach the SMART contract.
4. **The security/tenant-isolation review itself (§4) surfaces a real, unclosed gap** — the isolation
   guard is proven against fakes, not against two real database connections.
5. **No PR exists.** The work is 8 unpushed local commits.

None of this means the engineering is bad — the opposite: the design is careful, the guardrails are
real, and the gaps are precisely the ones a Q76/Q77-literate reviewer would expect to still be open at
this stage (they line up exactly with D-5, D-6, D-9 in the blocking-dependency register). It means the
Global Definition of Done in R2 is not yet satisfied, and this document's job is to say so plainly rather
than round up.
