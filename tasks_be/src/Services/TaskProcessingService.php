<?php

namespace TaskService\Services;

use TaskService\Framework\App;
use TaskService\Models\Email;
use TaskService\Models\Task;
use TaskService\Views\TaskCompletedEmail;

class TaskProcessingService
{
    public function __construct(private App $app) {}

    public function processTaskUpdate(Task $task): void
    {
        if ($task->completed) {
            $taskCompletedEmail = new TaskCompletedEmail();
            $taskCompletedEmail->task = $task;

            $email = new Email(
                sprintf($taskCompletedEmail->subject, $task->id),
                $taskCompletedEmail->from,
                $task->last_updated_by,
                $this->app->getTemplateService()->render($taskCompletedEmail)
            );

            $this->app->getEmailService()->send($email);
        }
    }
}
