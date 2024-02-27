<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Infrastructure;

use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class RedisTest extends TestCase
{
    public function testRedisConnection(): void
    {
        $app = new App([], [], [], '');

        /** @var array{used_memory: int, maxmemory: int}|false $info */
        $info = $app->getRedis()->info('memory');

        $this->assertNotEmpty($info);
        $this->assertLessThan(0.5, $info['used_memory'] / $info['maxmemory']);
    }
}
