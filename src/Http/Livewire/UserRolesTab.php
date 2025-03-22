<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Role;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class UserRolesTab extends Component
{
    public $users = [];
    public $allRoles = [];

    // User-role management properties
    public $selectedUserForRoles = '';
    public $userCurrentRoles = [];
    public $selectedRoleToAssign = '';

    public $selectedUserForRole = '';
    public $selectedRolesForUser = [];
    public $availableRolesForUser = [];

    protected $listeners = ['rolesChanged' => 'loadAllRoles'];

    public function mount()
    {
        $this->loadUsers();
        $this->loadAllRoles();
    }

    public function render()
    {
        return view('trina-crud::livewire.user-roles-tab');
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

    /**
     * Load current roles for the selected user
     */
    public function updatedSelectedUserForRoles($value)
    {
        $this->loadUserRoles();
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

    // Method to show the user-role assignment modal
    public function showUserRoleAssignmentModal()
    {
        $this->selectedUserForRole = '';
        $this->selectedRolesForUser = [];
        $this->availableRolesForUser = [];
    }

    // Method to close the user-role assignment modal
    public function closeUserRoleModal()
    {
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
            $this->loadUserRoles();
        }
    }

    /**
     * Handle the wire:change event from the user select dropdown
     */
    public function updateSelectedUser()
    {
        $this->loadUserRoles();
    }
}
