<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use TaskService\Controllers\CustomersController;
use TaskService\Exceptions\HttpException;
use TaskService\Models\Customer;
use TaskService\Tests\Unit\Framework\AppMock;

final class CustomersControllerTest extends TestCase
{
    private AppMock $appMock;

    protected function setUp(): void
    {
        $this->appMock = new AppMock($this->createMock(...), [], []);
    }

    public function testGetLoginToken(): void
    {
        $customer = new Customer(41, '');

        $this->appMock->getRateLimitService()->expects($this->once())
            ->method('isLoginBlocked')
            ->with('foo@bar.baz')
            ->willReturn(false);

        $this->appMock->getCustomersRepository()->expects($this->once())
            ->method('getCustomer')
            ->with('foo@bar.baz', 'insecure')
            ->willReturn($customer);

        $this->appMock->getAuthentication()->expects($this->once())
            ->method('getToken')
            ->with($customer, $this->appMock->getConfig()->privateKey)
            ->willReturn('BEARER token');

        $customersController = new CustomersController($this->appMock);

        $this->assertSame('BEARER token', $customersController->getLoginToken('foo@bar.baz', 'insecure'));
    }

    public function testGetLoginTokenLoginBlocked(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('unauthorized');

        $this->appMock->getRateLimitService()->expects($this->once())
            ->method('isLoginBlocked')
            ->with('foo@bar.baz')
            ->willReturn(true);

        $customersController = new CustomersController($this->appMock);
        $customersController->getLoginToken('foo@bar.baz', 'invalid');
    }

    public function testGetLoginTokenInvalidPassword(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('unauthorized');

        $this->appMock->getRateLimitService()->expects($this->once())
            ->method('isLoginBlocked')
            ->with('foo@bar.baz')
            ->willReturn(false);

        $this->appMock->getCustomersRepository()->expects($this->once())
            ->method('getCustomer')
            ->with('foo@bar.baz', 'invalid')
            ->willReturn(null);

        $customersController = new CustomersController($this->appMock);
        $customersController->getLoginToken('foo@bar.baz', 'invalid');
    }

    public function testGetLoginTokenEmptyEmail(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('missing email');

        $customersController = new CustomersController($this->appMock);
        $customersController->getLoginToken('', 'insecure');
    }

    public function testGetLoginTokenEmptyPassword(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('missing password');

        $customersController = new CustomersController($this->appMock);
        $customersController->getLoginToken('foo@bar.baz', '');
    }
}
