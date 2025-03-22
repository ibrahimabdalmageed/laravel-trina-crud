<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpatiePermissionAuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Get the authenticated user
     * 
     * @return ?Model
     */
    public function getUser(?int $userId = null): ?Model
    {
        return $userId ? User::find($userId) : Auth::user();
    }

    /**
     * Check if the user has permission to perform an action
     * 
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        try {
            return $user->hasPermissionTo($permissionName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Check if the user has permission to access a model attribute
     * 
     * @param Model $model The model
     * @param string $attribute The attribute
     * @param CrudAction $action The action (view, create, update, delete)
     * @return bool
     */
    public function isAttributeAuthorized(Model $model, string $attribute, CrudAction $action): bool
    {
        $modelName = get_class($model);
        $permissionName = $action->toAttributePermissionString($modelName, $attribute);
        return $this->hasPermissionTo($permissionName);
    }

    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param CrudAction $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, CrudAction $action): bool
    {
        $permissionName = $action->toModelPermissionString($modelName);
        // Check if user has the permission
        return $this->hasPermissionTo($permissionName);
    }


    public function setModelRolePermission(string $modelName, CrudAction $action, int $roleId, bool $enable): void
    {
        $permissionName = $action->toModelPermissionString($modelName);
        $role = Role::find($roleId);
        if ($enable) {
            $role->givePermissionTo($permissionName);
        } else {
            $role->revokePermissionTo($permissionName);
        }
    }

    public function setModelUserPermission(string $modelName, CrudAction $action, int $userId, bool $enable): void
    {
        $permissionName = $action->toModelPermissionString($modelName);
        $user = $this->getUser($userId);
        if ($enable) {
            $user->givePermissionTo($permissionName);
        } else {
            $user->revokePermissionTo($permissionName);
        }
    }

    /**
     * Get all permission rules for models
     * 
     * @return array
     */
    public function getRules(): array
    {
        $permissions = Permission::all();
        $rules = [];

        foreach ($permissions as $permission) {
            // Only include permissions that match our format
            if (preg_match('/^(read|create|update|delete)\s+(.+)$/', $permission->name, $matches)) {
                $action = $matches[1];
                $model = $matches[2];

                if (!isset($rules[$model])) {
                    $rules[$model] = [];
                }

                $rules[$model][$action] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'roles' => $permission->roles->pluck('name', 'id')->toArray(),
                    'users' => $permission->users->pluck('name', 'id')->toArray()
                ];
            }
        }

        return $rules;
    }

    /**
     * Add a permission rule
     * 
     * @param string $modelName The name of the model
     * @param CrudAction $action The action (read, create, update, delete)
     * @param string|int $userId The ID of the user or role to grant permission to
     * @param bool $isRole Whether the ID refers to a role (true) or user (false)
     * @return bool Success status
     */
    public function addRule(string $modelName, CrudAction $action, $userId, bool $isRole = false): bool
    {
        $permissionName = $action->toModelPermissionString($modelName);

        // Create permission if it doesn't exist
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);

        if ($isRole) {
            $role = Role::findById($userId);
            $role->givePermissionTo($permission);
        } else {
            $userModel = $this->getUserModelClass();
            $user = $userModel::find($userId);
            if ($user) {
                $user->givePermissionTo($permission);
            }
        }

        return true;
    }

    /**
     * Delete a permission rule
     * 
     * @param string $permissionName The name of the permission to delete
     * @return bool Success status
     */
    public function deleteRule(string $permissionName): bool
    {
        try {
            $permission = Permission::findByName($permissionName);
            $permission->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all users in the system
     * 
     * @return array
     */
    public function getAllUsers(): array
    {
        $userModel = $this->getUserModelClass();

        return $userModel::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? null
            ];
        })->toArray();
    }

    /**
     * Get all roles in the system
     * 
     * @return array
     */
    public function getAllRoles(): array
    {
        return Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name
            ];
        })->toArray();
    }

    /**
     * Get the user model class
     * 
     * @return string
     */
    protected function getUserModelClass(): string
    {
        return Config::get('auth.providers.users.model', '\App\Models\User');
    }

    public function findRole(int $roleId)
    {
        return Role::find($roleId);
    }
}
