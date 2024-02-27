// @ts-check

export class FetchResponse {
    /**
     * @param {number} status
     * @param {string} statusText
     * @param {string} body
     * @param {any} json
     * @param {string} error
     */
    constructor(status, statusText, body, json, error) {
        this.status = status;
        this.statusText = statusText;
        this.body = body;
        this.json = json;
        this.error = error;
    }

    toString() {
        return `status: ${this.status} ${this.statusText}, body: ${this.body}, ${this.error}`;
    }
}
