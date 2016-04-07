<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Verifications Driver
    |--------------------------------------------------------------------------
    |
    | Verifications driver used to manage verifications.
    |
    | Supported: "users"
    |
    */

    'default' => env('VERIFICATION_DRIVER', 'users'),

    /*
    |--------------------------------------------------------------------------
    | Default Verification Value
    |--------------------------------------------------------------------------
    |
    | This option determines if user MUST verify
    | his account before login or not.
    |
    */

    'verify' => true,

    /*
    |--------------------------------------------------------------------------
    | Repositories Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver information for each repository that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with this package. You are free to add more.
    |
    */

    'repositories' => [

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION', 'mysql'),
            'table' => 'users_verifications',
            'expires' => 0,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Brokers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure brokers.
    | A default configuration has been added
    | for each back-end shipped with this package. You are free to add more.
    |
    */

    'brokers' => [

        'users' => [
            'provider' => 'users',
            'repository' => 'database',
            'email' => 'verification::auth.emails.verification',
        ],

    ],

];
