<?php

namespace Notification\Core;

use Notification\Middleware\RateLimitMiddleware;
use Notification\Middleware\JwtMiddleware;
use Notification\Core\Request;
use Notification\Core\Response;

class Route
{
    protected static array $routes = [];
    protected static array $currentGroupMiddleware = [];

    public static function get(string $uri, callable|array $action, array $middleware = []): void
    {
        self::$routes['GET'][] = [
            'uri' => $uri,
            'action' => $action,
            'middleware' => array_merge(self::$currentGroupMiddleware, $middleware)
        ];
    }

    public static function post(string $uri, callable|array $action, array $middleware = []): void
    {
        self::$routes['POST'][] = [
            'uri' => $uri,
            'action' => $action,
            'middleware' => array_merge(self::$currentGroupMiddleware, $middleware)
        ];
    }

    public static function group(array $attributes, callable $callback): void
    {
        $originalMiddleware = self::$currentGroupMiddleware;

        // If there's a 'middleware' key, add it to the stack
        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware'])
                ? $attributes['middleware']
                : [$attributes['middleware']];

            self::$currentGroupMiddleware = array_merge(self::$currentGroupMiddleware, $middleware);
        }

        // If there's a prefix, you could also store it in a static property and prepend it to route URIs
        // For brevity, we'll omit prefix logic here.

        $callback();

        // Restore original middleware stack
        self::$currentGroupMiddleware = $originalMiddleware;
    }

    public static function dispatch(Request $request): void
    {
        $method = $request->method;
        $uri = $request->uri;

        if (!isset(self::$routes[$method])) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        // Find matching route
        foreach (self::$routes[$method] as $route) {
            if ($route['uri'] === $uri) {
                // Process middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middlewareObj = new $middlewareClass();
                    $middlewareObj->handle($request);
                }

                // Execute route action
                if (is_array($route['action'])) {
                    // [ControllerClass, 'methodName']
                    [$controllerClass, $methodName] = $route['action'];
                    $controller = new $controllerClass();
                    $controller->$methodName($request);
                } elseif (is_callable($route['action'])) {
                    call_user_func($route['action'], $request);
                }

                return;
            }
        }

        Response::json(['error' => 'Endpoint not found'], 404);
    }
}