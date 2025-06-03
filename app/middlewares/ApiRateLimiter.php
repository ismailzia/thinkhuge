<?php
namespace App\middlewares;

use App\core\BaseMiddleware;

/**
 * Middleware to limit API request rate per user and action.
 * Uses PHP session to track requests within a time window.
 * Returns 429 error when limit exceeded.
 */
class ApiRateLimiter extends BaseMiddleware
{
    public static function handle($options = [])
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        // Get user ID from ApiAuth middleware context
        $user = ApiAuth::user();
        $userId = $user->id ?? null;

        // Define rate limit parameters
        $action = $options['action'] ?? ($_SERVER['REQUEST_URI'] ?? 'default');
        $max = $options['max'] ?? 30;       // max requests allowed
        $window = $options['window'] ?? 60; // time window in seconds

        // Key to track rate limit in session (can replace with Redis for scaling)
        $rateKey = "api_rate_{$userId}_" . md5($action);
        $now = time();

        if (!isset($_SESSION[$rateKey])) {
            // Initialize tracking data
            $_SESSION[$rateKey] = ['attempts' => 1, 'reset' => $now + $window];
        } else {
            // Reset window if expired, otherwise increment attempts
            if ($_SESSION[$rateKey]['reset'] <= $now) {
                $_SESSION[$rateKey] = ['attempts' => 1, 'reset' => $now + $window];
            } else {
                $_SESSION[$rateKey]['attempts']++;
            }
        }

        // Deny if max requests exceeded
        if ($_SESSION[$rateKey]['attempts'] > $max) {
            $retryAfter = $_SESSION[$rateKey]['reset'] - $now;
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded. Try again in ' . $retryAfter . ' seconds.',
                'retry_after' => $retryAfter
            ]);
            exit;
        }

        return true;
    }
}
