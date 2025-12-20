# BideshGomon - 360° Error Prevention & Quality Assurance Checklist

**Last Updated:** December 20, 2025  
**Purpose:** Comprehensive error detection and prevention across all platform layers  
**Scope:** Frontend, Backend, Database, Infrastructure, Security, UX, Performance

---

## 🎯 Checklist Overview

This 360° checklist covers **12 critical dimensions**:

1. **Frontend (Vue/Inertia)** - UI/UX errors
2. **Backend (Laravel)** - API/Logic errors
3. **Database** - Data integrity errors
4. **Authentication & Authorization** - Security errors
5. **Forms & Validation** - Input errors
6. **File Uploads & Storage** - Media errors
7. **Email & Notifications** - Communication errors
8. **Payment & Wallet** - Financial errors
9. **Performance & Optimization** - Speed errors
10. **SEO & Accessibility** - Discoverability errors
11. **Testing & Monitoring** - Quality errors
12. **Deployment & DevOps** - Infrastructure errors

---

## 1️⃣ FRONTEND (Vue/Inertia) - 25 Checks

### 1.1 Component Loading Errors

```bash
# Check for broken components
npm run build 2>&1 | Select-String "error|ERROR"

# Check for missing imports
Get-ChildItem -Path resources/js/Pages -Recurse -Filter *.vue | Select-String "import.*from '@/" | ForEach-Object { $_.Line } | Sort-Object -Unique
```

**Manual Checks:**
- [ ] All pages load without console errors
- [ ] No "Component not found" errors
- [ ] No "Cannot read property of undefined" errors
- [ ] Lazy-loaded components work correctly
- [ ] Dynamic imports resolve properly

### 1.2 Inertia.js Errors

```bash
# Check route definitions
php artisan ziggy:generate

# Verify Inertia responses
php artisan route:list | Select-String "Inertia"
```

**Manual Checks:**
- [ ] Inertia::render() returns correct props
- [ ] No missing prop definitions in components
- [ ] Form submissions use Inertia forms
- [ ] Redirect responses work correctly
- [ ] Flash messages display properly
- [ ] Shared data (auth, errors) available everywhere

### 1.3 Vue Reactivity Issues

**Manual Checks:**
- [ ] Computed properties update correctly
- [ ] Watchers trigger when expected
- [ ] v-model bindings work both ways
- [ ] Array mutations trigger re-renders
- [ ] Object property additions are reactive
- [ ] No "Maximum call stack exceeded" errors

### 1.4 UI/UX Errors

**Manual Checks:**
- [ ] No layout shifts during page load
- [ ] Empty states show when data is empty
- [ ] Loading states visible during async operations
- [ ] Error states display helpful messages
- [ ] Success messages confirm actions
- [ ] Modals close properly
- [ ] Dropdowns close on outside click
- [ ] Forms reset after submission
- [ ] Pagination works correctly
- [ ] Search/filter updates results

### 1.5 Bangladesh Localization

```bash
# Check for hardcoded formats
Get-ChildItem -Path resources/js -Recurse -Filter *.vue | Select-String "toLocaleString|number_format|\$[0-9]" -List
```

**Manual Checks:**
- [ ] All currency shows ৳ symbol (not $)
- [ ] All dates use DD/MM/YYYY format
- [ ] Phone numbers use 01XXX-XXXXXX format
- [ ] Time uses 12-hour format with AM/PM
- [ ] useBangladeshFormat composable used consistently

---

## 2️⃣ BACKEND (Laravel) - 30 Checks

### 2.1 Route Errors

```bash
# Check for duplicate routes
php artisan route:list --columns=Method,URI,Name | Group-Object -Property URI | Where-Object { $_.Count -gt 1 }

# Check for missing route names
php artisan route:list | Select-String "Closure" | Measure-Object

# Verify middleware assignment
php artisan route:list --columns=URI,Name,Middleware | Select-String "auth"
```

