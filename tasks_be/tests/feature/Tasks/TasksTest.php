<?php

declare(strict_types=1);

namespace TaskService\Tests\Feature\Tasks;

use Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration;
use Exception;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use TaskService\Config\Config;
use TaskService\Framework\App;
use TaskService\Framework\Authentication;
use TaskService\Models\Customer;
use TaskService\Services\ServiceMocks;

final class TasksTest extends TestCase
{
    private string $authorization;

    protected function setUp(): void
    {
        $config = new Config();
        $authentication = new Authentication();
        $customer = new Customer(42, 'foo.bar@invalid.local');

        $this->authorization = $authentication->getToken($customer, $config->privateKey);
    }

    #[MaximumDuration(150)]
    public function testLoginCustomer(): void
    {
        $request = ['email' => 'foo@bar.baz', 'password' => 'insecure'];
        $actual = $this->curl('POST', '/v1/customers/login', '/v1/customers/login', $request, 201);
        $this->assertNotEmpty($actual['token'] ?? null);
    }

    #[MaximumDuration(150)]
    public function testLoginCustomerFail(): void
    {
        $request = ['email' => 'foo@bar.baz', 'password' => 'invalid'];
        $actual = $this->curl('POST', '/v1/customers/login', '/v1/customers/login', $request, 401);
        $this->assertSame(['error' => 'unauthorized'], $actual);
    }

    public function testTaskNotFound(): void
    {
        $actual = $this->curl('GET', '/v1/tasks/123123', '/v1/tasks/{taskId}', [], 404);
        $this->assertSame(['error' => 'task not found'], $actual);
    }

    public function testGetTask(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $actual = $this->curl('GET', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', [], 200);
        $this->assertSame($task, $actual);
    }

    public function testCreateTask(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $expected = ['id' => (int) $taskId, 'title' => 'test', 'duedate' => '2020-05-22', 'completed' => false];
        $this->assertSame($expected, $task);

        $actual = $this->curl('GET', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', [], 200);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateTask(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $expected = ['id' => (int) $taskId, 'title' => 'test2', 'duedate' => '2020-06-22', 'completed' => true];

        $task = $this->curl('PUT', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', $expected, 200);
        $this->assertSame($expected, $task);

        $actual = $this->curl('GET', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', [], 200);
        $this->assertSame($expected, $actual);
    }

    public function testDeleteTask(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $actual = $this->curl('DELETE', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', [], 204);
        $this->assertSame([''], $actual);

        $task = $this->curl('GET', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', [], 404);
        $this->assertSame(['error' => 'task not found'], $task);
    }

    public function testGetCurrentTasks(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $actual = $this->curl('GET', '/v1/tasks', '/v1/tasks', [], 200);
        $this->assertContains($task, $actual);

        $actual = $this->curl('GET', '/v1/tasks?page=2', '/v1/tasks', [], 200);
        $this->assertSame([], $actual);
    }

    public function testGetCompletedTasks(): void
    {
        $task = $this->curl('POST', '/v1/tasks', '/v1/tasks', ['title' => 'test', 'duedate' => '2020-05-22'], 201);

        $taskId = (string) ($task['id'] ?? '');
        $this->assertNotEmpty($taskId);

        $expected = ['id' => (int) $taskId, 'title' => 'test2', 'duedate' => '2020-06-22', 'completed' => true];

        $task = $this->curl('PUT', '/v1/tasks/' . $taskId, '/v1/tasks/{taskId}', $expected, 200);
        $this->assertSame($expected, $task);

        $actual = $this->curl('GET', '/v1/tasks?completed=1', '/v1/tasks', [], 200);
        $this->assertContains($task, $actual);
    }

    public function testProcessTasksFromQueue(): void
    {
        ServiceMocks::$mailReturn = true;

        $app = new App([], [], [], '');
        $processed = $app->getTasksController()->processTasksFromQueue();

        $this->assertNotEmpty($processed);
    }

    public function testImportStreamToClickHouse(): void
    {
        $app = new App([], [], [], '');
        $processed = $app->getTasksController()->processTasksFromStream('consumer1');

        $this->assertNotEmpty($processed);
    }

    /**
     * @param scalar[] $params
     *
     * @return mixed[]
     */
    private function curl(string $method, string $url, string $schemaUrl, array $params, int $status): array
    {
        if ($schemaUrl !== '') {
            $this->validateRequest($method, $schemaUrl, (object) $params);
        }

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => 'http://nginx:8080' . $url,
            CURLOPT_POSTFIELDS => json_encode($params, JSON_FORCE_OBJECT),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: ' . $this->authorization],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_BUFFERSIZE => 8192,
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
        $this->assertStringContainsString('Content-Type: application/json', $response[0]);
        $this->assertStringContainsString('Strict-Transport-Security: max-age=31536000', $response[0]);
        $this->assertStringContainsString("Content-Security-Policy: default-src 'none'", $response[0]);

        if ($schemaUrl !== '') {
            $this->validateResponse($method, $schemaUrl, $status, json_decode($body ?: 'null', false, 20, 0));
        }

        return (array) json_decode($body ?: '""', true, 20, JSON_THROW_ON_ERROR);
    }

    private function validateResponse(string $method, string $url, int $status, mixed $response): void
    {
        // tasks_be/tests/data/api_openapi.php is generated from tasks_be/docs/api_openapi.json
        $data = require __DIR__ . '/../../data/api_openapi.php';

        $content = $data['paths'][$url][strtolower($method)]['responses'][$status]['content'] ?? null;
        if ($content === null) {
            throw new Exception('missing schema');
        }

        $schema = $content['application/json']['schema'] ?? [];
        $schema['components'] = $data['components'];

        $validator = new Validator();
        $validator->validate($response, $schema);

        $this->assertTrue($validator->isValid(), json_encode($validator->getErrors(), 0) ?: '');
    }

    private function validateRequest(string $method, string $url, object $request): void
    {
        // tasks_be/tests/data/api_openapi.php is generated from tasks_be/docs/api_openapi.json
        $data = require __DIR__ . '/../../data/api_openapi.php';

        $content = $data['paths'][$url][strtolower($method)] ?? null;
        if ($content === null) {
            throw new Exception('missing schema');
        }

        $schema = $content['requestBody']['content']['application/json']['schema'] ?? [];
        $schema['components'] = $data['components'];

        $validator = new Validator();
        $validator->validate($request, $schema);

        $this->assertTrue($validator->isValid(), json_encode($validator->getErrors(), 0) ?: '');
    }
}
