<?php

namespace TaskService\Framework;

use TaskService\Models\Event;

class Logger
{
    public function __construct(private App $app) {}

    public function log(Event $event, int $code): void
    {
        $status = 'INFO';
        if ($code >= 500 || $code < 200) {
            $status = 'ERROR';
        } elseif ($code >= 400) {
            $status = 'WARNING';
        }

        $event->status = $status;
        $event->datetime = date('c');

        file_put_contents($this->app->getConfig()->logfile, json_encode($event, 0) . PHP_EOL, FILE_APPEND);
    }
}
