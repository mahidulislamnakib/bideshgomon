# BideshGomon Platform - Release & Deployment Runbook

## Table of Contents
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Deployment Process](#deployment-process)
- [Feature Flags](#feature-flags)
- [Monitoring & Observability](#monitoring--observability)
- [Rollback Procedures](#rollback-procedures)
- [Emergency Response](#emergency-response)
- [Postmortem Process](#postmortem-process)
- [On-Call Contacts](#on-call-contacts)

---

## Pre-Deployment Checklist

### Before Merging to Main

- [ ] **All Tests Passing**
  - [ ] PHPUnit (unit + feature): `composer test`
  - [ ] E2E tests: `npm run test:e2e`
  - [ ] CI pipeline green: Check GitHub Actions

- [ ] **Code Quality**
  - [ ] PHPStan analysis: `composer phpstan`
  - [ ] Laravel Pint formatting: `composer pint`
  - [ ] ESLint: `npm run lint:js`
  - [ ] Stylelint: `npm run lint:css`

- [ ] **Database Changes**
  - [ ] Migrations reviewed and tested
  - [ ] Seeders updated (if needed)
  - [ ] No destructive migrations without backup plan
  - [ ] Rollback plan for migrations documented

- [ ] **Feature Flags**
  - [ ] New features behind flags (config/features.php)
  - [ ] Rollout percentage set appropriately (start at 5%)
  - [ ] Kill switches in place for risky features
  - [ ] Flag removal date documented

- [ ] **Security**
  - [ ] No hardcoded credentials
  - [ ] PII scrubbing configured (Sentry)
  - [ ] Composer audit clean: `composer audit`
  - [ ] NPM audit clean: `npm audit`

- [ ] **Documentation**
  - [ ] README updated (if public-facing changes)
  - [ ] API changes documented
  - [ ] Breaking changes noted in CHANGELOG.md
  - [ ] Deployment notes prepared

---

## Deployment Process

### Automatic Deployment (Recommended)

1. **Merge to Main Branch**
   ```bash
   git checkout main
   git pull origin main
   git merge feature-branch
   git push origin main
   ```

2. **CI Pipeline Runs Automatically**
   - Pre-merge checks (PHPStan, PHPUnit, ESLint, Build)
   - Docker image build and push to GHCR
   - Deploy to staging environment
   - Integration tests on staging
   - E2E tests with Playwright

3. **Canary Deployment (5% Traffic)**
   - Deploys to production with 5% traffic
   - Runs smoke tests:
     - Health check: `GET /api/health`
     - Homepage load test
     - API authentication test
     - Database connectivity test
     - Cache functionality test

4. **Monitoring Window (5 minutes)**
   - Prometheus queries:
     - Error rate < 1%
     - Latency p95 < 500ms
     - No 5xx responses
   - Grafana dashboard: https://grafana.bideshgomon.com/d/bideshgomon-prod
   - Sentry: https://sentry.io/bideshgomon

5. **Automatic Promotion**
   - If metrics healthy → promote to 100%
   - If metrics unhealthy → automatic rollback
   - Slack notification sent to #deployments

### Manual Deployment (Alternative)

If you need to deploy manually:

```bash
# SSH to deployment server
ssh deploy@bideshgomon.com

# Pull latest code
cd /var/www/bideshgomon
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart services
sudo systemctl restart php-fpm
sudo systemctl reload nginx

# Verify deployment
curl http://localhost/api/health
```

### Blue-Green Deployment (Alternative Strategy)

Use when you need instant rollback capability:

```bash
# Run blue-green deployment script
./scripts/deploy-blue-green.sh v2.1.0

# Script will:
# 1. Determine current color (blue/green)
# 2. Deploy to inactive color
# 3. Run smoke tests
# 4. Switch traffic atomically
# 5. Monitor for errors
# 6. Keep old version for quick rollback
```

---

## Feature Flags

### Creating a New Feature Flag

1. **Add to config/features.php**
   ```php
   'visa_new_flow_20250601' => [
       'enabled' => true,
       'rollout_percentage' => 5,  // Start at 5%
       'user_whitelist' => [1, 2], // Test users
       'description' => 'New visa application flow',
       'created_at' => '2025-06-01',
       'remove_after' => '2025-07-01',
   ],
   ```

2. **Use in Laravel**
   ```php
   use App\Services\FeatureFlagService;
   
   if (app(FeatureFlagService::class)->isEnabled('visa_new_flow_20250601')) {
       // New flow
   } else {
       // Old flow
   }
   ```

3. **Use in Vue**
   ```vue
   <template>
     <div v-feature="'visa_new_flow_20250601'">
       New flow UI
     </div>
     <div v-feature:hide="'visa_new_flow_20250601'">
       Old flow UI
     </div>
   </template>
   
   <script setup>
   import { useFeatureFlags } from '@/Composables/useFeatureFlags'
   const { isEnabled } = useFeatureFlags()
   const showNewFlow = isEnabled('visa_new_flow_20250601')
   </script>
   ```

### Gradual Rollout Process

1. **Week 1: 5% rollout + test users**
   ```php
   php artisan tinker
   >>> app(FeatureFlagService::class)->setRollout('visa_new_flow_20250601', 5);
   >>> app(FeatureFlagService::class)->enableForUsers('visa_new_flow_20250601', [1, 2, 3]);
   ```

2. **Week 2: 25% rollout** (if no issues)
   ```php
   >>> app(FeatureFlagService::class)->setRollout('visa_new_flow_20250601', 25);
   ```

3. **Week 3: 50% rollout**
   ```php
   >>> app(FeatureFlagService::class)->setRollout('visa_new_flow_20250601', 50);
   ```

4. **Week 4: 100% rollout**
   ```php
   >>> app(FeatureFlagService::class)->enable('visa_new_flow_20250601');
   ```

5. **Week 8: Remove flag** (after 30 days at 100%)
   - Remove from config/features.php
   - Remove conditional code
   - Deploy cleanup PR

### Emergency Disable (Kill Switch)

If feature causes issues:

```php
# Via tinker
php artisan tinker
>>> app(FeatureFlagService::class)->disable('visa_new_flow_20250601');

# Or via config (requires deployment)
# config/features.php
'visa_new_flow_20250601' => [
    'enabled' => false,  // Changed from true
    // ...
],
```

---

## Monitoring & Observability

### Key Dashboards

1. **Grafana (Production Overview)**
   - URL: https://grafana.bideshgomon.com/d/bideshgomon-prod
   - Panels:
     - HTTP Request Rate (by method/path)
     - Response Time (p95, p99)
     - Error Rate (4xx, 5xx)
     - Queue Status (pending/failed)
     - Database Performance

2. **Sentry (Error Tracking)**
   - URL: https://sentry.io/bideshgomon
   - Laravel errors: https://sentry.io/bideshgomon/laravel
   - Vue errors: https://sentry.io/bideshgomon/vue
   - Performance traces
   - Session replays

3. **Prometheus (Raw Metrics)**
   - URL: https://prometheus.bideshgomon.com
   - Query examples:
     ```promql
     # Error rate
     rate(http_requests_total{status=~"5.."}[5m])
     
     # Latency p95
     histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))
     
     # Queue backlog
     queue_jobs_pending
     ```

### Health Checks

**Endpoint:** `GET /api/health`

**Expected Response (200 OK):**
```json
{
  "status": "healthy",
  "timestamp": "2025-11-18T10:30:00Z",
  "response_time_ms": 45,
  "checks": {
    "database": { "status": "healthy", "message": "Connected" },
    "cache": { "status": "healthy", "message": "Read/write successful" },
    "queue": { "status": "healthy", "message": "Connection successful" },
    "storage": { "status": "healthy", "message": "50% free space" }
  }
}
```

**Unhealthy Response (503 Service Unavailable):**
```json
{
  "status": "unhealthy",
  "checks": {
    "database": { "status": "unhealthy", "message": "Connection timeout" }
  }
}
```

### Alert Rules (Prometheus Alerts)

| Alert Name | Condition | Severity | Action |
|------------|-----------|----------|--------|
| HighErrorRate | >1% for 2min | Critical | Page on-call, investigate immediately |
| HighLatency | p95 >1s for 3min | Warning | Check slow queries, optimize |
| ApplicationDown | Health check fails >1min | Critical | Page on-call, initiate rollback |
| DatabaseConnectionHigh | >80 connections for 5min | Warning | Check connection leaks |
| QueueBacklog | >1000 jobs for 10min | Warning | Scale queue workers |
| HighFailedJobs | >0.1/s for 5min | Critical | Check job logs, fix errors |
| CanaryDeploymentFailing | >2% errors for 1min | Critical | Auto-rollback triggered |

### Metrics to Watch

**During Deployment:**
- [ ] Error rate < 1%
- [ ] Latency p95 < 500ms
- [ ] No 5xx responses
- [ ] Queue not backing up
- [ ] Memory usage stable

**Post-Deployment (24 hours):**
- [ ] Sentry error count not increased
- [ ] User complaints in #support
- [ ] Revenue/conversion metrics stable
- [ ] Performance metrics within baseline

---

## Rollback Procedures

### Automatic Rollback

The CD pipeline automatically rolls back if:
- Smoke tests fail (health check, API tests)
- Error rate > 1% during canary monitoring
- Latency p95 > 500ms during canary monitoring

**What happens:**
1. kubectl rollout undo triggered
2. Traffic reverted to stable version
3. Slack notification sent to #incidents
4. Incident webhook called (PagerDuty/Opsgenie)

### Manual Rollback (CI/CD Pipeline)

If you need to manually trigger rollback:

```bash
# Via kubectl
kubectl rollout undo deployment/bideshgomon-canary -n production
kubectl rollout undo deployment/bideshgomon-app -n production

# Check rollout status
kubectl rollout status deployment/bideshgomon-app -n production

# Verify health
kubectl exec -it deployment/bideshgomon-app -- curl http://localhost/api/health
```

### Manual Rollback (Blue-Green)

If using blue-green deployment:

```bash
# Switch traffic back to previous color
./scripts/deploy-blue-green.sh --rollback

# Or manually patch service
kubectl patch service bideshgomon-service -p '{"spec":{"selector":{"version":"blue"}}}'

# Verify traffic switched
kubectl get service bideshgomon-service -o yaml | grep version
```

### Manual Rollback (Traditional Deployment)

```bash
# SSH to server
ssh deploy@bideshgomon.com
cd /var/www/bideshgomon

# Check current version
git log -1 --oneline

# Rollback to previous version
git log --oneline -10  # Find commit hash
git checkout <previous-commit-hash>

# Reinstall dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Rollback migrations (if needed)
php artisan migrate:rollback --step=1

# Clear caches
php artisan optimize:clear
php artisan config:cache

# Restart services
sudo systemctl restart php-fpm
sudo systemctl reload nginx

# Verify
curl http://localhost/api/health
```

### Database Rollback

**⚠️ WARNING: Database rollbacks can cause data loss!**

Before rolling back migrations:
1. Take a database snapshot
2. Check if data will be lost
3. Notify team in #incidents

```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback --step=1

# Rollback specific batch
php artisan migrate:rollback --batch=5

# Nuclear option (⚠️ DANGER)
php artisan migrate:fresh --seed  # Only in staging!
```

### Rollback Checklist

- [ ] **Identify issue**: Error logs, metrics, user reports
- [ ] **Notify team**: #incidents channel
- [ ] **Trigger rollback**: Automatic or manual
- [ ] **Verify health**: Check /api/health endpoint
- [ ] **Monitor metrics**: Ensure errors decrease
- [ ] **Check Sentry**: Verify error count drops
- [ ] **User notification**: If widespread impact
- [ ] **Postmortem**: Schedule within 24 hours

---

## Emergency Response

### Severity Levels

**P0 - Critical (Page Immediately)**
- Site completely down
- Payment processing broken
- Data breach suspected
- Database corruption

**P1 - High (Notify Within 15min)**
- Feature broken for all users
- Error rate > 5%
- Latency > 5 seconds
- Queue completely backed up

**P2 - Medium (Notify Within 1hr)**
- Feature broken for some users
- Error rate 1-5%
- Non-critical API down

**P3 - Low (Next Business Day)**
- UI glitch
- Minor performance degradation
- Documentation issue

### Emergency Contacts

**On-Call Engineer (Primary):**
- PagerDuty: +1-XXX-XXX-XXXX
- Slack: #platform-oncall
- Phone: +880-XXX-XXX-XXXX

**Platform Team Lead:**
- Name: [Your Name]
- Phone: +880-XXX-XXX-XXXX
- Email: platform-lead@bideshgomon.com

**CTO:**
- Name: [CTO Name]
- Phone: +880-XXX-XXX-XXXX
- Email: cto@bideshgomon.com

**DevOps Lead:**
- Name: [DevOps Name]
- Phone: +880-XXX-XXX-XXXX
- Email: devops@bideshgomon.com

### Incident Response Workflow

1. **Acknowledge** (Within 5min)
   - Acknowledge PagerDuty alert
   - Post in #incidents: "Investigating [issue]"
   - Assign severity level

2. **Assess** (Within 10min)
   - Check Grafana dashboards
   - Review Sentry errors
   - Check application logs: `kubectl logs -f deployment/bideshgomon-app`
   - Identify scope: % users affected

3. **Communicate** (Within 15min)
   - Post update in #incidents
   - Notify stakeholders if P0/P1
   - Update status page (if public-facing)

4. **Mitigate** (ASAP)
   - Rollback if recent deployment
   - Disable feature flag if specific feature
   - Scale resources if capacity issue
   - Apply hotfix if quick fix available

5. **Resolve** (ASAP)
   - Verify metrics return to normal
   - Confirm user reports decrease
   - Update #incidents: "Resolved at [time]"
   - Close PagerDuty incident

6. **Document** (Within 24hr)
   - Create postmortem document
   - Timeline of events
   - Root cause analysis
   - Action items

### Emergency Commands

**View live logs:**
```bash
# Laravel logs
kubectl logs -f deployment/bideshgomon-app --tail=100

# PHP-FPM errors
tail -f /var/log/php-fpm/error.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

**Scale application:**
```bash
# Scale up replicas
kubectl scale deployment/bideshgomon-app --replicas=10

# Scale down
kubectl scale deployment/bideshgomon-app --replicas=3
```

**Disable feature flag:**
```bash
php artisan tinker
>>> app(\App\Services\FeatureFlagService::class)->disable('problematic_feature');
>>> cache()->flush(); // Clear feature flag cache
```

**Clear all caches:**
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
redis-cli FLUSHALL  # ⚠️ Clears all Redis data
```

**Restart services:**
```bash
# Restart all pods
kubectl rollout restart deployment/bideshgomon-app

# Restart specific services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
sudo systemctl restart redis
```

**Database emergency:**
```bash
# Check database connections
php artisan tinker
>>> DB::select('SELECT COUNT(*) as connections FROM pg_stat_activity;');

# Kill long-running queries
>>> DB::select("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE state = 'active' AND query_start < NOW() - INTERVAL '5 minutes';");

# Check table sizes
>>> DB::select("SELECT schemaname, tablename, pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size FROM pg_tables ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC LIMIT 10;");
```

---

## Postmortem Process

### Template

Create a document with this structure:

```markdown
# Incident Postmortem: [Brief Title]

**Date:** YYYY-MM-DD  
**Severity:** P0 / P1 / P2  
**Duration:** HH:MM  
**Impact:** XX users affected, $YY revenue lost  
**Incident Commander:** [Name]

## Summary
One-paragraph summary of what happened.

## Timeline (Asia/Dhaka Time)
- **10:00:00** - Initial deployment started
- **10:15:00** - Error rate spike detected (5%)
- **10:17:00** - PagerDuty alert triggered
- **10:18:00** - On-call engineer acknowledged
- **10:20:00** - Root cause identified: database connection leak
- **10:22:00** - Rollback initiated
- **10:25:00** - Rollback completed
- **10:28:00** - Error rate returned to normal (<0.1%)
- **10:30:00** - Incident resolved

## Root Cause
Detailed explanation of what caused the issue.

Example: Database connection pool was not properly closed in new code, causing connection exhaustion after ~1000 requests.

## Detection
How was the issue detected?
- Prometheus alert: HighErrorRate
- Sentry error spike
- User reports in #support

## Impact
- **Users affected:** 1,234 (5% of active users)
- **Duration:** 30 minutes
- **Revenue impact:** ৳50,000 (estimated)
- **User experience:** Failed visa applications, error messages

## Resolution
What fixed the issue?
- Rolled back deployment to previous version
- Database connections returned to normal
- Users able to complete applications again

## Action Items

### Immediate (Within 24hr)
- [ ] Add database connection monitoring alert (@devops-team)
- [ ] Review all database connection usage in codebase (@backend-team)
- [ ] Add connection pool size to health check (@platform-lead)

### Short-term (Within 1 week)
- [ ] Implement connection leak detection in CI (@devops-team)
- [ ] Add load testing to deployment pipeline (@qa-team)
- [ ] Update deployment checklist with connection review (@platform-lead)

### Long-term (Within 1 month)
- [ ] Implement circuit breaker pattern for database (@backend-team)
- [ ] Add automated chaos engineering tests (@devops-team)
- [ ] Review and update monitoring strategy (@platform-lead)

## Lessons Learned

### What Went Well
- Rollback executed quickly (7 minutes)
- Clear communication in #incidents
- Monitoring detected issue before major impact

### What Went Wrong
- Database connection leak not caught in testing
- Load testing doesn't cover this scenario
- Health check didn't detect connection pool exhaustion

### Where We Got Lucky
- Issue occurred during low-traffic period
- Database didn't completely lock up
- Team was online and responsive

## Follow-Up
- Postmortem reviewed with team: [Date]
- Action items assigned and tracked in [JIRA/Asana]
- Process improvements documented in runbook
```

### Postmortem Meeting Agenda

**Duration:** 1 hour  
**Participants:** Engineering team, product, stakeholders  
**Format:** Blameless, focus on learning

1. **Review timeline** (10min)
   - Walk through events chronologically
   - No blame, just facts

2. **Root cause analysis** (20min)
   - What was the technical cause?
   - What process failures enabled it?
   - Use "5 Whys" technique

3. **Impact assessment** (10min)
   - User impact quantified
   - Business impact calculated
   - Reputation/trust considerations

4. **Action items** (15min)
   - Brainstorm improvements
   - Assign owners and deadlines
   - Prioritize by impact

5. **Process improvements** (5min)
   - What can we change to prevent recurrence?
   - Update runbooks/checklists

---

## Deployment Frequency & Windows

### Recommended Schedule

**Regular Deployments:**
- **Tuesday-Thursday:** 10:00 AM - 4:00 PM BST (Asia/Dhaka)
- Avoid Mondays (weekend issues may surface)
- Avoid Fridays (limits incident response time)

**Emergency Deployments:**
- Anytime for P0 incidents
- Require approval for P1+ outside regular hours

**Blackout Periods (No Deployments):**
- Government holidays (Bangladesh)
- High-traffic events (e.g., Eid visa rush)
- Friday evenings (weekend starts)
- After 6:00 PM BST (limited support availability)

### Deployment Approvals

**Regular Deployments:**
- Code review: 2 approvals required
- QA sign-off: Required for major features
- Product sign-off: Required for user-facing changes

**Hotfix Deployments:**
- 1 approval required
- Must include rollback plan
- Post-deployment review within 24hr

---

## Related Documentation

- **Architecture Overview:** `docs/ARCHITECTURE.md`
- **API Documentation:** `docs/API.md`
- **Database Schema:** `docs/DATABASE.md`
- **Feature Flag Guide:** `config/features.php` (comments)
- **Monitoring Setup:** `docs/MONITORING.md`
- **Security Policies:** `docs/SECURITY.md`

---

## Changelog

| Date | Author | Changes |
|------|--------|---------|
| 2025-11-18 | Platform Team | Initial runbook created |
| | | Added canary deployment process |
| | | Added feature flag guidelines |
| | | Added emergency response procedures |

---

**Last Updated:** November 18, 2025  
**Next Review:** February 18, 2026  
**Owner:** Platform Team  
**Contact:** platform@bideshgomon.com
```

This comprehensive runbook provides everything needed for safe, reliable deployments with quick rollback capabilities.
