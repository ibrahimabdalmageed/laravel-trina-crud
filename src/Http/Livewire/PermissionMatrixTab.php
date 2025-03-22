<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionMatrixTab extends Component
{
    public $rules = [];
    public $roles = [];
    public $selectedRoleFilter = '';

    protected $listeners = [
        'permissionsChanged' => 'loadData',
        'rolesChanged' => 'loadData'
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->loadRoles();
        $this->loadRules();
    }

    public function render()
    {
        return view('trina-crud::livewire.permission-matrix-tab');
    }

    public function loadRoles()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $roles = $authService->getAllRoles();

        // Add permissions count to each role
        foreach ($roles as &$role) {
            $roleModel = Role::find($role['id']);
            $role['permissionsCount'] = $roleModel ? $roleModel->permissions->count() : 0;
        }

        $this->roles = $roles;
    }

    public function loadRules()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $this->rules = $authService->getRules();

        // If a role filter is applied, filter the rules
        if (!empty($this->selectedRoleFilter)) {
            $filteredRules = [];
            $selectedRole = Role::find($this->selectedRoleFilter);

            if ($selectedRole) {
                $rolePermissions = $selectedRole->permissions->pluck('name')->toArray();

                foreach ($this->rules as $model => $actions) {
                    $filteredActions = [];

                    foreach ($actions as $action => $details) {
                        if (in_array($details['name'], $rolePermissions)) {
                            $filteredActions[$action] = $details;
                        }
                    }

                    if (!empty($filteredActions)) {
                        $filteredRules[$model] = $filteredActions;
                    }
                }

                $this->rules = $filteredRules;
            }
        }
    }

    // Method for permission matrix
    public function togglePermission($model, $action)
    {
        if (empty($this->selectedRoleFilter)) {
            session()->flash('message', 'Please select a role to toggle permissions');
            return;
        }

        $authService = App::make(AuthorizationServiceInterface::class);
        $role = Role::find($this->selectedRoleFilter);

        if (!$role) {
            return;
        }

        $permissionName = "$action $model";
        $permission = Permission::where('name', $permissionName)->first();

        if ($permission && $role->hasPermissionTo($permissionName)) {
            // Remove permission
            $role->revokePermissionTo($permissionName);
            session()->flash('message', "Permission '$action' removed from " . class_basename($model));
        } else {
            // Add permission
            $action = CrudAction::from($action);
            $authService->addRule($model, $action, $role->id, true);
            session()->flash('message', "Permission '$action' added to " . class_basename($model));
        }

        $this->loadRules();
        $this->loadRoles();
        $this->dispatch('permissionsChanged');
    }

    // Method to handle role filter changes
    public function updatedSelectedRoleFilter()
    {
        $this->loadRules();
    }
}
