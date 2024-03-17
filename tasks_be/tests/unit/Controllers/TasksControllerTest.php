<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use TaskService\Controllers\TasksController;
use TaskService\Exceptions\HttpException;
use TaskService\Models\Customer;
use TaskService\Models\Task;
use TaskService\Tests\Unit\Framework\AppMock;
use TaskService\Views\TaskCompletedEmail;

final class TasksControllerTest extends TestCase
{
    private AppMock $appMock;

    private Customer $customer;

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);

        $this->customer = new Customer(41, 'foo@invalid.local');
    }

    public function testGetCurrentTasks(): void
    {
        $task = new Task(42, '', '', false, '');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getCurrentTasks')
            ->with($this->customer, 42)
            ->willReturn([$task]);

        $tasksController = new TasksController($this->appMock);

        $this->assertSame([$task], $tasksController->getCurrentTasks($this->customer, 42));
    }

    public function testGetCompletedTasks(): void
    {
        $task = new Task(42, '', '', false, '');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getCompletedTasks')
            ->with($this->customer, 42)
            ->willReturn([$task]);

        $tasksController = new TasksController($this->appMock);

        $this->assertSame([$task], $tasksController->getCompletedTasks($this->customer, 42));
    }

    public function testGetTask(): void
    {
        $task = new Task(42, '', '', false, '');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(true);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getTasks')
            ->with([42])
            ->willReturn([$task]);

        $tasksController = new TasksController($this->appMock);

        $this->assertSame($task, $tasksController->getTask($this->customer, 42));
    }

    public function testGetTaskNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('task not found');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(true);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getTasks')
            ->with([42])
            ->willReturn([]);

        $tasksController = new TasksController($this->appMock);
        $tasksController->getTask($this->customer, 42);
    }

    public function testGetTaskNotFoundForCustomer(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('task not found');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(false);

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('getTasks');

        $tasksController = new TasksController($this->appMock);
        $tasksController->getTask($this->customer, 42);
    }

    public function testDeleteTask(): void
    {
        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('deleteTask')
            ->with(42);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(true);

        $tasksController = new TasksController($this->appMock);
        $tasksController->deleteTask($this->customer, 42);
    }

    public function testDeleteTaskNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('task not found');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(false);

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('deleteTask');

        $tasksController = new TasksController($this->appMock);
        $tasksController->deleteTask($this->customer, 42);
    }

    public function testCreateTask(): void
    {
        $task = new Task(0, 'Test', '2020-05-22', false, $this->customer->email);
        $task2 = new Task(42, 'Test', '2020-05-22', false, $this->customer->email);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getTask')
            ->with(0, 'Test', '2020-05-22', false, $this->customer->email)
            ->willReturn($task);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('createTask')
            ->with($this->customer, $task)
            ->willReturn($task2);

        $tasksController = new TasksController($this->appMock);

        $this->assertEquals($task2, $tasksController->createTask($this->customer, 'Test', '2020-05-22'));
    }

    public function testCreateTaskMissingTitle(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('missing title');

        $tasksController = new TasksController($this->appMock);
        $tasksController->createTask($this->customer, '', '2020-05-22');
    }

    public function testCreateTaskInvalidDuedate(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('invalid duedate');

        $tasksController = new TasksController($this->appMock);
        $tasksController->createTask($this->customer, 'test', 'tomorrow');
    }

    public function testUpdateTask(): void
    {
        $task = new Task(42, 'test', '2020-05-22', true, $this->customer->email);

        $taskCompletedEmail = new TaskCompletedEmail();
        $taskCompletedEmail->task = $task;

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, $task->id)
            ->willReturn(true);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getTask')
            ->with(42, 'test', '2020-05-22', true, $this->customer->email)
            ->willReturn($task);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('updateTask')
            ->with($task);

        $tasksController = new TasksController($this->appMock);
        $tasksController->updateTask($this->customer, $task->id, $task->title, $task->duedate, $task->completed);
    }

    public function testUpdateTaskInvalidTitle(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('missing title');

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('updateTask');

        $tasksController = new TasksController($this->appMock);
        $tasksController->updateTask($this->customer, 42, '', '2020-05-22', false);
    }

    public function testUpdateTaskInvalidDuedate(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('invalid duedate');

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('updateTask');

        $tasksController = new TasksController($this->appMock);
        $tasksController->updateTask($this->customer, 42, 'test', '', false);
    }

    public function testUpdateTaskNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('task not found');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('taskExists')
            ->with($this->customer, 42)
            ->willReturn(false);

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('updateTask');

        $tasksController = new TasksController($this->appMock);
        $tasksController->updateTask($this->customer, 42, 'test', '2020-05-22', false);
    }

    public function testProcessTasksFromQueue(): void
    {
        $task = new Task(42, '', '', false, '');

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('getTasksFromQueue')
            ->willReturn([$task]);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('updateTaskQueue')
            ->with($task->id);

        $this->appMock->getTaskProcessingService()->expects($this->once())
            ->method('processTaskUpdate')
            ->with($task);

        $this->appMock->getRedisService()->expects($this->once())
            ->method('addTaskToStream')
            ->with($this->appMock->getConfig()->redisStreamTasks, $task);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('deleteTaskQueue')
            ->with($task->id);

        $tasksController = new TasksController($this->appMock);
        $tasksController->processTasksFromQueue();
    }

    public function testProcessTasksFromStream(): void
    {
        $task = new Task(42, '', '', false, '');
        $task2 = new Task(43, '', '', false, '');

        $expected = ['some-id' => $task, 'some-id-other' => $task2];

        $group = $this->appMock->getConfig()->redisStreamGroup;
        $stream = $this->appMock->getConfig()->redisStreamTasks;

        $this->appMock->getRedisService()->expects($this->once())
            ->method('getTasksFromStream')
            ->with($stream, $group, 'consumer1', 100)
            ->willReturn($expected);

        $this->appMock->getRedisService()->expects($this->once())
            ->method('getRetriesFromStream')
            ->with($stream, $group, 'consumer1', 100)
            ->willReturn(['some-id' => 2, 'some-id-other' => 11]);

        $this->appMock->getRedisService()->expects($this->once())
            ->method('removeMessagesFromStream')
            ->with($stream, $group, ['some-id', 'some-id-other']);

        $this->appMock->getTasksRepository()->expects($this->once())
            ->method('importTasksToClickHouse')
            ->with(['some-id' => $expected['some-id']]);

        $tasksController = new TasksController($this->appMock);
        $actual = $tasksController->processTasksFromStream('consumer1');

        $this->assertSame(['some-id', 'some-id-other'], $actual);
    }

    public function testProcessTasksFromStreamEmpty(): void
    {
        $group = $this->appMock->getConfig()->redisStreamGroup;
        $stream = $this->appMock->getConfig()->redisStreamTasks;

        $this->appMock->getRedisService()->expects($this->once())
            ->method('getTasksFromStream')
            ->with($stream, $group, 'consumer1', 100)
            ->willReturn([]);

        $this->appMock->getRedisService()->expects($this->never())
            ->method('getRetriesFromStream');

        $this->appMock->getRedisService()->expects($this->never())
            ->method('removeMessagesFromStream');

        $this->appMock->getTasksRepository()->expects($this->never())
            ->method('importTasksToClickHouse');

        $tasksController = new TasksController($this->appMock);
        $actual = $tasksController->processTasksFromStream('consumer1');

        $this->assertSame([], $actual);
    }
}
