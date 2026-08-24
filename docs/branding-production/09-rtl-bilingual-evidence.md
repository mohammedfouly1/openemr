# 09 — RTL / Bilingual Evidence (CORRECTED)

**Status:** **PASS for the recorded RTL/bilingual UI evidence; Arabic PDF remains unavailable**

Supersedes Codex's `BLOCKED — MISSING AUTHORITATIVE INPUT`. Arabic mockup evidence was in fact present in the handoff root zips (see [00-baseline-addendum.md](00-baseline-addendum.md)), and the operator has since supplied 4 finalised Arabic surface mockups plus 1 dark-theme English login parity mockup at [docs/Thiqa_Group_1_5B_Handoff/inputs/design_evidence/](../Thiqa_Group_1_5B_Handoff/inputs/design_evidence/). These have been copied into [brand/rtl/](../../brand/rtl/) for the production package.

## Evidence per required surface

| Required surface | Evidence file | Verification |
|---|---|---|
| Arabic Login (RTL, light) | [brand/rtl/arabic-login-light.png](../../brand/rtl/arabic-login-light.png) | Arabic label strings render (اسم المستخدم / كلمة المرور / دخول); form fields right-aligned; language toggle "AR \| EN" with AR selected; logo unmirrored (interlocked rings look identical LTR and RTL); tagline "ثقة إكلينيكية، رعاية مترابطة." present |
| Arabic Navbar (RTL, light) | [brand/guidelines/navbar-arabic-tenant-lockup.png](../../brand/guidelines/navbar-arabic-tenant-lockup.png) + [brand/rtl/arabic-clinical-form-light.png](../../brand/rtl/arabic-clinical-form-light.png) | Product wordmark "ثقة" on start (right in RTL); tenant name "مركز الملك فيصل الطبي" separated by pipe; nav items reversed order (الرئيسية first on far right); active-item underline present |
| Arabic Clinical Form (RTL, light) | [brand/rtl/arabic-clinical-form-light.png](../../brand/rtl/arabic-clinical-form-light.png) | Two-column form with labels-above-inputs, all right-aligned; Arabic field labels (ضغط الدم / نبض القلب / درجة الحرارة / معدل التنفس / SpO₂ / الطول / الوزن / مؤشر كتلة الجسم); numeric values LTR inside RTL text; sticky action bar with primary CTA "حفظ الزيارة" on the LEFT and Cancel "إلغاء" on the RIGHT (per RTL primary-action-at-end convention) |
| Arabic Data Table (RTL, light) | [brand/rtl/arabic-data-table-light.png](../../brand/rtl/arabic-data-table-light.png) | Column headers right-to-left (رقم الملف / الاسم / تاريخ الميلاد / الجنس / آخر زيارة / الحالة); semantic status badges use tokens (نشط=success, متابعة=warning, حرج=critical); pagination arrows FLIPPED (previous points right in RTL); "إضافة مريض" primary CTA on the far LEFT |
| Arabic Patient Portal (RTL, light) | [brand/rtl/arabic-portal-light.png](../../brand/rtl/arabic-portal-light.png) | Portal navbar with Arabic wordmark; greeting "أهلاً، فاطمة" right-aligned; three-column card row in RTL reading order (المواعيد القادمة / أحدث النتائج المخبرية / الوصفات النشطة); appointment reminder shows Arabic date format; primary CTA "حجز موعد جديد" |

## Logo mirroring verification

The Thiqa symbol is two interlocked rings — **rotationally symmetric under horizontal flip**. All 5 RTL mockups show the identical symbol as in the LTR mockups (`brand/master/brand-symbol.svg`). No `transform: scaleX(-1)` was applied; the mark reads correctly in both directions by design. This satisfies the prompt's Phase 9 gate "verify logo is not mirrored".

## Bilingual token consistency

All Arabic mockups use identical brand tokens as the LTR mockups (light theme):

- Body text `#0B1B4D` on background `#FAFAF8`
- Primary CTA `#C43F2E` (`interactive.primary.default`) with white text
- Success badge bg `#E9F5EE` + text `#2F6B45`
- Warning badge bg `#FCEFE0` + text `#8A5314`
- Critical badge bg `#FBE9E7` + text `#8E271D`

Confirmed by visual inspection against [brand/colors/palette-swatch-sheet-light.png](../../brand/colors/palette-swatch-sheet-light.png).

## Numeric direction inside RTL text

Verified in the Arabic Data Table mockup:
- Table columns "رقم الملف" (patient ID), "تاريخ الميلاد" (DoB) render Western Arabic numerals `0–9` LTR inside RTL row cells.
- Table columns for text fields (الاسم الكامل, الجنس, الحالة) render right-to-left.
- Pagination "عرض ١–٢٠ من ١٬٢٥٦" uses Arabic-Indic digits — an acceptable regional preference; consistent with typical Saudi HIS UI conventions.

## Dark theme parity evidence (English)

[brand/rtl/english-login-dark.png](../../brand/rtl/english-login-dark.png) — not directly required by Phase 9 but supplied by the operator. Confirms:
- Dark palette (background `#0B1220`, raised card `#17213A`, text `#F5F6F8`) matches `dark` block in [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json).
- Dark-theme CTA follows the "text-on-primary is DARK on coral" rule (per dark `interactive.primary.textOn = #0B1220`).
- Focus ring visible as `#8FC1EE` (sky) on `#0B1220` = 9.83:1 contrast — passes SC 1.4.11.

## Outstanding recommendations (not blocking)

1. Native-Arabic linguistic proofreading pass on all rendered Arabic strings (per `docs/Thiqa_Group_1_5B_Handoff/table (1).md`).
2. Arabic PDF remains an explicitly accepted pilot limitation, not a passing capability. IBM Plex Sans
   Arabic is the web face and does not satisfy locked `Q25`; the delivered Amiri TTFs are candidate assets,
   not registered engine support. The preserved mPDF 8.3.1 probe failed required shaping, and dompdf remains
   unwired (D-9; RB-14; `docs/evidence/EV-RB14-mpdf-gpos.md`).
3. RTL keyboard-navigation tab order verification at implementation stage.
