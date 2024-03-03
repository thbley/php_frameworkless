<?php

namespace TaskService\Controllers;

use TaskService\Exceptions\HttpException;
use TaskService\Framework\App;
use TaskService\Models\Customer;
use TaskService\Models\Task;

class TasksController
{
    /**
     * passing App allows late initialization (e.g. open db connection only when needed), avoids circular references
     */
    public function __construct(private App $app) {}

    public function createTask(Customer $customer, string $title, string $duedate): Task
    {
        if ($title === '') {
            throw new HttpException('missing title', 400);
        }

        if (date_create_from_format('Y-m-d', $duedate) === false) {
            throw new HttpException('invalid duedate', 400);
        }

        $tasksRepository = $this->app->getTasksRepository();

        $task = $tasksRepository->getTask(0, $title, $duedate, false, $customer->email);

        $task->id = $tasksRepository->createTask($customer, $task);

        return $task;
    }

    public function updateTask(Customer $customer, int $taskId, string $title, string $duedate, bool $completed): Task
    {
        if ($title === '') {
            throw new HttpException('missing title', 400);
        }

        if (date_create_from_format('Y-m-d', $duedate) === false) {
            throw new HttpException('invalid duedate', 400);
        }

        $tasksRepository = $this->app->getTasksRepository();

        if (!$tasksRepository->taskExists($customer, $taskId)) {
            throw new HttpException('task not found', 404);
        }

        $task = $tasksRepository->getTask($taskId, $title, $duedate, $completed, $customer->email);

        $tasksRepository->updateTask($task);

        return $task;
    }

    public function deleteTask(Customer $customer, int $taskId): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        if (!$tasksRepository->taskExists($customer, $taskId)) {
            throw new HttpException('task not found', 404);
        }

        $tasksRepository->deleteTask($taskId);
    }

    /**
     * @return Task[]
     */
    public function getCurrentTasks(Customer $customer, int $page): array
    {
        $tasksRepository = $this->app->getTasksRepository();

        return $tasksRepository->getCurrentTasks($customer, $page);
    }

    /**
     * @return Task[]
     */
    public function getCompletedTasks(Customer $customer, int $page): array
    {
        $tasksRepository = $this->app->getTasksRepository();

        return $tasksRepository->getCompletedTasks($customer, $page);
    }

    public function getTask(Customer $customer, int $taskId): Task
    {
        $tasksRepository = $this->app->getTasksRepository();

        if (!$tasksRepository->taskExists($customer, $taskId)) {
            throw new HttpException('task not found', 404);
        }

        $tasks = $tasksRepository->getTasks([$taskId]);
        if (!array_key_exists(0, $tasks)) {
            throw new HttpException('task not found', 404);
        }

        return $tasks[0];
    }

    /**
     * @return int[]
     */
    public function processTasksFromQueue(): array
    {
        $tasksRepository = $this->app->getTasksRepository();
        $taskProcessingService = $this->app->getTaskProcessingService();
        $redisService = $this->app->getRedisService();

        $stream = $this->app->getConfig()->redisStreamTasks;

        $processed = [];
        foreach ($tasksRepository->getTasksFromQueue() as $task) {
            $tasksRepository->updateTaskQueue($task->id);

            $taskProcessingService->processTaskUpdate($task);

            $redisService->addTaskToStream($stream, $task);

            $tasksRepository->deleteTaskQueue($task->id);

            $processed[] = $task->id;
        }

        return $processed;
    }

    /**
     * @return string[]
     */
    public function processTasksFromStream(string $consumer): array
    {
        $redisService = $this->app->getRedisService();

        $stream = $this->app->getConfig()->redisStreamTasks;
        $group = $this->app->getConfig()->redisStreamGroup;

        // max 200 (100 new + 100 old)
        $tasks = $redisService->getTasksFromStream($stream, $group, $consumer, 100);
        if ($tasks === []) {
            return [];
        }

        $messageIds = array_keys($tasks);

        $retries = $redisService->getRetriesFromStream($stream, $group, $consumer, 100);

        foreach ($retries as $messageId => $count) {
            if ($count > 10) {
                error_log('retried too often: ' . json_encode([$messageId => $count], 0));

                unset($tasks[$messageId]);
            }
        }

        $this->app->getTasksRepository()->importTasksToClickHouse($tasks);

        $redisService->removeMessagesFromStream($stream, $group, $messageIds);

        return $messageIds;
    }
}
