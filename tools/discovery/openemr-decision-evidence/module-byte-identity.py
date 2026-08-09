#!/usr/bin/env python3
"""Q36 — module byte-identity comparator.

Reads three git-ls-tree manifests (fork HEAD, upstream v8_2_0, upstream master)
scoped to interface/modules/custom_modules/, groups blobs by module directory
(depth-1 segment under that path), and emits a per-module CSV of set/blob-SHA
differences.

READ-ONLY. Does no git calls itself — consumes pre-generated manifests.
"""
from __future__ import annotations

import csv
import json
import sys
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
MANI_DIR = ROOT / "docs" / "discovery" / "openemr-decision-evidence" / "evidence" / "manifests"
OUT_CSV = ROOT / "docs" / "discovery" / "openemr-decision-evidence" / "06-module-drift-inventory.csv"
RAW_DIR = ROOT / "docs" / "discovery" / "openemr-decision-evidence" / "evidence" / "raw"
MODULE_ROOT = "interface/modules/custom_modules/"


def parse_manifest(path: Path) -> dict[str, tuple[str, str]]:
    """Return {relpath: (objtype, sha)} for every blob/symlink line."""
    out: dict[str, tuple[str, str]] = {}
    raw = path.read_bytes()
    if raw.startswith(b"\xff\xfe") or raw.startswith(b"\xfe\xff"):
        text = raw.decode("utf-16")
    elif raw.startswith(b"\xef\xbb\xbf"):
        text = raw.decode("utf-8-sig")
    else:
        text = raw.decode("utf-8", errors="replace")
    for line in text.splitlines():
        if not line.strip():
            continue
        # Format: "<objtype> <sha> <path>"
        parts = line.split(" ", 2)
        if len(parts) != 3:
            continue
        objtype, sha, relpath = parts
        out[relpath] = (objtype, sha)
    return out


def group_by_module(entries: dict[str, tuple[str, str]]) -> dict[str, dict[str, tuple[str, str]]]:
    """Group entries by depth-1 dir segment under MODULE_ROOT. Files directly
    in MODULE_ROOT (e.g. README.md) go under module '' (root)."""
    groups: dict[str, dict[str, tuple[str, str]]] = defaultdict(dict)
    for relpath, meta in entries.items():
        if not relpath.startswith(MODULE_ROOT):
            continue
        rest = relpath[len(MODULE_ROOT):]
        if "/" not in rest:
            # File directly at MODULE_ROOT
            groups[""][relpath] = meta
        else:
            mod = rest.split("/", 1)[0]
            groups[mod][relpath] = meta
    return groups


