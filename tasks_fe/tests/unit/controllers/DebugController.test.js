// @ts-check

import { expect, test } from 'vitest';
import { appTest } from '../utils/app.js';

test.concurrent('test debug memory', async () => {
    const app = appTest('', fetch);

    app.debugController?.updateMemory(undefined);
    expect(app.pageStore.debugJsHeap).toBe('');

    app.debugController?.updateMemory({ totalJSHeapSize: 12345678, usedJSHeapSize: 1234567 });
    expect(app.pageStore.debugJsHeap).toBe('JSHeapSize used 1.18 mb, total 11.77 mb');
});

test.concurrent('test debug web vitals', async () => {
    const app = appTest('', fetch);
    app.debugController?.updateWebVitals({ name: 'test', delta: 42, value: 142.42 });

    expect(app.pageStore.debugWebVitals).toContain('test 142.4 Δ42');
});
