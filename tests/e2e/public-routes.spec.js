import { test, expect } from '@playwright/test';

test.describe('Public Front-End Routes', () => {
  test('homepage should load properly', async ({ page }) => {
    await page.goto('http://localhost:8000/');
    await page.waitForLoadState('networkidle');
    
    // Check if body is visible
    await expect(page.locator('body')).toBeVisible();
  });

  test('artikel index should load properly', async ({ page }) => {
    await page.goto('http://localhost:8000/artikel');
    await page.waitForLoadState('networkidle');
    
    await expect(page.locator('body')).toBeVisible();
  });

  test('inabuyer feedback form should load properly', async ({ page }) => {
    await page.goto('http://localhost:8000/inabuyer2026/feedback');
    await page.waitForLoadState('networkidle');
    
    await expect(page.locator('form')).toBeVisible();
  });
});
