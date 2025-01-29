#!/bin/bash

echo "Starting WebSocket server..."
php app/websocket/ws-server.php &

echo "Starting Notification Worker..."
php worker.php &

echo "Starting Scheduler..."
php scheduler.php &

echo "All processes started. Press Ctrl+C to stop."

wait
