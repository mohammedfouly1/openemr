#!/usr/bin/env python3
"""Verify ancestor claim at blob level.

For each sampled path, check whether the fork's blob SHA at HEAD appears at
that path anywhere in the upstream/master commit history. If yes, the fork's
version of the file WAS the upstream version at some past point -> confirms
ancestor relationship.
"""
import subprocess
import sys
from pathlib import Path

ROOT = Path(r"D:\OpenEmr")
PATHS_FILE = ROOT / "docs/discovery/openemr-decision-evidence/evidence/raw/drift-verification-paths.txt"
OUT_FILE = ROOT / "docs/discovery/openemr-decision-evidence/evidence/raw/drift-verification-sample.txt"


def blob_at(ref: str, path: str) -> str:
    r = subprocess.run(
        ["git", "-C", str(ROOT), "ls-tree", ref, "--", path],
        capture_output=True, text=True, check=False,
    )
    line = r.stdout.strip()
    if not line:
        return ""
    # "<mode> blob <sha>\t<path>"
    parts = line.split()
    return parts[2] if len(parts) >= 3 else ""


def commits_touching(ref_range: str, path: str) -> list[str]:
    r = subprocess.run(
        ["git", "-C", str(ROOT), "log", "--pretty=format:%H", ref_range, "--", path],
        capture_output=True, text=True, check=False,
    )
    return [s for s in r.stdout.strip().splitlines() if s]


def main() -> None:
    paths = [p.strip() for p in PATHS_FILE.read_text(encoding="utf-8").splitlines() if p.strip()]
    lines = []
    lines.append(f"Verification: fork HEAD blob for each path found somewhere in upstream/master history for same path?")
    lines.append(f"Sample size: {len(paths)}")
    lines.append(f"HEAD = 631f2b38cf633769c305233f88cdf9c73ca80657")
    lines.append(f"upstream/master = 608f9ae37ccaea5d5c251a0aad84793e801ca485")
    lines.append("")
    confirmed = 0
    for p in paths:
        fork_blob = blob_at("HEAD", p)
        up_blob = blob_at("upstream/master", p)
        # walk upstream/master commits that touched this path
        commits = commits_touching("upstream/master", p)
        match_sha = ""
        for c in commits:
            if blob_at(c, p) == fork_blob:
                match_sha = c
                break
        status = "MATCH" if match_sha else "NO-MATCH"
        if match_sha:
            confirmed += 1
        lines.append(f"{status:9s} {p}")
        lines.append(f"          fork_blob={fork_blob}")
        lines.append(f"          upstream_master_blob={up_blob}")
        lines.append(f"          first_upstream_commit_containing_fork_blob={match_sha or '(none among ' + str(len(commits)) + ' commits touching this path)'}")
    lines.append("")
    lines.append(f"SUMMARY: {confirmed}/{len(paths)} sampled paths had fork blob present in upstream/master history at same path.")
    OUT_FILE.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(f"{confirmed}/{len(paths)} confirmed")


if __name__ == "__main__":
    main()
