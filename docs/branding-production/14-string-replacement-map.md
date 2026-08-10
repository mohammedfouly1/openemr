# 14 — Thiqa String Replacement Map (EN + AR)

**Status:** DRAFT — awaits native-Arabic linguistic proofing pass (per `docs/Thiqa_Group_1_5B_Handoff/table (1).md`).
**Revision 2 (2026-08-09):** cross-references corrected (see *Cross-reference correction* below); Part 1 heading corrected; rows 25/28 corrected for the RTL mechanism (decision CR-3); production domain set to `skyeagle.uk` (decision D-2). Recorded in [15-decision-record.md](15-decision-record.md).
**Scope:** Every hardcoded OpenEMR brand string enumerated in `docs/rebranding.md` §15.1–§15.2, plus the branding globals in `docs/rebranding.md` §3 / §9.4.
**Rule:** Text-content only. This file does NOT modify OpenEMR source; it is the authoritative EN/AR value list that Group 2 will materialise.

Product name (English): **Thiqa**
Product name (Arabic): **ثقة**
Product name (Arabic transliteration): **Thiqah**
Tagline (English): **Clinical confidence, connected care.**
Tagline (Arabic): **ثقة إكلينيكية، رعاية مترابطة.**
Production domain: **`skyeagle.uk`**

### Cross-reference correction (revision 2)

Revision 1 of this document cited the wrong sections of `docs/rebranding.md`. The *content* of every part
below was, and remains, an exact item-for-item match with the discovery inventory; only the signposts were
wrong. Corrected mapping:

| Cited in revision 1 | Actual section in `docs/rebranding.md` | Covers |
|---|---|---|
| §13 (branding globals) | **§3** (live DB values) and **§9.4** / §9.5 / §9.6 / §9.7 (canonical inventory) | The 33 branding-relevant globals |
| §14 (mandatory patches) | **§15.1** | The 8 mandatory MVP patches |
| §15 (conditional patches) | **§15.2** | The 5 conditional patches |
| §16 (R-SMART-DARK) | **§16.1** | The SMART dark-token requirement |

`docs/rebranding.md` §13 is the `Q76` materialisation analysis and §14 is the `Q77` theme-surplus analysis;
neither is a string list.

---

## Part 1 — Branding configuration rows (35 rows: the 33 branding-relevant globals of `docs/rebranding.md` §3, plus 2 portal-enablement globals)

> **Row count note (revision 2).** Revision 1 titled this table *"33 items"* while listing 35 rows. Both
> figures were defensible and nothing was inserted: rows 1–33 are the 33 branding-relevant globals counted
> in `docs/rebranding.md` §3, and rows 34–35 (`portal_onsite_two_address` = BRAND-070,
> `portal_onsite_two_enable` = BRAND-064) are portal-enablement globals that the same report inventories
> separately. The heading now states both numbers.

