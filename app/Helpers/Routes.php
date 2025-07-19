<?php

namespace Helpers;

use Exception;

class Routes
{
    private static $routes = [
        "GET" => [],
        "POST" => [],
    ];

    public static function get($endpoint, $action)
    {
        self::$routes["GET"][$endpoint] = $action;
    }

    public static function post($endpoint, $action)
    {
        self::$routes["POST"][$endpoint] = $action;
    }

    public static function dispatch($request)
    {
        $method = $request['method'];
        $endpoint = $request['endpoint'];
        $handler = $request['handler'];

        if (!isset(self::$routes[$method][$endpoint])) {
            throw new Exception("Endpoint not found");
        }

        $action = explode('@', self::$routes[$method][$endpoint]);

        if ($action[1] !== $handler) {
            throw new Exception("Handler not found");
        }

        $class = "Controllers\\" . $action[0];

        if (!class_exists($class)) {
            throw new Exception("Controller class not found");
        }

        $controller = new $class;

        if (!method_exists($controller, $handler)) {
            throw new Exception("Method $handler not found in class $class");
        }

        $controller->$handler();
    }
}
