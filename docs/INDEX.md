# 📚 DevOps & QA Documentation Index

**Quick Links to All Documentation**

## 🚀 Getting Started

- **[QUICKSTART.md](../QUICKSTART.md)** - Get running in 15 minutes
- **[DEVOPS_COMPLETE.md](../DEVOPS_COMPLETE.md)** - Complete implementation summary
- **[DEVOPS_SETUP.md](DEVOPS_SETUP.md)** - Full installation guide

## 🔧 Setup Guides

- **[GITHUB_SECRETS.md](GITHUB_SECRETS.md)** - Configure GitHub Actions secrets
- **[E2E Testing Guide](../tests/e2e/README.md)** - Playwright E2E test documentation
- **[.env.example](../.env.example)** - Environment configuration (updated with Sentry)

## 📖 Operations

- **[RELEASE.md](RELEASE.md)** - Comprehensive deployment runbook
  - Pre-deployment checklist
  - Deployment process (automatic, manual, blue-green)
  - Feature flag guidelines
  - Monitoring & observability
  - Rollback procedures
  - Emergency response
  - Postmortem process

## 🏗️ Architecture

### CI/CD Workflows
- **[.github/workflows/ci.yml](../.github/workflows/ci.yml)** - Pre-merge checks
- **[.github/workflows/cd.yml](../.github/workflows/cd.yml)** - Deployment pipeline
- **[.github/dependabot.yml](../.github/dependabot.yml)** - Dependency updates

### Kubernetes
- **[k8s/canary-deployment.yml](../k8s/canary-deployment.yml)** - Canary deployment
- **[k8s/traffic-split.yml](../k8s/traffic-split.yml)** - Istio traffic routing

### Deployment Scripts
- **[scripts/deploy-blue-green.sh](../scripts/deploy-blue-green.sh)** - Blue-green deployment

## 🧪 Testing

### Test Files
- **[tests/Unit/FeatureFlagServiceTest.php](../tests/Unit/FeatureFlagServiceTest.php)** - Feature flag unit tests
- **[tests/Feature/HealthCheckTest.php](../tests/Feature/HealthCheckTest.php)** - Health check feature tests
- **[tests/e2e/critical-paths.spec.ts](../tests/e2e/critical-paths.spec.ts)** - E2E test suite

### Test Configuration
- **[playwright.config.ts](../playwright.config.ts)** - Playwright configuration
- **[phpunit.xml](../phpunit.xml)** - PHPUnit configuration

### Test Fixtures
- **[tests/fixtures/sample-passport.pdf](../tests/fixtures/sample-passport.pdf)** - Test document

## 📊 Monitoring

### Configuration
- **[monitoring/grafana-dashboard.json](../monitoring/grafana-dashboard.json)** - Pre-configured dashboard
- **[monitoring/prometheus-alerts.yml](../monitoring/prometheus-alerts.yml)** - 11 alert rules
- **[config/sentry.php](../config/sentry.php)** - Sentry error tracking

### Controllers
- **[app/Http/Controllers/HealthCheckController.php](../app/Http/Controllers/HealthCheckController.php)** - Health endpoint
- **[app/Http/Controllers/MetricsController.php](../app/Http/Controllers/MetricsController.php)** - Prometheus metrics

## 🚩 Feature Flags

- **[app/Services/FeatureFlagService.php](../app/Services/FeatureFlagService.php)** - Feature flag service
- **[config/features.php](../config/features.php)** - Feature flag definitions
- **[resources/js/Composables/useFeatureFlags.ts](../resources/js/Composables/useFeatureFlags.ts)** - Vue composable

## 🛠️ Development Tools

### Linting & Formatting
- **[.eslintrc.json](../.eslintrc.json)** - ESLint configuration
- **[.stylelintrc.json](../.stylelintrc.json)** - Stylelint configuration
- **[.prettierrc](../.prettierrc)** - Prettier configuration
- **[phpstan.neon](../phpstan.neon)** - PHPStan configuration

### Pre-commit Hooks
- **[.husky/pre-commit](../.husky/pre-commit)** - Pre-commit hook script

