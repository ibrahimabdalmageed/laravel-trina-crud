<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthorizationServiceInterface
{
    /**
     * Check if the user has permission to access a model
     *
     * @param string $permissionName The name of the permission
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool;



    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action): bool;


    /**
     * Get the user model
     * 
     * @return ?Model
     */
    public function getUser(): ?Model;

    /**
     * Check if the user has permission to access a model attribute
     * 
     * @param Model $model The model
     * @param string $attribute The attribute
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function isAttributeAuthorized(Model $model, string $attribute, string $action): bool;
}
