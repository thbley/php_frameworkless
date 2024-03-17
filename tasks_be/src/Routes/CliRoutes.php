<?php

namespace TaskService\Routes;

use TaskService\Framework\App;
use TaskService\Models\Customer;

class CliRoutes
{
    public function __construct(private App $app) {}

    public function run(): void
    {
        $app = $this->app;

        $router = $app->getRouter();
        $router->any('task_queue.php', $this->taskQueue(...));
        $router->any('task_stream.php', $this->taskStream(...));
        $router->any('generate_token.php (\d+) (\S+)', $this->generateToken(...));
        $router->any('generate_token.php .*', $this->generateTokenUsage(...));
        $router->any('update_database.php', $this->updateDatabase(...));
        $router->any('(.*)', $this->notFound(...));
        $router->match('', $app->getHeader('argv'));
    }

    private function taskQueue(): void
    {
        $processed = $this->app->getTasksController()->processTasksFromQueue();

        $this->app->getOutput()->text('Processed: ' . json_encode($processed, 0) . PHP_EOL, 0);
    }

    private function taskStream(): void
    {
        $processed = $this->app->getTasksController()->processTasksFromStream('consumer1');

        $this->app->getOutput()->text('Processed: ' . json_encode($processed, 0) . PHP_EOL, 0);
    }

    private function generateToken(int $customerId, string $email): void
    {
        $customer = new Customer($customerId, $email);

        $token = $this->app->getAuthentication()->getToken($customer, $this->app->getConfig()->privateKey);

        $this->app->getOutput()->text('export TOKEN="' . $token . '"' . PHP_EOL, 0);
    }

    private function generateTokenUsage(): void
    {
        $this->app->getOutput()->text('Usage: generate_token.php customer-id customer-email' . PHP_EOL, 1);
    }

    private function updateDatabase(): void
    {
        $path = __DIR__ . '/../Migrations/mysql/';

        $logs = $this->app->getMigrationsController()->updateDatabaseMySql($path);
        foreach ($logs as $log) {
            $this->app->getOutput()->text($log . PHP_EOL, 0);
        }

        $path = __DIR__ . '/../Migrations/clickhouse/';

        $logs = $this->app->getMigrationsController()->updateDatabaseClickHouse($path);
        foreach ($logs as $log) {
            $this->app->getOutput()->text($log . PHP_EOL, 0);
        }
    }

    private function notFound(string $input): void
    {
        $this->app->getOutput()->text('invalid input: ' . $input . PHP_EOL, 1);
    }
}
