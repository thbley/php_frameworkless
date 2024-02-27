<?php

namespace TaskService\Serializer;

use TaskService\Models\Task;

class TasksSerializer
{
    /**
     * @param Task[] $tasks
     *
     * @return array{id: int, title: string, duedate: string, completed: bool}[]
     */
    public function serializeTasks(array $tasks): array
    {
        $result = [];

        foreach ($tasks as $task) {
            $result[] = $this->serializeTask($task);
        }

        return $result;
    }

    /**
     * @return array{id: int, title: string, duedate: string, completed: bool}
     */
    public function serializeTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'duedate' => $task->duedate,
            'completed' => $task->completed,
        ];
    }
}
