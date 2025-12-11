# Development Environment Reset Script
# This script clears all caches and prepares a clean development environment

Write-Host ""
Write-Host "🔄 Resetting Development Environment..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Clear all Laravel caches
Write-Host "📦 Clearing Laravel caches..." -ForegroundColor Yellow
try {
    php artisan system:clear-all --force
    Write-Host "  ✅ Laravel caches cleared" -ForegroundColor Green
} catch {
    Write-Host "  ⚠️  system:clear-all failed, trying individual commands..." -ForegroundColor Yellow
    php artisan config:clear
    php artisan route:clear
    php artisan cache:clear
    php artisan view:clear
    Write-Host "  ✅ Laravel caches cleared individually" -ForegroundColor Green
}

Write-Host ""

# Clear Vite cache
Write-Host "⚡ Clearing Vite caches..." -ForegroundColor Yellow
Remove-Item "node_modules/.vite" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "  ✅ Vite dependency cache cleared" -ForegroundColor Green

# Clear build artifacts
Remove-Item "public/build" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item "public/hot" -Force -ErrorAction SilentlyContinue
Write-Host "  ✅ Build artifacts cleared" -ForegroundColor Green

Write-Host ""

# Regenerate Ziggy routes
Write-Host "🗺️  Regenerating Ziggy routes..." -ForegroundColor Yellow
php artisan ziggy:generate
Write-Host "  ✅ Ziggy routes regenerated" -ForegroundColor Green

Write-Host ""

# Kill stuck Node processes
Write-Host "🔪 Killing stuck Node/Vite processes..." -ForegroundColor Yellow
$nodeProcesses = Get-Process | Where-Object {$_.ProcessName -like "*node*"} -ErrorAction SilentlyContinue
if ($nodeProcesses) {
    $nodeProcesses | Stop-Process -Force -ErrorAction SilentlyContinue
    Write-Host "  ✅ Killed $($nodeProcesses.Count) Node process(es)" -ForegroundColor Green
} else {
    Write-Host "  ℹ️  No Node processes to kill" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ Environment Reset Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "📌 Next Steps:" -ForegroundColor Cyan
Write-Host "  1. Run: npm run dev" -ForegroundColor White
Write-Host "  2. Run: php artisan serve (in another terminal)" -ForegroundColor White
Write-Host "  3. Hard refresh browser: Ctrl+Shift+R" -ForegroundColor White
Write-Host ""
