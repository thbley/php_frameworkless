<?php

namespace TaskService\Controllers;

use TaskService\Framework\App;

class MigrationsController
{
    public function __construct(private App $app) {}

    /**
     * @return iterable<int, string>
     */
    public function updateDatabaseMySql(string $path): iterable
    {
        $migrationsRepository = $this->app->getMigrationsRepository();

        foreach (scandir($path) ?: [] as $file) {
            if (str_ends_with($file, '.sql') && !$migrationsRepository->isMySqlImported($file)) {
                yield 'Processing mysql/' . $file;

                $migrationsRepository->importMySql($path . $file);
            }
        }
    }

    /**
     * @return iterable<int, string>
     */
    public function updateDatabaseClickHouse(string $path): iterable
    {
        $migrationsRepository = $this->app->getMigrationsRepository();

        foreach (scandir($path) ?: [] as $file) {
            if (str_ends_with($file, '.sql') && !$migrationsRepository->isClickHouseImported($file)) {
                yield 'Processing clickhouse/' . $file;

                $migrationsRepository->importClickHouse($path . $file);
            }
        }
    }
}
