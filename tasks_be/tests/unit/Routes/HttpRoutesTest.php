<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Routes;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Exceptions\HttpException;
use TaskService\Models\Customer;
use TaskService\Models\Event;
use TaskService\Models\Task;
use TaskService\Routes\HttpRoutes;
use TaskService\Tests\Unit\Framework\AppMock;

final class HttpRoutesTest extends TestCase
{
    private Customer $customer;

    protected function setUp(): void
    {
        $this->customer = new Customer(42, '');
    }

    public function testGetCurrentTasks(): void
    {
        $task = new Task(0, '', '', false, '');

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks'), ['page' => '42']);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('getCurrentTasks')
            ->with($this->customer, 42)
            ->willReturn([$task]);

        $appMock->getTasksSerializer()->expects($this->once())
            ->method('serializeTasks')
            ->with([$task])
            ->willReturn(['some-data']);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['some-data'], 200, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testGetCompletedTasks(): void
    {
        $task = new Task(0, '', '', false, '');

        $input = ['completed' => '1', 'page' => '42'];
        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks'), $input);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('getCompletedTasks')
            ->with($this->customer, 42)
            ->willReturn([$task]);

        $appMock->getTasksSerializer()->expects($this->once())
            ->method('serializeTasks')
            ->with([$task])
            ->willReturn(['some-data']);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['some-data'], 200, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testDeleteTask(): void
    {
        $appMock = new AppMock($this->createMock(...), $this->getHeaders('DELETE', '/v1/tasks/123'), []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('deleteTask')
            ->with($this->customer, '123');

        $appMock->getOutput()->expects($this->once())
            ->method('noContent');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testGetTask(): void
    {
        $task = new Task(0, '', '', false, '');

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks/123'), []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('getTask')
            ->with($this->customer, '123')
            ->willReturn($task);

        $appMock->getTasksSerializer()->expects($this->once())
            ->method('serializeTask')
            ->with($task)
            ->willReturn(['some-data']);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['some-data'], 200, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testCreateTask(): void
    {
        $task = new Task(42, '', '', false, '');

        $params = ['title' => 'Test', 'duedate' => '2020-05-22'];

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('POST', '/v1/tasks'), $params);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('createTask')
            ->with($this->customer, 'Test', '2020-05-22')
            ->willReturn($task);

        $appMock->getTasksSerializer()->expects($this->once())
            ->method('serializeTask')
            ->with($task)
            ->willReturn(['some-data']);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['some-data'], 201, '/v1/tasks/42');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testUpdateTask(): void
    {
        $task = new Task(123, 'Test', '2020-05-22', true, '');

        $params = ['title' => $task->title, 'duedate' => $task->duedate, 'completed' => (string) $task->completed];

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('PUT', '/v1/tasks/123'), $params);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('updateTask')
            ->with($this->customer, $task->id, $task->title, $task->duedate, $task->completed)
            ->willReturn($task);

        $appMock->getTasksSerializer()->expects($this->once())
            ->method('serializeTask')
            ->with($task)
            ->willReturn(['some-data']);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['some-data'], 200, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testTokenInvalidOrMissing(): void
    {
        $event = new Event('unauthorized', 401, 0, 'GET', '/v1/tasks', '');

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks'), []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn(null);

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 401);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'unauthorized'], 401, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testGetCurrentTasksHttpException(): void
    {
        $event = new Event('missing something', 400, $this->customer->id, 'GET', '/v1/tasks', '');

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks'), []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('getCurrentTasks')
            ->willThrowException(new HttpException('missing something', 400));

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 400);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'missing something'], 400, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testGetCurrentTasksException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('some error');

        $event = new Event('some error', 0, $this->customer->id, 'GET', '/v1/tasks', '');

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('GET', '/v1/tasks'), []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getTasksController()->expects($this->once())
            ->method('getCurrentTasks')
            ->willThrowException(new Exception('some error'));

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 0);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'internal server error'], 500, '');

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    public function testNotFound(): void
    {
        $event = new Event('not found', 404, $this->customer->id, '', '', '');

        $appMock = new AppMock($this->createMock(...), [], []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 404);

        $httpRoutes = new HttpRoutes($appMock);
        $httpRoutes->run();
    }

    /**
     * @return string[]
     */
    private function getHeaders(string $method, string $url): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'secret',
            'REQUEST_METHOD' => $method,
            'DOCUMENT_URI' => $url,
        ];
    }
}
