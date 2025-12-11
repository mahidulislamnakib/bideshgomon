# DevOps & QA Automation Setup Guide

This guide walks you through setting up the complete DevOps and QA automation infrastructure for the BideshGomon platform.

## 📦 Package Installation

### 1. Install PHP Dependencies

```powershell
# Sentry for error tracking
composer require sentry/sentry-laravel

# PHPStan for static analysis
composer require --dev phpstan/phpstan

# Laravel Pint (already included, but verify)
composer require --dev laravel/pint

# Optional: Prometheus client (if using PHP metrics)
composer require promphp/prometheus_client_php
```

### 2. Install Node.js Dependencies

```powershell
# Playwright for E2E testing
npm install --save-dev @playwright/test

# Husky for pre-commit hooks
npm install --save-dev husky

# Lint-staged for running linters on staged files
npm install --save-dev lint-staged

# ESLint and plugins (if not already installed)
npm install --save-dev eslint eslint-plugin-vue

# Stylelint for CSS linting
npm install --save-dev stylelint stylelint-config-standard

# Prettier for code formatting
npm install --save-dev prettier

# Sentry for Vue error tracking
npm install @sentry/vue @sentry/tracing
```

### 3. Install Playwright Browsers

```powershell
npx playwright install
```

---

## ⚙️ Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# Sentry Configuration
SENTRY_LARAVEL_DSN=https://your-key@sentry.io/your-project-id
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1

# Feature Flags
CACHE_DRIVER=redis  # Required for feature flag caching

# Prometheus (if self-hosting)
PROMETHEUS_URL=http://prometheus:9090

# Deployment
APP_URL=http://localhost:8000  # Or production URL
```

### 2. GitHub Secrets

Add these secrets to your GitHub repository (Settings → Secrets → Actions):

**Kubernetes:**
- `KUBE_CONFIG_STAGING`: Base64-encoded kubeconfig for staging
- `KUBE_CONFIG_PROD`: Base64-encoded kubeconfig for production

**Database:**
- `DB_HOST_STAGING`: Staging database host
- `DB_USERNAME_STAGING`: Staging database username
- `DB_PASSWORD_STAGING`: Staging database password

**Monitoring:**
- `PROMETHEUS_URL`: Prometheus server URL
- `SENTRY_DSN`: Sentry DSN for error tracking

**Notifications:**
- `SLACK_WEBHOOK`: Slack webhook URL for deployment notifications
- `INCIDENT_WEBHOOK_URL`: PagerDuty/Opsgenie webhook for incidents

**Container Registry:**
- GitHub Actions automatically has access to GHCR (GitHub Container Registry)

### 3. Laravel Configuration

**Publish Sentry configuration:**
```powershell
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

**Configure Sentry in `config/sentry.php`:**
The file is already created. Just verify the DSN is set correctly.

**Configure Feature Flags:**
Review and customize `config/features.php` with your features.

### 4. Vue/Vite Configuration

**Initialize Sentry in your main Vue file:**

Edit `resources/js/app.js` or `resources/js/app.ts`:

```javascript
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { initSentry } from './Utils/sentry'

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
    
    // Initialize Sentry
    initSentry(app, null, {
      dsn: import.meta.env.VITE_SENTRY_DSN,
      environment: import.meta.env.MODE,
      enabled: import.meta.env.PROD,
    })
    
    app.mount(el)
  },
})
```

Add to `.env`:
```env
VITE_SENTRY_DSN=https://your-key@sentry.io/your-project-id
```

### 5. Husky Setup

**Initialize Husky:**
```powershell
npx husky install
```

**Make pre-commit hook executable (Linux/Mac only):**
```bash
chmod +x .husky/pre-commit
```

**On Windows:** The hook will work automatically.

### 6. Create ESLint Configuration

Create `.eslintrc.json`:
```json
{
  "env": {
    "browser": true,
    "es2021": true
  },
  "extends": [
    "eslint:recommended",
    "plugin:vue/vue3-recommended"
  ],
  "parserOptions": {
    "ecmaVersion": 12,
    "sourceType": "module"
  },
  "rules": {
    "vue/multi-word-component-names": "off",
    "no-console": "warn",
    "no-debugger": "error"
  }
}
```

