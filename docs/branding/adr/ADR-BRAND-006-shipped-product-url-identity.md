# ADR-BRAND-006: What the installer should ship as the product's website, support and documentation URLs

**Status:** **PROPOSED — awaiting an Owner ruling.** Nothing in this document has been applied.
No value in `branding-profile.json`, `library/product_identity.generated.php`, the `globals` table
or any source file was changed to write it. Records finding **B4**.

**Decision required from:** Owner (product identity / SkyEagle rename).
**Blocks:** nothing today; the exposure it describes is live and grows with every new install.

**Verification history.** Drafted in a first pass on 2026-08-25 that terminated on an infrastructure
error. A second pass the same day **re-derived every substantive claim independently** rather than
adopting the draft — the artefact contents, the install-time write path, the provenance commits, the
consumer map, both database states, the anonymous live surfaces, and all six DNS/HTTP measurements
were re-run from scratch and **all reproduced**. Four corrections were applied and are marked in
place: the 2FA tooltip line number had drifted (`setup.php:1939` → `:1945`, concurrent edit by
another agent), the `setup.php` product-name line list in §3.3 was wrong at its tail, the count of
pinning test assertions in §7 was an undercount (five → **eleven**, across four files not three), and
the ADR-BRAND-005 citation in §10.1 was off by one line (`:79-80` → `:78-79`). Two measurements were
added: `/en/support` and `/ar/support` also 404, so no locale-prefixed support route exists at any
prefix.

---

## 1. The problem, in one paragraph

Since commit `e16913d5b` the installer ships a **product identity artefact**
(`library/product_identity.generated.php`) that `library/globals.inc.php` writes straight into the
`globals` table when a site is created. It says the product is called **Thiqa** and that its website,
support page and user manual live at **`https://skyeagle.uk/`**, **`https://skyeagle.uk/support`** and
**`https://skyeagle.uk/docs`**. Two facts make that worth an explicit ruling rather than a silent
carry-forward. First, **`https://skyeagle.uk/support` and `https://skyeagle.uk/docs` both return HTTP
404** (measured from this host 2026-08-25, see §5) — the product does not merely point at a differently
branded site, it ships two dead links, and one of them is rendered as **visible link text** on the
About page directly beneath a heading that reads "About Thiqa". Second, `https://skyeagle.uk/` itself
resolves to a live SkyEagle corporate marketing site that **does not contain the word "Thiqa"
anywhere** — so a user who clicks the product logo leaves a product called Thiqa and lands on a page
that has never heard of it. Whether the *name* and the *domain* should match is a branding decision
the Owner owns and this document does not pre-empt; whether the product should ship two URLs that
404 is not really a decision at all, and that is the part this document urges be fixed first.

---

## 2. Provenance — and a correction to ADR-BRAND-005

ADR-BRAND-005's Consequences section states:

> Note that `product_domain` in the committed profile has been `skyeagle.uk` since `b3b821ffa`,
> well before this ADR

**That citation is wrong on both halves, and the error is corrected here.**

| Claim in ADR-BRAND-005:116 | Verified fact | Method |
|---|---|---|
| `skyeagle.uk` entered the profile at `b3b821ffa` | It entered at **`a1c22b6a1`** ("feat(branding): add the Thiqa branding layer module"), the commit that created `branding-profile.json` | `git show a1c22b6a1:interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json \| grep -n skyeagle` → lines `5`, `49`, `73`, `81` |
| `b3b821ffa` is where it changed | `b3b821ffa` touches **one** line of that file — the `omitted` array's `reason` prose about materialiser-owned globals. It never touches `product_domain` or any URL row | `git show b3b821ffa -- .../branding-profile.json \| grep -E '^[-+@]'` — a single `-`/`+` pair at `@@ -319,7 +319,7 @@` |

**A second, subtler inaccuracy in the same sentence.** `product_domain` is *not* the field the
artefact is generated from, and it is **inert**: a repo-wide search finds exactly one occurrence, its
own definition.

```
git grep -n "product_domain" -- interface src library tools tests
→ interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json:5
```

The generator reads `product_name` as a document member and the three URLs as **`globals` rows** of
the profile — `main_menu_logo_link`, `online_support_link`, `user_manual_link`
(`tools/branding/src/ProductIdentityKey.php:73-81`, `sourceName()`). Those three rows have carried
`https://skyeagle.uk/`, `/support` and `/docs` since `a1c22b6a1` as well. So the substantive claim
ADR-BRAND-005 was making — *the generator reflects existing configuration and introduces no new
identity decision* — **holds, and is if anything stronger than stated**: the values are older than
the ADR said, and arrived in the same commit as the module itself.

