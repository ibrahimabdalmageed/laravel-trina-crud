<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class AllowAllAuthorizationService implements AuthorizationServiceInterface
{
    public function hasPermissionTo(string $permissionName): bool
    {
        return true;
    }

    public function hasModelPermission(string $modelName, CrudAction $action): bool
    {
        return true;
    }

    public function getUser(): ?Model
    {
        return null;
    }

    public function isAttributeAuthorized(Model $model, string $attribute, CrudAction $action): bool
    {
        return true;
    }
}
