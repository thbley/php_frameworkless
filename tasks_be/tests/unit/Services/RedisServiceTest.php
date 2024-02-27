<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Services;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Models\Task;
use TaskService\Services\RedisService;
use TaskService\Tests\Unit\Framework\AppMock;

final class RedisServiceTest extends TestCase
{
    private AppMock $appMock;

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);
    }

    public function testAddTaskToStreamException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: add');

        $this->appMock->getRedis()->expects($this->once())
            ->method('xAdd')
            ->willReturn(false);

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('add');

        $redisService = new RedisService($this->appMock);
        $redisService->addTaskToStream('test', new Task());
    }

    public function testRemoveMessagesFromStreamXackException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: remove');

        $this->appMock->getRedis()->expects($this->once())
            ->method('xAck')
            ->willReturn(false);

        $this->appMock->getRedis()->expects($this->never())
            ->method('xDel');

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('remove');

        $redisService = new RedisService($this->appMock);
        $redisService->removeMessagesFromStream('test', 'group', ['1234']);
    }

    public function testRemoveMessagesFromStreamDelException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: remove');

        $this->appMock->getRedis()->expects($this->once())
            ->method('xAck')
            ->willReturn(1);

        $this->appMock->getRedis()->expects($this->once())
            ->method('xDel')
            ->willReturn(false);

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('remove');

        $redisService = new RedisService($this->appMock);
        $redisService->removeMessagesFromStream('test', 'group', ['1234']);
    }

    public function testGetPendingMessagesFromStreamException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: get_pending');

        $this->appMock->getRedis()->expects($this->once())
            ->method('xReadGroup')
            ->willReturn(false);

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('get_pending');

        $redisService = new RedisService($this->appMock);
        $redisService->getTasksFromStream('test', 'mygroup', 'consumer1', 10);
    }

    public function testGetNewMessagesFromStreamException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: get_new');

        $this->appMock->getRedis()->expects($this->exactly(2))
            ->method('xReadGroup')
            ->willReturn([], false);

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('get_new');

        $redisService = new RedisService($this->appMock);
        $redisService->getTasksFromStream('test', 'mygroup', 'consumer1', 10);
    }

    public function testGetRetriesFromStreamException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('redis error: pending');

        $this->appMock->getRedis()->expects($this->once())
            ->method('xPending')
            ->willReturn(false);

        $this->appMock->getRedis()->expects($this->once())
            ->method('getLastError')
            ->willReturn('pending');

        $redisService = new RedisService($this->appMock);
        $redisService->getRetriesFromStream('test', 'group', 'consumer', 10);
    }
}
