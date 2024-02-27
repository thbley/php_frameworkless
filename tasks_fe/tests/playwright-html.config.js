// @ts-check

import { defineConfig, devices } from '@playwright/test';

// see https://playwright.dev/docs/test-configuration
export default defineConfig({
    testDir: './playwright',
    fullyParallel: false,
    forbidOnly: true,
    retries: 0,
    workers: 1,
    reporter: [
        ['list'],
        ['html', { open: 'always', outputFolder: '/tmp/playwright-html', host: '0.0.0.0', port: 8081 }]
    ],
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
        trace: 'on',
        video: 'on',
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
