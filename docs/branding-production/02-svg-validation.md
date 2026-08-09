# Automated SVG Validation

Renderer: ImageMagick 7.1.2-29 Q16-HDRI with built-in RSVG delegate. XML parser: Python `xml.etree.ElementTree`.

| Canonical SVG | XML/root/viewBox | No raster/script/external ref | 512×512 render | Nonzero output | Status |
|---|---|---|---|---|---|
| `brand-logo-black.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-logo-compact.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-logo-primary-dark.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-logo-primary.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-logo-white.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-symbol-black.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-symbol-white.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |
| `brand-symbol.svg` | PASS | PASS | 512x512 PNG | PASS | PASS |

All canonical files are valid XML, have an SVG root and viewBox, contain no embedded image/script/unsafe reference, and rendered successfully. Path data was accepted by the standards-capable RSVG renderer. Visual comparison sheets show nonblank, unclipped artwork and no OpenEMR artwork/text. Monochrome white logo geometry is a color-only derivation of the approved monochrome dark master.
