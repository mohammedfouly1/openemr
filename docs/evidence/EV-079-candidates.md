# EV-079 — RDY-0079 Candidate List (Ophthalmology / Multi-Specialty Outpatient, Saudi Arabia)

## 0. What this file is, and what it is NOT

**Author:** Agent D, session AGENT-PROSPECT (claim registered `docs/evidence/AGENT-CLAIMS.md` line 319,
range Agent D, "0079 (candidate-list half)").

**RDY-0079** (`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` line 1065, row 8791, V-9 at
line 9636, U-9 at line 10565) reads: *"Confirm A-01 (founder network) with a counted list of warm
introductions, and A-07/V-9 with 30 named ophthalmology clinics of which 5 are reached."*

**This file supplies the 30-named-clinic candidate-list half ONLY.**

- It does **not** attempt, and cannot substitute for, the "5 reached" half of the acceptance criterion.
  Reaching a candidate means an actual outbound conversation (call, WhatsApp, email, in-person) with a
  clinic owner/manager and a recorded outcome. **No such conversation happened in the production of this
  file.** Every row below is desk research from public web sources — no clinic was contacted.
- It does **not** close RDY-0079, U-9, or V-9. Per the requirements document, V-9's second question —
  "Can 30 named clinics be listed **and 5 reached**?" — has two parts; this file answers the first part
  only. The **Founder / Product Owner** (RDY-0079's named Owner) or the **Sales / Pilot Owner** must pick
  candidates from this list, actually contact 5, and record the outcome of each conversation before this
  item has any chance of moving off `BLOCKED — VALIDATION`.
- **Closure discipline:** every entry below traces to a real web search result or a real page fetch
  performed in this session. Nothing is invented. Where a detail (phone, doctor count, branch count,
  payer language) could not be found publicly, the cell says so explicitly rather than being filled in.
  Where a fetch failed (403, DNS error, dead redirect, server error) or a candidate looked possibly
  defunct, that is recorded rather than silently dropped or silently included as if verified.

## 1. Method

- Tool: `WebSearch` (multiple queries per city × specialty, English and Arabic) run 2026-08-16, plus
  `WebFetch` against a subset of candidates' own websites or the strongest directory listing found, to
  pull contact details, branch/staff counts, and payer-mix language directly from source.
- Geographic scope: **Riyadh, Jeddah, and the Eastern Province (Dammam / Khobar / Dhahran)** — this
  matches the GTM's locked hosting-region decision (`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`
  line 7465: hosting region is Google Cloud `me-central2`, Dammam) and the V-1 interview scope named in
  the document ("mixed specialty, Riyadh + Jeddah", line 9632), extended to the Eastern Province because
  RDY-0079/V-9 does not restrict itself to Riyadh/Jeddah alone and Dammam/Khobar/Dhahran is the region the
  product is actually being hosted in.
- ICP filter applied: `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` line 10269 (locked ICP,
  §41.1) — *"Saudi private self-pay outpatient clinics and small medical centres, 3–15 providers, 1 site
  (up to 3), predominantly self-pay, invoicing staying elsewhere, ophthalmology beachhead."* I searched
  for independent/small ophthalmology practices and small multi-specialty polyclinics first. Several
  large hospital-scale or national-chain results surfaced anyway (they dominate search results for these
  terms) — I kept a handful of those **explicitly flagged as a poor ICP size/sector fit** rather than
  omit them silently, because the Owner may still want them as stretch/comparison targets, but they
  should **not** be prioritised for the "5 reached" outreach ahead of the better-fitting small
  independents.
- Two searched leads were **excluded outright** rather than padded in: General Medical Polyclinic
  (`gpcsmedical.com`, Jeddah) resolved to an expired-domain parking redirect on fetch — likely defunct,
  not listed below. Khobar Cooperative Polyclinic (`en.saudibusiness.directory` listing) failed DNS
  resolution on fetch and had no corroborating second source — not listed below, noted here so the
  search effort isn't invisible.

## 2. Candidate list (30)

Legend for **ICP fit**: `GOOD` = independent/small operation, plausible fit for locked ICP size (3–15
providers, ≤3 sites). `SIZE MISMATCH` = real, reachable, but publicly-known size signals put it well
outside 3–15 providers / 1–3 sites (large hospital or national chain) — kept for completeness, not
recommended as a priority pick for the 5-reached step. `SECTOR MISMATCH` = public/government or
non-profit, not a private self-pay commercial clinic — kept for completeness only. `UNKNOWN` = no public
size signal found either way.

