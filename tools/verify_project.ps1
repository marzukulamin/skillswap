$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$requiredFiles = @(
    'index.php',
    'login.php',
    'register.php',
    'includes/db.php',
    'includes/auth.php',
    'student/dashboard.php',
    'student/search_skills.php',
    'student/bookings.php',
    'admin/dashboard.php',
    'sql/skillswap.sql'
)

$missing = @($requiredFiles | Where-Object { -not (Test-Path -LiteralPath (Join-Path $projectRoot $_)) })
if ($missing.Count -gt 0) {
    throw "Missing required files: $($missing -join ', ')"
}

$schemaPath = Join-Path $projectRoot 'sql/skillswap.sql'
$schema = Get-Content -Raw -LiteralPath $schemaPath
$matches = [regex]::Matches($schema, 'CREATE\s+TABLE\s+`([A-Z_]+)`', 'IgnoreCase')
$actualTables = @($matches | ForEach-Object { $_.Groups[1].Value.ToUpperInvariant() })
$expectedTables = @('USER', 'SKILL', 'SESSION_BOOKING', 'REVIEW', 'BADGE', 'USER_BADGE')

if (($actualTables -join ',') -ne ($expectedTables -join ',')) {
    throw "Unexpected table set or order. Found: $($actualTables -join ', ')"
}

$foreignKeyCount = [regex]::Matches($schema, 'FOREIGN\s+KEY', 'IgnoreCase').Count
if ($foreignKeyCount -ne 7) {
    throw "Expected 7 foreign keys; found $foreignKeyCount."
}

$phpFiles = @(Get-ChildItem -LiteralPath $projectRoot -Recurse -Filter '*.php' -File | Where-Object { $_.FullName -notlike '*\tmp\*' })
foreach ($file in $phpFiles) {
    $content = Get-Content -Raw -LiteralPath $file.FullName
    if ($content -notmatch '<\?') {
        throw "No PHP tag found: $($file.FullName)"
    }
}

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($phpCommand) {
    foreach ($file in $phpFiles) {
        $output = & $phpCommand.Source -l $file.FullName 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "PHP lint failed for $($file.FullName): $output"
        }
    }
    Write-Host "PASS: PHP linted $($phpFiles.Count) files."
} else {
    Write-Host "SKIP: PHP executable not found; install/start XAMPP to run php -l and live tests."
}

Write-Host "PASS: Required project files exist."
Write-Host "PASS: Exact six-table schema confirmed."
Write-Host "PASS: Seven foreign-key definitions confirmed."
Write-Host "PASS: Checked $($phpFiles.Count) PHP source files."
