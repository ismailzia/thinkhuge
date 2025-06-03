<?php

namespace App\middlewares;
use App\core\BaseMiddleware;

/**
 * Middleware to verify CSRF tokens on POST requests.
 * Prevents Cross-Site Request Forgery attacks by validating tokens.
 */
class VerifyCsrfToken extends BaseMiddleware
{
    public static function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                echo 'Invalid CSRF token.';
                exit;
            }
        }
        return true;
    }
}
