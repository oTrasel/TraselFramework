<?php

require_once (__DIR__ . '/../vendor/autoload.php');

use Helpers\Routes;

/**
 * Class Index
 *
 * Application entry point. Handles route loading, request parsing, and dispatching.
 */
class Index
{
    /**
     * Index constructor.
     *
     * Loads web and API routes.
     */
    public function __construct()
    {
        require 'Routes/web.php';
        require 'Routes/api.php';
    }

    /**
     * Set the request URI endpoint.
     */
    private function setUri()
    {
        $_REQUEST["endpoint"] = str_replace("/TraselFramework/app", "", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (empty($_REQUEST["endpoint"])) {
            $_REQUEST["endpoint"] = "/";
        }
    }

    /**
     * Ensure the HTTP method is set in the request.
     */
    private function verifyMethod()
    {
        if (!isset($_REQUEST["method"]) || empty($_REQUEST["method"])) {
            $_REQUEST["method"] = "GET";
        }
    }

    /**
     * Ensure the handler is set in the request.
     */
    private function verifyHandler()
    {
        if (!isset($_REQUEST["handler"]) || empty($_REQUEST["handler"])) {
            $_REQUEST["handler"] = "index";
        }
    }

    /**
     * Dispatch the request to the router.
     */
    public function dispatch()
    {
        $this->setUri();
        $this->verifyMethod();
        $this->verifyHandler();

        try {
            Routes::dispatch($_REQUEST);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$index = new Index();

$index->dispatch();

?>