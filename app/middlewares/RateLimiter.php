<?php

namespace App\middlewares;

use App\core\BaseMiddleware;
use App\core\Flash;
use App\core\Redirect;

/**
 * Middleware to limit the rate of requests per session and IP.
 * Prevents abuse by enforcing max requests within a time window.
 * Returns JSON error for API/AJAX requests or flashes error and redirects for regular POST requests.
 */
class RateLimiter extends BaseMiddleware
{
    /**
     * Handle rate limiting.
     *
     * @param array $options {
     *     @type string $action  Unique action key (default 'default').
     *     @type int    $max     Max attempts allowed (default 5).
     *     @type int    $window  Time window in seconds (default 60).
     * }
     * @return bool True if allowed, otherwise exits with error response.
     */
    public static function handle($options = [])
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        $action = $options['action'] ?? 'default';
        $max = $options['max'] ?? 5;
        $window = $options['window'] ?? 60;
        $now = time();

        // Track attempts per session
        $sessionKey = "rate_limit_session_{$action}";
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['attempts' => 1, 'reset' => $now + $window];
        } else {
            if ($_SESSION[$sessionKey]['reset'] <= $now) {
                $_SESSION[$sessionKey] = ['attempts' => 1, 'reset' => $now + $window];
            } else {
                $_SESSION[$sessionKey]['attempts']++;
            }
        }

        // Track attempts per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ipKey = "rate_limit_ip_{$action}_{$ip}";
        if (!isset($_SESSION[$ipKey])) {
            $_SESSION[$ipKey] = ['attempts' => 1, 'reset' => $now + $window];
        } else {
            if ($_SESSION[$ipKey]['reset'] <= $now) {
                $_SESSION[$ipKey] = ['attempts' => 1, 'reset' => $now + $window];
            } else {
                $_SESSION[$ipKey]['attempts']++;
            }
        }

        $sessionExceeded = $_SESSION[$sessionKey]['attempts'] > $max;
        $ipExceeded = $_SESSION[$ipKey]['attempts'] > $max;
        $retryAfter = min(
            $_SESSION[$sessionKey]['reset'] - $now,
            $_SESSION[$ipKey]['reset'] - $now
        );

        if ($sessionExceeded || $ipExceeded) {
            http_response_code(429); // Too Many Requests

            // Return JSON for AJAX or API requests
            if (
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            ) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Rate limit exceeded.',
                    'retry_after' => $retryAfter,
                    'type' => $sessionExceeded ? 'session' : 'ip'
                ]);
                exit();
            }

            // For regular POST requests, flash error and redirect back
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                Flash::set('errors', [
                    'Rate Limit' => [
                        "Rate limit exceeded for your " .
                        ($sessionExceeded ? "session" : "IP") .
                        ". Try again in $retryAfter seconds."
                    ]
                ]);
                Redirect::back();
                exit();
            }

            // Otherwise render an error page
            render('error', [
                'errorTitle' => "Rate limit exceeded",
                'errorMessage' => "Rate limit exceeded for your " .
                    ($sessionExceeded ? "session" : "IP") .
                    ". Try again in $retryAfter seconds.",
            ], null);
            exit();
        }

        return true;
    }
}
