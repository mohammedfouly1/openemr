#!/usr/bin/env python3
"""
Build 07-core-modification-inventory.csv from pre-computed raw evidence.

Reads:
  evidence/raw/diff-HEAD-vs-upstream-master.txt   (name-status)
  evidence/raw/numstat-HEAD-vs-upstream-master.txt
  evidence/raw/ls-tree-HEAD.txt
  evidence/raw/ls-tree-upstream-master.txt

Writes:
  docs/discovery/openemr-decision-evidence/07-core-modification-inventory.csv

Framing note (per §5 mission spec):
  merge-base(HEAD, upstream/master) == HEAD, so the fork is an ancestor of
  upstream/master. Every path listed here is UPSTREAM advancing forward, NOT
  a fork-only modification. Therefore `likely_core_modification` = FALSE for
  every row.
"""
from __future__ import annotations

import csv
import re
import subprocess
from pathlib import Path

ROOT = Path(r"D:\OpenEmr")
RAW = ROOT / "docs" / "discovery" / "openemr-decision-evidence" / "evidence" / "raw"
OUT_CSV = ROOT / "docs" / "discovery" / "openemr-decision-evidence" / "07-core-modification-inventory.csv"

NAME_STATUS = RAW / "diff-HEAD-vs-upstream-master.txt"
NUMSTAT = RAW / "numstat-HEAD-vs-upstream-master.txt"
LSTREE_FORK = RAW / "ls-tree-HEAD.txt"
LSTREE_UPSTREAM = RAW / "ls-tree-upstream-master.txt"


def parse_ls_tree(path: Path) -> dict[str, str]:
    """Return {path: blob_sha}. Format: '<mode> blob <sha>\\t<path>'."""
    out: dict[str, str] = {}
    with path.open(encoding="utf-8") as fh:
        for line in fh:
            line = line.rstrip("\n")
            if not line:
                continue
            # e.g. "100644 blob abc123\tsome/path"
            m = re.match(r"^\S+\s+\S+\s+(\S+)\t(.+)$", line)
            if m:
                out[m.group(2)] = m.group(1)
    return out


def parse_numstat(path: Path) -> dict[str, tuple[str, str]]:
    """Return {path: (added, deleted)}. Binary files use '-'."""
    out: dict[str, tuple[str, str]] = {}
    with path.open(encoding="utf-8") as fh:
        for line in fh:
            line = line.rstrip("\n")
            if not line:
                continue
            parts = line.split("\t", 2)
            if len(parts) != 3:
                continue
            added, deleted, p = parts
            # Rename form: "path1 => path2" -> keep the destination side
            if " => " in p:
                # Formal rename numstat form: "{old => new}" possibly inside a dir
                # Simplify: parse to old and new.
                # e.g. "dir/{old.txt => new.txt}"
                mm = re.match(r"^(.*)\{(.*?) => (.*?)\}(.*)$", p)
                if mm:
                    new_path = mm.group(1) + mm.group(3) + mm.group(4)
                    p = new_path
                else:
                    p = p.split(" => ")[-1]
            out[p] = (added, deleted)
    return out


def parse_name_status(path: Path) -> list[tuple[str, str]]:
    """Return list of (change_type, path). R paths become one row for the new path."""
    rows: list[tuple[str, str]] = []
    with path.open(encoding="utf-8-sig") as fh:
        for line in fh:
            line = line.rstrip("\n").lstrip("\ufeff")
            if not line:
                continue
            parts = line.split("\t")
            status = parts[0]
            if status.startswith("R"):
                # R<score>\t<old>\t<new>
                new_path = parts[2] if len(parts) >= 3 else parts[-1]
                rows.append((status, new_path))
            else:
                rows.append((status, parts[1]))
    return rows


