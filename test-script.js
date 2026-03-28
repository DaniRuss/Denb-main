const puppeteer = require('puppeteer');
(async () => {
  const browser = await puppeteer.launch({headless: 'new'});
  const page = await browser.newPage();
  await page.goto('http://localhost:8000/admin/login');
  await page.type('#data\\.email', 'field@aalea.gov.et');
  await page.type('#data\\.password', 'password');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]')
  ]);
  
  await page.goto('http://localhost:8000/admin/volunteer-tips/create');
  
  // Wait for the Livewire global
  await page.waitForFunction('window.Livewire !== undefined');
  
  const js = await page.evaluate(() => {
     let data = null;
     const comps = window.Livewire.all();
     for (const c of comps) {
         if (c.data && typeof c.data === 'object') {
             data = c.data;
             break;
         }
     }
     return data;
  });
  console.log(JSON.stringify(js));
  await browser.close();
})();
