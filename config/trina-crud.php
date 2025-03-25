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
        app_path('app/Models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Model Namespaces
    |--------------------------------------------------------------------------
    |
    | This is a security feature that restricts which model namespaces are allowed
    | to be loaded by this package. Only models in these namespaces will be
    | accessible through the CRUD interface.
    |
    | This setting helps prevent potential security vulnerabilities by ensuring that
    | only your application's intended models can be accessed via the API. Without
    | this restriction, a malicious user could potentially attempt to access sensitive
    | system classes or models outside your application's intended scope.
    |
    | Example:
    |   - 'App\\Models' - Allow access to all models in App\Models namespace
    |   - 'App\\Models\\Public' - Restrict to only models in a specific subfolder
    |
    | For multi-module applications, you may need to add additional namespaces.
    | Always use the most specific namespaces possible for better security.
    |
    */
    'allowed_model_namespaces' => [
        'App\\Models',
        // Add other authorized namespaces here
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


    /*
    |--------------------------------------------------------------------------
    | Admin Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to all TrinaCrud admin routes.
    | You can add your own middleware to this list or use an empty array
    | to disable all middleware for TrinaCrud admin routes.
    |
    */
    'admin_middleware' => [
        'web',
    ],


    'admin_route_prefix' => env('TRINA_CRUD_ADMIN_PREFIX', 'trina-crud/admin'),


];
