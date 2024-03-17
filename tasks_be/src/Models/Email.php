<?php

namespace TaskService\Models;

class Email
{
    public function __construct(
        public readonly string $subject,
        public readonly string $from,
        public readonly string $recipients,
        public readonly string $content
    ) {}
}