### 7. Create Stylelint Configuration

Create `.stylelintrc.json`:
```json
{
  "extends": "stylelint-config-standard",
  "rules": {
    "at-rule-no-unknown": [
      true,
      {
        "ignoreAtRules": ["tailwind", "apply", "layer", "variants", "responsive", "screen"]
      }
    ]
  }
}
```

### 8. Create Prettier Configuration

Create `.prettierrc`:
```json
{
  "semi": false,
  "singleQuote": true,
  "tabWidth": 2,
  "trailingComma": "es5",
  "printWidth": 100
}
```

### 9. PHPStan Configuration

Create `phpstan.neon`:
```neon
parameters:
    level: 5
    paths:
        - app
    excludePaths:
        - app/Console/Kernel.php
    ignoreErrors:
        - '#Undefined variable: \$this#'
    checkMissingIterableValueType: false
```

---

## 🧪 Testing Setup

### 1. Create Test Fixtures

Create `tests/fixtures/sample-passport.pdf`:
- This is a dummy PDF file for E2E upload tests
- You can create a blank PDF or use a test document

### 2. Update PHPUnit Configuration

Verify `phpunit.xml` has these test suites:
```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
</testsuites>
```

### 3. Run Tests Locally

```powershell
# PHP tests
composer test

# Or individual suites
composer test:unit
composer test:feature

# E2E tests
npm run test:e2e

# E2E with UI
npm run test:e2e:ui
```

---

## 🚀 CI/CD Setup

### 1. Verify GitHub Actions Workflows

Check that these files exist:
- `.github/workflows/ci.yml`
- `.github/workflows/cd.yml`

### 2. Configure Kubernetes (If Using K8s)

**Apply Kubernetes manifests:**
```bash
# Apply to staging
kubectl apply -f k8s/canary-deployment.yml --namespace=staging

# Apply to production
kubectl apply -f k8s/canary-deployment.yml --namespace=production
kubectl apply -f k8s/traffic-split.yml --namespace=production
```

### 3. Test CI Pipeline

Push a branch and create a pull request:
```powershell
git checkout -b test-ci
git add .
git commit -m "test: CI pipeline"
git push origin test-ci
```

Open a PR on GitHub and verify all checks pass.

### 4. Test CD Pipeline

Merge to main:
```powershell
git checkout main
git merge test-ci
git push origin main
```

Watch the deployment in GitHub Actions.

---

## 📊 Monitoring Setup

### 1. Sentry Setup

1. Sign up at https://sentry.io
2. Create a new project (Laravel + Vue)
3. Copy the DSN
4. Add to `.env` and GitHub secrets
5. Test error tracking:
   ```php
   throw new \Exception('Test Sentry integration');
   ```

### 2. Prometheus Setup (Optional)

**If self-hosting Prometheus:**

Create `prometheus.yml`:
```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'bideshgomon'
    static_configs:
      - targets: ['localhost:8000']
    metrics_path: '/metrics'
```

**Run Prometheus:**
```bash
docker run -d -p 9090:9090 \
  -v $(pwd)/prometheus.yml:/etc/prometheus/prometheus.yml \
  prom/prometheus
```

### 3. Grafana Setup

**Run Grafana:**
```bash
docker run -d -p 3000:3000 grafana/grafana
```

