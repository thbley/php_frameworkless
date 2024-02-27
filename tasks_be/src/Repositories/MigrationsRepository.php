<?php

namespace TaskService\Repositories;

use Exception;
use TaskService\Framework\App;

class MigrationsRepository
{
    public function __construct(private App $app) {}

    public function importMySql(string $file): void
    {
        if (!is_readable($file)) {
            throw new Exception('invalid file');
        }

        $database = $this->app->getDatabase();

        $database->exec(file_get_contents($file) ?: '');

        $query = 'INSERT INTO migration SET filename = ?, created_at = now()';
        $database->prepare($query)->execute([basename($file)]);
    }

    public function isMySqlImported(string $filename): bool
    {
        $database = $this->app->getDatabase();

        $query = "SHOW tables LIKE 'migration'";
        if ($database->query($query)->fetchColumn(0) === false) {
            return false;
        }

        $query = 'SELECT filename FROM migration WHERE filename = ?';
        $statement = $database->prepare($query);
        $statement->execute([$filename]);

        return (bool) $statement->rowCount();
    }

    public function importClickHouse(string $file): void
    {
        if (!is_readable($file)) {
            throw new Exception('invalid file');
        }

        $database = $this->app->getClickHouse();

        $database->exec(file_get_contents($file) ?: '');

        $query = 'INSERT INTO migration (filename, created_at) VALUES (?, now())';
        $statement = $database->prepare($query);

        $statement->execute([basename($file)]);
    }

    public function isClickHouseImported(string $filename): bool
    {
        $database = $this->app->getClickHouse();

        $query = "SHOW tables LIKE 'migration'";
        if ($database->query($query)->fetchColumn(0) === false) {
            return false;
        }

        $query = 'SELECT filename FROM migration WHERE filename = ?';
        $statement = $database->prepare($query);
        $statement->execute([$filename]);

        return (bool) $statement->rowCount();
    }
}
