<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class RolesTab extends Component
{

    public function mount() {}

    public function render()
    {
        return view('trina-crud::livewire.roles-tab');
    }
}
