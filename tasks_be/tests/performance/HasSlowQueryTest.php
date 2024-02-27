<?php

declare(strict_types=1);

namespace TaskService\Tests\Performance;

use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class HasSlowQueryTest extends TestCase
{
    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function testNoMysqlSlowQueriesInPreviousTests(): void
    {
        $startTime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? 0);

        $app = new App([], [], [], '');
        $database = $app->getDatabase();

        $database->query('SELECT sleep(0.01)');

        $query = '
            SELECT query_time, lock_time, rows_sent, rows_examined, convert(sql_text using utf8mb4) as query
            FROM mysql.slow_log
            WHERE start_time > ?
            HAVING query not like "INSERT INTO%"
            AND query not like "CREATE%"
            AND query not like "SHOW%"
            AND query not like "%information_schema%"
        ';
        $statement = $database->prepare($query);
        $statement->execute([$startTime]);

        $actual = $statement->fetchAll(PDO::FETCH_ASSOC);
        $expected = ['SELECT sleep(0.01)'];

        $this->assertSame($expected, array_column($actual, 'query'), json_encode($actual, JSON_PRETTY_PRINT) ?: '');
    }
}
