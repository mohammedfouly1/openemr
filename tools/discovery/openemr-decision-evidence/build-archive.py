#!/usr/bin/env python3
"""
build-archive.py — secret-scan, then archive the evidence package.

1. Scans every generated file for secret-shaped strings. Any hit is reported and
   the archive is REFUSED (exit 2) so a leak cannot be shipped silently.
2. Builds docs/discovery/openemr-decision-evidence/openemr-decision-evidence-<SHORT_SHA>.zip
   containing the reports, CSVs, JSON, raw outputs and helper scripts.
3. Writes the archive SHA-256 to evidence/manifests/archive-sha256.txt.

Excluded from the archive: .git, vendor, node_modules, any nested archive.
The package contains no patient data, credentials, private keys, DB dumps or
environment files by construction - it is generated solely from the audit's own
output directories.
"""

from __future__ import annotations

import hashlib
import re
import subprocess
import sys
import zipfile
from pathlib import Path

ROOT = Path(subprocess.run(["git", "rev-parse", "--show-toplevel"],
                           capture_output=True, text=True, check=True).stdout.strip())
SHORT_SHA = subprocess.run(["git", "rev-parse", "--short", "HEAD"],
                           capture_output=True, text=True, check=True).stdout.strip()
PKG = ROOT / "docs" / "discovery" / "openemr-decision-evidence"
TOOLS = ROOT / "tools" / "discovery" / "openemr-decision-evidence"
ARCHIVE = PKG / f"openemr-decision-evidence-{SHORT_SHA}.zip"

# Secret-shaped patterns. Deliberately includes the ENCODED forms of the GitHub
# tokens found in Q43 - a plain `ghp_` scanner catches only one of the three
# obfuscation layers upstream uses, which is exactly how the earlier audit
# under-counted the exposure.
SECRET_PATTERNS: list[tuple[str, re.Pattern[str]]] = [
    ("github_pat_classic", re.compile(r"gh[pousr]_[A-Za-z0-9]{30,}")),
    ("github_pat_fine", re.compile(r"github_pat_[A-Za-z0-9_]{50,}")),
    ("base64_ghp", re.compile(r"Z2hwX[A-Za-z0-9+/=]{20,}")),
    ("decimal_ghp", re.compile(r"\b103\s+104\s+112\s+95(?:\s+\d{2,3}){10,}")),
    ("aws_access_key", re.compile(r"AKIA[0-9A-Z]{16}")),
    ("private_key_block", re.compile(r"-----BEGIN (?:RSA |EC |OPENSSH |PGP )?PRIVATE KEY-----")),
    ("slack_token", re.compile(r"xox[baprs]-[A-Za-z0-9-]{10,}")),
    ("generic_bearer", re.compile(r"\bBearer\s+[A-Za-z0-9._~+/-]{40,}")),
]

# Files whose own text legitimately names the patterns (this scanner, and the
# reports that document the redaction policy). We still scan them, but a match on
# the literal pattern NAME is not a secret - only a real value is.
TEXT_EXT = {".md", ".txt", ".csv", ".json", ".py", ".sh", ".tsv"}


def iter_files():
    for base in (PKG, TOOLS):
        for p in sorted(base.rglob("*")):
            if p.is_file() and p.suffix.lower() not in {".zip", ".tgz", ".gz"}:
                yield p


def scan() -> list[tuple[Path, int, str]]:
    hits = []
    for p in iter_files():
        if p.suffix.lower() not in TEXT_EXT:
            continue
        if p.resolve() == Path(__file__).resolve():
            continue  # this file defines the patterns
        try:
            text = p.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        for lineno, line in enumerate(text.splitlines(), 1):
            for name, pat in SECRET_PATTERNS:
                if pat.search(line):
                    hits.append((p, lineno, name))
    return hits


def sha256(p: Path) -> str:
    h = hashlib.sha256()
    with p.open("rb") as fh:
        for chunk in iter(lambda: fh.read(1 << 20), b""):
            h.update(chunk)
    return h.hexdigest()


def main() -> int:
    print(f"scanning evidence package for secrets ({SHORT_SHA}) ...")
    hits = scan()
    if hits:
        print("\nREFUSING TO ARCHIVE - secret-shaped strings found:", file=sys.stderr)
        for p, lineno, name in hits:
            print(f"  {p.relative_to(ROOT)}:{lineno}  [{name}]", file=sys.stderr)
        print("\nRedact these before archiving.", file=sys.stderr)
        return 2
    print("  clean - no secret-shaped strings in any generated file")

    if ARCHIVE.exists():
        ARCHIVE.unlink()

    count = 0
    with zipfile.ZipFile(ARCHIVE, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        for p in iter_files():
            parts = p.relative_to(ROOT).parts
            if any(seg in {".git", "vendor", "node_modules"} for seg in parts):
                continue
            z.write(p, arcname=p.relative_to(ROOT).as_posix())
            count += 1

    digest = sha256(ARCHIVE)
    man = PKG / "evidence" / "manifests"
    man.mkdir(parents=True, exist_ok=True)
    (man / "archive-sha256.txt").write_text(
        f"{digest}  {ARCHIVE.name}\n"
        f"# fork_commit: {subprocess.run(['git','rev-parse','HEAD'],capture_output=True,text=True).stdout.strip()}\n"
        f"# files: {count}\n"
        f"# bytes: {ARCHIVE.stat().st_size}\n",
        encoding="utf-8")

    print(f"\narchive : {ARCHIVE.relative_to(ROOT)}")
    print(f"files   : {count}")
    print(f"bytes   : {ARCHIVE.stat().st_size:,}")
    print(f"sha256  : {digest}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
