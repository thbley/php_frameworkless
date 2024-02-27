// @ts-check

import { defineConfig, devices } from '@playwright/test';
import os from 'node:os';

// see https://playwright.dev/docs/test-configuration
export default defineConfig({
    testDir: './playwright',
    fullyParallel: false,
    forbidOnly: true,
    retries: 0,
    workers: os.cpus().length,
    reporter: 'list',
    outputDir: '/tmp/playwright-results',
    timeout: 10000,
    testIgnore: /accessibility/, // only chromium
    use: {
        baseURL: 'https://nginx',
        ignoreHTTPSErrors: true,
        acceptDownloads: false,
        locale: 'de-DE',
        timezoneId: 'Europe/Paris',
        colorScheme: 'no-preference',
        trace: 'off',
        video: 'off',
        screenshot: 'off'
    },
    expect: { timeout: 3000 },
    // see file://./node_modules/playwright-core/lib/server/deviceDescriptorsSource.json
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] }, testIgnore: ' ' },
        { name: 'chromium_hidpi', use: { ...devices['Desktop Chrome HiDPI'] } },
        { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
        { name: 'webkit', use: { ...devices['Desktop Safari'] } },
        { name: 'edge', use: { ...devices['Desktop Edge'], channel: 'msedge' } },
        { name: 'iphone', use: { ...devices['iPhone 14'] } },
        { name: 'ipad', use: { ...devices['iPad (gen 7)'] } },
        { name: 'galaxy', use: { ...devices['Galaxy S9+'] } }
    ]
});
