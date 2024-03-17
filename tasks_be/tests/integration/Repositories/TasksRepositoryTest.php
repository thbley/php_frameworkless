<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Repositories;

use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;
use TaskService\Models\Customer;
use TaskService\Models\Task;
use TaskService\Repositories\TasksRepository;

final class TasksRepositoryTest extends TestCase
{
    private App $app;

    private Customer $customer;

    private Customer $customer2;

    private Task $task;

    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');

        $this->app->getDatabase()->beginTransaction();

        $this->customer = new Customer(41, '', '');
        $this->customer2 = new Customer(42, '', '');

        $this->task = new Task(0, 'test', '2020-05-22', false, 'foo@invalid.local');
    }

    protected function tearDown(): void
    {
        $this->app->getDatabase()->rollBack();
    }

    public function testLockCron(): void
    {
        $statement = $this->app->getDatabase()->query('SELECT CONNECTION_ID() as id');
        $expected = $statement->fetch(PDO::FETCH_ASSOC);

        $tasksRepository = new TasksRepository($this->app);
        $this->assertTrue($tasksRepository->lockCron('foobar'));

        $statement = $this->app->getDatabase()->query("SELECT IS_USED_LOCK('cron_foobar') as id");
        $actual = $statement->fetch(PDO::FETCH_ASSOC);

        $this->assertSame($expected, $actual);
    }

    public function testGetTask(): void
    {
        $task = $this->app->getTasksRepository()->getTask(0, 'test', '2020-05-22', false, 'foo@invalid.local');

        $expected = new Task(0, 'test', '2020-05-22', false, 'foo@invalid.local');
        $this->assertEquals($expected, $task);
    }

    public function testCreateTask(): void
    {
        $expected = $this->app->getTasksRepository()->createTask($this->customer, $this->task);
        $this->assertGreaterThan(0, $expected);

        $query = '
            SELECT title, duedate, completed, last_updated_by FROM task WHERE id = ? AND customer_id = ?
        ';
        $statement = $this->app->getDatabase()->prepare($query);
        $statement->execute([$expected, $this->customer->id]);

        $expected = [
            'title' => 'test', 'duedate' => '2020-05-22', 'completed' => 0, 'last_updated_by' => 'foo@invalid.local',
        ];

        $actual = $statement->fetch(PDO::FETCH_ASSOC);

        $this->assertSame($expected, $actual);
    }

    public function testTaskExists(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $actual = $tasksRepository->createTask($this->customer, $this->task);

        $this->assertTrue($tasksRepository->taskExists($this->customer, $actual->id));
        $this->assertFalse($tasksRepository->taskExists($this->customer2, $actual->id));
        $this->assertFalse($tasksRepository->taskExists($this->customer, 42));
    }

    public function testGetCurrentTasks(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $task = new Task(0, 'test', '2020-05-22', false, 'foo@invalid.local');
        $task2 = new Task(0, 'test2', '2020-05-23', false, 'foo@invalid.local');

        $tasksRepository->createTask($this->customer2, $task);

        $taskCreated = $tasksRepository->createTask($this->customer, $task);

        $taskCreated2 = $tasksRepository->createTask($this->customer, $task2);

        $actual = $tasksRepository->getCurrentTasks($this->customer, 1);

        $this->assertCount(2, $actual);
        $this->assertEquals([$taskCreated, $taskCreated2], $actual);
    }

    public function testGetCompletedTasks(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $task = $tasksRepository->createTask($this->customer, $this->task);

        $task2 = new Task($task->id, 'test', '2020-05-22', true, 'foo@invalid.local');
        $tasksRepository->updateTask($task2);

        $this->assertEquals([$task2], $tasksRepository->getCompletedTasks($this->customer, 1));
    }

    public function testDeleteTask(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $actual = $tasksRepository->createTask($this->customer, $this->task);

        $this->assertTrue($tasksRepository->taskExists($this->customer, $actual->id));

        $tasksRepository->deleteTask($actual->id);

        $this->assertFalse($tasksRepository->taskExists($this->customer, $actual->id));
    }

    public function testGetTasks(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $task = $tasksRepository->createTask($this->customer, $this->task);

        $this->assertEquals([$task], $tasksRepository->getTasks([$task->id]));

        $this->assertSame([], $tasksRepository->getTasks([]));
        $this->assertSame([], $tasksRepository->getTasks([0]));
    }

    public function testGetTasksFromQueue(): void
    {
        $tasksRepository = $this->app->getTasksRepository();

        $task = $tasksRepository->createTask($this->customer, $this->task);

        $this->assertTrue(in_array($task, $tasksRepository->getTasksFromQueue(), false));

        $tasksRepository->deleteTaskQueue($task->id);

        $this->assertFalse(in_array($task, $tasksRepository->getTasksFromQueue(), false));
    }

    public function testCreateTaskQueue(): void
    {
        $database = $this->app->getDatabase();
        $tasksRepository = $this->app->getTasksRepository();

        $task = $tasksRepository->createTask($this->customer, $this->task);

        $query = 'SELECT num_tries FROM task_queue WHERE task_id = ?';

        $tasksRepository->updateTaskQueue($task->id);

        $statement = $database->prepare($query);
        $statement->execute([$task->id]);
        $this->assertSame(1, $statement->fetchColumn(0));

        $tasksRepository->updateTaskQueue($task->id);
        $statement = $database->prepare($query);
        $statement->execute([$task->id]);
        $this->assertSame(2, $statement->fetchColumn(0));
    }

    public function testUpdateTaskQueue(): void
    {
        $database = $this->app->getDatabase();
        $tasksRepository = $this->app->getTasksRepository();

        $task = new Task(42, 'test', '2020-05-22', false, 'foo@invalid.local');
        $tasksRepository->updateTask($task);

        $query = 'SELECT num_tries FROM task_queue WHERE task_id = ?';

        $tasksRepository->updateTaskQueue(42);

        $statement = $database->prepare($query);
        $statement->execute([42]);
        $this->assertSame(1, $statement->fetchColumn(0));

        $tasksRepository->updateTaskQueue(42);
        $statement = $database->prepare($query);
        $statement->execute([42]);
        $this->assertSame(2, $statement->fetchColumn(0));
    }

    public function testDeleteTaskQueue(): void
    {
        $database = $this->app->getDatabase();
        $tasksRepository = $this->app->getTasksRepository();

        $actual = $tasksRepository->createTask($this->customer, $this->task);
        $tasksRepository->deleteTaskQueue($actual->id);

        $query = 'SELECT * FROM task_queue WHERE task_id = ?';
        $statement = $database->prepare($query);
        $statement->execute([$actual->id]);
        $this->assertFalse($statement->fetch(PDO::FETCH_ASSOC));
    }

    public function testImportTasksToClickHouse(): void
    {
        $task = new Task(42, 'test', '2020-05-22', false, 'foo@invalid.local');

        $clickHouse = $this->app->getClickHouse();
        $tasksRepository = $this->app->getTasksRepository();

        $query = 'DELETE FROM stream_tasks WHERE last_updated_by = ?';
        $clickHouse->prepare($query)->execute([$task->last_updated_by]);

        $tasksRepository->importTasksToClickHouse([$task]);

        $query = 'SELECT count(*) FROM stream_tasks WHERE last_updated_by = ?';
        $statement = $clickHouse->prepare($query);
        $statement->execute([$task->last_updated_by]);
        $this->assertSame('1', $statement->fetchColumn(0));

        $query = 'DELETE FROM stream_tasks WHERE last_updated_by = ?';
        $clickHouse->prepare($query)->execute([$task->last_updated_by]);
    }

    public function testImportTasksToClickHouseEmpty(): void
    {
        $clickHouse = $this->app->getClickHouse();
        $tasksRepository = $this->app->getTasksRepository();

        $query = 'DELETE FROM stream_tasks WHERE last_updated_by = ?';
        $clickHouse->prepare($query)->execute([$this->task->last_updated_by]);

        $tasksRepository->importTasksToClickHouse([]);

        $query = 'SELECT count(*) FROM stream_tasks WHERE last_updated_by = ?';
        $statement = $clickHouse->prepare($query);
        $statement->execute([$this->task->last_updated_by]);
        $this->assertSame('0', $statement->fetchColumn(0));
    }
}
