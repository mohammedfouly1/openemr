#!/usr/bin/env bash
# collect-remaining-evidence.sh
#
# Gathers the repository counts and match-lists still outstanding after the
# first audit pass (Q1-Q3, Q39-Q42, Q48-Q50, Q54, Q60, Q62-Q64, Q67-Q69, Q73).
#
# Design note: every search runs as `git grep <pattern> HEAD` rather than a
# filesystem walk. On the Google-Drive mount that hosts this checkout a
# recursive filesystem grep takes minutes (~92% of I/O is Drive metadata
# round-trips) whereas reading blobs out of the packfiles takes ~1s. Using
# HEAD also pins every count to an exact commit, which is what the evidence
# schema requires.
#
# Read-only. Writes only under docs/discovery/openemr-decision-evidence/.

set -uo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT" || exit 1

OUT="docs/discovery/openemr-decision-evidence/evidence/raw"
MAN="docs/discovery/openemr-decision-evidence/evidence/manifests"
mkdir -p "$OUT" "$MAN"

REF="HEAD"
SHA="$(git rev-parse HEAD)"

# emit <slug> <description> <git-grep-args...>
# Saves the full match list and prints "slug<TAB>count" to the counts file.
COUNTS="$OUT/remaining-counts.tsv"
: > "$COUNTS"

emit() {
    local slug="$1"; shift
    local desc="$1"; shift
    local file="$OUT/count-${slug}.txt"
    {
        echo "# slug: $slug"
        echo "# description: $desc"
        echo "# ref: $REF ($SHA)"
        echo "# command: git grep $* $REF"
        echo "# generated: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
        echo "#"
    } > "$file"
    git grep "$@" "$REF" >> "$file" 2>/dev/null
    # count only the payload lines, not the header
    local n
    n="$(grep -cv '^#' "$file")"
    printf '%s\t%s\t%s\n' "$slug" "$n" "$desc" >> "$COUNTS"
    echo "  $slug = $n"
}

echo "== SQL / data-access call-site counts (Q73, Q11) =="
emit sqlstatement      "sqlStatement( call sites"            -n -e 'sqlStatement(' -- '*.php'
emit sqlquery          "sqlQuery( call sites"                -n -e 'sqlQuery('     -- '*.php'
emit sqlfetcharray     "sqlFetchArray( call sites"           -n -e 'sqlFetchArray(' -- '*.php'
emit sqlinsert         "sqlInsert( call sites"               -n -e 'sqlInsert('    -- '*.php'
emit queryutils        "QueryUtils:: static call sites"      -n -e 'QueryUtils::'  -- '*.php'
emit doctrine_dbal     "Doctrine DBAL usage sites"           -n -e 'Doctrine\\DBAL' -- '*.php'
emit oe_site_dir       "OE_SITE_DIR file-path sites"         -n -e 'OE_SITE_DIR'   -- '*.php'
emit oe_site_webroot   "OE_SITE_WEBROOT sites"               -n -e 'OE_SITE_WEBROOT' -- '*.php'

echo "== Output-escaping ratio (Q67) =="
emit echo_text         "echo text( sites"                    -n -e 'echo text('    -- '*.php'
emit echo_attr         "echo attr( sites"                    -n -e 'echo attr('    -- '*.php'
emit echo_xlt          "echo xlt( sites"                     -n -e 'echo xlt('     -- '*.php'
emit htmlspecialchars  "htmlspecialchars( sites"             -n -e 'htmlspecialchars(' -- '*.php'
emit twig_raw          "Twig |raw filter sites"              -n -e '|raw'          -- '*.twig'
emit smarty_nofilter   "Smarty nofilter sites"               -n -e 'nofilter'      -- '*.tpl' '*.html'
emit js_innerhtml      "innerHTML assignment sites"          -n -e 'innerHTML'     -- '*.js' '*.php'
emit js_docwrite       "document.write sites"                -n -e 'document.write' -- '*.js' '*.php'

echo "== Encryption inventory (Q69) =="
emit encryptstandard   "encryptStandard( sites"              -n -e 'encryptStandard(' -- '*.php'
emit decryptstandard   "decryptStandard( sites"              -n -e 'decryptStandard(' -- '*.php'
emit cryptogen         "CryptoGen usage sites"               -n -e 'CryptoGen'     -- '*.php'
emit encrypt_fs        "encryptForFilesystem sites"          -n -e 'ForFilesystem' -- '*.php'

