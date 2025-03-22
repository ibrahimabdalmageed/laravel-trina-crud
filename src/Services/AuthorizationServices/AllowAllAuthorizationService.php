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

    public function setModelRolePermission(string $modelName, CrudAction $action, int $roleId, bool $enable): void {}

    public function setModelUserPermission(string $modelName, CrudAction $action, int $userId, bool $enable): void {}

    public function getRules(): array
    {
        return [];
    }


    public function getAllUsers(): array
    {
        return [];
    }

    public function addRule(string $modelName, CrudAction $action, $userId, bool $isRole = false): bool
    {
        return true;
    }

    public function deleteRule(string $permissionName): bool
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

    public function findRole(int $roleId)
    {
        return null;
    }
}
