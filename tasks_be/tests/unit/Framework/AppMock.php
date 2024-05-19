<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Framework;

use PHPUnit\Framework\MockObject\MockObject;
use Redis;
use TaskService\Config\Config;
use TaskService\Controllers\CustomersController;
use TaskService\Controllers\MigrationsController;
use TaskService\Controllers\TasksController;
use TaskService\Framework\App;
use TaskService\Framework\Authentication;
use TaskService\Framework\Logger;
use TaskService\Framework\Output;
use TaskService\Repositories\CustomersRepository;
use TaskService\Repositories\MigrationsRepository;
use TaskService\Repositories\TasksRepository;
use TaskService\Serializer\TasksSerializer;
use TaskService\Services\EmailService;
use TaskService\Services\RateLimitService;
use TaskService\Services\RedisService;
use TaskService\Services\TaskProcessingService;
use TaskService\Services\TemplateService;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class AppMock extends App
{
    private Output&MockObject $output;

    private Logger&MockObject $logger;

    private Authentication&MockObject $authentication;

    private Config&MockObject $config;

    private Redis&MockObject $redis;

    private TasksController&MockObject $tasksController;

    private CustomersController&MockObject $customersController;

    private TasksRepository&MockObject $tasks;

    private CustomersRepository&MockObject $customers;

    private MigrationsRepository&MockObject $migrations;

    private EmailService&MockObject $emailsService;

    private TemplateService&MockObject $templateService;

    private RedisService&MockObject $redisService;

    private RateLimitService&MockObject $rateLimitService;

    private TaskProcessingService&MockObject $taskProcessingService;

    private MigrationsController&MockObject $migrationsController;

    private TasksSerializer&MockObject $tasksSerializer;

    /**
     * @param string[]|string[][] $server
     * @param string[] $input
     */
    public function __construct(callable $createMock, array $server, array $input)
    {
        $this->server = $server;
        $this->input = $input;

        $this->output = $createMock(Output::class);
        $this->logger = $createMock(Logger::class);
        $this->authentication = $createMock(Authentication::class);
        $this->config = $createMock(Config::class);
        $this->redis = $createMock(Redis::class);
        $this->tasksController = $createMock(TasksController::class);
        $this->customersController = $createMock(CustomersController::class);
        $this->tasks = $createMock(TasksRepository::class);
        $this->customers = $createMock(CustomersRepository::class);
        $this->migrations = $createMock(MigrationsRepository::class);
        $this->emailsService = $createMock(EmailService::class);
        $this->templateService = $createMock(TemplateService::class);
        $this->redisService = $createMock(RedisService::class);
        $this->rateLimitService = $createMock(RateLimitService::class);
        $this->taskProcessingService = $createMock(TaskProcessingService::class);
        $this->migrationsController = $createMock(MigrationsController::class);
        $this->tasksSerializer = $createMock(TasksSerializer::class);
    }

    public function getOutput(): Output&MockObject
    {
        return $this->output;
    }

    public function getLogger(): Logger&MockObject
    {
        return $this->logger;
    }

    public function getAuthentication(): Authentication&MockObject
    {
        return $this->authentication;
    }

    public function getConfig(): Config&MockObject
    {
        return $this->config;
    }

    public function getRedis(): Redis&MockObject
    {
        return $this->redis;
    }

    public function getTasksController(): TasksController&MockObject
    {
        return $this->tasksController;
    }

    public function getTasksRepository(): TasksRepository&MockObject
    {
        return $this->tasks;
    }

    public function getCustomersController(): CustomersController&MockObject
    {
        return $this->customersController;
    }

    public function getCustomersRepository(): CustomersRepository&MockObject
    {
        return $this->customers;
    }

    public function getMigrationsRepository(): MigrationsRepository&MockObject
    {
        return $this->migrations;
    }

    public function getEmailService(): EmailService&MockObject
    {
        return $this->emailsService;
    }

    public function getTemplateService(): TemplateService&MockObject
    {
        return $this->templateService;
    }

    public function getRedisService(): RedisService&MockObject
    {
        return $this->redisService;
    }

    public function getRateLimitService(): RateLimitService&MockObject
    {
        return $this->rateLimitService;
    }

    public function getTaskProcessingService(): TaskProcessingService&MockObject
    {
        return $this->taskProcessingService;
    }

    public function getMigrationsController(): MigrationsController&MockObject
    {
        return $this->migrationsController;
    }

    public function getTasksSerializer(): TasksSerializer&MockObject
    {
        return $this->tasksSerializer;
    }
}
