<?php

declare(strict_types=1);

namespace TaskService\Tests\Unit\Framework;

use Exception;
use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use TaskService\Config\Config;
use TaskService\Framework\Authentication;
use TaskService\Models\Customer;

final class AuthenticationTest extends TestCase
{
    public function testPKeyBitLength(): void
    {
        $config = new Config();

        $privateKey = openssl_pkey_get_private($config->privateKey, null);
        $this->assertInstanceOf(OpenSSLAsymmetricKey::class, $privateKey);

        $publicKey = openssl_pkey_get_public($config->publicKey);
        $this->assertInstanceOf(OpenSSLAsymmetricKey::class, $publicKey);

        $this->assertSame(4096, openssl_pkey_get_details($privateKey)['bits'] ?? 0);
        $this->assertSame(4096, openssl_pkey_get_details($publicKey)['bits'] ?? 0);
    }

    public function testGetToken(): void
    {
        $config = new Config();
        $customer = new Customer(42, 'foo.bar@invalid.local');

        $authentication = new Authentication();
        $token = $authentication->getToken($customer, $config->privateKey);

        $tokens = explode('.', substr($token, 7), 3);
        $header = $tokens[0];
        $payload = $tokens[1] ?? '';

        $actual = (array) json_decode(base64_decode(strtr($payload, '-_', '+/'), true) ?: '', true, 10, 0);

        $this->assertNotEmpty($actual);
        $this->assertLessThan(time() + 23 * 7200, $actual['exp'] ?? 0);
        $this->assertSame('42', $actual['sub'] ?? '');

        $actual = (array) json_decode(base64_decode(strtr($header, '-_', '+/'), true) ?: '', true, 10, 0);

        $this->assertNotEmpty($actual);
        $this->assertSame('JWT', $actual['typ'] ?? '');
        $this->assertSame('RS512', $actual['alg'] ?? '');
    }

    public function testGetCustomer(): void
    {
        $config = new Config();
        $authentication = new Authentication();
        $customer = new Customer(42, 'foo.bar@invalid.local');

        $token = $authentication->getToken($customer, $config->privateKey);

        $actual = $authentication->getCustomer($token, $config->publicKey);

        $this->assertNotNull($actual);
        $this->assertEquals($customer, $actual);
    }

    public function testGetCustomerInvalidToken(): void
    {
        $config = new Config();
        $authentication = new Authentication();
        $customer = new Customer(0, 'foo.bar@invalid.local');

        $actual = $authentication->getCustomer('foo', $config->publicKey);
        $this->assertNull($actual);

        $actual = $authentication->getCustomer('Bearer foo.bar.baz', $config->publicKey);
        $this->assertNull($actual);

        $token = $authentication->getToken($customer, $config->privateKey);

        $actual = $authentication->getCustomer($token, $config->publicKey);
        $this->assertNull($actual);
    }

    /** @SuppressWarnings(PHPMD.ErrorControlOperator) */
    public function testGetCustomerInvalidKey(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('signing failed');

        $customer = new Customer(42, 'foo.bar@invalid.local');

        $authentication = new Authentication();
        @$authentication->getToken($customer, '');
    }
}
