#!/usr/bin/env python3
"""
analyze-brand-assets.py — image/asset metadata extractor for the rebranding audit.

Read-only. Emits CSV to stdout (and optionally a file) with, per tracked asset:
    path, ext, bytes, width, height, colour_mode, colours

Dependency-free by design: PNG/GIF/JPEG/ICO/BMP headers are parsed directly and
PNG pixel data is defiltered with the stdlib `zlib`, so the script runs anywhere
Python 3 does (no PIL on this host). SVG colours come from an attribute/stylesheet
regex, which is exact for the flat vector logos in this tree.
"""

from __future__ import annotations

import argparse
import binascii
import collections
import re
import struct
import subprocess
import sys
import zlib
from pathlib import Path

ROOT = Path(subprocess.run(["git", "rev-parse", "--show-toplevel"],
                           capture_output=True, text=True, check=True).stdout.strip())

IMAGE_EXT = {".png", ".gif", ".jpg", ".jpeg", ".svg", ".ico", ".bmp", ".webp"}

PNG_COLOR_TYPE = {0: "grayscale", 2: "truecolor", 3: "palette",
                  4: "grayscale+alpha", 6: "truecolor+alpha"}


# ----------------------------------------------------------------- dimensions

def png_info(data: bytes):
    if len(data) < 26 or data[:8] != b"\x89PNG\r\n\x1a\n":
        return None
    w, h = struct.unpack(">II", data[16:24])
    depth, ctype = data[24], data[25]
    return w, h, f"png/{PNG_COLOR_TYPE.get(ctype, ctype)}/{depth}bit"


def gif_info(data: bytes):
    if len(data) < 10 or data[:3] != b"GIF":
        return None
    w, h = struct.unpack("<HH", data[6:10])
    flags = data[10]
    ncolors = 2 ** ((flags & 0x07) + 1) if flags & 0x80 else 0
    return w, h, f"gif/palette/{ncolors}col"


def jpeg_info(data: bytes):
    if len(data) < 4 or data[:2] != b"\xff\xd8":
        return None
    i = 2
    n = len(data)
    while i < n - 9:
        if data[i] != 0xFF:
            i += 1
            continue
        marker = data[i + 1]
        if marker in (0xD8, 0xD9) or 0xD0 <= marker <= 0xD7:
            i += 2
            continue
        seglen = struct.unpack(">H", data[i + 2:i + 4])[0]
        if marker in (0xC0, 0xC1, 0xC2, 0xC3, 0xC5, 0xC6, 0xC7,
                      0xC9, 0xCA, 0xCB, 0xCD, 0xCE, 0xCF):
            h, w = struct.unpack(">HH", data[i + 5:i + 9])
            comps = data[i + 9] if i + 9 < n else 0
            return w, h, f"jpeg/{'ycbcr' if comps == 3 else 'gray' if comps == 1 else comps}"
        i += 2 + seglen
    return None


def ico_info(data: bytes):
    if len(data) < 22 or data[:4] != b"\x00\x00\x01\x00":
        return None
    count = struct.unpack("<H", data[4:6])[0]
    sizes = []
    for k in range(count):
        off = 6 + k * 16
        if off + 16 > len(data):
            break
        w = data[off] or 256
        h = data[off + 1] or 256
        sizes.append(f"{w}x{h}")
    if not sizes:
        return None
    first_w, first_h = sizes[0].split("x")
    return int(first_w), int(first_h), f"ico/{count}frame[{','.join(sizes)}]"


def bmp_info(data: bytes):
    if len(data) < 26 or data[:2] != b"BM":
        return None
    w, h = struct.unpack("<ii", data[18:26])
    return w, abs(h), "bmp"


SVG_W = re.compile(rb'\bwidth\s*=\s*["\']([^"\']+)["\']')
SVG_H = re.compile(rb'\bheight\s*=\s*["\']([^"\']+)["\']')
SVG_VB = re.compile(rb'\bviewBox\s*=\s*["\']\s*[-\d.]+\s+[-\d.]+\s+([\d.]+)\s+([\d.]+)')


def svg_info(data: bytes):
    head = data[:4000]
    vb = SVG_VB.search(head)
    if vb:
        try:
            return (int(float(vb.group(1))), int(float(vb.group(2))), "svg/viewBox")
        except ValueError:
            pass
    w, h = SVG_W.search(head), SVG_H.search(head)
    if w and h:
        def num(b):
            m = re.match(rb"([\d.]+)", b)
            return int(float(m.group(1))) if m else 0
        return num(w.group(1)), num(h.group(1)), "svg/attr"
    return 0, 0, "svg/scalable"


def dimensions(path: Path, data: bytes):
    ext = path.suffix.lower()
    fn = {".png": png_info, ".gif": gif_info, ".jpg": jpeg_info, ".jpeg": jpeg_info,
          ".ico": ico_info, ".bmp": bmp_info, ".svg": svg_info}.get(ext)
    if not fn:
        return None
    try:
        return fn(data)
    except Exception:
        return None


# --------------------------------------------------------------------- colours

HEX_RE = re.compile(rb"#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b")
RGB_RE = re.compile(rb"rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)")
NAMED = (b"white", b"black", b"none", b"currentColor", b"red", b"blue", b"green")


