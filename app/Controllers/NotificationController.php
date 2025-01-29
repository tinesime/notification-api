<?php

namespace Notification\Controllers;

use Notification\Core\Request;
use Notification\Core\Response;
use PDO;

class NotificationController
{
    private PDO $db;
    private \Predis\Client $redis;

    public function __construct()
    {
        global $pdo, $redisClient;
        $this->db = $pdo;
        $this->redis = $redisClient;
    }

    public function createNotification(Request $request): void
    {
        $userId = $request->input('user_id');
        $templateId = $request->input('template_id');
        $scheduledFor = $request->input('scheduled_for'); // e.g. '2025-01-01 10:00:00'

        if (!$userId || !$templateId) {
            Response::json(['error' => 'Missing user_id or template_id'], 400);
        }

        // Insert into notifications
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, template_id, status, scheduled_for) 
                                    VALUES (:uid, :tid, 'queued', :sched)");
        $success = $stmt->execute([
            ':uid' => $userId,
            ':tid' => $templateId,
            ':sched' => $scheduledFor
        ]);

        if (!$success) {
            Response::json(['error' => 'Failed to create notification'], 500);
        }

        $notifId = $this->db->lastInsertId();

        // If scheduled_for is null, we assume immediate dispatch:
        if (empty($scheduledFor)) {
            $this->redis->lpush('notification_queue', (array)json_encode([
                'notification_id' => $notifId,
                'user_id' => $userId,
                'template_id' => $templateId
            ]));
        }

        Response::json(['message' => 'Notification created', 'id' => $notifId], 201);
    }

}