**Manual Checks:**
- [ ] All routes have names
- [ ] No duplicate route names
- [ ] Middleware applied correctly (auth, role, etc.)
- [ ] API routes use throttling
- [ ] Admin routes require admin role
- [ ] Guest routes accessible without auth

### 2.2 Controller Logic Errors

```bash
# Check for missing auth checks
Get-ChildItem -Path app/Http/Controllers -Recurse -Filter *.php | Select-String "auth\(\)->user\(\)" -List | Measure-Object

# Check for missing validation
Get-ChildItem -Path app/Http/Controllers -Recurse -Filter *.php | Select-String "validate|ValidatesRequests" -List
```

**Manual Checks:**
- [ ] User ownership verified before updates/deletes
- [ ] Authorization checks (Policy/Gate) in place
- [ ] Try-catch blocks for critical operations
- [ ] Proper error messages returned
- [ ] Bangladesh helpers used for formatting
- [ ] Service layer used for business logic
- [ ] No N+1 query problems

### 2.3 Model & Relationship Errors

```bash
# Check for missing fillable/guarded
Get-ChildItem -Path app/Models -Filter *.php | Select-String "class.*extends Model" -Context 0,10 | Select-String "fillable|guarded"

# Verify relationships
php artisan tinker --execute="App\Models\User::first()->load('profile', 'wallet', 'referrals');"
```

**Manual Checks:**
- [ ] Mass assignment protected ($fillable or $guarded)
- [ ] Relationships defined correctly
- [ ] Inverse relationships exist
- [ ] Foreign keys properly named
- [ ] Soft deletes used where appropriate
- [ ] Timestamps enabled ($timestamps = true)
- [ ] Casts defined for JSON/date fields

### 2.4 Service Layer Errors

**Manual Checks:**
- [ ] WalletService wraps operations in transactions
- [ ] ReferralService validates rewards correctly
- [ ] ServiceApplicationService handles files properly
- [ ] Services throw meaningful exceptions
- [ ] Services return consistent data structures

### 2.5 Observer & Event Errors

```bash
# Check observer registration
php artisan tinker --execute="print_r(app('events')->getListeners('eloquent.created: App\\\Models\\\User'));"
```

**Manual Checks:**
- [ ] UserObserver creates wallet on user creation
- [ ] UserObserver generates referral code
- [ ] Events dispatch correctly
- [ ] Listeners don't throw exceptions
- [ ] Queue jobs for heavy listeners

---

## 3️⃣ DATABASE - 20 Checks

### 3.1 Schema Errors

```bash
# Check for missing migrations
php artisan migrate:status

# Verify indexes
php artisan tinker --execute="DB::select('SHOW INDEX FROM users');"
```

**Manual Checks:**
- [ ] All foreign keys have indexes
- [ ] Frequently queried columns indexed
- [ ] No missing migrations
- [ ] Migration rollbacks work
- [ ] Seeds run without errors

### 3.2 Data Integrity Errors

```bash
# Check for orphaned records
php artisan tinker --execute="echo 'Orphaned transactions: ' . App\Models\WalletTransaction::whereDoesntHave('wallet')->count();"

# Verify required data exists
php artisan dev:help analyze
```

**Manual Checks:**
- [ ] No orphaned foreign keys
- [ ] Required reference data seeded (roles, countries)
- [ ] Enum values match database constraints
- [ ] Decimal precision correct for currency
- [ ] Date formats stored correctly
- [ ] JSON columns validate against schema

### 3.3 Query Performance

```bash
# Enable query logging
php artisan tinker --execute="DB::enableQueryLog(); \$user = App\Models\User::with('profile', 'wallet')->first(); echo 'Queries: ' . count(DB::getQueryLog());"
```

**Manual Checks:**
- [ ] No N+1 queries on list pages (< 15 queries)
- [ ] Eager loading used for relationships
- [ ] Pagination used for large datasets
- [ ] Select specific columns when possible
- [ ] Indexes utilized in WHERE clauses

