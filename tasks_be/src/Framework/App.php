<?php

namespace TaskService\Framework;

use PDO;
use Redis;
use TaskService\Config\Config;
use TaskService\Controllers\CustomersController;
use TaskService\Controllers\MigrationsController;
use TaskService\Controllers\TasksController;
use TaskService\Repositories\CustomersRepository;
use TaskService\Repositories\MigrationsRepository;
use TaskService\Repositories\TasksRepository;
use TaskService\Routes\CliRoutes;
use TaskService\Routes\HttpPublicRoutes;
use TaskService\Routes\HttpRoutes;
use TaskService\Routes\Router;
use TaskService\Serializer\TasksSerializer;
use TaskService\Services\EmailService;
use TaskService\Services\RateLimitService;
use TaskService\Services\RedisService;
use TaskService\Services\TaskProcessingService;
use TaskService\Services\TemplateService;

/**
 * Application container, provides object initialization
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class App
{
    /** @var mixed[] */
    protected array $input;

    private ?PDO $database;

    private ?PDO $clickhouse;

    private ?Redis $redis;

    /**
     * @param mixed[] $get
     * @param mixed[] $post
     * @param mixed[] $server
     */
    public function __construct(protected array $get, protected array $post, protected array $server, string $input)
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->input = $input === '' ? [] : (array) json_decode(file_get_contents($input) ?: '', true, 10, 0);
    }

    public function getParam(string $key): string
    {
        $param = (string) ($this->input[$key] ?? $this->post[$key] ?? $this->get[$key] ?? '');

        return preg_replace('/^\s+|\s+$/u', '', $param) ?: '';
    }

    public function getHeader(string $key): string
    {
        /** @var string|string[] $result */
        $result = $this->server[$key] ?? '';

        return is_array($result) ? implode(' ', $result) : $result;
    }

    public function getConfig(): Config
    {
        return new Config();
    }

    public function getDatabase(): PDO
    {
        if (!isset($this->database)) {
            $config = $this->getConfig();

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;port=3306;charset=utf8mb4;', $config->mysqlHost, $config->mysqlDatabase
            );

            $this->database = new PDO($dsn, $config->mysqlUsername, $config->mysqlPassword, [PDO::ATTR_TIMEOUT => 3]);
        }

        return $this->database;
    }

    public function getClickHouse(): PDO
    {
        if (!isset($this->clickhouse)) {
            $config = $this->getConfig();

            $dsn = sprintf(
                'pgsql:host=%s;dbname=%s;port=9005;sslmode=disable', $config->clickhouseHost, $config->clickhouseDatabase
            );
            $options = [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_EMULATE_PREPARES => 1];

            $this->clickhouse = new PDO($dsn, $config->clickhouseUsername, $config->clickhousePassword, $options);
        }

        return $this->clickhouse;
    }

    public function getRedis(): Redis
    {
        if (!isset($this->redis)) {
            $config = $this->getConfig();

            $redis = new Redis();
            $redis->pconnect(
                $config->redisHost, $config->redisPort, 3, null, 100, 0,
                ['auth' => [$config->redisUsername, $config->redisPassword]]
            );

            $this->redis = $redis;
        }

        return $this->redis;
    }

    public function getMigrationsRepository(): MigrationsRepository
    {
        return new MigrationsRepository($this);
    }

    public function getRouter(): Router
    {
        return new Router();
    }

    public function getLogger(): Logger
    {
        return new Logger($this);
    }

    public function getHttpPublicRoutes(): HttpPublicRoutes
    {
        return new HttpPublicRoutes($this);
    }

    public function getHttpRoutes(): HttpRoutes
    {
        return new HttpRoutes($this);
    }

    public function getCliRoutes(): CliRoutes
    {
        return new CliRoutes($this);
    }

    public function getOutput(): Output
    {
        return new Output();
    }

    public function getAuthentication(): Authentication
    {
        return new Authentication();
    }

    public function getEmailService(): EmailService
    {
        return new EmailService();
    }

    public function getTemplateService(): TemplateService
    {
        return new TemplateService();
    }

    public function getTaskProcessingService(): TaskProcessingService
    {
        return new TaskProcessingService($this);
    }

    public function getRedisService(): RedisService
    {
        return new RedisService($this);
    }

    public function getRateLimitService(): RateLimitService
    {
        return new RateLimitService($this);
    }

    public function getTasksController(): TasksController
    {
        return new TasksController($this);
    }

    public function getTasksRepository(): TasksRepository
    {
        return new TasksRepository($this);
    }

    public function getTasksSerializer(): TasksSerializer
    {
        return new TasksSerializer();
    }

    public function getCustomersController(): CustomersController
    {
        return new CustomersController($this);
    }

    public function getCustomersRepository(): CustomersRepository
    {
        return new CustomersRepository($this);
    }

    public function getMigrationsController(): MigrationsController
    {
        return new MigrationsController($this);
    }
}
