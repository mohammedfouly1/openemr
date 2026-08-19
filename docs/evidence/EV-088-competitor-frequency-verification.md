# EV-088 — Re-verification of the 9 unverified Source C dossiers

**Requirement:** RDY-0088 (competitive frequency figures withheld until re-verified) · **Gates:** G5,
G6 · **Owner:** Product Marketing · **Source:** GTM §2.3, §35, R-12; Source C §24.1, §24.2 item 6
**Produced:** 2026-08-16 · **Agent D (AGENT-COMP)**, claimed in `AGENT-CLAIMS.md` under "Agent D — Wave
execution" before this file was written; confirmed still unclaimed by any other agent as of this run.

**This file does not close RDY-0088.** It re-runs one step of Source C §24.2 item 6 — live
verification of the 9 dossiers marked "Limited" — and reports what changed. It does not re-run the
multi-dimension scoring (§6 of Source C) that would be required before any frequency figure could be
recomputed, and per RDY-0088's own acceptance criterion it deliberately does not print a number.

---

## 1. Where the 9 came from, and why this is not the scan RDY-0088 warns against

RDY-0088's own text warns that a naive scan for `[0-9]+ of (16|11|26)` over the document set produces
false positives, because 16/11/26 are simultaneously the competitor-set sizes **and** unrelated counts
elsewhere (dormant clinical forms, encounter forms) — see `docs/Marketing-MVP-and-Launch-Readiness-
Requirements.md:4214-4220` and `EV-056-057-088-claim-discipline.md` §4.2/§5. That warning applies to
scanning **marketing artefacts for a wrongly-published frequency figure** — a different task from the
one this file performs.

**This file's task — identifying which 9 of the 26 dossiers are unverified — used no regex or numeral
scan at all.** Source C (`docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md.pdf`, extracted via
`pdftotext -layout`) names the 9 explicitly, twice, as a literal enumerated list — §5.1's "Evidence-
depth summary across the 26" table and §24.2 item 1's remediation list. Both enumerations agree
verbatim on the same 9 codes. There is no pattern-matching step, so the "16 of 16 forms" /
"0 of 16 forms" collision the warning describes cannot occur here — it only affects scans that search
prose for a numeral pattern, and this step never does that.

**The 9, per Source C §5.1 and §24.2 item 1 (verbatim identity):**

| Code | Name | Sheet URL (Source C) | Source C's stated reason for "Limited" |
|---|---|---|---|
| C-15 | MediSys | `medisys.com.sa` | Name heavily collided internationally; no substantive marketing content verified |
| C-16 | TEKNOSys PolyCare | `teknosysweb.com/clinic-management/` | Dossier incomplete beyond category label |
| C-18 | MAEN.MEDS (M3n Technology) | `m3ntech.com` | Unresolved identity cluster with MEDS(ambiguous)/MEDEX/Medex Horizon |
| C-19 | Kizen Clinic | `kizensoft.com` | Name collision risk with an unrelated US CRM platform |
| C-20 | e-Medicare (e-MCS) | `e-mcs.org` | `.org` domain anomaly on a commercial HIS product; name collides with US Medicare |
| C-21 | HMISFOX | `hmisfox.com` | Sheet itself flagged Low confidence |
| C-22 | Solver HMIS | `solver-erp.com/…/HMIS-Product-Profile-KSA.pdf` | Only evidence was a PDF brochure (Priority 3), not a marketing site (Priority 1/2) |
| C-24 | DenTech (KSA) | `dentech.com.sa` | Confirmed name collision with an unrelated US vendor at `dentech.com` |
| C-26 | Avicenna (Alhazen Tech) | `alhazentech.com/avicenna/` | Name collides with unrelated regional healthcare entities |

All 9 were, per Source C, excluded from every scorecard and frequency table — none of the published
figures ("0 of 16", "1 of 16", "0 of 11 GCC", "1.9/5") are computed over any of these 9.

## 2. Verification method used against each live site

**WebFetch first** (renders the live page through a fetch+summarize pass), **WebSearch as fallback**
only when WebFetch failed outright (DNS failure, HTTP error, timeout), **capped at 2 reasonable
attempts per competitor** per this task's time-management instruction — no extensive retrying. Date
accessed for all 9: **2026-08-16**. This is a **homepage-level spot check** — it answers "does a live
marketing surface exist today and what does it say," not Source C's own "Full" standard (homepage +
internal pages) or "Verified" standard (official pages confirmed current across multiple sources). It
is sufficient to answer the yes/no question this task asks; it is **not** sufficient by itself to
promote any of the 9 to Source C's Full/Verified tier or to re-run §6 scoring.

## 3. Per-competitor findings