### Riyadh — Ophthalmology

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 1 | Roaya Center for Ophthalmology (مركز رؤية) | Riyadh (Al Murabba / King Fahad Rd, after MOI, Al‑Nour Tower) | No public doctor/branch count found; site references a location map suggesting >1 site | Phone 920007606, email info@roayaeyecenter.com, roayaeyecenter.com, WhatsApp | Site lists "Insurance Companies" as partners (names not itemised on the fetched page) — insurance-accepting, no explicit self-pay/cash language | GOOD | roayaeyecenter.com (fetched); 4.6★/2,241 reviews per WhatClinic aggregation |
| 2 | Eye World Medical & Surgical Complex (مجمع عالم العيون الطبي الجراحي) | Riyadh (Tahlia St, P.O. Box 100205) | 3-floor single complex (optometry + optical mall + clinics); opened 2007; no doctor count found | No phone found in search results; Facebook page facebook.com/Eyeworldsa; directory listing at edexy.com | None found | GOOD | edexy.com, sa.top10place.com, facebook.com/Eyeworldsa (search only, not fetched) |
| 3 | Alsubhi Medical Center — Ophthalmic Clinic | Riyadh (Al Rawda, East Riyadh) | Single named "specialist ophthalmologist"; no branch count found | Phone 0534142222 / 0112335000, WhatsApp +966534142222, asmc.med.sa | None found on fetched page | GOOD | asmc.med.sa/en/ophthalmic-clinic (fetched) |

### Riyadh — Multi-specialty

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 4 | Suncity Polyclinic | Riyadh (Batha, opposite Markaz Jamal) | 20+ physicians named on site; founded July 2007; sister branch "Al‑Raqi Polyclinic" in Jeddah | Phone +966 53 453 4808 / +966 55 885 1003, email infosuncitypolyclinic@gmail.com | Explicit: "accepts over 25+ health insurance plans"; health packages 35–400 SR also advertised, implying a cash-pay option alongside insurance | GOOD | suncitypolyclinicsa.com (fetched) |
| 5 | Medical World Polyclinic | Riyadh (Malaz — Jarir St; Taawun — Abu Bakr Al‑Siddiq Rd) | Site states **954 doctors** across 2 branches — well above 15-provider ICP ceiling | Malaz 0534731313, Taawun 920002278, email m.shafik@mw.com.sa | 12+ insurance-partner logos displayed; no explicit self-pay language | **SIZE MISMATCH** | medicalworld.com.sa (fetched) |
| 6 | KIMSHEALTH Medical Center, Riyadh | Riyadh (Jarir St, Al Malaz) | 2-centre group (Riyadh + Jubail); doctor count not published | Phone +966 11 4777 471, WhatsApp +966532640033, email info.ruh@kimshealth.sa | "Find Insurance partner" feature referenced; no self-pay language found | UNKNOWN (likely mid-size — dialysis/imaging/pharmacy suggests larger than 3–15 providers, but not stated) | kimshealth.co/saudi-arabia/facilities/kimshealth-medical-center-riyadh (fetched, redirected from kimshealth.sa) |
| 7 | My Clinic (عيادتي) — Riyadh branch | Riyadh (Al Sahafa) | Chain-wide: **6 branches (1 Riyadh + 5 Jeddah), 390+ doctors, 27+ specialties** — chain is far above ICP size; the single Al Sahafa branch's own headcount is not separately published | Phone 920 022 811, WhatsApp, myclinic.com.sa | No explicit self-pay/insurance language found on fetched page | **SIZE MISMATCH** (as a chain; individual-branch fit unknown) | myclinic.com.sa (fetched) |
| 8 | Healthy Clinics (عيادات هيلثي) | Riyadh | No size signal found — only a page title surfaced, full content did not load on fetch | Website healthyclinics-sa.com | None found | UNKNOWN — weak evidence, flagged | healthyclinics-sa.com (fetch returned title only, content did not resolve) |
| 9 | Raha Medical Complex (مجمع رها الطبي) | Riyadh | Multi-department (dental, internal medicine, OB/GYN, pediatrics per earlier search snippet); no doctor/branch count found | Website rahahealth.com.sa/clinic — **site blocked automated fetch (403)**; no phone captured | None found | UNKNOWN — contact route is the website only; a live call/browser visit is needed to get a phone number | rahahealth.com.sa (search snippet only; direct fetch returned HTTP 403) |
| 10 | Ishbiliyah / Ashbelia NMC Medical Polyclinics | Riyadh | Listed as an "NMC" multi-speciality clinic group on Okadoc; no size detail retrievable — fetch returned only a loading shell | Contact route: Okadoc listing page (okadoc.com/en-sa/polyclinic/riyadh/nmc-ishbiliyah-medical-polyclinics) — no independent phone/site found | None found | UNKNOWN — weakest entry in this list; flagged for verification before outreach | okadoc.com listing (search only; fetch did not return usable content) |

