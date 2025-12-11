# Playwright E2E Tests

End-to-end tests for critical user paths on the BideshGomon platform.

## 🚀 Quick Start

```bash
# Install Playwright browsers (one-time setup)
npx playwright install

# Run all E2E tests
npm run test:e2e

# Run with UI mode (recommended for development)
npm run test:e2e:ui

# Run in headed mode (see browser)
npm run test:e2e:headed

# Debug a specific test
npm run test:e2e:debug
```

## 📋 Test Coverage

### Authentication Tests
- ✅ User can login with valid credentials
- ✅ User cannot login with invalid credentials
- ✅ User can logout

### Visa Application Tests
- ✅ User can create new visa application
- ✅ User can view application details
- ✅ User can upload documents

### Search Tests
- ✅ User can search for visa information
- ✅ Autocomplete works correctly
- ✅ Search results navigate properly

### Performance Tests
- ✅ Homepage loads within 3 seconds

### Accessibility Tests
- ✅ Critical pages meet WCAG standards

## 🎯 Test Fixtures

Test fixtures are located in `tests/fixtures/`:
- `sample-passport.pdf` - Dummy PDF for document upload tests

## 🔧 Configuration

### Base URL
The base URL is configured in `playwright.config.ts`:
- **Default:** `http://localhost:8000`
- **CI Environment:** Set `APP_URL` environment variable

### Test Accounts
Tests require the following accounts to exist:
- **Email:** `test@bideshgomon.com`
- **Password:** `password123`

Create test account:
```bash
php artisan tinker
>>> \App\Models\User::factory()->create([
...     'email' => 'test@bideshgomon.com',
...     'password' => bcrypt('password123')
... ]);
```

## 📊 Test Reports

After running tests, reports are available at:
- **HTML Report:** `storage/playwright-report/index.html`
- **JUnit XML:** `storage/playwright-results.xml` (for CI)

View HTML report:
```bash
npx playwright show-report storage/playwright-report
```

## 🐛 Debugging Tests

### Visual Debugging
```bash
# Open Playwright Inspector
npm run test:e2e:debug

# Run specific test file
npx playwright test tests/e2e/critical-paths.spec.ts --debug
```

### Screenshots & Videos
- **Screenshots:** Captured on failure (default)
- **Videos:** Captured on failure (retention policy)
- **Location:** `test-results/` directory

### Trace Viewer
```bash
# View trace for failed test
npx playwright show-trace test-results/.../trace.zip
```

## 🌐 Browser Support

Tests run on multiple browsers:
- Chromium (Desktop)
- Firefox (Desktop)
- WebKit/Safari (Desktop)
- Mobile Chrome (Pixel 5 emulation)
- Mobile Safari (iPhone 12 emulation)

## 📝 Writing New Tests

### Test Structure
```typescript
import { test, expect } from '@playwright/test'

test.describe('Feature Name', () => {
  test.beforeEach(async ({ page }) => {
    // Login or setup
    await page.goto('/login')
    await page.fill('input[name="email"]', 'test@bideshgomon.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')
    await page.waitForURL('**/dashboard')
  })

  test('should do something', async ({ page }) => {
    // Your test code
    await page.goto('/some-page')
    await expect(page.locator('h1')).toContainText('Expected Text')
  })
})
```

### Best Practices
1. **Use data-testid attributes** for reliable selectors
2. **Wait for network idle** before assertions
3. **Avoid hard-coded waits** - use `waitForSelector` instead
4. **Clean up test data** after each test
5. **Use Page Object Model** for complex pages

### Example Page Object
```typescript
// tests/e2e/pages/VisaApplicationPage.ts
export class VisaApplicationPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/visa-applications/create')
  }

  async fillForm(data: ApplicationData) {
    await this.page.selectOption('select[name="country_id"]', data.country)
    await this.page.selectOption('select[name="visa_type"]', data.visaType)
    await this.page.fill('input[name="travel_date"]', data.travelDate)
  }

  async submit() {
    await this.page.click('button[type="submit"]')
  }
}
```

## 🔄 CI Integration

E2E tests run automatically in GitHub Actions:
- **Trigger:** On pull requests and main branch commits
- **Environment:** Staging after deployment
- **Browsers:** Chromium only (for speed)
- **Retry:** Failed tests retried 2 times

See `.github/workflows/cd.yml` for CI configuration.

## 🚦 Parallel Execution

Tests run in parallel by default:
- **Local:** Unlimited workers
- **CI:** 1 worker (to avoid resource contention)

Control parallelization:
```bash
# Run with specific number of workers
npx playwright test --workers=4

# Run in serial mode
npx playwright test --workers=1
```

## 📈 Performance Monitoring

Track test performance:
```bash
# Generate performance report
npx playwright test --reporter=html,junit

# View slowest tests
grep "took" storage/playwright-report/index.html
```

## 🔒 Security Considerations

- **Never commit credentials** to test files
- **Use environment variables** for sensitive data
- **Clean up test data** to avoid leaking information
- **Use separate test database** to avoid contaminating production data

## 🆘 Troubleshooting

### Tests fail with "browser not found"
```bash
npx playwright install
```

### Tests timeout
- Increase timeout in `playwright.config.ts`
- Check if server is running: `php artisan serve`
- Verify network connectivity

### Element not found
- Use `page.waitForSelector()` before interacting
- Add `data-testid` attributes for stable selectors
- Check if element is visible: `page.isVisible()`

### Database state issues
- Use `RefreshDatabase` trait in Laravel tests
- Ensure migrations run before tests
- Check database seeder

## 📚 Resources

- [Playwright Documentation](https://playwright.dev/)
- [Best Practices](https://playwright.dev/docs/best-practices)
- [Debugging Guide](https://playwright.dev/docs/debug)
- [CI Configuration](https://playwright.dev/docs/ci)

---

**Last Updated:** December 3, 2025  
**Maintained By:** QA Team  
**Contact:** qa@bideshgomon.com
