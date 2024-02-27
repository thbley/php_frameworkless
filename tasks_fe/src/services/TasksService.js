// @ts-check

import { AppError } from '../models/AppError.js';
import { Task } from '../models/Task.js';

export class TasksService {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {boolean} completed
     * @param {string} token
     * @returns {Promise<Task[] | AppError>}
     */
    async getTasks(completed, token) {
        const params = completed ? '?completed=1' : '';
        const url = `/v1/tasks${params}`;

        const options = { method: 'GET', headers: { Authorization: token } };

        const response = await this.app.fetchService.fetch(url, options, 5000);
        if (response.status !== 200 || response.error !== '') {
            return new AppError(`Cannot load tasks, ${response}`);
        }

        const results = [];
        for (const row of response.json) {
            results.push(new Task(row.id, row.title, row.duedate, row.completed));
        }

        return results;
    }

    /**
     * @param {number} id
     * @param {string} token
     * @returns {Promise<AppError | null>}
     */
    async deleteTask(id, token) {
        const url = '/v1/tasks/'.concat(encodeURIComponent(id));

        const options = { method: 'DELETE', headers: { Authorization: token } };

        const response = await this.app.fetchService.fetch(url, options, 5000);
        if (response.status !== 204 || response.body !== '') {
            return new AppError(`Cannot delete task, ${response}`);
        }

        return null;
    }
}
