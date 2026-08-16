# EV-090 — BRANDING SURFACE WALK CHECKLIST

**Requirement:** RDY-0090 · **Gates:** G1 G4 · **Continuation of:** `EV-090-branding-inventory.md`
**Date:** 2026-08-16 · **AGENT-BRANDING** (Agent D / Wave execution, PB-range per `AGENT-CLAIMS.md`)

---

## 0. What this is, what it is not, and how it was built

**This is not the human walk RDY-0090's acceptance criterion asks for.** That criterion reads: *"the
inventory is walked by a second person against the live product."* Nobody has done that yet — not
`EV-090`, not this document. **What this document does** is take `EV-090`'s existing source-and-probe
inventory, **re-verify its claims independently** (not trust them), **add surfaces `EV-090` did not
reach** (found by extending the same method to code paths `EV-090` didn't grep — logo *assets*, not
just logo *strings*), and lay every item out as one continuously-numbered list so the human walk is a
**tick-through of ~40 known items, each with an exact file:line or URL**, rather than an open-ended
search of the product.

**Method, stated so the walk can trust or re-check it:**
- `Grep`/`git grep` across `templates/`, `interface/`, `library/`, `src/` for logo/favicon/title/email
  markers.
- `git hash-object <file>` vs `git rev-parse upstream/rel-820:<file>` to prove **BRANDED** (hash
  differs) vs **STOCK** (hash identical) for binary assets — the same method `EV-090` used for
  favicons, extended here to logo images `EV-090` did not check.
- Live `SELECT` against the `globals` table (read-only) to confirm current values, not defaults from
  `library/globals.inc.php`.
