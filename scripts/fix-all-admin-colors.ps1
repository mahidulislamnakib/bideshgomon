Param(
  [string]$Root = "$PSScriptRoot\..\resources\js"
)

Write-Host "\n=== Fixing primary colors to brand red ===" -ForegroundColor Cyan
if (!(Test-Path $Root)) { Write-Error "Root not found: $Root"; exit 1 }

$files = Get-ChildItem -Path $Root -Filter "*.vue" -Recurse
$totalFiles = 0
$totalChanges = 0

# Regex patterns to unify primary actions to brand red
$replacements = @(
  @{ pattern = '\bbg-(?:blue|indigo|teal|cyan|sky|ocean|primary)-(?:500|600)\b'; replacement = 'bg-brand-red-600' },
  @{ pattern = '\bhover:bg-(?:blue|indigo|teal|cyan|sky|ocean|primary)-700\b'; replacement = 'hover:bg-red-700' },
  @{ pattern = '\btext-(?:blue|indigo|teal|cyan|sky|ocean|primary)-600\b'; replacement = 'text-brand-red-600' },
  @{ pattern = '\bhover:text-(?:blue|indigo|teal|cyan|sky|ocean|primary)-900\b'; replacement = 'hover:text-red-900' },
  @{ pattern = '\bfocus:ring-(?:blue|indigo|teal|cyan|sky|ocean|primary)-(?:500|600)\b'; replacement = 'focus:ring-brand-red-600' },
  # Pagination active states in class arrays/strings
  @{ pattern = "bg-(?:blue|indigo|teal|cyan|sky|ocean|primary)-(?:500|600) text-white"; replacement = 'bg-brand-red-600 text-white' }
)

foreach ($f in $files) {
  $content = Get-Content $f.FullName -Raw
  $fileChanges = 0

  foreach ($rule in $replacements) {
    $before = $content
    $content = [regex]::Replace($content, $rule.pattern, $rule.replacement)
    if ($content -ne $before) {
      $diffCount = ([regex]::Matches($before, $rule.pattern)).Count
      $fileChanges += $diffCount
    }
  }

  if ($fileChanges -gt 0) {
    Set-Content -Path $f.FullName -Value $content -NoNewline
    $totalFiles += 1
    $totalChanges += $fileChanges
    Write-Host ("Updated: {0} ({1} changes)" -f $f.FullName, $fileChanges) -ForegroundColor Green
  }
}

Write-Host ("\nDone. Files updated: {0}, total replacements: {1}\n" -f $totalFiles, $totalChanges) -ForegroundColor Green
