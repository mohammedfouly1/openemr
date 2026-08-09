#!/usr/bin/env python3
"""
scan-ui-inventory.py — Q72 UI responsiveness + RTL inventory scanner.

Read-only. Enumerates first-party UI files via `git ls-files`, applies the
exclusion rules from the mission spec (§15.1), regex-scans each included file
for grid/responsive/fixed-width/direction-sensitive/RTL/iframe signals, and
emits:
  - <out-json>            per-file JSON with metadata
  - <out-file-list>       one path per line (sha256 of this file = fingerprint)
  - <out-exclusions>      "<path> -- <reason>" per line
  - <out-csv>             17-column CSV (when --emit-csv given, reads out-json)

Usage:
  python scan-ui-inventory.py --root D:/OpenEmr \
      --out-json    docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-output.json \
      --out-file-list  docs/discovery/openemr-decision-evidence/evidence/raw/q72-file-list.txt \
      --out-exclusions docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-exclusions.txt
  python scan-ui-inventory.py --emit-csv \
      --in-json     docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-output.json \
      --out-csv     docs/discovery/openemr-decision-evidence/18-q72-ui-responsiveness-inventory.csv \
      --root D:/OpenEmr
"""
from __future__ import annotations

import argparse
import csv
import hashlib
import json
import os
import re
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path

# ---------------------------------------------------------------------------
# Scope
# ---------------------------------------------------------------------------

INCLUDE_EXTS = {
    ".php", ".phtml", ".html", ".twig", ".mustache",
    ".js", ".jsx", ".ts", ".tsx", ".vue",
    ".scss", ".css",
}

# Directory-prefix exclusions (posix-form). Each entry: (prefix, reason).
DIR_EXCLUSIONS = [
    ("vendor/",                      "third-party composer vendor tree"),
    ("node_modules/",                "third-party npm dependencies"),
    ("public/assets/",               "third-party build output / npm mirror"),
    ("Documentation/EHI_Export/",    "generated ONC EHI export documentation"),
    (".git/",                        "git internals"),
    (".webpack-cache/",              "build cache"),
    ("tmp/",                         "runtime scratch"),
    ("tmp-phpstan/",                 "phpstan scratch"),
    ("docs/",                        "documentation set (out of first-party UI scope; includes our own output under docs/discovery/)"),
    # Bundled third-party JS libraries under library/js/
    ("library/js/jquery-",           "bundled third-party jquery build"),
    ("library/js/knockout-",         "bundled third-party knockout build"),
    ("library/js/bootstrap",         "bundled third-party bootstrap build"),
    ("library/js/summernote-",       "bundled third-party summernote build"),
    ("library/js/select2",           "bundled third-party select2 build"),
    ("library/js/datatables",        "bundled third-party datatables build"),
    ("library/js/moment",            "bundled third-party moment build"),
    ("library/js/chart",             "bundled third-party chart build"),
    ("library/js/backbone",          "bundled third-party backbone build"),
    ("library/js/underscore",        "bundled third-party underscore build"),
    ("library/js/purecss",           "bundled third-party purecss build"),
    ("library/js/dropzone",          "bundled third-party dropzone build"),
    ("library/js/flot",              "bundled third-party flot build"),
    ("library/js/ckeditor",          "bundled third-party ckeditor build"),
    ("library/js/vendors/",          "explicit vendor bucket under library/js/"),
    ("swagger/",                     "bundled swagger-ui third-party assets"),
    # Third-party CSS libraries dropped under Zend module public/
    ("interface/modules/zend_modules/public/css/easyui.css",             "bundled easyui vendor CSS"),
    ("interface/modules/zend_modules/public/css/jquery-ui.css",          "bundled jquery-ui vendor CSS"),
    ("interface/modules/zend_modules/public/css/jquery.contextMenu.css", "bundled jquery.contextMenu vendor CSS"),
    ("interface/modules/zend_modules/public/css/jquery.custom-scrollbar.css", "bundled jquery.custom-scrollbar vendor CSS"),
    ("interface/modules/zend_modules/public/css/jquery.treeview-1.4.1/", "bundled jquery.treeview vendor CSS"),
    ("interface/modules/zend_modules/public/css/slider/",                "bundled slider vendor CSS"),
    ("interface/modules/zend_modules/public/css/multipledb/",            "bundled multipledb vendor CSS"),
    # Built/bundled assets shipped under module public dirs (mirror of top-level public/assets rule)
    ("interface/modules/custom_modules/oe-module-comlink-telehealth/public/assets/", "built/bundled module assets"),
    ("interface/modules/zend_modules/public/js/",                        "bundled zend module JS"),
]

