// @ts-check

import { FetchResponse } from '../models/FetchResponse.js';

export class FetchService {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} url
     * @param {RequestInit} options
     * @param {number} timeoutMs
     * @returns {Promise<FetchResponse>}
     */
    async fetch(url, options, timeoutMs) {
        const controller = new AbortController();
        const signal = controller.signal;
        setTimeout(() => controller.abort(), timeoutMs);
        options.signal = signal;

        const fetchResponse = new FetchResponse(0, '', '', null, '');
        try {
            const response = await this.app.fetch(url, options);
            fetchResponse.status = response.status;
            fetchResponse.statusText = response.statusText;
            fetchResponse.body = await response.text();
            if (response.headers.get('content-type')?.startsWith('application/json')) {
                fetchResponse.json = JSON.parse(fetchResponse.body);
            }
        } catch (error) {
            fetchResponse.error = String(error);
        }

        return fetchResponse;
    }
}
