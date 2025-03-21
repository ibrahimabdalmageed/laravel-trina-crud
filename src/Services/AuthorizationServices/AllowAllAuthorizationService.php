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

    public function hasModelPermission(string $modelName, string $action): bool
    {
        return true;
    }

    public function getUser(): ?Model
    {
        return null;
    }

    public function isAttributeAuthorized(Model $model, string $attribute, string $action): bool
    {
        return true;
    }
}