---

## 4️⃣ AUTHENTICATION & AUTHORIZATION - 15 Checks

### 4.1 Auth Flow Errors

```bash
# Test authentication
php artisan tinker --execute="echo 'Admin users: ' . App\Models\User::whereHas('role', fn(\$q) => \$q->where('slug', 'admin'))->count();"
```

**Manual Checks:**
- [ ] Login works with correct credentials
- [ ] Login fails with wrong credentials
- [ ] Registration creates user + wallet + referral code
- [ ] Email verification required
- [ ] Password reset sends email
- [ ] Password reset updates password
- [ ] Logout clears session
- [ ] Remember me checkbox works

### 4.2 Authorization Errors

**Manual Checks:**
- [ ] Guests can't access auth routes
- [ ] Users can't access admin routes
- [ ] Users can only edit their own data
- [ ] Admin can impersonate users
- [ ] Impersonation logs correctly
- [ ] Role checks work (isAdmin(), hasRole())
- [ ] Middleware enforces authorization

---

## 5️⃣ FORMS & VALIDATION - 18 Checks

### 5.1 Form Validation Errors

```bash
# Check validation rules
Get-ChildItem -Path app/Http/Controllers -Recurse -Filter *.php | Select-String "\->validate\(" -Context 0,5
```

**Manual Checks:**
- [ ] All forms have validation rules
- [ ] Required fields marked as required
- [ ] Email validation uses 'email' rule
- [ ] Phone validation uses Bangladesh format
- [ ] NID validation (10 or 17 digits)
- [ ] File uploads validate type/size
- [ ] Unique validation checks database
- [ ] Custom validation messages clear

### 5.2 Form Submission Errors

**Manual Checks:**
- [ ] Form errors display inline
- [ ] CSRF token included
- [ ] Success messages show after submit
- [ ] Form clears after success
- [ ] Loading state prevents double-submit
- [ ] File uploads handle large files
- [ ] Multi-step forms preserve data
- [ ] Draft saving works if applicable

### 5.3 Bangladesh-Specific Validation

**Manual Checks:**
- [ ] NID validates: validate_bd_nid()
- [ ] Phone validates: validate_bd_phone()
- [ ] Currency validates: numeric, min:0
- [ ] Division validates: in:Dhaka,Chittagong,...

---

## 6️⃣ FILE UPLOADS & STORAGE - 12 Checks

### 6.1 Upload Errors

```bash
# Verify storage link
Test-Path public/storage

# Check storage permissions
ls -la storage/app/public
```

**Manual Checks:**
- [ ] Storage link exists (php artisan storage:link)
- [ ] Upload directories writable
- [ ] Max file size enforced (validation + nginx)
- [ ] Allowed file types enforced
- [ ] Files saved to correct directory
- [ ] Filenames sanitized (no special chars)

### 6.2 Storage Management

**Manual Checks:**
- [ ] Old files deleted on update
- [ ] Files deleted when parent record deleted
- [ ] Image URLs accessible publicly
- [ ] PDF generation works
- [ ] CSV export creates valid files
- [ ] Download endpoints secure (ownership check)

---

## 7️⃣ EMAIL & NOTIFICATIONS - 10 Checks

### 7.1 Email Configuration

```bash
# Test mail config
php artisan tinker --execute="echo config('mail.mailers.smtp.host');"
```

**Manual Checks:**
- [ ] MAIL_* variables set in .env
- [ ] Test email sends successfully
- [ ] Queue configured for emails
- [ ] Email templates render correctly
- [ ] Markdown emails styled properly

### 7.2 Notification Errors

**Manual Checks:**
- [ ] In-app notifications save to database
- [ ] Notification bell shows unread count
- [ ] Mark as read works
- [ ] Notification links navigate correctly
- [ ] Email notifications queue properly

---

## 8️⃣ PAYMENT & WALLET - 15 Checks

