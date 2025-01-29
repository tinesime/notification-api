<?php

namespace Notification\Workers;

use PDO;
use Predis\Client as RedisClient;

class Scheduler
{
    private PDO $db;
    private RedisClient $redis;

    public function __construct(PDO $db, RedisClient $redis)
    {
        $this->db = $db;
        $this->redis = $redis;
    }

    public function run()
    {
        echo "[Scheduler] Checking for scheduled notifications...\n";
        while (true) {
            $this->checkScheduled();
            sleep(60);
        }
    }

    private function checkScheduled(): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications
             WHERE status = 'queued'
               AND scheduled_for IS NOT NULL
               AND scheduled_for <= NOW()"
        );

        $stmt->execute();
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($notifications as $notif) {
            $this->redis->lpush('notification_queue', (array)json_encode([
                'notification_id' => $notif['id'],
                'user_id' => $notif['user_id'],
                'template_id' => $notif['template_id']
            ]));
        }
    }

}