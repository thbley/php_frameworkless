<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Routes;

final class CallbackMocks
{
    public bool $never = false;

    public bool $called = false;

    /** @var mixed[] */
    public array $with = [];

    public function never(): void
    {
        $this->never = true;
    }

    public function called(): void
    {
        $this->called = true;
    }

    public function calledWith(int $number, string $string): void
    {
        $this->called = true;
        $this->with = [$number, $string];
    }

    public function calledWithString(string $string): void
    {
        $this->called = true;
        $this->with = [$string];
    }
}
