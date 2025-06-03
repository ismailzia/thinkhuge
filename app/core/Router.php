<?php
namespace App\core;

class Router
{
    private $routes = [];

    private static $middlewareAliases = [
        'auth' => 'Auth',
        'api_auth' => 'ApiAuth',
        'csrf' => 'VerifyCsrfToken',
        'rate_limiter' => 'RateLimiter',
        'api_rate_limiter' => 'ApiRateLimiter',
    ];

    /**
     * Initialize router by loading routes from given files with optional prefix.
     *
     * @param array $routeFiles Array of route file paths or [file, prefix] pairs.
     */
    public function __construct($routeFiles = [])
    {
        foreach ($routeFiles as $fileConfig) {
            if (is_array($fileConfig)) {
                [$file, $prefix] = $fileConfig;
            } else {
                $file = $fileConfig;
                $prefix = '';
            }

            if (file_exists($file)) {
                $routes = require $file;
                if ($prefix) {
                    // Prepend prefix to each route URI
                    foreach ($routes as &$route) {
                        $route[1] = $prefix . $route[1];
                    }
                }
                $this->routes = array_merge($this->routes, $routes);
            }
        }
    }

    // Helper to define GET route
    public static function get($uri, $controller, $action, $middleware = [])
    {
        return ['GET', $uri, $controller, $action, (array) $middleware];
    }

    // Helper to define POST route
    public static function post($uri, $controller, $action, $middleware = [])
    {
        return ['POST', $uri, $controller, $action, (array) $middleware];
    }

    /**
     * Define a group of routes with shared attributes like middleware.
     *
     * @param array $attributes Attributes like ['middleware' => ...]
     * @param callable $routesCallback Callback that registers routes with given middleware.
     * @return array Registered routes
     */
    public static function group(array $attributes, callable $routesCallback)
    {
        $routes = [];
        $middlewares = $attributes['middleware'] ?? [];
        if (!is_array($middlewares))
            $middlewares = [$middlewares];
        $routesCallback($middlewares, $routes);
        return $routes;
    }

    /**
     * Dispatch current request to matching route controller/action.
     * Runs middleware before controller call.
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = strtok($_SERVER['REQUEST_URI'], '?');
        $requestUri = $this->normalizeUri($requestUri);

        foreach ($this->routes as $route) {
            [$method, $pattern, $controller, $action, $middleware] = array_pad($route, 5, []);

            // Convert route pattern (e.g. /client/{id}) to regex
            $regex = $pattern;
            $regex = preg_replace('#\{id\}#', '([0-9]+)', $regex);
            $regex = preg_replace('#\{[a-zA-Z_]+\}#', '([0-9a-zA-Z_-]+)', $regex);
            $regex = '#^' . $regex . '$#';

            if (
                strtoupper($method) === $requestMethod &&
                preg_match($regex, $requestUri, $matches)
            ) {
                array_shift($matches); // Remove full match

                // Run middleware, abort if any fails
                foreach ((array) $middleware as $mw) {
                    // If it's a two-element array and the first element is a string, treat it as a middleware with options
                    if (is_array($mw) && isset($mw[0]) && is_string($mw[0])) {
                        if (!$this->runMiddleware($mw)) {
                            http_response_code(403);
                            echo "Forbidden (middleware: " . (is_array($mw) ? $mw[0] : $mw) . ")";
                            return;
                        }
                    }
                    // If it's a simple string middleware
                    elseif (is_string($mw)) {
                        if (!$this->runMiddleware($mw)) {
                            http_response_code(403);
                            echo "Forbidden (middleware: $mw)";
                            return;
                        }
                    }
                    // Defensive: If it's something else (bad config)
                    else {
                        error_log("Invalid middleware config: " . print_r($mw, true));
                        http_response_code(403);
                        echo "Forbidden (middleware: invalid config)";
                        return;
                    }
                }


                $controllerClass = $this->resolveControllerClass($controller);

                if (class_exists($controllerClass)) {
                    $controllerObj = new $controllerClass();
                    if (method_exists($controllerObj, $action)) {
                        $request = new Request();
                        $params = array_merge([$request], $matches);
                        return call_user_func_array([$controllerObj, $action], $params);
                    }
                }

                http_response_code(500);
                http_response_code(500);
                echo "Controller or action not found!<br>";
                echo "Requested URI: <b>$requestUri</b><br>";
                echo "Expected controller: <b>$controllerClass</b><br>";
                echo "Expected action: <b>$action</b><br>";
                echo "Route pattern: <b>$pattern</b><br>";
                return;
            }
        }

        http_response_code(404);
        render('404', ['pageTitle' => 'Page Not Found'], null);
    }

    /**
     * Run middleware by alias or class name.
     * Middleware classes must implement static handle($options).
     *
     * @param string|array $mw Middleware alias or [alias, options]
     * @return bool True if middleware passes, false otherwise
     */
    private function runMiddleware($mw)
    {
        // Debug print
        error_log("runMiddleware called with: " . print_r($mw, true));

        if (is_array($mw)) {
            if (empty($mw)) {
                error_log("Empty array passed as middleware!");
                return false;
            }
            $mwKey = strtolower($mw[0] ?? '');
            $options = $mw[1] ?? [];
        } else {
            $mwKey = strtolower($mw);
            $options = [];
        }

        $map = self::$middlewareAliases ?? [];
        if (isset($map[$mwKey])) {
            $mwKey = $map[$mwKey];
        }
        $class = "App\\middlewares\\" . ucfirst($mwKey);

        if (class_exists($class) && method_exists($class, 'handle')) {
            return $class::handle($options);
        }
        // If middleware not found, deny by default for safety
        error_log("Middleware class or handle method not found for: $class");
        return false;
    }

    /**
     * Resolve controller string to fully qualified class name.
     *
     * @param string $controller Controller name (optionally with namespace like 'Api\ClientApiController')
     * @return string Fully qualified class name
     */
    private function resolveControllerClass($controller)
    {
        return 'App\\controllers\\' . $controller;
    }

    /**
     * Normalize request URI by removing base path if app is not at web root.
     *
     * @param string $uri Raw request URI
     * @return string Normalized URI starting with '/'
     */
    private function normalizeUri($uri)
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $basePath = dirname($scriptName);

        $decodedUri = urldecode($uri);
        $decodedBasePath = urldecode($basePath);

        if ($decodedBasePath !== '/' && strpos($decodedUri, $decodedBasePath) === 0) {
            $decodedUri = substr($decodedUri, strlen($decodedBasePath));
        }
        if ($decodedUri === '' || $decodedUri === false) {
            $decodedUri = '/';
        }
        return $decodedUri;
    }
}
