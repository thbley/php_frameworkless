<?php

namespace TaskService\Models;

readonly class Customer
{
    public function __construct(
        public int $id,
        public string $email,
        public string $password
    ) {}
}
