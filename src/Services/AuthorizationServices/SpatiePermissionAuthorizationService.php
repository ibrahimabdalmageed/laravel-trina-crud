<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Spatie\Permission\Models\Role;

class SpatiePermissionAuthorizationService implements AuthorizationServiceInterface
{


    public function getUser(int $user): ?Model
    {
        $userClass = config('auth.providers.users.model');

        return $userClass::find($user);
    }

    public function getAuthUser(): ?Model
    {
        return Auth::user();
    }

    public function getUserRoles(Model|int $user): ?array
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        if (!$user) {
            return [];
        }

        if (!$user->roles) {
            return [];
        }

        return $user->roles->pluck('name')->toArray();
    }

    public function getAllUsers(): array
    {
        return User::all()->map(function ($user) {
            return ['id' => $user->id, 'name' => $user->name];
        })->toArray();
    }

    public function assignRoleToUser($role, int|Model $user): bool
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        $user->assignRole($role);
        return true;
    }

    public function removeRoleForUser($role, int|Model $user): bool
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        $user->removeRole($role);
        return true;
    }

    public function getAllRoles(): array
    {
        return Role::all()->pluck('name')->toArray();
    }

    public function userHasModelPermission(string $modelName, CrudAction $action, int|Model $user): bool
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        try {
            return $user->hasPermissionTo($action->toModelPermissionString($modelName));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function userHasAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user): bool
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        try {
            return $user->hasPermissionTo($action->toAttributePermissionString($modelName, $attribute));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function roleHasModelPermission(string $modelName, CrudAction $action, string $role): bool
    {
        try {
            return Role::findByName($role)->hasPermissionTo($action->toModelPermissionString($modelName));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function roleHasAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role): bool
    {
        try {
            return Role::findByName($role)->hasPermissionTo($action->toAttributePermissionString($modelName, $attribute));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function setRoleModelPermission(string $modelName, CrudAction $action, string $role, bool $enable): void
    {
        $role = Role::findByName($role);
        $permissionName = $action->toModelPermissionString($modelName);

        if ($enable) {
            try {
                Permission::create([
                    'name' => $permissionName
                ]);
            } catch (PermissionAlreadyExists $e) {
            }

            $role->givePermissionTo($permissionName);
        } else {
            try {
                $role->revokePermissionTo($permissionName);
            } catch (PermissionDoesNotExist $e) {
            }
        }
    }

    public function setUserModelPermission(string $modelName, CrudAction $action, int|Model $user, bool $enable): void
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }
        $permissionName = $action->toModelPermissionString($modelName);
        if ($enable) {
            try {
                Permission::create([
                    'name' => $permissionName
                ]);
            } catch (PermissionAlreadyExists $e) {
            }

            $user->givePermissionTo($permissionName);
        } else {
            try {
                $user->removePermissionTo($permissionName);
            } catch (PermissionDoesNotExist $e) {
            }
        }
    }

    public function setRoleAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role, bool $enable): void
    {
        $role = Role::findByName($role);
        $permissionName = $action->toAttributePermissionString($modelName, $attribute);
        if ($enable) {
            try {
                Permission::create([
                    'name' => $permissionName
                ]);
            } catch (PermissionAlreadyExists $e) {
            }
            $role->givePermissionTo($permissionName);
        } else {
            try {
                $role->revokePermissionTo($permissionName);
            } catch (PermissionDoesNotExist $e) {
            }
        }
    }

    public function setUserAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user, bool $enable): void
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        $permissionName = $action->toAttributePermissionString($modelName, $attribute);
        if ($enable) {
            try {
                Permission::create([
                    'name' => $permissionName
                ]);
            } catch (PermissionAlreadyExists $e) {
            }
            $user->givePermissionTo($permissionName);
        } else {
            try {
                $user->removePermissionTo($permissionName);
            } catch (PermissionDoesNotExist $e) {
            }
        }
    }

    public function authHasModelPermission(string $modelName, CrudAction $action): bool
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return false;
        }
        return $this->userHasModelPermission($modelName, $action, $user);
    }

    public function authHasAttributePermission(string $modelName, string $attribute, CrudAction $action): bool
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return false;
        }
        return $this->userHasAttributePermission($modelName, $attribute, $action, $user);
    }

    public function deleteRole(string $role): void
    {
        Role::findByName($role)->delete();
    }

    public function createRole(string $role): void
    {
        Role::create([
            'name' => $role
        ]);
    }
}
