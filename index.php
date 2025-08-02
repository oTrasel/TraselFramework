<?php

use Helpers\Core\FrontController;

require_once (__DIR__ . '/vendor/autoload.php');

const APP_DIR = __DIR__ . '/app/';
const REQUEST_REPLACE = "/TraselFramework/";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$FrontController = new FrontController();

$FrontController->dispatch();
