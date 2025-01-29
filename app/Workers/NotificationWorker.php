<?php

namespace Notification\Workers;

use PDO;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Predis\Client as RedisClient;

class NotificationWorker
{
    private PDO $db;
    private RedisClient $redis;
    private array $config;
    private PHPMailer $mailer;

    public function __construct(PDO $db, RedisClient $redis, array $config)
    {
        $this->db = $db;
        $this->redis = $redis;
        $this->config = $config;

        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = $this->config['SMTP_HOST'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $this->config['SMTP_USER'];
        $this->mailer->Password   = $this->config['SMTP_PASS'];
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port       = $this->config['SMTP_PORT'];
        try {
            $this->mailer->setFrom('no-reply@yourdomain.com', 'Notification System');
        } catch (Exception $e) {
            http_response_code(500);
            exit();
        }
    }

    public function run()
    {
        echo "[Worker] Listening for jobs...\n";

        while (true) {
            $task = $this->redis->brpop('notification_queue', 0);
            if ($task) {
                $this->processJob(json_decode($task[1], true));
            }
        }
    }

    private function processJob(array $payload): void
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE id = :id");
        $stmt->execute([':id' => $payload['notification_id']]);
        $notification = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$notification) {
            echo "[Worker] Notification not found.\n";
            return;
        }

        $tmplStmt = $this->db->prepare("SELECT * FROM templates WHERE id = :tid");
        $tmplStmt->execute([':tid' => $notification['template_id']]);
        $template = $tmplStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$template) {
            echo "[Worker] Template not found.\n";
            return;
        }

        switch ($template['type']) {
            case 'email':
                $this->sendEmail($notification, $template);
                break;
            case 'sms':
                $this->sendSms($notification, $template);
                break;
            case 'in-app':
                $this->sendInApp($notification, $template);
                break;
        }
    }

    private function sendEmail(array $notification, array $template): void
    {
        $userStmt = $this->db->prepare("SELECT * FROM users WHERE id = :uid");
        $userStmt->execute([':uid' => $notification['user_id']]);
        $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo "[Worker] User not found.\n";
            return;
        }

        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($user['email']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $template['subject'] ?? 'No Subject';
            $this->mailer->Body    = $template['body'] ?? 'No Content';

            $this->mailer->send();

            $this->updateStatus($notification['id'], 'sent');
            echo "[Worker] Email sent to {$user['email']}\n";
        } catch (MailException $e) {
            $this->updateStatus($notification['id'], 'failed');
            echo "[Worker] Email failed: " . $e->getMessage() . "\n";
        }
    }

    private function sendSms(array $notification, array $template): void
    {
        $this->updateStatus($notification['id'], 'sent');
        echo "[Worker] SMS sent (simulated)\n";
    }

    private function sendInApp(array $notification, array $template)
    {
        // For in-app, you might store in a user_inbox table or broadcast via websockets
        $this->updateStatus($notification['id'], 'sent');
        echo "[Worker] In-app notification set to 'sent'.\n";
    }

    private function updateStatus(int $notifId, string $status)
    {
        $stmt = $this->db->prepare("UPDATE notifications SET status = :st, sent_at = NOW() WHERE id = :nid");
        $stmt->execute([':st' => $status, ':nid' => $notifId]);
    }

}