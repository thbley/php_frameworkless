// @ts-check

export class Task {
    /**
     * @param {number} id
     * @param {string} title
     * @param {string} duedate
     * @param {boolean} completed
     */
    constructor(id, title, duedate, completed) {
        this.id = id;
        this.title = title;
        this.duedate = duedate;
        this.completed = completed;

        for (const key in this) {
            if (this[key] === undefined || this[key] === null) {
                throw new Error(`undefined property ${key}`);
            }
        }
    }
}
