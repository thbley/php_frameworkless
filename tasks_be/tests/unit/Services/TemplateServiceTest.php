<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Services;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Models\Task;
use TaskService\Services\TemplateService;
use TaskService\Views\TaskCompletedEmail;
use TaskService\Views\View;

final class TemplateServiceTest extends TestCase
{
    public function testRender(): void
    {
        $task = new Task(41, 'some title', '2020-01-02', false, '');

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->subject = 'Task #41 completed';
        $taskCompletedEmail->from = 'foo.sender@invalid.local';
        $taskCompletedEmail->task = $task;

        $templateService = new TemplateService();
        $actual = $templateService->render($taskCompletedEmail);

        $this->assertStringContainsString('Task <b>some title</b> completed', $actual);
    }

    public function testEscape(): void
    {
        $templateService = new TemplateService();
        $actual = $templateService->escape('Foo <Bar>');

        $this->assertSame('Foo &lt;Bar&gt;', $actual);
    }

    public function testRenderMissingTemplate(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('missing template');

        $view = new class() implements View {
            public const TEMPLATE = '';
        };

        $templateService = new TemplateService();
        $templateService->render($view);
    }

    public function testRenderMissingTemplateFile(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('missing template file');

        $view = new class() implements View {
            public const TEMPLATE = 'invalid';
        };

        $templateService = new TemplateService();
        $templateService->render($view);
    }
}
