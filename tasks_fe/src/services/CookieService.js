// @ts-check

export class CookieService {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {string} name
     * @returns {string}
     */
    getCookie(name) {
        const value = `; ${this.app.document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return String(parts.pop()?.split(';').shift());
        }

        return '';
    }

    /**
     * @param {string} name
     * @param {string} value
     * @param {number} expiry seconds
     * @returns {string}
     */
    setCookie(name, value, expiry) {
        const protocol = this.app.document.location.protocol;
        const secure = protocol === 'https:' ? '; Secure' : '';

        this.app.document.cookie = `${name}=${value}; Max-Age=${expiry}; path=/; SameSite=Strict${secure}`;

        return this.app.document.cookie;
    }
}
