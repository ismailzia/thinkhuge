<?php

use App\core\Router;

return array_merge(

    // Group for auth-required routes
    Router::group(['middleware' => ['auth']], function ($middlewares, &$routes) {

        // Dashboard routes
        $routes[] = Router::get('/', 'ReportsController', 'index', $middlewares);
        $routes[] = Router::get('/reports',  'ReportsController', 'index', $middlewares);
        $routes[] = Router::get('/api_integration',  'ApiController', 'index', $middlewares);

        // Transactions routes
        $routes[] = Router::get('/transactions', 'TransactionController', 'index', $middlewares);
        $routes[] = Router::post('/transactions/add', 'TransactionController', 'store', array_merge($middlewares, ['csrf']));
        $routes[] = Router::post('/transactions/update', 'TransactionController', 'update', array_merge($middlewares, ['csrf']));
        $routes[] = Router::post('/transactions/delete/{id}', 'TransactionController', 'delete', array_merge($middlewares, ['csrf']));
        $routes[] = Router::get('/transactions/{id}', 'TransactionController', 'show', $middlewares);



        // Clients routes
        $routes[] = Router::get('/clients', 'ClientController', 'index', $middlewares);
        $routes[] = Router::post(
            '/clients/add',
            'ClientController',
            'store',
            array_merge($middlewares, [
                ['rate_limiter', ['action' => 'clients_add', 'max' => 10, 'window' => 60]],
                'csrf'
            ])
        );
        $routes[] = Router::post(
            '/clients/update',
            'ClientController',
            'update',
            array_merge($middlewares, [
                ['rate_limiter', ['action' => 'clients_update', 'max' => 30, 'window' => 60]],
                'csrf'
            ])
        );
        $routes[] = Router::post(
            '/clients/delete/{id}',
            'ClientController',
            'delete',
            array_merge($middlewares, [
                ['rate_limiter', ['action' => 'clients_delete', 'max' => 30, 'window' => 60]],
                'csrf'
            ])
        );

    }),

    // Public routes
    [

        Router::get('/login', 'AuthController', 'showLogin'),
        Router::get('/register', 'AuthController', 'showRegister'),
        Router::get('/logout', 'AuthController', 'logout'),


        Router::post(
            '/auth/login',
            'AuthController',
            'login',
            [
                ['rate_limiter', ['action' => 'login', 'max' => 10, 'window' => 60]], // 5 login attempts per minute
                'csrf'
            ]
        ),

        Router::post(
            '/auth/register',
            'AuthController',
            'register',
            [
                ['rate_limiter', ['action' => 'register', 'max' => 10, 'window' => 60]], // 3 registers per minute
                'csrf'
            ]
        )
    ]
);