// @ts-check

import { expect, test } from 'vitest';
import { AppError } from '../../../src/models/AppError.js';
import { appTest } from '../utils/app.js';

test.concurrent('test get email', () => {
    // emulate JWT token
    const token = `Bearer .${btoa(JSON.stringify({ email: 'invalid@invalid.local' }))}.`;

    const app = appTest('', fetch);
    const email = app.loginService.getEmail(token);

    expect(email).toEqual('invalid@invalid.local');
});

test.concurrent('test get email invalid token', () => {
    const app = appTest('', fetch);
    const email = app.loginService.getEmail('Bearer ..');

    expect(email).toBeInstanceOf(AppError);
    expect(email instanceof AppError ? email.error : '').toContain('SyntaxError: Unexpected end of JSON');
});

test.concurrent('test get email invalid token #2', () => {
    const app = appTest('', fetch);
    const email = app.loginService.getEmail('Bearer foo bar');

    expect(email).toBeInstanceOf(AppError);
    expect(email instanceof AppError ? email.error : '').toContain('SyntaxError: Unexpected end of JSON');
});

test.concurrent('test get email invalid token #3', () => {
    const app = appTest('', fetch);
    const email = app.loginService.getEmail('Bearer .asd.');

    expect(email).toBeInstanceOf(AppError);
    expect(email instanceof AppError ? email.error : '').toContain('SyntaxError: Unexpected token');
});
