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

    /**
     * In the AllowAll implementation, admin access is always granted
     * This is suitable for development environments or when no authentication is needed
     * 
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        return true;
    }

    public function getUser(): ?Model
    {
        return null;
    }
}
