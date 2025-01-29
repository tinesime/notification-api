<?php

namespace Notification\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

require __DIR__ . '/../../vendor/autoload.php';

class NotificationWebSocket implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo "[WebSocket] Connection opened: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Optionally process incoming messages
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        echo "[WebSocket] Connection closed: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "[WebSocket] Error: {$e->getMessage()}\n";
        $conn->close();
    }

    // You could broadcast a message to all connected clients:
    public function broadcast(array $data)
    {
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }

}