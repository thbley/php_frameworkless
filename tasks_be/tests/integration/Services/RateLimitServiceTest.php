<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Services;

use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class RateLimitServiceTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');
    }

    public function testIsLoginBlocked(): void
    {
        $email = 'test_' . microtime(true);

        $rateLimitService = $this->app->getRateLimitService();
        $this->assertFalse($rateLimitService->isLoginBlocked($email));

        for ($count = 0; $count < 10; $count++) {
            $rateLimitService->logFailedLogin($email);
        }

        $this->assertTrue($rateLimitService->isLoginBlocked($email));
    }
}
