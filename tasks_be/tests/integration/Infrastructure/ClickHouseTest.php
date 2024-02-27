<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Infrastructure;

use PDO;
use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class ClickHouseTest extends TestCase
{
    public function testClickHouseConnectionUnicode(): void
    {
        $expected = "\u{1F31D}";

        $app = new App([], [], [], '');
        $clickhouse = $app->getClickHouse();

        $this->assertTrue($clickhouse->getAttribute(PDO::ATTR_EMULATE_PREPARES));

        $statement = $clickhouse->prepare('SELECT ? as result');
        $statement->execute([$expected]);

        $actual = (string) $statement->fetchColumn();

        $this->assertSame(1, mb_strlen($actual));
        $this->assertSame($expected, $actual);
    }
}
