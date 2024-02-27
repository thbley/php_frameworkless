<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Framework;

use PHPUnit\Framework\TestCase;
use TaskService\Framework\Output;
use TaskService\Framework\OutputMocks;

final class OutputTest extends TestCase
{
    protected function setUp(): void
    {
        OutputMocks::$header = [];
    }

    public function testJson(): void
    {
        $this->expectOutputString('{"param":"value"}');

        $output = new Output();
        $output->json(['param' => 'value'], 200, '');

        $this->assertSame(200, http_response_code());
        $this->assertSame(['Content-Type: application/json'], OutputMocks::$header);
    }

    public function testJsonLocation(): void
    {
        $this->expectOutputString('{"foo":"bar"}');

        $output = new Output();
        $output->json(['foo' => 'bar'], 200, '/foobar');

        $this->assertSame(200, http_response_code());
        $this->assertSame(['Content-Type: application/json', 'Location: /foobar'], OutputMocks::$header);
    }

    public function testText(): void
    {
        $this->expectOutputString('some text');

        $output = new Output();
        $output->text('some text', 1);

        $this->assertSame(1, http_response_code());
    }

    public function testTextError(): void
    {
        $this->expectOutputString('some text');

        $output = new Output();
        $output->text('some text', 1);
    }

    public function testNoContent(): void
    {
        $this->expectOutputString('');

        $output = new Output();
        $output->noContent();

        $this->assertSame(204, http_response_code());
        $this->assertSame(['Content-Type: application/json'], OutputMocks::$header);
    }
}
