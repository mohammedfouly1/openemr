# Re-import edited Arabic translations from arabic-translations.csv back into
# the OpenEMR lang_definitions table.
#
# Reads:  docs/branding-production/i18n/arabic-translations.csv
# Writes: docs/branding-production/i18n/arabic-translations-diff.log (audit)
# Runs:   INSERT / UPDATE against lang_definitions where the CSV arabic column
#         differs from the current database value.
#
# Rules:
#   - cons_id and english columns are IGNORED for writes (identity, immutable).
#   - Only rows where the CSV's arabic column differs from the DB's current
#     definition (or where the DB has no row) trigger a write.
#   - Empty CSV arabic cell + existing DB row  -> DELETE that def row.
#   - Non-empty CSV arabic cell + no DB row    -> INSERT.
#   - Non-empty CSV arabic cell + existing DB row (different) -> UPDATE.
#   - Non-empty CSV arabic cell + existing DB row (same)      -> skip (no-op).
#
# Safety: --dry-run flag prints intended writes without touching the DB.

param(
    [switch]$DryRun,
    [string]$Csv = 'G:\My Drive\OpenEMR\docs\branding-production\i18n\arabic-translations.csv',
    [string]$Log = 'G:\My Drive\OpenEMR\docs\branding-production\i18n\arabic-translations-diff.log'
)

$mariadb = 'C:\openemr-stack\mariadb\bin\mariadb.exe'
$arabicLangId = 22

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::InputEncoding  = [System.Text.Encoding]::UTF8
$OutputEncoding           = [System.Text.Encoding]::UTF8

if (-not (Test-Path $Csv)) { throw "CSV not found: $Csv" }

Write-Host "Loading CSV: $Csv"
# Import-Csv reads UTF-8-with-BOM correctly on PS 7 / .NET 5+
$rows = Import-Csv -Path $Csv -Encoding UTF8

Write-Host ("  rows in CSV: {0}" -f $rows.Count)

# Pull current DB state into a hashtable: cons_id -> @{ def_id; arabic }
Write-Host 'Loading current DB state...'
$sqlCurrent = @"
SELECT c.cons_id, IFNULL(d.def_id, 0) AS def_id, IFNULL(d.definition, '') AS arabic
FROM lang_constants c
LEFT JOIN lang_definitions d ON d.cons_id = c.cons_id AND d.lang_id = $arabicLangId;
"@
$dbRaw = & $mariadb -u root --host=127.0.0.1 --port=3306 openemr --batch --default-character-set=utf8mb4 -e $sqlCurrent
$dbLines = ($dbRaw -join "`n") -split "`r?`n" | Where-Object { $_ -ne '' }