### 8.1 Wallet Operations

```bash
# Verify wallet integrity
php artisan tinker --execute="echo 'Users without wallets: ' . App\Models\User::doesntHave('wallet')->count();"
```

**Manual Checks:**
- [ ] Every user has a wallet (auto-created)
- [ ] Balance never goes negative
- [ ] Credit operations create transactions
- [ ] Debit operations create transactions
- [ ] Transaction history shows all operations
- [ ] Balance before/after stored correctly

### 8.2 Transaction Errors

**Manual Checks:**
- [ ] All wallet ops wrapped in DB::transaction()
- [ ] Insufficient balance prevents debit
- [ ] Transaction metadata stored correctly
- [ ] Reference types consistent
- [ ] Wallet status checks (active/suspended)
- [ ] Currency always BDT
- [ ] Amount precision correct (2 decimals)

### 8.3 Refund Logic

**Manual Checks:**
- [ ] Cancellation fees calculate correctly
- [ ] Refunds credit wallet properly
- [ ] Refund transactions reference original

---

## 9️⃣ PERFORMANCE & OPTIMIZATION - 12 Checks

### 9.1 Page Load Performance

```bash
# Check build performance
Measure-Command { npm run build }

# Analyze bundle size
ls -lh public/build/assets/*.js | Sort-Object Length -Descending | Select-Object -First 10
```

**Manual Checks:**
- [ ] Build time < 15 seconds
- [ ] Total bundle < 500KB gzipped
- [ ] Images lazy loaded
- [ ] Components code-split
- [ ] Critical CSS inlined
- [ ] Fonts optimized

### 9.2 Server Performance

**Manual Checks:**
- [ ] Homepage loads < 1 second
- [ ] Dashboard loads < 2 seconds
- [ ] Database queries < 20 per page
- [ ] Cache hit rate > 80%
- [ ] Memory usage stable
- [ ] No memory leaks

---

## 🔟 SEO & ACCESSIBILITY - 10 Checks

### 10.1 SEO Errors

**Manual Checks:**
- [ ] Page titles unique and descriptive
- [ ] Meta descriptions present
- [ ] Open Graph tags for social sharing
- [ ] Sitemap.xml generated
- [ ] Robots.txt configured
- [ ] Canonical URLs set

### 10.2 Accessibility

**Manual Checks:**
- [ ] Images have alt text
- [ ] Forms have labels
- [ ] Buttons have aria-labels
- [ ] Color contrast meets WCAG AA
- [ ] Keyboard navigation works
- [ ] Screen reader compatible

---

## 1️⃣1️⃣ TESTING & MONITORING - 15 Checks

### 11.1 Test Coverage

```bash
# Run test suite
php artisan dev:help test

# Check test count
Get-ChildItem -Path tests -Recurse -Filter *Test.php | Measure-Object
```

**Manual Checks:**
- [ ] Authentication tests pass
- [ ] Profile tests pass
- [ ] Wallet operation tests exist
- [ ] API endpoint tests exist
- [ ] Feature test coverage > 60%

### 11.2 Error Monitoring

```bash
# Check error logs
Get-Content storage/logs/laravel.log -Tail 100 | Select-String "ERROR|CRITICAL"
```

**Manual Checks:**
- [ ] Laravel logs errors
- [ ] Sentry configured (production)
- [ ] Error pages styled (404, 500, 403)
- [ ] Error tracking dashboard accessible
- [ ] Critical errors alert admins

### 11.3 Health Monitoring

```bash
# Test health endpoint
php artisan tinker --execute="echo app(\App\Http\Controllers\HealthCheckController::class)->index()->status();"
```

**Manual Checks:**
- [ ] Health check endpoint works
- [ ] Database connection monitored
- [ ] Queue workers monitored
- [ ] Disk space monitored
- [ ] Uptime tracking configured

---

## 1️⃣2️⃣ DEPLOYMENT & DEVOPS - 12 Checks

