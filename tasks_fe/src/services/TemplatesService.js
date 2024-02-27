// @ts-check

import { AppError } from '../models/AppError.js';

export class TemplatesService {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} url
     * @returns {Promise<string | AppError>}
     */
    async load(url) {
        const response = await this.app.fetchService.fetch(url, { method: 'GET' }, 5000);

        if (response.status !== 200 || response.error !== '') {
            return new AppError(`Cannot load ${url}, ${response}`);
        }

        return response.body;
    }
}
