// @ts-check

import { DebugController } from '../controllers/DebugController.js';
import { ErrorController } from '../controllers/ErrorController.js';
import { LoginController } from '../controllers/LoginController.js';
import { TasksController } from '../controllers/TasksController.js';
import { TemplatesController } from '../controllers/TemplatesController.js';
import { CookieService } from '../services/CookieService.js';
import { FetchService } from '../services/FetchService.js';
import { LoginService } from '../services/LoginService.js';
import { TasksService } from '../services/TasksService.js';
import { TemplatesService } from '../services/TemplatesService.js';
import { PageStore } from '../stores/PageStore.js';
import { TasksStore } from '../stores/TasksStore.js';
import { Router } from './Router.js';

export class App {
    /**
     * @param {{cookie: string, title: string, location: {protocol: string, pathname: string}}} document
     * @param {function(string, RequestInit): Promise<Response>} fetch
     */
    constructor(document, fetch) {
        this.router = new Router(this);

        this.errorController = new ErrorController(this);
        this.loginController = new LoginController(this);
        this.templatesController = new TemplatesController(this);
        this.tasksController = new TasksController(this);

        this.cookieService = new CookieService(this);
        this.fetchService = new FetchService(this);
        this.templateService = new TemplatesService(this);

        this.loginService = new LoginService(this);
        this.tasksService = new TasksService(this);

        this.document = document;
        this.fetch = fetch;

        this.pageStore = new PageStore();
        this.tasksStore = new TasksStore();

        if (!globalThis.PROD) {
            this.debugController = new DebugController(this);
        }
    }
}