### C-15 — MediSys (`medisys.com.sa`)
- **Accessed:** 2026-08-16, WebFetch, succeeded on first attempt.
- **Found:** A live, real medical-informatics vendor (est. 1989, operations in 5 countries including
  Saudi Arabia), marketing a suite — AcuCare, HemoCare, Weblab, BillSoft, Neonatal, AmbuCare, Elaj,
  Psy-med, Forentech, Connect. Homepage tagline "Regional Leader in Health Information System";
  technical claims about real-time data transmission and device integration.
- **Not found:** No audit-trail, role-based-access, configurability, or pricing claims anywhere on the
  homepage.
- **Does the original claim still hold?** **Partially superseded.** Source C's stated reason for
  exclusion was identity ambiguity ("no substantive marketing content verified"; heavy name collision).
  The live site resolves the identity question — this is a real, operating vendor with a genuine
  product suite, not a collision artifact. It was **not previously "no marketing surface"** — it was
  "not yet looked at" — and now it has been looked at once. A single homepage pass is not the
  multi-page "Full" depth Source C uses for its 9 already-scored full dossiers, so this competitor is
  better described as **promoted from Limited toward Verified, not yet at Full**.

### C-16 — TEKNOSys PolyCare (`teknosysweb.com/clinic-management/`)
- **Accessed:** 2026-08-16, two attempts (direct clinic-management page, then `/about-us/`), both
  returned **HTTP 520** (Cloudflare "unknown error"). WebSearch fallback succeeded.
- **Found (via WebSearch, third-party indexed content):** Real company, Jeddah, Saudi Arabia, founded
  2010; offers clinic management, e-invoicing, procurement, and cloud ERP; an active job listing for a
  full-stack role indicates ongoing development.
- **Not found:** Could not independently confirm current on-page marketing claims — both direct fetch
  attempts failed with a server-side error, not a DNS/host failure, so the site is plausibly live but
  was unreachable to this tool twice.
- **Does the original claim still hold?** **Unresolved, evidence points against "unverified."**
  Third-party evidence (job board, business-directory-style listing) is consistent with a real,
  operating vendor, matching Source C's own identity data. Direct re-verification failed on two
  attempts and was not pursued further per the time budget — **recorded as unreachable today, not as
  confirmed live**.

### C-18 — MAEN.MEDS / M3n Technology (`m3ntech.com`)
- **Accessed:** 2026-08-16. Direct WebFetch to `m3ntech.com` **timed out** (60s). WebSearch fallback
  succeeded.
- **Found (via WebSearch):** A live product subdomain, `meds.m3ntech.com`, indexed with the title
  *"M3n Technology-WEB.MEDS(ver.01.01.2025.07)[ZATCA APPROVED][NAPHIS APPROVED] MED.INSURANCE
  ENABLED"* — a versioned, actively maintained Saudi healthcare product with regulatory-approval
  claims (ZATCA, NAPHIS). Company presence confirmed on Facebook/X under "M3n Technology," Jeddah.
- **Not found:** No audit-trail/RBAC/configurability/pricing claims surfaced in the search snippets;
  page was not read directly (only indexed/cached content was available).
- **Does the original claim still hold?** **No longer holds as "no verified marketing surface."** This
  is now the strongest positive finding of the 9 — a versioned, dated, regulator-flagged live product
  page exists. Source C's own concern (disambiguating MAEN.MEDS from the MEDS(ambiguous)/MEDEX/Medex
  Horizon cluster) is **not resolved by this check** — that is a separate identity-clustering task
  (§24.2 item 2), not a site-liveness question, and is out of this file's scope.

### C-19 — Kizen Clinic (`kizensoft.com`)
- **Accessed:** 2026-08-16, WebFetch succeeded (page loaded, low content). WebSearch used to
  cross-check.
- **Found:** `kizensoft.com`'s homepage is minimal — a "Downloads" link and a copyright notice spanning
  2009–2024 — no product description. WebSearch found a live subdomain instance,
  `saudident.kizensoft.com`, suggesting the platform is deployed for at least one Saudi dental client,
  and a separate site `kizenclinic.com` that is an actual medical clinic's own site (not the software
  vendor).
- **Not found:** No marketing copy, positioning, or claims of any kind on the vendor's own domain.
- **Does the original claim still hold? Yes, largely.** Source C's specific worry — a name collision
  with an unrelated platform trading as "Kizen" — is **confirmed real**: `kizen.com` is a distinct US
  CRM/healthcare-solutions company unrelated to this Saudi dental product. The vendor's own domain
  remains marketing-content-free (a bare downloads/login shell), consistent with "no verified marketing
  surface." This is the one case where re-verification **supports** the dossier's original disposition
  rather than revising it.

