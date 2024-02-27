<?php

namespace TaskService\Routes;

class Router
{
    /** @var array{string, string, callable}[] */
    private array $routes = [];

    public function get(string $pattern, callable $callback): void
    {
        $this->routes[] = ['GET', $pattern, $callback];
    }

    public function put(string $pattern, callable $callback): void
    {
        $this->routes[] = ['PUT', $pattern, $callback];
    }

    public function post(string $pattern, callable $callback): void
    {
        $this->routes[] = ['POST', $pattern, $callback];
    }

    public function patch(string $pattern, callable $callback): void
    {
        $this->routes[] = ['PATCH', $pattern, $callback];
    }

    public function delete(string $pattern, callable $callback): void
    {
        $this->routes[] = ['DELETE', $pattern, $callback];
    }

    public function any(string $pattern, callable $callback): void
    {
        $this->routes[] = ['', $pattern, $callback];
    }

    public function match(string $requestMethod, string $requestPath): void
    {
        foreach ($this->routes as $route) {
            [$method, $pattern, $callback] = $route;

            if ($method !== '' && $requestMethod !== $method) {
                continue;
            }

            // extract parameter values from URL
            $values = [];
            if (preg_match('#^' . $pattern . '$#', $requestPath, $values) !== 1) {
                continue;
            }

            array_shift($values);

            $callback(...$values);

            break;
        }
    }
}
