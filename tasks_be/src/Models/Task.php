<?php

namespace TaskService\Models;

/** @SuppressWarnings(PHPMD.CamelCasePropertyName) */
readonly class Task
{
    public function __construct(
        public int $id,
        public string $title,
        public string $duedate,
        public bool $completed,
        public string $last_updated_by
    ) {}
}
