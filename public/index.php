<?php

use Notification\Core\Route;
use Notification\Core\Request;
use Notification\Core\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

header('Content-Type: application/json');

Route::dispatch($uri, $method);