**Therefore this is confirmed as not a Category-B change.** What `e16913d5b` changed is **reach**, not
identity: before it, these values existed only in a module profile that `thiqa-branding:apply-profile`
had to be run to apply; after it, they are the installer's own defaults and land in the `globals` table
of every new site automatically.

---

## 3. Where the four values go — the full consumer map

Derived on disk 2026-08-25. Every row is a `file:line` that can be re-read.

### 3.1 The artefact and its read side

| File:line | What |
|---|---|
| `library/product_identity.generated.php:28-31` | The four literals as shipped |
| `src/Common/Branding/ProductIdentity.php:108-131` | `name()`, `websiteUrl()`, `supportUrl()`, `documentationUrl()` |
| `src/Common/Branding/ProductIdentity.php:89-94` | `FALLBACK` — upstream OpenEMR values, used only when the artefact is missing/malformed |

### 3.2 Install-time writers — **the only path by which these reach a database**

| File:line | Global written | Live or latent |
|---|---|---|
| `library/globals.inc.php:454` | `openemr_name` ← `ProductIdentity::name()` | **Install-time default** |
| `library/globals.inc.php:468` | `main_menu_logo_link` ← `::websiteUrl()` | **Install-time default** |
| `library/globals.inc.php:482` | `online_support_link` ← `::supportUrl()` | **Install-time default** |
| `library/globals.inc.php:494` | `user_manual_link` ← `::documentationUrl()` | **Install-time default** |

The write mechanism is `Installer::insert_globals()`
(`library/classes/Installer.class.php:823-839`), which loops `$GLOBALS_METADATA` and calls
`writeGlobal($fldid, $flddef, 0, true)`. The fourth argument is `$insert_only`, and
`writeGlobal()` at `:850-869` short-circuits with `return true` when a row already exists
(`:859-861`). **So these are genuinely install-time-only: an existing row always wins, and re-running
the installer over a populated `globals` table cannot overwrite them.**

### 3.3 Rendering consumers — where a user meets the values

| File:line | Surface | Reads | Status |
|---|---|---|---|
| `interface/main/tabs/main.php:492,496` | Main-menu logo `<a href>` | `main_menu_logo_link` | **LIVE — authenticated** |
| `interface/main/about_page.php:46` → `templates/core/about.html.twig:36-39` | About page "Online Support" — rendered as **`href` *and* as visible text** (`{{ onlineSupportHref\|text }}`) | `online_support_link` | **LIVE — authenticated** |
| `interface/main/about_page.php:38-40,55` → `about.html.twig:50-52` | About page "User Manual" button | `user_manual_link` | **LIVE — authenticated** |
| `setup.php:78` (resolved once), then `:157, 172, 189, 300, 314, 368, 464, 516, 519, 522, 534, 536, 538, 543, 625, 687, 689, 705` | Installer copy — page title, navbar, the already-installed refusal, the success panel, every step heading | `ProductIdentity::name()` | **LIVE — anonymous** (verified §4) |
| `setup.php:1945` | 2FA tooltip JS | `ProductIdentity::name()` via `js_escape()` | **LIVE — anonymous-reachable page** |

> **`setup.php` line numbers are volatile.** That file is under concurrent edit by another agent
> (`git status --short setup.php` → ` M setup.php` at the time of writing), and the 2FA tooltip has
> already moved from `:1939` to `:1945` between the first and second pass of this finding. Locate it
> by `grep -n "2fa-section-tooltip" setup.php` rather than by line number.
| `interface/globals.php:104-105, 112-113` | Pre-bootstrap openssl fatal messages | `ProductIdentity::name()` | **LIVE — but only on a broken host** |
| `interface/modules/zend_modules/.../installer/index.phtml:36,117` | Module-manager page: **`href="https://skyeagle.uk/docs/installer"` with anchor text "Visit additional modules for Thiqa developed…"** | Hardcoded — **not** driven by any global | **LIVE — admin-authenticated. Target 404s (§5).** This is the single most literal Thiqa-name-plus-SkyEagle-URL juxtaposition in the tree, and no configuration change reaches it |

### 3.4 A second, independent copy of the same three URLs — currently **latent**

| File:line | What | Status |
|---|---|---|
| `.../ThiqaBranding/src/Config/BrandingGlobalKey.php:49` | `const PRODUCT_URL = 'https://skyeagle.uk/'` | Used only at `:200` |
| `.../BrandingGlobalKey.php:200, 219, 226` | Runtime *fallback* defaults for the three URL keys | Resolved by `BrandingConfigFactory.php:64,67,68` into `BrandingConfig->mainMenuLogoLink / onlineSupportLink / userManualLink` |
| `.../Config/BrandingConfig.php:39,42,43` | The three fields | **LATENT.** `git grep -n "mainMenuLogoLink\|onlineSupportLink\|userManualLink" -- interface src templates` finds **no reader** outside the module's own construction path. The values are computed on every request and never rendered |

