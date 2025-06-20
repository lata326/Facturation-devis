<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | Ce paramètre détermine le driver de diffusion à utiliser par défaut
    | pour diffuser les événements de ton application. Les options possibles
    | sont : "pusher", "ably", "redis", "log", ou "null".
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcasters Configuration
    |--------------------------------------------------------------------------
    |
    | Ici tu configures les différents drivers de diffusion disponibles.
    | Chaque driver a ses propres paramètres, notamment pour se connecter
    | aux services tiers comme Pusher ou Ably, ou utiliser Redis.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
            'client_options' => [
                // options pour le client Guzzle HTTP
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],


];
