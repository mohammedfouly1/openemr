# Group 1.5B Baseline and Input Purity

Captured before creation of any `brand/` production assets on 2026-08-09.

## Repository baseline

- Branch: `master`
- HEAD: `631f2b38cf633769c305233f88cdf9c73ca80657`
- Baseline `git status --porcelain=v1`:

```text
 D Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.dot
 M sites/default/sqlconf.php
?? Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.docx
?? "Locked Desicions/"
?? SETUP-STATUS.md
?? docs/00-discovery/
?? docs/HISModulesUsers.md
?? docs/Thiqa_Group_1_5B_Handoff/
?? docs/discovery/
?? docs/rebranding.md
?? fix-docker-virtualization.ps1
?? tools/discovery/
```

These differences pre-date Group 1.5B and are user-owned. The task may create only `brand/` and `docs/branding-production/`; supplied inputs must remain byte-identical.

## Input inventory

| Relative path | Bytes | SHA-256 |
|---|---:|---|
| `inputs/reference_docs/typography-weight-contract.md` | 404 | `c7987bd0b4b1f6f1ed6911fd3afd117a0f7b5448ca10ad1447bed6a371c1e5aa` |
| `inputs/svg_masters_unmapped/image (1).svg` | 25251 | `0c712047fa77e4573308fa48e04046b87a94005cbbd33ebe60f2cb221134651d` |
| `inputs/svg_masters_unmapped/image (10).svg` | 17608 | `2251c21f37cdf44be49ddc7b11238b84e344ea47f50b92ae518f0e65337c6771` |
| `inputs/svg_masters_unmapped/image (2).svg` | 25109 | `743a9e5ca5bf582760bd6ad992ef0754eeb577071799cf7f3697b91f68a76769` |
| `inputs/svg_masters_unmapped/image (3).svg` | 24936 | `4f81cc2d678cb0d51ee7fc4cb5edbdcb808702a59a28fd2f10cddfe224fc8ce1` |
| `inputs/svg_masters_unmapped/image (4).svg` | 25211 | `ab4cd7f5a479d17ccd90fe22eacfd5c40bac93bcf5e02deefba380a72fd02b53` |
| `inputs/svg_masters_unmapped/image (5).svg` | 31524 | `2bd04257a3eda2664fd66eb3d2e87c3cb787f791e3860fd317a8618608cb90db` |
| `inputs/svg_masters_unmapped/image (6).svg` | 31776 | `68abe709d6aa8b515f6c630d04a96c7bd15f0a487f480c2e6201502e7239efee` |
| `inputs/svg_masters_unmapped/image (7).svg` | 14714 | `693e851dfa5087d3200457b23d69f67194fe1b084b782896b7f1c7273e358984` |
| `inputs/svg_masters_unmapped/image (8).svg` | 17703 | `08be679643a78c46cd38697fb2d9b01c052a4a66ae4d03dd1bfa9e3ec9661d31` |
| `inputs/svg_masters_unmapped/image (9).svg` | 17880 | `377173b115a369fed1cc561a8319ef48eba75696a600819b2ce2c34e13685738` |
| `inputs/svg_masters_unmapped/image.svg` | 23050 | `61354d082e8b01a273e03a5c9d50e86e999895fe61fc1dda8d57fc7c10a1847f` |

The 11 SVG hashes agree with the supplied handoff README. Originals are read-only evidence and will not be overwritten.
