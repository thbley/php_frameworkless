<?php

namespace TaskService\Views;

use TaskService\Models\Task;

class TaskCompletedEmail implements View
{
    public const TEMPLATE = __DIR__ . '/TaskCompletedEmailTemplate.php';

    public string $from = 'Task Service <task.service@invalid.local>';

    public string $subject = 'Task #%s completed';

    public Task $task;
}
