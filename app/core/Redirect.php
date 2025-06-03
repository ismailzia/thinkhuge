<?php
namespace App\core;

class Redirect
{
    /**
     * Redirect immediately to a given absolute or relative URL.
     *
     * @param string $url URL to redirect to
     * @return void
     */
    public static function to($url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Redirect to a route relative to the base path.
     * Adds the application base path prefix to the given route.
     *
     * @param string $route Relative route path (without leading slash)
     * @return void
     */
    public static function toBased($route)
    {
        $basePath = app_base_path();

        // Remove leading slash from route if any
        $route = ltrim($route, '/');

        $url = $basePath . '/' . $route;
        header("Location: $url");
        exit;
    }

    /**
     * Redirect back to the referring page or a default if invalid or unsafe.
     *
     * @param string $default Default fallback path
     * @return void
     */
    public static function back($default = '/dashboard')
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $baseUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http')
            . '://' . $_SERVER['HTTP_HOST'];

        if (strpos($referer, $baseUrl) === 0) {
            $refererPath = parse_url($referer, PHP_URL_PATH) ?? $default;

            // Security: prevent path traversal or invalid referers
            if (
                strpos($refererPath, '..') !== false ||
                !preg_match('#^/[a-zA-Z0-9/_-]*$#', $refererPath)
            ) {
                $refererPath = $default;
            }
        } else {
            $refererPath = $default;
        }

        header("Location: $refererPath", true, 302);
        exit;
    }
}