**Import Dashboard:**
1. Login to Grafana (admin/admin)
2. Add Prometheus data source (http://localhost:9090)
3. Import `monitoring/grafana-dashboard.json`

### 4. Test Metrics Endpoint

```powershell
curl http://localhost:8000/metrics
```

Should return Prometheus-formatted metrics.

---

## ✅ Verification Checklist

### Backend

- [ ] `composer test` passes
- [ ] `composer phpstan` passes
- [ ] `composer pint --test` passes
- [ ] Health check works: `curl http://localhost:8000/api/health`
- [ ] Metrics endpoint works: `curl http://localhost:8000/metrics`
- [ ] Sentry captures errors (test with thrown exception)

### Frontend

- [ ] `npm run build` succeeds
- [ ] `npm run lint:js` passes
- [ ] `npm run lint:css` passes
- [ ] `npm run test:e2e` passes
- [ ] Sentry captures Vue errors

### CI/CD

- [ ] GitHub Actions CI workflow runs on PR
- [ ] All CI checks pass
- [ ] GitHub Actions CD workflow runs on merge to main
- [ ] Deployment succeeds (or would succeed with proper infra)

### Monitoring

- [ ] Sentry project created and DSN configured
- [ ] Grafana dashboard displays data
- [ ] Prometheus scrapes metrics
- [ ] Alerts configured (if using Prometheus Alertmanager)

### Feature Flags

- [ ] Feature flags work in Laravel: `app(FeatureFlagService::class)->isEnabled('test')`
- [ ] Feature flags work in Vue: `isEnabled('test')`
- [ ] Cache invalidation works: `php artisan cache:clear`

---

## 🐛 Troubleshooting

### Issue: Playwright tests fail with "browser not found"

**Solution:**
```powershell
npx playwright install
```

### Issue: Pre-commit hook doesn't run

**Solution (Linux/Mac):**
```bash
chmod +x .husky/pre-commit
```

**Solution (Windows):**
Ensure Git Bash is installed and hooks are enabled:
```powershell
git config core.hooksPath .husky
```

### Issue: PHPStan memory limit exceeded

**Solution:**
```powershell
composer phpstan -- --memory-limit=2G
```

Or update `phpstan.neon`:
```neon
parameters:
    tmpDir: storage/phpstan
```

### Issue: Metrics endpoint returns 404

**Solution:**
Ensure route is registered in `routes/api.php`:
```php
Route::get('/metrics', [MetricsController::class, '__invoke'])
    ->name('api.metrics');
```

### Issue: Sentry not capturing errors

**Solution:**
1. Check DSN is correct in `.env`
2. Verify `SENTRY_LARAVEL_DSN` is set
3. Test with:
   ```php
   Sentry\captureMessage('Test message');
   ```

### Issue: CI fails with "secrets not found"

**Solution:**
Add secrets to GitHub repository settings. For testing, you can comment out jobs that require infrastructure (e.g., deploy-canary).

---

## 📚 Next Steps

1. **Customize Alerts:** Edit `monitoring/prometheus-alerts.yml` with your thresholds
2. **Add More Tests:** Expand `tests/e2e/critical-paths.spec.ts` with more scenarios
3. **Configure Notifications:** Set up Slack/PagerDuty webhooks
4. **Review Feature Flags:** Plan your feature rollout strategy
5. **Schedule Postmortems:** Set up regular incident review meetings
6. **Update Documentation:** Keep `docs/RELEASE.md` current with your processes

---

## 🆘 Support

**Internal Documentation:**
- Release Runbook: `docs/RELEASE.md`
- Architecture: `docs/ARCHITECTURE.md` (if exists)
- Copilot Instructions: `.github/copilot-instructions.md`

**External Resources:**
- Sentry Docs: https://docs.sentry.io/platforms/php/guides/laravel/
- Playwright Docs: https://playwright.dev/
- Prometheus Docs: https://prometheus.io/docs/
- Grafana Docs: https://grafana.com/docs/

**Team Contacts:**
- Platform Team: #platform (Slack)
- DevOps: #devops (Slack)
- On-Call: See `docs/RELEASE.md`

---

**Setup Complete! 🎉**

You now have a production-ready CI/CD pipeline with:
- ✅ Automated testing (unit, integration, E2E)
- ✅ Canary deployments with automatic rollback
- ✅ Feature flags for gradual rollouts
- ✅ Comprehensive monitoring and alerting
- ✅ Error tracking with Sentry
- ✅ Pre-commit hooks for code quality
- ✅ Automated dependency updates

**Happy deploying! 🚀**
