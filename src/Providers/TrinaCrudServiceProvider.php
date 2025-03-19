<?php

namespace Trinavo\TrinaCrud\Providers;

use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/../../config/trinacrud.php',
            'trinacrud'
        );

        $this->commands([
            \Trinavo\TrinaCrud\Console\Commands\SyncTrinaCrudModelsCommand::class,
            \Trinavo\TrinaCrud\Console\Commands\SyncTrinaCrudColumnsCommand::class,
        ]);

        $this->app->singleton('dart-model-generator', function () {
            return new \Trinavo\TrinaCrud\Services\Generators\DartModelGeneratorService();
        });

        $this->app->singleton(\Trinavo\TrinaCrud\Services\TrinaCrudAuthorizationService::class);
    }
}