| # | Global | Current default | Thiqa EN | Thiqa AR |
|---|---|---|---|---|
| 1 | `openemr_name` | `OpenEMR` | `Thiqa` | `ثقة` |
| 2 | `login_tagline_text` | open-source advert copy | `Clinical confidence, connected care.` | `ثقة إكلينيكية، رعاية مترابطة.` |
| 3 | `show_tagline_on_login` | `1` | `1` | `1` |
| 4 | `main_menu_logo_link` | `https://www.open-emr.org/` | `https://skyeagle.uk/` | same |
| 5 | `main_menu_logo_title` | `''` (empty) | `Thiqa Health Information System` | `نظام ثقة للمعلومات الصحية` |
| 6 | `display_main_menu_logo` | `1` | `1` | `1` |
| 7 | `online_support_link` | `http://open-emr.org/` | `https://skyeagle.uk/support` *(HTTPS mandatory)* | same |
| 8 | `user_manual_link` | `''` (auto → open-emr.org wiki) | `https://skyeagle.uk/docs` | same |
| 9 | `support_phone_number` | `''` | *(tenant-specific — leave blank at product level)* | same |
| 10 | `display_acknowledgements` | `1` | `1` | `1` |
| 11 | `display_review_link` | `1` | `0` | `0` |
| 12 | `display_donations_link` | `1` | `0` | `0` |
| 13 | `display_acknowledgements_on_login` | `1` | `1` | `1` |
| 14 | `login_page_layout` | `vertical_band` | `vertical_band` | `vertical_band` |
| 15 | `primary_logo_width` | `w-50` | `w-50` | `w-50` |
| 16 | `secondary_logo_width` | `w-50` | `w-50` | `w-50` |
| 17 | `logo_position` | `flex-column` | `flex-column` | `flex-column` |
| 18 | `show_primary_logo` | `1` | `1` | `1` |
| 19 | `extra_logo_login` | `0` | `0` | `0` |
| 20 | `secondary_logo_position` | `second` | `second` | `second` |
| 21 | `show_labels_on_login_form` | `1` | `1` | `1` |
| 22 | `show_label_login` | `0` | `0` | `0` |
| 23 | `tiny_logo_1` | `0` | `0` | `0` |
| 24 | `tiny_logo_2` | `0` | `0` | `0` |
| 25 | `css_header` | `style_default.css` | `style_light.css` (Saudi Light) | **`style_light.css`** — see RTL note below |
| 26 | `theme_tabs_layout` | `tabs_style_full.css` | `tabs_style_full.css` | `tabs_style_full.css` |
| 27 | `window_title_add_patient_name` | `0` | `0` | `0` |
| 28 | `portal_css_header` | `style_light.css` | `style_light.css` | **`style_light.css`** — see RTL note below |
| 29 | `show_portal_primary_logo` | `1` | `1` | `1` |
| 30 | `extra_portal_logo_login` | `0` | `0` | `0` |
| 31 | `secondary_portal_logo_position` | `second` | `second` | `second` |
| 32 | `portal_primary_menu_logo_height` | `30` | `30` | `30` |
| 33 | `statement_logo` | `practice_logo.gif` | `practice_logo.gif` (Thiqa monochrome navy — see [brand/logos/legacy/practice_logo.gif](../../brand/logos/legacy/practice_logo.gif)) | same |
| 34 | `portal_onsite_two_address` | `https://your_web_site.com/openemr/portal` | *(tenant-specific)* | same |
| 35 | `portal_onsite_two_enable` | `0` | `1` | `1` |

### RTL note for rows 25 and 28 — CORRECTED (decision CR-3, 2026-08-09)

> **Revision 1 was wrong and is withdrawn.** It instructed that `css_header` and `portal_css_header` be set
> to `rtl_style_light.css` for Arabic. **Never store an `rtl_`-prefixed filename in `globals`.**
>
> OpenEMR performs the RTL substitution itself from the session's `language_direction`
> (`interface/globals.php:551-611`). Separately, it derives the compact stylesheet by prefixing whatever
> value is stored (`interface/globals.php:494`). Storing `rtl_style_light.css` therefore yields
> `compact_rtl_style_light.css`, which the build never produces — the real artefact is
> `rtl_compact_style_light.css` — so every Arabic session using the compact layout would request a
> non-existent stylesheet and render unstyled. It also suppresses OpenEMR's own RTL override, because that
> code skips the substitution when the stored value already contains `rtl`.
>
> **Correct behaviour:** store `style_light.css` for **all** languages including Arabic. The runtime
> resolves `rtl_style_light.css` and `rtl_compact_style_light.css` automatically. A regression test
> (plan check `V-07`) asserts both files are served on an Arabic session.

### Theme filename note (decision CR-9, 2026-08-09)

The two shipped variants keep the inherited CSS filenames **`style_light.css`** and **`style_dark.css`**;
*Saudi Light* and *Saudi Dark* are the product-facing labels. Renaming the files would require patching the
hardcoded fallback literal at `interface/globals.php:476`, creating a permanent upstream-rebase conflict in
a core bootstrap file for no user-visible benefit. The file **contents** are entirely Thiqa; only the
internal filename is inherited.

