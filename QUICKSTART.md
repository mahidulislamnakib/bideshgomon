# DevOps & QA Automation - Quick Start

Get your production-ready CI/CD pipeline running in 15 minutes.

## 🚀 Quick Install

```powershell
# 1. Install dependencies
composer require sentry/sentry-laravel
composer require --dev phpstan/phpstan
npm install --save-dev @playwright/test husky lint-staged eslint stylelint prettier
npm install @sentry/vue @sentry/tracing
npx playwright install

# 2. Initialize Husky
npx husky install

# 3. Copy environment variables
# Add to .env:
SENTRY_LARAVEL_DSN=your-sentry-dsn
CACHE_DRIVER=redis

# 4. Test everything
composer test
npm run test:e2e
curl http://localhost:8000/api/health
```

## ⚙️ GitHub Setup

### Add Secrets (Settings → Secrets → Actions):

**Required for deployment:**
- `KUBE_CONFIG_STAGING` - Staging Kubernetes config
- `KUBE_CONFIG_PROD` - Production Kubernetes config
- `SLACK_WEBHOOK` - Deployment notifications

**Required for monitoring:**
- `PROMETHEUS_URL` - Metrics server URL
- `INCIDENT_WEBHOOK_URL` - PagerDuty/Opsgenie webhook

**Optional (for testing):**
- `DB_HOST_STAGING`, `DB_USERNAME_STAGING`, `DB_PASSWORD_STAGING`

### Enable Workflows

Push to trigger:
```powershell
git add .
git commit -m "feat: Add DevOps automation"
git push origin main
```

## 📊 Monitoring Setup

### 1. Sentry (5 minutes)

1. Sign up: https://sentry.io
2. Create project: **Laravel** + **Vue**
3. Copy DSN
4. Add to `.env`: `SENTRY_LARAVEL_DSN=your-dsn`
5. Test: `php artisan tinker` → `throw new \Exception('Test')`

### 2. Grafana (5 minutes)

```bash
docker run -d -p 3000:3000 grafana/grafana
```

1. Open http://localhost:3000 (admin/admin)
2. Add data source → Prometheus → http://localhost:9090
3. Import dashboard: `monitoring/grafana-dashboard.json`

### 3. Prometheus (5 minutes)

```bash
docker run -d -p 9090:9090 \
  -v $(pwd)/prometheus.yml:/etc/prometheus/prometheus.yml \
  prom/prometheus
```

Create `prometheus.yml`:
```yaml
scrape_configs:
  - job_name: 'bideshgomon'
    static_configs:
      - targets: ['host.docker.internal:8000']
    metrics_path: '/metrics'
```

## ✅ Verify Installation

### Backend
```powershell
# Tests pass
composer test                        # ✅ Should pass

# Code quality
composer phpstan                     # ✅ Should pass
composer pint --test                 # ✅ Should pass

# Health check
curl http://localhost:8000/api/health  # ✅ Should return 200

# Metrics
curl http://localhost:8000/metrics     # ✅ Should return Prometheus format
```

### Frontend
```powershell
# Build succeeds
npm run build                        # ✅ Should succeed

# Linting
npm run lint:js                      # ✅ Should pass
npm run lint:css                     # ✅ Should pass

# E2E tests
npm run test:e2e                     # ✅ Should pass
```

### CI/CD
- Create a PR → Check GitHub Actions → ✅ All checks should pass
- Merge PR → Check deployment workflow → ✅ Should deploy successfully

## 🎯 First Deployment

### 1. Create Feature Flag

Edit `config/features.php`:
```php
'my_new_feature_20250118' => [
    'enabled' => true,
    'rollout_percentage' => 5,  // Start at 5%
    'user_whitelist' => [1, 2], // Your test user IDs
    'description' => 'My awesome new feature',
    'remove_after' => '2025-02-18',
],
```

### 2. Use in Code

**Laravel:**
```php
if (app(FeatureFlagService::class)->isEnabled('my_new_feature_20250118')) {
    // New code
} else {
    // Old code
}
```

**Vue:**
```vue
<div v-feature="'my_new_feature_20250118'">
  New feature UI
</div>
```

### 3. Deploy

```powershell
git add .
git commit -m "feat: Add my new feature behind flag"
git push origin feature-branch
```

Create PR → Wait for CI → Merge → Automatic deployment!

### 4. Monitor

- **Grafana:** http://localhost:3000
- **Sentry:** https://sentry.io/bideshgomon
- **Prometheus:** http://localhost:9090

### 5. Gradual Rollout

```php
php artisan tinker

# Week 1: 5% (already set)
# Week 2: Increase to 25%
>>> app(\App\Services\FeatureFlagService::class)->setRollout('my_new_feature_20250118', 25);

# Week 3: Increase to 50%
>>> app(\App\Services\FeatureFlagService::class)->setRollout('my_new_feature_20250118', 50);

# Week 4: Enable for everyone
>>> app(\App\Services\FeatureFlagService::class)->enable('my_new_feature_20250118');
```

## 🆘 Troubleshooting

### Pre-commit hook not working?
```powershell
git config core.hooksPath .husky
```

### Playwright can't find browsers?
```powershell
npx playwright install
```

### PHPStan memory error?
```powershell
composer phpstan -- --memory-limit=2G
```

### Health check returns 404?
Clear routes:
```powershell
php artisan route:clear
php artisan optimize:clear
```

### Sentry not capturing errors?
Check DSN:
```powershell
php artisan tinker
>>> config('sentry.dsn')  # Should not be null
```

## 📚 Full Documentation

- **Complete guide:** `docs/DEVOPS_SETUP.md`
- **Deployment runbook:** `docs/RELEASE.md`
- **Implementation details:** `docs/IMPLEMENTATION_COMPLETE.md`

## 🎉 You're Ready!

Your platform now has:
- ✅ Automated testing
- ✅ Safe deployments with rollback
- ✅ Feature flags
- ✅ Comprehensive monitoring
- ✅ Error tracking
- ✅ Pre-commit quality checks

**Deploy with confidence! 🚀**
