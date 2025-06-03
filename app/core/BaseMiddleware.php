<?php

namespace App\core;

/**
 * Base class for all middleware.
 * Middleware processes requests before they reach controllers,
 */
abstract class BaseMiddleware
{
    /**
     * Middleware handler method.
     * Should return true to continue request processing,
     * or handle the response itself (e.g., redirect, error).
     *
     * @return bool
     */
    abstract public static function handle();
}