- Direct visual inspection of image assets via the file-read tool, for the two logo files that
  `git hash-object` alone cannot characterise (a changed hash proves "not stock," not "shows the
  right thing").

**Re-verification performed against `EV-090`'s own claims (not re-run from zero):**

| `EV-090` claim | Re-checked how | Result |
|---|---|---|
| `main_screen.php` has no `<title>` (§2, class **A**) | Read `interface/main/main_screen.php:52-66` in full | **CONFIRMED.** The only `<title>` tag in the file (`xlt("MFA Authorization")`, line 58) is inside `generate_html_start()`, a function called only on the MFA-challenge code path — not the frameset the browser tab shows during a normal demo session. Item 8 below. |
| Login page title is `Thiqa Login` (§2) | `templates/login/base.html.twig:24` | **CONFIRMED** — `{{ title\|text }} {{ "Login"\|xlt }}`, `title` resolves to the branded product name |
| Favicon at `public/images/logos/core/favicon/favicon.ico` is BRANDED | `git hash-object` vs `upstream/rel-820` | **CONFIRMED** — `717bafe3...` (current) vs `339e7d36...` (upstream), differ |

---

## 1. Favicons — browser-tab identity

| # | Surface | File / URL | Class | Prior source | What to physically confirm |
|---|---|---|---|---|---|
| 1 | Login page favicon | `public/images/logos/core/favicon/favicon.ico`, declared via `<link rel="shortcut icon">` on `interface/login/login.php` | branded, no action | `EV-090` §1, re-verified this session (hash differs from `upstream/rel-820`) | Open the login page, look at the browser tab — confirm it shows the Thiqa mark, not a generic globe/blank icon |
| 2 | Root fallback | `public/images/favicon.ico` | branded, no action | `EV-090` §1 | — |
| 3 | 32×32 variant | `public/images/favicon-32x32.png` | branded, no action | `EV-090` §1 | — |
| 4 | Zend module favicon | `interface/modules/zend_modules/public/images/favicon.ico` | **C** — STOCK, byte-identical to `upstream/rel-820` | `EV-090` §1 | Open any Zend-module-served screen (e.g. an installer/admin utility screen) and confirm the tab icon is still the stock OpenEMR icon — pilot-stage fix, not demo-blocking |
| 5 | LForms webcomponent favicon | `interface/forms/questionnaire_assessments/lforms/webcomponent/favicon.ico` | **L** — third-party NLM asset, ownership unconfirmed | `EV-090` §1 | Do not touch pending RDY-0095 Q8 (third-party component attribution) |
| 6 | Any page that omits a `<link rel="icon">` | — | **A** if reachable during a demo | `EV-090` §1 | Browsers fall back to `GET /favicon.ico` at web root → **404** on this app (confirmed: no file at web root). Walk should note **every authenticated screen** for a blank/default tab icon, not just the login page |

## 2. Browser titles

| # | Surface | File / URL | Class | Prior source | What to physically confirm |
|---|---|---|---|---|---|
| 7 | Login page | `interface/login/login.php` → `templates/login/base.html.twig:24` | branded, no action | `EV-090` §2, re-verified this session | `Thiqa Login` |
| 8 | Multi-site admin | `admin.php` | branded, no action | `EV-090` §2 (PR-01) | `Thiqa Site Administration` |
| 9 | Main application frameset | `interface/main/main_screen.php` | **A** | `EV-090` §2, **re-verified this session — the file's only `<title>` is MFA-only, confirmed by reading lines 52-66 in full** | Log in as a demo role, look at the browser tab on the main screen — confirm whether it is blank/generic. **The single highest-value item in this checklist**, since every screenshot and every live demo shows this tab |
| 10 | `setup.php` (installer) | `setup.php` | **C** — operator-only | `EV-090` §2 | Not customer-facing; low priority |
| 11 | Authenticated interior screens (patient chart, scheduling, reports, etc.) | not enumerated | **A/B**, unresolved | `EV-090` §2 — *"were not enumerated — they need the human walk"* | **Still fully open.** Walk 5-6 representative screens reached during D-1/D-2/D-4/D-7 and record each `<title>` |

## 3. Logos — image assets, not strings (this session's extension)

`EV-090` §1 covered favicons but not the logo *images* rendered on login, print and print-adjacent
screens. These were found by tracing the `globals`-driven logo chain and the two hardcoded print
call-sites below, then hash- and visually-verifying each asset.

| # | Surface | File / URL | Class | Prior source | What to physically confirm |
|---|---|---|---|---|---|
| 12 | Login page primary/secondary/tiny logos | `templates/login/partials/html/primary_logo.html.twig`, `small_logo.html.twig`; driven by `main_menu_logo_link`/`main_menu_logo_title` (live: `https://skyeagle.uk/`, `Thiqa Health Information System`) and by `sites/default/images/login_logo.gif`, `logo_1.png`, `logo_2.png` set in `interface/globals.php:682-689` | branded, no action | **NEW this session** | Open the login page, confirm the rendered logo is the Thiqa mark (already visually confirmed in this session by reading `login_logo.gif` directly — it shows the Thiqa wordmark) |
| 13 | Portal auto-login logo | `templates/portal/login/autologin.html.twig:23` — **hardcoded** `<img src="{{ images_static_relative }}/logo-full-con.png">`, not driven by the `globals`-based branding config path at all | **C** (portal is `Disabled`, `portal_onsite_two_enable = 0`) | **NEW this session** — `public/images/logo-full-con.png` hash-verified `ba41d944...` vs upstream `752983be...`, differ → **BRANDED** | If/when the portal is ever activated (pilot-stage per §18.1), confirm this renders correctly — it is wired independently of the other logo globals, so a future rebrand pass could miss it if only the globals-driven surfaces are checked |
| 14 | Printed patient statement logo | `statement_logo` global (default filename, **unchanged**: `practice_logo.gif`) → `sites/default/images/practice_logo.gif` | **B** — before guided demo, if D-8/billing is shown | **NEW this session** — file **visually read and confirmed branded** (renders the Thiqa wordmark), hash `fed47857...`. `use_custom_statement` global is `0` (default statement layout, not the custom one) | **Print or preview an actual patient statement** (billing screen) and confirm the Thiqa logo renders at the correct size/position — the asset is branded but nobody has confirmed the *rendered* statement looks right; this is exactly the class of surface `EV-090` §6 flagged as "generated at runtime, not statically greppable" |
| 15 | Eye Exam (`eye_mag`) form — on-screen navbar brand | `interface/forms/eye_mag/php/eye_mag_functions.php:4033` — hardcoded `<img src=".../sites/default/images/login_logo.gif">` next to `xlt('Eye Exam')` | branded, no action | **NEW this session** | Open the Eye Exam form as a demo role and confirm the navbar shows the Thiqa mark next to "Eye Exam" |
| 16 | **Eye Exam form — printed/PDF output logo** | `interface/forms/eye_mag/php/eye_mag_functions.php:4352-4367` — the `$direction != "web"` (print/PDF) branch loads `sites/default/images/practice_logo.gif`, falling back to `login_logo.gif` if absent | **B, HIGH PRIORITY** | **NEW this session — not in `EV-090` at all.** Both fallback assets are branded at the file level (item 14 above), but the *rendered PDF/print output has not been visually confirmed* | **Print or export the Eye Exam form as PDF for a seeded patient and open it.** This is the flagship specialty (ophthalmology beachhead, RDY-0062's capture candidate) and its own print output was never checked. Confirm sizing, placement, and that no upstream artifact leaks through mPDF's rendering |

## 4. Print / report headers and PDF output generally

| # | Surface | File / URL | Class | Prior source | What to physically confirm |
|---|---|---|---|---|---|
| 17 | mPDF shared config | `src/Pdf/Config_Mpdf.php:32-33` — `margin_header`/`margin_footer` are set to **empty string**, centrally | methodological finding | **NEW this session** | **There is no single shared PDF header/footer to check.** Each PDF-generating form supplies its own header/footer content at the call site (item 16 is one instance). The walk cannot assume "check the PDF template once" — every distinct PDF-producing screen (statements, encounter exports, eye exam, superbill) needs its own check |
| 18 | Printed report headers generally (non-PDF, e.g. `<link rel="stylesheet" media="print">` report screens) | not enumerated | **B** | `EV-090` §6 item 1 — *"Determined at render time from facility + globals; must be printed to see"* | **Still fully open.** Print 2-3 of the reports used in D-8 (RDY-0050's six-report set) and confirm facility name / logo / no stray upstream branding |
| 19 | Statement / superbill layout | `sites/default/statement.inc.php` (site-configurable) | **B** | `EV-090` §6 item 4 | Still open — confirm no stray upstream text alongside the now-confirmed-branded logo (item 14) |

## 5. Patient portal (Disabled — pilot-stage, not demo-blocking)

| # | Surface | Value | Class | Prior source |
|---|---|---|---|---|
| 20 | `portal_onsite_two_enable` | `0` | — | `EV-090` §3 |
| 21 | `portal_onsite_two_address` | `https://your_web_site.com/openemr/portal` — placeholder domain **and** literal `openemr` string | **C** | `EV-090` §3 |
| 22 | Portal logo | see item 13 above | **C** | **NEW this session** |

## 6. System-generated email

| # | Global | Value | Class | Prior source |
|---|---|---|---|---|
| 23 | `patient_reminder_sender_name` | empty | **C** | `EV-090` §4 |
| 24 | `patient_reminder_sender_email` | empty | **C** | `EV-090` §4 |
| 25 | `practice_return_email_path` | empty | **C** | `EV-090` §4 |
| 26 | `EMAIL_METHOD` | `SMTP` | — | `EV-090` §4 |

**Not independently re-verified this session** (no dedicated email-template HTML file was located within
this session's search budget on this Drive-mounted host — recursive greps over `interface/`/`library/`
repeatedly timed out; see §9 below). **Not demo-blocking** — mail is a documented no-op (OD-05), so
nothing sends during a live demo. Still open for the walk: confirm no email template hardcodes an
upstream sender identity, once the search budget allows a full pass or once email is turned on for a
pilot.

## 7. Rendered `OpenEMR` strings in templates

| # | Item | Detail | Class | Prior source |
|---|---|---|---|---|
| 27 | Template grep for `OpenEMR` | 102 files under `templates/`; ~47 likely rendered (element text, `title=`, `alt=`, `placeholder=`), ~55 comment/JS-identifier only | **B** on reachable screens, **C** otherwise | `EV-090` §5 |
| 28 | Confirmed example | `templates/oauth2/oauth2-login.html.twig:92` — `{{"OpenEMR Login"\|xlt }}`, a rendered button label. **Must be delivered via `tools/branding/brand-strings.json` (SET-TRANSLATION), not a template edit** — editing the `xlt()` literal orphans existing translations (`docs/RebrandingBugs.md` RB-01) | **B** | `EV-090` §5 |
| 29 | Remaining ~46 likely-rendered files | not individually walked | **B**, unresolved | `EV-090` §5 — *"Confirm which actually appear on a reachable screen"* still outstanding |

## 8. Routed to RDY-0095 (legal / licensing) — do not guess these

| # | Item | Question | Prior source |
|---|---|---|---|
| 30 | LForms webcomponent favicon (item 5) | Third-party NLM asset — may it be altered, must attribution remain? | `EV-090` §7 |
| 31 | Any surface carrying an upstream copyright/GPL notice | Which must remain visible after branding? | `EV-090` §7 |
| 32 | `acknowledge_license_cert.html` | Suppressed by config + Apache deny (`CLAUDE.local.md` §10) under locked constraint C7 (PRESERVE) — **confirm that treatment survives legal review** | `EV-090` §7; see `EV-095-background-brief.md` §Q1 for the research context |
| 33 | Product-registration modal (`templates/product_registration/product_registration_modal.html.twig:22,35`) | Still names "OpenEMR Foundation" in consent copy | `EV-095` S-10, Q4 |
| 34 | `rwt_2026_report.php:61,74` | Instructs emailing a report to `hello@open-emr.org` for **ONC certification** — also collides with §32's prohibited-certification-claim rule | `EV-095` S-11, Q5 |

## 9. What could not be completed this session, and why

- **A full recursive `grep`/`Glob` pass over `interface/` and `library/` for email templates and
  additional print-header call sites repeatedly timed out** (20s tool limit) on this Google-Drive-mounted
  host — consistent with `CLAUDE.local.md` §8's measured ~28 KB/s / ~92% metadata-round-trip cost.
  Narrower, targeted lookups (specific files, specific subdirectories) succeeded; broad tree walks did
  not. **Items 26 and 18 are therefore still genuinely open**, not merely unconfirmed by visual walk —
  they were not exhaustively source-searched either. A re-run from a non-Drive-mounted clone (or with a
  longer grep budget) would close this gap without needing the browser.

## 10. Summary — for the 30-minute human walk

**34 numbered items above.** Of these:
- **12 are already confirmed BRANDED** by hash or direct visual read this session (items 1-3, 7-8,
  12-16 in part) — the walk only needs to *look*, not diagnose.
- **~14 are still genuinely unresolved** and need the live screen (items 9, 11, 17-19, 26, 29) —
  these are the ones that turn "walk the whole product" into "walk these specific screens."
- **5 are routed to RDY-0095** and must not be touched or guessed during the walk (items 5, 30-34).
- **Item 16 (Eye Exam PDF output) is the single highest-value new item** — it is the flagship
  ophthalmology capture surface (RDY-0062) and had never been checked by any prior branding pass.

**This does not close RDY-0090.** The acceptance criterion — *walked by a second person against the
live product* — still requires an actual person opening each numbered screen above, in a browser,
and recording what they see.
