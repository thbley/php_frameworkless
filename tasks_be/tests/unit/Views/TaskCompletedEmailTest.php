<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Views;

use PHPUnit\Framework\TestCase;
use TaskService\Models\Task;
use TaskService\Services\TemplateService;
use TaskService\Views\TaskCompletedEmail;

final class TaskCompletedEmailTest extends TestCase
{
    public function testRenderView(): void
    {
        $task = new Task();
        $task->id = 42;
        $task->title = 'example title';
        $task->last_updated_by = 'foo@invalid.local';

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->task = $task;
        $taskCompletedEmail->subject = sprintf($taskCompletedEmail->subject, $task->id);

        $templateService = new TemplateService();
        $actual = $templateService->render($taskCompletedEmail);

        $this->assertMatchesRegularExpression('!^<\!DOCTYPE html>\s+<html>\s*<body>.+?</body>\s*</html>$!s', $actual);
        $this->assertStringContainsString('Task <b>example title</b> completed!', $actual);
    }
}
