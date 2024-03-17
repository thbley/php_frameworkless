<?php

namespace TaskService\Models;

readonly class Email
{
    public function __construct(
        public string $subject,
        public string $from,
        public string $recipients,
        public string $content
    ) {}
}
