// @ts-check

import { AppError } from '../models/AppError.js';

export class LoginService {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} email
     * @param {string} password
     * @returns {Promise<string | AppError>}
     */
    async login(email, password) {
        const options = { method: 'POST', body: JSON.stringify({ email: email, password: password }) };

        const response = await this.app.fetchService.fetch('/v1/customers/login', options, 5000);

        if (response.status !== 201 || response.error !== '' || response.json.token === undefined) {
            return new AppError(`Login failed, ${response}`);
        }

        return String(response.json.token);
    }

    /**
     * @param {string} token
     * @returns {string | AppError}
     */
    getEmail(token) {
        if (token === '') {
            return '';
        }

        const base64Url = token.split('.')[1] ?? '';
        const payload = atob(base64Url.replace(/-/g, '+').replace(/_/g, '/'));

        try {
            return String(JSON.parse(payload).email);
        } catch (error) {
            return new AppError(`Get email failed, ${error} ${token}`);
        }
    }
}
