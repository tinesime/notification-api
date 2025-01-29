<?php

return [
    'DB_HOST'     => getenv('DB_HOST') ?: 'localhost',
    'DB_NAME'     => getenv('DB_NAME') ?: 'notification_system',
    'DB_USER'     => getenv('DB_USER') ?: 'root',
    'DB_PASS'     => getenv('DB_PASS') ?: '',
    'DB_CHARSET' => 'utf8mb4',

    'REDIS_HOST' => getenv('REDIS_HOST')  ?: '127.0.0.1',
    'REDIS_PORT' => getenv('REDIS_PORT')  ?: '6379',

    'JWT_SECRET' => getenv('JWT_SECRET')  ?: 'secret',
    'JWT_ISSUER' => getenv('JWT_ISSUER')  ?: 'local.com',

    'SMTP_HOST'  => getenv('SMTP_HOST')   ?: 'smtp.example.com',
    'SMTP_USER'  => getenv('SMTP_USER')   ?: 'user',
    'SMTP_PASS'  => getenv('SMTP_PASS')   ?: 'pass',
    'SMTP_PORT'  => getenv('SMTP_PORT')   ?: 587,

    'RATE_LIMIT' => getenv('RATE_LIMIT')  ?: 100,
    'RATE_TTL'   => getenv('RATE_TTL')    ?: 60,
];
