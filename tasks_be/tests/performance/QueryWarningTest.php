<?php

declare(strict_types=1);

namespace TaskService\Tests\Performance;

use Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration;
use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class QueryWarningTest extends TestCase
{
    private App $app;

    private string $eventTime;

    private string $testDatabase;

    /** @SuppressWarnings(PHPMD.Superglobals) */
    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');

        $this->eventTime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? 0);

        $this->testDatabase = 'test_' . ($_SERVER['REQUEST_TIME'] ?? 0);

        $database = $this->app->getDatabase();
        $database->query('CREATE DATABASE ' . $this->testDatabase);
        $database->query('USE ' . $this->testDatabase);
    }

    protected function tearDown(): void
    {
        $this->app->getDatabase()->query('DROP DATABASE ' . $this->testDatabase);
    }

    #[MaximumDuration(5000)]
    public function testReplayQueriesAndCheckWarnings(): void
    {
        $database = $this->app->getDatabase();

        $query = '
            SELECT convert(argument using utf8mb4) as query
            FROM mysql.general_log
            WHERE event_time > ? AND command_type = "Query"
            HAVING query NOT LIKE "SHOW%" AND query NOT LIKE "USE%" AND query NOT LIKE "CREATE%"
        ';
        $statement = $database->prepare($query);
        $statement->execute([$this->eventTime]);

        /** @var string[] $queries */
        $queries = $statement->fetchAll(PDO::FETCH_COLUMN);

        $path = __DIR__ . '/../../src/Migrations/mysql/';

        $actual = [];
        foreach (scandir($path) ?: [] as $file) {
            if (str_ends_with($file, '.sql')) {
                $migration = file_get_contents($path . $file) ?: '';
                $database->exec($migration);

                $warnings = $database->query('SHOW WARNINGS')->fetchAll(PDO::FETCH_ASSOC);
                if ($warnings !== []) {
                    $actual[$migration] = $warnings;
                }
            }
        }

        foreach ($queries as $query) {
            $database->query($query);

            $warnings = $database->query('SHOW WARNINGS')->fetchAll(PDO::FETCH_ASSOC);
            if ($warnings !== []) {
                $actual[$query] = $warnings;
            }
        }

        $this->assertSame([], $actual);
    }
}
