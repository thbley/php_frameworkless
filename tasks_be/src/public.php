<?php

namespace TaskService;

use TaskService\Framework\App;
use Throwable;

error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

try {
    $app = new App($_GET, $_POST, $_SERVER, 'php://input');
    $app->getHttpPublicRoutes()->run();
} catch (Throwable $throwable) {
    // log uncaught exceptions as E_USER_WARNING
    trigger_error((string) $throwable, E_USER_WARNING);
}