Session-identity constants (`CORE_SESSION_ID`, `PORTAL_SESSION_ID`, `OAUTH_SESSION_ID`, `API_SESSION_ID`, `SETUP_SESSION_ID`, `APP_COOKIE_NAME`) remain unchanged per `docs/rebranding.md` §15.3 (locked decision `Q17`).

---

## Part 2 — Mandatory source patches (8 items from `docs/rebranding.md` §15.1)

| # | File | Line | Current text | Thiqa EN | Thiqa AR (where UI-translatable) |
|---|---|---:|---|---|---|
| 1 | `src/RestControllers/FhirMetaDataRestController.php` | 84 | `"OpenEMR"` (FHIR `software.name`) | `Thiqa` | *machine-facing; not translated* |
| 2 | same | 70 | `"OpenEMR FHIR API"` (`implementation.description`) | `Thiqa FHIR API` | *machine-facing; not translated* |
| 3 | `src/Services/ProductRegistrationService.php` | 121 | `https://reg.open-emr.org/api/registration` | **Registration DISABLED** (decision D-10, 2026-08-09). No endpoint is configured; the product does not phone home. The patch makes registration a no-op/disabled feature rather than repointing the URL, so no Thiqa endpoint has to exist or be operated | same |
| 4 | `templates/api/smart/smart-style_dark.json.twig` | — | *does not exist* | Create with 12 keys from [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json) `dark` block. **Delivered as a branding-module template override, not a core file** — the resolver at `SMARTAuthorizationController.php:433-434` already requests `smart-<theme>.json.twig` and only falls back to light when it is missing | same |
| 5a | `admin.php` | 40 | `OpenEMR Site Administration` | `Thiqa Site Administration` | `إدارة موقع ثقة` |
| 5b | `admin.php` | 53 | `OpenEMR Multi Site Administration` | `Thiqa Multi-Site Administration` | `إدارة مواقع ثقة المتعددة` |
| 6 | `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php` | 108 | `"OpenEMR Error: API is disabled"` | `Thiqa Error: API is disabled` | *machine-facing JSON; not translated* |
| 7a | `interface/globals.php` | 99 | `OpenEMR Error :` (pre-bootstrap fatal) | `Thiqa Error:` | *pre-bootstrap; cannot use xl()* |
| 7b | `interface/globals.php` | 106 | `OpenEMR Error :` (pre-bootstrap fatal) | `Thiqa Error:` | *pre-bootstrap; cannot use xl()* |
| 8 | `Installer/...` Zend | 36, 117 | hardcoded `open-emr.org/wiki` URLs | `https://skyeagle.uk/docs/installer` | same |

---

## Part 3 — Conditional patches (5 items from `docs/rebranding.md` §15.2)

| # | File | Current | Thiqa EN | Thiqa AR |
|---|---|---|---|---|
| 1a | `setup.php` title | `OpenEMR Setup` | `Thiqa Setup` | `تثبيت ثقة` |
| 1b | `sql_patch.php` | `OpenEMR Patch` | `Thiqa Patch` | `تحديث ثقة` |
| 1c | `sql_upgrade.php` | `OpenEMR Upgrade` | `Thiqa Upgrade` | `ترقية ثقة` |
| 1d | `ippf_upgrade.php` | `OpenEMR IPPF Upgrade` | `Thiqa IPPF Upgrade` | `ترقية ثقة IPPF` |
| 2 | `eye_mag/help.php`, `eye_mag_functions.php` | `sites/default` hardcode | keep hardcode; only affects Eye-Magic form (conditional) | same |
| 3 | `Header.php:95` duplicate favicon `<link>` | cosmetic double emit | remove duplicate emit | n/a |
| 4 | `primary_logo.html.twig` | `alt=""` | `alt="Thiqa"` (default) — override per tenant. **Delivered as a branding-module template override, not a core edit** | Arabic pages: `alt="ثقة"` |
| 5 | `globals.php:633` pre-DB `$openemr_name` fallback | `OpenEMR` | `Thiqa` | pre-bootstrap; cannot translate |

