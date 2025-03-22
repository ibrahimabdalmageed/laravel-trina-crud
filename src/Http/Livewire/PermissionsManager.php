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
    public $rules = [];
    public $users = [];
    public $roles = [];

    public $selectedModel = '';
    public $selectedActions = [];
    public $isRole = 1; // Default to role
    public $selectedUserId = '';

    // New properties for enhanced UI
    public $activeTab = 'permissions';
    public $tabs = ['permissions', 'roles', 'matrix', 'user-roles'];
    public $showRoleModal = false;
    public $roleName = '';
    public $editingRoleId = null;
    public $selectedRoleFilter = '';

    // New properties for user-role assignment
    public $showUserRoleModal = false;
    public $selectedUserForRole = '';
    public $selectedRolesForUser = [];
    public $availableRolesForUser = [];

    // User-role management properties
    public $selectedUserForRoles = '';
    public $userCurrentRoles = [];
    public $allRoles = [];
    public $selectedRoleToAssign = '';

    protected $validationRules = [
        'selectedModel' => 'required|string',
        'selectedActions' => 'required|array|min:1',
        'selectedUserId' => 'required',
    ];

    protected $messages = [
        'selectedModel.required' => 'Please select a model',
        'selectedActions.required' => 'Please select at least one action',
        'selectedActions.min' => 'Please select at least one action',
        'selectedUserId.required' => 'Please select a user or role',
    ];

    public function mount()
    {
        $this->loadModels();
        $this->loadRules();
        $this->loadUsers();
        $this->loadRoles();
        $this->loadAllRoles();
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

    /**
     * Load users from the authorization service
     */
    protected function loadUsers()
    {
        /**
         * @var AuthorizationServiceInterface $authService
         */
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

    /**
     * Load all roles for the user-role management tab
     */
    protected function loadAllRoles()
    {
        $this->allRoles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name
            ];
        })->toArray();
    }

    public function addPermission()
    {
        $this->validate($this->validationRules);

        /**
         * @var AuthorizationServiceInterface $authService
         */
        $authService = App::make(AuthorizationServiceInterface::class);

        foreach ($this->selectedActions as $actionName) {
            $action = CrudAction::from($actionName);

            if ($this->isRole) {
                $authService->addRule($this->selectedModel, $action, $this->selectedUserId, true);
            } else {
                $authService->addRule($this->selectedModel, $action, $this->selectedUserId, false);
            }
        }

        $this->reset(['selectedActions', 'selectedUserId']);
        $this->loadRules();
        session()->flash('message', 'Permissions added successfully!');
    }

    public function deletePermission($permissionName)
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $authService->deleteRule($permissionName);

        $this->loadRules();
        $this->loadRoles(); // Refresh roles to update permission counts

        session()->flash('message', 'Permission deleted successfully!');
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
            'roleName' => 'required|string',
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
        } elseif ($this->activeTab === 'user-roles') {
            $this->loadAllRoles();
            $this->loadUserRoles();
        }
    }

    // Method to handle role filter changes
    public function updatedSelectedRoleFilter()
    {
        $this->loadRules();
    }

    // Method to show the user-role assignment modal
    public function showUserRoleAssignmentModal()
    {
        // Debug statement to verify method is being called
        session()->flash('message', 'Opening user role assignment modal');

        $this->showUserRoleModal = true;
        $this->selectedUserForRole = '';
        $this->selectedRolesForUser = [];
        $this->availableRolesForUser = [];
    }

    // Method to close the user-role assignment modal
    public function closeUserRoleModal()
    {
        $this->showUserRoleModal = false;
        $this->selectedUserForRole = '';
        $this->selectedRolesForUser = [];
        $this->availableRolesForUser = [];
    }

    // Method to load roles for a selected user
    public function updatedSelectedUserForRole()
    {
        if (!empty($this->selectedUserForRole)) {
            $userModel = app(config('auth.providers.users.model'));
            $user = $userModel::find($this->selectedUserForRole);

            if ($user) {
                // Get user's current roles
                $this->selectedRolesForUser = $user->roles->pluck('id')->toArray();

                // Get all available roles
                $this->availableRolesForUser = Role::all()->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })->toArray();
            }
        }
    }

    // Method to save user-role assignments
    public function saveUserRoles()
    {
        $this->validate([
            'selectedUserForRole' => 'required',
        ]);

        $userModel = app(config('auth.providers.users.model'));
        $user = $userModel::find($this->selectedUserForRole);

        if ($user) {
            // Sync the selected roles to the user
            $user->syncRoles($this->selectedRolesForUser);

            session()->flash('message', 'User roles updated successfully!');
            $this->closeUserRoleModal();
        }
    }

    /**
     * Directly assign a role to a user
     * 
     * @param int $userId The user ID
     * @param string $roleName The role name (e.g., 'admin', 'editor')
     */
    public function assignRoleToUser($userId, $roleName)
    {
        $userModel = app(config('auth.providers.users.model'));
        $user = $userModel::find($userId);

        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            // Create the role if it doesn't exist
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            session()->flash('message', "Role '$roleName' created and assigned to user");
        } else {
            session()->flash('message', "Role '$roleName' assigned to user");
        }

        // Assign the role to the user
        $user->assignRole($role);

        // Refresh the data
        $this->loadUsers();
        $this->loadRoles();
    }

    /**
     * Load current roles for the selected user
     */
    public function updatedSelectedUserForRoles()
    {
        $this->loadUserRoles();
        // Debug message to confirm the method is being called
        session()->flash('message', "User selection updated. Selected user ID: {$this->selectedUserForRoles}");
    }

    /**
     * Load the current roles for the selected user
     */
    protected function loadUserRoles()
    {
        $this->userCurrentRoles = [];

        if (!empty($this->selectedUserForRoles)) {
            $userModel = app(config('auth.providers.users.model'));
            $user = $userModel::find($this->selectedUserForRoles);

            if ($user) {
                $this->userCurrentRoles = $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name
                    ];
                })->toArray();
            } else {
                session()->flash('error', "Failed to load user with ID: {$this->selectedUserForRoles}");
            }
        }
    }

    /**
     * Assign a role to the selected user
     */
    public function assignRoleToSelectedUser()
    {
        if (empty($this->selectedUserForRoles) || empty($this->selectedRoleToAssign)) {
            session()->flash('error', 'Please select both a user and a role');
            return;
        }

        $userModel = app(config('auth.providers.users.model'));
        $user = $userModel::find($this->selectedUserForRoles);
        $role = Role::find($this->selectedRoleToAssign);

        if ($user && $role) {
            // Debug information
            session()->flash('message', "Attempting to assign role '{$role->name}' to user. User ID: {$this->selectedUserForRoles}, Role ID: {$this->selectedRoleToAssign}");

            try {
                $user->assignRole($role);
                session()->flash('message', "Role '{$role->name}' assigned to user successfully");
            } catch (\Exception $e) {
                session()->flash('error', "Error assigning role: " . $e->getMessage());
            }

            $this->loadUserRoles();
            $this->selectedRoleToAssign = '';
        } else {
            if (!$user) {
                session()->flash('error', "User not found with ID: {$this->selectedUserForRoles}");
            }
            if (!$role) {
                session()->flash('error', "Role not found with ID: {$this->selectedRoleToAssign}");
            }
        }
    }

    /**
     * Remove a role from the selected user
     */
    public function removeRoleFromUser($roleId)
    {
        if (empty($this->selectedUserForRoles)) {
            session()->flash('error', 'No user selected');
            return;
        }

        $userModel = app(config('auth.providers.users.model'));
        $user = $userModel::find($this->selectedUserForRoles);
        $role = Role::find($roleId);

        if ($user && $role) {
            $user->removeRole($role);
            session()->flash('message', "Role '{$role->name}' removed from user successfully");
            $this->loadUserRoles();
        }
    }
}
