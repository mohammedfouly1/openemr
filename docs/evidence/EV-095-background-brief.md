**BACKGROUND RESEARCH ONLY. This is not a legal determination and must not be recorded as one. RDY-0095
closes only on a determination from a qualified reviewer.**

# EV-095 — LICENCE / ATTRIBUTION BACKGROUND RESEARCH BRIEF

**Requirement:** RDY-0095 · **Gates:** G1 G4 · **Deps:** RDY-0090 · **Owner:** Legal / Compliance
**Continuation of:** `EV-095-licence-attribution-pack.md` (which framed the eight questions in its §4
and commissioned the determination to SkyEagle at PB-077 — outstanding)
**Date:** 2026-08-16 · **AGENT-BRANDING** (Agent D / Wave execution)

---

## 0. What this document is, and the two hard limits on it

**It is background research, assembled by an engineer using web search and the GPL primary text
already cited in `EV-095`, not a legal opinion.** Every general statement below is sourced; where a
source could not be found specific to this project (OpenEMR's own trademark policy, in particular —
searched, not found publicly published), that gap is stated rather than filled with a guess.

**It does not answer `EV-095`'s eight questions.** It gives a qualified reviewer more to work with on
each one — what the licence text says, what the general trademark position is for a project of this
shape, and what comparable open-source-derived commercial products have actually done. **The §6
determination block in `EV-095` is still blank and stays blank until a named, qualified reviewer with
stated authority completes it.**

### 0.0 The self-review problem — read before using anything below

`EV-095` §0.0 already flags this, in its own words: *"the party determining the licence obligations is
the party that benefits from the answer"* — SkyEagle is both the commissioned reviewer and the
branding target already live across the product (`main_menu_logo_link`, `online_support_link`,
`user_manual_link` all point to `skyeagle.uk`). `EV-095` names **two questions as the ones most likely
to be answered conveniently**, and this brief flags them again here, at the top, because they are the
two where "background research" is least able to substitute for independent judgment:

- **Q1** (acknowledgements-page suppression) — *"The convenient answer is 'yes, §5(d) covers it.' The
  safe answer is to restore it."*
- **Q3** ("OpenEMR" as a trademark) — *"The convenient answer is 'it's our choice.' GPL grants no
  trademark licence, so it may be an obligation."*

**This brief's research findings on Q1 and Q3 (§2.1, §2.3 below) should be weighed knowing that a
self-interested determination has an incentive to read them narrowly.** The recommendation already on
file in `EV-095` §0.0 — an independent counter-read by someone with authority to say no to the
business — is not repeated as new advice here; it is simply not contradicted by anything found in this
research.

---

## 1. The licence text itself — what it actually says (grounded in `EV-095` §1, re-confirmed)

`EV-095` §1 already extracted the three governing clauses from `LICENSE` (this repository's own text,
GPL-3.0-or-later per `composer.json:4`). Restated here because every question below turns on them:

| Clause | What it says | Where |
|---|---|---|
| Definition of "Appropriate Legal Notices" | An interactive UI displays them if it has "a convenient and prominently visible feature" showing copyright notice + no-warranty statement + how to view the licence. **"If the interface presents a list of user commands or options, such as a menu, a prominent item in the list meets this criterion."** | `LICENSE:103-110` |
| §4, Conveying Verbatim Copies | Must "conspicuously and appropriately publish... an appropriate copyright notice," **keep intact all licence/warranty notices**, and give recipients a copy of the licence | `LICENSE:196-203` |
| §5(d), Conveying Modified Source Versions | Interactive interfaces must display Appropriate Legal Notices — **"however, if the Program has interactive interfaces that do not display Appropriate Legal Notices, your work need not make them do so."** | `LICENSE:230-233` |
| "Conveying" trigger | *"Mere interaction with a user through a computer network, with no transfer of a copy, is not conveying."* | `LICENSE:100-101` |

