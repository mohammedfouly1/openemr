# Exact Production Exports

Renderer: ImageMagick 7.1.2-29 Q16-HDRI with the built-in RSVG delegate.

Command pattern: `magick -background none MASTER.svg -resize WIDTHxHEIGHT -gravity center -extent WIDTHxHEIGHT -strip OUTPUT.png`.

This preserves artwork aspect ratio and uses transparent centered padding. Exact width, height, format/mode, alpha, byte size, and SHA-256 for every required PNG are computed from the physical files in `11-asset-manifest.md`, `brand/manifests/asset-manifest.json`, and `brand/manifests/asset-manifest.csv`.

`navbar-symbol.png` uses a 64×64 production canvas. Its integration contract is 16×16 CSS pixels (16 px rendered height); Group 1.5B does not modify application CSS or runtime assets.

Favicon PNGs were rendered independently from the canonical symbol at 16×16, 32×32, and 48×48. The ICO was assembled from those three physical PNGs rather than renamed from another format.
