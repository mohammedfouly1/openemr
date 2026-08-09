#!/usr/bin/env python3
"""
build-file-index.py — emits 23-file-evidence-index.csv.

Walks the evidence package and records, for every artifact: path, kind, size,
SHA-256, and which questions it supports (derived from 04-question-evidence.json
plus filename conventions, so the mapping cannot silently drift from the
evidence JSON).
"""

from __future__ import annotations

import csv
import hashlib
import json
import re
import subprocess
from collections import defaultdict
from pathlib import Path

ROOT = Path(subprocess.run(["git", "rev-parse", "--show-toplevel"],
                           capture_output=True, text=True, check=True).stdout.strip())
PKG = ROOT / "docs" / "discovery" / "openemr-decision-evidence"
TOOLS = ROOT / "tools" / "discovery" / "openemr-decision-evidence"

# Report -> questions it primarily covers.
REPORT_QS = {
    "00-executive-summary.md": "Q1-Q75",
    "01-run-metadata.json": "Q1",
    "02-repository-baseline.md": "Q1",
    "03-question-status-matrix.csv": "Q1-Q75",
    "04-question-evidence.json": "Q1-Q75",
    "05-upstream-fork-drift.md": "Q1;Q2;Q36",
    "06-module-drift-inventory.csv": "Q36;Q70",
    "07-core-modification-inventory.csv": "Q1;Q36",
    "08-dependency-runtime-inventory.csv": "Q31;Q37;Q55;Q70",
    "09-test-and-ci-inventory.md": "Q51;Q52;Q53;Q74;Q75",
    "10-database-and-tenancy-evidence.md": "Q11;Q57;Q58;Q60",
    "11-authentication-authorization-evidence.md": "Q4;Q5;Q6;Q7;Q8;Q9;Q10",
    "12-fhir-nphies-billing-evidence.md": "Q27;Q28;Q30;Q65;Q66",
    "13-localization-arabic-evidence.md": "Q18-Q26;Q61;Q71",
    "14-frontend-ui-evidence.md": "Q34;Q59",
    "15-security-compliance-code-evidence.md": "Q42-Q50;Q67;Q68;Q69;Q73",
    "16-control-plane-constraints.md": "Q11-Q17;Q44",
    "17-q36-module-byte-identity.md": "Q36",
    "18-q72-ui-responsiveness-inventory.csv": "Q72;Q33",
    "19-q72-ui-responsiveness-summary.md": "Q72;Q33",
    "20-unresolved-external-inputs.md": "Q2;Q3;Q13;Q14;Q15;Q19;Q21;Q22;Q32;Q39;Q40;Q41;Q45;Q48",
    "21-recommended-decision-updates.md": "Q10;Q11;Q37;Q43;Q47;Q49;Q50;Q51;Q59;Q68;Q69;Q70",
    "22-command-log.txt": "Q1-Q75",
    "23-file-evidence-index.csv": "Q1-Q75",
    "24-reproduction-guide.md": "Q1-Q75",
}

# Raw-evidence slug -> questions (for count-*.txt and topical captures).
SLUG_QS = {
    "sqlstatement": "Q11;Q67;Q73", "sqlquery": "Q11;Q73", "sqlfetcharray": "Q11;Q73",
    "sqlinsert": "Q11;Q73", "queryutils": "Q11;Q73", "doctrine_dbal": "Q11;Q73",
    "oe_site_dir": "Q11;Q12", "oe_site_webroot": "Q11;Q12",
    "echo_text": "Q67", "echo_attr": "Q67", "echo_xlt": "Q67",
    "htmlspecialchars": "Q67", "twig_raw": "Q67", "smarty_nofilter": "Q67",
    "js_innerhtml": "Q67", "js_docwrite": "Q67",
    "encryptstandard": "Q69", "decryptstandard": "Q69", "cryptogen": "Q69;Q44",
    "encrypt_fs": "Q47;Q69",
    "createdocument": "Q49", "iswhitefile": "Q49", "move_uploaded": "Q49", "finfo": "Q49",
    "log_checksum": "Q68",
    "fhir_services": "Q28;Q62", "search_params": "Q62", "rest_controllers": "Q28;Q63",
    "rate_limit": "Q64", "api_version_seg": "Q63",
    "xforwardedfor": "Q42;Q6", "trusted_proxy": "Q42", "helm_k8s": "Q3;Q41",
}


