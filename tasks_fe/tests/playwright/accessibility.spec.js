// @ts-check

import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { beforeEachTest } from './utils/helper';

test.beforeEach(async ({ page }) => beforeEachTest(page));
test.use({ bypassCSP: true }); // required by Axebuilder

test('pass accessibility scans', async ({ page }) => {
    await page.goto('/tasks/');
    await expect(page.getByText('Login')).toBeVisible();

    const results = await new AxeBuilder({ page }).analyze();
    expect(results.violations).toEqual([]);

    await page.getByLabel('Email').fill('foo@bar.baz');
    await page.getByLabel('Password').fill('insecure');
    await page.getByText('Login').click();
    await expect(page.getByText('12345')).toBeVisible();

    const results2 = await new AxeBuilder({ page }).analyze();
    expect(results2.violations).toEqual([]);
});