The module *is* active on both local sites (`SELECT mod_name, mod_directory, mod_active FROM modules
WHERE mod_directory LIKE '%thiqa%'` → `1` in both databases), so this is latency by absence of a
consumer, not by the module being off. Any correction that touches only the profile/artefact and not
`BrandingGlobalKey.php` leaves this copy stale — worth noting, since `BrandingGlobalKey.php` is owned
by the branding module and is **not** generated.

### 3.5 Not a consumer, despite appearances

`src/Telemetry/TelemetryService.php:202` still posts to `https://reg.open-emr.org/api/usage`.
`reg.skyeagle.uk` appears **only** as an expectation inside
`tests/Tests/Isolated/BrandingCoreStrings/MandatoryCoreStringPatchesIsolatedTest.php:204`, and
**`reg.skyeagle.uk` does not resolve at all** (NXDOMAIN). No shipped code references it.

**But note what that expectation is.** `:204` is the replacement pair
`['reg.open-emr.org', 'reg.skyeagle.uk']` — i.e. the branding suite already *intends* the telemetry
endpoint to move to a `skyeagle.uk` subdomain that has never been published, while
`ForbiddenBrandingPlaceholderDomainRule` (`:45`) simultaneously forbids the literal
`reg.open-emr.org` that `TelemetryService.php:202` still ships. That is the **same defect class as
this finding** — a declared destination on `skyeagle.uk` that does not exist — reached by a different
route, and it is flagged here rather than pursued, because the telemetry endpoint is outside this
document's scope. It is additional evidence for §7's conclusion that the programme has no gate
asking whether a declared destination is real.

---

## 4. What is observed live on this host

Stack running (`httpd` pids 4740/20032, `mariadbd` 20320). Anonymous requests only — **no
authentication was attempted, per the task's constraint.**

| Surface | Command | Result |
|---|---|---|
| Login page | `Invoke-WebRequest 'http://localhost:8300/interface/login/login.php?site=default'` | `200`, 9165 bytes. `<title>Thiqa Login</title>`; **2** occurrences of "Thiqa"; **0** occurrences of `skyeagle`; **0** of `open-emr.org` |
| `setup.php` | `Invoke-WebRequest 'http://localhost:8300/setup.php?site=default'` | `200`, 99 bytes: **`Thiqa has already been installed. If you wish to force re-installation, see log for details.`** — this is `setup.php:314`, i.e. `ProductIdentity::name()` rendering anonymously. Read-only: the request dies at the already-installed guard (`setup.php:296-301, 308-315`) before any installer step |
| `interface/main/tabs/main.php` | anonymous GET | **HTTP 400** — auth-gated |
| `interface/main/about_page.php` | anonymous GET | **HTTP 400** — auth-gated |

**Conclusion on co-occurrence.** On the surfaces reachable without a login, the Thiqa name appears and
the skyeagle.uk URLs do not — they never appear together anonymously. **Every surface where the name
and the SkyEagle URL are rendered on the same page requires a login** (About page, main-menu logo,
module manager). **I did not log in and therefore did not visually confirm those three.** The
source-level evidence that they co-occur is nonetheless unambiguous:
`templates/core/about.html.twig:10` renders `{{ "About %s"|xlp|text }}` (the product name) and `:39`
renders `{{ onlineSupportHref|text }}` (the literal support URL) in the same document.
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` RDY-0090 records a prior agent's live
render of this page on 2026-08-19 showing exactly that pairing — cited as corroboration, not as my own
observation.

---

## 5. Does the domain resolve, and what does it serve?

Measured from this host, 2026-08-25, via `Resolve-DnsName` and `Invoke-WebRequest`.

| URL | Result |
|---|---|
| DNS `skyeagle.uk` | `A 76.76.21.21` (Vercel) — resolves |
| `https://skyeagle.uk/` | **`200`**, 170,970 bytes, `server: Vercel`. Title: *"Five Healthcare Software Product Lines, Each Published With Its Limits \| SkyEagle"*. **`Thiqa` occurs 0 times in the page** |
| `https://skyeagle.uk/support` | **`404 Not Found`** |
| `https://skyeagle.uk/docs` | **`404 Not Found`** |
| `https://skyeagle.uk/docs/installer` | **`404 Not Found`** (the `index.phtml:36,117` target) |
| `https://skyeagle.uk/en/support` | **`404 Not Found`** — the locale-prefixed form does not exist either |
| `https://skyeagle.uk/ar/support` | **`404 Not Found`** — nor the Arabic one |
| `https://reg.skyeagle.uk/` | **does not resolve** (NXDOMAIN, re-verified 2026-08-25) |

