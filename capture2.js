import puppeteer from 'puppeteer';

(async () => {
    try {
        const browser = await puppeteer.launch({ headless: 'new' });
        const page = await browser.newPage();
        await page.setViewport({ width: 400, height: 800 });
        
        await page.goto('http://localhost:8000/login');
        await page.type('input[type="email"]', 'admin@desatanjungkesuma.id');
        await page.type('input[type="password"]', 'Apriyansah74!');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('http://localhost:8000/admin/keluargas', { waitUntil: 'networkidle2' });
        
        const metrics = await page.evaluate(() => {
           const body = document.querySelector('.resident-table__body');
           const row = document.querySelector('.resident-table__row');
           const cell = document.querySelector('.resident-table__cell');
           return {
               bodyDisplay: getComputedStyle(body).display,
               bodyGridCols: getComputedStyle(body).gridTemplateColumns,
               rowDisplay: getComputedStyle(row).display,
               rowGridCols: getComputedStyle(row).gridTemplateColumns,
               cellDisplay: getComputedStyle(cell).display
           };
        });
        console.log("METRICS:", metrics);
        await browser.close();
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
})();
