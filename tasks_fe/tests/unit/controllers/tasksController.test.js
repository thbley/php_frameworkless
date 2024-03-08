// @ts-check

import { expect, test } from 'vitest';
import { Task } from '../../../src/models/Task.js';
import { appTest } from '../utils/app.js';
import { fetch500 } from '../utils/fetch.js';

test.concurrent('test load tasks error', async () => {
    const app = appTest('', fetch500);

    const task = new Task(0, '', '', false);
    for (let i = 0; i <= 100; i++) {
        app.tasksStore.tasks.push(task);
    }

    app.pageStore.token = 'insecure';
    app.tasksStore.completed = true;
    await app.tasksController.loadTasks(1);
    expect(app.pageStore.errors[0]).toContain('500 internal server error');
});

test.concurrent('test delete task error', async () => {
    const app = appTest('', fetch500);

    app.pageStore.token = '';
    await app.tasksController.deleteTask(1);
    expect(app.pageStore.errors.length).toBe(0);

    app.pageStore.token = 'insecure';
    await app.tasksController.deleteTask(1);
    expect(app.pageStore.errors[0]).toContain('500 internal server error');
});
