<?php

namespace TaskService\Models;

/**
 * @psalm-suppress PossiblyUnusedProperty
 */
readonly class Event
{
    public function __construct(
        public string $message,
        public int $code,
        public int $customer,
        public string $method,
        public string $uri,
        public string $datetime
    ) {}
}