function Unescape-MysqlBatch([string]$s) {
    $sb = [System.Text.StringBuilder]::new($s.Length)
    $i = 0
    while ($i -lt $s.Length) {
        $ch = $s[$i]
        if ($ch -eq '\' -and $i + 1 -lt $s.Length) {
            $nxt = $s[$i + 1]
            switch ($nxt) {
                '0' { [void]$sb.Append([char]0);   $i += 2; continue }
                'b' { [void]$sb.Append([char]8);   $i += 2; continue }
                'n' { [void]$sb.Append("`n");      $i += 2; continue }
                'r' { [void]$sb.Append("`r");      $i += 2; continue }
                't' { [void]$sb.Append("`t");      $i += 2; continue }
                'Z' { [void]$sb.Append([char]26);  $i += 2; continue }
                '\' { [void]$sb.Append('\');       $i += 2; continue }
                default { [void]$sb.Append('\'); [void]$sb.Append($nxt); $i += 2; continue }
            }
        } else { [void]$sb.Append($ch); $i++ }
    }
    return $sb.ToString()
}

$db = @{}
foreach ($ln in ($dbLines | Select-Object -Skip 1)) {
    $p = $ln -split "`t"
    if ($p.Count -lt 3) { continue }
    $db[$p[0]] = @{ def_id = [int]$p[1]; arabic = (Unescape-MysqlBatch $p[2]) }
}
Write-Host ("  rows in DB:  {0}" -f $db.Count)

# Build the diff
$inserts = @(); $updates = @(); $deletes = @(); $noop = 0; $missingConsId = 0
foreach ($r in $rows) {
    $cid = $r.cons_id
    $csvArb = if ($null -eq $r.arabic) { '' } else { $r.arabic.Trim() }
    if (-not $db.ContainsKey($cid)) { $missingConsId++; continue }
    $cur = $db[$cid]
    $curArb = $cur.arabic
    if ($csvArb -eq '' -and $cur.def_id -gt 0 -and $curArb -ne '') {
        $deletes += @{ def_id = $cur.def_id; cons_id = $cid; english = $r.english; oldArabic = $curArb }
    } elseif ($csvArb -ne '' -and $cur.def_id -eq 0) {
        $inserts += @{ cons_id = $cid; arabic = $csvArb; english = $r.english }
    } elseif ($csvArb -ne '' -and $csvArb -ne $curArb) {
        $updates += @{ def_id = $cur.def_id; cons_id = $cid; arabic = $csvArb; english = $r.english; oldArabic = $curArb }
    } else {
        $noop++
    }
}

Write-Host ''
Write-Host 'Planned writes:'
Write-Host ("  INSERT: {0}" -f $inserts.Count)
Write-Host ("  UPDATE: {0}" -f $updates.Count)
Write-Host ("  DELETE: {0}" -f $deletes.Count)
Write-Host ("  no-op:  {0}" -f $noop)
Write-Host ("  cons_id in CSV but not in DB: {0}" -f $missingConsId)
Write-Host ''

# Write diff log for review
$logLines = @()
$logLines += "# Diff generated $(Get-Date -Format o)"
$logLines += "# INSERT: $($inserts.Count)  UPDATE: $($updates.Count)  DELETE: $($deletes.Count)"
foreach ($i in $inserts) { $logLines += "INSERT  cons_id=$($i.cons_id)  eng=[$($i.english)]  arb=[$($i.arabic)]" }
foreach ($u in $updates) { $logLines += "UPDATE  def_id=$($u.def_id)  cons_id=$($u.cons_id)  eng=[$($u.english)]  old=[$($u.oldArabic)]  new=[$($u.arabic)]" }
foreach ($d in $deletes) { $logLines += "DELETE  def_id=$($d.def_id)  cons_id=$($d.cons_id)  eng=[$($d.english)]  old=[$($d.oldArabic)]" }
$utf8Bom = New-Object System.Text.UTF8Encoding($true)
[System.IO.File]::WriteAllLines($Log, $logLines, $utf8Bom)
Write-Host "Diff log: $Log"

if ($DryRun) { Write-Host ''; Write-Host 'DRY RUN — no database writes performed.'; return }

# Confirm before applying
Write-Host ''
$confirm = Read-Host 'Apply these changes to the database? Type YES to proceed'
if ($confirm -ne 'YES') { Write-Host 'Aborted.'; return }

function SqlLiteral([string]$s) {
    if ($null -eq $s) { return "NULL" }
    return "'" + ($s -replace "\\", "\\" -replace "'", "\'") + "'"
}

# Batch the writes into a single SQL file so mariadb executes them
# transactionally through START TRANSACTION / COMMIT.
$sqlFile = [System.IO.Path]::GetTempFileName() + '.sql'
$sw = New-Object System.IO.StreamWriter($sqlFile, $false, (New-Object System.Text.UTF8Encoding($false)))
try {
    $sw.WriteLine('SET NAMES utf8mb4;')
    $sw.WriteLine('START TRANSACTION;')
    foreach ($i in $inserts) {
        $sw.WriteLine("INSERT INTO lang_definitions (cons_id, lang_id, definition) VALUES ($($i.cons_id), $arabicLangId, $(SqlLiteral $i.arabic));")
    }
    foreach ($u in $updates) {
        $sw.WriteLine("UPDATE lang_definitions SET definition = $(SqlLiteral $u.arabic) WHERE def_id = $($u.def_id);")
    }
    foreach ($d in $deletes) {
        $sw.WriteLine("DELETE FROM lang_definitions WHERE def_id = $($d.def_id);")
    }
    $sw.WriteLine('COMMIT;')
} finally { $sw.Close() }

Write-Host "Executing SQL batch..."
Get-Content -Raw -LiteralPath $sqlFile | & $mariadb -u root --host=127.0.0.1 --port=3306 openemr --default-character-set=utf8mb4
if ($LASTEXITCODE -eq 0) {
    Write-Host 'DONE.'
} else {
    Write-Host "ERROR — mariadb exit code $LASTEXITCODE. See $sqlFile for the batch content."
}
Remove-Item $sqlFile -ErrorAction SilentlyContinue
