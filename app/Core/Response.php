<?php

namespace Notification\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function text(string $message, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/plain');
        echo $message;
        exit;
    }
}