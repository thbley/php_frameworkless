<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use TaskService\Models\Task;
use TaskService\Serializer\TasksSerializer;

final class TasksSerializerTest extends TestCase
{
    public function testSerializeTasks(): void
    {
        $task = new Task(1234, 'test task', '2020-05-22', false, '');

        $tasksSerializer = new TasksSerializer();

        $actual = $tasksSerializer->serializeTasks([$task]);

        $expected = [[
            'id' => 1234,
            'title' => 'test task',
            'duedate' => '2020-05-22',
            'completed' => false,
        ]];

        $this->assertSame($expected, $actual);
    }
}
