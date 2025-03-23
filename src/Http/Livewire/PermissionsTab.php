<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Models\ModelSchema;

class PermissionsTab extends Component
{


    protected $validationRules = [
        'selectedModel' => 'required|string',
        'selectedActions' => 'required|array|min:1',
        'selectedRole' => 'required',
    ];

    protected $messages = [
        'selectedModel.required' => 'Please select a model',
        'selectedActions.required' => 'Please select at least one action',
        'selectedActions.min' => 'Please select at least one action',
        'selectedRole.required' => 'Please select a user or role',
    ];


    public function mount() {}

    public function render()
    {
        return view('trina-crud::livewire.permissions-tab');
    }
}
