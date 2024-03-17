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

        $data = [
            'message' => $event->message,
            'code' => $event->code,
            'customer' => $event->customer,
            'method' => $event->method,
            'uri' => $event->uri,
            'datetime' => date('c'),
            'status' => $status,
        ];
        file_put_contents($this->app->getConfig()->logfile, json_encode($data, 0) . PHP_EOL, FILE_APPEND);
    }
}