### C-20 — e-Medicare / e-MCS (`e-mcs.org`)
- **Accessed:** 2026-08-16, two separate WebFetch attempts (`e-mcs.org` and `e-mcs.org/about/`), both
  returned the same live-but-suspended hosting notice: *"This Account has been suspended."*
  WebSearch found search-engine-indexed pages (`/about.php`, `/contact-us/`, `/solution/`) from before
  the suspension, describing a company "E-MediCare Solutions," founded 2013, operating from Dubai
  Healthcare City, UAE, marketing a product called "iHealthCure."
- **Not found:** No live marketing content reachable today under any path tried.
- **Does the original claim still hold? Yes, and now on firmer ground.** Source C flagged the `.org`
  domain as anomalous and the dossier as unverified; today the domain's hosting account is confirmed
  suspended by two independent fetches. **This is a definitive, reproducible negative finding** — not
  "we didn't get around to checking," but "the site is currently down." No frequency table needs to
  account for a marketing surface that does not currently exist.

### C-21 — HMISFOX (`hmisfox.com`)
- **Accessed:** 2026-08-16. Direct WebFetch to both `hmisfox.com` and `www.hmisfox.com` failed with
  **DNS resolution errors** (`ENOTFOUND`) on both attempts. WebSearch fallback succeeded.
- **Found (via WebSearch, indexed content):** Google's index shows live-looking pages at
  `hmisfox.com/solutions` and `hmisfox.com/services` describing lab-equipment integration and a
  practice-management/HIS/telemedicine product line.
- **Not found:** No direct confirmation — this tool's DNS resolution failed twice against a domain that
  a search engine's crawler evidently reached previously (or still reaches from different
  infrastructure).
- **Does the original claim still hold?** **Ambiguous — recorded honestly rather than guessed.**
  Source C's own sheet already rated this competitor Low confidence before Phase 2. The indexed content
  suggests a real product exists; this tool could not independently reach it today. **Treated as
  unreachable, not as verified**, consistent with the task's instruction not to guess when a site is
  unreachable.

### C-22 — Solver HMIS (`solver-erp.com`)
- **Accessed:** 2026-08-16, WebFetch succeeded on first attempt.
- **Found:** A full marketing website, not merely the PDF brochure Source C's dossier was built on.
  Vendor: AFI Technologies, established 1995, operating across Saudi Arabia, Qatar, Oman and UAE.
  "Solver Healthcare" line named explicitly: Hospital Management Information System, Pharmacy
  Management, Clinic solutions, Lab Information System, Radiology Information System, Occupational
  Healthcare Management. Marketing language: "fully integrated ERP," "Power BI enabled dashboards,"
  "secure server architecture," "single source of truth." Site has full navigation (products,
  services, careers, news, blogs, contact) across regional pages.
- **Not found:** No explicit audit-trail, role-based-access, or pricing claims on the homepage;
  "customized solutions" mentioned but not detailed.
- **Does the original claim still hold? No — clearly superseded.** Source C's own §24.2 item 3 flagged
  this exact competitor as a case where Priority-3 evidence (a brochure PDF) stood in for Priority-1
  evidence (a real marketing site) it assumed didn't exist. **It does exist.** Of the 9, this is the
  clearest case where the "Limited" disposition is now factually out of date — Solver has a real,
  navigable marketing site today, at the highest sheet-similarity score of the 9 (rank 5, score 90).

### C-24 — DenTech, KSA (`dentech.com.sa`)
- **Accessed:** 2026-08-16, WebFetch returned **HTTP 403 Forbidden** (likely bot-blocking). WebSearch
  fallback succeeded.
- **Found (via WebSearch, indexed content):** Confirms a real, distinct Saudi company — "Dentech,"
  Riyadh, founded 2004, over 20 years' experience, dental/clinic practice-management software for
  "hundreds of clinics and hospitals inside and outside Saudi Arabia" — under the `.com.sa` domain,
  with an `/en/` bilingual structure indexed (`dentech.com.sa/en/…`).
- **Not found:** No direct page content (403 blocked the fetch); relying on search-index summaries
  only.
- **Does the original claim still hold?** **The identity question is resolved, the content question is
  not.** Source C's specific concern was the collision with the unrelated US vendor at `dentech.com`
  (confirmed still a distinct company). The Saudi vendor's own domain is confirmed real and indexed
  with substantive content, but this tool could not read it directly (bot-blocked) to verify specific
  marketing claims (audit trail, RBAC, pricing) — those remain Unknown pending a successful direct
  read.

### C-26 — Avicenna, Alhazen Tech (`alhazentech.com/avicenna/`)
- **Accessed:** 2026-08-16, WebFetch succeeded on first attempt.
- **Found:** A live, real marketing page for "Avicenna," described as "a complete cloud-based
  healthcare solution package" for hospitals, pathology labs, pharmacies and clinics. Operator: Alhazen
  Technologies (Pvt) Ltd, **Islamabad, Pakistan** (business hours listed as GMT+5) — this refines, and
  partly contradicts, Source C's own "region GCC, not disclosed" note; the operating company is
  South-Asian, even if the product is marketed toward GCC buyers. Six named benefit claims, including
  explicitly: **"increased visibility through audit trail tracking"** and "automation and EMR flow
  across departments."
