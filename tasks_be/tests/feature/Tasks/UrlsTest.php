<?php

declare(strict_types=1);

namespace TaskService\Tests\Feature\Tasks;

use PHPUnit\Framework\TestCase;

final class UrlsTest extends TestCase
{
    public function testPublicDocs(): void
    {
        $actual = $this->curlHtml('GET', '/docs/tasks/api_openapi.json', 200);
        $this->assertStringContainsString('Tasks PHP API', $actual);

        $actual = $this->curlHtml('GET', '/docs/tasks/api.html.gz', 200);
        $this->assertStringContainsString('PHP API', $actual);
    }

    public function testLocalCoverage(): void
    {
        $actual = $this->curlHtml('GET', '/coverage/tasks/', 200);
        $this->assertStringContainsString('Code Coverage', $actual);
    }

    public function testUrlNotFound(): void
    {
        $actual = $this->curlHtml('GET', '/foobar', 404);
        $this->assertStringContainsString('404 Not Found', $actual);
    }

    private function curlHtml(string $method, string $url, int $status): string
    {
        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => 'http://nginx:8080' . $url,
            CURLOPT_ENCODING => '',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = explode("\r\n\r\n", (string) curl_exec($curlHandle), 2);
        $body = $response[1] ?? '';

        $responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        /** @var float $time */
        $time = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);
        $error = curl_error($curlHandle);
        curl_close($curlHandle);

        $this->assertSame('', $error);
        $this->assertSame($status, $responseCode, json_encode([$response, $responseCode, $time, $error], 0) ?: '');
        $this->assertMatchesRegularExpression('!Content-Type: (text/html|application/json)!', $response[0]);
        $this->assertStringContainsString('Strict-Transport-Security: max-age=31536000', $response[0]);
        $this->assertStringContainsString("Content-Security-Policy: default-src 'none'", $response[0]);

        return $body;
    }
}