### Jeddah — Ophthalmology

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 11 | Batal Specialist Medical Center (Batal Eye Center) | Jeddah (Murjanah Tower, Prince Sultan Rd, Ar Rawdah) + Yanbu branch | 2 branches (Jeddah, Yanbu); 10+ named ophthalmologists incl. founder Dr Ahmad Hassan Batal; established 2010 | Phone 8001111897 (toll-free), email info@bataleyecenter.com, WhatsApp 966555690310 | Explicit financing/self-pay signal: "Installments available up to 100% via Tamara & Tabby & Amwal" — a strong cash-pay-with-financing signal, no insurance language found | GOOD | bataleyecenter.com/en/eye-clinics (fetched) |
| 12 | Saba Medical Clinics — Ophthalmology Dept | Jeddah (6 branches: Al Bawadi, Al Zahra, Al Safa, Al Sulaymaniyah, Al Marwah, Um Al Salm) | 6 branches across Jeddah; doctor count not published | Phone 920008470, sabamedical.com | None found on fetched page | UNKNOWN (6 sites already exceeds the "1 site, up to 3" ICP ceiling if this counts as one operation) | sabamedical.com/en/department/ophthalmology (fetched) |
| 13 | Magrabi Health Eye — North Jeddah | Jeddah (Prince Sultan St, Salama Center, Tower 3) | Part of the national Magrabi network; page states "more than 50,000 patients in Jeddah have chosen Magrabi Health Eye branches" — national-chain scale | Phone 920018000, WhatsApp | None found; JCI/CBAHI accreditation noted instead | **SIZE MISMATCH** (national hospital-group chain, not an independent small clinic) | magrabihealth.com/sa/eye/branches/magrabi-health-eye--north-jeddah (fetched) |
| 14 | Medical Reference Eye Center | Jeddah (King Road, after Hira St intersection, postal code 23412) | Founded 2012; described in search results as "one of the largest specialized clinics" in ophthalmology in Jeddah — self-described as large, no numeric count found | No phone found in search results; location address only | None found | UNKNOWN — "largest specialized clinic" self-description suggests possible size mismatch, but unconfirmed | whatclinic.com Jeddah eye-clinics listing (search only, not fetched — no working direct site URL found) |

### Jeddah — Multi-specialty

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 15 | Shifa Jeddah Polyclinic | Jeddah (Al Wahah District, 23354) | 11 departments; 10 doctor profiles shown on site; "multiple branches referenced" but exact count not given | Phone +966 57 043 4333 / +966 53 955 7333, email contact@shifajeddahksa.com | Explicit insurance partner list: "Bupa, GIG, TCS, Gulf Union, Saico, Malath, Al Etihad, Saudi Nextcare, Al Rajhi Takaful"; site also markets "affordable cost" — mixed insurance + cash-pay-leaning language | GOOD | shifajeddah.com (fetched) |
| 16 | Shifa Medical Complex (مجمع الشفاء الطبي) | Jeddah | Single location; described only as "a select group of doctors and specialists" — no numeric count | Phone 0535609555, WhatsApp | None found | GOOD (single site, described as one of the older/smaller Jeddah complexes) | shifamdc.com (fetched) |
| 17 | Taj Polyclinic (مجمع التاج التخصصي الطبي العام) | Jeddah (South Mall, Ghulayl district, Ahmed Moumen St) | Single location; 20+ doctors listed across specialties incl. ophthalmology | Phone +966 501901954 / +966 536699237, WhatsApp, 24/7 emergency line | Also operates as a "migrant worker health screening center" (occupational medicine revenue line); no explicit self-pay/insurance split stated, site emphasises "affordable cost" | GOOD (single site, ~20 doctors is within/near the 3–15 provider band depending on how "provider" is counted) | tajpolyclinic.com (fetched) |
| 18 | My Clinic (عيادتي) — Jeddah branches | Jeddah (5 branches: Al Mohammadiyah, Al Safa, Al Khalidiyyah [dental], Al Tahlia, Obhour) | Same chain as row 7 — 390+ doctors, 27+ specialties chain-wide | Phone 920 022 811, myclinic.com.sa | No explicit self-pay/insurance language found | **SIZE MISMATCH** (chain) | myclinic.com.sa (fetched) |
| 19 | Al‑Alamiah Medical Clinic & Day Surgery Center (مجمع العالمية الطبي) | Jeddah (Al‑Nahda district) | 17 medical specialties, 36 doctors per search-result summary | Contact route: Vezeeta clinic listing page (saudi.vezeeta.com) — no independent phone captured in this session | None found | GOOD, but not independently fetched — verify contact details before outreach | saudi.vezeeta.com clinic listing (search snippet only, not fetched) |
| 20 | Dr. Muayad Elias Specialized Clinics Complex (مجمع عيادات د. مؤيد الياس المتخصصة) | Jeddah | No size signal found beyond the directory listing title | Contact route: qayimdactory.com directory listing — no phone/site captured | None found | UNKNOWN — weak evidence, flagged for verification before outreach | qayimdactory.com listing (search snippet only, not fetched) |

