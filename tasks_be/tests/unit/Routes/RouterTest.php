<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Routes;

use PHPUnit\Framework\TestCase;
use TaskService\Routes\Router;

final class RouterTest extends TestCase
{
    public function testGet(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->post('/v1/api/123/abc', $callbackMocks->never(...));
        $router->get('/v1/api/other', $callbackMocks->never(...));
        $router->get('/v1/api/(\d+)/(\w+)', $callbackMocks->calledWith(...));
        $router->match('GET', '/v1/api/123/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
        $this->assertSame([123, 'abc'], $callbackMocks->with);
    }

    public function testPut(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->post('/v1/api/abc', $callbackMocks->never(...));
        $router->put('/v1/api/other', $callbackMocks->never(...));
        $router->put('/v1/api/abc', $callbackMocks->called(...));
        $router->match('PUT', '/v1/api/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
    }

    public function testPost(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->put('/v1/api/abc', $callbackMocks->never(...));
        $router->post('/v1/api/other', $callbackMocks->never(...));
        $router->post('/v1/api/abc', $callbackMocks->called(...));
        $router->match('POST', '/v1/api/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
    }

    public function testPatch(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->post('/v1/api/abc', $callbackMocks->never(...));
        $router->patch('/v1/api/other', $callbackMocks->never(...));
        $router->patch('/v1/api/abc', $callbackMocks->called(...));
        $router->match('PATCH', '/v1/api/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
    }

    public function testDelete(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->patch('/v1/api/abc', $callbackMocks->never(...));
        $router->delete('/v1/api/other', $callbackMocks->never(...));
        $router->delete('/v1/api/abc', $callbackMocks->called(...));
        $router->match('DELETE', '/v1/api/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
    }

    public function testAny(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->any('/v1/api/test', $callbackMocks->never(...));
        $router->any('/v1/api/abc', $callbackMocks->called(...));
        $router->any('/v1/api/abc', $callbackMocks->never(...));
        $router->match('GET', '/v1/api/abc');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
    }

    public function testCli(): void
    {
        $callbackMocks = new CallbackMocks();

        $router = new Router();
        $router->any('example\.php', $callbackMocks->never(...));
        $router->any('example\.php (\w+)', $callbackMocks->calledWithString(...));
        $router->any('example\.php', $callbackMocks->never(...));
        $router->match('', 'example.php 123');

        $this->assertFalse($callbackMocks->never);
        $this->assertTrue($callbackMocks->called);
        $this->assertSame(['123'], $callbackMocks->with);
    }
}
