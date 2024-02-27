<?php

declare(strict_types=1);

namespace TaskService\Services;

/**
 * @param string[] $headers
 */
function mail(string $recipients, string $subject, string $content, array $headers): bool
{
    ServiceMocks::$mailRecipients = $recipients;
    ServiceMocks::$mailSubject = $subject;
    ServiceMocks::$mailContent = $content;
    ServiceMocks::$mailHeaders = $headers;

    return ServiceMocks::$mailReturn;
}

abstract class ServiceMocks
{
    public static bool $mailReturn;

    public static string $mailRecipients;

    public static string $mailSubject;

    public static string $mailContent;

    /** @var string[] */
    public static array $mailHeaders;
}