- **Not found:** No pricing, no explicit role-based-access description, no configurability claim beyond
  general "customization" language, and — importantly — **no visual demonstration of an audit trail**,
  only the prose claim quoted above.
- **Does the original claim still hold? No, on identity — but the material RDY-0088 figure is
  unaffected.** The marketing surface is real and live, so "no verified marketing surface" no longer
  holds. However, the specific GTM claim this could threaten is RDY-0062/§8.13's **"zero of sixteen
  competitors demonstrate audit or tamper-evidence visually; they assert security in prose"** — and
  Avicenna's finding is exactly a prose assertion ("track everything"), not a visual demonstration, the
  same pattern the 16 already-scored competitors show. **If Avicenna were added to the scored set on
  this evidence alone, the "0 of N demonstrate audit integrity visually" claim would still read 0 of
  N+1 — the qualifier ("visually," as opposed to asserted in prose) is exactly what survives.** This is
  the one finding worth flagging explicitly to whoever eventually re-runs the full §24.2 item 6 scoring
  pass, because it is the closest any of the 9 comes to touching a specific published figure.

## 4. Summary table

| Code | Competitor | Reachable today | Live marketing surface found? | Original "Limited/Unknown" still accurate? |
|---|---|---|---|---|
| C-15 | MediSys | Yes (WebFetch) | Yes — real, multi-product | No — identity resolved, real vendor |
| C-16 | TEKNOSys | No (520 x2; WebSearch only) | Indirect evidence only | Unresolved — unreachable directly |
| C-18 | M3n MEDS | No (timeout; WebSearch only) | Yes — versioned live product page (indirect) | No — real, actively maintained product |
| C-19 | Kizen | Yes (WebFetch) | No — content-free shell | **Yes — still accurate** |
| C-20 | e-MCS | Yes (WebFetch, x2) | **No — hosting suspended, confirmed** | **Yes — confirmed down, stronger than before** |
| C-21 | HMISFOX | No (DNS failure x2) | Indirect evidence only | Unresolved — unreachable directly |
| C-22 | Solver | Yes (WebFetch) | Yes — full site, not brochure-only | No — clearly superseded |
| C-24 | DenTech KSA | No (403; WebSearch only) | Indirect evidence only (identity confirmed) | Partially — content unverified, identity resolved |
| C-26 | Avicenna | Yes (WebFetch) | Yes — real site, prose audit-trail claim | No — identity resolved; scored-claim impact assessed as null |

**Reachable directly:** 5 of 9 (C-15, C-19, C-20, C-22, C-26). **Unreachable directly, evidence via
WebSearch only:** 4 of 9 (C-16, C-18, C-21, C-24) — none of these four were retried beyond 2 attempts,
per the time-management instruction. **Confirmed to have no live marketing surface:** 1 of 9 (C-20,
e-MCS — hosting suspended). **Confirmed unchanged from Source C's original disposition:** 1 of 9
(C-19, Kizen — collision risk validated, vendor domain still content-free).

## 5. What this file establishes, and what it deliberately does not

**Established:** Of the 9 dossiers Source C marked "Limited / Unknown," at least 6 (C-15, C-16, C-18,
C-22, C-24, C-26) now show credible evidence of a real, live marketing surface — meaning the
denominator behind every published frequency ("0 of 16," "1 of 16," "0 of 11 GCC," "1.9/5") is very
likely to grow once §24.2 item 6 is fully re-run, and RDY-0088's own §8 note that *"7 of the 9
unverified sit in the tier we will actually meet in deals"* reads as directionally correct on this
pass. One (C-20) is confirmed to have no current marketing surface at all. One (C-19) is confirmed
unchanged.

**Not established, and not attempted:** a recomputed frequency figure. Doing that correctly requires
Source C's full §6 scorecard methodology — multiple pages per competitor, scored across ~10 marketing
dimensions by structured comparative judgement, cross-checked the way the original 17 were — not a
single homepage fetch per competitor under a time budget. Publishing a new number from this pass alone
would repeat exactly the mistake RDY-0088 exists to prevent: a figure that looks precise but rests on
thinner evidence than it implies. **No frequency number is published in this file, or anywhere else,
as a result of this work.**

