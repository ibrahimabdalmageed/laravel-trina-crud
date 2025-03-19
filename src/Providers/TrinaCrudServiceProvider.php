<?php

namespace Trinavo\TrinaCrud\Providers;

use Illuminate\Support\ServiceProvider;
use Trinavo\TrinaCrud\Contracts\TrinaCrudAuthorizationServiceInterface;
use Trinavo\TrinaCrud\Services\AllowAllAuthorizationService;
use Trinavo\TrinaCrud\Services\SpatiePermissionAuthorizationService;

class TrinaCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Publish configuration (optional)
        $this->publishes([
            __DIR__ . '/../../config/trinacrud.php' => config_path('trinacrud.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/trina-crud.php',
            'trina-crud'
        );

        $this->commands([
            \Trinavo\TrinaCrud\Console\Commands\SyncTrinaCrudModelsCommand::class,
            \Trinavo\TrinaCrud\Console\Commands\SyncTrinaCrudColumnsCommand::class,
        ]);

        $this->app->singleton('dart-model-generator', function () {
            return new \Trinavo\TrinaCrud\Services\Generators\DartModelGeneratorService();
        });

        $this->registerAuthServiceProvider();
    }


    public function registerAuthServiceProvider()
    {
        // Get authorization type from config, default to 'default'
        $authType = config('trina-crud.authorization_type', 'default');

        // Bind the appropriate implementation based on the config
        switch ($authType) {
            case 'allow_all':
                $this->app->bind(TrinaCrudAuthorizationServiceInterface::class, AllowAllAuthorizationService::class);
                break;
            case 'spatie':
                $this->app->bind(TrinaCrudAuthorizationServiceInterface::class, SpatiePermissionAuthorizationService::class);
                break;
            default:
                break;
        }
    }
}