### Eastern Province (Dammam / Khobar / Dhahran) — Ophthalmology

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 21 | Safa Medical Center — Ophthalmology Dept | Dammam | Part of an 11-department multi-specialty center; ophthalmology sub-specialties named (cornea, glaucoma, retina, oculoplasty, retinoblastoma) but no doctor count found | Phone 013 833 1016 / 834 1016 / 835 1016, fax 834 7796, safadammam.com | Insurance link present in site navigation; no self-pay language found | GOOD | safadammam.com/Ophthalmology (fetched) |
| 22 | Hakeem Oyoun (حكيم عيون) Eye Clinic | Dammam (King Fahd district) | Functions as a single doctor's office/clinic per directory description — smallest-scale ophthalmology entry in this list | Phone +966 55 064 5200 | None found | GOOD | zavis.ai/ar/directory/dammam/clinics/hakeem-oyoun (search only, not fetched) |
| 23 | Al‑Kahhal (الكحال) Eye Clinic | Dammam / Eastern Province | Described as offering eye-disease treatment for children and elderly patients; no doctor/branch count found | Website alkahhal.com.sa — **site blocked automated fetch (403)**; no phone captured | None found | UNKNOWN — contact route is the website only; a live visit is needed for a phone number | alkahhal.com.sa (search snippet only; direct fetch returned HTTP 403) |
| 24 | Magrabi Eye Center — Dammam branch | Dammam | Part of the same national Magrabi network as row 13 | Contact route: Okadoc clinic-group listing (fetch did not return usable content beyond a loading shell) | None found | **SIZE MISMATCH** (national chain) | okadoc.com/en-sa/clinic-group/dammam/magrabi-eye-center-dammam (search + attempted fetch) |
| 25 | Dhahran Eye Specialist Hospital | Dhahran | Part of the **Eastern Health Cluster** (government health system) | ehc.med.sa/portfolio/dhahran-eye-specialist-hospital; address: 7630 H.E Ali Naimi St, Aljamiah District, Dhahran 34257 | Public-sector facility — not a self-pay commercial clinic | **SECTOR MISMATCH** — public/government, kept for completeness only, not a fit for a self-pay pilot pitch | ehc.med.sa (search only) |
| 26 | Al Basar International Foundation | Al Khobar | Non-profit organisation focused on ophthalmology/cataract services | en.wikipedia.org/wiki/Al_Basar_International_Foundation | Charitable, donor-funded — not a self-pay commercial clinic | **SECTOR MISMATCH** — non-profit, kept for completeness only | Wikipedia (search only) |

### Eastern Province — Multi-specialty

| # | Clinic | City | Size signals | Contact route | Payer-mix signal | ICP fit | Source |
|---|---|---|---|---|---|---|---|
| 27 | Shifa Al‑Khobar Medical Center | Al‑Khobar (+ Al‑Rass, Buraidha branches — 3 total) | 3 branches; doctor count not published | Phone +966 13 894 4984, toll-free 920007320, email connect@shifagrp.com, WhatsApp +966 54 625 9942 | Explicit: "accept all major insurance cards"; also runs a paid "SEHIA Care Privilege Card" membership (23 free GP visits + 23% specialist/dental/radiology/lab discounts + 10% pharmacy) — a direct cash-pay membership product alongside insurance | GOOD (Al‑Khobar branch specifically; the 3-branch group is at the edge of the "1 site, up to 3" ICP ceiling) | shifagrp.com/shifa-al-khobar (fetched) |
| 28 | Dar As Sihha Medical Center | Dammam (Al Zuhur) + Khobar (Alkurnaish) — 2 branches | 60+ doctors listed across specialties incl. ophthalmology; operating since 1996, MOH-approved | Phone +966 13 8301953 (both branches), email info@darassihha.net | Named insurance partners: Salama, Saico, Al Rajhi Takaful, Mednet; no explicit self-pay language | GOOD on site count (2, within "up to 3"), but 60+ doctors is above the 15-provider ICP ceiling if that count is group-wide | darassihha.net (fetched) |
| 29 | Al Ryan Polyclinic Dammam (Ryan Clinic Dammam) | Dammam (King Saud St, Al Khaleej) | Single location; 3 named doctors (general medicine, orthopedics, ENT) found on site — smallest-scale multi-specialty entry in this list, plausibly inside the 3–15 provider band | Phone +966138330233, WhatsApp 053 950 5151, ryanclinicdammam.com | No explicit self-pay/insurance language found; site targets Bengali-speaking patients specifically (occupational/expat health niche) | GOOD | findhealthclinics.org listing + ryanclinicdammam.com (fetched via directory) |
| 30 | Tadawi Hospital | Dammam | Self-describes as a "general hospital" serving Dammam, Khobar, Dhahran, Qatif since 1994; ophthalmology is one department among many — hospital-scale, not a small clinic | tadawi.com.sa/en/services/ophthalmology | None found | **SIZE MISMATCH** (hospital, not a small outpatient clinic) | tadawi.com.sa (search only) |