The site is a locale-prefixed Next.js application. Its own internal links (extracted from the
homepage) are `/en`, `/ar`, `/en/about`, `/en/contact`, `/en/demo`, `/en/pricing`, `/en/products`,
`/en/products/his-emr`, `/en/products/{claim-submission,denial-management,pbm,rcm}`, `/en/resources`,
`/en/solutions`, `/en/who-we-serve`. **There is no `/support` and no `/docs` route, at any locale
prefix.** Spot-checked live pages that do return `200`: `/en/contact`, `/en/demo`, `/en/resources`,
`/en/products/his-emr` (*"Outpatient EMR and Clinic Management System | SkyEagle"*). **None of the
four contains the string "Thiqa".**

**This materially raises the severity of the finding.** The shipped defaults are not merely
inconsistent branding; two of the three are **broken links to a site that has no such page**, and the
third leads to a vendor site that does not name the product the user just came from.

### 5.1 Is there a Thiqa-owned domain to move to? — evidence, and an honest limit

The task asks this be established rather than assumed.

- **Nothing in the repository names one.** The only Thiqa-shaped domain anywhere in `docs/`,
  `interface/`, `src/`, `library/` or `tools/` is the RFC 2606 placeholder **`thiqa.example`**
  (`docs/RebrandingPlan.md`, `docs/branding/architecture.md`,
  `docs/branding/remaining-dependencies.md`, `docs/branding-production/14-string-replacement-map.md`,
  `docs/branding-production/15-decision-record.md`).
- **That placeholder was deliberately retired in favour of skyeagle.uk.**
  `docs/branding-production/15-decision-record.md:27` records decision **D-2**: *"Production domain is
  **`skyeagle.uk`**; all `thiqa.example` placeholders replaced."* So the present state is not drift —
  it is a recorded decision being honoured.
- **`thiqa.example` is additionally forbidden in shipped code by a PHPStan rule**,
  `tests/PHPStan/Rules/ForbiddenBrandingPlaceholderDomainRule.php:45-55`. Reverting to a placeholder
  is therefore not available without also changing that rule.
- **Obvious Thiqa domains are third parties.** `thiqa.sa` → `162.55.86.195`, serves
  *"ثقة العقارية"* (Thiqa Real Estate). `thiqa.io` → `76.223.105.230`, serves a site titled
  *"THIQA"*. `thiqa.com` (SERVFAIL), `thiqa.uk`, `thiqa.health`, `thiqahis.com` — all NXDOMAIN.

**Limit of this evidence, stated explicitly.** DNS and HTTP tell me a name resolves and who serves it
today. They **cannot** tell me what the Owner has registered, holds unpublished, or intends to
register. **I could not establish whether a Thiqa-owned domain exists.** The finding is narrower and
defensible: *no Thiqa-owned domain is recorded anywhere in this repository, and the only recorded
decision on the subject (D-2) points at skyeagle.uk.* Option A below therefore cannot be costed
without an Owner answer that no artefact in this tree contains.

---

## 6. What a fresh install would actually produce

Distinguishing the shipped default from what this host's already-populated database shows — the
distinction the task asks for, and the one it is easiest to get wrong.

**Both local databases already carry the fully-branded values.** Verified by direct query
(`SELECT gl_name, gl_value FROM <db>.globals WHERE gl_name IN (…)`, run against both configured sites):

| Global | `openemr` (site `default`) | `openemr_rdy0082_restore` |
|---|---|---|
| `openemr_name` | `Thiqa` | `Thiqa` |
| `main_menu_logo_link` | `https://skyeagle.uk/` | `https://skyeagle.uk/` |
| `main_menu_logo_title` | `Thiqa Health Information System` | `Thiqa Health Information System` |
| `online_support_link` | `https://skyeagle.uk/support` | `https://skyeagle.uk/support` |
| `user_manual_link` | `https://skyeagle.uk/docs` | `https://skyeagle.uk/docs` |

These rows were written by `thiqa-branding:apply-profile`, **not** by the new install-time defaults —
both databases predate `e16913d5b`. So *nothing observable on this host is evidence about the new
default path.* The install-time behaviour must be reasoned from `Installer::insert_globals()` and
`writeGlobal($…, insert_only: true)` (§3.2), which is what I have done. **No installer was run and no
database was created**, per the task's constraint.

