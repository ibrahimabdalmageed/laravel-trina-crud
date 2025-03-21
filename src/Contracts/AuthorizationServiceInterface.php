<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Container\Attributes\Authenticated;

interface AuthorizationServiceInterface
{
    /**
     * Check if the user has permission to access a model
     *
     * @param string $permissionName The name of the permission
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool;


    public function getUser(): ?Authenticated;
}
