# GitHub Secrets Configuration Guide

This document lists all GitHub secrets required for the CI/CD pipeline to function properly.

## 📋 How to Add Secrets

1. Go to your GitHub repository
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Enter the name and value for each secret below
5. Click **Add secret**

---

## 🔐 Required Secrets

### Kubernetes Configuration

**`KUBE_CONFIG_STAGING`**
- **Description:** Base64-encoded kubeconfig file for staging Kubernetes cluster
- **Used by:** Deploy to Staging job in cd.yml
- **How to get:**
  ```bash
  # On your local machine with kubectl configured for staging
  cat ~/.kube/config | base64 -w 0
  # Or on Windows PowerShell:
  [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((Get-Content ~/.kube/config -Raw)))
  ```
- **Priority:** High (required for staging deployment)

**`KUBE_CONFIG_PROD`**
- **Description:** Base64-encoded kubeconfig file for production Kubernetes cluster
- **Used by:** Deploy Canary & Promote Production jobs in cd.yml
- **How to get:** Same as KUBE_CONFIG_STAGING but for production cluster
- **Priority:** High (required for production deployment)

---

### Database Configuration (Staging)

**`DB_HOST_STAGING`**
- **Description:** Staging database host (e.g., `staging-db.bideshgomon.com` or `10.0.1.5`)
- **Used by:** Integration tests in cd.yml
- **Example:** `staging-mysql.internal` or `localhost` if using K8s service
- **Priority:** Medium (required for integration tests)

**`DB_USERNAME_STAGING`**
- **Description:** Staging database username
- **Used by:** Integration tests in cd.yml
- **Example:** `bideshgomon_staging` or `root`
- **Priority:** Medium

**`DB_PASSWORD_STAGING`**
- **Description:** Staging database password
- **Used by:** Integration tests in cd.yml
- **Security:** Never commit this value, always use secrets
- **Priority:** Medium

---

### Monitoring & Observability

**`PROMETHEUS_URL`**
- **Description:** Prometheus server URL for metrics monitoring
- **Used by:** Monitor Canary job in cd.yml (queries metrics to validate deployment)
- **Example:** `https://prometheus.bideshgomon.com` or `http://prometheus.monitoring.svc.cluster.local:9090`
- **Priority:** High (required for canary monitoring and auto-rollback)

**`SENTRY_DSN`**
- **Description:** Sentry Data Source Name for error tracking
- **Used by:** Application error tracking (configured in .env during deployment)
- **How to get:**
  1. Sign up at https://sentry.io
  2. Create a new project (Laravel + Vue)
  3. Copy DSN from Settings → Projects → [Your Project] → Client Keys (DSN)
- **Example:** `https://abc123def456@o123456.ingest.sentry.io/7890123`
- **Priority:** Medium (error tracking, not blocking for deployment)

---

### Notifications

**`SLACK_WEBHOOK`**
- **Description:** Slack incoming webhook URL for deployment notifications
- **Used by:** All jobs in cd.yml (success/failure notifications)
- **How to get:**
  1. Go to https://api.slack.com/apps
  2. Create a new app
  3. Add "Incoming Webhooks" feature
  4. Create webhook for your channel
- **Example:** `https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXX`
- **Priority:** Low (nice to have, not blocking)

**`INCIDENT_WEBHOOK_URL`**
- **Description:** PagerDuty, Opsgenie, or custom incident management webhook
- **Used by:** Rollback job in cd.yml (creates incidents on deployment failures)
- **How to get (PagerDuty):**
  1. Go to PagerDuty → Services → [Your Service]
  2. Integrations → Add Integration → Events API V2
  3. Copy Integration URL
- **How to get (Opsgenie):**
  1. Go to Opsgenie → Settings → API key management
  2. Create API key with "Create and Update" permissions
  3. Webhook URL: `https://api.opsgenie.com/v2/alerts` (with API key in headers)
- **Example (PagerDuty):** `https://events.pagerduty.com/v2/enqueue`
- **Priority:** Medium (incident management)

---

### Container Registry (Automatically Configured)

**GitHub Container Registry (GHCR)**
- **Token:** `GITHUB_TOKEN` (automatically provided by GitHub Actions)
- **Registry:** `ghcr.io`
- **No manual configuration needed** - GitHub Actions automatically has access

