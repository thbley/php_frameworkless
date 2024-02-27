// @ts-check

export class ErrorController {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} message
     */
    errorUncaught(message) {
        const pageStore = this.app.pageStore;
        pageStore.errors.push(`Uncaught error, ${message}`);
    }

    /**
     * @param {string} message
     */
    errorUnhandled(message) {
        const pageStore = this.app.pageStore;
        pageStore.errors.push(`Unhandled rejection, ${message}`);
    }
}
