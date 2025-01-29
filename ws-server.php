<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Notification\WebSocket\NotificationWebSocket;

require_once __DIR__ . '/vendor/autoload.php';

$webSocket = new NotificationWebSocket();

$server = IoServer::factory(
    new HttpServer(
        new WsServer($webSocket)
    ),
    8080
);

echo "[WebSocket] Server running on port 8080...\n";
$server->run();

