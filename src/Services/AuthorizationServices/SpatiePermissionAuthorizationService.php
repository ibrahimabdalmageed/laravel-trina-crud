<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class SpatiePermissionAuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Check if the user has permission to access a model
     * 
     * @param string $permissionName The name of the permission
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        return $user->hasPermissionTo($permissionName);
    }

    public function isAttributeAuthorized(Model $model, string $attribute, string $action): bool
    {
        $permissionName = Str::kebab(get_class($model)) . '_' . Str::kebab($attribute) . '_' . Str::kebab($action);
        return $this->hasPermissionTo($permissionName);
    }

    public function hasModelPermission(string $modelName, string $action): bool
    {
        $permissionName = Str::kebab($modelName) . '_' . Str::kebab($action);

        // Check if user has the permission
        return $this->hasPermissionTo($permissionName);
    }


    /**
     * Get the authenticated user
     * 
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getUser(): ?Model
    {
        return Auth::user();
    }
}