def classify(p: str) -> str:
    # More specific patterns first
    if p.startswith("interface/modules/custom_modules/oe-module-"):
        return "custom_module"
    if p.startswith("interface/modules/zend_modules/"):
        return "custom_module"
    if p.startswith("sites/"):
        return "custom_module"
    if p.startswith("custom/"):
        return "custom_module"
    if p in ("composer.lock", "package-lock.json"):
        return "dependency_lock_change"
    if (
        p.startswith(".github/")
        or p.startswith(".codespell")
        or p in (".gitattributes",)
        or p.startswith(".composer-")
    ):
        # Per mission spec: fork_configuration UNLESS upstream-only (then upstream_unmodified).
        # Since HEAD is ancestor of upstream/master, every changed file here is upstream
        # advancing forward -> upstream_unmodified is the accurate label. Spec however says
        # ".github/... -> fork_configuration unless upstream-only". These ARE upstream-only,
        # so classify as upstream_unmodified.
        return "upstream_unmodified"
    core_prefixes = (
        "sql/", "src/", "library/", "interface/", "apis/", "oauth2/",
        "templates/", "portal/", "ccdaservice/", "public/", "gacl/",
    )
    if any(p.startswith(pref) for pref in core_prefixes):
        return "upstream_unmodified"
    # Top-level PHP/config files that ship with OpenEMR
    if "/" not in p:
        # composer.json, package.json, phpunit.xml, README.md etc are core files;
        # since HEAD is an ancestor of upstream/master, changes are upstream-driven.
        return "upstream_unmodified"
    if p.startswith((
        "docker/", "ci/", "contrib/", "tests/", "vendor/", "Documentation/",
        ".phpstan/", "ccr/", "config/", "controllers/", "docs/", "tools/",
        "myportal/", "phpmyadmin/", "swagger/", "acknowledge/", "modules/",
        "Documentation", "images/", "themes/",
    )):
        return "upstream_unmodified"
    return "unknown"


def risk(classification: str, change_type: str) -> str:
    if classification in ("dependency_lock_change",):
        return "MEDIUM"
    # SQL migrations = schema
    return "LOW"


def manual_review(classification: str, change_type: str, p: str) -> str:
    if classification in ("unknown", "custom_module"):
        return "TRUE"
    if p.startswith("sql/"):
        return "TRUE"
    return "FALSE"


def git_log_reason(p: str) -> str:
    """Return newest commit subject touching path in HEAD..upstream/master."""
    try:
        r = subprocess.run(
            ["git", "-C", str(ROOT), "log", "--oneline", "-1",
             "HEAD..upstream/master", "--", p],
            capture_output=True, text=True, check=False, timeout=15,
        )
        line = r.stdout.strip().splitlines()[0] if r.stdout.strip() else ""
        # strip leading short-sha
        if line:
            parts = line.split(" ", 1)
            return parts[1] if len(parts) > 1 else ""
        return ""
    except Exception:
        return ""


def extract_pr(subject: str) -> str:
    m = re.search(r"#(\d+)", subject)
    return f"#{m.group(1)}" if m else ""


def main() -> None:
    fork_blobs = parse_ls_tree(LSTREE_FORK)
    up_blobs = parse_ls_tree(LSTREE_UPSTREAM)
    numstat = parse_numstat(NUMSTAT)
    entries = parse_name_status(NAME_STATUS)

    # Sample paths for `reason_if_detectable` (spec says 30-path sample).
    # Deterministic: every Nth entry to spread across areas.
    step = max(1, len(entries) // 30)
    sample_idx = {i for i in range(0, len(entries), step)}
    while len(sample_idx) < 30 and len(sample_idx) < len(entries):
        sample_idx.add(len(sample_idx))

    rows_out = []
    for i, (status, p) in enumerate(entries):
        added, deleted = numstat.get(p, ("", ""))
        fork_sha = fork_blobs.get(p, "")
        up_sha = up_blobs.get(p, "")
        cls = classify(p)
        owner = "upstream"  # ancestor claim -> everything is upstream-owned change
        reason = ""
        pr = ""
        if i in sample_idx:
            reason = git_log_reason(p)
            pr = extract_pr(reason)
        rows_out.append({
            "path": p,
            "change_type": status,
            "added_lines": added,
            "deleted_lines": deleted,
            "fork_blob_sha": fork_sha,
            "upstream_blob_sha": up_sha,
            "classification": cls,
            "likely_core_modification": "FALSE",
            "owner_if_detectable": owner,
            "upstream_pr_if_detectable": pr,
            "reason_if_detectable": reason,
            "risk_on_upgrade": risk(cls, status),
            "manual_review_required": manual_review(cls, status, p),
        })

    fieldnames = [
        "path", "change_type", "added_lines", "deleted_lines",
        "fork_blob_sha", "upstream_blob_sha", "classification",
        "likely_core_modification", "owner_if_detectable",
        "upstream_pr_if_detectable", "reason_if_detectable",
        "risk_on_upgrade", "manual_review_required",
    ]
    with OUT_CSV.open("w", encoding="utf-8", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=fieldnames)
        w.writeheader()
        w.writerows(rows_out)

    # Also print classification breakdown for the .md
    from collections import Counter
    c = Counter(r["classification"] for r in rows_out)
    print(f"rows: {len(rows_out)}")
    for k, v in sorted(c.items(), key=lambda kv: -kv[1]):
        print(f"  {k}: {v}")


if __name__ == "__main__":
    main()
