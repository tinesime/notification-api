<?php

namespace Integration;

use Notification\Workers\NotificationWorker;
use PDO;
use Predis\Client as RedisClient;
use ReflectionClass;

class WorkerTest extends \PHPUnit\Framework\TestCase
{
    private PDO $pdo;
    private RedisClient $redis;
    private array $config;

    protected function setUp(): void
    {
        // Typically, you'd connect to a test DB
        $this->config = [
            'DB_HOST'    => '127.0.0.1',
            'DB_NAME'    => 'test_db',
            'DB_USER'    => 'root',
            'DB_PASS'    => 'password',
            'SMTP_HOST'  => 'mailhog',
            'SMTP_USER'  => 'example',
            'SMTP_PASS'  => 'example',
            'SMTP_PORT'  => 1025,
        ];
        $dsn = "mysql:host={$this->config['DB_HOST']};dbname={$this->config['DB_NAME']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $this->config['DB_USER'], $this->config['DB_PASS']);

        // Redis
        $this->redis = new RedisClient([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379
        ]);

        // Clear test tables and Redis
        $this->pdo->exec("TRUNCATE TABLE notifications");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE templates");
        $this->redis->flushall();
    }

    public function testNotificationWorkerSendsEmail()
    {
        $this->pdo->exec("INSERT INTO users (email, password) VALUES ('test@example.com','hash')");
        $userId = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO templates (name, type, subject, body)
                          VALUES ('TestTemplate','email','Test Subject','Test Body')");
        $templateId = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO notifications (user_id, template_id, status)
                          VALUES ($userId, $templateId, 'queued')");
        $notifId = $this->pdo->lastInsertId();

        $this->redis->lpush('notification_queue', (array)json_encode([
            'notification_id' => $notifId,
            'user_id' => $userId,
            'template_id' => $templateId
        ]));

        // Worker
        $worker = new NotificationWorker($this->pdo, $this->redis, $this->config);

        // Instead of an infinite loop, let's simulate one job
        $task = $this->redis->brpop('notification_queue', 1);
        if ($task) {
            $reflection = new ReflectionClass(get_class($worker));
            $method = $reflection->getMethod('processJob');
            $method->setAccessible(true);
            $method->invokeArgs($worker, [json_decode($task[1], true)]);
        }

        // Check status
        $stmt = $this->pdo->prepare("SELECT status FROM notifications WHERE id = :id");
        $stmt->execute([':id' => $notifId]);
        $status = $stmt->fetchColumn();

        $this->assertEquals('sent', $status);
    }
}