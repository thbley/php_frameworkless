<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use TaskService\Models\Email;
use TaskService\Models\Task;
use TaskService\Services\TaskProcessingService;
use TaskService\Tests\Unit\Framework\AppMock;
use TaskService\Views\TaskCompletedEmail;

final class TaskProcessingServiceTest extends TestCase
{
    private AppMock $appMock;

    private Task $task;

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);

        $task = new Task();
        $task->id = 42;
        $task->title = 'test';
        $task->duedate = '2020-05-22';
        $task->completed = false;
        $task->last_updated_by = 'foo@invalid.local';

        $this->task = $task;
    }

    public function testProcessTaskUpdateCompleted(): void
    {
        $this->task->completed = true;

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->task = $this->task;

        $email = new Email();
        $email->from = 'Task Service <task.service@invalid.local>';
        $email->recipients = $this->task->last_updated_by;
        $email->subject = 'Task #42 completed';
        $email->content = 'foo bar content';

        $this->appMock->getTemplateService()->expects($this->once())
            ->method('render')
            ->with($taskCompletedEmail)
            ->willReturn('foo bar content');

        $this->appMock->getEmailService()->expects($this->once())
            ->method('send')
            ->with($email);

        $taskProcessingService = new TaskProcessingService($this->appMock);
        $taskProcessingService->processTaskUpdate($this->task);
    }

    public function testProcessTaskUpdateUnCompleted(): void
    {
        $this->appMock->getEmailService()->expects($this->never())
            ->method('send');

        $taskProcessingService = new TaskProcessingService($this->appMock);
        $taskProcessingService->processTaskUpdate($this->task);
    }
}
