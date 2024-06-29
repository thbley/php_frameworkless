<?php

declare(strict_types=1);

namespace TaskService\Tests\Integration\Infrastructure;

use Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[MaximumDuration(150)]
    public function testEmailDelivery(): void
    {
        $subject = 'test ' . microtime(true);

        $result = mail('recipient@invalid.local', $subject, 'some content', 'From: sender@invalid.local');
        $this->assertTrue($result);

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_URL => sprintf('http://%s:8025/api/v1/messages?limit=1', gethostbyname('mailpit')),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = (string) curl_exec($curlHandle);
        $this->assertSame('', curl_error($curlHandle));
        curl_close($curlHandle);

        /**
         * @var ?array{ messages:array{ID:string}[] } $data
         */
        $data = json_decode($response, true, 10, 0);

        $id = $data['messages'][0]['ID'] ?? '';
        $this->assertNotEmpty($id);

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_URL => sprintf('http://%s:8025/api/v1/message/%s', gethostbyname('mailpit'), urlencode($id)),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = (string) curl_exec($curlHandle);
        $this->assertSame('', curl_error($curlHandle));
        curl_close($curlHandle);

        /**
         * @var ?array{ Subject:string, Text:string, From:array{Address:string}, To:array{Address:string}[] } $data
         */
        $data = json_decode($response, true, 10, 0);

        $this->assertNotEmpty($data);
        $this->assertSame('sender@invalid.local', $data['From']['Address']);
        $this->assertSame('recipient@invalid.local', $data['To'][0]['Address'] ?? '');
        $this->assertSame($subject, $data['Subject']);
        $this->assertSame('some content', trim($data['Text']));
    }
}
