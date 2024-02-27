// @ts-check

// reactive, use $app.pageStore.x in HTML template
export class PageStore {
    constructor() {
        this.cookie = '';
        this.token = '';
        this.email = '';
        this.loggedin = false;
        this.route = '';

        /** @type {string[]} */
        this.errors = [];

        this.debugJsHeap = '';
        this.debugWebVitals = '';
    }
}
