// @ts-check

import { AppError } from '../models/AppError.js';

export class TemplatesController {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} url
     * @returns {Promise<string>}
     */
    async load(url) {
        const content = await this.app.templateService.load(url);
        const pageStore = this.app.pageStore;

        if (content instanceof AppError) {
            pageStore.errors.push(content.error);
            return '';
        }

        return content;
    }
}
