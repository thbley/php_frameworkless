<?php

namespace TaskService\Models;

class Event
{
    public function __construct(
        public readonly string $message,
        public readonly int $code,
        public readonly int $customer,
        public readonly string $method,
        public readonly string $uri
    ) {}
}