def main() -> int:
    fork = parse_manifest(MANI_DIR / "fork-module-blobs.txt")
    stable = parse_manifest(MANI_DIR / "upstream-module-blobs-v8_2_0.txt")
    master = parse_manifest(MANI_DIR / "upstream-module-blobs-master.txt")

    g_fork = group_by_module(fork)
    g_stable = group_by_module(stable)
    g_master = group_by_module(master)

    # Composer info (hard-coded from composer.json:52 / composer.lock:426-431 inspection).
    # oe-module-claimrev-connect: gitignored (.gitignore:15) — dropped in by
    # openemr/oe-module-installer-plugin at composer install time. It exists on
    # disk in this fork (verified via Get-ChildItem) but is NOT in any tracked tree.
    composer_pkgs = {
        "oe-module-claimrev-connect": ("claimrevolution/oe-module-claimrev-connect", "v2.1.6"),
    }
    # Ensure composer-only modules appear in output even when absent from every
    # git tree (they will be classified as composer_installed_only).
    all_modules = sorted(set(g_fork) | set(g_stable) | set(g_master) | set(composer_pkgs))
    # composer.json declares openemr/oe-module-installer-plugin (plugin, not a
    # module dir) and repository entry for openemr/oe-module-cqm (not required).

    RAW_DIR.mkdir(parents=True, exist_ok=True)
    per_module_detail: dict[str, dict] = {}

    with OUT_CSV.open("w", newline="", encoding="utf-8") as fh:
        w = csv.writer(fh, lineterminator="\n")
        w.writerow([
            "module", "module_path",
            "exists_in_fork", "exists_in_upstream_stable", "exists_in_upstream_master",
            "tracked_in_fork", "composer_package", "composer_version",
            "byte_identical_to_stable", "byte_identical_to_master",
            "added_files", "deleted_files", "modified_files", "untracked_files",
            "classification", "recommended_ownership", "confidence",
        ])

        for mod in all_modules:
            display = mod if mod else "(root-files)"
            path = MODULE_ROOT + mod if mod else MODULE_ROOT
            f = g_fork.get(mod, {})
            s = g_stable.get(mod, {})
            m = g_master.get(mod, {})

            in_fork = bool(f)
            in_stable = bool(s)
            in_master = bool(m)

            # Compare fork vs stable
            f_paths = set(f)
            s_paths = set(s)
            m_paths = set(m)

            added_vs_stable = sorted(f_paths - s_paths)
            deleted_vs_stable = sorted(s_paths - f_paths)
            common_stable = f_paths & s_paths
            modified_vs_stable = sorted(p for p in common_stable if f[p][1] != s[p][1])

            added_vs_master = sorted(f_paths - m_paths)
            deleted_vs_master = sorted(m_paths - f_paths)
            common_master = f_paths & m_paths
            modified_vs_master = sorted(p for p in common_master if f[p][1] != m[p][1])

            byte_id_stable = (
                in_fork and in_stable
                and not added_vs_stable and not deleted_vs_stable and not modified_vs_stable
            )
            byte_id_master = (
                in_fork and in_master
                and not added_vs_master and not deleted_vs_master and not modified_vs_master
            )

            pkg, ver = composer_pkgs.get(mod, ("", ""))

            # Classification
            if not in_fork and not in_stable and not in_master and pkg:
                classification = "composer_installed_only"
                ownership = "composer"
            elif in_fork and in_stable and byte_id_stable:
                classification = "upstream_bundled"
                ownership = "upstream"
            elif in_fork and in_stable and not byte_id_stable:
                classification = "upstream_bundled_modified"
                ownership = "mixed"
            elif in_fork and not in_stable and not in_master:
                classification = "fork_only"
                ownership = "fork"
            elif in_fork and not in_stable and in_master:
                # In fork + master but not stable: added upstream after tag
                classification = "upstream_bundled"
                ownership = "upstream"
            elif not in_fork and (in_stable or in_master):
                classification = "upstream_only"
                ownership = "upstream"
            else:
                classification = "unknown"
                ownership = "UNKNOWN"

            if pkg and classification != "composer_installed_only":
                ownership = "composer" if ownership == "upstream" else "mixed"

            # Confidence: git object identity is authoritative
            confidence = "CONFIRMED"

            w.writerow([
                display, path,
                str(in_fork).upper(), str(in_stable).upper(), str(in_master).upper(),
                "TRUE" if in_fork else "FALSE",
                pkg, ver,
                "TRUE" if byte_id_stable else "FALSE",
                "TRUE" if byte_id_master else "FALSE",
                len(added_vs_stable), len(deleted_vs_stable), len(modified_vs_stable),
                0,  # untracked_files — none per git status
                classification, ownership, confidence,
            ])

            per_module_detail[display] = {
                "path": path,
                "counts": {"fork": len(f), "stable": len(s), "master": len(m)},
                "vs_stable": {
                    "added": added_vs_stable,
                    "deleted": deleted_vs_stable,
                    "modified": modified_vs_stable,
                    "byte_identical": byte_id_stable,
                },
                "vs_master": {
                    "added": added_vs_master,
                    "deleted": deleted_vs_master,
                    "modified": modified_vs_master,
                    "byte_identical": byte_id_master,
                },
                "in_fork": in_fork,
                "in_stable": in_stable,
                "in_master": in_master,
                "composer_pkg": pkg,
                "composer_ver": ver,
                "classification": classification,
            }

    (RAW_DIR / "module-detail.json").write_text(
        json.dumps(per_module_detail, indent=2), encoding="utf-8"
    )
    print(f"Wrote {OUT_CSV} ({len(all_modules)} module rows)")
    print(f"Wrote {RAW_DIR / 'module-detail.json'}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
