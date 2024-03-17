<?php

namespace TaskService\Models;

class Task
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $duedate,
        public readonly bool $completed,
        public readonly string $last_updated_by
    ) {}
}
