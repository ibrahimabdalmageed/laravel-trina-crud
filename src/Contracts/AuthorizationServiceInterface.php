<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Enums\CrudAction;

interface AuthorizationServiceInterface
{
    /**
     * Check if the user has permission to perform an action
     * 
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool;



    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param CrudAction $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, CrudAction $action): bool;


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
     * @param CrudAction $action The action (view, create, update, delete)
     * @return bool
     */
    public function isAttributeAuthorized(Model $model, string $attribute, CrudAction $action): bool;
    
    /**
     * Get all permission rules for models
     * 
     * @return array
     */
    public function getRules(): array;
    
    /**
     * Add a permission rule
     * 
     * @param string $modelName The name of the model
     * @param CrudAction $action The action (read, create, update, delete)
     * @param string|int $userId The ID of the user or role to grant permission to
     * @param bool $isRole Whether the ID refers to a role (true) or user (false)
     * @return bool Success status
     */
    public function addRule(string $modelName, CrudAction $action, $userId, bool $isRole = false): bool;
    
    /**
     * Delete a permission rule
     * 
     * @param string $permissionName The name of the permission to delete
     * @return bool Success status
     */
    public function deleteRule(string $permissionName): bool;
    
    /**
     * Get all users in the system
     * 
     * @return array
     */
    public function getAllUsers(): array;
}
