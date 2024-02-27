<?php

namespace TaskService\Services;

use TaskService\Framework\App;

class RateLimitService
{
    public function __construct(private App $app) {}

    /**
     * @see https://github.com/phpredis/phpredis#incr-incrby
     * @see https://github.com/phpredis/phpredis#expire-settimeout-pexpire
     * @see https://redis.io/commands/INCR
     * @see https://redis.io/commands/EXPIRE
     */
    public function logFailedLogin(string $email): void
    {
        $key = 'l_' . mb_strtolower($email);

        $redis = $this->app->getRedis();
        $redis->set($key, '0', ['EX' => 3600, 'NX']);
        $redis->incr($key);
    }

    /**
     * @see https://github.com/phpredis/phpredis#get
     * @see https://redis.io/commands/GET
     */
    public function isLoginBlocked(string $email): bool
    {
        $key = 'l_' . mb_strtolower($email);

        return $this->app->getRedis()->get($key) >= 10;
    }
}
