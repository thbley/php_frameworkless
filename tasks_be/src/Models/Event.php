<?php

namespace TaskService\Models;

/**
 * @psalm-suppress PossiblyUnusedProperty
 */
class Event
{
    public string $message;

    public int $code;

    public int $customer;

    public string $method;

    public string $uri;

    public string $status;

    public string $datetime;
}
