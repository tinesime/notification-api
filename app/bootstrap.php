<?php

use Notification\Services\RedisService;

$config = require __DIR__ . '/config/config.php';

$dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4";
$pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS']);

$redisService = new RedisService($config['REDIS_HOST'] ?? 'redis', 6379);
$redisClient = $redisService->getClient();

global $config, $pdo, $redisClient;

require_once __DIR__ . '/routes/api.php';

