# EV-095 — LICENCE / ATTRIBUTION DETERMINATION PACK

**Requirement:** RDY-0095 · **Gates:** G1, G4 · **Owner:** Legal / Compliance
**Status of the requirement:** **BLOCKED — DECISION.** This pack does **not** determine anything.
**Prepared:** 2026-08-14 · **Agent B**, Phase 2B

---

## 0. What this document is — and explicitly is not

**It is not a legal determination, and nothing in it may be relied on as one.** It was prepared by
an engineer, not a lawyer.

**What it is:** the complete evidence a qualified reviewer needs, assembled so that RDY-0095 becomes
a bounded questionnaire rather than an open-ended "review the licensing". Every question below is
closed-form, carries the primary text it turns on, names the surfaces it affects, and lists the
options with their consequences.

**Why it matters more than its size suggests.** RDY-0095 is the **only** blocker on gate **G4**, and
**RDY-0033 and RDY-0034 both name it as a dependency** — their acceptance criteria each end with
*"the licence determination is attached."* Neither can close until this does. It also has **no
technical predecessor**: it could have started on day one.

---

## 1. The licence, established from primary text in this repository

| Fact | Evidence |
|---|---|
| Licence | **GPL-3.0-or-later** — `composer.json:4` (`"license": "GPL-3.0-or-later"`) |
| Full text present | `LICENSE`, 674 lines, *GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007* |
| Per-file notices | **2,396 PHP files** under `src/`, `library/` and `interface/` carry a GNU GPL header |
| Upstream acknowledgements page | `acknowledge_license_cert.html` — **tracked**, 24,739 bytes, present at repository root |
| Login-page acknowledgements partial | `templates/login/partials/html/acknowledgements.html.twig` — tracked |

### 1.1 The three clauses everything below turns on

**Definition of "Appropriate Legal Notices" (`LICENSE:103-110`):**

> An interactive user interface displays "Appropriate Legal Notices" to the extent that it includes
> a convenient and prominently visible feature that (1) displays an appropriate copyright notice,
> and (2) tells the user that there is no warranty for the work…, that licensees may convey the work
> under this License, and how to view a copy of this License. **If the interface presents a list of
> user commands or options, such as a menu, a prominent item in the list meets this criterion.**

**§4, Conveying Verbatim Copies (`LICENSE:196-203`):**

> …provided that you conspicuously and appropriately publish on each copy an appropriate copyright
> notice; **keep intact all notices** stating that this License and any non-permissive terms added
> in accord with section 7 apply to the code; **keep intact all notices of the absence of any
> warranty**; and give all recipients a copy of this License along with the Program.

**§5(d), Conveying Modified Source Versions (`LICENSE:230-233`):**

> If the work has interactive user interfaces, each must display Appropriate Legal Notices;
> **however, if the Program has interactive interfaces that do not display Appropriate Legal
> Notices, your work need not make them do so.**

### 1.2 One structural observation, offered as context and not as advice

**"Conveying" is the trigger.** `LICENSE:100-101`: *"Mere interaction with a user through a computer
network, with no transfer of a copy, is not conveying."* The locked commercial model is **hosted**
(GTM-006: implementation, hosting, patching and support are the product; CLM-0029/L-07 per-clinic
deployment). Whether a hosted-only model conveys at all — and what changes the moment a customer
takes an on-premise copy, which the GTM offers as *"a supported option priced separately"* — is
**Q6 below** and is squarely a legal question.

---

## 2. Current state of every attribution-bearing surface

Enumerated live. `Changed` describes what this project has already done.

