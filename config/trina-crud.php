<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authorization Type
    |--------------------------------------------------------------------------
    |
    | This value determines which authorization implementation to use.
    | Supported: "default", "spatie", "allow_all"
    |
    */
    'authorization_service' => env('TRINA_CRUD_AUTH_TYPE', 'spatie'),
    'ownership_service' => env('TRINA_CRUD_OWNERSHIP_TYPE', 'ownable'),
    'ownership_field' => env('TRINA_CRUD_OWNERSHIP_FIELD', 'user_id'),
    'model_paths' => [
        base_path('app/Models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix used for all TrinaCrud routes.
    | You can change this to customize the URL structure of your API.
    |
    */
    'route_prefix' => env('TRINA_CRUD_PREFIX', 'api/crud'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to all TrinaCrud routes.
    | You can add your own middleware to this list or use an empty array
    | to disable all middleware for TrinaCrud routes.
    |
    */
    'middleware' => [
        'auth:sanctum',
    ],

];
