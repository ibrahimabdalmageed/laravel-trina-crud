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
     * Check if the user has admin access to TrinaCrud
     * This is used to protect administrative routes like sync-models
     *
     * @return bool
     */
    public function hasAdminAccess(): bool;

    public function getUser(): ?Model;
}
