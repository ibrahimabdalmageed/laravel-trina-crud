<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
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

    public function revokeRoleFromUser($role, int|Model $user): bool
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

        return $user->can($action->toModelPermissionString($modelName));
    }

    public function userHasAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user): bool
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        return $user->can($action->toAttributePermissionString($modelName, $attribute));
    }

    public function roleHasModelPermission(string $modelName, CrudAction $action, string $role): bool
    {
        return Role::findByName($role)->can($action->toModelPermissionString($modelName));
    }

    public function roleHasAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role): bool
    {
        return Role::findByName($role)->can($action->toAttributePermissionString($modelName, $attribute));
    }

    public function setRoleModelPermission(string $modelName, CrudAction $action, string $role, bool $enable): void
    {
        $role = Role::findByName($role);
        $role->givePermissionTo($action->toModelPermissionString($modelName));
    }

    public function setUserModelPermission(string $modelName, CrudAction $action, int|Model $user, bool $enable): void
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        if ($enable) {
            $user->givePermissionTo($action->toModelPermissionString($modelName));
        } else {
            $user->removePermissionTo($action->toModelPermissionString($modelName));
        }
    }

    public function setRoleAttributePermission(string $modelName, string $attribute, CrudAction $action, string $role, bool $enable): void
    {
        $role = Role::findByName($role);
        if ($enable) {
            $role->givePermissionTo($action->toAttributePermissionString($modelName, $attribute));
        } else {
            $role->removePermissionTo($action->toAttributePermissionString($modelName, $attribute));
        }
    }

    public function setUserAttributePermission(string $modelName, string $attribute, CrudAction $action, int|Model $user, bool $enable): void
    {
        if (!$user instanceof Model) {
            $user = $this->getUser($user);
        }

        if ($enable) {
            $user->givePermissionTo($action->toAttributePermissionString($modelName, $attribute));
        } else {
            $user->removePermissionTo($action->toAttributePermissionString($modelName, $attribute));
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