---

## Part 4 — Common UI strings that reference the product name

These are strings materialised through `xl()`/`xlt()` — Group 2 wires the Arabic value into `.po`/`.mo` translation files. The list here is the source-of-truth for those catalogues.

| Context | English | Arabic |
|---|---|---|
| Login window title | `Thiqa Login` | `تسجيل الدخول إلى ثقة` |
| Portal login title | `Thiqa Patient Portal Login` | `تسجيل الدخول إلى بوابة مرضى ثقة` |
| Portal home title | `Thiqa Patient Portal` | `بوابة مرضى ثقة` |
| Main menu tooltip | `Thiqa Health Information System` | `نظام ثقة للمعلومات الصحية` |
| Support link label | `Thiqa Support` | `دعم ثقة` |
| Documentation link label | `Thiqa Documentation` | `توثيق ثقة` |
| Acknowledgements link label | `Acknowledgements, Licensing and Certification` | `الإقرارات، الترخيص، والاعتماد` |
| 400 error page title | `Thiqa 400 Error` | `ثقة — خطأ 400` |
| 404 error page title | `Thiqa 404 Error` | `ثقة — خطأ 404` |
| OAuth2 authorization title | `Thiqa Authorization` | `تفويض ثقة` |
| SMART consent header | `Authorization Request` | `طلب التفويض` |
| SMART "Powered by" footer | `Powered by Thiqa on FHIR R4` | `مشغّل بواسطة ثقة عبر FHIR R4` |
| Prescription email subject prefix | `Thiqa Prescription` | `وصفة ثقة` |
| Telehealth email subject | `Thiqa Telehealth Invitation` | `دعوة الرعاية الصحية عن بعد من ثقة` |
| Statement/PDF header | `Statement — Thiqa` | `كشف حساب — ثقة` |

---

## Part 5 — Nav labels (from Recraft NAV-01/02 mockups)

| Nav item | English | Arabic |
|---|---|---|
| Home | `Home` | `الرئيسية` |
| Appointments | `Appointments` | `المواعيد` |
| Records | `Records` | `السجلات` |
| Messages | `Messages` | `الرسائل` |
| Billing | `Billing` | `الفوترة` |
| Search placeholder | `Search records, patients…` | `ابحث في السجلات، المرضى…` |
| Language toggle | `AR | EN` | `AR | EN` |
| Sign in | `Sign in` | `تسجيل الدخول` |
| Sign out | `Sign out` | `تسجيل الخروج` |

---

## Part 6 — Business decisions: status

**RESOLVED (decision D-2, 2026-08-09).** The production domain is **`skyeagle.uk`**. Every `thiqa.example`
placeholder in revision 1 has been replaced:

| Value | Setting | Resolved to |
|---|---|---|
| Product root URL | `main_menu_logo_link` (BRAND-055) | `https://skyeagle.uk/` |
| Support URL | `online_support_link` (BRAND-057) | `https://skyeagle.uk/support` |
| Documentation URL | `user_manual_link` (BRAND-058) | `https://skyeagle.uk/docs` |
| Installer docs URL | Zend installer view (BRAND-130) | `https://skyeagle.uk/docs/installer` |

**No `.example` domain remains in this document.** A release check must assert this
(plan §7.3 release gate).

**STILL OPEN:**

| # | Item | Owner | Note |
|---|---|---|---|
| D-3 | Legal registration of the product name `Thiqa` / `ثقة`, **and** integration-owner clearance for the **two** machine-facing carriers identified by the code inventory in [16-conflict-resolutions.md](16-conflict-resolutions.md) §3 | Legal + integration owners | Narrower than first assumed: exactly one HL7 `MSH-3` emission (syndromic-surveillance ADT) and two QRDA organisation fields |
| — | Support email address and support phone (`support_phone_number`, BRAND-059) | Product owner | Optional; currently blank at product level, expected to be tenant-specific |

