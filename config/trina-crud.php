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
    'authorization_type' => env('TRINA_CRUD_AUTH_TYPE', 'allow_all'),

    'model_paths' => [
        base_path('app/Models'),
    ],
];
