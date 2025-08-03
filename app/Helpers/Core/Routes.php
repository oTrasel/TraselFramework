<?php

namespace Helpers\Core;

use Exception;

/**
 * Class Routes
 *
 * Handles route registration and dispatching for HTTP requests.
 *
 * @package Helpers
 */
class Routes
{
    /**
     * Registered routes.
     * @var array
     */
    private static $routes = [
        "GET" => [],
        "POST" => [],
    ];

    /**
     * Prefix to be incorporated into the route
     * @var string
     */
    private static $currentPrefix = '';

    /**
     * Register a GET route.
     *
     * @param string $endpoint
     * @param string $action
     */
    public static function get($endpoint, $action)
    {
        $endpoint = self::applyPrefix($endpoint);
        self::$routes["GET"][$endpoint] = $action;
    }

    /**
     * Register a POST route.
     *
     * @param string $endpoint
     * @param string $action
     */
    public static function post($endpoint, $action)
    {
        $endpoint = self::applyPrefix($endpoint);
        self::$routes["POST"][$endpoint] = $action;
    }

    /**
     * Group multiple routes under a common configuration.
     *
     * Allows grouping routes with shared attributes such as prefix or middleware.
     * The provided callback will be executed within the group context.
     *
     * @param array $options Array of group options (e.g., ['prefix' => 'admin']).
     * @param callable $callback Callback function to register routes within the group.
     * @return void
     */
    public static function group(array $options, callable $callback)
    {
        foreach ($options as $opt => $val) {
            switch ($opt) {
                case "prefix":
                    self::$currentPrefix = $val;
                    break;
            }
        }

        $callback();

        self::$currentPrefix = null;
    }

    /**
     * Apply the current group prefix to the given endpoint.
     *
     * Ensures the route endpoint includes the active group prefix if one is set.
     *
     * @param string $endpoint The original route endpoint.
     * @return string The endpoint with the applied prefix.
     */
    private static function applyPrefix(string $endpoint)
    {
        if (!empty(self::$currentPrefix && self::$currentPrefix !== null)) {
            if (trim($endpoint) == "/") {
                $endpoint = self::$currentPrefix . $endpoint;
            } else {
                $endpoint = self::$currentPrefix . "/" . $endpoint;
            }
        }

        return $endpoint;
    }


    /**
     * Dispatch the request to the appropriate controller and handler.
     *
     * @param array $request
     * @throws Exception
     */
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
