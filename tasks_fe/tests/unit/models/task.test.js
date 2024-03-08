// @ts-check

import { expect, test } from 'vitest';
import { Task } from '../../../src/models/Task.js';

test.concurrent('test task constructor error', async () => {
    const title = JSON.parse('null');
    expect(() => new Task(0, title, '', false)).toThrowError('undefined property title');
});
