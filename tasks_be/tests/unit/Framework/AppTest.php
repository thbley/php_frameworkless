<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Framework;

use PHPUnit\Framework\TestCase;
use TaskService\Framework\App;

final class AppTest extends TestCase
{
    public function testGetParam(): void
    {
        $get = ['param' => 'value', 'param2' => 'ignored'];
        $post = ['param2' => 'value2', 'param3' => 'ignored'];

        file_put_contents('/tmp/test.json', json_encode(['param3' => 'value3'], 0));

        $app = new App($get, $post, [], '/tmp/test.json');

        $this->assertSame('value', $app->getParam('param'));
        $this->assertSame('value2', $app->getParam('param2'));
        $this->assertSame('value3', $app->getParam('param3'));
        $this->assertSame('', $app->getParam('invalid'));

        $app = new App([], [], [], '/tmp/test.json');
        $this->assertSame('value3', $app->getParam('param3'));

        $app = new App(['param' => ' value '], ['param2' => ' value2 '], [], '');
        $this->assertSame('value', $app->getParam('param'));
        $this->assertSame('value2', $app->getParam('param2'));
    }

    public function testGetHeader(): void
    {
        $app = new App([], [], ['header' => 'value'], '');
        $this->assertSame('value', $app->getHeader('header'));
        $this->assertSame('', $app->getHeader('invalid'));

        $app = new App([], [], ['argv' => ['test.php', 'param', 'value']], '');
        $this->assertSame('test.php param value', $app->getHeader('argv'));
    }

    public function testGetRouter(): void
    {
        $app = new App([], [], [], '');
        $app->getRouter();

        $this->expectNotToPerformAssertions();
    }

    public function testGetLogger(): void
    {
        $app = new App([], [], [], '');
        $app->getLogger();

        $this->expectNotToPerformAssertions();
    }

    public function testGetRoutes(): void
    {
        $app = new App([], [], [], '');
        $app->getHttpPublicRoutes();
        $app->getHttpRoutes();
        $app->getCliRoutes();

        $this->expectNotToPerformAssertions();
    }

    public function testGetOutput(): void
    {
        $app = new App([], [], [], '');
        $app->getOutput();

        $this->expectNotToPerformAssertions();
    }

    public function testGetAuthentication(): void
    {
        $app = new App([], [], [], '');
        $app->getAuthentication();

        $this->expectNotToPerformAssertions();
    }

    public function testGetConfig(): void
    {
        $app = new App([], [], [], '');
        $app->getConfig();

        $this->expectNotToPerformAssertions();
    }

    public function testGetService(): void
    {
        $app = new App([], [], [], '');
        $app->getEmailService();
        $app->getTemplateService();
        $app->getTaskProcessingService();
        $app->getRedisService();
        $app->getRateLimitService();

        $this->expectNotToPerformAssertions();
    }

    public function testGetController(): void
    {
        $app = new App([], [], [], '');
        $app->getMigrationsController();
        $app->getTasksController();
        $app->getCustomersController();

        $this->expectNotToPerformAssertions();
    }

    public function testGetRepository(): void
    {
        $app = new App([], [], [], '');
        $app->getMigrationsRepository();
        $app->getTasksRepository();
        $app->getCustomersRepository();

        $this->expectNotToPerformAssertions();
    }

    public function testGetSerializer(): void
    {
        $app = new App([], [], [], '');
        $app->getTasksSerializer();

        $this->expectNotToPerformAssertions();
    }
}
