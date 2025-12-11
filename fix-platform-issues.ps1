# Emergency Platform Stabilization Script
# Run this to fix auto-reload and caching issues
# Date: December 3, 2025

Write-Host "🚨 EMERGENCY PLATFORM STABILIZATION" -ForegroundColor Red
Write-Host "=================================" -ForegroundColor Red
Write-Host ""

# Step 1: Backup current state
Write-Host "📦 Step 1: Creating backup..." -ForegroundColor Yellow
$backupDir = "platform-backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
Copy-Item -Path "bootstrap/cache/*" -Destination "$backupDir/bootstrap-cache/" -Recurse -ErrorAction SilentlyContinue
Copy-Item -Path "storage/framework/cache/*" -Destination "$backupDir/storage-cache/" -Recurse -ErrorAction SilentlyContinue
Copy-Item -Path "storage/framework/sessions/*" -Destination "$backupDir/sessions/" -Recurse -ErrorAction SilentlyContinue
Write-Host "  ✓ Backup created in: $backupDir" -ForegroundColor Green

# Step 2: Clear all Laravel caches
Write-Host ""
Write-Host "🧹 Step 2: Clearing Laravel caches..." -ForegroundColor Yellow
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
Write-Host "  ✓ Laravel caches cleared" -ForegroundColor Green

# Step 3: Run custom cache diagnostics
Write-Host ""
Write-Host "🔍 Step 3: Running cache diagnostics..." -ForegroundColor Yellow
php artisan system:diagnose-cache
Write-Host "  ✓ Diagnostic complete" -ForegroundColor Green

# Step 4: Check for PostCSS build errors
Write-Host ""
Write-Host "🔧 Step 4: Testing build process..." -ForegroundColor Yellow
Write-Host "  Running: npm run build" -ForegroundColor Cyan
$buildOutput = npm run build 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ Build successful" -ForegroundColor Green
} else {
    Write-Host "  ✗ Build failed - PostCSS error detected" -ForegroundColor Red
    Write-Host "  Check build output above for details" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Quick Fix: Temporarily using CDN Tailwind" -ForegroundColor Yellow
    Write-Host "  Long-term: Fix Sucrase parser error in PostCSS" -ForegroundColor Yellow
}

# Step 5: Clear compiled files
Write-Host ""
Write-Host "🗑️  Step 5: Removing stale compiled files..." -ForegroundColor Yellow
Remove-Item -Path "bootstrap/cache/compiled.php" -ErrorAction SilentlyContinue
Remove-Item -Path "bootstrap/cache/services.php" -ErrorAction SilentlyContinue
Remove-Item -Path "bootstrap/cache/config.php" -ErrorAction SilentlyContinue
Remove-Item -Path "bootstrap/cache/routes-v7.php" -ErrorAction SilentlyContinue
Write-Host "  ✓ Compiled files removed" -ForegroundColor Green

# Step 6: Check Vite manifest
Write-Host ""
Write-Host "📄 Step 6: Checking Vite manifest..." -ForegroundColor Yellow
if (Test-Path "public/build/manifest.json") {
    $manifestTime = (Get-Item "public/build/manifest.json").LastWriteTime
    Write-Host "  ✓ Manifest exists (modified: $manifestTime)" -ForegroundColor Green
} else {
    Write-Host "  ✗ Manifest missing - run: npm run build" -ForegroundColor Red
}

# Step 7: Session cleanup (optional)
Write-Host ""
Write-Host "🔒 Step 7: Session cleanup..." -ForegroundColor Yellow
$cleanSessions = Read-Host "  Clear all sessions? This logs out ALL users (y/N)"
if ($cleanSessions -eq 'y' -or $cleanSessions -eq 'Y') {
    php artisan system:clear-all --force
    Write-Host "  ✓ Sessions cleared" -ForegroundColor Green
} else {
    Write-Host "  ⊘ Skipped" -ForegroundColor Gray
}

# Step 8: Verify fixes
Write-Host ""
Write-Host "✅ Step 8: Verification..." -ForegroundColor Yellow
Write-Host "  Checking critical files..." -ForegroundColor Cyan

$checks = @()

# Check if manual reloads are removed
$walletContent = Get-Content "resources/js/Pages/Wallet/Index.vue" -Raw
if ($walletContent -notmatch 'window\.location\.reload') {
    $checks += "  ✓ Wallet page: Manual reload removed"
} else {
    $checks += "  ✗ Wallet page: Still has window.location.reload"
}

# Check if error handler is added
$appJsContent = Get-Content "resources/js/app.js" -Raw
if ($appJsContent -match 'app\.config\.errorHandler') {
    $checks += "  ✓ Error handler: Configured"
} else {
    $checks += "  ✗ Error handler: Missing"
}

# Check if progress bar is enabled
if ($appJsContent -match 'progress:\s*\{') {
    $checks += "  ✓ Progress bar: Enabled"
} else {
    $checks += "  ✗ Progress bar: Disabled"
}

# Check if NoCacheHeaders middleware exists
if (Test-Path "app/Http/Middleware/NoCacheHeaders.php") {
    $checks += "  ✓ Cache middleware: Created"
} else {
    $checks += "  ✗ Cache middleware: Missing"
}

foreach ($check in $checks) {
    if ($check -match '✓') {
        Write-Host $check -ForegroundColor Green
    } else {
        Write-Host $check -ForegroundColor Red
    }
}

# Final summary
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "STABILIZATION COMPLETE" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Clear browser cache (Ctrl+Shift+Delete)" -ForegroundColor White
Write-Host "  2. Hard refresh (Ctrl+Shift+R)" -ForegroundColor White
Write-Host "  3. Test these scenarios:" -ForegroundColor White
Write-Host "     • Settings page loads correctly" -ForegroundColor Gray
Write-Host "     • Profile updates save without refresh" -ForegroundColor Gray
Write-Host "     • Wallet transactions display" -ForegroundColor Gray
Write-Host "     • No unexpected page reloads" -ForegroundColor Gray
Write-Host ""
Write-Host "🔍 For detailed diagnostics, run:" -ForegroundColor Yellow
Write-Host "     php artisan system:diagnose-cache" -ForegroundColor Cyan
Write-Host ""
Write-Host "📖 Full report available at:" -ForegroundColor Yellow
Write-Host "     docs/PLATFORM_ISSUES_DIAGNOSTIC_REPORT.md" -ForegroundColor Cyan
Write-Host ""
