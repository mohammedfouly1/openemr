# EV-078 — ZATCA / NPHIES Primary-Source Regulatory Verification

**RDY item:** RDY-0078 — V-10: primary-source regulatory verification (EXT-01, EXT-02)
**Agent:** AGENT-REG (Agent D fleet), claimed in `docs/evidence/AGENT-CLAIMS.md` under "Agent D — Wave
execution, 2026-08-16"
**Date/time of this work:** 2026-08-16 (session date; access timestamps recorded per source below)
**Method:** `WebSearch` to locate candidate primary-source URLs, then `WebFetch` to retrieve and read
each candidate page/document directly. A URL is only cited as evidence below if it was **successfully
fetched and read in this session** — search-engine snippets and AI-synthesized search summaries are
not treated as verification of a source's content and are not cited as evidence.

**Scope discipline (binding, per the task brief):** secondary summaries, tax-advisory/vendor
commentary, consultancy explainers, and the Locked Decisions corpus's own Q21 citation are excluded as
evidence here, even where they agree with the primary-source finding. Several such sources were
returned by search and are named below only to record what they claim, never as the basis for a
finding. No claim is made that OpenEMR/Thiqa is ZATCA- or NPHIES-compliant; this record only
characterizes external regulatory obligations on the clinic, per RDY-0078's own instruction.

---

## EXT-01 — ZATCA Phase 2 ("Integration Phase") wave/threshold obligation

### Primary sources read directly

**Source 1**
- **URL:** `https://zatca.gov.sa/en/Pages/news-1426.aspx`
- **Document title:** "Criteria set for taxpayers in Wave 24 of E-invoicing" (ZATCA news release; the
  same release is also indexed under the title "ZATCA Determines the Criteria for Selecting the
  Targeted Taxpayers in Wave 24 for 'Integration Phase' of E-invoicing")
- **Publication date (as shown on the page):** 26 September 2025
- **Date accessed:** 2026-08-16
- **Passage relied on (paraphrased, not quoted at length):** ZATCA states that Wave 24 of the
  Integration Phase covers all taxpayers whose VAT-subject revenue exceeded SAR 375,000 in any of
  2022, 2023 or 2024, and that those taxpayers must integrate their e-invoicing solution with the
  Fatoora platform no later than 30 June 2026.

**Source 2**
- **URL:** `https://zatca.gov.sa/en/MediaCenter/News/Pages/Wave25-E-invoicing.aspx`
- **Document title:** "ZATCA Determines the Criteria for Selecting the Targeted Taxpayers in Wave 25
  for 'Integration Phase' of E-invoicing"
- **Publication date (as shown on the page):** 24 July 2026
- **Date accessed:** 2026-08-16
- **Passage relied on (paraphrased, not quoted at length):** ZATCA states that Wave 25 covers all
  taxpayers whose VAT-subject revenue exceeded SAR 187,500 in any of 2022, 2023, 2024 or 2025, with an
  integration deadline of 1 February 2027.

Both pages are ZATCA's own `zatca.gov.sa` domain, both returned live content (not cached/archived) on
direct fetch, and both name the wave number, the exact threshold, the measurement years and the
integration deadline in ZATCA's own words — this satisfies "primary regulator source" as scoped by the
task.

### Note on a third attempted primary source (not usable)

`https://zatca.gov.sa/en/Pages/news_1426.aspx` (underscore variant of Source 1's URL, the exact URL
`WebSearch` first returned) resolved to a ZATCA 404 page on fetch. The working URL uses a hyphen
(`news-1426.aspx`), found via a follow-up search. Recorded so the dead link isn't mistaken for a
missing source by the next reader.

### Assessment against RDY-0078's acceptance criteria

**Confirmed, and refined with specific figures.** The GTM's EXT-01 (Medium-High confidence, sourced
from tax-advisory/vendor commentary) is directly corroborated by ZATCA's own wave announcements: e-
invoicing Integration Phase obligation is real, is being rolled out wave-by-wave, and — critically for
"small private clinics" — **the threshold is falling toward, and then below, the SAR 375,000 mandatory
VAT-registration threshold.** Wave 24's SAR 375,000 figure equals Saudi Arabia's mandatory VAT
registration threshold (i.e. by Wave 24, essentially every mandatorily VAT-registered business is in
scope). Wave 25's SAR 187,500 figure is below that, at the level of Saudi Arabia's voluntary VAT
registration threshold — meaning by the Wave 25 deadline (1 February 2027), even small clinics that
registered for VAT voluntarily rather than because they were required to fall into scope.