# Substring exclusions (path segment) — apply after prefix + suffix pass
SEGMENT_EXCLUSIONS = [
    ("/dist/",  "path contains /dist/ (built output)"),
    ("/node_modules/", "nested node_modules"),
    ("/vendor/", "nested composer vendor"),
]

# Bundled library/js/dwv/* is third-party per spec, EXCEPT dicom_launcher.js
# which the spec calls out as first-party. Encoded as a dedicated rule.
DWV_PREFIX = "library/js/dwv/"
DWV_KEEP = {"library/js/dwv/dicom_launcher.js"}

# File-suffix exclusions
SUFFIX_EXCLUSIONS = [
    (".min.js",  "third-party minified JS"),
    (".min.css", "third-party minified CSS"),
]

# Ambiguous per spec — INCLUDED. contrib/util is treated as legacy first-party.
# Everything else under contrib/ is included by default unless it has a
# bundled-vendor look; contrib/ is not called out as excluded.

# ---------------------------------------------------------------------------
# Signal patterns (§15.2)
# ---------------------------------------------------------------------------

# Bootstrap grid / utility classes
GRID_RE = re.compile(
    r"\bcol(?:-(?:xs|sm|md|lg|xl))?(?:-(?:auto|\d+))?\b"
    r"|\brow\b"
    r"|\bcontainer(?:-fluid)?\b"
    r"|\bd-(?:none|inline|block|flex|inline-block|inline-flex|table|grid)\b"
    r"|\bflex-(?:row|column|wrap|nowrap|grow|shrink)"
    r"|\bjustify-content-"
    r"|\balign-items-"
    r"|\bg-[0-5]\b"
)

RESPONSIVE_RE = re.compile(
    r"@media"
    r"|col-md-|col-lg-|col-sm-|col-xl-"
    r"|d-md-|d-lg-|d-sm-|d-xl-"
    r"|d-none.*d-"
)

FIXED_WIDTH_RE = re.compile(
    r"<table\s+[^>]*width="
    r"|width:\s*\d+px"
    r"|width=\"\d+\""
    r"|style=\"[^\"]*width:\s*\d+px"
    r"|<img\s+[^>]*width="
)

DIRECTION_RE = re.compile(
    r"margin-left|margin-right"
    r"|padding-left|padding-right"
    r"|text-align:\s*left|text-align:\s*right"
    r"|float:\s*left|float:\s*right"
    r"|left:\s*\d|right:\s*\d"
    r"|border-left|border-right"
)

RTL_RE = re.compile(
    r"dir=\"rtl\"|dir=\"ltr\""
    r"|direction:\s*rtl|direction:\s*ltr"
    r"|\.rtl\b"
    r"|is_rtl|IS_RTL|getRtl"
    r"|bootstrap-rtl"
    r"|rtl_style_pdf"
)

IFRAME_RE = re.compile(
    r"<iframe|document\.write|window\.open|newwindow|frameborder"
)