**A fresh install from this branch would produce:**

| Global | Value on a brand-new site | Source |
|---|---|---|
| `openemr_name` | `Thiqa` | `globals.inc.php:454` |
| `main_menu_logo_link` | `https://skyeagle.uk/` | `globals.inc.php:468` |
| `online_support_link` | `https://skyeagle.uk/support` — **404** | `globals.inc.php:482` |
| `user_manual_link` | `https://skyeagle.uk/docs` — **404** | `globals.inc.php:494` |
| `main_menu_logo_title` | **`''` (empty)** | `globals.inc.php:474` — **unchanged by `e16913d5b`** |

**That last row is a related gap this finding surfaces and does not resolve.** The branding profile's
own note on `main_menu_logo_title` reads *"MUST NOT be blank. Upstream auto-generates an open-emr.org
title from an empty value, which leaks the inherited brand"*, and
`BrandingGlobalKey.php:202-208` repeats the warning. Yet `e16913d5b` branded the four values around it
and left this default at `''`. A fresh install therefore writes an **empty** row, and because
`OEGlobalsBag::getString('main_menu_logo_title', $logoTitleDefault)` at `main.php:493` only returns its
default when the key is *absent* — not when it is present-but-empty — the `$logoTitleDefault =
xlp('%s Website')` fallback at `:491` does **not** fire. The observable result is an empty `title`
attribute rather than an "OpenEMR Website" leak. **I have not executed this path**, so treat the
"empty tooltip, not a brand leak" conclusion as a source reading, not a measurement. Either way the
default is inconsistent with the profile's stated requirement, and is flagged here rather than fixed.

---

## 7. Would any guard have caught this?

**No.** Established by direct inspection.

- **`composer branding-ci`** (`composer.json:315-320`) runs the token check, the identity `--check`
  drift gate, the brand-manifest verifier, and an isolated PHPUnit selection. The identity gate
  (`tools/branding/bin/generate-product-identity.php --check`) verifies only that the artefact's bytes
  **match what the profile would regenerate**. It is a *determinism* gate. It has no opinion about
  what the values mean, whether they resolve, or whether they name the same organisation.
- **`ForbiddenBrandingPlaceholderDomainRule`** (`tests/PHPStan/Rules/…:45-48`) forbids exactly two
  things in shipped branding code: any RFC 2606 `.example` host, and `reg.open-emr.org`.
  `skyeagle.uk` is neither.
