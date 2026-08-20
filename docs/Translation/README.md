# Thiqa English–Arabic Translation Inventory

This folder contains the incrementally maintained translation inventory for the user-visible D-7 and RDY-0090 surfaces.

## Collection rules

- Each applicable screen is inspected in English and Arabic under the relevant demo role/account.
- Work is read-only: nothing is saved, submitted, signed, prescribed, or created.
- Current Arabic text is preserved exactly, including untranslated English or errors.
- Recommended Arabic uses formal Modern Standard Arabic for a Saudi healthcare/HIS context.
- Product names such as Thiqa are preserved.
- Terms are deduplicated within each screen, role/account, and access state.
- Routes, database IDs, clinical codes, patient data, and legal attribution are excluded.
- Browser inspection is preferred. Disabled or template-only surfaces may use local templates/configuration and are labeled accordingly.
- Screenshots are retained only for ambiguous translations, denial states, and print/email outputs.

## Files

- `Thiqa_Translation_Inventory_Master.xlsx`: current master workbook.
- `checkpoints/`: timestamped workbook copies created after each completed screen/account-language pass.
- `screenshots/`: selected evidence images referenced by the workbook.

## Workbook columns

The consolidated inventory includes Stable ID, Screen/Menu, Route, Role, Account, Access State, UI Element Type, English Term, Current Arabic (Exact), Recommended Arabic, Review Notes, Collection Source, Verification Status, Screenshot Reference, and Collected At.
