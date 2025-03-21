<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Container\Attributes\Authenticated;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class AllowAllAuthorizationService implements AuthorizationServiceInterface
{

    public function hasPermissionTo(string $permissionName): bool
    {
        return true;
    }

    public function getUser(): ?Authenticated
    {
        return null;
    }
}
