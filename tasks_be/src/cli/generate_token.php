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
$app->getCliRoutes()->run();

exit((int) http_response_code());
