<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Role;
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
            $roleModel = Role::find($role['id']);
            $role['permissionsCount'] = $roleModel ? $roleModel->permissions->count() : 0;
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
        $this->emit('rolesChanged');
    }

    public function deleteRole($roleId)
    {
        $role = Role::find($roleId);
        if ($role) {
            // Remove all permissions from this role first
            $role->syncPermissions([]);
            $role->delete();

            $this->loadRoles();
            $this->emit('rolesChanged');
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