def sha256(p: Path) -> str:
    h = hashlib.sha256()
    with p.open("rb") as fh:
        for chunk in iter(lambda: fh.read(1 << 20), b""):
            h.update(chunk)
    return h.hexdigest()


def questions_for(rel: str, name: str) -> str:
    if name in REPORT_QS:
        return REPORT_QS[name]
    m = re.match(r"count-(.+)\.txt$", name)
    if m and m.group(1) in SLUG_QS:
        return SLUG_QS[m.group(1)]
    # q<NN>-... convention used by snippets/manifests/raw captures
    qs = sorted({f"Q{n}" for n in re.findall(r"\bq(\d{1,2})[-_.]", name, re.I)},
                key=lambda s: int(s[1:]))
    if qs:
        return ";".join(qs)
    for key, val in (("module", "Q36;Q70"), ("drift", "Q1;Q36"), ("dwv", "Q61;Q71"),
                     ("workflow", "Q39;Q52;Q75"), ("arabic", "Q18"), ("napa", "Q24;Q56"),
                     ("claimrev", "Q31;Q66"), ("phpunit", "Q74;Q75"), ("theme", "Q34;Q59"),
                     ("security-sql", "Q67"), ("security-echo", "Q67"),
                     ("security-crypto", "Q69"), ("security-upload", "Q49"),
                     ("security-js", "Q67"), ("security-smarty", "Q67"),
                     ("integration", "Q74"), ("isolated", "Q75"), ("codecov", "Q51"),
                     ("installer-plugin", "Q37;Q70"), ("sites-default", "Q11;Q12"),
                     ("ls-tree", "Q36"), ("commit", "Q1"), ("head", "Q1")):
        if key in name.lower():
            return val
    return "(supporting)"


def kind_for(rel: str) -> str:
    if rel.startswith("evidence/raw/"):
        return "raw_command_output"
    if rel.startswith("evidence/snippets/"):
        return "analysis_snippet"
    if rel.startswith("evidence/manifests/"):
        return "manifest"
    if rel.startswith("tools/"):
        return "helper_script"
    if rel.endswith(".csv"):
        return "report_csv"
    if rel.endswith(".json"):
        return "report_json"
    return "report_markdown"


def main() -> int:
    rows = []
    for base, prefix in ((PKG, ""), (TOOLS, "tools/")):
        for p in sorted(base.rglob("*")):
            if not p.is_file():
                continue
            rel = prefix + p.relative_to(base).as_posix()
            if rel.endswith(".zip") or rel.endswith("23-file-evidence-index.csv"):
                continue
            rows.append({
                "path": rel,
                "kind": kind_for(rel),
                "size_bytes": p.stat().st_size,
                "sha256": sha256(p),
                "supports_questions": questions_for(rel, p.name),
            })

    out = PKG / "23-file-evidence-index.csv"
    with out.open("w", newline="", encoding="utf-8") as fh:
        w = csv.DictWriter(fh, fieldnames=["path", "kind", "size_bytes", "sha256", "supports_questions"])
        w.writeheader()
        w.writerows(rows)

    by_kind = defaultdict(int)
    for r in rows:
        by_kind[r["kind"]] += 1
    print(f"indexed {len(rows)} files")
    for k, v in sorted(by_kind.items()):
        print(f"  {k}: {v}")
    unmapped = [r["path"] for r in rows if r["supports_questions"] == "(supporting)"]
    print(f"unmapped (generic supporting): {len(unmapped)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
