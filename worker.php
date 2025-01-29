<?php

require_once __DIR__ . '/vendor/autoload.php';

use Notification\Container;
use Notification\Database\Database;
use Notification\Services\RedisService;
use Notification\Workers\NotificationWorker;

$config = require __DIR__ . '/app/config/config.php';

$container = new Container();

$container->bind('config', function() use ($config) {
    return $config;
});

$container->bind('db', function($c) {
    $config = $c->get('config');
    $dbInstance = Database::getInstance($config);
    return $dbInstance->getConnection();
});

$container->bind('redis', function($c) {
    $config = $c->get('config');
    return new RedisService($config['REDIS_HOST'], $config['REDIS_PORT']);
});

$container->bind('notificationWorker', function($c) {
    return new NotificationWorker(
        $c->get('db'),
        $c->get('redis')->getClient(),
        $c->get('config')
    );
}, true);

try {
    $worker = $container->get('notificationWorker');
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}

$worker->run();
