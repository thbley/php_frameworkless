// @ts-check

/**
 * @vitest-environment jsdom
 */

import { expect, test } from 'vitest';
import { appTest } from '../utils/app.js';

test.concurrent('test ignore ctrl+click', async () => {
    const event = new MouseEvent('click', { ctrlKey: true });
    const pushState = { pushState: () => {} };

    const app = appTest('', fetch);
    app.router.handleLinkClick(event, pushState);
    expect(app.pageStore.route).toBe('');
});
test.concurrent('test ignore double click', async () => {
    const event = new MouseEvent('click', { detail: 2 });
    const pushState = { pushState: () => {} };

    const target = document.createElement('a');
    target.setAttribute('href', '/');
    target.dispatchEvent(event);

    const app = appTest('', fetch);
    app.router.handleLinkClick(event, pushState);
    expect(app.pageStore.route).toBe('');
});

test.concurrent('test handle click on image', async () => {
    const event = new MouseEvent('click');
    const pushState = { pushState: () => {} };

    const image = document.createElement('img');
    const target = document.createElement('a');
    target.appendChild(image);
    target.setAttribute('href', '/tasks/');
    image.dispatchEvent(event);

    const app = appTest('', fetch);
    app.router.handleLinkClick(event, pushState);

    await new Promise((resolve) => setTimeout(resolve, 0));
    expect(app.pageStore.route).toBe('/tasks/');
});
