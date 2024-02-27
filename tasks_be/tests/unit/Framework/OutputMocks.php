<?php

declare(strict_types=1);

namespace TaskService\Framework;

function header(string $header): void
{
    OutputMocks::$header[] = $header;
}

abstract class OutputMocks
{
    /** @var string[] */
    public static array $header;
}
