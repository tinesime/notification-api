<?php

namespace Notification\Services;

use Predis\Client;

class RedisService
{
    private Client $client {
        get {
            return $this->client;
        }
    }

    public function __construct($host = 'redis', $port = 6379)
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port
        ]);
    }

}