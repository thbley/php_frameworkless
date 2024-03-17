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
        $task = new Task(42, 'example title', '2020-01-02', false, 'foo@invalid.local');

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->task = $task;
        $taskCompletedEmail->subject = sprintf($taskCompletedEmail->subject, $task->id);

        $templateService = new TemplateService();
        $actual = $templateService->render($taskCompletedEmail);

        $this->assertMatchesRegularExpression('!^<\!DOCTYPE html>\s+<html>\s*<body>.+?</body>\s*</html>$!s', $actual);
        $this->assertStringContainsString('Task <b>example title</b> completed!', $actual);
    }
}
