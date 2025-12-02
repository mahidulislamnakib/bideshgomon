# BideshGomon Brand Color Update Script
# Replaces ocean blue theme with official brand colors
# Brand Red: #e4282b | Brand Green: #64ac44 | Brand Gray: #505050

Set-Location "c:\xampp\htdocs\bideshgomon"

Write-Host "Brand Color Update Starting..." -ForegroundColor Cyan

# Create backup
$backupPath = "backups\brand-update-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
New-Item -ItemType Directory -Path $backupPath -Force | Out-Null
Copy-Item -Path "resources\js\Pages\Admin" -Destination $backupPath -Recurse -Force
Write-Host "Backup created: $backupPath" -ForegroundColor Green

# Color replacements
$replacements = @{
    'bg-blue-600' = 'bg-[#e4282b]'
    'hover:bg-blue-700' = 'hover:bg-red-700'
    'bg-indigo-600' = 'bg-[#e4282b]'
    'hover:bg-indigo-700' = 'hover:bg-red-700'
    'bg-blue-100' = 'bg-red-100'
    'bg-indigo-100' = 'bg-red-100'
    'text-blue-600' = 'text-[#e4282b]'
    'hover:text-blue-700' = 'hover:text-red-700'
    'hover:text-blue-900' = 'hover:text-red-900'
    'text-blue-800' = 'text-[#e4282b]'
    'text-blue-900' = 'text-red-900'
    'text-indigo-600' = 'text-[#e4282b]'
    'hover:text-indigo-900' = 'hover:text-red-900'
    'text-indigo-800' = 'text-[#e4282b]'
    'text-indigo-500' = 'text-[#e4282b]'
    'text-indigo-400' = 'text-red-400'
    'border-blue-600' = 'border-[#e4282b]'
    'border-blue-500' = 'border-[#e4282b]'
    'border-blue-200' = 'border-red-200'
    'border-indigo-600' = 'border-[#e4282b]'
    'border-indigo-500' = 'border-[#e4282b]'
    'focus:border-blue-500' = 'focus:border-[#e4282b]'
    'focus:ring-blue-500' = 'focus:ring-[#e4282b]'
    'focus:border-indigo-500' = 'focus:border-[#e4282b]'
    'focus:ring-indigo-500' = 'focus:ring-[#e4282b]'
    'ring-indigo-500' = 'ring-[#e4282b]'
    'ring-blue-500' = 'ring-[#e4282b]'
    'bg-blue-50' = 'bg-red-50'
    'bg-indigo-50' = 'bg-red-50'
    'bg-blue-500' = 'bg-[#e4282b]'
    'text-blue-500' = 'text-[#e4282b]'
    'bg-indigo-500' = 'bg-[#e4282b]'
}

$files = Get-ChildItem -Path "resources\js\Pages\Admin" -Filter "*.vue" -Recurse
$filesChanged = 0
$totalReplacements = 0

Write-Host "Processing $($files.Count) files..." -ForegroundColor Cyan

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $original = $content
    $fileReplacements = 0
    
    foreach ($old in $replacements.Keys) {
        $new = $replacements[$old]
        $pattern = [regex]::Escape($old)
        $count = ([regex]::Matches($content, $pattern)).Count
        if ($count -gt 0) {
            $content = $content -replace $pattern, $new
            $fileReplacements += $count
        }
    }
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $filesChanged++
        $totalReplacements += $fileReplacements
        $name = $file.Name
        Write-Host "  $name ($fileReplacements changes)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "COMPLETE: $filesChanged files | $totalReplacements replacements" -ForegroundColor Green
Write-Host "Backup: $backupPath" -ForegroundColor Gray
Write-Host "Next: npm run dev" -ForegroundColor Cyan
