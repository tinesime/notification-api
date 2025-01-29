<?php

namespace Notification\Middleware;

use Notification\Core\Request;
use Notification\Core\Response;
use Notification\Utils\JwtUtils;

class JwtMiddleware
{
    public function handle(Request $request)
    {
        $headers = $request->getHeaders();
        if (!isset($headers['Authorization'])) {
            Response::json(['error' => 'No token provided'], 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'secret';
            $decoded = JwtUtils::validateToken($token, $secret);
            // Optionally store $decoded user data in a global context or static property
        } catch (\Exception $e) {
            Response::json(['error' => 'Invalid token'], 401);
        }
    }
}