**General GPL principle, confirmed by independent web research this session** (not specific to this
repository's text, but consistent with it): GPLv3 §7 explicitly permits a licensor to decline to grant
trademark rights for use of trade names/trademarks/service marks, and this is treated as compatible
with GPLv3 rather than an additional restriction on the code itself — i.e. **the licence's copyright
grant and any separate trademark position are understood by the licence's own drafters as two different
things**, one automatic (copyleft on the code), one not (no trademark licence is granted merely by
GPL'ing the code). A German appellate ruling (Düsseldorf) is cited in commentary as having held the
same distinction: *"The GPL does not contain consent for trademark use – neither expressly nor
implied. Rather, the license merely regulates copyright."*
[Chapter 9, GPL Version 3 — copyleft.org](https://copyleft.org/guide/comprehensive-gplguidech10.html),
[German Case Distinguishes Trademark and Copyright Licensing in GPL — Copyleft Currents](https://heathermeeker.com/2010/10/17/german-case-distinguishes-trademark-and-copyright-licensing-in-gpl/)

**What this means for reading `EV-095`'s eight questions:** Q1/Q2/Q4/Q5/Q6/Q7 are essentially **copyright
and licence-notice questions** — the clauses above bear on them fairly directly. **Q3 (trademark) and
Q8 (third-party component attribution) are a different body of law** — trademark and (for bundled
third-party code) whatever licence that specific component carries, not GPL-3 as such. A reviewer who
treats all eight as "the GPL question" risks under-weighing Q3 specifically, which is exactly the
question `EV-095` already flags as most likely to be answered too conveniently.

---

## 2. Per-question background — grounded in the licence text, general trademark position, and comparables

### 2.1 Q1 — Does suppressing the acknowledgements link **and** blocking the page satisfy §4/§5(d)?

**What §5(d) actually permits, read narrowly:** the clause says a modifier's work "need not" make an
interface display Appropriate Legal Notices **if the Program's own interfaces don't already do so**.
`EV-095` §3 already identifies the fact pattern problem with reading §5(d) as covering this case: the
upstream `acknowledge_license_cert.html` *did* provide the notices, gated behind a global that
defaulted to displaying them (`display_acknowledgements`, `display_acknowledgements_on_login` both
defaulted `1` upstream) — meaning **the Program, as received, does display Appropriate Legal Notices**.
Turning that off is arguably not "the Program not displaying them" but "this deployment choosing to
stop displaying something the Program does provide." Whether that distinction is legally load-bearing
is precisely Q1, and this brief does not resolve it — it only notes that the plain reading of §5(d)'s
own conditional ("if the Program... does not display... your work need not") sits less comfortably
with a fact pattern where the Program *does* display them by default and a downstream deployment turned
that off, than it would with a fact pattern where upstream never had such a feature at all.

**Additional structural point not previously raised:** the **Apache-level block** (`Require all
denied` on `acknowledge_license_cert.html`) is a stronger act than the `globals` suppression alone —
the globals change removes a *link*; the Apache block removes *reachability entirely*, including by a
user who already knows the URL or a licence-compliance auditor checking it directly. §4's requirement
to "conspicuously and appropriately publish" a notice is harder to satisfy for content that returns
HTTP 403 to every requester than for content that is merely unlinked but still servable. This
sharpens, rather than resolves, the question already on file.

### 2.2 Q2 — Must an Appropriate Legal Notices feature be reachable from the main menu?

The licence text's own definition (`LICENSE:103-110`) explicitly says a **menu item** is sufficient —
*"a prominent item in the list meets this criterion."* This is background, not new: `EV-095` already
quotes it. Worth adding for the reviewer: this phrasing sets a **low bar for what satisfies the
requirement** (one menu entry, not a full page reachable from everywhere) — so if Q1 is resolved toward
restoring some form of notice, Q2's likely remedy is narrow (one menu item) rather than a larger UI
project. That is a useful scoping fact for whoever estimates the cost of a "restore" verdict on Q1.

### 2.3 Q3 — Is "OpenEMR" a trademark, and does rebranding *require* removing it?

**GPL and trademark are legally separate bodies of law — confirmed by general research, not specific
to OpenEMR.** The GPL is a copyright licence; it grants rights to copy, modify and distribute the
*code*. It does not, and by its own drafters' understanding (GPLv3 §7's explicit trademark carve-out)
cannot, grant rights to use a project's *name or logo* as a trademark. Practically: **being free to
fork and modify GPL'd code has never implied being free to call the fork by the original project's
name.**
[Protecting Your Brand in Open Source: Trademarks, Forks, and Enforcement Strategies — TermsFeed](https://www.termsfeed.com/blog/open-source-trademark/)

**Comparables — projects that forked/rebranded and how they handled the name:**
- **MariaDB** forked MySQL and shipped under a **distinct name**, not "MySQL Community Edition
  (unofficial)" — a commonly cited example of a fork choosing a new trademark rather than relying on
  the original.
- **LibreOffice** forked OpenOffice.org under a **distinct name** for the same reason.
- **WordPress**: the WordPress Foundation owns the WORDPRESS/WOOCOMMERCE trademarks separately from
  the GPL licence on the code, and actively distinguishes "you may use and modify the code freely"
  from "you may not necessarily call your commercial offering 'WordPress' without a trademark
  licence." The 2024 Automattic/WP Engine dispute is a live, heavily litigated example of exactly this
  boundary being contested at scale — a GPL grant on the code did not settle the trademark question.
  [WordPress Trademarks: A Legal Perspective — Automattic](https://automattic.com/2024/10/02/wordpress-trademarks-a-legal-perspective/),
  [Trademark Policy — WordPress Foundation](https://wordpressfoundation.org/trademark-policy/)

**This project's own current posture is already consistent with the cautious reading**, for what that
is worth as a data point (not a determination): `openemr_name` has already been changed to `Thiqa`
(S-5 in `EV-095`), i.e. the product-facing name is already distinct, mirroring what MariaDB/LibreOffice
did. **What is NOT yet consistent** is `EV-095`'s own S-10 and S-12 findings — the product-registration
modal still says "OpenEMR Foundation" verbatim, and other operator-facing surfaces may too. If Q3
resolves toward "removal is an obligation, not a choice," those are the concrete remaining surfaces,
not the ones already fixed.

**A search for an OpenEMR-specific published trademark policy did not surface one** in this session (no
public "OpenEMR Trademark Policy" page was found, unlike WordPress's, Linux Foundation's, or Eclipse's
published policies). That absence is itself worth flagging to the reviewer: **the absence of a public
policy does not mean the mark is unprotected** — US and most jurisdictions recognise trademark rights
through use, registration is not strictly required to have *some* rights, and the OpenEMR Foundation
may hold registered or common-law rights not documented anywhere this research could reach. A
qualified reviewer should check the USPTO (and relevant KSA/GCC) trademark registers directly rather
than infer non-existence from an absent public policy page.

### 2.4 Q4 — Product-registration modal soliciting consent to share data with "OpenEMR Foundation"

`EV-095` already correctly frames this as **both** a licensing-adjacent branding question **and** a
distinct privacy question. Background research adds the privacy angle specifically for this project's
jurisdiction:

**Saudi Arabia's Personal Data Protection Law (PDPL)** is the operative framework, not GDPR (no EU
nexus stated for this deployment). General background, not a determination: the PDPL requires consent
to be "freely given" and "an informed decision," and — a point sharper than GDPR's default posture —
**the PDPL's starting position on cross-border transfer is restrictive**, described in commentary as
*prohibiting* transfers of personal data outside Saudi Arabia absent a qualifying basis, with
particular regulatory attention on assessing overseas business partners.
[Saudi Arabia's PDPL: Overview and Compliance Requirements — Enzuzo](https://www.enzuzo.com/blog/saudi-arabia-personal-data-protection-law-pdpl),
[Comparing privacy laws: GDPR v. PDPL — DataGuidance](https://www.dataguidance.com/sites/default/files/gdpr_v_pdpl_v2.pdf)

**What this means for Q4, as background only:** a modal that solicits a Saudi clinic's staff member's
consent to send "anonymous usage data" to a US-based foundation is, independent of whatever the GPL
licence itself says, a cross-border-transfer consent flow under a law with a notably restrictive
default posture on exactly that. This does not resolve Q4's three options (disable / rewrite / leave)
— it means the privacy half of the question likely needs to be answered before or alongside the
branding half, since "is it accurate to still say 'OpenEMR Foundation'" and "should this data flow
exist at all, under PDPL, for this customer base" are two different reviewers' questions that happen to
sit on the same screen.

### 2.5 Q5 — `rwt_2026_report.php`'s ONC-certification email instruction

No new licensing research applies here beyond what `EV-095` already states — this is squarely a
prohibited-claim question (§32 of the requirements document, already cited in `EV-095`) rather than a
GPL question. Background note only: **ONC certification is a US regulatory programme with no Saudi
equivalent**; a live screen inviting a user to participate in it is not a "convenient answer serves us"
risk the way Q1/Q3 are — leaving it live serves nobody and costs nothing to disable. This is the
lowest-ambiguity of the eight questions.

### 2.6 Q6 — Does the hosted model convey the Program at all?

**General GPL background, not specific to this repository's facts:** the FSF's own GPL FAQ and
commentary consistently describe "conveying" as requiring a *transfer of a copy* to another party;
providing access to software running on a server the licensor operates (SaaS-style) is the fact
pattern GPLv3 §13 (the "Remote Network Interaction" clause, distinct from AGPL) does **not** extend
copyleft obligations to, unlike the **AGPL**, which was drafted specifically to close that gap. This
project is licensed **GPL-3.0-or-later** (`EV-095` §1, `composer.json:4`), **not** AGPL — a materially
relevant fact for Q6 that this brief flags because it changes the shape of the likely answer (a
hosted-only offering under plain GPL-3 has a more plausible "not conveying yet" argument than the same
offering would under AGPL), without asserting what the answer actually is.

**What is unambiguous, and already correctly noted in `EV-095` §1.2:** the moment a customer takes the
on-premise option the GTM itself already offers ("a supported option priced separately"), a copy has
been transferred to that customer, and the "mere interaction... is not conveying" carve-out
(`LICENSE:100-101`) stops applying to that specific transaction on its plain text. **Q6 is likely to
have two different answers for two different products this business already sells (hosted vs.
on-premise), not one answer for the business as a whole.**

### 2.7 Q7 — Does the demo instance need different treatment from a customer instance?

No general research applies distinctly here; it is a consequence of however Q1/Q6 resolve, applied
twice. Flagged only to note that **a demo instance shown to unlimited prospects over time may have a
stronger "conveying" argument than a single customer's hosted instance**, if demo visitors are ever
given direct access rather than a presenter driving it live — worth confirming which mode (presenter-
driven vs. self-service sandbox) actually describes this project's demo delivery, since GTM §16.1 lists
"self-service seeded demo / sandbox" as **Deferred**, meaning the live-guided-demo mode (presenter
drives, prospect doesn't get a copy) is the only one active today — which is the reading least likely
to trigger "conveying" under Q6's own logic.

### 2.8 Q8 — Third-party component attribution

Not researched in depth this session — `EV-095` itself scopes this as needing "a full dependency-licence
inventory" if the answer is "it depends on which," and recommends raising it as its own RDY rather than
assuming. This brief adds one comparable data point: **LForms is a US National Library of Medicine
(NLM) product** — federal-government-authored software in the US is typically **public domain** under
17 U.S.C. §105 (no US copyright in works of the federal government), which, if it applies to LForms's
NLM-authored portions, would reduce (not necessarily eliminate — bundled third-party libraries within
LForms itself may carry their own licences) the attribution burden for that specific asset relative to
a privately-copyrighted third-party component. **This is offered as a research lead for whoever performs
Q8's inventory, not as a conclusion about LForms's actual licensing**, which was not independently
verified this session.

---

## 3. What "comparable open-source-derived commercial products" generally do — a pattern, not a rule

Drawing on the comparables surfaced above (MariaDB, LibreOffice, WordPress-ecosystem hosts), the
recurring pattern in businesses that host/sell a modified or repackaged version of GPL'd (or similarly
copyleft) software is:

1. **Rename the customer-facing product** — already done here (`openemr_name` → `Thiqa`).
2. **Disclose the open-source origin somewhere, rather than conceal it** — this project's own locked
   positioning already requires this independently of licensing (POS-002, R-04, cited throughout the
   requirements document and in `EV-095` §5) — a rare case where the commercial/marketing decision and
   the cautious-licensing decision point the same direction.
3. **Keep the underlying licence text and per-file notices intact in the code**, even while changing
   user-facing branding — this project has done this (`EV-095` §5: `LICENSE` unmodified, 2,396 headers
   intact).
4. **Not remove upstream trademark references from areas where the product still functions as, or
   interoperates with, the named upstream project**, absent a trademark clearance — this is the one
   area (Q3-adjacent) where this project's S-10/S-12 gaps (`EV-095`) remain open relative to the
   general pattern.
5. **Treat "must the acknowledgements/about page survive" as the most commonly contested single point**
   — this maps directly onto Q1, and is consistent with why `EV-095` flagged Q1 as the highest-risk
   convenient-answer question rather than an unusual one.

**None of this is a rule this project must follow** — it is a description of what comparably-positioned
businesses have generally done, offered so a reviewer has a frame of reference, not a standard to cite
as authority.

---

## 4. Summary table — for the qualified reviewer

| # | Question | GPL text bears on it? | Trademark/other law bears on it? | Self-review risk flagged by `EV-095` §0.0? | This brief's main addition |
|---|---|---|---|---|---|
| Q1 | Acknowledgements suppression | Yes — §4, §5(d), Appropriate Legal Notices definition | No | **Yes — highest flagged risk** | The Apache-level block is a stronger act than the globals change; §5(d)'s "if the Program does not" reads awkwardly against a Program that did, by default |
| Q2 | Menu-reachable notices feature | Yes — definition explicitly names a menu item as sufficient | No | No | Low likely remedy cost if Q1 resolves toward restoration |
| Q3 | "OpenEMR" trademark | No — GPL grants no trademark rights (general principle, multiple sources) | **Yes — this is a trademark question, not a GPL question** | **Yes — highest flagged risk** | Comparables (MariaDB, LibreOffice, WordPress) show forks generally rename *and* stay off the original mark elsewhere; no public OpenEMR trademark policy was found, which is not evidence of no rights |
| Q4 | Registration-modal consent | Peripheral | **Yes — PDPL cross-border transfer, separately from licensing** | Not flagged by `EV-095` as self-review risk, but has its own bias risk (same commissioning party) | PDPL's restrictive cross-border default sharpens the privacy half independently of the branding half |
| Q5 | ONC/RWT report | No | No — prohibited-claims question (§32) | No | Lowest-ambiguity item; no cost to disabling |
| Q6 | Hosted vs. conveyed | Yes — §13/AGPL distinction, "conveying" definition | No | No | Project is GPL-3, not AGPL — materially relevant; likely two different answers for hosted vs. on-premise |
| Q7 | Demo vs. customer instance | Consequence of Q1/Q6 | No | No | Demo is presenter-driven (self-service sandbox is Deferred per GTM §16.1) — the reading least likely to trigger "conveying" |
| Q8 | Third-party component attribution | Depends on each component's own licence | Possibly — varies by component | No | LForms's NLM origin is a research lead (possible US-government public-domain portions), not a conclusion |

---

## 5. Status

**RDY-0095 remains COMMISSIONED, determination OUTSTANDING** — unchanged by this document, which adds
research inputs only. `EV-095` §6's determination block is still entirely blank. **This brief must not
be cited as, or mistaken for, the determination itself.** The recommendation already on file — an
independent counter-read of whatever SkyEagle (or its named reviewer) produces, given the self-review
structure — is reiterated as unresolved, not superseded.

**`Blocks`:** G1 G4 (per `EV-095`). No gate count moved (§0.0 Rule 3).

**Consequential, unchanged:** RDY-0033 and RDY-0034 remain NOT CLOSED for the same reason `EV-095`
already states — their configuration work is done; only the attached determination is missing.

---

## Sources consulted (web research, 2026-08-16)

- [Chapter 9, GPL Version 3 — copyleft.org comprehensive guide](https://copyleft.org/guide/comprehensive-gpl-guidech10.html)
- [German Case Distinguishes Trademark and Copyright Licensing in GPL — Copyleft Currents](https://heathermeeker.com/2010/10/17/german-case-distinguishes-trademark-and-copyright-licensing-in-gpl/)
- [The "general public license" does not grant use of third party trademarks — Lexology](https://www.lexology.com/library/detail.aspx?g=5b0c0785-3eac-4cbc-ad08-2afba447b1e3)
- [Protecting Your Brand in Open Source: Trademarks, Forks, and Enforcement Strategies — TermsFeed](https://www.termsfeed.com/blog/open-source-trademark/)
- [WordPress Trademarks: A Legal Perspective — Automattic](https://automattic.com/2024/10/02/wordpress-trademarks-a-legal-perspective/)
- [Open Source, Trademarks, and WP Engine — Automattic](https://automattic.com/2024/09/25/open-source-trademarks-wp-engine/)
- [Trademark Policy — WordPress Foundation](https://wordpressfoundation.org/trademark-policy/)
- [Saudi Arabia's PDPL: Overview and Compliance Requirements — Enzuzo](https://www.enzuzo.com/blog/saudi-arabia-personal-data-protection-law-pdpl)
- [Comparing privacy laws: GDPR v. PDPL — DataGuidance](https://www.dataguidance.com/sites/default/files/gdpr_v_pdpl_v2.pdf)
- [Saudi Personal Data Protection Law PDPL Compliance Guide — Hala Privacy](https://halaprivacy.com/what-is-pdpl/)

**No source found for:** a published OpenEMR Foundation trademark policy (searched; absence noted in
§2.3, not treated as evidence of anything).
