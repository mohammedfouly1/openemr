# Export the English↔Arabic translation catalogue to CSV for external editing.
#
# Output: docs/branding-production/i18n/arabic-translations.csv
# Columns: cons_id, def_id, english, arabic
#   - cons_id  = lang_constants.cons_id (immutable primary key — DO NOT edit)
#   - def_id   = lang_definitions.def_id if a translation row exists, else blank
#   - english  = lang_constants.constant_name (DO NOT edit)
#   - arabic   = current Arabic translation (EDIT THIS COLUMN)
#
# Encoding: UTF-8 with BOM (Excel-friendly).
# Row order: cons_id ASC, so diffs stay stable across exports.

$mariadb = 'C:\openemr-stack\mariadb\bin\mariadb.exe'
$outCsv  = 'G:\My Drive\OpenEMR\docs\branding-production\i18n\arabic-translations.csv'
New-Item -ItemType Directory -Force -Path (Split-Path $outCsv) | Out-Null

# Force UTF-8 for stdin/stdout so mariadb.exe's Arabic bytes don't get mangled
# by the console's default OEM codepage (cp437/cp850) on Windows.
$prevOut = [Console]::OutputEncoding
$prevIn  = [Console]::InputEncoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::InputEncoding  = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8
try {

$sql = @"
SELECT
  c.cons_id,
  IFNULL(d.def_id, '') AS def_id,
  c.constant_name AS english,
  IFNULL(d.definition, '') AS arabic
FROM lang_constants c
LEFT JOIN lang_definitions d
  ON d.cons_id = c.cons_id
 AND d.lang_id = 22
ORDER BY c.cons_id;
"@

# --batch  -> tab-separated
# --raw    -> do NOT escape newlines/tabs (we want raw text; we handle escaping ourselves)
# Actually --raw + --batch gives raw tab-separated with unescaped values,
# so we need --batch WITHOUT --raw to get standard MySQL escaping (\t, \n, \\, \0)
# and then decode those escapes in PowerShell.

$rawBytes = & $mariadb -u root --host=127.0.0.1 --port=3306 openemr --batch --default-character-set=utf8mb4 -e $sql 2>$null | Out-String
$lines = $rawBytes -split "`r?`n" | Where-Object { $_ -ne '' }
if ($lines.Count -lt 1) { throw 'No output from mariadb.' }

# First line is the header row from --batch mode.
$header = $lines[0]
$dataLines = $lines[1..($lines.Count - 1)]

function Unescape-MysqlBatch([string]$s) {
    # MySQL --batch (no --raw) escapes: \0 \b \n \r \t \Z \\ \"
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
                default { [void]$sb.Append('\');   [void]$sb.Append($nxt); $i += 2; continue }
            }
        } else {
            [void]$sb.Append($ch)
            $i++
        }
    }
    return $sb.ToString()
}

function Csv-Quote([string]$s) {
    if ($null -eq $s) { return '""' }
    $needsQuote = ($s.Contains(',') -or $s.Contains('"') -or $s.Contains("`n") -or $s.Contains("`r"))
    $escaped = $s -replace '"', '""'
    if ($needsQuote) { return '"' + $escaped + '"' } else { return $escaped }
}

# Compose CSV
$csv = New-Object System.Text.StringBuilder
[void]$csv.AppendLine('cons_id,def_id,english,arabic')

$total = 0; $withArabic = 0; $empty = 0
foreach ($ln in $dataLines) {
    $parts = $ln -split "`t"
    if ($parts.Count -lt 4) { continue }
    $cons_id = $parts[0]
    $def_id  = $parts[1]
    $eng     = Unescape-MysqlBatch $parts[2]
    $arb     = Unescape-MysqlBatch $parts[3]
    [void]$csv.AppendLine( ((Csv-Quote $cons_id), (Csv-Quote $def_id), (Csv-Quote $eng), (Csv-Quote $arb) -join ',') )
    $total++
    if ([string]::IsNullOrWhiteSpace($arb)) { $empty++ } else { $withArabic++ }
}

# Write UTF-8 WITH BOM
$utf8Bom = New-Object System.Text.UTF8Encoding($true)
[System.IO.File]::WriteAllText($outCsv, $csv.ToString(), $utf8Bom)

$size = (Get-Item $outCsv).Length
"wrote: $outCsv"
"       total constants: $total"
"       already Arabic:  $withArabic"
"       still empty:     $empty"
"       coverage:        $([Math]::Round(100.0 * $withArabic / [Math]::Max($total,1), 1))%"
"       file size:       $size bytes"
"       encoding:        UTF-8 with BOM (Excel-ready)"
} finally {
    [Console]::OutputEncoding = $prevOut
    [Console]::InputEncoding  = $prevIn
}
