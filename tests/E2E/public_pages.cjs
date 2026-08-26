const { chromium } = require('playwright');
const fs = require('fs');

const artifactsDir = '/home/faliq/.gemini/antigravity-ide/brain/43df04b9-afd7-48a0-8275-833d39099c2f/artifacts';
if (!fs.existsSync(artifactsDir)) {
  fs.mkdirSync(artifactsDir, { recursive: true });
}

const viewports = [
  { name: 'Desktop', width: 1920, height: 1080 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Mobile', width: 375, height: 812 }
];

const routes = [
  { name: 'homepage', path: '/' },
  { name: 'product_detail', path: '/produk/ddr-madeena-hf100b-mdn' },
  { name: 'artikel_post', path: '/artikel/madeena-luncurkan-ddr-hf100b-mdn' },
  { name: 'static_page', path: '/halaman/tentang' },
  { name: 'inabuyer_feedback', path: '/inabuyer2026/feedback' },
  { name: 'inabuyer_display', path: '/inabuyer2026/display' }
];

const baseUrl = 'http://localhost:8011';

(async () => {
  const browser = await chromium.launch();
  
  for (const vp of viewports) {
    const context = await browser.newContext({
      viewport: { width: vp.width, height: vp.height },
      colorScheme: 'light'
    });
    const page = await context.newPage();
    
    for (const route of routes) {
      console.log(`Testing Public ${vp.name} - ${route.name}`);
      await page.goto(`${baseUrl}${route.path}`, { waitUntil: 'networkidle' });
      // add a small wait just to make sure fonts/images load
      await page.waitForTimeout(1000);
      await page.screenshot({ path: `${artifactsDir}/public_${route.name}_${vp.name.toLowerCase()}.png`, fullPage: true });
    }
    await context.close();
  }

  await browser.close();
})();