### 12.1 Environment Configuration

```bash
# Verify env variables
php artisan tinker --execute="echo 'APP_ENV: ' . config('app.env') . PHP_EOL; echo 'APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false');"
```

**Manual Checks:**
- [ ] .env.example up to date
- [ ] APP_DEBUG=false in production
- [ ] APP_KEY set (32 characters)
- [ ] Database credentials secure
- [ ] API keys not in code
- [ ] HTTPS enforced in production

### 12.2 Deployment Process

**Manual Checks:**
- [ ] Deployment script tested
- [ ] Zero-downtime deployment
- [ ] Database migrations run automatically
- [ ] Assets compiled before deploy
- [ ] Cache cleared after deploy
- [ ] Rollback plan documented

### 12.3 Backup & Recovery

```bash
# Verify backup exists
ls -la storage/app/backups | Sort-Object LastWriteTime -Descending | Select-Object -First 5
```

**Manual Checks:**
- [ ] Daily database backups
- [ ] Backup restoration tested
- [ ] File storage backed up
- [ ] Backup retention policy (30 days)
- [ ] Disaster recovery plan documented

---

## 🚨 CRITICAL ERROR SCENARIOS

### Scenario 1: User Can't Login
**Checklist:**
- [ ] Database connection working?
- [ ] User exists in database?
- [ ] Password correct? (reset to test)
- [ ] Session driver configured?
- [ ] Cookies enabled in browser?
- [ ] CSRF token valid?

### Scenario 2: Payment Fails
**Checklist:**
- [ ] Wallet exists for user?
- [ ] Wallet status is active?
- [ ] Sufficient balance?
- [ ] Transaction wrapped in DB::transaction()?
- [ ] Error logged?
- [ ] User notified of failure?

### Scenario 3: Email Not Sending
**Checklist:**
- [ ] MAIL_* variables set?
- [ ] Queue worker running?
- [ ] Job not failed?
- [ ] Email address valid?
- [ ] Mail server accessible?
- [ ] Rate limit not exceeded?

### Scenario 4: File Upload Fails
**Checklist:**
- [ ] Storage link exists?
- [ ] Directory writable?
- [ ] File size within limit?
- [ ] File type allowed?
- [ ] Disk space available?
- [ ] PHP upload_max_filesize sufficient?

### Scenario 5: Page Shows 0 Services
**Checklist:**
- [ ] Services seeded in database?
- [ ] Services marked as active?
- [ ] service_type = 'revenue_service'?
- [ ] Categories exist and linked?
- [ ] No filters hiding results?
- [ ] Query returns data in controller?

---

## 🔧 AUTOMATED ERROR CHECKING SCRIPT

