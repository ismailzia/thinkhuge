<?php
namespace App\middlewares;

use App\core\BaseMiddleware;
use App\models\User;

/**
 * Middleware to authenticate API requests using an API key.
 * Checks the X-API-KEY header or api_key query parameter,
 * validates the key, and ensures the associated user is active.
 * Denies access with JSON error if validation fails.
 */
class ApiAuth extends BaseMiddleware
{
    protected static $currentUser = null;

    public static function handle()
    {
        // 1. Get API key from header or query param
        $apiKey = null;

        // Prefer header (case-insensitive)
        foreach ($_SERVER as $key => $value) {
            if (strtolower($key) === 'http_x_api_key') {
                $apiKey = $value;
                break;
            }
        }
        // Fallback to GET param if not found in header
        if (!$apiKey && isset($_GET['api_key'])) {
            $apiKey = $_GET['api_key'];
        }

        // 2. Require API key presence
        if (!$apiKey) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'API key required. Provide via X-API-KEY header or api_key param.'
            ]);
            exit;
        }

        // 3. Validate API key and user active status
        $user = User::findByApiKey($apiKey);
        if (!$user || !$user->is_active) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Invalid or inactive API key.'
            ]);
            exit;
        }

        // 4. Store authenticated user for request use
        static::$currentUser = $user;
        return true;
    }

    /**
     * Get the authenticated user for this API request.
     *
     * @return User|null
     */
    public static function user()
    {
        return static::$currentUser;
    }
}
