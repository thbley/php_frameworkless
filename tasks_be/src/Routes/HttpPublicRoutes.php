<?php

namespace TaskService\Routes;

use TaskService\Exceptions\HttpException;
use TaskService\Framework\App;
use TaskService\Models\Event;
use Throwable;

class HttpPublicRoutes
{
    public function __construct(private App $app) {}

    public function run(): void
    {
        $app = $this->app;

        try {
            $router = $app->getRouter();
            $router->post('/v1/customers/login', $this->postCustomersLogin(...));
            $router->any('.*', $this->notFound(...));

            $router->match($app->getHeader('REQUEST_METHOD'), $app->getHeader('DOCUMENT_URI'));
        } catch (Throwable $throwable) {
            $event = new Event(
                $throwable->getMessage(),
                (int) $throwable->getCode(),
                0,
                $app->getHeader('REQUEST_METHOD'),
                $app->getHeader('DOCUMENT_URI'),
                ''
            );
            $app->getLogger()->log($event, $event->code);

            if (!$throwable instanceof HttpException) {
                $app->getOutput()->json(['error' => 'internal server error'], 500, '');

                throw $throwable;
            }

            $app->getOutput()->json(['error' => $throwable->getMessage()], $throwable->getCode(), '');
        }
    }

    private function postCustomersLogin(): void
    {
        $token = $this->app->getCustomersController()->getLoginToken(
            $this->app->getParam('email'), $this->app->getParam('password')
        );

        $this->app->getOutput()->json(['token' => $token], 201, '');
    }

    private function notFound(): void
    {
        $this->app->getOutput()->json(['error' => 'not found'], 404, '');
    }
}
