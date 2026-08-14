# EV-090 — BRANDING SURFACE INVENTORY

**Requirement:** RDY-0090 · **Gates:** G1 G4 · **Date:** 2026-08-14
**Classification (§18.1):** **A** before screenshot capture · **B** before guided demo · **C** before
pilot · **D** may remain if legally required · **L** legal/licensing review required (→ RDY-0095)

---

## 0. Method, and what it does not cover — read before relying on this

**This is a source-and-configuration enumeration with live HTTP probes.** Every finding below is
backed by a command, a `file:line`, or a live response.

**It is NOT the screen-by-screen walk RDY-0090's acceptance requires.** That criterion reads: *"the
inventory is walked by a second person against the live product."* **That has not happened, and this
document does not substitute for it.** What it does is turn an unbounded "walk the product" task into
a bounded checklist with the machine-findable surfaces already resolved, so the human walk confirms
and extends rather than starts cold.

**RDY-0090 therefore cannot close on this document alone.** Sections 1–5 are complete; §6 is the
outstanding human step.

---

## 1. Favicons — the browser-tab identity

The category the brief names and neither source enumerated.

| Surface | State | Class |
|---|---|---|
| `public/images/logos/core/favicon/favicon.ico` — **the one actually declared** by the login page (`<link rel="shortcut icon" href="/public/images/logos/core/favicon/favicon.ico">`, HTTP 200, 15,086 bytes) | **BRANDED** — differs from `upstream/rel-820`, installed by `d9757fc55` | — |
| `public/images/favicon.ico` | **BRANDED** | — |
| `public/images/favicon-32x32.png` | **BRANDED** | — |
| **`interface/modules/zend_modules/public/images/favicon.ico`** | **STOCK OPENEMR** — byte-identical to `upstream/rel-820` | **C** |
| `interface/forms/questionnaire_assessments/lforms/webcomponent/favicon.ico` | **STOCK** — but this is a bundled **third-party** NLM LForms component; the icon is likely the library's, not OpenEMR's | **L** — confirm ownership before touching a third-party asset |
| `Documentation/EHI_Export/**/favicon.png` | Stock; **documentation only, not a runtime surface** | **D** |

**Verification:** `git hash-object <file>` vs `git rev-parse upstream/rel-820:<file>`.

**`/favicon.ico` at the web root returns 404.** Browsers request that path by default when no `<link>`
is present. The login page declares one explicitly so the tab is branded there — but **any page that
omits the declaration falls back to a 404 and shows a blank/default icon.** Worth one line in the
human walk.

## 2. Browser titles

| Surface | Title | Class |
|---|---|---|
| Login (`interface/login/login.php`) | **`Thiqa Login`** | — |
| Multi-site admin (`admin.php`) | **`Thiqa Site Administration`** (PR-01) | — |
| `main_screen.php` | **no `<title>`** | **A** — an untitled tab in a screenshot reads as unfinished |
| `setup.php` | no title returned on probe | **C** — operator-only |

**Authenticated interior titles were not enumerated** — they need the human walk (§6).

## 3. Patient portal

| Item | Value | Class |
|---|---|---|
| `portal_onsite_two_enable` | **`0` — the portal is DISABLED** | — |
| `portal_onsite_two_address` | **`https://your_web_site.com/openemr/portal`** — the installer default, containing both a placeholder domain **and the string `openemr`** | **C** |
| `rest_portal_api`, `portal_two_pass_reset` | `0` | — |

**Because the portal is off, this is a pilot-stage surface, not a demo-stage one.** If it is ever
enabled, that address appears in patient-facing links and emails and must be corrected first.

## 4. System-generated email

| Global | Value | Class |
|---|---|---|
| `patient_reminder_sender_name` | **empty** | **C** |
| `patient_reminder_sender_email` | **empty** | **C** |
| `practice_return_email_path` | **empty** | **C** |
| `EMAIL_METHOD` | `SMTP` | — |

