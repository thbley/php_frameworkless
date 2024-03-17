<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Services;

use Exception;
use PHPUnit\Framework\TestCase;
use TaskService\Models\Email;
use TaskService\Services\EmailService;
use TaskService\Services\ServiceMocks;

final class EmailServiceTest extends TestCase
{
    public function testSend(): void
    {
        ServiceMocks::$mailReturn = true;

        $email = new Email(
            'Task #41 completed',
            'foo.sender@invalid.local',
            'foo.receiver@invalid.local',
            'Subject: Task #41 completed'
        );

        $emailService = new EmailService();
        $emailService->send($email);

        $expectedHeaders = [
            'From' => $email->from,
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Transfer-Encoding' => 'quoted-printable',
        ];
        $this->assertSame($email->recipients, ServiceMocks::$mailRecipients);
        $this->assertSame('=?UTF-8?Q?Task #41 completed?=', ServiceMocks::$mailSubject);
        $this->assertSame('Subject: Task #41 completed', ServiceMocks::$mailContent);
        $this->assertSame($expectedHeaders, ServiceMocks::$mailHeaders);
    }

    public function testSendMissingContent(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('missing content');

        $email = new Email('Task #41 completed', 'foo.sender@invalid.local', 'foo.receiver@invalid.local', '');

        $emailService = new EmailService();
        $emailService->send($email);
    }

    public function testSendFailure(): void
    {
        set_error_handler($this->throwExceptionOnError(...), E_USER_WARNING);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('failed to send');

        try {
            ServiceMocks::$mailReturn = false;

            $email = new Email('test', 'foo@invalid.local', 'bar@invalid.local', 'foo');
            $emailService = new EmailService();
            $emailService->send($email);
        } finally {
            restore_error_handler();
        }
    }

    private function throwExceptionOnError(int $errno, string $errstr): never
    {
        throw new Exception($errstr, $errno);
    }
}
