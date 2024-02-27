<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Infrastructure;

use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class MySqlTest extends TestCase
{
    public function testMySqlConnectionUnicode(): void
    {
        $app = new App([], [], [], '');
        $database = $app->getDatabase();

        $this->assertSame(1, $database->getAttribute(PDO::ATTR_EMULATE_PREPARES));

        $statement = $database->prepare('SHOW VARIABLES');
        $statement->execute([]);

        $variables = $statement->fetchAll(PDO::FETCH_KEY_PAIR);

        $subset = [
            'character_set_client' => 'utf8mb4',
            'character_set_connection' => 'utf8mb4',
            'character_set_results' => 'utf8mb4',
            'collation_connection' => 'utf8mb4_general_ci',
            'sql_require_primary_key' => 'ON',
            'sql_mode' => 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,' .
                'ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION',
        ];
        $variables += $subset;

        $this->assertSame([], array_diff_assoc($subset, $variables));

        $expected = "\u{1F31D}";
        $statement = $database->prepare('SELECT ? as result');
        $statement->execute([$expected]);

        $actual = (string) $statement->fetchColumn();

        $this->assertSame(1, mb_strlen($actual));
        $this->assertSame($expected, $actual);
    }

    public function testNoNullableColumns(): void
    {
        $query = '
            SELECT table_name, column_name FROM information_schema.columns
            WHERE IS_NULLABLE = "Yes"
            AND table_schema = ?
        ';

        $app = new App([], [], [], '');
        $statement = $app->getDatabase()->prepare($query);
        $statement->execute([$app->getConfig()->mysqlDatabase]);

        $this->assertSame([], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function testNoDefaultValueColumns(): void
    {
        $query = '
            SELECT table_name, column_name FROM information_schema.columns
            WHERE column_default IS NOT NULL
            AND table_schema = ?
        ';

        $app = new App([], [], [], '');
        $statement = $app->getDatabase()->prepare($query);
        $statement->execute([$app->getConfig()->mysqlDatabase]);

        $this->assertSame([], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function testVarcharColumns(): void
    {
        $query = "
            SELECT table_name, column_name FROM information_schema.columns
            WHERE table_schema = ?
            AND column_type LIKE 'varchar%'
            AND (character_set_name, collation_name) NOT IN
                (('utf8mb4', 'utf8mb4_general_ci'), ('ascii', 'ascii_general_ci'))
        ";

        $app = new App([], [], [], '');
        $statement = $app->getDatabase()->prepare($query);
        $statement->execute([$app->getConfig()->mysqlDatabase]);

        $this->assertSame([], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function testIdColumns(): void
    {
        $query = "
            SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = ?
            AND (column_name = 'id' OR column_name LIKE '%_id')
            AND column_type NOT IN ('bigint unsigned', 'bigint(20) unsigned')
        ";

        $app = new App([], [], [], '');
        $statement = $app->getDatabase()->prepare($query);
        $statement->execute([$app->getConfig()->mysqlDatabase]);

        $this->assertSame([], $statement->fetchAll(PDO::FETCH_ASSOC));
    }
}