**No sender identity is configured.** Any system-generated mail would go out with no display name and
no return path. Not demo-blocking (nothing sends during a demo), but it is a customer-visible surface
the moment a pilot turns reminders on.

## 5. Rendered product-name strings in templates

`grep -rln "OpenEMR" templates/` → **102 files**. Split by whether the string appears in rendered
output or only in a comment / JS identifier:

| | Count |
|---|---:|
| Likely **rendered** (element text, `title=`, `alt=`, `placeholder=`) | **47** |
| Comment or JS-identifier only | 55 |

*(Heuristic, not exact — it is a bound for the human walk, not a verdict on each file.)*

**Confirmed example, and it dictates the fix method:**

```
templates/oauth2/oauth2-login.html.twig:92
  {{"OpenEMR Login"|xlt }}
```

**This is a rendered button label wrapped in `xlt()`.** Per `docs/RebrandingBugs.md` **RB-01**,
editing the literal inside `xlt()` **orphans the existing translations**, because the English source
string is the catalogue key. **These must be delivered via `tools/branding/brand-strings.json`
(SET-TRANSLATION), not by patching the template.** Classification **B** for anything on an
authenticated screen a demo reaches, **C** otherwise.

> **This is the single most useful output of this inventory.** It says not just *what* to change but
> *by which mechanism* — and the wrong mechanism has already caused one recorded regression.

## 6. OUTSTANDING — the human walk (RDY-0090's actual acceptance)

A second person must walk the live product and confirm/extend the list. **These cannot be resolved
from source**, and each is a category the brief explicitly names:

| # | Surface | Why source cannot answer it |
|---|---|---|
| 1 | **Printed report headers** | Determined at render time from facility + globals; must be printed to see |
| 2 | **PDF output** (encounter PDFs, statements, the eye-exam PDF `store_PDF` path) | Generated by mPDF at runtime; header/footer content is not statically greppable |
| 3 | **Authenticated interior browser titles** | Only §2's unauthenticated pages were probed |
| 4 | **Statement / superbill layouts** | `sites/default/statement.inc.php` is site-configurable |
| 5 | **Any residual logo on interior screens** | Requires visual inspection |
| 6 | **The 47 rendered-string templates** | Confirm which actually appear on a reachable screen |

**Method for the walk:** open each surface as a demo role, screenshot, and mark **one** classification
per surface. **Anything uncertain is marked `L` and routed to RDY-0095 — never guessed.** That rule
is in the acceptance criteria and matters most for §1's third-party LForms asset and for anything
carrying an upstream copyright notice.

## 7. Routed to RDY-0095 (legal / licensing)

| Item | Question |
|---|---|
| LForms webcomponent favicon | Third-party NLM asset — may we alter it, and must attribution remain? |
| Any surface carrying an upstream copyright or GPL notice | Which must remain visible after branding? **This is RDY-0095's core question and is not answered here.** |
| `acknowledge_license_cert.html` | Already suppressed by config + Apache deny (`CLAUDE.local.md` §10) under locked constraint C7 as PRESERVE. **Confirm that treatment survives legal review** |

## 8. Summary

| Class | Count | Items |
|---|---:|---|
| **A** — before screenshot capture | **1** | `main_screen.php` has no `<title>` |
| **B** — before guided demo | **≤47** | Rendered `OpenEMR` strings on reachable screens — exact count needs §6 |
| **C** — before pilot | **6** | Zend favicon · portal address · 3 email-identity globals · `setup.php` title |
| **D** — may remain | **1** | Documentation favicons (not runtime) |
| **L** — legal review | **3** | LForms asset · upstream copyright notices · acknowledgements page treatment |

**Nothing found here blocks the flagship demo.** The single **A** item is a missing `<title>` on one
screen. The bulk of the work is **B**, is already understood, and has a known delivery mechanism
(string catalogue, not template edits).
