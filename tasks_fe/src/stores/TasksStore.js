// @ts-check

// reactive, use $app.tasksStore.x in HTML template
export class TasksStore {
    /**
     * @typedef {import('../models/Task.js').Task} Task
     */
    constructor() {
        this.loading = false;
        this.page = 1;
        this.completed = false;
        /** @type {Task[]} */
        this.tasks = [];
    }

    /**
     * @param {number} id
     */
    removeById(id) {
        for (let index = 0, length = this.tasks.length; index < length; index++) {
            if (this.tasks[index]?.id === id) {
                this.tasks.splice(index, 1);
                break;
            }
        }
    }
}
