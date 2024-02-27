// @ts-check

export class Router {
    #routes = ['/tasks/', '/tasks/create', '/tasks/edit/(\\d+)'];

    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    syncRoute() {
        this.app.pageStore.route = this.matchRoute(this.app.document.location.pathname);
    }

    /**
     * @param {MouseEvent} event
     * @param {{pushState: function({path: string}, string, string):void}} history
     */
    handleLinkClick(event, history) {
        if (event.ctrlKey || event.metaKey || event.altKey || event.shiftKey || event.button) {
            return;
        }
        const target = event.target instanceof HTMLImageElement ? event.target.closest('a') : event.target;
        if (!(target instanceof HTMLAnchorElement) || target.hasAttribute('native')) {
            return;
        }
        const href = String(target.getAttribute('href'));
        if (href === '') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        // ignore second event on double click
        if (event.detail === 2) {
            return;
        }
        const route = this.app.router.matchRoute(href);

        history.pushState({ path: href }, '', href);

        this.app.pageStore.route = ''; // unload page
        // Alpine.nextTick
        setTimeout(() => {
            this.app.pageStore.route = route;
        }, 0);
    }

    /**
     * @param {string} pathName
     * @returns {string}
     */
    matchRoute(pathName) {
        for (const route of this.#routes) {
            if (pathName.match(new RegExp(`^${route}$`))) {
                return route;
            }
        }
        return 'notFound';
    }
}
