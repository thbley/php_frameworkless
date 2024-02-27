<?php

namespace TaskService;

use TaskService\Framework\App;
use Throwable;

(function (array $get, array $post, array $server) {
    error_reporting(E_ALL);
    require __DIR__ . '/vendor/autoload.php';

    try {
        $app = new App($get, $post, $server, 'php://input');
        $app->getHttpRoutes()->run();
    } catch (Throwable $throwable) {
        // log uncaught exceptions as E_USER_WARNING
        trigger_error((string) $throwable, E_USER_WARNING);
    }
})($_GET, $_POST, $_SERVER);
