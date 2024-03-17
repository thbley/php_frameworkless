<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Framework;

use PHPUnit\Framework\TestCase;
use TaskService\Framework\Logger;
use TaskService\Models\Event;

final class LoggerTest extends TestCase
{
    public function testLog(): void
    {
        $logfile = tempnam('/tmp', 'loggertest') ?: '';

        $appMock = new AppMock($this->createMock(...), [], []);
        $appMock->getConfig()->logfile = $logfile;

        $event = new Event('value', 0, 0, '', '');

        $logger = new Logger($appMock);
        $logger->log($event, 200);

        $actual = (array) json_decode(file_get_contents($logfile) ?: '', true, 10, 0);

        $this->assertNotEmpty($actual);
        $this->assertSame('value', $actual['message'] ?? '');
        $this->assertSame('INFO', $actual['status'] ?? '');

        $datetime = (string) ($actual['datetime'] ?? '');
        $this->assertEqualsWithDelta(strtotime(date('c')), strtotime($datetime), 5);
    }

    public function testLogWarning(): void
    {
        $logfile = tempnam('/tmp', 'loggertest') ?: '';

        $appMock = new AppMock($this->createMock(...), [], []);
        $appMock->getConfig()->logfile = $logfile;

        $event = new Event('value', 0, 0, '', '');

        $logger = new Logger($appMock);
        $logger->log($event, 404);

        $actual = (array) json_decode(file_get_contents($logfile) ?: '', true, 10, 0);

        $this->assertNotEmpty($actual);
        $this->assertSame('WARNING', $actual['status'] ?? '');
    }

    public function testLogError(): void
    {
        $logfile = tempnam('/tmp', 'loggertest') ?: '';

        $appMock = new AppMock($this->createMock(...), [], []);
        $appMock->getConfig()->logfile = $logfile;

        $event = new Event('value', 0, 0, '', '');

        $logger = new Logger($appMock);
        $logger->log($event, 500);

        $actual = (array) json_decode(file_get_contents($logfile) ?: '', true, 10, 0);

        $this->assertNotEmpty($actual);
        $this->assertSame('ERROR', $actual['status'] ?? '');
    }
}
