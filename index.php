<?php

require_once (__DIR__ . '/vendor/autoload.php');

use App\FrontController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$FrontController = new FrontController();

$FrontController->dispatch();