| # | Surface | Carries | Current state | Changed by us? |
|---|---|---|---|---|
| S-1 | `LICENSE` (root) | Full GPL-3 text | **Present, unmodified** | No |
| S-2 | 2,396 per-file GPL headers | Licence + `@copyright` lines | **Present, unmodified** | No |
| S-3 | `acknowledge_license_cert.html` | GPL text + distribution copyright notices | **Present and tracked, but unreachable**: `display_acknowledgements` and `display_acknowledgements_on_login` set to `0`, **and** an Apache `<Files>` block returns **HTTP 403** to direct URLs | **YES — see §3** |
| S-4 | `templates/login/partials/html/acknowledgements.html.twig` | Login-page acknowledgements link | **Not rendered** — gated by `display_acknowledgements_on_login = 0` | **YES** |
| S-5 | `openemr_name` global | Product name shown throughout the UI | `'OpenEMR'` → **`'Thiqa'`** | **YES** |
| S-6 | `login_tagline_text` | Login-page tagline | Upstream tagline → **`'Clinical confidence, connected care.'`** | **YES** |
| S-7 | `main_menu_logo_link` | Where the logo navigates | `open-emr.org` → **`skyeagle.uk`** | **YES** |
| S-8 | `display_donations_link`, `display_review_link` | Upstream donation / review links | `1` → **`0`** (not rendered) | **YES** |
| S-9 | `online_support_link`, `user_manual_link` | Support / docs destinations | Upstream → **`skyeagle.uk/support`, `skyeagle.uk/docs`** | **YES** |
| S-10 | `templates/product_registration/product_registration_modal.html.twig:22,35` | *"…critical announcements from the **OpenEMR Foundation**"* and *"I consent to share anonymous usage data with the **OpenEMR Foundation**"* | **Text still names the OpenEMR Foundation** | No — **open, see Q4** |
| S-11 | `interface/reports/rwt_2026_report.php:61,74` | Instructs the user to email the report to **`hello@open-emr.org`** for **ONC certification** | **Unmodified** | No — **open, see Q5** |
| S-12 | Zend installer `index.phtml`, `admin.php`, error templates, etc. | Product name in operator screens | Rebranded under patch records PR-01…PR-13 | **YES** |

---

## 3. ⚠ The single item most likely to need reversing — and it was ours, not upstream's

**S-3, `acknowledge_license_cert.html`, is currently unreachable by two independent mechanisms.**

Per `CLAUDE.local.md` §10, this was deliberate: the two `globals` rows were set to `0` to remove the
*links*, and an Apache block was added because the globals only hide the links while the file stays
reachable by direct URL:

```apache
<Files "acknowledge_license_cert.html">
    Require all denied
</Files>
```

**The stated rationale was sound and is recorded honestly:** the file was *not deleted* precisely
because it carries GPL text and distribution copyright notices, and deleting it would breach locked
constraint **C7** (BRAND-063/118 = **PRESERVE**). The reasoning explicitly says *"remove notices the
licence requires to be preserved on distribution."*

**But preserving a file that no user can reach is not obviously the same as keeping a notice
intact.** §4 requires notices be kept intact *and* that a copyright notice be *"conspicuously and
appropriately publish[ed]"*. §0's definition of Appropriate Legal Notices turns on a *"convenient
and prominently visible feature"*, and says a **prominent menu item** satisfies it — which is
precisely what `display_acknowledgements` used to provide and no longer does.

**This is flagged against our own interest.** It is the change most likely to require reversal, it
was made by this project rather than inherited, and it is trivially reversible:

```sql
UPDATE globals SET gl_value = '1'
 WHERE gl_name IN ('display_acknowledgements','display_acknowledgements_on_login');
```
…plus removing the Apache `<Files>` block and restarting (`CLAUDE.local.md` §10 documents the
restart procedure, including the `PATH` precondition in §4b).

**§5(d) may cut the other way** — *"if the Program has interactive interfaces that do not display
Appropriate Legal Notices, your work need not make them do so"* — since the acknowledgements link is
config-gated upstream and ships behind a global. **That is exactly the kind of reading that needs a
lawyer and not an engineer**, which is why it is Q1.

---

## 4. The determination questions

Each is closed-form. **None is answered here.**