# For entrypoint/standalone detection
HTML_DOCTYPE_RE = re.compile(r"<html\b|<!doctype\s+html", re.IGNORECASE)
IFRAME_TAG_RE = re.compile(r"<iframe\b", re.IGNORECASE)
IFRAME_SRC_RE = re.compile(r"<iframe[^>]*\bsrc\s*=\s*[\"']([^\"']+)[\"']", re.IGNORECASE)
INCLUDE_STMT_RE = re.compile(
    r"""\b(?:include|include_once|require|require_once)\s*\(?\s*
        (?:__DIR__\s*\.\s*)?
        ["']([^"'?]+)["']""",
    re.VERBOSE,
)

# ---------------------------------------------------------------------------
# Exclusion decision
# ---------------------------------------------------------------------------

def excluded_reason(path_posix: str) -> str | None:
    """Return reason string if path is excluded, else None."""
    # Extension check first
    ext = os.path.splitext(path_posix)[1].lower()
    if ext not in INCLUDE_EXTS:
        return f"extension {ext or '<none>'} not in scope"

    # Suffix exclusions
    for suffix, reason in SUFFIX_EXCLUSIONS:
        if path_posix.endswith(suffix):
            return reason

    # dwv special-case
    if path_posix.startswith(DWV_PREFIX):
        if path_posix in DWV_KEEP:
            return None  # kept as first-party per spec
        return "bundled third-party dwv library (spec keeps only dicom_launcher.js)"

    # Directory prefix exclusions
    for prefix, reason in DIR_EXCLUSIONS:
        if path_posix.startswith(prefix):
            return reason

    # Segment substring exclusions
    for seg, reason in SEGMENT_EXCLUSIONS:
        if seg in ("/" + path_posix):
            return reason

    return None


# ---------------------------------------------------------------------------
# Classification (§15.3)
# ---------------------------------------------------------------------------

def classify(path_posix: str, text: str, iframe_count: int) -> tuple[str, str, bool, str]:
    """
    Return (classification, confidence, manual_review_required, notes).
    """
    p = path_posix
    notes_bits: list[str] = []
    manual = False

    # Modern shell (Twig)
    if p == "interface/main/tabs/main.php":
        return ("modern_shell", "HIGH", False, "main shell (Twig+Knockout)")
    if p.startswith("interface/main/tabs/menu/"):
        return ("modern_shell", "HIGH", False, "menu shell / menu partial")

    # Twig templates
    if p.endswith(".twig") and p.startswith("templates/"):
        # heuristic: layout/*.twig or ones referenced as base templates → shell; else partial
        if "/layout/" in p or p.endswith("/base.twig") or "/base/" in p:
            return ("modern_shell", "MEDIUM", False, "twig base/layout template")
        return ("shared_template", "HIGH", False, "twig template partial")

    if p.endswith(".twig"):
        # Twig outside templates/
        return ("shared_template", "MEDIUM", False, "twig template outside templates/")

    # Patient portal
    if p.startswith("portal/"):
        return ("patient_portal", "HIGH", False, "patient portal file")

    # Custom modules
    if p.startswith("interface/modules/custom_modules/oe-module-"):
        return ("custom_module_screen", "HIGH", False,
                "custom module: " + p.split("/")[3])

    # Non-custom modules (zend_modules, custom_modules other, etc.) → treat as shared/module
    if p.startswith("interface/modules/"):
        return ("custom_module_screen", "MEDIUM", False, "interface/modules subtree")

    # Administration screens
    if p.startswith("interface/usergroup/") or p.startswith("interface/super/") \
       or p.startswith("interface/main/administration/"):
        return ("administration_screen", "HIGH", False, "admin screen")

    # Legacy iframe entrypoints
    if p == "interface/main/main_screen.php":
        return ("legacy_iframe_entrypoint", "HIGH", False, "main iframe host")
    if p.startswith("interface/main/tabs/") and p.endswith(".php"):
        # main.php was handled above; other tabs/*.php are iframe orchestration
        if iframe_count >= 1:
            return ("legacy_iframe_entrypoint", "HIGH", False,
                    f"under interface/main/tabs/ with {iframe_count} iframe tags")
        return ("legacy_iframe_entrypoint", "MEDIUM", False,
                "under interface/main/tabs/")

    if iframe_count >= 2:
        return ("legacy_iframe_entrypoint", "MEDIUM", False,
                f"contains {iframe_count} iframe tags")

    # Standalone / included legacy PHP under interface/
    if p.startswith("interface/") and p.endswith(".php"):
        has_html_doctype = bool(HTML_DOCTYPE_RE.search(text))
        if has_html_doctype:
            return ("legacy_standalone_page", "HIGH", False,
                    "interface/ php with <html> doctype")
        # Heuristic: starts with <?php and echoes HTML → included fragment
        starts_php = text.lstrip().startswith("<?php")
        has_echo_html = "<" in text and ("echo" in text or "print " in text or "?>" in text)
        if starts_php and has_echo_html:
            return ("legacy_iframe_included_file", "LOW", True,
                    "heuristic: php fragment under interface/, no <html>; manual review recommended")
        return ("legacy_iframe_included_file", "LOW", True,
                "interface/ php with no clear html/echo markers")

    # CSS / SCSS
    if p.endswith((".css", ".scss")):
        return ("shared_template", "MEDIUM", False, "stylesheet")

    # JS
    if p.endswith((".js", ".jsx", ".ts", ".tsx", ".vue")):
        return ("shared_template", "MEDIUM", False, "javascript / frontend module")

    # library/ php
    if p.startswith("library/") and p.endswith(".php"):
        return ("shared_template", "MEDIUM", False, "library php helper")

    # ccdaservice / ccr / anything else
    return ("unknown", "LOW", True, "unclassified — needs manual review")


