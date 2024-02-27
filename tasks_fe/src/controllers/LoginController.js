// @ts-check

import { AppError } from '../models/AppError.js';

export class LoginController {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    syncCookie() {
        const pageStore = this.app.pageStore;
        pageStore.cookie = this.app.document.cookie;
    }

    // listen on pageStore.cookie changes, update depending properties
    updateCookie() {
        const pageStore = this.app.pageStore;
        pageStore.cookie += ''; // needed for reactive tracking
        pageStore.token = this.app.cookieService.getCookie('token');

        const email = this.app.loginService.getEmail(pageStore.token);
        if (email instanceof AppError) {
            pageStore.errors.push(email.error);
            return;
        }

        pageStore.email = email;
        pageStore.loggedin = pageStore.email !== '';

        this.app.document.title = `${pageStore.email} PHP Tasks REST API`;
    }

    /**
     * @param {string} email
     * @param {string} password
     */
    async login(email, password) {
        const token = await this.app.loginService.login(email, password);

        const pageStore = this.app.pageStore;
        if (token instanceof AppError) {
            pageStore.errors.push(token.error);
            return;
        }

        pageStore.cookie = this.app.cookieService.setCookie('token', token, 86400);
    }

    logout() {
        const pageStore = this.app.pageStore;
        pageStore.cookie = this.app.cookieService.setCookie('token', '', 0);

        const tasksStore = this.app.tasksStore;
        tasksStore.tasks = [];
    }
}
