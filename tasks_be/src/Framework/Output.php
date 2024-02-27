<?php

namespace TaskService\Framework;

class Output
{
    /**
     * @param mixed[] $data
     */
    public function json(array $data, int $code, string $location): void
    {
        http_response_code($code);
        header('Content-Type: application/json');

        if ($location !== '') {
            header('Location: ' . $location);
        }

        echo $this->escape($data);
    }

    public function noContent(): void
    {
        http_response_code(204);
        header('Content-Type: application/json');
    }

    public function text(string $text, int $code): void
    {
        http_response_code($code);
        echo $text;
    }

    /**
     * @param mixed[] $data
     *
     * @psalm-taint-escape html
     * @psalm-taint-escape has_quotes
     */
    private function escape(array $data): string
    {
        return json_encode($data, 0) ?: '';
    }
}
