<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Routes;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Exceptions\HttpException;
use TaskService\Models\Event;
use TaskService\Routes\HttpPublicRoutes;
use TaskService\Tests\Unit\Framework\AppMock;

final class HttpPublicRoutesTest extends TestCase
{
    public function testLoginCustomer(): void
    {
        $params = ['email' => 'foo@bar.baz', 'password' => 'insecure'];

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('POST', '/v1/customers/login'), $params);

        $appMock->getCustomersController()->expects($this->once())
            ->method('getLoginToken')
            ->with('foo@bar.baz', 'insecure')
            ->willReturn('BEARER token');

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['token' => 'BEARER token'], 201, '');

        $httpPublicRoutes = new HttpPublicRoutes($appMock);
        $httpPublicRoutes->run();
    }

    public function testLoginCustomerHttpException(): void
    {
        $event = new Event();
        $event->message = 'missing something';
        $event->code = 400;
        $event->customer = 0;
        $event->method = 'POST';
        $event->uri = '/v1/customers/login';

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('POST', '/v1/customers/login'), []);

        $appMock->getCustomersController()->expects($this->once())
            ->method('getLoginToken')
            ->willThrowException(new HttpException('missing something', 400));

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 400);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'missing something'], 400, '');

        $httpPublicRoutes = new HttpPublicRoutes($appMock);
        $httpPublicRoutes->run();
    }

    public function testLoginCustomerException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('some error');

        $event = new Event();
        $event->message = 'some error';
        $event->code = 0;
        $event->customer = 0;
        $event->method = 'POST';
        $event->uri = '/v1/customers/login';

        $appMock = new AppMock($this->createMock(...), $this->getHeaders('POST', '/v1/customers/login'), []);

        $appMock->getCustomersController()->expects($this->once())
            ->method('getLoginToken')
            ->willThrowException(new Exception('some error'));

        $appMock->getLogger()->expects($this->once())
            ->method('log')
            ->with($event, 0);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'internal server error'], 500, '');

        $httpPublicRoutes = new HttpPublicRoutes($appMock);
        $httpPublicRoutes->run();
    }

    public function testNotFound(): void
    {
        $appMock = new AppMock($this->createMock(...), [], []);

        $appMock->getOutput()->expects($this->once())
            ->method('json')
            ->with(['error' => 'not found'], 404, '');

        $httpPublicRoutes = new HttpPublicRoutes($appMock);
        $httpPublicRoutes->run();
    }

    /**
     * @return string[]
     */
    private function getHeaders(string $method, string $url): array
    {
        return [
            'REQUEST_METHOD' => $method,
            'DOCUMENT_URI' => $url,
        ];
    }
}
