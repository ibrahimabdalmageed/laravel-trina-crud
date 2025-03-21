<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class SpatiePermissionAuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Get the authenticated user
     * 
     * @return ?Model
     */
    public function getUser(): ?Model
    {
        return Auth::user();
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
        
        return $user->hasPermissionTo($permissionName);
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
        $permissionName = Str::kebab(get_class($model)) . '_' . Str::kebab($attribute) . '_' . Str::kebab($action->value);
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
        $permissionName = Str::kebab($modelName) . '_' . Str::kebab($action->value);

        // Check if user has the permission
        return $this->hasPermissionTo($permissionName);
    }
}
