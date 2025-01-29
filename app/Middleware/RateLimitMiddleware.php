<?php

namespace Notification\Middleware;

use Notification\Core\Request;
use Notification\Core\Response;
use Predis\Client as RedisClient;

class RateLimitMiddleware
{
    private int $limit;
    private int $ttl;

    public function __construct(int $limit = 100, int $ttl = 60)
    {
        $this->limit = $limit;
        $this->ttl = $ttl;
    }

    public function handle(Request $request): void
    {
        global $redisClient;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit:{$ip}";
        $count = $redisClient->incr($key);

        if ($count === 1) {
            $redisClient->expire($key, $this->ttl);
        }

        if ($count > $this->limit) {
            Response::json(['error' => 'Too Many Requests'], 429);
        }
    }
}