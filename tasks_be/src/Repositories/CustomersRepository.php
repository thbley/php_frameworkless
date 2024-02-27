<?php

namespace TaskService\Repositories;

use SensitiveParameter;
use TaskService\Framework\App;
use TaskService\Models\Customer;

class CustomersRepository
{
    public function __construct(private App $app) {}

    public function getCustomer(string $email, #[SensitiveParameter] string $password): ?Customer
    {
        $statement = $this->app->getDatabase()->prepare('SELECT id, email, password FROM customer WHERE email = ?');
        $statement->execute([$email]);

        $customer = $statement->fetchObject(Customer::class);

        if ($customer === false || !password_verify($password, $customer->password)) {
            return null;
        }

        return $customer;
    }
}
