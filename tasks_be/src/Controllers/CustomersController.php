<?php

namespace TaskService\Controllers;

use SensitiveParameter;
use TaskService\Exceptions\HttpException;
use TaskService\Framework\App;

class CustomersController
{
    /**
     * passing App allows late initialization (e.g. open db connection only when needed), avoids circular references
     */
    public function __construct(private App $app) {}

    public function getLoginToken(string $email, #[SensitiveParameter] string $password): string
    {
        if ($email === '') {
            throw new HttpException('missing email', 400);
        }

        if ($password === '') {
            throw new HttpException('missing password', 400);
        }

        if ($this->app->getRateLimitService()->isLoginBlocked($email)) {
            error_log('login is blocked for ' . $email);

            throw new HttpException('unauthorized', 401);
        }

        $customer = $this->app->getCustomersRepository()->getCustomer($email, $password);
        if ($customer === null) {
            $this->app->getRateLimitService()->logFailedLogin($email);

            throw new HttpException('unauthorized', 401);
        }

        return $this->app->getAuthentication()->getToken($customer, $this->app->getConfig()->privateKey);
    }
}
