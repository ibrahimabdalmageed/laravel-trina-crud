<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class RolesTab extends Component
{
    public $roles = [];
    public $showRoleModal = false;
    public $roleName = '';
    public $editingRoleId = null;

    protected $listeners = ['permissionsChanged' => 'loadRoles'];

    public function mount()
    {
        $this->loadRoles();
    }

    public function render()
    {
        return view('trina-crud::livewire.roles-tab');
    }

    public function loadRoles()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $roles = $authService->getAllRoles();

        // Add permissions count to each role
        foreach ($roles as &$role) {
            $roleModel = $authService->findRole($role['id']);
            $role['permissionsCount'] = $roleModel ? count($roleModel->permissions) : 0;
        }

        $this->roles = $roles;
    }

    public function showCreateRoleModal()
    {
        $this->showRoleModal = true;
        $this->editingRoleId = null;
        $this->roleName = '';
    }

    public function editRole($roleId)
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $role = $authService->findRole($roleId);
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

        $authService = App::make(AuthorizationServiceInterface::class);

        if ($this->editingRoleId) {
            // Update existing role
            $role = $authService->findRole($this->editingRoleId);
            if ($role) {
                $role->name = $this->roleName;
                $role->save();
                session()->flash('message', 'Role updated successfully!');
            }
        } else {
            // Create new role - we need to add a method to the authorization service
            // Since we don't have a createRole method in the interface yet, we'll use the existing role model
            // This should be updated later to use the authorization service
            $guardName = config('auth.defaults.guard', 'web');
            $roleClass = config('permission.models.role');
            $roleClass::create([
                'name' => $this->roleName,
                'guard_name' => $guardName
            ]);
            session()->flash('message', 'Role created successfully!');
        }

        $this->closeRoleModal();
        $this->loadRoles();
        $this->dispatch('rolesChanged');
    }

    public function deleteRole($roleId)
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $role = $authService->findRole($roleId);
        if ($role) {
            // Remove all permissions from this role first
            $role->syncPermissions([]);
            $role->delete();

            $this->loadRoles();
            $this->dispatch('rolesChanged');
            session()->flash('message', 'Role deleted successfully!');
        }
    }

    public function closeRoleModal()
    {
        $this->showRoleModal = false;
        $this->editingRoleId = null;
        $this->roleName = '';
    }
}
