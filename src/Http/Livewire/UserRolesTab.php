<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class UserRolesTab extends Component
{
    public array $roles = [];
    public array $users = [];
    public string $selectedUser = '';
    public string $selectedRole = '';

    public array $currentRoles = [];

    public function mount()
    {
        $this->roles = $this->getAuthorizationService()->getAllRoles();
        $this->users = $this->getAuthorizationService()->getAllUsers();
    }

    public function render()
    {
        return view('trina-crud::livewire.user-roles-tab');
    }

    private function getAuthorizationService(): AuthorizationServiceInterface
    {
        return app(AuthorizationServiceInterface::class);
    }

    public function assignRoleToUser()
    {
        $this->getAuthorizationService()->assignRoleToUser($this->selectedRole, $this->selectedUser);
        $this->getCurrentRoles();
    }

    public function removeRoleForUser($role)
    {
        $this->getAuthorizationService()->removeRoleForUser($role, $this->selectedUser);
        $this->getCurrentRoles();
    }

    public function getCurrentRoles()
    {
        $this->currentRoles = $this->getAuthorizationService()->getUserRoles($this->selectedUser);
    }
}
