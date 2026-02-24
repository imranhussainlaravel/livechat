<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LiveChat Configuration
    |--------------------------------------------------------------------------
    */

    'sla' => [
        'first_response' => env('SLA_FIRST_RESPONSE', 120),   // 2 minutes
        'resolution'     => env('SLA_RESOLUTION', 3600),       // 1 hour
        'queue_wait'     => env('SLA_QUEUE_WAIT', 300),        // 5 minutes
    ],

    'heartbeat' => [
        'timeout' => env('HEARTBEAT_TIMEOUT', 120), // seconds
    ],

    'agent' => [
        'default_max_concurrency' => env('AGENT_MAX_CONCURRENCY', 5),
    ],
];
