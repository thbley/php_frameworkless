// @ts-check

import { builtinEnvironments } from 'vitest/environments';

/**
 * @param {string} html
 * @param {string} script
 * @param {string} url
 * @param {string} cookie
 * @param {function(URL | RequestInfo): Promise<Response>} fetch
 * @returns {Promise<{teardown: function}>}
 */
export async function render(html, script, url, cookie, fetch) {
    const env = await builtinEnvironments['happy-dom'].setup(global, { happyDOM: { url: url } });

    document.write(html.replace(/<script[^>]+><\/script>/, '').replace(/<link rel="stylesheet"[^>]+>/, ''));

    window.fetch = fetch;
    window.confirm = () => true;
    window.console.warn = () => {};
    window.document.cookie = `${cookie}; Max-Age=60; path=/; SameSite=Strict`;
    window.performance.memory = { totalJSHeapSize: 10485760 * 2, usedJSHeapSize: 10485760 };
    // @ts-ignore
    window.setInterval = (handler) => setTimeout(() => handler(), 10);

    // use system loader for code coverage, add timestamp to re-load module
    await import(`${script}?${Date.now()}`);

    return {
        teardown: async () => {
            // wait for window.setInterval
            await new Promise((resolve) => setTimeout(resolve, 10));
            await env.teardown(global);
        },
    };
}

/**
 * @param {string} tag
 * @param {string} text
 * @returns {HTMLElement?}
 */
export function getElementByText(tag, text) {
    for (const node of Array.from(document.querySelectorAll(tag))) {
        if (node.textContent === text && node instanceof HTMLElement) {
            return node;
        }
    }
    return null;
}

/**
 * @param {string} name
 * @param {string} value
 */
export function fillInputByName(name, value) {
    const element = document.getElementsByName(name)[0];
    if (element instanceof HTMLInputElement) {
        element.value = value;
    }
}

/**
 * @param {string} text
 * @returns {Promise<string>}
 */
export async function waitForText(text) {
    return await new Promise((resolve) => {
        const interval = setInterval(() => {
            const html = document.documentElement.outerHTML;
            if (html.includes(text)) {
                clearInterval(interval);
                resolve(html);
            }
        }, 100);
    });
}