- **`ProductIdentityKey::rejectionReason()`** (`tools/branding/src/ProductIdentityKey.php:110-140`)
  validates *shape only* — UTF-8, length against `varchar(255)`, no control characters, no
  `< > " ' & \` or backtick, and a URL-shape check. A well-formed URL to a 404 passes cleanly.
- **The existing tests actively pin the mismatch as correct.** Enumerated exactly
  (`grep -rn "skyeagle" tests/ --include=*.php`), there are **eleven** pinning points across four
  files, not five:

  | File:line | Pins |
  |---|---|
  | `…/ThiqaBranding/Config/BrandingGlobalKeyTest.php:180,181,182` | the three `defaultValue()` URLs |
  | `…/ThiqaBranding/Config/BrandingConfigFactoryTest.php:52,54,55,108` | the three `BrandingConfig` fields (one twice) |
  | `…/ThiqaBranding/Config/BrandingProfileLoaderTest.php:110,111,112` | the three profile `globals` rows (data-provider cases) |
  | `…/BrandingCoreStrings/MandatoryCoreStringPatchesIsolatedTest.php:227` | the **hardcoded** `href="https://skyeagle.uk/docs/installer"` in `index.phtml`, together with its "additional modules for Thiqa" anchor text |

  A future agent who "fixed" the coherence problem would be met by eleven red assertions telling them
  they had broken something. Note that the first three files sit under
  `tests/Tests/Isolated/Modules/ThiqaBranding/Config`, which is **not** in the `branding-ci`
  selection at `composer.json:319` — they run only in the full isolated suite. The fourth,
  `BrandingCoreStrings`, **is** in that selection, so the `/docs/installer` link is pinned by the
  branding gate itself.
- **No test anywhere asserts that `product_name` and the product domain belong to the same identity**,
  and none asserts that any shipped URL resolves.
  (`grep -rn "skyeagle\|product_domain\|product_website_url" tests/ --include=*.php` returns only the
  pinning assertions above and `example.invalid` fixtures.)

**Why four scans missed a defect this visible.** Every gate in the branding programme was built to
answer *"does the shipped artefact match the declared profile?"* — a consistency question. Nobody
built a gate for *"is the declared profile itself right?"* — a correctness question. The programme's
own success criterion made the defect invisible: the more faithfully the generator reflected the
profile, the more confidently every check passed. **That is the reusable lesson here, and it is worth
more than the individual fix.** A cheap, non-networked partial guard is proposed in §9.

---

## 8. Options

Costs are stated as they actually fall, including migration for already-installed sites. Note
throughout that **migration cost for existing sites is genuinely small**: there are two known
databases, both already carrying these exact values, and any change is a `globals` row update the
existing `thiqa-branding:apply-profile` path already performs.

### Option A — Keep the name, repoint the URLs at a Thiqa-owned domain

**Cannot be costed.** §5.1 establishes that no Thiqa-owned domain is recorded anywhere in this
repository, the only recorded decision (**D-2**) points at `skyeagle.uk`, and the obvious candidates
are third parties — one of which serves a live business under the name "ثقة" / "THIQA", which is a
**trademark question in its own right** that this document raises and does not answer. If the Owner
holds a domain not recorded here, this option becomes a one-line profile edit plus a generator run
plus one `apply-profile` per site, and is then plainly the best answer. **Absent that answer it is not
selectable**, and pretending otherwise would be the "confident closure resting on an unverified
assumption" this programme documents as its failure mode.

### Option B — Keep the URLs, accept the mismatch until the SkyEagle rename resolves it

**Who sees it, precisely:** every logged-in user of the About page (support URL rendered as *visible
text*, beneath "About Thiqa"), every logged-in user who clicks the main-menu logo, and every
administrator who opens the module manager (`index.phtml:36` — "additional modules for **Thiqa**"
linking to a **404**). **Anonymous visitors see none of it** (§4).

**How long the exposure lasts:** *unknown, and I could not establish it.* No document in this tree
gives the SkyEagle rename a date. Treating "until the rename" as a bounded window is therefore an
assumption, not a fact, and this option should not be chosen on the strength of it.

**Cost:** zero engineering. **But it does not address the 404s at all**, and the 404s are the part of
this finding that is a defect rather than a decision. Accepting a naming mismatch is a legitimate
Owner call; accepting a dead "User Manual" button on a demoed product is a different proposition
wearing the same clothes. **If B is chosen, it should be chosen as B+D (below), not as B alone.**

### Option C — Blank or omit the URL defaults so nothing ships a wrong link

**Rejected on the evidence — and this is the important trap in this finding.** Blanking is not
neutral in this codebase; it is an *active regression*, and the code says so at both sites:

- `interface/main/about_page.php:38-39` — when `user_manual_link` is empty, the page **auto-generates**
  `https://open-emr.org/wiki/index.php/OpenEMR_<version>_Users_Guide`.
- `library/globals.inc.php:486-493` — the comment added by `e16913d5b` exists precisely to record
  this: *"a blank value makes the help link auto-generate an open-emr.org wiki URL … a brand leak that
  no `globals` row exists to override, because the row is empty."*
- `interface/main/tabs/main.php:491` — `main_menu_logo_title`'s blank-value fallback is
  `xlp('%s Website')`, the same class of problem.

So blanking `user_manual_link` **replaces a SkyEagle 404 with a working link to the upstream OpenEMR
wiki** — trading a cosmetic inconsistency for a live, functioning brand leak, which is strictly worse
against constraint G1/G4. Blanking `main_menu_logo_link` is the one safe blank
(`main.php:495-499` renders a non-clickable `<span>`), but fixing one of three while worsening another
is not a coherent option.

### Option D — Publish the two missing pages on the domain that is already live *(recommended)*

`https://skyeagle.uk/` is live, healthy and served from Vercel. The defect is that `/support` and
`/docs` were never published, not that the domain is wrong. **This is a content action on a site that
already exists — it requires no code change, no artefact regeneration, no profile edit, no database
migration, and no `apply-profile` run on any site, existing or future.** Every shipped value becomes
correct the moment the two routes return `200`, including the hardcoded `/docs/installer` link at
`index.phtml:36,117` if a route is published beneath `/docs`.

**Cost:** two pages on a marketing site the organisation already operates. **Residual:** the
Thiqa-name / SkyEagle-domain juxtaposition remains — but that is a *branding* question with a
recorded decision behind it (D-2), not a broken link, and it can then be settled on its own timetable
without a dead button forcing the issue.

**Risk to name honestly:** this option depends on someone outside this repository publishing two
pages. If that cannot happen before the next demo, D degrades to no action at all — which is why it
is paired with E.

### Option E — Interim: repoint the two dead URLs at live pages on the same domain

If D cannot land before the next demo, the smallest correct change is to repoint the two 404 targets
at routes that already return `200`:

