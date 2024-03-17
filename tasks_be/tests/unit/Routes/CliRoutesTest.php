<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Routes;

use PHPUnit\Framework\TestCase;
use TaskService\Models\Customer;
use TaskService\Routes\CliRoutes;
use TaskService\Tests\Unit\Framework\AppMock;

final class CliRoutesTest extends TestCase
{
    public function testRunTaskQueue(): void
    {
        $appMock = new AppMock($this->createMock(...), ['argv' => ['task_queue.php']], []);

        $appMock->getTasksController()->expects($this->once())
            ->method('processTasksFromQueue');

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }

    public function testRunTaskStream(): void
    {
        $appMock = new AppMock($this->createMock(...), ['argv' => ['task_stream.php']], []);

        $appMock->getTasksController()->expects($this->once())
            ->method('processTasksFromStream')
            ->with('consumer1')
            ->willReturn(['some-id']);

        $appMock->getOutput()->expects($this->once())
            ->method('text')
            ->with('Processed: ["some-id"]' . PHP_EOL, 0);

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }

    public function testRunGenerateToken(): void
    {
        $customer = new Customer(12345, 'foo@invalid.local');

        $argv = ['generate_token.php', '12345', 'foo@invalid.local'];
        $appMock = new AppMock($this->createMock(...), ['argv' => $argv], []);

        $appMock->getAuthentication()->expects($this->once())
            ->method('getToken')
            ->with($customer, $appMock->getConfig()->privateKey)
            ->willReturn('secret');

        $appMock->getOutput()->expects($this->once())
            ->method('text')
            ->with('export TOKEN="secret"' . PHP_EOL, 0);

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }

    public function testRunGenerateTokenUsage(): void
    {
        $appMock = new AppMock($this->createMock(...), ['argv' => ['generate_token.php', 'foo']], []);

        $appMock->getOutput()->expects($this->once())
            ->method('text')
            ->with('Usage: generate_token.php customer-id customer-email' . PHP_EOL, 1);

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }

    public function testRunUpdateDatabase(): void
    {
        $appMock = new AppMock($this->createMock(...), ['argv' => ['update_database.php']], []);

        $appMock->getMigrationsController()->expects($this->once())
            ->method('updateDatabaseMySql')
            ->willReturn(['Processing foo.sql']);

        $appMock->getMigrationsController()->expects($this->once())
            ->method('updateDatabaseClickHouse')
            ->willReturn(['Processing bar.sql']);

        $appMock->getOutput()->expects($this->exactly(2))
            ->method('text')
            ->with($this->stringStartsWith('Processing'), 0);

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }

    public function testNotFound(): void
    {
        $appMock = new AppMock($this->createMock(...), ['argv' => []], []);

        $appMock->getOutput()->expects($this->once())
            ->method('text')
            ->with('invalid input: ' . PHP_EOL, 1);

        $cliRoutes = new CliRoutes($appMock);
        $cliRoutes->run();
    }
}
