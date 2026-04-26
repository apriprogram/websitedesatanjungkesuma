import puppeteer from 'puppeteer';

(async () => {
    try {
        const browser = await puppeteer.launch({ headless: 'new' });
        const page = await browser.newPage();
        await page.setViewport({ width: 400, height: 800 });
        
        console.log("Navigating to login...");
        await page.goto('http://localhost:8000/login');
        
        console.log("Filling credentials...");
        await page.type('input[name="email"], input[type="email"]', 'admin@desatanjungkesuma.id');
        await page.type('input[name="password"], input[type="password"]', 'Apriyansah74!');
        
        console.log("Clicking submit...");
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        
        console.log("Navigating to keluargas...");
        await page.goto('http://localhost:8000/admin/keluargas', { waitUntil: 'networkidle2' });
        
        console.log("Extracting layout info...");
        const metrics = await page.evaluate(() => {
           const table = document.querySelector('.resident-table');
           const wrapper = document.querySelector('.resident-table-wrapper');
           if(!table || !wrapper) return { error: "Not found" };
           return {
               tableWidth: getComputedStyle(table).width,
               tableMinWidth: getComputedStyle(table).minWidth,
               tableCssText: table.style.cssText,
               wrapperWidth: getComputedStyle(wrapper).width,
               wrapperOverflow: getComputedStyle(wrapper).overflowX
           };
        });
        console.log(metrics);

        console.log("Taking screenshot...");
        await page.screenshot({ path: 'C:\\Users\\ASUS\\.gemini\\antigravity\\brain\\7f674135-b354-4008-8653-544c9107e818\\live_mobile_test.png', fullPage: true });
        
        await browser.close();
        console.log("Done.");
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
})();
