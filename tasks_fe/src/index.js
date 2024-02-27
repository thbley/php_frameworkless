// @ts-check

import { App } from './framework/App.js';
import Alpine from './node_modules/alpinejs/dist/module.esm.js';
import { onCLS, onFCP, onFID, onINP, onLCP, onTTFB } from './node_modules/web-vitals/dist/web-vitals.attribution.js';

export class Bootstrap {
    start() {
        document.addEventListener('alpine:init', () => this.init(), { once: true });
        Alpine.start();
    }

    init() {
        const app = new App(document, (url, options) => window.fetch(url, options));

        // make data stores reactive
        app.pageStore = Alpine.reactive(app.pageStore);
        app.tasksStore = Alpine.reactive(app.tasksStore);

        // initialize routing
        app.router.syncRoute();

        // synchronize cookie with local state to catch logins/logouts from other tabs
        app.loginController.syncCookie();

        // make $app available in html templates
        Alpine.magic('app', () => app);
        Alpine.effect(() => app.loginController.updateCookie());

        this.directives(app);
        this.events(app);
        this.intervals(app);
    }

    /**
     * @param {App} app
     */
    directives(app) {
        /**
         * e.g. <div x-src="/tasks/templates/foo.html"></div>
         *
         * @param {HTMLElement} element
         * @param {{expression: string}} params
         */
        const loadSrcHtml = async (element, { expression }) => {
            element.innerHTML = await app.templatesController.load(expression);
        };
        Alpine.directive('src', loadSrcHtml);

        /**
         * e.g. <div x-text="task.id"></div>
         *
         * @param {HTMLElement} element
         * @param {{expression: string}} params
         * @param {{effect: function(function(): void): void, evaluate: function(string):string}} utils
         */
        const setText = async (element, { expression }, { effect, evaluate }) => {
            effect(() => {
                element.textContent = evaluate(expression);
            });
        };
        Alpine.directive('text', setText);

        Alpine.directive('html', () => {}); // disable raw html
    }

    /**
     * @param {App} app
     */
    events(app) {
        window.addEventListener('error', (event) => {
            app.errorController.errorUncaught(event.message);
        });
        window.addEventListener('unhandledrejection', (event) => {
            app.errorController.errorUnhandled(event.reason.message);
        });

        window.addEventListener('popstate', () => app.router.syncRoute());

        // listen on clicked links
        document.body.addEventListener('click', (event) => app.router.handleLinkClick(event, window.history));

        if (!globalThis.PROD) {
            /**
             * @param {{name: string, delta: number, value: number}} metric
             */
            const updateVitals = (metric) => app.debugController?.updateWebVitals(metric);
            onCLS(updateVitals);
            onFID(updateVitals);
            onFCP(updateVitals);
            onLCP(updateVitals);
            onINP(updateVitals);
            onTTFB(updateVitals);
        }
    }

    /**
     * @param {App} app
     */
    intervals(app) {
        window.setInterval(() => app.loginController.syncCookie(), 1000);

        if (!globalThis.PROD && 'memory' in window.performance) {
            window.setInterval(() => app.debugController?.updateMemory(window.performance.memory), 2000);
        }
    }
}

new Bootstrap().start();
