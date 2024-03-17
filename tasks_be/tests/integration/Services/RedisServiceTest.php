<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Services;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;
use TaskService\Models\Task;
use TaskService\Services\RedisService;

final class RedisServiceTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');
    }

    public function testAddTaskToStream(): void
    {
        $stream = 'test_' . microtime(true);

        $task = new Task(42, 'test', '2020-01-02', false, '');

        $redis = $this->app->getRedis();

        $redisService = new RedisService($this->app);
        $messageId = $redisService->addTaskToStream($stream, $task);

        $actual = $redis->xRevRange($stream, '+', '-', 1);

        $this->assertSame([$messageId => ['data' => json_encode(['id' => 42], 0)]], $actual);

        $redis->del($stream);
    }

    public function testRemoveMessagesFromStream(): void
    {
        $stream = 'test_' . microtime(true);

        $redis = $this->app->getRedis();

        $messageId = $redis->xAdd($stream, '*', ['foo' => 'bar']);
        $this->assertIsString($messageId);
        $actual = $redis->xRevRange($stream, '+', '-', 1);

        $this->assertNotEmpty($messageId);
        $this->assertSame([$messageId => ['foo' => 'bar']], $actual);

        $redisService = new RedisService($this->app);
        $redisService->removeMessagesFromStream($stream, 'mygroup', []);
        $redisService->removeMessagesFromStream($stream, 'mygroup', [$messageId]);

        $actual = $redis->xRevRange($stream, '+', '-', 1);
        $this->assertSame([], $actual);

        $redis->del($stream);
    }

    public function testGetTasksFromStream(): void
    {
        $stream = 'test_' . microtime(true);

        $redis = $this->app->getRedis();

        /** @var string $messageId */
        $messageId = $redis->xAdd($stream, '*', ['data' => json_encode(['id' => 42], 0)]);

        /** @var string $messageId2 */
        $messageId2 = $redis->xAdd($stream, '*', ['data' => json_encode(['id' => 43], 0)]);

        $redisService = new RedisService($this->app);

        $actual = $redisService->getTasksFromStream($stream, 'mygroup', 'consumer1', 10);
        $this->assertArrayHasKey($messageId, $actual);
        $this->assertArrayHasKey($messageId2, $actual);
        $this->assertSame(42, $actual[$messageId]->id);
        $this->assertSame(43, $actual[$messageId2]->id);

        $redisService->removeMessagesFromStream($stream, 'mygroup', [$messageId]);

        $actual = $redisService->getTasksFromStream($stream, 'mygroup', 'consumer1', 10);
        $this->assertArrayNotHasKey($messageId, $actual);
        $this->assertArrayHasKey($messageId2, $actual);

        $redis->del($stream);
    }

    public function testGetTasksFromStreamInvalidData(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid data');

        $stream = 'test_' . microtime(true);

        $redis = $this->app->getRedis();

        try {
            $redis->xAdd($stream, '*', ['data' => 'foo bar']);

            $redisService = new RedisService($this->app);
            $redisService->getTasksFromStream($stream, 'mygroup', 'consumer1', 10);
        } finally {
            $redis->del($stream);
        }
    }

    public function testGetRetriesFromStream(): void
    {
        $stream = 'test_' . microtime(true);

        $redis = $this->app->getRedis();

        $messageId = $redis->xAdd($stream, '*', ['data' => json_encode(['id' => 21], 0)]);
        $this->assertIsString($messageId);
        $messageId2 = $redis->xAdd($stream, '*', ['data' => json_encode(['id' => 22], 0)]);
        $this->assertIsString($messageId2);
        $messageId3 = $redis->xAdd($stream, '*', ['data' => json_encode(['id' => 23], 0)]);
        $this->assertIsString($messageId3);

        $redisService = new RedisService($this->app);

        $redisService->getTasksFromStream($stream, 'mygroup', 'consumer1', 2);

        $actual = $redisService->getRetriesFromStream($stream, 'mygroup', 'consumer1', 3);

        $this->assertNotEmpty($messageId);
        $this->assertNotEmpty($messageId2);
        $this->assertSame([$messageId => 1, $messageId2 => 1], $actual);

        $redisService->removeMessagesFromStream($stream, 'mygroup', [$messageId]);
        $redisService->getTasksFromStream($stream, 'mygroup', 'consumer1', 2);

        $actual = $redisService->getRetriesFromStream($stream, 'mygroup', 'consumer1', 3);

        $this->assertNotEmpty($messageId3);
        $this->assertSame([$messageId2 => 2, $messageId3 => 1], $actual);

        $redis->del($stream);
    }

    public function testGetRetriesFromStreamException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error');

        $redisService = new RedisService($this->app);
        $redisService->getRetriesFromStream('test_' . microtime(true), 'mygroup', 'consumer1', 1);
    }
}
