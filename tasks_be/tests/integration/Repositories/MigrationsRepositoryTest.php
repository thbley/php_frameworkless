<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Repositories;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;
use TaskService\Repositories\MigrationsRepository;

final class MigrationsRepositoryTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');

        file_put_contents('/tmp/migration.sql', "INSERT INTO migration VALUES ('foo.sql', now());");
        file_put_contents('/tmp/clickhouse.sql', "INSERT INTO migration VALUES ('foo.sql', now());");
    }

    protected function tearDown(): void
    {
        $database = $this->app->getDatabase();
        $database->query("DELETE FROM migration WHERE filename IN ('migration.sql', 'foo.sql')");

        $clickhouse = $this->app->getClickHouse();
        $clickhouse->query("DELETE FROM migration WHERE filename IN ('clickhouse.sql', 'foo.sql')");

        unlink('/tmp/migration.sql');
        unlink('/tmp/clickhouse.sql');
    }

    public function testImportMySql(): void
    {
        $migrationsRepository = $this->app->getMigrationsRepository();
        $migrationsRepository->importMySql('/tmp/migration.sql');

        $database = $this->app->getDatabase();

        $query = "SELECT filename FROM migration WHERE filename IN ('migration.sql', 'foo.sql')";
        $this->assertSame(['foo.sql', 'migration.sql'], $database->query($query)->fetchAll(PDO::FETCH_COLUMN));
    }

    public function testImportMySqlInvalidFile(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid file');

        $migrationsRepository = $this->app->getMigrationsRepository();
        $migrationsRepository->importMySql('/tmp/invalid.sql');
    }

    public function testIsMySqlImported(): void
    {
        $migrationsRepository = $this->app->getMigrationsRepository();

        $this->assertFalse($migrationsRepository->isMySqlImported('migration.sql'));

        $migrationsRepository->importMySql('/tmp/migration.sql');

        $this->assertTrue($migrationsRepository->isMySqlImported('migration.sql'));
        $this->assertTrue($migrationsRepository->isMySqlImported('foo.sql'));
        $this->assertFalse($migrationsRepository->isMySqlImported('unknown.sql'));
    }

    public function testIsMySqlImportedNoMigrationSchema(): void
    {
        $database = $this->app->getDatabase();
        $database->exec('CREATE DATABASE if not exists test');
        $database->exec('use test');

        $migrationsRepository = new MigrationsRepository($this->app);
        $this->assertFalse($migrationsRepository->isMySqlImported('imported.sql'));

        $database->exec('use tasks');
    }

    public function testImportClickHouse(): void
    {
        $migrationsRepository = $this->app->getMigrationsRepository();
        $migrationsRepository->importClickHouse('/tmp/clickhouse.sql');

        $clickhouse = $this->app->getClickHouse();

        $query = "SELECT count(*) FROM migration WHERE filename IN ('clickhouse.sql', 'foo.sql')";
        $this->assertSame('2', $clickhouse->query($query)->fetchColumn(0));
    }

    public function testImportClickHouseInvalidFile(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid file');

        $migrationsRepository = $this->app->getMigrationsRepository();
        $migrationsRepository->importClickHouse('/tmp/invalid.sql');
    }

    public function testIsClickHouseImported(): void
    {
        $migrationsRepository = $this->app->getMigrationsRepository();

        $this->assertFalse($migrationsRepository->isClickHouseImported('clickhouse.sql'));

        $migrationsRepository->importClickHouse('/tmp/clickhouse.sql');

        $this->assertTrue($migrationsRepository->isClickHouseImported('clickhouse.sql'));
        $this->assertTrue($migrationsRepository->isClickHouseImported('foo.sql'));
        $this->assertFalse($migrationsRepository->isClickHouseImported('unknown.sql'));
    }

    public function testIsClickHouseImportedNoMigrationSchema(): void
    {
        $clickhouse = $this->app->getClickHouse();
        $clickhouse->exec('CREATE DATABASE if not exists test');
        $clickhouse->exec('use test');

        $migrationsRepository = new MigrationsRepository($this->app);
        $this->assertFalse($migrationsRepository->isClickHouseImported('imported.sql'));

        $clickhouse->exec('use tasks');
    }
}
