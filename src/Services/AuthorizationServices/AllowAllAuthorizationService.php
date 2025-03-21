<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class AllowAllAuthorizationService implements AuthorizationServiceInterface
{

    public function hasPermissionTo(string $permissionName): bool
    {
        return true;
    }

    public function getUser(): ?Model
    {
        return null;
    }
}
