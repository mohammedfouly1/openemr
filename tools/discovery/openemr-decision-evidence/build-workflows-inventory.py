"""Build github-workflows-inventory.csv from .github/workflows/*.yml.

READ-ONLY. No external deps. Parses YAML by hand-ish (uses PyYAML if available,
else falls back to text scanning). Output CSV column order defined in the
mission spec §8.5.
"""
import csv
import os
import re
import sys
from pathlib import Path

REPO = Path(r"D:\OpenEmr")
WF_DIR = REPO / ".github" / "workflows"
OUT = REPO / "docs" / "discovery" / "openemr-decision-evidence" / "evidence" / "manifests" / "github-workflows-inventory.csv"

try:
    import yaml
    HAVE_YAML = True
except ImportError:
    HAVE_YAML = False


def extract_triggers(text, doc):
    if doc and isinstance(doc, dict):
        # PyYAML converts YAML `on:` to Python `True` key because `on` is a boolean.
        on = doc.get("on", doc.get(True, None))
        if isinstance(on, dict):
            return "|".join(sorted(on.keys()))
        if isinstance(on, list):
            return "|".join(on)
        if isinstance(on, str):
            return on
    # fallback
    m = re.search(r"^on:\s*(.*)$", text, re.MULTILINE)
    return m.group(1).strip() if m else ""


def extract_name(text, doc):
    if doc and isinstance(doc, dict) and "name" in doc:
        return str(doc["name"])
    m = re.search(r"^name:\s*(.+)$", text, re.MULTILINE)
    return m.group(1).strip() if m else ""


def jobs_of(doc):
    if not doc or not isinstance(doc, dict):
        return {}
    return doc.get("jobs") or {}


def scan_matrix(doc, keys):
    """Return joined values for each requested matrix key across all jobs."""
    result = {k: set() for k in keys}
    for job in jobs_of(doc).values():
        if not isinstance(job, dict):
            continue
        strat = job.get("strategy") or {}
        mat = strat.get("matrix") if isinstance(strat, dict) else None
        if not isinstance(mat, dict):
            continue
        for k in keys:
            v = mat.get(k)
            if isinstance(v, list):
                for item in v:
                    if isinstance(item, dict):
                        # extract known scalars
                        for kk in ("version", "image", "name"):
                            if kk in item:
                                result[k].add(str(item[kk]))
                    else:
                        result[k].add(str(item))
            elif v is not None:
                result[k].add(str(v))
        runs_on = job.get("runs-on")
        if runs_on and "runs-on" in keys:
            if isinstance(runs_on, list):
                for r in runs_on:
                    result["runs-on"].add(str(r))
            else:
                result["runs-on"].add(str(runs_on))
    return {k: "|".join(sorted(v)) for k, v in result.items()}


def rows():
    files = sorted(WF_DIR.glob("*.yml"))
    for f in files:
        text = f.read_text(encoding="utf-8", errors="replace")
        doc = None
        if HAVE_YAML:
            try:
                doc = yaml.safe_load(text)
            except Exception:
                doc = None

        name = extract_name(text, doc)
        triggers = extract_triggers(text, doc)
        jobs = jobs_of(doc)
        job_names = "|".join(sorted(jobs.keys())) if jobs else ""

        mat = scan_matrix(doc, ["runs-on", "php-version", "php", "database", "mariadb", "mysql"])
        os_matrix = mat.get("runs-on", "")
        php_matrix = mat.get("php-version") or mat.get("php") or ""
        # mariadb/mysql detection from matrix or from "services:" block
        mariadb_matrix = ""
        mysql_matrix = ""
        # from matrix "database"
        dbmat = mat.get("database", "")
        if dbmat:
            mariadb_matrix = "|".join(sorted(x for x in dbmat.split("|") if "mariadb" in x.lower()))
            mysql_matrix = "|".join(sorted(x for x in dbmat.split("|") if "mysql" in x.lower()))
        # services: images
        for job in jobs.values() if isinstance(jobs, dict) else []:
            if not isinstance(job, dict):
                continue
            svcs = job.get("services")
            if isinstance(svcs, dict):
                for svc in svcs.values():
                    if isinstance(svc, dict):
                        img = str(svc.get("image", ""))
                        if img.lower().startswith("mariadb"):
                            mariadb_matrix = (mariadb_matrix + "|" + img).strip("|")
                        elif img.lower().startswith("mysql"):
                            mysql_matrix = (mysql_matrix + "|" + img).strip("|")

        invokes_phpunit_xml = bool(re.search(r"vendor/bin/phpunit(?![-.\w])", text)) or bool(re.search(r"\bphpunit\.xml\b", text))
        invokes_phpunit_isolated = "phpunit-isolated.xml" in text
        invokes_phpunit_integration = "phpunit.integration.xml" in text
        uses_codecov = "codecov/codecov-action" in text or "codecov-action" in text
        is_reusable = "workflow_call" in text

        secrets = sorted(set(re.findall(r"\bsecrets\.([A-Z0-9_]+)", text)))
        secrets_str = "|".join(secrets)

        notes = ""
        if "workflow_dispatch" in text and is_reusable:
            notes = "reusable+dispatch"
        elif is_reusable:
            notes = "reusable"

        yield {
            "workflow_file": ".github/workflows/" + f.name,
            "workflow_name": name,
            "triggers": triggers,
            "jobs": job_names,
            "os_matrix": os_matrix,
            "php_matrix": php_matrix,
            "mariadb_matrix": mariadb_matrix,
            "mysql_matrix": mysql_matrix,
            "invokes_phpunit_xml": invokes_phpunit_xml,
            "invokes_phpunit_isolated_xml": invokes_phpunit_isolated,
            "invokes_phpunit_integration_xml": invokes_phpunit_integration,
            "uses_codecov": uses_codecov,
            "is_reusable": is_reusable,
            "uses_secrets_flagged": secrets_str,
            "notes": notes,
        }


def main():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    fieldnames = [
        "workflow_file","workflow_name","triggers","jobs","os_matrix","php_matrix",
        "mariadb_matrix","mysql_matrix","invokes_phpunit_xml","invokes_phpunit_isolated_xml",
        "invokes_phpunit_integration_xml","uses_codecov","is_reusable","uses_secrets_flagged","notes",
    ]
    rs = list(rows())
    with OUT.open("w", newline="", encoding="utf-8") as fh:
        w = csv.DictWriter(fh, fieldnames=fieldnames)
        w.writeheader()
        for r in rs:
            w.writerow(r)
    print(f"wrote {len(rs)} rows -> {OUT}")


if __name__ == "__main__":
    main()
