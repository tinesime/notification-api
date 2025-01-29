<?php

namespace Notification\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtUtils
{
    public static function generateToken(array $payload, string $secret, int $expireTime = 3600): string
    {
        $issuedAt = time();
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $issuedAt + $expireTime;

        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function validateToken(string $token, string $secret)
    {
        return JWT::decode($token, new Key($secret, 'HS256'));
    }
}