**What the primary sources do *not* resolve, stated plainly:** whether any *specific* small private
clinic in the ICP is VAT-registered at all, and if so what its VAT-subject revenue was in the named
years, is a fact about the clinic, not something ZATCA's wave-criteria announcements can answer in the
abstract. The primary sources establish the rule; applying it to a specific prospect requires that
prospect's own VAT registration/revenue facts, which is outside the scope of this regulatory
verification and remains a per-prospect due-diligence question (relevant to RDY-0076's clinic-
accountant conversations).

**Conclusion for EXT-01: CONFIRMED against primary ZATCA sources**, with the added precision that the
obligation-onset threshold is already at or below the level most small VAT-registered clinics would
plausibly cross, and is scheduled to reach voluntary-registration-level revenue by February 2027.

---

## EXT-02 — CHI / NPHIES provider obligation

### What was attempted against the actual regulator (CHI) — and could not be completed

The Council of Health Insurance (CHI), `chi.gov.sa`, is the body that issues the regulatory instrument
secondary/vendor sources cite as establishing the NPHIES mandate (e.g. a "General Circular Number 6 of
2021 (GC04)" naming itself an "Enabling Provisions Notice"). **Every attempt to fetch a `chi.gov.sa`
page or PDF in this session failed at the network layer — `connect ECONNREFUSED`** — across seven
distinct URLs and two request schemes, tried over several minutes so a transient outage would not be
mistaken for a hard block:

| # | URL attempted | Result |
|---|---|---|
| 1 | `https://www.chi.gov.sa/en/MediaCenter/News/pages/news-11-3-2021.aspx` | `ECONNREFUSED 185.169.35.38:443` |
| 2 | `https://www.chi.gov.sa/` | `ECONNREFUSED 185.169.35.38:443` |
| 3 | `https://chi.gov.sa/en/MediaCenter/News/pages/news-11-3-2021.aspx` | `ECONNREFUSED 185.169.35.38:443` |
| 4 | `https://www.chi.gov.sa/en` | `ECONNREFUSED 185.169.35.38:443` |
| 5 | `https://www.chi.gov.sa/en/Uniplat/Documents/GC%2004-EN.pdf` (General Circular 6/2021, "Enabling Provisions Notice") | `ECONNREFUSED 185.169.35.38:443` |
| 6 | `https://www.chi.gov.sa/en/Uniplat/Documents/GC04-Updated.pdf` | `ECONNREFUSED 185.169.35.38:443` |
| 7 | `http://www.chi.gov.sa/en/Uniplat/Documents/GC%2004-EN.pdf` (plain HTTP) | `ECONNREFUSED 185.169.35.38:443` |
| — | `https://web.archive.org/web/2026/https://www.chi.gov.sa/...` (archived copy, attempted as a fallback) | Tool-level error: "Claude Code is unable to fetch from web.archive.org" |
| — | Direct `curl` from the sandbox's Bash tool, as a sanity check that this is a fetch-layer and not a content problem | `000` / connection failure — confirms this sandbox has no general outbound network path outside the WebFetch tool itself, so the ECONNREFUSED above is the WebFetch proxy's own result, not something a different tool in this session could route around |

Every attempt resolved to the same IP (`185.169.35.38`) and was refused at the TCP layer, not a 403/
404 application response — consistent with either an IP/network-level block against this fetch tool's
egress, or the host being unreachable from it for some other reason. **This is a disclosed tool/
environment limitation, not evidence about CHI's regulation itself**, and it is not converted into any
finding about NPHIES obligation.

### One primary source that *was* reachable and read directly

- **URL:** `https://portal.nphies.sa/ig/introduction.html`
- **Document title:** "Introduction — Healthcare Financial Services IG Edition 1 v1.0.0"
- **Issuing body:** HL7 Saudi Arabia, in collaboration with nphies (National Platform for Health
  Information Exchange Services) — hosted on nphies' own `nphies.sa` domain, which the task brief names
  as an acceptable primary-source domain alongside CHI
- **Publication/build date (as shown on the page):** generated 2025-12-03
- **Date accessed:** 2026-08-16
- **Passage relied on (paraphrased, not quoted at length):** the guide states that private insurers
  (and/or their TPAs) and most of roughly 6,600 "regulated provider organizations" currently exchange
  their eClaims transactions via nphies, and that any of the remaining Saudi healthcare provider
  organizations that have not yet joined "may also connect." The guide's stated purpose is to define
  the technical standard used for that exchange, not to assert a compliance mandate.

**This is a real primary source, but it materially narrows what can honestly be claimed.** It
documents current majority participation among a specific subset ("regulated provider organizations,"
i.e. those licensed/contracted for insurance billing), and frames onboarding for the rest as available
("may also connect") rather than stating, in its own words, that every healthcare provider is legally
compelled to join. That is a meaningfully softer framing than the "NPHIES is mandatory for every
healthcare provider in Saudi Arabia" language found in the vendor/integrator commentary this
verification is explicitly excluding as evidence (e.g. Juleb, Softloomer, Bonami, Cirrus — all
excluded here per the task's secondary-source rule). The document that *would* settle whether a binding
legal mandate exists — CHI's own regulatory circular — is exactly the one that could not be reached
(table above).

### Assessment against RDY-0078's acceptance criteria

**EXT-02 is not fully verifiable against primary sources in this session, and is recorded honestly as
such rather than resolved by the one adjacent document that could be reached.** One official
nphies-hosted publication was read and is cited above; it does not itself establish the universal
provider mandate that secondary sources assert, and in fact reads as more conditional than that
characterization. The actual regulator's instrument (CHI's General Circular / "Enabling Provisions
Notice" and any successor circulars) — which is what would confirm, refine or contradict the mandate
claim — was **not obtainable** due to the disclosed `chi.gov.sa` connectivity failure documented above.

**Per RDY-0078's own acceptance text** ("Where primary verification cannot be obtained, the item is
marked `PRIMARY-SOURCE VERIFICATION OUTSTANDING` and no downstream artefact asserts the fact"):

> **EXT-02: PRIMARY-SOURCE VERIFICATION OUTSTANDING** — for the specific claim that NPHIES
> participation is a binding regulatory *obligation* on all (or on some defined class of) healthcare
> providers. The one primary NPHIES-hosted document reached in this session neither confirms nor
> contradicts that specific claim cleanly; it describes majority current participation among insurance-
> billing providers and optional-sounding onboarding for the rest, which is not the same thing as a
> cited legal mandate. No downstream artefact should assert "NPHIES is mandatory for this clinic" on
> the strength of this record.

### Reference noted, not used as evidence

Locked Decisions Q21 was named in the task brief as excluded evidence. It was not opened in this
session and is not cited here, consistent with the brief's instruction to record it only as a
cross-check reference, not as a source.

---

## Summary

| Item | Primary source(s) reached | Finding |
|---|---|---|
| **EXT-01** (ZATCA) | 2/2 targeted `zatca.gov.sa` pages fetched successfully | **CONFIRMED and refined** — real, wave-based, threshold now at/below levels plausible for small VAT-registered clinics; Wave 25 (deadline 1 Feb 2027) reaches the voluntary-VAT-registration threshold (SAR 187,500) |
| **EXT-02** (NPHIES/CHI) | 1 `nphies.sa`-hosted IG reached; CHI (`chi.gov.sa`, the actual regulator) unreachable after 7 attempts, disclosed | **PRIMARY-SOURCE VERIFICATION OUTSTANDING** for the mandate claim itself. The one reachable primary document does not assert a universal obligation and reads more conditionally than secondary sources claim |

## Closure discipline note

This record supplies evidence only. Per the task's binding closure discipline, whether RDY-0078
actually closes is not this agent's call — the register row stays with its Owner
(Founder / Product Owner per the card) to mark. **Recommendation, not a self-closure:** EXT-01 can be
treated as resolved on primary sources; EXT-02 cannot yet, and RDY-0078 should stay **NOT READY** on
the EXT-02 leg specifically until either (a) `chi.gov.sa` access is retried successfully by a session
with a different network path, or (b) a CHI circular is obtained through another channel (e.g. a PDF a
human downloads and hands to an agent, or the two clinic-finance-manager conversations the card's
"Required action" already contemplates as a supplement). No claim of OpenEMR/Thiqa regulatory
compliance is made or implied anywhere in this record.
