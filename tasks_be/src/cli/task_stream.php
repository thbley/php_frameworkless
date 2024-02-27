<?php

namespace TaskService\cli;

use Exception;
use TaskService\Framework\App;

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    throw new Exception('invalid interface');
}

require __DIR__ . '/../vendor/autoload.php';

$app = new App([], [], $_SERVER, '');

// lock is held until disconnect (automatically when process ends)
if (!$app->getTasksRepository()->lockCron(basename(__FILE__))) {
    throw new Exception('lock cron failed');
}

$app->getCliRoutes()->run();

exit((int) http_response_code());