| # | Question | Surfaces | Options and consequences |
|---|---|---|---|
| **Q1** | Does suppressing the acknowledgements link **and** blocking the page at the web server satisfy GPL-3 §4 and §5(d), given §0's "convenient and prominently visible feature"? | S-3, S-4 | **(a)** Restore both globals and remove the Apache block — safest, costs one upstream-branded page in the demo surface. **(b)** Keep suppressed, relying on §5(d) — needs a written rationale on file. **(c)** Replace with a Thiqa-branded page carrying the same notices verbatim — best of both, needs drafting and review |
| **Q2** | Must an **Appropriate Legal Notices** feature be reachable from the main menu of the rebranded product, and if so what must it say? | New surface | If yes, this is a **build item** that does not exist today and would become a new RDY. Its absence is currently unassessed |
| **Q3** | Is **"OpenEMR"** a trademark of the OpenEMR Foundation, and does the rebrand therefore *require* removing it from user-facing surfaces (as opposed to merely permitting it)? | S-5, S-12 | Trademark and copyright are separate. GPL-3 grants no trademark licence. If yes, S-5/S-12 are **obligations**, not choices — and the framing in RDY-0033 changes |
| **Q4** | May the product-registration modal keep soliciting consent to send data to the **OpenEMR Foundation**? | S-10 | **(a)** Disable the modal. **(b)** Rewrite the copy. **(c)** Leave it. Note it is a **third-party data-sharing consent shown to a Saudi clinic's staff**, so it is also a privacy question, not only a licensing one |
| **Q5** | `rwt_2026_report.php` instructs users to email a report to `hello@open-emr.org` for **ONC certification**. ONC is US-specific and out of scope for this ICP | S-11 | **(a)** Disable the report. **(b)** Leave it and rely on it never being opened. Note §32 **prohibits all certification claims** — a live screen inviting participation in an ONC process sits badly against that prohibition |
| **Q6** | Does the **hosted** model convey the Program at all (`LICENSE:100-101`), and what changes when a customer takes the offered **on-premise** option? | All | Determines whether §4/§5 obligations are triggered now, at first on-premise sale, or not at all. **This question governs the answers to Q1 and Q2** |
| **Q7** | Does the treatment in Q1 need to differ between the **demo instance** and a **customer instance**? | S-3, S-4 | A demo shown to a prospect and a hosted clinic instance may not carry the same obligations |
| **Q8** | Third-party components bundled with different licences — is any separate attribution required? | LForms/NLM webcomponent assets (identified in `EV-090`); `vendor/` dependencies | Not enumerated here. **If the answer to this is "it depends on which", a full dependency-licence inventory becomes a follow-on task** and should be raised as a new RDY rather than assumed |

---

## 5. What is already safe, and should not be re-litigated

To keep the review bounded, these are **not** in question and need no determination:

- **`LICENSE` is present and unmodified** (S-1).
- **All 2,396 per-file GPL headers are intact** (S-2). No patch record removed one; PR-01…PR-16 are
  string and logic changes that preserved headers.
- **Existing `@author` / `@copyright` lines were preserved** in every file this project edited —
  the project's own file-header standard requires it, and the patch records evidence it.
- **The open-source origin is disclosed deliberately**, not concealed: GTM POS-002 makes the
  category descriptor disclose it, and R-04 rates a prospect discovering it as *Certain* / *Low
  impact if we said it first*. **Branding must not create the concealment that turns a certainty
  into damage** — RDY-0033's own risk note.

---

## 6. Determination block — to be completed by Legal / Compliance

| Field | Value |
|---|---|
| Reviewer (named individual) | |
| Role / basis of authority | |
| Date | |
| Jurisdiction(s) considered | |

| # | Determination | Answer | Required action | Deadline |
|---|---|---|---|---|
| Q1 | Acknowledgements suppression | | | |
| Q2 | Appropriate Legal Notices feature | | | |
| Q3 | "OpenEMR" trademark | | | |
| Q4 | Product-registration modal | | | |
| Q5 | ONC / RWT report | | | |
| Q6 | Hosted vs conveyed | | | |
| Q7 | Demo vs customer instance | | | |
| Q8 | Third-party component attribution | | | |

**Overall verdict:** ☐ CLEARED AS IS ☐ CLEARED WITH THE ACTIONS ABOVE ☐ NOT CLEARED

**Signature / attestation route:** ______________________

> **No answer, verdict, signature or date is pre-filled.** RDY-0095 closes when this block is
> completed and the required actions are executed and evidenced — **not** when this pack is written.
> RDY-0033 and RDY-0034 then attach the completed determination and can close.

---

## 7. Status

**RDY-0095 — STILL BLOCKED — DECISION.** Unchanged. This pack removes the *preparation* cost, not
the decision.

**`Blocks`: G1 G4.** No gate count moved (§0.0 Rule 3).

**Consequential:** **RDY-0033** and **RDY-0034** remain NOT CLOSED for this reason alone. Their
configuration work is done and verified live (S-5…S-9); only the attached determination is missing.
See `EV-033-034-identity-and-vendor-links.md`.
