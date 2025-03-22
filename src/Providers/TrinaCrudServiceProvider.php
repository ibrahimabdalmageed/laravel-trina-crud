<?php

namespace Trinavo\TrinaCrud\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Http\Livewire\PermissionsManager;
use Trinavo\TrinaCrud\Http\Livewire\PermissionsTab;
use Trinavo\TrinaCrud\Http\Livewire\RolesTab;
use Trinavo\TrinaCrud\Http\Livewire\PermissionMatrixTab;
use Trinavo\TrinaCrud\Http\Livewire\UserRolesTab;
use Trinavo\TrinaCrud\Http\Middleware\TrinaCrudAdminMiddleware;
use Trinavo\TrinaCrud\Services\AuthorizationServices\AllowAllAuthorizationService;
use Trinavo\TrinaCrud\Services\AuthorizationServices\SpatiePermissionAuthorizationService;
use Trinavo\TrinaCrud\Services\ModelService;
use Trinavo\TrinaCrud\Services\OwnershipServices\FieldOwnerService;
use Trinavo\TrinaCrud\Services\OwnershipServices\OwnableService;

class TrinaCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'trina-crud');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register Livewire components
        if (class_exists(Livewire::class)) {
            Livewire::component('trina-crud::permissions-manager', PermissionsManager::class);
            Livewire::component('trina-crud::permissions-tab', PermissionsTab::class);
            Livewire::component('trina-crud::roles-tab', RolesTab::class);
            Livewire::component('trina-crud::user-roles-tab', UserRolesTab::class);
        }

        // Publish configuration (optional)
        $this->publishes([
            __DIR__ . '/../../config/trina-crud.php' => config_path('trina-crud.php'),
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/trina-crud'),
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

        $this->app->singleton(ModelServiceInterface::class, ModelService::class);

        $this->registerAuthServiceProvider();
        $this->registerOwnershipServiceProvider();
    }


    public function registerAuthServiceProvider()
    {
        // Get authorization type from config, default to 'default'
        $authService = config('trina-crud.authorization_service', 'default');

        // Bind the appropriate implementation based on the config
        switch ($authService) {
            case 'allow_all':
                $this->app->singleton(AuthorizationServiceInterface::class, AllowAllAuthorizationService::class);
                break;
            case 'spatie':
                $this->app->singleton(AuthorizationServiceInterface::class, SpatiePermissionAuthorizationService::class);
                break;
            default:
                break;
        }
    }

    public function registerOwnershipServiceProvider()
    {
        $ownershipService = config('trina-crud.ownership_service', 'ownable');

        switch ($ownershipService) {
            case 'ownable':
                $this->app->singleton(OwnershipServiceInterface::class, OwnableService::class);
                break;
            case 'field':
                $this->app->singleton(OwnershipServiceInterface::class, FieldOwnerService::class);
                break;
            default:
                break;
        }
    }
}
