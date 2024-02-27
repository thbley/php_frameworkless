// @ts-check

import { expect, test } from '@playwright/test';
import { afterEachTest, beforeEachTest } from './utils/helper';

test.beforeEach(async ({ page }) => beforeEachTest(page));
test.afterEach(async ({ page }) => afterEachTest(page));

test('has task list after login', async ({ page }) => {
    // login
    await page.goto('/tasks/');
    await expect(page).toHaveTitle('PHP Tasks REST API');
    await expect(page.locator('.template-login')).toHaveCount(1);
    await page.getByLabel('Email').fill('foo@bar.baz');
    await page.getByLabel('Password').fill('insecure');
    await page.getByText('Login').click();

    // validate task list
    await expect(page).toHaveTitle('foo@bar.baz PHP Tasks REST API');
    await expect(page.locator('.template-tasks')).toHaveCount(1);
    await expect(page.getByText('(1)')).toBeVisible();
    await expect(page.getByText('foo@bar.baz')).toBeVisible();
    await expect(page.getByText('12345')).toBeVisible();
    await expect(page.getByText('Test Title 123')).toBeVisible();
    await expect(page.getByText('2024-02-01')).toBeVisible();

    // validate completed task list
    await page.getByText('Show completed').click();
    await expect(page.getByText('(1)')).toBeVisible();
    await expect(page.getByText('54321')).toBeVisible();
    await expect(page.getByText('Test Title completed')).toBeVisible();
    await expect(page.getByText('12345')).not.toBeVisible();
    await expect(page.locator('[data-error-count="0"]')).toHaveCount(1);
});

test('has no task list after logout', async ({ page }) => {
    // login
    await page.goto('/tasks/');
    await expect(page.locator('.template-login')).toHaveCount(1);
    await page.getByLabel('Email').fill('foo@bar.baz');
    await page.getByLabel('Password').fill('insecure');
    await page.getByText('Login').click();

    // validate task list
    await expect(page.locator('.template-tasks')).toHaveCount(1);
    await expect(page.getByText('(1)')).toBeVisible();
    await expect(page.getByText('foo@bar.baz')).toBeVisible();
    await expect(page.getByText('12345')).toBeVisible();

    // logout, check no task list
    await page.getByText('Logout').click();
    await expect(page.locator('.template-login')).toHaveCount(1);
    await expect(page.getByText('foo@bar.baz')).not.toBeVisible();
    await expect(page.getByText('12345')).not.toBeVisible();
    await expect(page.locator('[data-error-count="0"]')).toHaveCount(1);
});

test('can delete task after login', async ({ page }) => {
    // login
    await page.goto('/tasks/');
    await page.getByLabel('Email').fill('foo@bar.baz');
    await page.getByLabel('Password').fill('insecure');
    await page.getByText('Login').click();

    // validte task delete
    await expect(page.getByText('12345')).toBeVisible();
    await page.getByText('delete').click();
    await expect(page.getByText('12345')).not.toBeVisible();
    await expect(page.locator('[data-error-count="0"]')).toHaveCount(1);
});

test('can navigate to create task, reload and back', async ({ page }) => {
    // login
    await page.goto('/tasks/');
    await page.getByLabel('Email').fill('foo@bar.baz');
    await page.getByLabel('Password').fill('insecure');
    await page.getByText('Login').click();

    // validate task list
    await expect(page.locator('.template-tasks')).toHaveCount(1);
    await expect(page.getByText('12345')).toBeVisible();

    // navidate to create task
    await page.getByText('Create task').first().click();
    await expect(page.locator('.template-tasks-create')).toHaveCount(1);
    await expect(page.getByText('12345')).not.toBeVisible();

    // navidate back to task list
    await page.goBack();
    await expect(page.locator('.template-tasks')).toHaveCount(1);
    await expect(page.getByText('12345')).toBeVisible();
    await expect(page.locator('[data-error-count="0"]')).toHaveCount(1);
});

test('can fail login', async ({ page }) => {
    // login
    await page.goto('/tasks/');
    await page.getByLabel('Email').fill('invalid@invalid.local');
    await page.getByLabel('Password').fill('invalid');
    await page.getByText('Login').click();

    // validte login fail
    await expect(page.getByText('Login failed')).toBeVisible();
    await expect(page.locator('.template-login')).toHaveCount(1);

    // clear errors
    await page.getByText('✖️').click();
    await expect(page.getByText('Login failed')).not.toBeVisible();
    await expect(page.locator('[data-error-count="0"]')).toHaveCount(1);
});
