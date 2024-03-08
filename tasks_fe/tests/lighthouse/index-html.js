// @ts-check

import puppeteer from 'puppeteer-core';
import { formatOutput, lightHouse } from './utils/lighthouse.js';

const browser = await puppeteer.launch({
    headless: true,
    executablePath: 'chrome',
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
    ignoreHTTPSErrors: true,
});
const tasksUrl = 'https://nginx/tasks/';
const loginUrl = 'http://nginx:8080/v1/customers/login';

await (async () => {
    const page = await browser.newPage();
    page.setCookie({ domain: 'nginx', name: 'token', value: '' });

    const desktop = await lightHouse(page, tasksUrl, './lh_login_desktop.html', false);
    const mobile = await lightHouse(page, tasksUrl, './lh_login_mobile.html', true);

    console.info('Login (desktop, mobile)');
    console.info(formatOutput(desktop, mobile).join('\n'), '\n');

    page.close();
})();

await (async () => {
    const body = JSON.stringify({ email: 'foo@bar.baz', password: 'insecure' });
    const login = await fetch(loginUrl, { method: 'POST', body: body });
    const token = String((await login.json()).token);

    const page = await browser.newPage();
    page.setCookie({ domain: 'nginx', name: 'token', value: token });

    const desktop = await lightHouse(page, tasksUrl, './lh_list_desktop.html', false);
    const mobile = await lightHouse(page, tasksUrl, './lh_list_mobile.html', true);

    console.info('Task List (desktop, mobile)');
    console.info(formatOutput(desktop, mobile).join('\n'), '\n');

    page.close();
})();

browser.close();
