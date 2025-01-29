<?php

namespace Notification\Core;

class Request
{
    public string $method {
        get {
            return $this->method;
        }
    }

    public string $uri {
        get {
            return $this->uri;
        }
    }

    private array $headers {
        get {
            return $this->headers;
        }
    }

    private array $body;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->headers = getallheaders();
        // For simplicity, handle JSON or form data
        $this->body = $_POST;

        if ($this->isJson()) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input)) {
                $this->body = array_merge($this->body, $input);
            }
        }
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    private function isJson(): bool
    {
        $contentType = $this->headers['Content-Type'] ?? '';
        return str_contains($contentType, 'application/json');
    }
}