```powershell
# Save as: check-errors.ps1

Write-Host "🔍 BideshGomon 360° Error Check" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# 1. Check Build
Write-Host "1️⃣  Checking Build..." -ForegroundColor Yellow
$buildResult = npm run build 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Build successful" -ForegroundColor Green
} else {
    Write-Host "   ❌ Build failed" -ForegroundColor Red
    $buildResult | Select-String "error"
}

# 2. Check Database Connection
Write-Host "2️⃣  Checking Database..." -ForegroundColor Yellow
$dbCheck = php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Connected'; } catch (\Exception \$e) { echo 'Failed: ' . \$e->getMessage(); }"
if ($dbCheck -like "*Connected*") {
    Write-Host "   ✅ Database connected" -ForegroundColor Green
} else {
    Write-Host "   ❌ Database connection failed" -ForegroundColor Red
}

# 3. Check Active Services
Write-Host "3️⃣  Checking Services..." -ForegroundColor Yellow
$serviceCount = php artisan tinker --execute="echo App\Models\ServiceModule::where('is_active', true)->where('service_type', 'revenue_service')->count();"
Write-Host "   📊 Active services: $serviceCount" -ForegroundColor Cyan
if ([int]$serviceCount -gt 0) {
    Write-Host "   ✅ Services available" -ForegroundColor Green
} else {
    Write-Host "   ❌ No active services" -ForegroundColor Red
}

# 4. Check Storage Link
Write-Host "4️⃣  Checking Storage..." -ForegroundColor Yellow
if (Test-Path "public/storage") {
    Write-Host "   ✅ Storage link exists" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Storage link missing (run: php artisan storage:link)" -ForegroundColor Yellow
}

# 5. Check Logs for Errors
Write-Host "5️⃣  Checking Logs..." -ForegroundColor Yellow
$recentErrors = Get-Content storage/logs/laravel.log -Tail 100 -ErrorAction SilentlyContinue | Select-String "ERROR|CRITICAL"
if ($recentErrors) {
    Write-Host "   ⚠️  Found errors in logs:" -ForegroundColor Yellow
    $recentErrors | Select-Object -First 5 | ForEach-Object { Write-Host "      $_" -ForegroundColor Red }
} else {
    Write-Host "   ✅ No recent errors" -ForegroundColor Green
}

# 6. Check Environment
Write-Host "6️⃣  Checking Environment..." -ForegroundColor Yellow
$appEnv = php artisan tinker --execute="echo config('app.env');"
$appDebug = php artisan tinker --execute="echo config('app.debug') ? 'true' : 'false';"
Write-Host "   📊 Environment: $appEnv" -ForegroundColor Cyan
Write-Host "   📊 Debug mode: $appDebug" -ForegroundColor Cyan
if ($appEnv -eq "production" -and $appDebug -eq "true") {
    Write-Host "   ⚠️  Debug mode enabled in production!" -ForegroundColor Red
} else {
    Write-Host "   ✅ Environment configured correctly" -ForegroundColor Green
}

# 7. Run Health Check
Write-Host "7️⃣  Running Health Check..." -ForegroundColor Yellow
php artisan dev:help analyze

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "✅ 360° Error Check Complete!" -ForegroundColor Green
```

**Usage:**
```powershell
# Make script executable
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

# Run check
.\check-errors.ps1
```

---

## 📋 DAILY CHECKLIST

### Morning Routine (5 minutes)
```bash
# 1. Check overnight errors
Get-Content storage/logs/laravel.log -Tail 100 | Select-String "ERROR|CRITICAL"

# 2. Run health check
php artisan dev:help analyze

# 3. Check queue
php artisan queue:failed

# 4. Verify services active
php artisan tinker --execute="echo App\Models\ServiceModule::where('is_active', true)->count();"

# 5. Quick build test
npm run build
```

### Before Deployment (10 minutes)
```bash
# 1. Run all tests
php artisan dev:help test

# 2. Build assets
npm run build

# 3. Check for console errors (manual)
# Open browser, check console on key pages

# 4. Verify env config
php artisan config:cache

# 5. Database backup
php artisan backup:run --only-db

# 6. Clear caches
php artisan dev:help clear

# 7. Optimize
php artisan dev:help optimize
```

### Weekly Deep Check (30 minutes)
```bash
# 1. Run full automated check
.\check-errors.ps1

# 2. Review all 12 dimensions
# Go through each section above

# 3. Check performance metrics
# Use browser DevTools, Lighthouse

# 4. Security audit
composer audit
npm audit

# 5. Database optimization
php artisan db:optimize

# 6. Update dependencies (if needed)
composer update --dry-run
npm outdated
```

---

## 🎯 ERROR PRIORITY MATRIX

### 🔴 P0 - Critical (Fix Immediately)
- Database connection down
- Payment processing broken
- Authentication not working
- Site completely inaccessible
- Data loss occurring

### 🟠 P1 - High (Fix Same Day)
- Email sending failing
- File uploads broken
- Major features not working
- Performance severely degraded
- Security vulnerabilities

