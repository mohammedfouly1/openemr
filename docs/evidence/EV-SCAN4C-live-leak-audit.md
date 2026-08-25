# EV-SCAN4C — Live Brand-Leak Surface Audit (re-dispatch of Scan-4C)

**Status:** COMPLETE. **Method:** read-only, live system at `http://localhost:8300/`
(native Windows stack per `CLAUDE.local.md`) plus static source tracing. No repository
file was edited. No fix was applied — this is a findings register only.

**Live database mutation:** **NONE.** Confirmed by before/after check:

```
Before:  v_database=541, openemr_name=Thiqa, saas_branding_product_name_ar=ثقة,
         main_menu_logo_title="Thiqa Health Information System"
After:   v_database=541, openemr_name=Thiqa, saas_branding_product_name_ar=ثقة,
         main_menu_logo_title="Thiqa Health Information System"
```

No side-effect-bearing endpoint (`sql_patch.php`, `ippf_upgrade.php`, `acl_upgrade.php`,
`setup.php`) was hit by GET during this audit. `acl_upgrade.php` was read (not executed) and
confirmed to write `AclExtended::*` rows / `AclExtended::setAclVersion()` on every load —
correctly avoided.

Session used for authenticated checks: `admin` (password read from
`C:\openemr-stack\secrets\thiqa-demo-credentials.json`, never printed). Login verified working
(69,732-byte authenticated response, consistent with the PB-020 "authenticated shell" size
class recorded in `CLAUDE.local.md`).

---

## Findings

### S4C-01 — The session/app cookie machinery hardcodes the literal string "OpenEMR", sent to every visitor, anonymous and authenticated, on both tenants — **P1**

Every response from `interface/login/login.php` (both `?site=default` and
`?site=rdy0082restore`) carries:

```
Set-Cookie: App=OpenEMR; expires=...; Max-Age=31536000; path=/; HttpOnly; SameSite=strict
Set-Cookie: OpenEMR=<sessid>; path=/; SameSite=Strict
```

Two independent leaks, same root cause:

1. **The PHP session cookie's own name is the literal string `OpenEMR`.**
   `src/Common/Session/SessionUtil.php:81` — `public const CORE_SESSION_ID = "OpenEMR";` —
   used directly as the session name. Any visitor's browser DevTools → Application → Cookies
   (or a network proxy, or `document.cookie`) shows a cookie literally named `OpenEMR` on a
   product that is configured, everywhere else in the rendered UI, as `Thiqa`.
2. **The `App` cookie's *value* is the same literal.**
   `interface/login/login.php:49` — `SessionUtil::setAppCookie(SessionUtil::CORE_SESSION_ID);`
   — sets a second, one-year-lived cookie named `App` whose value is `OpenEMR`.
