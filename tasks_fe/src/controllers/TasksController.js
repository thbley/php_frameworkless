// @ts-check

import { AppError } from '../models/AppError.js';

export class TasksController {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    async loadTasks() {
        const tasksStore = this.app.tasksStore;
        const pageStore = this.app.pageStore;

        // avoid concurrent requests
        if (pageStore.token === '' || tasksStore.loading) {
            return;
        }

        tasksStore.loading = true;

        // avoid memory leaks
        tasksStore.tasks = [];

        const tasks = await this.app.tasksService.getTasks(tasksStore.completed, pageStore.token);
        if (tasks instanceof AppError) {
            pageStore.errors.push(tasks.error);
        } else {
            tasksStore.tasks = tasks;
        }

        tasksStore.loading = false;
    }

    /**
     * @param {number} id
     */
    async deleteTask(id) {
        const tasksStore = this.app.tasksStore;
        const pageStore = this.app.pageStore;

        if (pageStore.token === '') {
            return;
        }

        const result = await this.app.tasksService.deleteTask(id, pageStore.token);
        if (result instanceof AppError) {
            pageStore.errors.push(result.error);
            return;
        }

        tasksStore.removeById(id);
    }
}