- `online_support_link` → `https://skyeagle.uk/en/contact` (verified `200`)
- `user_manual_link` → `https://skyeagle.uk/en/products/his-emr` or `https://skyeagle.uk/en/resources`
  (both verified `200`)

**Cost:** edit two `globals` rows in `branding-profile.json`, re-run
`tools/branding/bin/generate-product-identity.php`, update the pinned assertions enumerated in §7
(`BrandingGlobalKeyTest.php:180,182` / `BrandingConfigFactoryTest.php:54,55,108` /
`BrandingProfileLoaderTest.php:111,112` — seven of the eleven move; the four `main_menu_logo_link`
ones do not), and update the two hand-written literals in `BrandingGlobalKey.php:219,226` (**not**
generated — §3.4). Migration for the two existing sites is one `apply-profile` run each; new sites
get it from the installer.
**Downside:** it hardcodes a locale prefix (`/en`) into a product that ships an Arabic identity, which
is a fresh small wrong thing. Genuinely interim.

---

## 9. Recommendation

**Recommended: D, with E as a dated fallback, and B explicitly *not* on its own.**

> Publish `/support` and `/docs` on `skyeagle.uk`; if they cannot be published before the next demo,
> repoint those two globals at pages on that domain that already return `200`. Leave `product_name`
> and `product_website_url` exactly as they are, and treat the Thiqa-name / SkyEagle-domain question
> as a separate branding ruling with no dead link forcing its hand.

**Why.** The evidence separates cleanly into two things that have been travelling together and should
not. The **naming mismatch** is a decision, it has a recorded owner and a recorded prior ruling (D-2),
it is visible only to authenticated users, and it is legitimately deferrable. The **404s** are a
defect, they need no ruling, and they are the reason this finding is worth an Owner's time at all: a
product whose "User Manual" button and "Online Support" link both lead nowhere fails a demo on
competence grounds long before anyone asks why the domain says SkyEagle. Option D fixes the defect at
the lowest total cost of any option here — **zero code, zero migration, zero test churn** — and is the
only one that also repairs the hardcoded `/docs/installer` link that no configuration change can
reach. Option C is rejected on positive evidence rather than preference: the code at
`about_page.php:38-39` turns a blank into a working open-emr.org link, so "ship nothing wrong" would
ship something worse.

**Additionally recommended (small, and independent of the ruling above):** add a guard that closes the
class of defect described in §7 without requiring network access in CI — an isolated test asserting
that every URL in the artefact shares one registrable domain, that that domain is declared once in the
profile, and that `main_menu_logo_title` is non-empty. That would have caught the `main_menu_logo_title`
gap in §6 immediately, and would catch the next partial rename. It cannot catch a 404, which is why D
matters more than the guard does.

**This is a recommendation, not a decision.** Nothing has been applied. It awaits an Owner ruling, and
in particular Option A supersedes all of the above the moment the Owner confirms a Thiqa-owned domain
exists — a fact this document could not establish (§5.1).

---

## 10. Separately: two further errors in ADR-BRAND-005

Recorded here because they were found while verifying this finding. **They belong to ADR-BRAND-005 and
have deliberately not been fixed in that file** — it is another agent's document and outside this
document's ownership. They are noted so the next editor of ADR-BRAND-005 does not have to re-derive
them, and so neither is propagated.

### 10.1 "Every read is escaped with `text()`" — one call site uses `js_escape()`

ADR-BRAND-005:78-79 states (the sentence wraps across those two lines; `text()` itself is on `:79`):

> At the point of *use*, `setup.php` and `interface/globals.php` emit HTML, so every read is escaped
> with `text()` at the call site.

`setup.php:1945` is the exception (was `:1939` — the file is under concurrent edit; locate it with
`grep -n "2fa-section-tooltip" setup.php`):

```php
$('.2fa-section-tooltip').prop( "title", "…unauthorized access to " + <?php echo js_escape(ProductIdentity::name()); ?> + " thus improves security…").tooltip();
```

`js_escape()` is the **correct** helper there — the value lands in a JavaScript string literal, not in
HTML body copy, and `text()` would be the wrong escaper for that sink. The ADR's *reasoning* ("escaping
depends on the sink") is right and the code is right; only the ADR's summary sentence is wrong, and it
is wrong in the direction that would mislead a future reader into "correcting" a correct call site.
Suggested wording: *"every read is escaped at the call site for its sink — `text()` in HTML,
`js_escape()` in the one JavaScript context (the 2FA tooltip in `setup.php`)."* Deliberately no line
number: that file moves.

### 10.2 The PRESERVE citation `:513-515` points at the wrong lines

