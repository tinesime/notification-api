<?php

require_once "vendor/autoload.php";

use Notification\Database\Database;
use Notification\Database\Migrator;

$config = require __DIR__ . '/app/config/config.php';

$migrationsPath = __DIR__ . '/app/database/migrations';

$dbInstance = Database::getInstance($config);
$pdo = $dbInstance->getConnection();

$migrator = new Migrator($pdo, $migrationsPath);
$migrator->migrate();

echo "[Migrate] All done.\n";