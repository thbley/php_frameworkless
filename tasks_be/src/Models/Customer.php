<?php

namespace TaskService\Models;

class Customer
{
    public function __construct(
        public readonly int $id,
        public readonly string $email
    ) {}
}
