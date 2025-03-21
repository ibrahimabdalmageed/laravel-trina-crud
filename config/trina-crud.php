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
];
