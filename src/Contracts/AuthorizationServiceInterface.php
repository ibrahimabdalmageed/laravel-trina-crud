<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Enums\CrudAction;

interface AuthorizationServiceInterface
{

    public function authHasModelPermission(string $modelName, CrudAction $action): bool;

    public function authHasAttributePermission(string $modelName, string $attribute, CrudAction $action): bool;

    public function roleHasModelPermission(string $modelName, CrudAction $action, string $role): bool;

    public function userHasModelPermission(string $modelName, CrudAction $action, int|Model $user): bool;

    public function setRoleModelPermission(string $modelName, CrudAction $action, string $role, bool $enable): void;

    public function setUserModelPermission(string $modelName, CrudAction $action, int|Model $user, bool $enable): void;

    public function roleHasAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role): bool;

    public function userHasAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user): bool;

    public function setRoleAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role, bool $enable): void;

    public function setUserAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user, bool $enable): void;

    public function getAuthUser(): ?Model;

    public function getUserRoles(Model $user): ?array;

    public function getAllUsers(): array;

    public function assignRole($role, int|Model $user): bool;

    public function getAllRoles(): array;

    public function getUser(int $user): ?Model;
}
