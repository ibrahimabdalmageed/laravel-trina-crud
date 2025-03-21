<?php

namespace Trinavo\TrinaCrud\Services\AuthorizationServices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // Check if the user model has the hasPermissionTo method from Spatie's package
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permissionName);
        }
        
        // Fallback if Spatie's package is not properly installed
        return false;
    }

    /**
     * Check if the user has admin access to TrinaCrud
     * In the Spatie implementation, this checks for a specific permission
     * 
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // Check for admin permission
        // You can configure this permission name in the config file
        $adminPermission = config('trina-crud.admin_permission', 'manage-trina-crud');
        
        // Check if the user model has the hasPermissionTo method from Spatie's package
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($adminPermission);
        }
        
        // Fallback if Spatie's package is not properly installed
        return false;
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
