// @ts-check

import { emulateApiFetch } from '../utils/fetch.js';

/**
 * @param {import("@playwright/test").Page} page
 */
export const beforeEachTest = (page) => {
    page.on('console', (msg) => {
        if (!msg.text().match(/Autofocus processing|status of 401/)) {
            throw new Error(msg.text()); // e.g. console.error
        }
    });
    page.on('requestfailed', (request) => {
        if (!request.failure()?.errorText.includes('204 No Content')) {
            throw new Error(`${request.url()} ${request.failure()?.errorText}`); // e.g. network error, timeout
        }
    });
    page.on('pageerror', (error) => {
        throw new Error(String(error)); // e.g. JS SyntaxError
    });
    page.on('dialog', (dialog) => dialog.accept()); // always accept dialogs
    page.route('/v1/**', (route) => emulateApiFetch(route)); // mock api calls

    // reduce setInterval delay to 10 ms, run once
    page.addInitScript('{ window.setInterval = (handler) => setTimeout(() => handler(), 10); }');
};

/**
 * @param {import("@playwright/test").Page} page
 */
export const afterEachTest = async (page) => {
    if (await page.isVisible('.debug')) {
        console.info('  debug:', (await page.locator('.debug').textContent())?.replace(/\s+/g, ' ')); // show debug info
    }

    // output resource metrics
    const timings = await page.evaluate(() => window.performance.getEntriesByType('resource'));
    const duration = timings.reduce((a, b) => a + b.duration, 0).toFixed(2);
    const transferSize = (timings.reduce((a, b) => a + b.transferSize, 0) / 1024).toFixed(0);
    const size = (timings.reduce((a, b) => a + b.decodedBodySize, 0) / 1024).toFixed(0);
    console.info(`  duration: ${duration} ms, transfer size: ${transferSize} kb, size: ${size} kb`);

    // output user agent
    const userAgent = await page.evaluate(() => navigator.userAgent);
    console.info('  version:', userAgent.substring(userAgent.lastIndexOf(')') + 2));
};
