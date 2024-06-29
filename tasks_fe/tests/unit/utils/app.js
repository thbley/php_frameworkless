// @ts-check

import { App } from '../../../src/framework/App.js';

/**
 * @param {string} cookie
 * @param {function(string): Promise<Response>} fetch
 * @returns {App}
 */
export function appTest(cookie, fetch) {
    return new App({ cookie: cookie, title: '', location: { protocol: '', pathname: '' } }, fetch);
}
