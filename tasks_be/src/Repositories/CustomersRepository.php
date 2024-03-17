<?php

namespace TaskService\Repositories;

use PDO;
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

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false || !password_verify($password, (string) ($row['password'] ?? ''))) {
            return null;
        }

        return new Customer((int) ($row['id'] ?? 0), (string) ($row['email'] ?? ''));
    }
}