3. **The same literal reaches authenticated page JavaScript.**
   `library/restoreSession.php:32` —
   `var oemr_session_name = <?php echo json_encode(urlencode($session->getName())); ?>;` —
   emits `var oemr_session_name = "OpenEMR";` into every authenticated page's `<script>` scope
   (confirmed live in the `admin` session's `main_screen.php` response). Comments in
   `interface/main/tabs/main.php:105,195,198` ("backing out of OpenEMR", "timing out mechanism
   in OpenEMR") are the same session-machinery vocabulary, visible via view-source, not visual
   UI — noted for completeness but not counted as a separate finding.

**Why the existing guard cannot catch this class:** `BrandLeakSurfaceContractTest` scans PHP/
Twig source for the *rendered* product-name string. A `Set-Cookie` header is emitted by
`setcookie()`/PHP's session machinery, not printed as page content, and `CORE_SESSION_ID` is a
session-plumbing identifier, not a `xl()`/`xlt()` call — structurally outside every scanned
surface. This is exactly the "dynamically-composed / HTTP-header" leak class the re-dispatch
brief called out.

**Severity reasoning:** P1, not P0. It requires opening DevTools or a proxy to notice — no
normal user sees it — but it is live on 100% of sessions (both tenants, authenticated and
anonymous) and is a genuine, unaudited surface that would ship as-is if SkyEagle branding were
applied today, since nothing in the rename tooling touches `CORE_SESSION_ID`. Renaming the
session cookie name is a real behavior change (existing sessions, load-balancer sticky-cookie
rules, browser-extension allowlists keyed on cookie name) — not a trivial string swap — so this
is recorded rather than fixed, per audit scope.

### S4C-02 — The second live tenant serves fully old-branded content to any anonymous visitor, with no visibility signal on the served page itself — **P1** (escalation of the already-closed S4E-01)

`docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §23.1 records S4E-01 as **FIXED — VERIFIED**,
but the fix (`SiteInventory`) only makes the second tenant visible to **CLI rename tooling**.
It does not touch the live web-serving path. Verified live, this audit:

```
GET http://localhost:8300/index.php?site=rdy0082restore
  -> 302 Found, Location: interface/login/login.php?site=rdy0082restore

GET http://localhost:8300/interface/login/login.php?site=rdy0082restore
  -> 200, 9172 bytes
  <title>Thiqa Login</title>
  ... <img src="/public/images/logos/core/login/primary/logo.png?..." alt="Thiqa logo">
  ... <p class="text-center lead">Clinical confidence, connected care.</p>
```

Both requests are fully anonymous (no auth, no special header, just the documented `?site=`
query parameter that `index.php:12-24` accepts and passes straight to
`sites/$site_id/sqlconf.php` after a character-class check). The served page carries **zero**
indication that it is the untracked, gitignored, off-books tenant recorded at checkpoint
§23.1/§9 — it looks, byte-for-byte in structure, identical to the primary tenant's own login
page, just branded `Thiqa` instead of whatever the primary tenant is configured as at the time.

**Why this is worse than the closed finding acknowledges:** §23.1's own text says "Closed for
**visibility**, which is the part that is code" and explicitly scopes the fix to "every
tenant-scoped command now renders a notice" — i.e., an operator running a rename *command*
gets warned. A visitor, or an automated brand-leak crawler, hitting the live site gets no such
warning; the tenant is exactly as invisible to *live traffic* today as it was before the fix.
If SkyEagle branding is ever applied against `--site=default` only (which §23.1 confirms is
possible, since `--site` has no default and nothing enumerates it automatically unless the
operator remembers to), this tenant will keep serving `Thiqa`-branded, apparently-legitimate
login pages to anyone who has or guesses the site slug — indefinitely, with no code path that
would ever surface the drift to anyone monitoring the live product.

**Severity reasoning:** P1. Not re-scored as a new P0 because the underlying fact (the tenant
exists and is fully branded) was already known and its retirement was already deliberately
deferred to an Owner decision (§23.1's closing line). What is new here is the *live-reachability
proof* — this had not been checked from the browser/HTTP side before, only from the filesystem/
DB side — and the observation that the CLI-tooling fix creates a false sense that the exposure
is "handled," when the actual anonymous attack/discovery surface is untouched.

**Not attempted:** modifying, renaming, or retiring `sites/rdy0082restore` — out of scope and
explicitly an Owner decision per §23.1/§23.7.

### S4C-03 — Quantified blast radius of the two dead `skyeagle.uk` URLs (informs the pending ADR-BRAND-006 ruling) — **P2 (informational, no new severity claim)**

Per checkpoint §23.6, `https://skyeagle.uk/support` and `https://skyeagle.uk/docs` are
confirmed 404 (that determination is not re-verified here — this task added no new network
egress test — this section only traces which *live UI elements* actually link to them).

Both globals are consumed in exactly one place: `interface/main/about_page.php:38-55`, feeding
`templates/core/about.html.twig`:

```
templates/core/about.html.twig:36-39   "Online Support" link  -> online_support_link
                                        (DB value: https://skyeagle.uk/support — 404)
templates/core/about.html.twig:50-54   "User Manual" button   -> user_manual_link
                                        (DB value: https://skyeagle.uk/docs — 404)
```

Reachability chain, traced statically (this page was deliberately **not** loaded live — see
"Not attempted" below): any authenticated user → top-nav dropdown → **"About Thiqa"** menu item
(confirmed live in the `admin` session's rendered nav:
`<i class="fa ... fa-info"></i>&nbsp;About Thiqa`) → opens a tab pointed at
`./../about_page.php` (`templates/interface/main/tabs/user_data_template.html.twig:46`,
`data-bind="click: ... navigateTab('./../about_page.php', 'About', ...)"`) → renders the two
dead links above. So: **every authenticated user of every role, on every tenant, is two clicks
from a dead "Online Support" link and a dead "User Manual" button**, both under the
`Thiqa`-branded About page.

The third URL in the same globals row, `main_menu_logo_link = https://skyeagle.uk/`, is **not**
dead (root returns 200 per §23.6) and is the top-nav logo's `href`
(`interface/main/tabs/main.php:492`) — confirmed live in the authenticated shell:
`<a class="navbar-brand" href="https://skyeagle.uk/" title="Thiqa Health Information System" ...>`.
So the blast radius is precisely: **2 of 3** shipped URLs are dead, both reachable from the same
single page, both one static classification/Owner-ruling away from being either fixed or
deliberately preserved.

**Not attempted:** actually loading `about_page.php` live. `CLAUDE.local.md` §9 documents this
page's render path (`TwigExtension::getGlobals()` → `Session::all()` →
`read_and_close` session start) as reproducibly hanging this host's Apache process and wedging
the session lock for the rest of the browser tab, live, through real Apache — not just under
PHPUnit. Given that hazard is explicitly host-specific infrastructure risk with no code fix
available on this machine, and the two link targets' dead/live status was already independently
confirmed in §23.6 by direct URL fetch (not by loading the About page), re-triggering the known
hang added no verification value here and risked disrupting the audit session itself. The
reachability chain above was established entirely from static source (`grep`), which is
sufficient to answer the "which live UI elements link to these" question the brief asked.

### Negative results (checked, no leak found)

These are recorded because the brief asked for a genuine falsification attempt, not a
confirmation exercise — absence of a finding here is itself informative.

- **REST/FHIR API error bodies.** `GET /apis/default/fhir/Patient` (no auth) → `401`,
  body `{"error":"An error occurred","message":"The resource owner or authorization server
  denied the request.","code":0}`. `GET /apis/default/api/patient` (no auth) → `401`, identical
  generic body. No brand string, upstream or configured, in either. Clean.
- **`.well-known/smart-configuration`.** `GET /apis/default/fhir/.well-known/smart-configuration`
  → `200`, pure OAuth2/SMART metadata JSON (issuer URLs, scopes, capabilities). No product-name
  field present in the FHIR CapabilityStatement discovery response checked. Clean.
- **Generic root `.well-known`.** `GET /.well-known/smart-configuration` (no `apis/default/fhir`
  prefix) → `404`, plain Apache 404, no content to leak.
- **404 handling.** `GET /nonexistent-page-xyz123.php` → `404` via Apache's own handler (no
  OpenEMR-branded 404 page is served at all at the webroot level for a nonexistent script) —
  nothing to leak because there is no content.