## 3. Summary counts

- **Total candidates listed: 30** (30 named, distinct entities — no duplicates, no fabrication).
- **By city:** Riyadh 10, Jeddah 10, Eastern Province 10.
- **By specialty focus:** Ophthalmology-focused 13 (Riyadh 3, Jeddah 4, Eastern 6), multi-specialty
  (ophthalmology as one department among several) 17 (Riyadh 7, Jeddah 6, Eastern 4).
- **By ICP fit:**
  - **GOOD** (plausible fit for the locked 3–15 provider / ≤3 site self-pay ICP): **14** — rows 1, 2, 3,
    4, 11, 15, 16, 17, 19, 21, 22, 27, 28, 29.
  - **UNKNOWN** (real, contactable, but no public size signal found either way, or a fetch failed to
    return usable data): **9** — rows 6, 8, 9, 10, 12, 14, 20, 23.
  - **SIZE MISMATCH** (real and reachable, but publicly-known size — hundreds of doctors, national-chain
    branding, or hospital-scale — puts it well outside the locked ICP): **5** — rows 5, 7/18 (same chain,
    counted once as an org), 13/24 (same chain, counted once), 30.
  - **SECTOR MISMATCH** (public/government or non-profit, not a private self-pay commercial clinic):
    **2** — rows 25, 26.
- **Contact-detail completeness:** 22 of 30 rows carry a working phone number and/or email captured
  directly from a fetched source page. 8 rows (8, 9, 10, 14, 19, 20, 23, and the Medical Reference Eye
  Center row 14) carry only a directory/search-snippet-level contact route and should be re-verified
  (a fresh site visit or call) before being used for outreach.
- **Excluded, not padded in:** General Medical Polyclinic Jeddah (`gpcsmedical.com` — expired-domain
  redirect, likely defunct) and Khobar Cooperative Polyclinic (DNS resolution failure on its only found
  listing, no second source). See §1.

## 4. What the Owner needs to do next (the "5 reached" half — NOT done here)

RDY-0079 / U-9 / V-9 do not close on this file. The remaining work, which this agent is explicitly **not**
authorised or positioned to do (per the AGENT-CLAIMS.md claim: "Research output only, not the '5 reached'
acceptance half"):

1. **Pick 5+ candidates to contact**, prioritising the 14 `GOOD`-fit rows above over the `SIZE MISMATCH` /
   `SECTOR MISMATCH` / weak-evidence rows — those are stretch/comparison entries, not priority targets.
2. **Actually reach them** — a real call, WhatsApp message, or in-person visit to a clinic owner or
   manager, per V-9's own definition ("Target-list build + **5 conversations**").
3. **Record the outcome of each conversation** — who was spoken to, when, by what route, and what they
   said — in a form V-9/U-9 and the RDY-0079 acceptance criteria can point to. The qualification checklist
   from RDY-0065 (ICP-001/ICP-002, payer mix asked first) should structure each conversation once it
   exists; if RDY-0065 is still not closed when outreach starts, payer mix should still be asked first
   as an informal discipline, since it's the ICP's defining attribute per the GTM.
4. **Verify the weak-evidence rows before calling them** — 8 rows above have only a directory-listing
   or search-snippet contact route; a phone number should be confirmed live before it's dialled.
5. Whoever performs steps 1–4 should update the RDY-0079 row status in
   `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` (line 1065 / row 8791) themselves, per the
   file's own closure discipline (§0.0 Rule 5) — this file does not do that either.
