import { test, expect } from '@playwright/test';

test.describe('Academic CMS Frontend Rendering', () => {
  test('should render academic blocks properly on public post', async ({ page }) => {
    await page.goto('http://localhost:8000/artikel/e2e-test-post');
    await page.waitForLoadState('networkidle');
    
    const title = await page.title();
    expect(title).toContain('E2E Test Post');

    // Check heading auto-numbering
    const h2Text = await page.locator('h2').first().textContent();
    expect(h2Text).toContain('1 Introduction');

    // Check equation rendering (KaTeX creates .katex elements)
    const equationText = await page.locator('.academic-equation .equation-content').getAttribute('data-latex');
    expect(equationText).toBe('E = mc^2');

    // Check cross reference parsing
    const xrefText = await page.locator('a.xref').first().textContent();
    expect(xrefText).toBe('[Fig. 1]');
  });
});
