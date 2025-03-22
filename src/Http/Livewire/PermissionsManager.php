<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;

class PermissionsManager extends Component
{
    public $activeTab = 'permissions';
    public $tabs = ['permissions', 'roles', 'matrix', 'user-roles'];

    protected $listeners = [
        'permissionsChanged' => '$refresh',
        'rolesChanged' => '$refresh'
    ];

    public function render()
    {
        return view('trina-crud::livewire.permissions-manager');
    }

    // Method to handle tab switching
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }
}
