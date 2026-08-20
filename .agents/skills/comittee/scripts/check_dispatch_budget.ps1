param(
    [Parameter(Mandatory = $true)][string]$CommitteeDir,
    [Parameter(Mandatory = $true)][string]$DispatchId,
    [int]$TotalLimit = 100000
)

$ErrorActionPreference = 'Stop'
$resolved = (Resolve-Path -LiteralPath $CommitteeDir).Path
$files = Get-ChildItem -LiteralPath $resolved -File -Filter '*.md' | Where-Object {
    (Get-Content -LiteralPath $_.FullName -Raw) -match [regex]::Escape($DispatchId)
}

if (-not $files) {
    Write-Error "No files found for dispatch $DispatchId"
    exit 2
}

function Limit-For([string]$name) {
    switch -Regex ($name) {
        '^M0-dispatch-log\.md$' { return 12000 }
        '^M0-decision-pack-' { return 20000 }
        '^M6-' { return 30000 }
        '^M4-attack-' { return 12000 }
        '^M5-' { return 15000 }
        '^COM-.*-evidence-index\.md$' { return 30000 }
        '^M[1-47]-' { return 20000 }
        default { return 20000 }
    }
}

$rows = @()
$total = 0
$failed = $false
foreach ($file in $files) {
    $content = Get-Content -LiteralPath $file.FullName -Raw
    $chars = $content.Length
    $words = ([regex]::Matches($content, '\S+')).Count
    $limit = Limit-For $file.Name
    $approved = $content -match '(?im)^BUDGET EXTENSION APPROVED:\s*\+?\d+'
    $state = if ($chars -le $limit) { 'PASS' } elseif ($approved) { 'PASS WITH RECORDED EXTENSION' } else { 'FAIL' }
    if ($state -eq 'FAIL') { $failed = $true }
    $total += $chars
    $rows += [pscustomobject]@{ File=$file.Name; Characters=$chars; Words=$words; Limit=$limit; Status=$state }
}

$rows | Sort-Object File | Format-Table -AutoSize
$totalState = if ($total -le $TotalLimit) { 'PASS' } else { 'FAIL' }
Write-Output "TOTAL: $total / $TotalLimit characters — $totalState"
if ($totalState -eq 'FAIL') { $failed = $true }

if ($failed) { exit 2 }
exit 0
