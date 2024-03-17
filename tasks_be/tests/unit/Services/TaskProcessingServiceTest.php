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

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);
    }

    public function testProcessTaskUpdateCompleted(): void
    {
        $task = new Task(42, 'test', '2020-05-22', true, 'foo@invalid.local');

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->task = $task;

        $email = new Email(
            'Task #42 completed',
            'Task Service <task.service@invalid.local>',
            $task->last_updated_by,
            'foo bar content'
        );

        $this->appMock->getTemplateService()->expects($this->once())
            ->method('render')
            ->with($taskCompletedEmail)
            ->willReturn('foo bar content');

        $this->appMock->getEmailService()->expects($this->once())
            ->method('send')
            ->with($email);

        $taskProcessingService = new TaskProcessingService($this->appMock);
        $taskProcessingService->processTaskUpdate($task);
    }

    public function testProcessTaskUpdateUnCompleted(): void
    {
        $task = new Task(42, 'test', '2020-05-22', false, 'foo@invalid.local');

        $this->appMock->getEmailService()->expects($this->never())
            ->method('send');

        $taskProcessingService = new TaskProcessingService($this->appMock);
        $taskProcessingService->processTaskUpdate($task);
    }
}
