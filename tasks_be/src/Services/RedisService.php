<?php

namespace TaskService\Services;

use Exception;
use TaskService\Framework\App;
use TaskService\Models\Task;

class RedisService
{
    public function __construct(private App $app) {}

    /**
     * @see https://github.com/phpredis/phpredis#xadd
     * @see https://redis.io/commands/XADD
     */
    public function addTaskToStream(string $stream, Task $task): string
    {
        $redis = $this->app->getRedis();

        // * = auto generated id
        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         *
         * @var string|false $result
         */
        $result = $redis->xAdd($stream, '*', ['data' => json_encode($task, JSON_FORCE_OBJECT)]);
        if ($result === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }

        return $result;
    }

    /**
     * @param string[] $messages
     *
     * @see https://github.com/phpredis/phpredis#xAck
     * @see https://redis.io/commands/XACK
     * @see https://github.com/phpredis/phpredis#xDel
     * @see https://redis.io/commands/XDEL
     */
    public function removeMessagesFromStream(string $stream, string $group, array $messages): void
    {
        if ($messages === []) {
            return;
        }

        $redis = $this->app->getRedis();

        $result = $redis->xAck($stream, $group, $messages);
        if ($result === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }

        $result = $redis->xDel($stream, $messages);
        if ($result === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }
    }

    /**
     * @see https://github.com/phpredis/phpredis#xGroup
     * @see https://redis.io/commands/XGROUP
     * @see https://github.com/phpredis/phpredis#xReadGroup
     * @see https://redis.io/commands/XREADGROUP
     *
     * @return array<string, Task>
     */
    public function getTasksFromStream(string $stream, string $group, string $consumer, int $count): array
    {
        $redis = $this->app->getRedis();

        $redis->xGroup('CREATE', $stream, $group, '0', true);

        // 0 = pending messages
        /** @var array{data:string}[][]|false */
        $pendingMessages = $redis->xReadGroup($group, $consumer, [$stream => 0], $count);
        if ($pendingMessages === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }

        // > = new messages
        /** @var array{data:string}[][]|false */
        $newMessages = $redis->xReadGroup($group, $consumer, [$stream => '>'], $count);
        if ($newMessages === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }

        $result = [];
        foreach (($newMessages[$stream] ?? []) + ($pendingMessages[$stream] ?? []) as $key => $value) {
            $data = (array) json_decode($value['data'], true, 10, 0);

            if (!isset($data['id'])) {
                throw new Exception('invalid data: ' . json_encode($data, 0));
            }

            $result[(string) $key] = $this->app->getTasksRepository()->getTask(
                (int) $data['id'], (string) ($data['title'] ?? ''), (string) ($data['duedate'] ?? ''),
                (bool) ($data['completed'] ?? false), (string) ($data['lastUpdatedBy'] ?? ''),
            );
        }

        return $result;
    }

    /**
     * @see https://github.com/phpredis/phpredis#xPending
     * @see https://redis.io/commands/XPENDING
     *
     * @return array<string, int>
     */
    public function getRetriesFromStream(string $stream, string $group, string $consumer, int $count): array
    {
        $redis = $this->app->getRedis();

        /** @var array{string, string, int, int}[]|false $pendings */
        $pendings = $redis->xPending($stream, $group, '-', '+', $count, $consumer);
        if ($pendings === false) {
            throw new Exception('redis error: ' . ($redis->getLastError() ?? ''));
        }

        // message id => delivery count
        $retries = [];
        foreach ($pendings as $pending) {
            $retries[$pending[0]] = $pending[3];
        }

        return $retries;
    }
}
