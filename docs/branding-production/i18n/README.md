# i18n — Arabic Translation Round-Trip Kit

This folder holds the tooling for the **native-Arabic proofreading pass** described in the corrected Group 1.5B certification. It lets you export the OpenEMR translation catalogue to CSV, hand it to a translator, and re-import their edits.

## Files

| File | Purpose |
|---|---|
| [export-arabic-translations.ps1](export-arabic-translations.ps1) | Reads `lang_constants` + `lang_definitions (lang_id=22)` and writes a UTF-8-with-BOM CSV. |
| [arabic-translations.csv](arabic-translations.csv) | The exported catalogue — 13,234 rows. Columns: `cons_id`, `def_id`, `english`, `arabic`. |
| [import-arabic-translations.ps1](import-arabic-translations.ps1) | Reads an edited CSV and diffs it against the DB. Applies INSERT/UPDATE/DELETE with a `START TRANSACTION` / `COMMIT` batch, gated by `-DryRun` and a `YES` confirmation. |
| [arabic-translations-diff.log](arabic-translations-diff.log) | Written by the import script — full audit of every row changed, including old vs new value. |

## Catalogue snapshot (last export)

- **Total constants:** 13,234
- **Already translated to Arabic:** 6,290 (47.5%)
- **Still empty:** 6,944
- File size: ~830 KB
- Encoding: UTF-8 with BOM (opens correctly in Excel and Google Sheets)

## The round-trip

```
  DB (lang_definitions)
        │
        ▼  export-arabic-translations.ps1
  arabic-translations.csv            ◀── proofreader edits the "arabic" column
        │
        ▼  import-arabic-translations.ps1 -DryRun
  arabic-translations-diff.log      ◀── you review before applying
        │
        ▼  import-arabic-translations.ps1  (YES prompt)
  DB (lang_definitions) updated
```

## Column rules (give this to the proofreader)

| Column | Editable? | Notes |
|---|---|---|
| `cons_id` | **NO** | Immutable primary key. Do not touch. |
| `def_id` | **NO** | Blank means "no Arabic row exists yet"; the import script will INSERT. Non-blank means "there is a row"; the import will UPDATE. |
| `english` | **NO** | The source string. Reference only. |
| `arabic` | **YES — this is the only column to edit** | Empty cell → the row will be DELETED from the DB if a row exists there. Non-empty cell → UPDATE or INSERT as appropriate. |

Add columns freely for your own use (e.g. `priority`, `reviewer`, `notes`) — the import script ignores unknown columns.

## Priority ordering for the proofreader

The catalogue is exported in `cons_id` order (insertion order in the OpenEMR upstream translation project). Sort or filter externally by:

1. **Safety-critical (P0)** — search `english` for `allergy|allergic|dose|dosage|prescription|prescribe|drug|medication|lot|batch|expiration|contraindication|interaction|critical|panic|normal|abnormal|reaction|adverse`.
2. **Highest visibility (P1)** — search for `login|sign in|password|username|dashboard|home|appointment|patient|record|search|message|billing`.
3. **Legal/financial (P2)** — search for `invoice|statement|claim|payment|refund|consent|insurance|balance|copay|charge`.
4. **Clinical daily forms (P3)** — search for `vital|blood pressure|pulse|temperature|weight|height|history|exam|encounter|complaint|assessment|plan|review of systems`.
5. **Rare / admin (P4)** — everything else (setup, upgrade, deep admin).

Filter in Excel/Google Sheets, sort by priority, work top-down.

## Style guide (give this to the proofreader)

- Modern Standard Arabic (فصحى), not dialect.
- Noun/verbal-noun on buttons (`حفظ`, `تعديل`, `حذف`), not imperative.
- Prefer the shorter synonym for tight UI slots (buttons, table headers).
- Numbers: Western Arabic (`0–9`) inside UI unless the customer specifies Arabic-Indic (`٠–٩`).
- Brand name: `ثقة` — never `الثقة`, never a prefix.
- Punctuation: Arabic comma `،` (U+060C), question mark `؟` (U+061F), semicolon `؛` (U+061B).
- Keep English brand terms in Latin script inside Arabic sentences (FHIR, SMART, HL7, EMR).
- Tone: professional-formal (implied `أنت`, not `حضرتك` and not colloquial).
- Medical terms: match Saudi Ministry of Health publications. Add English in parentheses on first use if uncertain.

## Usage

### Export current DB state

```powershell
G:\My Drive\OpenEMR\docs\branding-production\i18n\export-arabic-translations.ps1
```

Overwrites `arabic-translations.csv`.

### Hand the CSV to the proofreader

Just send them the file. It opens directly in Excel — the BOM ensures Arabic renders correctly. Ask them to save as CSV UTF-8 when done (Excel: `Save As → CSV UTF-8 (Comma delimited) (*.csv)` — not "CSV (Comma delimited)" which is Windows-1252).

### Preview their edits without changing the DB

```powershell
G:\My Drive\OpenEMR\docs\branding-production\i18n\import-arabic-translations.ps1 -DryRun
```

Writes `arabic-translations-diff.log`, prints INSERT/UPDATE/DELETE counts, and does NOT touch the database.

### Apply their edits

```powershell
G:\My Drive\OpenEMR\docs\branding-production\i18n\import-arabic-translations.ps1
```

Reads the CSV, shows counts, asks for `YES`, then applies inside a single `START TRANSACTION` / `COMMIT` block. If mariadb reports an error the transaction rolls back.

Use a **stage/test tenant** for this. Do not run against production data.

### Alternate CSV path

Both scripts accept `-Csv <path>` — pass a different filename if you keep multiple edit rounds:

```powershell
.\import-arabic-translations.ps1 -Csv 'C:\reviews\arabic-P0-safety-2026-08.csv' -DryRun
```

## Environment assumptions

- MariaDB CLI at `C:\openemr-stack\mariadb\bin\mariadb.exe` (native Windows stack — see [CLAUDE.local.md](../../../CLAUDE.local.md)).
- MariaDB running at `127.0.0.1:3306`, root, no password, database `openemr`.
- Arabic `lang_id = 22` in `lang_languages`. If your tenant uses a different `lang_id`, edit `$arabicLangId` at the top of the import script.
- The scripts force `[Console]::OutputEncoding = UTF-8` before invoking mariadb.exe — necessary on Windows so Arabic bytes aren't mangled by the console's OEM codepage.

## Where this fits in the overall proofreading plan

This is **Bucket A** from the plan in the audit report: the translation catalogue. Buckets B (config values in `globals`, `list_options`, `layout_options`, `facility`) and the Thiqa string map in [../14-string-replacement-map.md](../14-string-replacement-map.md) are separate deliverables.

Once the proofreader signs off on Bucket A, you have covered ~90% of scattered Arabic text in OpenEMR.
