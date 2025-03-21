<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Support\Facades\Auth;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class SpatiePermissionAuthorizationService implements AuthorizationServiceInterface
{
    public function hasPermissionTo(string $permissionName): bool
    {
        return Auth::user()->hasPermissionTo($permissionName);
    }

    public function getUser(): ?Authenticated
    {
        return Auth::user();
    }
}
