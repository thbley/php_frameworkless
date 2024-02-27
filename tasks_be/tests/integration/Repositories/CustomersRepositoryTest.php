<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Repositories;

use Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class CustomersRepositoryTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $this->app = new App([], [], [], '');

        $this->app->getDatabase()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->app->getDatabase()->rollBack();
    }

    #[MaximumDuration(150)]
    public function testGetCustomer(): void
    {
        $customer = $this->app->getCustomersRepository()->getCustomer('foo@bar.baz', 'insecure');
        $this->assertNotNull($customer);
        $this->assertSame('foo@bar.baz', $customer->email);
    }

    #[MaximumDuration(150)]
    public function testGetCustomerInvalidPassword(): void
    {
        $customer = $this->app->getCustomersRepository()->getCustomer('foo@bar.baz', 'invalid');
        $this->assertNull($customer);
    }

    public function testGetCustomerInvalidEmail(): void
    {
        $customer = $this->app->getCustomersRepository()->getCustomer('foo@invalid', 'invalid');
        $this->assertNull($customer);
    }
}
