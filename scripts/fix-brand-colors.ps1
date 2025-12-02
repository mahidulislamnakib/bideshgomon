# Fix Brand Colors - Replace arbitrary values with Tailwind config colors
# Replace bg-[#e4282b] with bg-brand-red-600 (defined in tailwind.config.js)

Set-Location "c:\xampp\htdocs\bideshgomon"

Write-Host "Fixing brand color classes..." -ForegroundColor Cyan

$files = Get-ChildItem -Path "resources\js\Pages\Admin" -Filter "*.vue" -Recurse
$filesChanged = 0
$totalReplacements = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $original = $content
    $changes = 0
    
    # Replace arbitrary color values with proper Tailwind class names
    $replacements = @{
        'bg-\[#e4282b\]' = 'bg-brand-red-600'
        'text-\[#e4282b\]' = 'text-brand-red-600'
        'border-\[#e4282b\]' = 'border-brand-red-600'
        'focus:border-\[#e4282b\]' = 'focus:border-brand-red-600'
        'focus:ring-\[#e4282b\]' = 'focus:ring-brand-red-600'
        'ring-\[#e4282b\]' = 'ring-brand-red-600'
    }
    
    foreach ($old in $replacements.Keys) {
        $new = $replacements[$old]
        $count = ([regex]::Matches($content, $old)).Count
        if ($count -gt 0) {
            $content = $content -replace $old, $new
            $changes += $count
        }
    }
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $filesChanged++
        $totalReplacements += $changes
        Write-Host "  Fixed: $($file.Name) ($changes)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "COMPLETE: $filesChanged files | $totalReplacements replacements" -ForegroundColor Green