### 🟡 P2 - Medium (Fix This Week)
- UI bugs affecting UX
- Non-critical features broken
- Performance issues
- Minor validation errors
- Accessibility issues

### 🟢 P3 - Low (Fix When Possible)
- Cosmetic issues
- Edge case bugs
- Nice-to-have features
- Documentation gaps
- Code cleanup

---

## 📞 ERROR RESPONSE PLAN

### 1. Detect
- Monitor logs continuously
- Set up alerts for critical errors
- Check health endpoint every 5 minutes
- User reports via support

### 2. Diagnose
- Check recent deployments
- Review error logs
- Test affected functionality
- Identify root cause

### 3. Fix
- Rollback if deployment-related
- Apply hotfix if code issue
- Restart services if infrastructure issue
- Document the fix

### 4. Prevent
- Add test for the scenario
- Update error handling
- Improve monitoring
- Document in runbook

---

## ✅ QUALITY GATES

### Before Merge to Master
- [ ] All tests pass
- [ ] Build successful
- [ ] No console errors
- [ ] Code reviewed
- [ ] Documentation updated

### Before Deployment
- [ ] All quality gates passed
- [ ] Backup completed
- [ ] Rollback plan ready
- [ ] Deployment checklist completed
- [ ] Stakeholders notified

### After Deployment
- [ ] Health check passes
- [ ] Key features tested
- [ ] Performance acceptable
- [ ] No error spikes
- [ ] Monitoring active

---

## 📚 REFERENCE COMMANDS

```bash
# Quick Error Checks
php artisan dev:help analyze                    # Overall health
Get-Content storage/logs/laravel.log -Tail 50   # Recent logs
php artisan queue:failed                        # Failed jobs
php artisan route:list | Select-String "error"  # Route issues

# Database Checks
php artisan tinker --execute="DB::connection()->getPdo();"  # Connection
php artisan migrate:status                                   # Migrations
php artisan db:seed --class=TestDataSeeder                  # Test data

# Performance Checks
Measure-Command { npm run build }               # Build time
php artisan optimize                            # Optimize app
php artisan config:cache                        # Cache config

# Security Checks
composer audit                                  # PHP vulnerabilities
npm audit                                       # JS vulnerabilities
php artisan route:list --columns=URI,Middleware # Auth coverage
```

---

## 🎓 ERROR PREVENTION BEST PRACTICES

### 1. Always Test Before Commit
```bash
php artisan test
npm run build
```

### 2. Use Type Hints
```php
public function credit(Wallet $wallet, float $amount): WalletTransaction
```

### 3. Validate Everything
```php
$validated = $request->validate([
    'amount' => 'required|numeric|min:0',
]);
```

### 4. Wrap Transactions
```php
DB::transaction(function () use ($wallet, $amount) {
    // Critical operations
});
```

### 5. Log Errors Properly
```php
Log::error('Payment failed', [
    'wallet_id' => $wallet->id,
    'amount' => $amount,
    'error' => $e->getMessage(),
]);
```

### 6. Handle Errors Gracefully
```php
try {
    $result = riskyOperation();
} catch (\Exception $e) {
    Log::error('Operation failed', ['error' => $e->getMessage()]);
    return back()->with('error', 'Operation failed. Please try again.');
}
```

---

## 🏁 SUCCESS CRITERIA

Platform is error-free when:

- ✅ **Build**: < 15s, 0 errors
- ✅ **Tests**: 100% pass rate
- ✅ **Logs**: No ERROR/CRITICAL in last 24h
- ✅ **Performance**: All pages < 2s load
- ✅ **Uptime**: 99.9% availability
- ✅ **Database**: 0 orphaned records
- ✅ **Security**: 0 vulnerabilities
- ✅ **UX**: 0 broken features
- ✅ **Data**: 100% integrity
- ✅ **Monitoring**: All systems green

---

**Last Review:** December 20, 2025  
**Next Review:** January 20, 2026  
**Owner:** Development Team  
**Status:** 🟢 Active