---

## 🔧 Optional Secrets

These secrets are used for enhanced features but not required for basic CI/CD:

**`APP_URL_STAGING`**
- **Description:** Staging application URL for E2E tests
- **Example:** `https://staging.bideshgomon.com`
- **Default:** Uses localhost if not provided

**`APP_URL_PRODUCTION`**
- **Description:** Production application URL
- **Example:** `https://bideshgomon.com`

**`GRAFANA_API_KEY`**
- **Description:** Grafana API key for dashboard management
- **Used by:** Automated dashboard updates

**`DISCORD_WEBHOOK`**
- **Description:** Discord webhook for alternative notifications
- **Alternative to:** SLACK_WEBHOOK

---

## ✅ Verification Checklist

After adding secrets, verify they're configured correctly:

### Check Secrets Are Added
```bash
# You cannot view secret values, but you can see if they exist
# Go to: Settings → Secrets and variables → Actions
# You should see all required secrets listed
```

### Test CI Pipeline (Pre-merge Checks)
- [ ] Push a branch
- [ ] Create a pull request
- [ ] Verify CI workflow runs and passes
- [ ] No secrets needed for CI (only CD)

### Test CD Pipeline (Deployment)
- [ ] Merge to main branch
- [ ] Verify CD workflow starts
- [ ] Check each job completes successfully:
  - [ ] Build Docker image
  - [ ] Deploy to staging
  - [ ] Integration tests (needs DB_* secrets)
  - [ ] E2E tests
  - [ ] Deploy canary (needs KUBE_CONFIG_PROD)
  - [ ] Monitor canary (needs PROMETHEUS_URL)
  - [ ] Promote production

### Common Issues

**Issue: "Error: kubectl: command not found"**
- **Cause:** KUBE_CONFIG_STAGING or KUBE_CONFIG_PROD is invalid
- **Fix:** Re-encode your kubeconfig file and update the secret

**Issue: "Cannot connect to database"**
- **Cause:** DB_HOST_STAGING, DB_USERNAME_STAGING, or DB_PASSWORD_STAGING is incorrect
- **Fix:** Verify credentials and ensure staging DB is accessible from GitHub Actions runners

**Issue: "Prometheus query failed"**
- **Cause:** PROMETHEUS_URL is incorrect or Prometheus is not accessible
- **Fix:** Verify Prometheus URL is publicly accessible or use Tailscale/VPN for GitHub Actions

**Issue: "Slack notification not sent"**
- **Cause:** SLACK_WEBHOOK is invalid
- **Fix:** Create a new webhook or verify the existing one is active

---

## 🔒 Security Best Practices

1. **Rotate Secrets Regularly**
   - Update database passwords every 90 days
   - Rotate API keys every 6 months
   - Update immediately if suspected compromise

2. **Principle of Least Privilege**
   - Use read-only credentials where possible
   - Create service accounts with minimal permissions
   - Use separate credentials for staging and production

3. **Never Commit Secrets**
   - Use `.gitignore` for `.env` files
   - Scan commits with tools like `gitleaks` or `trufflehog`
   - Use pre-commit hooks to prevent accidental commits

4. **Monitor Secret Usage**
   - Review GitHub Actions logs for authentication failures
   - Set up alerts for unauthorized access attempts
   - Audit secret access regularly

5. **Use Secret Management Tools**
   - Consider HashiCorp Vault for advanced secret management
   - Use AWS Secrets Manager or Azure Key Vault for cloud deployments
   - Implement automatic secret rotation

---

## 📞 Support

If you encounter issues with secrets configuration:

1. **Check GitHub Actions Logs:** Actions tab → Failed workflow → View logs
2. **Verify Secret Names:** Exact case-sensitive match required
3. **Test Locally:** Simulate the workflow with your secrets locally first
4. **Documentation:** See `docs/RELEASE.md` for deployment troubleshooting

**Internal Contacts:**
- DevOps Team: #devops (Slack)
- Platform Team: #platform (Slack)
- On-Call: See `docs/RELEASE.md`

---

**Last Updated:** December 3, 2025  
**Maintained By:** Platform Team  
**Review Frequency:** Quarterly