# ---------------------------------------------------------------------------
# Scan
# ---------------------------------------------------------------------------

def scan_matches(pattern: re.Pattern, text_lines: list[str]) -> tuple[int, list[int]]:
    """Return (count, list-of-1-based-line-numbers). count = # matches (not lines)."""
    total = 0
    hits: list[int] = []
    for i, line in enumerate(text_lines, start=1):
        m = pattern.findall(line)
        if m:
            total += len(m)
            hits.append(i)
    return total, hits


def read_text(path: Path) -> str:
    try:
        with open(path, "r", encoding="utf-8", errors="replace") as f:
            return f.read()
    except (OSError, PermissionError) as e:
        return ""


def git_ls_files(root: Path) -> list[str]:
    out = subprocess.check_output(
        ["git", "ls-files"], cwd=str(root), text=True, encoding="utf-8"
    )
    return [ln.strip() for ln in out.splitlines() if ln.strip()]


def sha256_file(path: Path) -> str:
    h = hashlib.sha256()
    with open(path, "rb") as f:
        for chunk in iter(lambda: f.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


# ---------------------------------------------------------------------------
# Cross-file relations (best-effort)
# ---------------------------------------------------------------------------

def build_include_index(root: Path, included_paths: list[str]) -> tuple[dict[str, list[str]], dict[str, list[str]]]:
    """
    Best-effort:
      included_by[target] = [list of files that include/require target by exact tail-match]
      iframe_launcher[target] = [files with <iframe src=... target ...>]
    """
    # Precompute basenames of each included file
    tail_index: dict[str, list[str]] = {}
    for p in included_paths:
        # Index by last 2 components ("dir/file") and by basename
        parts = p.split("/")
        tail_index.setdefault(parts[-1], []).append(p)
        if len(parts) >= 2:
            tail_index.setdefault("/".join(parts[-2:]), []).append(p)

    included_by: dict[str, list[str]] = {p: [] for p in included_paths}
    iframe_by: dict[str, list[str]] = {p: [] for p in included_paths}

    for src in included_paths:
        # Only PHP + twig + html are likely to contain include/iframe
        if not src.endswith((".php", ".twig", ".html", ".phtml", ".mustache")):
            continue
        text = read_text(root / src)
        if not text:
            continue

        for m in INCLUDE_STMT_RE.finditer(text):
            target = m.group(1).replace("\\", "/")
            # Strip leading ./ and $var interpolation looks — only literal
            for key in (target, target.split("/")[-1]):
                if key in tail_index:
                    for cand in tail_index[key]:
                        if cand != src and src not in included_by[cand]:
                            included_by[cand].append(src)
                    break

        for m in IFRAME_SRC_RE.finditer(text):
            target = m.group(1).split("?")[0].split("#")[0].replace("\\", "/")
            base = target.split("/")[-1]
            if base in tail_index:
                for cand in tail_index[base]:
                    if cand != src and src not in iframe_by[cand]:
                        iframe_by[cand].append(src)

    return included_by, iframe_by


# ---------------------------------------------------------------------------
# Menu labels
# ---------------------------------------------------------------------------

def build_menu_index(root: Path) -> dict[str, str]:
    """
    Return {url_target -> label} extracted from
    interface/main/tabs/menu/menus/*.json (recursive).
    """
    menu_root = root / "interface" / "main" / "tabs" / "menu" / "menus"
    if not menu_root.is_dir():
        return {}
    result: dict[str, str] = {}

    def walk(node, current_label: str = ""):
        if isinstance(node, list):
            for x in node:
                walk(x, current_label)
        elif isinstance(node, dict):
            label = node.get("label") or current_label
            url = node.get("url")
            if isinstance(url, str) and url:
                # Normalise: strip leading /, query string
                u = url.lstrip("/").split("?")[0]
                result.setdefault(u, label or "")
            for v in node.values():
                if isinstance(v, (list, dict)):
                    walk(v, label)

    for jf in menu_root.rglob("*.json"):
        try:
            with open(jf, "r", encoding="utf-8") as f:
                data = json.load(f)
            walk(data)
        except (json.JSONDecodeError, OSError):
            continue
    return result


# ---------------------------------------------------------------------------
# Risk scoring
# ---------------------------------------------------------------------------

def mobile_risk(fixed: int, grid: int, classification: str) -> str:
    if fixed >= 3 and grid == 0:
        return "HIGH"
    if fixed >= 1 or (grid == 0 and classification == "legacy_standalone_page"):
        return "MEDIUM"
    return "LOW"


def rtl_risk(direction: int, rtl: int) -> str:
    if direction >= 5 and rtl == 0:
        return "HIGH"
    if direction >= 1 and rtl == 0:
        return "MEDIUM"
    return "LOW"


# ---------------------------------------------------------------------------
# Main scan
# ---------------------------------------------------------------------------

def do_scan(args) -> None:
    root = Path(args.root).resolve()
    print(f"[scan] root = {root}", file=sys.stderr)

    all_tracked = git_ls_files(root)
    print(f"[scan] git ls-files: {len(all_tracked)} tracked paths", file=sys.stderr)

    included: list[str] = []
    excluded: list[tuple[str, str]] = []

    for p in all_tracked:
        posix = p.replace("\\", "/")
        reason = excluded_reason(posix)
        if reason is None:
            included.append(posix)
        else:
            # Only record exclusions that WERE in-scope by extension —
            # otherwise the exclusion file explodes with every .md/.json.
            ext = os.path.splitext(posix)[1].lower()
            if ext in INCLUDE_EXTS or reason.startswith("bundled") \
               or reason.startswith("third-party") or reason.startswith("generated") \
               or reason.startswith("build") or reason.startswith("runtime") \
               or "vendor" in reason or reason.startswith("documentation"):
                excluded.append((posix, reason))

    print(f"[scan] included={len(included)}  excluded(in-scope-ext)={len(excluded)}",
          file=sys.stderr)

    # Emit exclusions
    excl_path = Path(args.out_exclusions)
    excl_path.parent.mkdir(parents=True, exist_ok=True)
    with open(excl_path, "w", encoding="utf-8", newline="\n") as f:
        f.write(f"# Q72 scanner exclusions — generated {datetime.now(timezone.utc).isoformat()}\n")
        f.write(f"# Total excluded (in-scope extension): {len(excluded)}\n")
        f.write("# Format: <path> -- <reason>\n\n")
        for path, reason in sorted(excluded):
            f.write(f"{path} -- {reason}\n")

    # Emit file list
    fl_path = Path(args.out_file_list)
    fl_path.parent.mkdir(parents=True, exist_ok=True)
    with open(fl_path, "w", encoding="utf-8", newline="\n") as f:
        for p in sorted(included):
            f.write(p + "\n")
    file_list_sha = sha256_file(fl_path)
    print(f"[scan] file-list sha256 = {file_list_sha}", file=sys.stderr)

    # Menu label + include/iframe cross-index
    menu_labels = build_menu_index(root)
    print(f"[scan] menu index entries: {len(menu_labels)}", file=sys.stderr)
    included_by, iframe_by = build_include_index(root, included)
    print(f"[scan] cross-file relations computed", file=sys.stderr)

    # Scan each file
    files_out: list[dict] = []
    for i, rel in enumerate(sorted(included)):
        if i and i % 500 == 0:
            print(f"[scan]   {i}/{len(included)}", file=sys.stderr)
        text = read_text(root / rel)
        lines = text.splitlines()

        grid_c, grid_l         = scan_matches(GRID_RE,         lines)
        resp_c, resp_l         = scan_matches(RESPONSIVE_RE,   lines)
        fw_c, fw_l             = scan_matches(FIXED_WIDTH_RE,  lines)
        dir_c, dir_l           = scan_matches(DIRECTION_RE,    lines)
        rtl_c, rtl_l           = scan_matches(RTL_RE,          lines)
        ifr_c, ifr_l           = scan_matches(IFRAME_RE,       lines)
        iframe_tag_count = len(IFRAME_TAG_RE.findall(text))

        classification, confidence, manual, class_notes = classify(rel, text, iframe_tag_count)

        # Compose notes
        note_bits = [class_notes]
        launcher = ""
        if rel in iframe_by and iframe_by[rel]:
            launcher = iframe_by[rel][0]
        # Menu label
        if rel in menu_labels and menu_labels[rel]:
            note_bits.append(f"menu-label={menu_labels[rel]}")

        files_out.append({
            "path": rel,
            "extension": os.path.splitext(rel)[1].lower(),
            "line_count": len(lines),
            "iframe_tag_count": iframe_tag_count,
            "counts": {
                "grid":       grid_c,
                "responsive": resp_c,
                "fixed_width": fw_c,
                "direction":  dir_c,
                "rtl":        rtl_c,
                "iframe":     ifr_c,
            },
            "lines": {
                "grid":       grid_l,
                "responsive": resp_l,
                "fixed_width": fw_l,
                "direction":  dir_l,
                "rtl":        rtl_l,
                "iframe":     ifr_l,
            },
            "classification": classification,
            "confidence": confidence,
            "manual_review_required": manual,
            "included_by":  included_by.get(rel, []),
            "iframe_launcher": launcher,
            "notes": "; ".join(b for b in note_bits if b),
        })

    # Emit JSON
    js_path = Path(args.out_json)
    js_path.parent.mkdir(parents=True, exist_ok=True)
    meta = {
        "generated_at":  datetime.now(timezone.utc).isoformat(),
        "root":          str(root),
        "fork_sha":      "631f2b38cf633769c305233f88cdf9c73ca80657",
        "tracked_file_count":         len(all_tracked),
        "included_file_count":        len(included),
        "excluded_in_scope_ext_count": len(excluded),
        "file_list_sha256": file_list_sha,
        "signal_patterns": {
            "grid":         GRID_RE.pattern,
            "responsive":   RESPONSIVE_RE.pattern,
            "fixed_width":  FIXED_WIDTH_RE.pattern,
            "direction":    DIRECTION_RE.pattern,
            "rtl":          RTL_RE.pattern,
            "iframe":       IFRAME_RE.pattern,
        },
    }
    with open(js_path, "w", encoding="utf-8", newline="\n") as f:
        json.dump({"metadata": meta, "files": files_out}, f, indent=2)

    print(f"[scan] JSON written: {js_path}", file=sys.stderr)
    print(f"[scan] done", file=sys.stderr)


# ---------------------------------------------------------------------------
# CSV emission
# ---------------------------------------------------------------------------

CSV_COLUMNS = [
    "path", "extension", "first_party", "entrypoint", "included_by",
    "iframe_or_tab_launcher", "classification", "classification_confidence",
    "grid_patterns", "responsive_patterns", "fixed_width_patterns",
    "direction_sensitive_patterns", "rtl_support", "mobile_risk", "rtl_risk",
    "matched_line_numbers", "manual_review_required", "notes",
]

ENTRYPOINT_CLASSES = {
    "legacy_iframe_entrypoint", "modern_shell", "legacy_standalone_page",
    "custom_module_screen", "patient_portal", "administration_screen",
}


def truncate_lines(lst: list[int], cap: int = 20):
    if len(lst) <= cap:
        return lst
    return lst[:cap] + ["..."]


def do_emit_csv(args) -> None:
    with open(args.in_json, "r", encoding="utf-8") as f:
        data = json.load(f)
    files = data["files"]

    out_path = Path(args.out_csv)
    out_path.parent.mkdir(parents=True, exist_ok=True)
    with open(out_path, "w", encoding="utf-8", newline="") as f:
        w = csv.writer(f, quoting=csv.QUOTE_MINIMAL, lineterminator="\n")
        w.writerow(CSV_COLUMNS)
        for row in files:
            classification = row["classification"]
            counts = row["counts"]
            lines = row["lines"]

            mr = mobile_risk(counts["fixed_width"], counts["grid"], classification)
            rr = rtl_risk(counts["direction"], counts["rtl"])
            entry = classification in ENTRYPOINT_CLASSES

            matched = {
                "grid":        truncate_lines(lines["grid"]),
                "fixed_width": truncate_lines(lines["fixed_width"]),
                "direction":   truncate_lines(lines["direction"]),
            }

            w.writerow([
                row["path"],
                row["extension"],
                "TRUE",
                "TRUE" if entry else "FALSE",
                ";".join(row.get("included_by", [])),
                row.get("iframe_launcher", ""),
                classification,
                row["confidence"],
                counts["grid"],
                counts["responsive"],
                counts["fixed_width"],
                counts["direction"],
                counts["rtl"],
                mr,
                rr,
                json.dumps(matched, separators=(",", ":")),
                "TRUE" if row["manual_review_required"] else "FALSE",
                row["notes"],
            ])
    print(f"[csv] wrote {out_path} with {len(files)} rows", file=sys.stderr)


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default="D:/OpenEmr")
    ap.add_argument("--out-json")
    ap.add_argument("--out-file-list")
    ap.add_argument("--out-exclusions")
    ap.add_argument("--emit-csv", action="store_true")
    ap.add_argument("--in-json")
    ap.add_argument("--out-csv")
    args = ap.parse_args()

    if args.emit_csv:
        if not args.in_json or not args.out_csv:
            ap.error("--emit-csv requires --in-json and --out-csv")
        do_emit_csv(args)
    else:
        if not (args.out_json and args.out_file_list and args.out_exclusions):
            ap.error("scan mode requires --out-json, --out-file-list, --out-exclusions")
        do_scan(args)
    return 0


if __name__ == "__main__":
    sys.exit(main())
