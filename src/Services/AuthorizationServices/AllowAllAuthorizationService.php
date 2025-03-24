<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class AllowAllAuthorizationService implements AuthorizationServiceInterface
{
    public function getUser(int $user): ?Model
    {
        return null;
    }

    public function getAuthUser(): ?Model
    {
        return null;
    }

    public function getUserRoles(Model $user): ?array
    {
        return [];
    }

    public function getAllUsers(): array
    {
        return [];
    }

    public function assignRole($role, int|Model $user): bool
    {
        return true;
    }

    public function getAllRoles(): array
    {
        return [];
    }

    public function userHasModelPermission(string $modelName, CrudAction $action, int|Model $user): bool
    {
        return true;
    }

    public function userHasAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user): bool
    {
        return true;
    }

    public function roleHasModelPermission(string $modelName, CrudAction $action, string $role): bool
    {
        return true;
    }

    public function roleHasAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role): bool
    {
        return true;
    }

    public function setRoleModelPermission(string $modelName, CrudAction $action, string $role, bool $enable): void
    {
        // Do nothing
    }

    public function setUserModelPermission(string $modelName, CrudAction $action, int|Model $user, bool $enable): void
    {
        // Do nothing
    }

    public function setRoleAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role, bool $enable): void
    {
        // Do nothing
    }

    public function setUserAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user, bool $enable): void
    {
        // Do nothing
    }

    public function authHasModelPermission(string $modelName, CrudAction $action): bool
    {
        return true;
    }

    public function authHasAttributePermission(string $modelName, string $attribute, CrudAction $action): bool
    {
        return true;
    }

    public function deleteRole(string $role): void
    {
        // Do nothing
    }

    public function createRole(string $role): void
    {
        // Do nothing
    }
}