echo "== Upload path (Q49, Q67) =="
emit createdocument    "createDocument( call sites"          -n -e 'createDocument(' -- '*.php'
emit iswhitefile       "isWhiteFile( call sites"             -n -e 'isWhiteFile('  -- '*.php'
emit move_uploaded     "move_uploaded_file sites"            -n -e 'move_uploaded_file' -- '*.php'
emit finfo             "finfo / mime detection sites"        -n -e 'finfo_'        -- '*.php'

echo "== Audit integrity (Q68) =="
emit log_checksum      "log checksum references"             -n -e 'checksum'      -- '*.php'

echo "== FHIR surface (Q28, Q62) =="
emit fhir_services     "FHIR service class files"            -l -e 'class Fhir'    -- 'src/Services/FHIR/*.php'
emit search_params     "FhirSearchParameterDefinition sites" -n -e 'FhirSearchParameterDefinition' -- 'src/Services/FHIR/*.php'
emit rest_controllers  "RestController class files"          -l -e 'class '        -- 'src/RestControllers/*.php'

echo "== API posture (Q63, Q64) =="
emit rate_limit        "rate limiting references"            -in -e 'rate.limit'   -- '*.php'
emit api_version_seg   "/v1/ style version segments"         -n -e '/v1/'          -- 'src/RestControllers/*.php' '_rest_routes.inc.php'

echo "== DevOps (Q3, Q39, Q40, Q41, Q42) =="
emit xforwardedfor     "X-Forwarded-For handling"            -in -e 'X_FORWARDED_FOR' -e 'X-Forwarded-For' -- '*.php' '*.conf' '*.yml'
emit trusted_proxy     "trusted proxy configuration"         -in -e 'trustedproxies' -e 'trusted_proxy' -- '*.php' '*.yml'
emit helm_k8s          "helm / kubernetes artifacts"         -iln -e 'apiVersion: apps/v1' -e 'kind: Deployment' -- '*.yml' '*.yaml'

echo
echo "== Non-grep manifests =="

# Q54 - tools/ directory inventory
git ls-files 'tools/**' > "$MAN/q54-tools-inventory.txt"
echo "  tools/ tracked files = $(wc -l < "$MAN/q54-tools-inventory.txt")"

# Q3/Q41 - deployment artifacts
{
    echo "# Deployment / orchestration artifact search at $SHA"
    echo "## Files matching helm/k8s/nomad/swarm naming:"
    git ls-files | grep -iE 'chart\.yaml|values\.yaml|/templates/.*\.yaml|k8s|kubernetes|nomad|swarm' || echo "(none)"
    echo "## docker-compose files:"
    git ls-files | grep -iE 'docker-compose.*\.ya?ml' || echo "(none)"
} > "$MAN/q3-deployment-artifacts.txt"

# Q40 - inferno
{
    echo "# Inferno ONC certification artifacts at $SHA"
    git ls-files | grep -i inferno || echo "(none)"
} > "$MAN/q40-inferno-artifacts.txt"

# Q39 - docker workflows
{
    echo "# GitHub workflows at $SHA"
    git ls-files '.github/workflows/*'
} > "$MAN/q39-workflow-inventory.txt"
echo "  workflows = $(git ls-files '.github/workflows/*' | wc -l)"

# Q60 - charset / collation in schema
{
    echo "# CHARSET / COLLATE declarations in sql/ at $SHA"
    echo "## sql/database.sql DEFAULT CHARSET occurrences:"
    git grep -c 'DEFAULT CHARSET' HEAD -- 'sql/database.sql' || echo "0"
    echo "## COLLATE occurrences in sql/database.sql:"
    git grep -c 'COLLATE' HEAD -- 'sql/database.sql' || echo "0"
    echo "## Installer collation handling:"
    git grep -n 'utf8mb4\|COLLATE\|CHARACTER SET' HEAD -- 'library/classes/Installer.class.php'
} > "$MAN/q60-charset-collation.txt"

# Q62 - FHIR service file list
git ls-files 'src/Services/FHIR/*.php' > "$MAN/q62-fhir-service-files.txt"
echo "  FHIR service files = $(wc -l < "$MAN/q62-fhir-service-files.txt")"

# checksums for reproducibility
( cd "$OUT" && sha256sum count-*.txt remaining-counts.tsv > "../manifests/remaining-counts-sha256.txt" 2>/dev/null )

echo
echo "DONE. Counts summary:"
column -t -s $'\t' "$COUNTS" 2>/dev/null || cat "$COUNTS"
