<?php

global $pdo, $redisClient;

require_once __DIR__ . '/vendor/autoload.php';

use Notification\Workers\Scheduler;

$scheduler = new Scheduler($pdo, $redisClient);
$scheduler->run();
