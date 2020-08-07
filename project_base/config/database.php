<?php

return [
    'fetch' => \PDO::FETCH_CLASS,
    'default' => env('DB_CONNECTION'),

    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_URL_MYSQL', 'localhost'),
            'port'      => 3306,
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'    => env('DB_PREFIX', ''),
        ],

        'testing' => [
            'driver'    => 'mysql',
            'host'      => env('DB_URL_MYSQL', 'localhost'),
            'port'      => 3306,
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'    => env('DB_PREFIX', ''),
        ],

        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => 'mongodb://' . env('DB_URL_MONGODB', 'localhost') . ':27017',
            'database' => ''
        ],

        'redis' => [
            'cluster' => false,
            'default' => [
                'host'     => env('REDIS_HOST', '127.0.0.1'),
                'port'     => env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', null),
                'database' => env('REDIS_DB', 0),
            ],

            'cache' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => env('REDIS_CACHE_DB', '1'),
            ],
        ],
    ],

    'migrations' => 'migrations',
];