**D-10 is CLOSED (2026-08-09):** product registration is **disabled**, not repointed. No
`reg.skyeagle.uk` endpoint is required.

---

## Part 7 — Post-implementation linguistic verification

Before flipping any of these strings live:

1. Native Arabic proofreader must review all Arabic values in Parts 2–5.
2. Arabic PDF rendering must be verified with **`Amiri`** (Noto Naskh Arabic is the accepted alternative), vendored at [brand/typography/fonts/pdf/](../../brand/typography/fonts/pdf/), with **both** PDF engines (`mpdf/mpdf`, `dompdf/dompdf`) explicitly configured to use it.
   **Corrected 2026-08-10 (RB-13).** This item previously named `IBM Plex Sans Arabic` for PDF. That conflicts with locked decision **`Q25`**, which names *Amiri and/or Noto Naskh Arabic* for print and requires explicit engine configuration — the conflict was raised as **CR-16** and accepted in `docs/RebrandingPlan.md` §1.0.2 and §3.7.4. IBM Plex Sans Arabic remains the **web** face, where no locked decision governs the choice; it does **not** satisfy `Q25` for PDF. Tracking dependency: **D-9**.
3. Right-to-left CSS mirror-tests must pass for the login, navbar, patient portal, clinical form, and data table surfaces documented in [docs/Thiqa_Group_1_5B_Handoff/inputs/design_evidence/](../Thiqa_Group_1_5B_Handoff/inputs/design_evidence/).
4. Numeric fields (patient ID, claim number, monetary values, dates) must render Western Arabic numerals `0–9` LTR inside RTL text runs.

---

## Phase 5 status check (2026-08-10)

Checked as Phase 5 deliverable F8 (`docs/RebrandingPlan.md` §7.1: "Fix CR-1/CR-2 section references and
the CR-3 rows in `14-string-replacement-map.md`; lift its status from DRAFT once D-4 completes").

**Amended 2026-08-10 (RB-13): this check originally concluded "nothing stale was found". That was wrong.**
The F8 pass verified cross-reference *numbering* and missed a substantive conflict with a locked decision
two screens above it: Part 7 item 2 named `IBM Plex Sans Arabic` for Arabic **PDF** rendering, which
contradicts **`Q25`** (Amiri and/or Noto Naskh Arabic, engines explicitly configured) and the already-accepted
correction **CR-16**. Item 2 has now been corrected in place. Recorded here rather than silently amended,
because a "nothing stale was found" statement that missed a locked-decision conflict is worse than no
statement — `docs/rebranding.md` §18's corrections-register rule applies to this document too.

**CR-1/CR-2/CR-3 re-verified, no further correction needed.** Every cross-reference this document makes
into `docs/rebranding.md` (§3, §9.4, §15.1, §15.2, §15.3, §16.1 — see the "Cross-reference correction"
table near the top of this file and the RTL note at rows 25/28) was checked directly against
`docs/rebranding.md`'s actual section headers during this pass and matches exactly. Revision 2
(2026-08-09, recorded above) already applied the CR-1/CR-2/CR-3 corrections per
[`docs/branding-production/15-decision-record.md`](15-decision-record.md); nothing stale was found.

**Status remains DRAFT — this is correct, not an oversight.** The header of this document ties lifting
DRAFT status to completion of the native-Arabic linguistic proofing pass. That dependency is **D-4** in
the blocking-dependency register, and `docs/branding/remaining-dependencies.md` §4 (this session's Phase 4
verification) confirms D-4 is still **OPEN — no proofreading evidence found**. Per F8's own condition
("lift its status from DRAFT once D-4 completes"), that condition is not yet met, so the DRAFT status at
the top of this document is left unchanged by this check.
