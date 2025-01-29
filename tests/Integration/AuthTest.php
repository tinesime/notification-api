<?php

namespace Integration;

use Notification\Utils\JwtUtils;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testGenerateAndValidateToken()
    {
        $payload = ['sub' => 123, 'email' => 'test@example.com'];
        $secret = 'test';

        $token = JwtUtils::generateToken($payload, $secret, 3600);
        $decoded = JwtUtils::validateToken($token, $secret);

        $this->assertEquals($payload['sub'], $decoded->sub);
        $this->assertEquals($payload['email'], $decoded->email);
    }
}