**Also not attempted:** Source C §24.2 items 2 (the four-way identity-cluster resolution:
MAEN.MEDS/MEDS(ambiguous)/MEDEX/Medex Horizon), 3 (the Solver/VIDA/iRis evidence-class escalation —
though this file's C-22 finding is directly relevant to it), 4 (the HIS_SoftWare.xlsx re-ranking), and
5 (Arabic-language review, which is RDY-0089's separate scope). These remain open regardless of this
file.

## 6. Recommendation

**RDY-0088 is not closeable on this evidence.** Its acceptance criterion is explicit: figures may be
recomputed "if the nine dossiers are completed" — and they are not; at most 6 of 9 gained a
homepage-level positive finding, and none reached Source C's own "Full" or "Verified" depth standard.
The correct next state is: **keep publishing the mechanism, not a number** (RDY-0088's own instruction,
honored here), and hand this file to whoever performs a full §24.2 item 6 re-run as a head start — it
narrows "9 completely unchecked dossiers" to "1 confirmed dead, 1 confirmed unchanged, 3 confirmed
live with real content, 4 indirectly evidenced but needing a direct re-fetch attempt (TEKNOSys, M3n,
HMISFOX, DenTech KSA all returned transient-looking errors — 520, timeout, DNS, 403 — worth a retry
from different network infrastructure rather than accepted as permanently unreachable)."

**Register status:** left as **BLOCKED — VALIDATION** / NOT READY, unchanged. Not marked closed by
this agent, per the closure contract (§0.0 Rule 5) and this task's explicit instruction that closure is
not this agent's call.

**⚠ Superseded by §8 below (2026-08-19), which narrows this to 3 of the 9 still open — read §9's
updated accounting before treating this section's "4 indirectly evidenced" figure as current.**

---

## 7. Addendum — second independent verification pass (2026-08-16, same session role)

**⚠ Process note first.** This addendum was produced by a second AGENT-COMP run that was assigned
this exact task independently, discovered mid-task (via `git log`, after `.git/index.lock` cleared)
that commit `dc6baee78` had already written §§1–6 above under the same filename. Per §0.0 Rule 4 —
"if you see a hunk you did not write, do not stage the file" — the second run's own full rewrite was
discarded rather than committed over §§1–6; this section appends only what its independent pass found
that §§1–6 did not, plus corroboration. **Flagged for whoever syncs `AGENT-CLAIMS.md`:** the RDY-0088
row apparently allowed two concurrent AGENT-COMP instances to both start this item — the claim
protocol prevented a *different* agent from colliding, but not a duplicate instance of the same one.

**Method, for continuity with §2:** same WebFetch-first/WebSearch-fallback approach, not capped at 2
attempts (a handful of retries with alternate paths/protocols were used, noted per-finding below).
Competitor-scoped scan from `EV-056-057-088-claim-discipline.md` §5 was run against this whole file
before every commit in this addendum's history; see §7.5.

### 7.1 C-16 — TEKNOSys: a direct positive finding, not only indirect evidence

§3's C-16 entry reached only indirect (job-listing) evidence after two direct attempts failed with
HTTP 520. This pass also got 520 from `teknosysweb.com` (both the clinic-management page and the
bare domain), but WebSearch surfaced a **different live domain, `teknosys.net`**, for a company
matching on every available identifying detail — Jeddah, Saudi Arabia; clinic management + pharmacy
management + cloud ERP + ZATCA e-invoicing; "15+ years experience," 100+ clients. **This one was
fetched directly and succeeded (HTTP 200)**, with substantive on-page content, not merely a search
snippet. Nothing on `teknosys.net` states outright that it supersedes `teknosysweb.com`, so this is
strong circumstantial evidence of a live domain migration, not a confirmed same-entity match — but
it upgrades C-16 from "indirect evidence only" to "a live, substantive, directly-fetched site
plausibly belonging to the same vendor, pending domain-continuity confirmation."

### 7.2 C-24 — DenTech (KSA): the identity question got more complicated, not less

§3's C-24 entry treats the identity question as resolved (distinct from the US `dentech.com`) and
the content question as open (403-blocked). This pass surfaces two further wrinkles neither §3 nor
Source C had on record:

1. A WebSearch-indexed page at `dentech.com.sa/en/about-ar.html` carries the page **title "About us
   - Tech Meta Solution"** — a company-name mismatch this pass could not resolve, because the page
   itself 403'd on every direct attempt (root domain, the Arabic-labelled about path, and
   `/en/our-services/` were all tried; all three 403'd).
2. WebSearch also surfaced a **third, separate domain, `dentech.sa`** ("Dentech AI — Dental ERP &
   Electronic Dental Records"), distinct from both `dentech.com.sa` and the already-known-unrelated
   US `dentech.com`.

Neither wrinkle was independently confirmed live (both are search-index findings only, same
limitation as §3 notes for its own C-24 finding). **Recorded so the next re-verification pass does
not treat C-24's identity as settled** — it is at minimum a three-domain situation
(`dentech.com` US / `dentech.com.sa` Saudi, access-blocked / `dentech.sa` unconfirmed relationship),
not the two-domain collision Source C and §3 both describe.

**§8 below (2026-08-19) substantially resolves this wrinkle** — see §8.4: "Tech Meta Solution" reads
as the developer/operator brand behind `dentech.com.sa`, not a mismatched different company.

### 7.3 Corroboration, no new information

This pass's independent fetches of C-15 (MediSys — live, substantive, matches §3's finding),
C-19 (Kizen — homepage still content-free, `/downloads` and `/en` both 404, matches §3's "confirmed
unchanged" verdict), C-20 (e-MCS — root and `/hospital-information-system/` both returned the same
live "Account Suspended" hosting notice, matches §3's "confirmed down" finding), C-21 (HMISFOX — DNS
resolution failure on `hmisfox.com`, `www.hmisfox.com`, both protocols, four attempts total; matches
§3's "unreachable" verdict), C-22 (Solver — live, full navigable site with regional pages and
testimonials, matches §3's "clearly superseded" finding) and C-26 (Avicenna — live, Islamabad,
Pakistan-registered operator, no GCC-targeting language found on the page, matches §3's identity
finding) independently arrived at the same conclusions as §3 for all six. No conflicting evidence
was found for any of these six.

### 7.4 Net effect on §5's establishment and §6's recommendation

**Unchanged.** This addendum does not move any competitor from "indirectly evidenced" to "Source
C's Full/Verified depth standard," does not recompute a frequency, and does not alter §6's
recommendation. It sharpens two of the four "indirectly evidenced" rows in §4's summary table
(C-16 now has a direct fetch behind it in addition to indirect evidence; C-24's identity question
is now known to be messier, not simpler) and leaves the other seven rows exactly as §3/§4 found
them.

### 7.5 Self-check: competitor-scoped scan against this file

Per RDY-0088's warning against a naive `[0-9]+ of (16|11|26)` scan (it false-positives on
"16 of 16 installed cleanly" / "0 of 16 forms" elsewhere in this document set — see
`EV-056-057-088-claim-discipline.md` §4.2), the competitor-scoped pattern was run before commit:

```bash
grep -rniE "[0-9]+ of (16|11|26)[^0-9]{0,60}(competitor|vendor|GCC|market|publish|demonstrat|discuss)" \
  docs/evidence/EV-088-competitor-frequency-verification.md \
  | grep -viE "prohibit|must not|never|withheld|until|provisional|hold"
```

**Run against the whole file: 2 lines** (`:47` and `:232`, both in §§1/5 above this addendum, both
pre-existing text from commit `dc6baee78`, not introduced here). Read in context, both are the
document naming the currently-withheld figures by quoted value while describing what is withheld
and why ("None of the published figures... are computed over any of these 9"; "...every published
frequency ... is very likely to grow once §24.2 item 6 is fully re-run") — the same category §4.4 of
`EV-056-057-088-claim-discipline.md` already accepted for this document set: internal evidence-file
prose about the hold, not a customer-facing assertion of the figure as settled. **Neither is a new
violation, and this addendum introduces neither** — re-running the same command scoped to only the
lines added by this addendum (from `## 7.` onward) returns 0. Recorded rather than silently
adjusted, per the same discipline `EV-056-057-088-claim-discipline.md` §4.3 applied to its own
author's violation: the honest number is 2-in-context, not a claimed 0 that does not survive
literal reproduction of the command shown.

---

## 8. Third independent pass — dossier-scoped claim (2026-08-19)

**Claimed, per `AGENT-CLAIMS.md`, before this section was written:** exactly `C-16`, `C-18`, `C-21`,
`C-24` — the 4 dossiers this session's own re-read of §3/§4/§7 above found still genuinely open, after
re-deriving that count from the dossier-level detail rather than trusting either prior summary number
(see the claim commit for the full method). The other 5 (`C-15`, `C-19`, `C-20`, `C-22`, `C-26`) are
not touched here — §3/§4/§7 already treat them as terminal (2 confirmed-real-and-live, 1 confirmed
dead, 1 confirmed unchanged, 1 identity-resolved-with-claim-impact-assessed).

**Method, for continuity with §2/§7:** WebFetch first, WebSearch fallback, capped at 2 direct-fetch
attempts per URL per this task's own convention. Date accessed for all 4: **2026-08-19**.

### 8.1 C-18 — MAEN.MEDS / M3n Technology: direct fetch now succeeds

- **Accessed:** 2026-08-19, WebFetch to `meds.m3ntech.com` (the exact subdomain §3/§7 only ever saw
  via WebSearch snippets) — **succeeded on the first attempt.**
- **Found:** "M3N MEDS" (مؤسسة معن لتقنية المعلومات / M3nTech) — a web-based clinic management system,
  bilingual (Arabic/English) login page, versioned **"Build 15"** with an update date of
  **01/07/2026** — i.e. actively maintained into this year, not a stale snapshot. Username/password
  authentication confirmed live.
- **Not found:** No audit-trail, RBAC, configurability, or pricing claims on the login page itself
  (expected — it is an authentication gate, not a marketing homepage); ZATCA/NAPHIS badges seen in
  §3's WebSearch-only pass were not re-confirmed on this specific page (they may live on a different
  page of the same product).
- **Does the original claim still hold? No — and this is now confirmed, not inferred.** §3/§7 already
  concluded C-18 "no longer holds as no verified marketing surface" from indexed search content alone;
  this pass confirms the identical conclusion via a successful direct fetch of the live product. **C-18
  moves from "indirectly evidenced" to resolved** — no further reachability research needed. The
  MAEN.MEDS/MEDS(ambiguous)/MEDEX/Medex Horizon identity-cluster question (Source C §24.2 item 2)
  remains untouched, as it was in §3 — that is a separate task.

### 8.2 C-16 — TEKNOSys PolyCare: original domain still unreachable, identity now on firmer ground

- **Accessed:** 2026-08-19, WebFetch to `teknosysweb.com` (bare domain) — **HTTP 520**, the same
  Cloudflare error §3 and §7 both recorded against this exact domain. This is the **third independent
  session** in which `teknosysweb.com` has returned this identical error — no longer read as
  transient.
- **Found (via WebSearch):** Search results show a **parallel page structure across both domains**:
  `teknosysweb.com/about-us/` and `teknosys.net/about-us/` both indexed, and matching product-login
  paths — `teknosysweb.com/inventory/signin/` alongside `teknosys.net/inventory/dashboard/`. This is
  stronger, more specific evidence than §7.1 had (which relied on matching prose descriptions) — the
  *same application paths* exist on both domains, consistent with one platform served from two
  domains, not two coincidentally-similar companies.
- **Not found:** No direct fetch of `teknosysweb.com` has ever succeeded, in any of 3 independent
  sessions (§3, §7, this pass) — 6 total attempts, uniformly HTTP 520. `teknosys.net` was fetched
  directly and succeeded in §7.1 (not repeated here).
- **Does the original claim still hold? No, on identity — same-domain reachability remains
  unresolved.** Treat `teknosysweb.com` itself as unlikely to answer a future direct-fetch attempt
  without different network infrastructure (§6's own suggestion); `teknosys.net` is the reachable
  surface for this vendor going forward. Recorded as **live real vendor, most likely same-entity as
  the confirmed-reachable `teknosys.net`, original dossier domain durably unreachable** — better
  evidenced than §7.1 but not fully closed, since "most likely" is not "confirmed."

### 8.3 C-21 — HMISFOX: DNS failure now confirmed durable across 3 sessions; still the most stubborn of the 9

- **Accessed:** 2026-08-19, WebFetch to `www.hmisfox.com` and `hmisfox.com` — **both `ENOTFOUND`**,
  identical to every prior attempt. Across §3 (2 attempts), §7.3 (4 attempts), and this pass (2
  attempts): **8 total direct-fetch attempts, 0 successes, 0 variation in error type.**
- **Found (via WebSearch, fresh):** Search engines still index a live-looking site — homepage,
  `/careers`, `/news-and-events/electronic-bills`, `/partners` — unchanged in kind from §3's finding.
  One new identity nugget: a discrepancy between a stated 2013 founding and an on-site note suggesting
  operation since 2008 under a named individual ("SAAD MUSLIM AL AMERI") — not resolved, not material
  to the reachability question, recorded rather than chased further (identity-cluster work is out of
  this file's scope per §5).
- **Does the original claim still hold?** **Still ambiguous, but the ambiguity itself is now better
  characterised.** This is not a transient error pattern like C-16's 520s or C-24's 403s — it is a
  **consistent DNS resolution failure specific to this tool's network path**, while a completely
  different piece of infrastructure (a search engine's crawler) evidently still reaches the domain.
  That split — reachable to indexing, unreachable to direct fetch, unchanged across 3 independent
  sessions — is itself the most stable finding available for C-21. **Not upgraded to "confirmed dead"**
  (unlike C-20, which had a positive, reproducible "Account Suspended" response) and **not upgraded to
  "confirmed live"** (no direct read ever succeeded). Recorded honestly as the one dossier of the 9
  where more research through this tool's own network path is very unlikely to add anything further —
  the next genuinely new step is a fetch from different infrastructure, not another attempt from here.

### 8.4 C-24 — DenTech KSA: the domain cluster clarifies rather than resolves; content still unread

- **Accessed:** 2026-08-19. Direct WebFetch to `dentech.sa` — **HTTP 403** (same block as
  `dentech.com.sa` in §3/§7). Direct WebFetch to `dentechmeta.com` — **connection refused**
  (`ECONNREFUSED`), a different failure mode from every other attempt in this file.
- **Found (via WebSearch):** Two findings that materially reframe §7.2's "identity got more
  complicated" note:
  1. **"Tech Meta Solution" reads as the developer/operator brand, not a mismatched company.**
     `dentechmeta.com`'s own indexed title is *"Tech Meta Solutions | Healthcare software development
     company in Riyadh, Saudi Arabia"* — the same page-title string §7.2 found on
     `dentech.com.sa/en/about-ar.html`. Read together, this is the same pattern already established
     elsewhere in this file (M3n Technology develops M3N MEDS at `meds.m3ntech.com`; AFI Technologies
     develops Solver Healthcare) — a developer company operating a distinctly-branded product site, not
     an identity collision.
  2. **A fourth domain surfaced:** `dentechksa.com` ("Dentech Ksa" — a login page), on top of the
     `dentech.sa` ("Dentech AI — Dental ERP & Electronic Dental Records") §7.2 had already found.
     `dentech.sa`'s own marketing text claims **"over 20 years of experience in the Saudi market"** —
     the identical tenure claim WebSearch surfaces for `dentech.com.sa`. That specific, matching claim
     is the strongest evidence in this file that `dentech.com.sa`, `dentech.sa` and `dentechksa.com`
     are the same vendor's current/legacy/regional domains, not three separate competitors — but it is
     still an inference from matching prose, not a direct read of any of them (all three either 403,
     refuse connection, or were never tried this session).
- **Not found:** No direct page content from any domain in the cluster — audit-trail, RBAC,
  configurability and pricing claims remain completely unread, exactly as in §3 and §7.
- **Does the original claim still hold?** **Identity substantially clarified, content question fully
  open, unchanged from §7.** The three/four-domain situation reads far more like one vendor's domain
  history than a genuine multi-competitor collision, but this file has still never successfully read a
  single page of `dentech.com.sa`, `dentech.sa`, `dentechmeta.com` or `dentechksa.com` directly — every
  attempt across 3 sessions has 403'd or refused the connection. This is the one dossier of the 4
  claimed here where identity work moved furthest and content verification moved not at all.

## 9. Updated remainder accounting (2026-08-19) — supersedes §6's "4 dossiers" language for these four

**After this pass, 6 of 9 are terminal** (unchanged from §5's list plus one promotion): `C-15`, `C-18`,
`C-19`, `C-20`, `C-22`, `C-26`. `C-18` is newly terminal this pass — §8.1's direct fetch confirms what
§3/§7 could previously only infer from search snippets.

**3 of 9 remain genuinely open** — `C-16`, `C-21`, `C-24` — each for a different, specific reason, not
interchangeably "unverified":

| Code | What's missing | What would resolve it |
|---|---|---|
| C-16 | A successful direct fetch of the *original dossier domain* (`teknosysweb.com`) itself — `teknosys.net` is reachable and matches on identity, but "most likely the same vendor" is not "confirmed" | A fetch of `teknosysweb.com` from different network infrastructure, or explicit same-entity confirmation (e.g. a page on either domain naming the other) |
| C-21 | Any direct read at all — 8 attempts across 3 sessions, 0 successes, DNS failure every time, while search-engine indexing still finds the site | A fetch from different network infrastructure/DNS resolver; this tool's own path is very unlikely to ever succeed here |
| C-24 | Any direct read at all — 403/refused on every domain in a now-4-domain cluster, across 3 sessions | A fetch from different network infrastructure, or a bot-blocking-tolerant method; identity clarity (§8.4) does not substitute for reading actual product-claim content |

**Note on a discrepancy with the register:** `docs/Marketing-MVP-and-Launch-Readiness-
Requirements.md` §47 (PB-300 sync, 2026-08-19) and `EV-GATE-SYNC-20260819.md:117` both already say "3
remaining" for RDY-0088. Before this pass that number did not hold up against the dossier-level detail
(this file's own `AGENT-CLAIMS.md` claim entry, written earlier today, shows the derivation — 4 were
actually open, not 3). **After this pass, the count genuinely is 3** — but by coincidence of today's
new work resolving C-18, not because the register's earlier arithmetic was correct. Recorded so a
future reader does not conclude the register was right all along for the wrong reason.

**RDY-0088 is still not closeable.** Even with 6 of 9 now terminal, none reached Source C's own
Full/Verified depth standard (§2's own caveat, unchanged), and 3 dossiers remain genuinely open. No
frequency figure is published in this file or anywhere else as a result of this pass.
