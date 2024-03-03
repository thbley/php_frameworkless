// @ts-check

import { readFileSync } from 'node:fs';
import { expect, test } from 'vitest';
import { fetch as emulateFetch } from './utils/fetch.js';
import { fillInputByName, getElementByText, render, waitForText } from './utils/jsdom.js';

// TODO split to multiple files

const indexHtml = readFileSync('../src/index.html', 'utf8');
const script = '../../../src/index.js';

test('test fetch error', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/', '', fetch);

    const html = await waitForText('TypeError');
    expect(html).not.toContain('template-login');
    expect(html).toContain('data-error-count="1"');
    expect(html).toContain('TypeError: Failed to parse URL');

    await env.teardown();
});

test('test tasks list', async () => {
    const cookie = `token=Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`; // emulate JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('12345');
    expect(html).toContain('</html>');
    expect(html).toContain('invalid@invalid.local');
    expect(html).toContain('template-tasks');
    expect(html).toContain('12345');
    expect(html).toContain('Test Title 123');
    expect(html).toContain('2024-02-01');
    expect(html).toContain('data-error-count="0"');

    const html2 = await waitForText('JSHeapSize');
    expect(html2).toContain('JSHeapSize used 10.00 mb, total 20.00 mb');

    await env.teardown();
});

test('test create page', async () => {
    const cookie = `token=Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`; // emulate JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/create', cookie, emulateFetch);

    const html = await waitForText('template-tasks-create');
    expect(html).toContain('</html>');
    expect(html).toContain('invalid@invalid.local');
    expect(html).toContain('data-error-count="0"');

    await env.teardown();
});

test('test invalid cookie', async () => {
    const cookie = 'token=Bearer foo bar'; // invalid JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('template-login');
    expect(html).toContain('data-error-count="1"');
    expect(html).toContain('SyntaxError: Unexpected end of JSON');

    await env.teardown();
});

test('test unmatched cookie', async () => {
    const cookie = 'foo=bar';
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('template-login');
    expect(html).toContain('data-error-count="0"');

    await env.teardown();
});

test('test not found', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/invalid', '', emulateFetch);

    const html = await waitForText('template-404');
    expect(html).toContain('</html>');
    expect(html).toContain('data-error-count="0"');

    await env.teardown();
});

test('test index.html', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/index.html', '', emulateFetch);

    const html = await waitForText('template-404');
    expect(html).toContain('data-error-count="0"');

    await env.teardown();
});

test('test login', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/', '', emulateFetch);

    const html = await waitForText('template-login');
    expect(html).toContain('</html>');
    expect(html).toContain('data-error-count="0"');

    fillInputByName('email', 'foo@bar.baz');
    fillInputByName('password', 'insecure');
    getElementByText('button', 'Login')?.click();

    const html2 = await waitForText('12345');
    expect(html2).toContain('template-tasks');
    expect(html2).toContain('data-error-count="0"');
    await env.teardown();
});

test('test logout', async () => {
    const cookie = `token=Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`; // emulate JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('template-tasks');
    expect(html).toContain('invalid@invalid.local');
    expect(html).toContain('12345');
    expect(html).toContain('Test Title 123');
    expect(html).toContain('2024-02-01');
    expect(html).toContain('data-error-count="0"');

    getElementByText('a', 'Logout')?.click();

    const html2 = await waitForText('template-login');
    expect(html2).toContain('data-error-count="0"');
    expect(html2).not.toContain('invalid@invalid.local');
    await env.teardown();
});

test('test clicks', async () => {
    const cookie = `token=Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`; // emulate JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('template-tasks');
    expect(html).toContain('12345');
    expect(html).toContain('data-error-count="0"');

    getElementByText('a', 'Create task')?.click();

    const html2 = await waitForText('template-tasks-create');
    expect(html2).not.toContain('12345');
    expect(html2).toContain('data-error-count="0"');

    getElementByText('a', 'Home')?.click();

    const html3 = await waitForText('template-tasks');
    expect(html3).toContain('12345');
    expect(html3).toContain('data-error-count="0"');
    await env.teardown();
});

test('test task delete', async () => {
    const cookie = `token=Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`; // emulate JWT token
    const env = await render(indexHtml, script, 'https://nginx/tasks/', cookie, emulateFetch);

    const html = await waitForText('template-tasks');
    expect(html).toContain('12345');
    expect(html).toContain('data-error-count="0"');

    getElementByText('a', 'delete')?.click();

    const html2 = await waitForText('No tasks');
    expect(html2).toContain('template-tasks');
    expect(html2).toContain('data-error-count="0"');
    expect(html2).not.toContain('12345');

    await env.teardown();
});

test('test uncaught error', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/', '', emulateFetch);

    window.dispatchEvent(new ErrorEvent('error', { message: 'some message' }));

    const html = await waitForText('template-login');
    expect(html).toContain('data-error-count="1"');
    expect(html).toContain('Uncaught error, some message');

    await env.teardown();
});

test('test unhandled rejection', async () => {
    const env = await render(indexHtml, script, 'https://nginx/tasks/', '', emulateFetch);

    class RejectionEvent extends Event {
        /**
         * @param {string} type
         * @param {{ message: string }} reason
         */
        constructor(type, reason) {
            super(type);
            this.reason = reason;
        }
    }
    window.dispatchEvent(new RejectionEvent('unhandledrejection', { message: 'some message' }));

    const html = await waitForText('template-login');
    expect(html).toContain('data-error-count="1"');
    expect(html).toContain('Unhandled rejection, some message');

    await env.teardown();
});
