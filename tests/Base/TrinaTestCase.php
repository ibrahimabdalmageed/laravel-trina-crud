<?php

namespace Trinavo\TrinaCrud\Tests\Base;

use Trinavo\TrinaCrud\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;

class TrinaTestCase extends TestCase
{
    protected AuthorizationServiceInterface|MockInterface $authService;
    protected OwnershipServiceInterface|MockInterface $ownershipService;

    protected function mockAuthService()
    {
        $this->authService = Mockery::mock(AuthorizationServiceInterface::class);
        $this->authService->shouldReceive('authHasModelPermission')->andReturn(true);
        $this->authService->shouldReceive('authHasAttributePermission')->andReturn(true);
        $this->authService->shouldReceive('getAuthUser')->andReturn(null);
        $this->authService->shouldReceive('getUser')->andReturn(null);
        $this->authService->shouldReceive('userHasModelPermission')->andReturn(true);


        // Bind the mock to the container
        $this->app->singleton(AuthorizationServiceInterface::class, function ($app) {
            return $this->authService;
        });
    }


    protected function mockOwnershipService()
    {
        $this->ownershipService = Mockery::mock(OwnershipServiceInterface::class);
        $this->ownershipService->shouldReceive('addOwnershipQuery')->andReturnUsing(function ($query, $modelClassName, $action) {
            return $query;
        });

        // Bind the mock to the container
        $this->app->singleton(OwnershipServiceInterface::class, function ($app) {
            return $this->ownershipService;
        });

        config(['trina-crud.allowed_model_namespaces' => ['Trinavo\\TrinaCrud']]);
    }
}
