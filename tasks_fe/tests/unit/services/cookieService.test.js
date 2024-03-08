// @ts-check

import { expect, test } from 'vitest';
import { appTest } from '../utils/app.js';

test.concurrent('test get cookie', () => {
    const app = appTest('foo=bar; bar=baz', fetch);

    const foo = app.cookieService.getCookie('foo');
    expect(foo).toEqual('bar');

    const bar = app.cookieService.getCookie('bar');
    expect(bar).toEqual('baz');

    const baz = app.cookieService.getCookie('baz');
    expect(baz).toEqual('');
});

test.concurrent('test set cookie', () => {
    const app = appTest('', fetch);

    const cookie = app.cookieService.setCookie('foo', 'bar', 600);
    expect(cookie).toEqual('foo=bar; Max-Age=600; path=/; SameSite=Strict');
    expect(app.document.cookie).toEqual(cookie);

    const cookieRemoved = app.cookieService.setCookie('foo', '', 0);
    expect(cookieRemoved).toEqual('foo=; Max-Age=0; path=/; SameSite=Strict');

    app.document.location.protocol = 'https:';
    const cookieHttps = app.cookieService.setCookie('foo', 'baz', 300);
    expect(cookieHttps).toEqual('foo=baz; Max-Age=300; path=/; SameSite=Strict; Secure');
});
