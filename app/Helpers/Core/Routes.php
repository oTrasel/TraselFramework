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
     * Middleware to apply in a route
     * @var array
     */
    private static $currentMiddleware = [];

    /**
     * Register a GET route.
     *
     * @param string $endpoint
     * @param string $action
     * @param array $middleware
     */
    public static function get($endpoint, $action, $middleware = [])
    {
        $endpoint = self::applyPrefix($endpoint);
        $middleware = self::applyMiddlewares($middleware);
        self::$routes["GET"][$endpoint] = [
            'action' => $action,
            'middlewares' => $middleware
        ];
    }

    /**
     * Register a POST route.
     *
     * @param string $endpoint
     * @param string $action
     * @param array $middleware
     */
    public static function post($endpoint, $action, $middleware = [])
    {
        $endpoint = self::applyPrefix($endpoint);
        $middleware = self::applyMiddlewares($middleware);
        self::$routes["POST"][$endpoint] = [
            'action' => $action,
            'middlewares' => $middleware
        ];
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
                case "middleware":
                    self::$currentMiddleware = $val;
                    break;
            }
        }

        $callback();
        self::$currentMiddleware = [];
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
        if (!empty(self::$currentPrefix) && self::$currentPrefix !== null) {
            if (trim($endpoint) == "/") {
                $endpoint = self::$currentPrefix . $endpoint;
            } else {
                $endpoint = self::$currentPrefix . "/" . $endpoint;
            }
        }

        return $endpoint;
    }

    /**
     * Applies the group's middleware to the route's middleware.
     *
     *
     * @param array individual route middleware
     * @return array The array with middlewares
     */
    private static function applyMiddlewares($middlewares){
        if (count(self::$currentMiddleware) > 0 && self::$currentPrefix !== null) {
            $middlewares = array_merge(self::$currentMiddleware, $middlewares);
        }

        return $middlewares;
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

        if (!isset(self::$routes[$method][$endpoint]['action'])) {
            throw new Exception("Endpoint not found");
        }

        $action = explode('@', self::$routes[$method][$endpoint]['action']);

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