### Package Scripts
- **[package.json](../package.json)** - NPM scripts (lint, test, format)
- **[composer.json](../composer.json)** - Composer scripts (phpstan, pint, test)

## 📝 Additional Documentation

- **[IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md)** - Full implementation details
- **[MASTER_ROADMAP.md](MASTER_ROADMAP.md)** - Platform roadmap
- **[DESIGN_SYSTEM.md](DESIGN_SYSTEM.md)** - Design system
- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Deployment guide

## 🔍 Quick Reference

### Common Commands

```bash
# Development
php artisan serve
npm run dev

# Testing
composer test
npm run test:e2e
npm run test:e2e:ui

# Code Quality
composer phpstan
composer pint
npm run lint:js
npm run lint:css

# Health Check
curl http://localhost:8000/api/health

# Metrics
curl http://localhost:8000/metrics

# Build
npm run build

# Clear Cache
php artisan optimize:clear
```

### Important URLs

- **Health Check:** `/api/health`
- **Metrics:** `/metrics`
- **Grafana:** `https://grafana.bideshgomon.com/d/bideshgomon-prod`
- **Sentry:** `https://sentry.io/bideshgomon`
- **Prometheus:** `https://prometheus.bideshgomon.com`

### GitHub Secrets Required

See **[GITHUB_SECRETS.md](GITHUB_SECRETS.md)** for complete list:
- `KUBE_CONFIG_STAGING` / `KUBE_CONFIG_PROD`
- `DB_HOST_STAGING` / `DB_USERNAME_STAGING` / `DB_PASSWORD_STAGING`
- `PROMETHEUS_URL`
- `SLACK_WEBHOOK`
- `INCIDENT_WEBHOOK_URL`
- `SENTRY_DSN`

## 🎯 By Role

### For Developers
1. [QUICKSTART.md](../QUICKSTART.md) - Get started quickly
2. [E2E Testing Guide](../tests/e2e/README.md) - Write E2E tests
3. [Feature Flags](../config/features.php) - Use feature flags
4. [.env.example](../.env.example) - Environment setup

### For DevOps Engineers
1. [DEVOPS_SETUP.md](DEVOPS_SETUP.md) - Complete setup
2. [GITHUB_SECRETS.md](GITHUB_SECRETS.md) - Configure secrets
3. [CI/CD Workflows](../.github/workflows/) - Pipeline configuration
4. [Kubernetes Manifests](../k8s/) - Deployment specs

### For Operations Team
1. [RELEASE.md](RELEASE.md) - Deployment runbook
2. [Monitoring Dashboard](../monitoring/grafana-dashboard.json) - Grafana setup
3. [Alert Rules](../monitoring/prometheus-alerts.yml) - Prometheus alerts
4. [Health Checks](../app/Http/Controllers/HealthCheckController.php) - System health

### For QA Team
1. [E2E Testing Guide](../tests/e2e/README.md) - Test automation
2. [Test Fixtures](../tests/fixtures/) - Test data
3. [Playwright Config](../playwright.config.ts) - Test configuration

### For Product Managers
1. [Feature Flags](../config/features.php) - Feature rollout control
2. [Monitoring Dashboard](../monitoring/grafana-dashboard.json) - Performance metrics
3. [RELEASE.md](RELEASE.md) - Deployment process understanding

## 📞 Support

### Internal Channels
- **Platform Team:** #platform (Slack)
- **DevOps Team:** #devops (Slack)
- **QA Team:** #qa (Slack)
- **On-Call:** See [RELEASE.md](RELEASE.md)

### External Resources
- [GitHub Actions](https://docs.github.com/en/actions)
- [Playwright](https://playwright.dev/)
- [Sentry](https://docs.sentry.io/)
- [Prometheus](https://prometheus.io/docs/)
- [Grafana](https://grafana.com/docs/)
- [Kubernetes](https://kubernetes.io/docs/)

---

**Last Updated:** December 3, 2025  
**Maintained By:** Platform Team  
**Version:** 1.0.0
