// @ts-check

import { expect, test } from 'vitest';
import { appTest } from '../utils/app.js';
import { fetch500, fetchReject } from '../utils/fetch.js';

test.concurrent('test login error', async () => {
    const app = appTest('', fetch500);
    await app.loginController.login('', '');

    expect(app.pageStore.errors[0]).toContain('500 internal server error');
});

test.concurrent('test login reject', async () => {
    const app = appTest('', fetchReject);
    await app.loginController.login('', '');

    expect(app.pageStore.errors[0]).toContain('Error: rejected');
});
