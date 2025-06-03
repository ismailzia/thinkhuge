<?php
namespace App\middlewares;

use App\core\BaseMiddleware;
use App\models\SessionModel;
use App\models\User;

/**
 * Middleware to handle user authentication.
 * Redirects unauthenticated users to the login page.
 * Validates active user and session.
 * Stores the authenticated user in a static property for global access.
 */
class Auth extends BaseMiddleware
{
    protected static $currentUser = null;

    /**
     * Main middleware handler to check authentication.
     * Redirects to login if user not authenticated or session invalid.
     *
     * @return bool True if authenticated
     */
    public static function handle()
    {
        $basePath = app_base_path();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Load user by session user_id
        static::$currentUser = User::find(["id" => $_SESSION['user_id']]);
        if (!static::$currentUser || !static::$currentUser->is_active) {
            unset($_SESSION['user_id']);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Optionally verify session is valid using SessionModel
        $sessionId = session_id();
        if (!SessionModel::isValid($_SESSION['user_id'], $sessionId)) {
            unset($_SESSION['user_id']);
            session_destroy();
            header('Location: ' . $basePath . '/login');
            exit;
        }

        return true;
    }

    /**
     * Get the authenticated user object.
     *
     * @return User|null
     */
    public static function user()
    {
        if (static::$currentUser !== null) {
            return static::$currentUser;
        }

        if (isset($_SESSION['user_id'])) {
            static::$currentUser = User::find(["id" => $_SESSION['user_id']]);
            return static::$currentUser;
        }

        return null;
    }
}
