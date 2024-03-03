// @ts-check

import { readFileSync } from 'node:fs';
import { dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Emulate fetch results
 *
 * @param {URL | RequestInfo} url
 * @returns {Promise<Response>}
 */
export const fetch = (url) => {
    let content = '';
    let contentType = 'text/html';
    let status = 200;
    const path = dirname(fileURLToPath(import.meta.url));

    switch (url) {
        case '/tasks/templates/login.html':
            content = readFileSync(`${path}/../../../src/templates/login.html`).toString();
            break;
        case '/tasks/templates/tasks_list.html':
            content = readFileSync(`${path}/../../../src/templates/tasks_list.html`).toString();
            break;
        case '/tasks/templates/tasks_create.html':
            content = readFileSync(`${path}/../../../src/templates/tasks_create.html`).toString();
            break;
        case '/tasks/templates/404.html':
            content = readFileSync(`${path}/../../../src/templates/404.html`).toString();
            break;
        case '/v1/tasks?page=1&completed=0':
            content = '[{"id":12345,"title":"Test Title 123","duedate":"2024-02-01","completed":false}]';
            contentType = 'application/json';
            break;
        case '/v1/customers/login':
            content = `{"token": "Bearer .${btoa(JSON.stringify({ email: 'foo@bar.baz' }))}."}`;
            contentType = 'application/json';
            status = 201;
            break;
        case '/v1/tasks/12345':
            contentType = '';
            status = 204;
            break;
        default:
            console.error(`Undefined URL ${url}`);
            status = 501;
    }
    return new Promise((resolve) => {
        const headers = new Headers();
        headers.set('Content-Type', contentType);
        const body = content !== '' ? content : null;
        const response = new Response(body, { status: status, statusText: '', headers: headers });
        resolve(response);
    });
};