- **A genuine PHP 500 (found opportunistically).** `GET /portal/index.php?site=default` → `500`,
  body is exactly `An error has occurred.` (22 bytes, generic, no stack trace, no brand string).
  Traced the underlying cause in `C:\openemr-stack\logs\php_error.log`: an unrelated
  pre-existing bug — `portal_onsite_two_enable` global fails
  `Symfony\Component\HttpFoundation\ParameterBag::filter()`'s boolean coercion
  (`interface/globals.php:737`) with `FILTER_NULL_ON_FAILURE` unset — **not branding-related**,
  out of scope, not further pursued. Relevant to this audit only as evidence that the generic
  error page itself is clean.
- **HTTP response headers.** `Server: Apache/2.4.57 (Win64) PHP/8.3.33`,
  `X-Powered-By: PHP/8.3.33` — stock infrastructure identifiers, not OpenEMR/Thiqa/SkyEagle
  product strings. Not a brand leak in the sense this audit is scoped to.
- **Authenticated app shell (title, top nav, About menu label, product-registration modal).**
  Logged in as `admin`: page `<title>Thiqa</title>`, `WindowTitleBase = "Thiqa"`, nav brand
  image + `title="Thiqa Health Information System"`, About menu reads "About Thiqa", product
  registration modal title reads "Thiqa Product Registration". All correctly composed through
  the configured identity — consistent with §23's "3 anonymous pages + 2 Documentation files"
  already being the closed set for *rendered* literals. No new rendered-literal leak found in
  the authenticated shell itself.

---

## Summary

| ID | Area | Severity | Status |
|---|---|---|---|
| S4C-01 | Session/App cookie machinery hardcodes literal `"OpenEMR"` (cookie name, cookie value, and JS var, on every visitor) | P1 | Recorded, not fixed |
| S4C-02 | Second live tenant (`rdy0082restore`) fully reachable and Thiqa-branded to anonymous visitors; CLI-tooling fix (S4E-01) does not cover the live path | P1 | Recorded, not fixed (retirement is Owner's decision per §23.1/§23.7) |
| S4C-03 | Blast-radius quantification: both dead `skyeagle.uk` URLs are reachable from exactly one page (`about_page.php`), 2 clicks from any authenticated user | P2 / informational | Recorded for ADR-BRAND-006 ruling |

**Three findings, two P1 and one P2/informational. Live database was not mutated — verified by
identical before/after `globals`/`version` snapshot.** No repository file was edited by this
audit.
