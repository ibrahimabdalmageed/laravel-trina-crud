<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class RolesTab extends Component
{

    public $roles = [];

    public $openCreateRoleModal = false;

    public $createRoleName = '';

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
        $this->roles = $this->getAuthorizationService()->getAllRoles();
    }

    public function deleteRole($role)
    {
        $this->getAuthorizationService()->deleteRole($role);
        $this->loadRoles();
    }

    private function getAuthorizationService(): AuthorizationServiceInterface
    {
        return app(AuthorizationServiceInterface::class);
    }

    public function createRole()
    {
        $this->getAuthorizationService()->createRole($this->createRoleName);
        $this->hideCreateRoleModal();
        $this->loadRoles();
    }

    public function showCreateRoleModal()
    {
        $this->openCreateRoleModal = true;
    }

    public function hideCreateRoleModal()
    {
        $this->openCreateRoleModal = false;
    }
}