ADR-BRAND-005:40 cites *"the genuinely factual project references at `:513-515`"* as class-(c)
PRESERVE content. Those lines are structural markup, not references:

```
513:                 ?>
514:             <h3 class="mb-3 border-bottom">Final step - Success</h3>
515:             <div class="jumbotron p-5">
```

The genuine preserved upstream project references are two lines further down:

```
526: <li>The OpenEMR project home page, documentation, and forums can be found at <a href="https://www.open-emr.org" …>
527: <li>We pursue grants to help fund the future development of OpenEMR… <a href="mailto:hello@open-emr.org">…
```

`:513` is in fact the **converted** side of the change — the `?>` immediately preceding the
`{$productNameEsc}` success message at `:516`. Citing it as PRESERVE inverts the classification of the
very lines the ADR converted. **The correct citation is `:526-527`.** Method: `awk 'NR>=510 && NR<=530
{printf "%d: %s\n", NR, $0}' setup.php`.

---

## 11. Method and limits

Every claim above is backed by a re-runnable command or a `file:line`, per this repository's standard.
Stated plainly, so this document is not read as more certain than it is:

**Established by direct measurement, twice, independently** (see the verification note at the head of
this document): the artefact's contents; the install-time write path; the consumer map; the two live
database states; the anonymous behaviour of the login page and `setup.php`; DNS and HTTP status for
`skyeagle.uk` and seven of its paths plus `reg.skyeagle.uk`; the absence of any coherence or
reachability guard; the enumeration of the eleven pinning assertions; both ADR-BRAND-005 errors; the
`a1c22b6a1` / `b3b821ffa` provenance.

Re-runnable in one block:

```bash
# provenance
git show a1c22b6a1:interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json | grep -n skyeagle
git show b3b821ffa -- interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json | grep -c product_domain   # -> 0

# reachability
for u in / /support /docs /docs/installer /en/contact /en/resources /en/support; do
  printf '%-20s ' "$u"; curl -s -o /dev/null -w '%{http_code}\n' -L "https://skyeagle.uk$u"; done

# what the two local sites actually hold
mariadb -u root --host=127.0.0.1 -N -e "SELECT gl_name, gl_value FROM openemr.globals \
  WHERE gl_name IN ('openemr_name','main_menu_logo_link','main_menu_logo_title','online_support_link','user_manual_link');"

# anonymous surfaces
curl -s 'http://localhost:8300/setup.php?site=default'
curl -s -o /dev/null -w '%{http_code}\n' 'http://localhost:8300/interface/main/about_page.php'   # -> 400

# the pinning assertions
grep -rn skyeagle tests/ --include=*.php
```

**Established by source reading, not execution** — treat accordingly: that a fresh install writes an
empty `main_menu_logo_title` and that the empty row suppresses rather than triggers the
`xlp('%s Website')` fallback (§6); that `BrandingConfig`'s three URL fields have no renderer (§3.4),
which rests on a `git grep` finding no reader and would miss a dynamic or string-built access.

**Not established:**

- **Whether a Thiqa-owned domain exists.** DNS cannot answer this; only the Owner can (§5.1).
- **How long the Option-B exposure would last.** No document in this tree dates the SkyEagle rename.
- **Live visual confirmation of the About page, the main-menu logo and the module-manager link.** All
  three require authentication; **no login was attempted**, per the task's constraint. The
  source-level co-occurrence is unambiguous and a prior agent's 2026-08-19 render is cited as
  corroboration, but I did not see these pages myself.
- **Whether `thiqa.sa` / `thiqa.io` represent a trademark conflict.** They are live third-party sites
  under the name; that is a legal question this document raises and does not answer.

**Constraints honoured:** no value was changed anywhere — not `product_domain`, not the profile JSON,
not `library/product_identity.generated.php`, not a global, not a database row. **The identity
artefact was not regenerated**, deliberately, so as not to disturb the `--check` hash gate during
another agent's test run. All database access was `SELECT`. No installer was run. No file owned by
another agent was edited. No commit was made. **The sole output of this task is this document.**

## References

- Finding **B4**; `docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §9 (the "already-live inconsistency")
- **ADR-BRAND-005** — the artefact this document reviews the payload of (corrected at §2 and §10)
- Decision **D-2**, `docs/branding-production/15-decision-record.md:27`
- `docs/branding-production/14-string-replacement-map.md` rows 4, 7, 8 and Part 6
- Locked constraint **C7** / BRAND-063/118 — upstream notices are PRESERVE
- Commits `a1c22b6a1` (profile introduced, with `skyeagle.uk`), `e16913d5b` (artefact + install-time defaults)