def svg_colors(data: bytes, limit=8):
    seen = collections.Counter()
    for m in HEX_RE.finditer(data):
        h = m.group(1).decode()
        if len(h) == 3:
            h = "".join(c * 2 for c in h)
        seen[f"#{h.lower()}"] += 1
    for m in RGB_RE.finditer(data):
        r, g, b = (int(m.group(i)) for i in (1, 2, 3))
        seen[f"#{r:02x}{g:02x}{b:02x}"] += 1
    for nm in NAMED:
        if re.search(rb'[:="\']\s*' + nm + rb'\b', data):
            seen[nm.decode()] += 1
    return [c for c, _ in seen.most_common(limit)]


def png_palette(data: bytes, limit=8):
    """Return PLTE entries for palette PNGs — cheap and exact, no decoding needed."""
    i = 8
    while i + 8 <= len(data):
        ln = struct.unpack(">I", data[i:i + 4])[0]
        typ = data[i + 4:i + 8]
        if typ == b"PLTE":
            pal = data[i + 8:i + 8 + ln]
            out = []
            for k in range(0, min(len(pal), limit * 3), 3):
                out.append(f"#{pal[k]:02x}{pal[k+1]:02x}{pal[k+2]:02x}")
            return out
        if typ == b"IDAT":
            break
        i += 12 + ln
    return []


def png_dominant(data: bytes, limit=6, max_pixels=120000):
    """
    Decode a truecolor/grayscale PNG far enough to count visible colours.
    Handles the 5 PNG filter types; skips interlaced images (rare, and none of
    the brand assets here use interlacing).
    """
    try:
        w, h = struct.unpack(">II", data[16:24])
        depth, ctype, _, _, interlace = data[24], data[25], data[26], data[27], data[28]
        if interlace or depth != 8 or ctype not in (0, 2, 4, 6):
            return []
        if w * h > max_pixels:
            return []
        channels = {0: 1, 2: 3, 4: 2, 6: 4}[ctype]

        idat = bytearray()
        i = 8
        while i + 8 <= len(data):
            ln = struct.unpack(">I", data[i:i + 4])[0]
            typ = data[i + 4:i + 8]
            if typ == b"IDAT":
                idat += data[i + 8:i + 8 + ln]
            elif typ == b"IEND":
                break
            i += 12 + ln
        raw = zlib.decompress(bytes(idat))

        stride = w * channels
        prev = bytearray(stride)
        out = bytearray()
        pos = 0
        for _ in range(h):
            if pos >= len(raw):
                break
            ft = raw[pos]; pos += 1
            line = bytearray(raw[pos:pos + stride]); pos += stride
            if ft == 1:
                for x in range(channels, stride):
                    line[x] = (line[x] + line[x - channels]) & 0xFF
            elif ft == 2:
                for x in range(stride):
                    line[x] = (line[x] + prev[x]) & 0xFF
            elif ft == 3:
                for x in range(stride):
                    a = line[x - channels] if x >= channels else 0
                    line[x] = (line[x] + ((a + prev[x]) >> 1)) & 0xFF
            elif ft == 4:
                for x in range(stride):
                    a = line[x - channels] if x >= channels else 0
                    b = prev[x]
                    c = prev[x - channels] if x >= channels else 0
                    p = a + b - c
                    pa, pb, pc = abs(p - a), abs(p - b), abs(p - c)
                    pr = a if (pa <= pb and pa <= pc) else (b if pb <= pc else c)
                    line[x] = (line[x] + pr) & 0xFF
            out += line
            prev = line

        cnt = collections.Counter()
        for p in range(0, len(out) - channels + 1, channels):
            if ctype == 2:
                r, g, b, a = out[p], out[p + 1], out[p + 2], 255
            elif ctype == 6:
                r, g, b, a = out[p], out[p + 1], out[p + 2], out[p + 3]
            elif ctype == 0:
                r = g = b = out[p]; a = 255
            else:
                r = g = b = out[p]; a = out[p + 1]
            if a < 16:
                continue  # transparent
            cnt[(r, g, b)] += 1
        return [f"#{r:02x}{g:02x}{b:02x}" for (r, g, b), _ in cnt.most_common(limit)]
    except Exception:
        return []


def colors_for(path: Path, data: bytes):
    ext = path.suffix.lower()
    if ext == ".svg":
        return svg_colors(data)
    if ext == ".png":
        pal = png_palette(data)
        return pal if pal else png_dominant(data)
    return []


# ------------------------------------------------------------------------ main

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--filter", default="", help="only paths containing this substring")
    ap.add_argument("--colors", action="store_true", help="extract colours (slower)")
    ap.add_argument("--out", default="", help="write CSV here instead of stdout")
    args = ap.parse_args()

    tracked = subprocess.run(["git", "ls-files"], capture_output=True, text=True,
                             check=True, cwd=ROOT).stdout.splitlines()
    rows = []
    for rel in tracked:
        p = Path(rel)
        if p.suffix.lower() not in IMAGE_EXT:
            continue
        if args.filter and args.filter not in rel:
            continue
        full = ROOT / rel
        try:
            data = full.read_bytes()
        except OSError:
            continue
        info = dimensions(p, data)
        w, h, mode = info if info else (0, 0, "unknown")
        cols = colors_for(p, data) if args.colors else []
        rows.append((rel, p.suffix.lower().lstrip("."), len(data), w, h, mode, " ".join(cols)))

    lines = ["path,ext,bytes,width,height,color_mode,colors"]
    for r in rows:
        lines.append(",".join(
            f'"{str(x)}"' if ("," in str(x) or '"' in str(x)) else str(x) for x in r))
    text = "\n".join(lines) + "\n"
    if args.out:
        Path(args.out).write_text(text, encoding="utf-8")
        print(f"wrote {len(rows)} rows to {args.out}", file=sys.stderr)
    else:
        sys.stdout.write(text)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
