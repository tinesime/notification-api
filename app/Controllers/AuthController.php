<?php

namespace Notification\Controllers;

use Notification\Core\Request;
use Notification\Core\Response;
use Notification\Utils\JwtUtils;
use PDO;

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function register(Request $request): void
    {
        $email = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL);
        $password = $request->input('password');

        if (!$email || !$password) {
            Response::json(['error' => 'Invalid input'], 400);
        }

        $hashedPass = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
        if (!$stmt->execute([':email' => $email, ':password' => $hashedPass])) {
            Response::json(['error' => 'Registration failed'], 500);
        }

        Response::json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request): void
    {
        $email = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL);
        $password = $request->input('password');

        if (!$email || !$password) {
            Response::json(['error' => 'Invalid input'], 400);
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            Response::json(['error' => 'Invalid credentials'], 401);
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['email']
        ];
        $secret = $_ENV['JWT_SECRET'] ?? 'changeme';
        $token = JwtUtils::generateToken($payload, $secret);

        Response::json(['token' => $token], 200);
    }
}