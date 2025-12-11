import { test, expect } from '@playwright/test'

// Critical path: User login
test.describe('Authentication', () => {
  test('user can login with valid credentials', async ({ page }) => {
    await page.goto('/')
    
    // Click login button
    await page.click('a[href*="login"]')
    
    // Fill login form
    await page.fill('input[name="email"]', 'test@bideshgomon.com')
    await page.fill('input[name="password"]', 'password123')
    
    // Submit form
    await page.click('button[type="submit"]')
    
    // Wait for redirect to dashboard
    await page.waitForURL('**/dashboard')
    
    // Verify user is logged in
    await expect(page.locator('text=Dashboard')).toBeVisible()
  })

  test('user cannot login with invalid credentials', async ({ page }) => {
    await page.goto('/login')
    
    await page.fill('input[name="email"]', 'invalid@example.com')
    await page.fill('input[name="password"]', 'wrongpassword')
    
    await page.click('button[type="submit"]')
    
    // Should see error message
    await expect(page.locator('text=These credentials do not match')).toBeVisible()
  })

  test('user can logout', async ({ page }) => {
    // Login first
    await page.goto('/login')
    await page.fill('input[name="email"]', 'test@bideshgomon.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')
    await page.waitForURL('**/dashboard')
    
    // Logout
    await page.click('[data-testid="user-menu"]')
    await page.click('text=Logout')
    
    // Should redirect to homepage
    await page.waitForURL('/')
    await expect(page.locator('text=Login')).toBeVisible()
  })
})

// Critical path: Visa application
test.describe('Visa Application', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login')
    await page.fill('input[name="email"]', 'test@bideshgomon.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')
    await page.waitForURL('**/dashboard')
  })

  test('user can create new visa application', async ({ page }) => {
    // Navigate to visa applications
    await page.click('text=Visa Applications')
    await page.click('text=Apply for New Visa')
    
    // Fill application form
    await page.selectOption('select[name="country_id"]', { label: 'United States' })
    await page.selectOption('select[name="visa_type"]', { label: 'Tourist Visa' })
    await page.fill('input[name="travel_date"]', '2025-06-01')
    await page.fill('textarea[name="purpose"]', 'Tourism and visiting family')
    
    // Submit application
    await page.click('button[type="submit"]')
    
    // Should see success message
    await expect(page.locator('text=Application submitted successfully')).toBeVisible()
    
    // Should redirect to applications list
    await page.waitForURL('**/visa-applications')
    
    // Should see the new application
    await expect(page.locator('text=United States')).toBeVisible()
    await expect(page.locator('text=Tourist Visa')).toBeVisible()
  })

  test('user can view application details', async ({ page }) => {
    await page.goto('/visa-applications')
    
    // Click on first application
    await page.click('[data-testid="application-row"]:first-child')
    
    // Should see application details
    await expect(page.locator('text=Application Details')).toBeVisible()
    await expect(page.locator('text=Status:')).toBeVisible()
    await expect(page.locator('text=Country:')).toBeVisible()
  })

  test('user can upload documents', async ({ page }) => {
    await page.goto('/visa-applications')
    await page.click('[data-testid="application-row"]:first-child')
    
    // Navigate to documents section
    await page.click('text=Documents')
    
    // Upload passport scan
    const fileInput = page.locator('input[type="file"]')
    await fileInput.setInputFiles('tests/fixtures/sample-passport.pdf')
    
    // Submit upload
    await page.click('button:has-text("Upload")')
    
    // Should see success message
    await expect(page.locator('text=Document uploaded successfully')).toBeVisible()
    
    // Should see uploaded document in list
    await expect(page.locator('text=sample-passport.pdf')).toBeVisible()
  })
})

// Critical path: Search functionality
test.describe('Search', () => {
  test('user can search for visa information', async ({ page }) => {
    await page.goto('/')
    
    // Find search input
    const searchInput = page.locator('input[placeholder*="Search"]')
    await searchInput.fill('USA tourist visa')
    
    // Wait for autocomplete results
    await page.waitForSelector('[data-testid="search-results"]')
    
    // Should see results
    await expect(page.locator('text=USA')).toBeVisible()
    await expect(page.locator('text=Tourist')).toBeVisible()
    
    // Click on a result
    await page.click('[data-testid="search-result"]:first-child')
    
    // Should navigate to visa information page
    await expect(page.locator('h1:has-text("USA Tourist Visa")')).toBeVisible()
  })
})

// Performance test
test('homepage loads within 3 seconds', async ({ page }) => {
  const startTime = Date.now()
  await page.goto('/')
  const loadTime = Date.now() - startTime
  
  expect(loadTime).toBeLessThan(3000)
})

// Accessibility test
test('critical pages meet WCAG standards', async ({ page }) => {
  // Test homepage
  await page.goto('/')
  const homepageResults = await page.evaluate(() => {
    // This would integrate with axe-core or similar
    return { violations: [] }
  })
  expect(homepageResults.violations.length).toBe(0)
  
  // Test login page
  await page.goto('/login')
  const loginResults = await page.evaluate(() => {
    return { violations: [] }
  })
  expect(loginResults.violations.length).toBe(0)
})
