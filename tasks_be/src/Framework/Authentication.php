<?php

namespace TaskService\Framework;

use Exception;
use SensitiveParameter;
use TaskService\Models\Customer;

class Authentication
{
    public function getCustomer(#[SensitiveParameter] string $token, string $publicKey): ?Customer
    {
        if (substr_count($token, '.') !== 2) {
            return null;
        }

        $tokens = explode('.', substr($token, 7), 3);
        $header = $tokens[0];
        $payload = $tokens[1] ?? '';
        $signature = $tokens[2] ?? '';

        $data = $header . '.' . $payload;

        if (openssl_verify($data, $this->urlBase64Decode($signature), $publicKey, OPENSSL_ALGO_SHA512) !== 1) {
            return null;
        }

        $payload = (array) json_decode($this->urlBase64Decode($payload), true, 10, 0);

        $expiry = (int) ($payload['exp'] ?? 0);
        $subject = (int) ($payload['sub'] ?? 0);
        $email = (string) ($payload['email'] ?? '');

        if ($expiry < time() || $subject < 1 || $email === '') {
            return null;
        }

        return new Customer($subject, $email, '');
    }

    public function getToken(Customer $customer, #[SensitiveParameter] string $privateKey): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'RS512'], 0) ?: '';

        $payload = json_encode([
            'exp' => strtotime('+1 day'),
            'sub' => (string) $customer->id,
            'email' => $customer->email,
        ], 0) ?: '';
        $data = $this->urlBase64Encode($header) . '.' . $this->urlBase64Encode($payload);

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA512)) {
            throw new Exception('signing failed');
        }

        return 'Bearer ' . $data . '.' . $this->urlBase64Encode($signature);
    }

    private function urlBase64Decode(string $string): string
    {
        return base64_decode(strtr($string, '-_', '+/'), true) ?: '';
    }

    private function urlBase64Encode(string $string): string
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}
