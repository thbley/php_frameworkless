<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use TaskService\Controllers\MigrationsController;
use TaskService\Tests\Unit\Framework\AppMock;

final class MigrationsControllerTest extends TestCase
{
    private AppMock $appMock;

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);
    }

    public function testUpdateDatabaseMySql(): void
    {
        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('isMySqlImported')
            ->with($this->isType('string'))
            ->willReturn(false);

        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('importMySql')
            ->with($this->isType('string'));

        $migrationsController = new MigrationsController($this->appMock);
        $actual = $migrationsController->updateDatabaseMySql(__DIR__ . '/../../../src/Migrations/mysql/');

        $this->assertContains('Processing mysql/2020-05-21_2000_add_migration_table.sql', [...$actual]);
    }

    public function testUpdateDatabaseMySqlAllDone(): void
    {
        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('isMySqlImported')
            ->with($this->isType('string'))
            ->willReturn(true);

        $this->appMock->getMigrationsRepository()->expects($this->never())
            ->method('importMySql');

        $migrationsController = new MigrationsController($this->appMock);
        $actual = $migrationsController->updateDatabaseMySql(__DIR__ . '/../../../src/Migrations/mysql/');

        $this->assertSame([], [...$actual]);
    }

    public function testUpdateDatabaseClickHouse(): void
    {
        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('isClickHouseImported')
            ->with($this->isType('string'))
            ->willReturn(false);

        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('importClickHouse')
            ->with($this->isType('string'));

        $migrationsController = new MigrationsController($this->appMock);
        $actual = $migrationsController->updateDatabaseClickHouse(__DIR__ . '/../../../src/Migrations/clickhouse/');

        $this->assertContains('Processing clickhouse/2020-05-21_2000_add_migration_table.sql', [...$actual]);
    }

    public function testUpdateDatabaseClickHouseAllDone(): void
    {
        $this->appMock->getMigrationsRepository()->expects($this->atLeastOnce())
            ->method('isClickHouseImported')
            ->with($this->isType('string'))
            ->willReturn(true);

        $this->appMock->getMigrationsRepository()->expects($this->never())
            ->method('importClickHouse');

        $migrationsController = new MigrationsController($this->appMock);
        $actual = $migrationsController->updateDatabaseClickHouse(__DIR__ . '/../../../src/Migrations/clickhouse/');

        $this->assertSame([], [...$actual]);
    }
}
