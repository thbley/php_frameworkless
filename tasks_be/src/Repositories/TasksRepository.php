<?php

namespace TaskService\Repositories;

use PDO;
use TaskService\Framework\App;
use TaskService\Models\Customer;
use TaskService\Models\Task;

class TasksRepository
{
    public function __construct(private App $app) {}

    public function lockCron(string $identifier): bool
    {
        $statement = $this->app->getDatabase()->prepare('SELECT GET_LOCK(?, 1)');
        $statement->execute(['cron_' . $identifier]);

        return $statement->fetchColumn(0) === 1;
    }

    public function getTask(int $id, string $title, string $duedate, bool $completed, string $lastUpdatedBy): Task
    {
        $task = new Task();
        $task->id = $id;
        $task->title = $title;
        $task->duedate = $duedate;
        $task->completed = $completed;
        $task->last_updated_by = $lastUpdatedBy;

        return $task;
    }

    public function taskExists(Customer $customer, int $taskId): bool
    {
        $query = '
            SELECT id FROM task WHERE id = ? AND customer_id = ?
        ';
        $statement = $this->app->getDatabase()->prepare($query);
        $statement->execute([$taskId, $customer->id]);

        return $statement->fetchColumn(0) !== false;
    }

    public function createTask(Customer $customer, Task $task): int
    {
        $database = $this->app->getDatabase();

        $inTransaction = $database->inTransaction();
        $inTransaction || $database->beginTransaction();

        $query = '
            INSERT INTO task SET customer_id = ?, title = ?, duedate = ?, completed = 0, last_updated_by = ?
        ';
        $database = $this->app->getDatabase();
        $statement = $database->prepare($query);
        $statement->execute([$customer->id, $task->title, $task->duedate, $task->last_updated_by]);

        $id = (int) $database->lastInsertId();

        $query = 'INSERT INTO task_queue SET task_id = ?, num_tries = 0, last_try = "1000-01-01 00:00:00"';
        $database->prepare($query)->execute([$id]);

        $inTransaction || $database->commit();

        return $id;
    }

    public function updateTask(Task $task): void
    {
        $database = $this->app->getDatabase();

        $inTransaction = $database->inTransaction();
        $inTransaction || $database->beginTransaction();

        $query = '
            UPDATE task SET title = ?, duedate = ?, completed = ?, last_updated_by = ? WHERE id = ?
        ';
        $statement = $database->prepare($query);
        $statement->execute([$task->title, $task->duedate, (int) $task->completed, $task->last_updated_by, $task->id]);

        $query = 'REPLACE INTO task_queue SET task_id = ?, num_tries = 0, last_try = "1000-01-01 00:00:00"';
        $database->prepare($query)->execute([$task->id]);

        $inTransaction || $database->commit();
    }

    public function deleteTask(int $taskId): void
    {
        $query = '
            DELETE FROM task WHERE id = ?
        ';
        $this->app->getDatabase()->prepare($query)->execute([$taskId]);
    }

    /**
     * @param int[] $taskIds
     *
     * @return Task[]
     */
    public function getTasks(array $taskIds): array
    {
        if ($taskIds === []) {
            return [];
        }

        $database = $this->app->getDatabase();

        /** @var '1,2,3' $ids */
        $ids = implode(',', array_map(intval(...), $taskIds));

        $query = sprintf('SELECT id, title, duedate, completed, last_updated_by FROM task WHERE id IN (%s)', $ids);
        $statement = $database->query($query);

        return $statement->fetchAll(PDO::FETCH_CLASS, Task::class);
    }

    /**
     * @return Task[]
     */
    public function getCurrentTasks(Customer $customer): array
    {
        $database = $this->app->getDatabase();

        $query = '
            SELECT id, title, duedate, completed, last_updated_by FROM task
            WHERE customer_id = ? AND completed = 0 AND duedate < ?
            ORDER BY duedate, id
            LIMIT 500
        ';
        $statement = $database->prepare($query);
        $statement->execute([$customer->id, date('Y-m-d', strtotime('+1 week'))]);

        return $statement->fetchAll(PDO::FETCH_CLASS, Task::class);
    }

    /**
     * @return Task[]
     */
    public function getCompletedTasks(Customer $customer): array
    {
        $database = $this->app->getDatabase();

        $query = '
            SELECT id, title, duedate, completed, last_updated_by FROM task
            WHERE customer_id = ? AND completed = 1
            ORDER BY duedate DESC, id
            LIMIT 500
        ';
        $statement = $database->prepare($query);
        $statement->execute([$customer->id]);

        return $statement->fetchAll(PDO::FETCH_CLASS, Task::class);
    }

    /**
     * @return Task[]
     */
    public function getTasksFromQueue(): array
    {
        $database = $this->app->getDatabase();

        $query = 'SELECT task_id FROM task_queue WHERE num_tries < 20 LIMIT 500';

        /** @var int[] $taskIds */
        $taskIds = $database->query($query)->fetchAll(PDO::FETCH_COLUMN);

        return $this->getTasks($taskIds);
    }

    public function updateTaskQueue(int $taskId): void
    {
        $query = 'UPDATE task_queue SET num_tries = num_tries + 1, last_try = now() WHERE task_id = ?';

        $statement = $this->app->getDatabase()->prepare($query);
        $statement->execute([$taskId]);
    }

    public function deleteTaskQueue(int $taskId): void
    {
        $query = 'DELETE FROM task_queue WHERE task_id = ?';

        $statement = $this->app->getDatabase()->prepare($query);
        $statement->execute([$taskId]);
    }

    /**
     * @param Task[] $tasks
     */
    public function importTasksToClickHouse(array $tasks): void
    {
        if ($tasks === []) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $rows = '';
        foreach ($tasks as $task) {
            $rows .= ' ' . json_encode([$date, $task->last_updated_by, $task], 0);
        }

        $query = 'INSERT INTO stream_tasks FORMAT JSONCompactEachRow ' . $rows;

        $this->app->getClickHouse()->exec($query);
    }
}
