<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Models\ModelSchema;

class PermissionsManager extends Component
{
    public $models = [];
    public $users = [];
    public $roles = [];
    public $rules = [];

    public $selectedModel = '';
    public $selectedActions = [];
    public $isRole = 1; // Default to role
    public $selectedUserId = '';

    // New properties for enhanced UI
    public $activeTab = 'permissions';
    public $showRoleModal = false;
    public $roleName = '';
    public $editingRoleId = null;
    public $selectedRoleFilter = '';

    protected $validationRules = [
        'selectedModel' => 'required|string',
        'selectedActions' => 'required|array|min:1',
        'selectedUserId' => 'required',
        'roleName' => 'required|string|min:2|max:50',
    ];

    protected $messages = [
        'selectedModel.required' => 'Please select a model',
        'selectedActions.required' => 'Please select at least one action',
        'selectedActions.min' => 'Please select at least one action',
        'selectedUserId.required' => 'Please select a user or role',
        'roleName.required' => 'Role name is required',
        'roleName.min' => 'Role name must be at least 2 characters',
    ];

    public function mount()
    {
        $this->loadModels();
        $this->loadUsers();
        $this->loadRoles();
        $this->loadRules();
    }

    public function render()
    {
        return view('trina-crud::livewire.permissions-manager');
    }

    public function loadModels()
    {
        $modelService = App::make(ModelServiceInterface::class);
        $this->models = collect($modelService->getSchema())->map(
            fn(ModelSchema $model) =>
            $model->getModelName()
        );
    }

    public function loadUsers()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $this->users = $authService->getAllUsers();
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

    public function addPermission()
    {
        $this->validate($this->validationRules);

        $authService = App::make(AuthorizationServiceInterface::class);

        foreach ($this->selectedActions as $actionName) {
            $action = CrudAction::from($actionName);

            if ($this->isRole) {
                $authService->addRule($this->selectedModel, $action, null, $this->selectedUserId);
            } else {
                $authService->addRule($this->selectedModel, $action, $this->selectedUserId, null);
            }
        }

        $this->reset(['selectedActions', 'selectedUserId']);
        $this->loadRules();
        $this->loadRoles(); // Refresh roles to update permission counts

        session()->flash('message', 'Permission added successfully!');
    }

    public function deletePermission($permissionName)
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $authService->deleteRule($permissionName);

        $this->loadRules();
        $this->loadRoles(); // Refresh roles to update permission counts

        session()->flash('message', 'Permission deleted successfully!');
    }

    public function bulkAssignToRole($model, $roleName)
    {
        $authService = App::make(AuthorizationServiceInterface::class);

        // Check if role exists, if not create it
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Determine which actions to assign based on role
        $actions = [];
        switch ($roleName) {
            case 'admin':
                $actions = ['read', 'create', 'update', 'delete'];
                break;
            case 'editor':
                $actions = ['read', 'update'];
                break;
            case 'viewer':
                $actions = ['read'];
                break;
        }

        // Assign permissions
        foreach ($actions as $actionName) {
            $action = CrudAction::from($actionName);
            $authService->addRule($model, $action, null, $role->id);
        }

        $this->loadRules();
        $this->loadRoles(); // Refresh roles to update permission counts

        session()->flash('message', ucfirst($roleName) . ' permissions assigned to ' . class_basename($model) . ' successfully!');
    }

    // New methods for role management
    public function showCreateRoleModal()
    {
        $this->showRoleModal = true;
        $this->editingRoleId = null;
        $this->roleName = '';
    }

    public function editRole($roleId)
    {
        $role = Role::find($roleId);
        if ($role) {
            $this->editingRoleId = $roleId;
            $this->roleName = $role->name;
            $this->showRoleModal = true;
        }
    }

    public function saveRole()
    {
        $this->validate([
            'roleName' => $this->validationRules['roleName'],
        ]);

        if ($this->editingRoleId) {
            // Update existing role
            $role = Role::find($this->editingRoleId);
            if ($role) {
                $role->name = $this->roleName;
                $role->save();
                session()->flash('message', 'Role updated successfully!');
            }
        } else {
            // Create new role
            Role::create([
                'name' => $this->roleName,
                'guard_name' => 'web'
            ]);
            session()->flash('message', 'Role created successfully!');
        }

        $this->closeRoleModal();
        $this->loadRoles();
    }

    public function deleteRole($roleId)
    {
        $role = Role::find($roleId);
        if ($role) {
            // Remove all permissions from this role first
            $role->syncPermissions([]);
            $role->delete();

            $this->loadRules();
            $this->loadRoles();

            session()->flash('message', 'Role deleted successfully!');
        }
    }

    public function closeRoleModal()
    {
        $this->showRoleModal = false;
        $this->editingRoleId = null;
        $this->roleName = '';
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
            $authService->addRule($model, $action, null, $role->id);
            session()->flash('message', "Permission '$action' added to " . class_basename($model));
        }

        $this->loadRules();
        $this->loadRoles();
    }

    // Method to handle tab switching
    public function updatedActiveTab()
    {
        if ($this->activeTab === 'roles') {
            $this->loadRoles();
        } elseif ($this->activeTab === 'matrix') {
            $this->loadRules();
        }
    }

    // Method to handle role filter changes
    public function updatedSelectedRoleFilter()
    {
        $this->loadRules();
    }
}
