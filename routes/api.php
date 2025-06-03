<?php

use App\core\Router;

return array_merge(

    Router::group([
        'middleware' => [
            'api_auth',
            ['api_rate_limiter', ['max' => 60, 'window' => 60]]
        ]
    ], function ($middlewares, &$routes) {
        $routes[] = Router::get('/clients', 'ApiController', 'clients', $middlewares);
        $routes[] = Router::get('/transactions', 'ApiController', 'transactions', $middlewares);
        $routes[] = Router::get('/client/{id}/transactions', 'ApiController', 'clientTransactions', $middlewares);
    })

);