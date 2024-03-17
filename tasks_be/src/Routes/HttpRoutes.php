<?php

namespace TaskService\Routes;

use TaskService\Exceptions\HttpException;
use TaskService\Framework\App;
use TaskService\Models\Customer;
use TaskService\Models\Event;
use Throwable;

class HttpRoutes
{
    private Customer $customer;

    public function __construct(private App $app) {}

    public function run(): void
    {
        $app = $this->app;

        try {
            $customer = $app->getAuthentication()->getCustomer(
                $app->getHeader('HTTP_AUTHORIZATION'),
                $app->getConfig()->publicKey
            );
            if ($customer === null) {
                throw new HttpException('unauthorized', 401);
            }

            $this->customer = $customer;

            $router = $app->getRouter();
            $router->get('/v1/tasks', $this->getTasks(...));
            $router->get('/v1/tasks/(\d+)', $this->getTask(...));
            $router->post('/v1/tasks', $this->createTask(...));
            $router->put('/v1/tasks/(\d+)', $this->updateTask(...));
            $router->delete('/v1/tasks/(\d+)', $this->deleteTask(...));
            $router->any('.*', $this->notFound(...));

            $router->match($app->getHeader('REQUEST_METHOD'), $app->getHeader('DOCUMENT_URI'));
        } catch (Throwable $throwable) {
            $event = new Event(
                $throwable->getMessage(),
                (int) $throwable->getCode(),
                $this->customer->id ?? 0,
                $app->getHeader('REQUEST_METHOD'),
                $app->getHeader('DOCUMENT_URI'),
                ''
            );
            $app->getLogger()->log($event, $event->code);

            if (!$throwable instanceof HttpException) {
                $app->getOutput()->json(['error' => 'internal server error'], 500, '');

                throw $throwable;
            }

            $app->getOutput()->json(['error' => $throwable->getMessage()], $throwable->getCode(), '');
        }
    }

    private function getTasks(): void
    {
        $page = max(1, (int) $this->app->getParam('page'));
        $tasks = (bool) $this->app->getParam('completed') ?
            $this->app->getTasksController()->getCompletedTasks($this->customer, $page) :
            $this->app->getTasksController()->getCurrentTasks($this->customer, $page);

        $this->app->getOutput()->json($this->app->getTasksSerializer()->serializeTasks($tasks), 200, '');
    }

    private function getTask(int $taskId): void
    {
        $task = $this->app->getTasksController()->getTask($this->customer, $taskId);

        $this->app->getOutput()->json($this->app->getTasksSerializer()->serializeTask($task), 200, '');
    }

    private function createTask(): void
    {
        $task = $this->app->getTasksController()->createTask(
            $this->customer, $this->app->getParam('title'), $this->app->getParam('duedate')
        );

        $location = sprintf('/v1/tasks/%s', $task->id);

        $this->app->getOutput()->json($this->app->getTasksSerializer()->serializeTask($task), 201, $location);
    }

    private function updateTask(int $taskId): void
    {
        $task = $this->app->getTasksController()->updateTask(
            $this->customer, $taskId, $this->app->getParam('title'), $this->app->getParam('duedate'),
            (bool) $this->app->getParam('completed')
        );

        $this->app->getOutput()->json($this->app->getTasksSerializer()->serializeTask($task), 200, '');
    }

    private function deleteTask(int $taskId): void
    {
        $this->app->getTasksController()->deleteTask($this->customer, $taskId);

        $this->app->getOutput()->noContent();
    }

    private function notFound(): never
    {
        throw new HttpException('not found', 404);
    }
}
